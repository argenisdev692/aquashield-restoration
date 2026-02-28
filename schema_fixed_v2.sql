-- ============================================================
-- SCHEMA ROBUSTO DE AUTH PARA SUPABASE — VERSIÓN CORREGIDA v2
-- FIX PRINCIPAL: políticas RLS con dependencias cruzadas
-- (roles ↔ user_roles) se agregan AL FINAL, después de crear
-- todas las tablas. Elimina el error 42P01.
-- ============================================================

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ============================================================
-- FUNCIÓN AUXILIAR
-- ============================================================
CREATE OR REPLACE FUNCTION public.set_updated_at()
RETURNS TRIGGER AS $set_updated_at$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$set_updated_at$
LANGUAGE plpgsql;

-- ============================================================
-- 1. public.users
-- ============================================================
CREATE TABLE public.users (
  id                    UUID        PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
  uuid                  UUID        NOT NULL DEFAULT uuid_generate_v4() UNIQUE,
  name                  TEXT        NOT NULL,
  last_name             TEXT,
  username              TEXT        UNIQUE,
  email                 TEXT        NOT NULL UNIQUE,
  email_verified_at     TIMESTAMPTZ,
  phone                 TEXT,
  date_of_birth         DATE,
  address               TEXT,
  zip_code              TEXT,
  city                  TEXT,
  state                 TEXT,
  country               TEXT,
  gender                TEXT        CHECK (gender IN ('male','female','non_binary','prefer_not_to_say','other')),
  profile_photo_path    TEXT,
  latitude              NUMERIC(10,6),
  longitude             NUMERIC(10,6),
  terms_and_conditions  BOOLEAN     NOT NULL DEFAULT FALSE,
  current_team_id       UUID,
  is_active             BOOLEAN     NOT NULL DEFAULT TRUE,
  last_login_at         TIMESTAMPTZ,
  login_count           INTEGER     NOT NULL DEFAULT 0,
  failed_login_attempts INTEGER     NOT NULL DEFAULT 0,
  locked_until          TIMESTAMPTZ,
  created_at            TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at            TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  deleted_at            TIMESTAMPTZ
);

ALTER TABLE public.users ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Users can view own profile"
  ON public.users FOR SELECT
  USING (auth.uid() = id);

CREATE POLICY "Users can update own profile"
  ON public.users FOR UPDATE
  USING (auth.uid() = id);

CREATE TRIGGER users_updated_at
  BEFORE UPDATE ON public.users
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER AS $handle_new_user$
BEGIN
  INSERT INTO public.users (id, email, name)
  VALUES (
    NEW.id,
    NEW.email,
    COALESCE(NEW.raw_user_meta_data->>'name', split_part(NEW.email, '@', 1))
  );
  RETURN NEW;
END;
$handle_new_user$
LANGUAGE plpgsql SECURITY DEFINER;

CREATE TRIGGER on_auth_user_created
  AFTER INSERT ON auth.users
  FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();


-- ============================================================
-- 2. public.user_sessions
-- ============================================================
CREATE TABLE public.user_sessions (
  id               UUID        PRIMARY KEY DEFAULT uuid_generate_v4(),
  user_id          UUID        NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  session_token    TEXT        NOT NULL UNIQUE DEFAULT encode(gen_random_bytes(64), 'hex'),
  ip_address       INET,
  user_agent       TEXT,
  device_type      TEXT        CHECK (device_type IN ('web','mobile','desktop','tablet','unknown')) DEFAULT 'unknown',
  device_name      TEXT,
  os               TEXT,
  browser          TEXT,
  country          TEXT,
  city             TEXT,
  is_active        BOOLEAN     NOT NULL DEFAULT TRUE,
  last_activity_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  expires_at       TIMESTAMPTZ NOT NULL DEFAULT (NOW() + INTERVAL '30 days'),
  created_at       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  revoked_at       TIMESTAMPTZ,
  revoke_reason    TEXT        CHECK (revoke_reason IN ('logout','password_change','admin_revoke','expired','security'))
);

CREATE INDEX idx_user_sessions_user_id ON public.user_sessions(user_id);
CREATE INDEX idx_user_sessions_token   ON public.user_sessions(session_token);
CREATE INDEX idx_user_sessions_active  ON public.user_sessions(user_id, is_active) WHERE is_active = TRUE;

ALTER TABLE public.user_sessions ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Users can view own sessions"
  ON public.user_sessions FOR SELECT
  USING (auth.uid() = user_id);

CREATE POLICY "Users can revoke own sessions"
  ON public.user_sessions FOR UPDATE
  USING (auth.uid() = user_id);


-- ============================================================
-- 3. public.refresh_tokens
-- ============================================================
CREATE TABLE public.refresh_tokens (
  id           UUID        PRIMARY KEY DEFAULT uuid_generate_v4(),
  user_id      UUID        NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  session_id   UUID        REFERENCES public.user_sessions(id) ON DELETE CASCADE,
  token        TEXT        NOT NULL UNIQUE DEFAULT encode(gen_random_bytes(64), 'hex'),
  parent_token TEXT,
  is_used      BOOLEAN     NOT NULL DEFAULT FALSE,
  is_revoked   BOOLEAN     NOT NULL DEFAULT FALSE,
  ip_address   INET,
  user_agent   TEXT,
  used_at      TIMESTAMPTZ,
  expires_at   TIMESTAMPTZ NOT NULL DEFAULT (NOW() + INTERVAL '90 days'),
  created_at   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_refresh_tokens_user_id ON public.refresh_tokens(user_id);
CREATE INDEX idx_refresh_tokens_token   ON public.refresh_tokens(token);
CREATE INDEX idx_refresh_tokens_active  ON public.refresh_tokens(token)
  WHERE is_used = FALSE AND is_revoked = FALSE;

ALTER TABLE public.refresh_tokens ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Users can view own refresh tokens"
  ON public.refresh_tokens FOR SELECT
  USING (auth.uid() = user_id);


-- ============================================================
-- 4. public.password_reset_tokens  (solo service_role)
-- ============================================================
CREATE TABLE public.password_reset_tokens (
  id         UUID        PRIMARY KEY DEFAULT uuid_generate_v4(),
  user_id    UUID        NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  email      TEXT        NOT NULL,
  token      TEXT        NOT NULL UNIQUE DEFAULT encode(gen_random_bytes(32), 'hex'),
  token_hash TEXT        NOT NULL DEFAULT encode(digest(gen_random_bytes(32), 'sha256'), 'hex'),
  is_used    BOOLEAN     NOT NULL DEFAULT FALSE,
  used_at    TIMESTAMPTZ,
  ip_address INET,
  expires_at TIMESTAMPTZ NOT NULL DEFAULT (NOW() + INTERVAL '1 hour'),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_password_reset_user_id     ON public.password_reset_tokens(user_id);
CREATE INDEX idx_password_reset_token       ON public.password_reset_tokens(token);
CREATE INDEX idx_password_reset_active_user ON public.password_reset_tokens(user_id)
  WHERE is_used = FALSE;

ALTER TABLE public.password_reset_tokens ENABLE ROW LEVEL SECURITY;
-- Sin políticas públicas: acceso solo vía service_role (backend)


-- ============================================================
-- 5. public.login_otps  (solo service_role)
-- ============================================================
CREATE TABLE public.login_otps (
  id              UUID        PRIMARY KEY DEFAULT uuid_generate_v4(),
  user_id         UUID        REFERENCES public.users(id) ON DELETE CASCADE,
  identifier      TEXT        NOT NULL,
  identifier_type TEXT        NOT NULL CHECK (identifier_type IN ('email','phone')),
  otp_code        CHAR(6)     NOT NULL DEFAULT lpad(floor(random() * 1000000)::TEXT, 6, '0'),
  otp_hash        TEXT        NOT NULL,
  purpose         TEXT        NOT NULL CHECK (purpose IN ('login','signup','phone_verify','email_verify','2fa')),
  attempts        SMALLINT    NOT NULL DEFAULT 0,
  max_attempts    SMALLINT    NOT NULL DEFAULT 5,
  is_used         BOOLEAN     NOT NULL DEFAULT FALSE,
  is_blocked      BOOLEAN     NOT NULL DEFAULT FALSE,
  used_at         TIMESTAMPTZ,
  ip_address      INET,
  expires_at      TIMESTAMPTZ NOT NULL DEFAULT (NOW() + INTERVAL '10 minutes'),
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_login_otps_user_id    ON public.login_otps(user_id);
CREATE INDEX idx_login_otps_identifier ON public.login_otps(identifier, identifier_type);
CREATE INDEX idx_login_otps_active     ON public.login_otps(identifier)
  WHERE is_used = FALSE AND is_blocked = FALSE;

ALTER TABLE public.login_otps ENABLE ROW LEVEL SECURITY;
-- Sin políticas públicas: acceso solo vía service_role (backend)

CREATE OR REPLACE FUNCTION public.generate_otp(
  p_identifier      TEXT,
  p_identifier_type TEXT,
  p_purpose         TEXT,
  p_user_id         UUID DEFAULT NULL
)
RETURNS TEXT AS $generate_otp$
DECLARE
  v_code TEXT;
BEGIN
  UPDATE public.login_otps
  SET is_used = TRUE, used_at = NOW()
  WHERE identifier = p_identifier
    AND purpose    = p_purpose
    AND is_used    = FALSE;

  v_code := lpad(floor(random() * 1000000)::TEXT, 6, '0');

  INSERT INTO public.login_otps (user_id, identifier, identifier_type, otp_code, otp_hash, purpose)
  VALUES (
    p_user_id,
    p_identifier,
    p_identifier_type,
    v_code,
    encode(digest(v_code || p_identifier, 'sha256'), 'hex'),
    p_purpose
  );

  RETURN v_code;
END;
$generate_otp$
LANGUAGE plpgsql SECURITY DEFINER;


-- ============================================================
-- 6. public.account_delete_codes
-- ============================================================
CREATE TABLE public.account_delete_codes (
  id                UUID        PRIMARY KEY DEFAULT uuid_generate_v4(),
  user_id           UUID        NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  code              CHAR(6)     NOT NULL DEFAULT lpad(floor(random() * 1000000)::TEXT, 6, '0'),
  code_hash         TEXT        NOT NULL,
  confirmation_text TEXT,
  is_used           BOOLEAN     NOT NULL DEFAULT FALSE,
  used_at           TIMESTAMPTZ,
  ip_address        INET,
  expires_at        TIMESTAMPTZ NOT NULL DEFAULT (NOW() + INTERVAL '15 minutes'),
  created_at        TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_delete_codes_user_id     ON public.account_delete_codes(user_id);
CREATE INDEX idx_delete_codes_active_user ON public.account_delete_codes(user_id)
  WHERE is_used = FALSE;

ALTER TABLE public.account_delete_codes ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Users can view own delete codes"
  ON public.account_delete_codes FOR SELECT
  USING (auth.uid() = user_id);


-- ============================================================
-- 7. public.auth_audit_log
-- ============================================================
CREATE TABLE public.auth_audit_log (
  id         BIGSERIAL   PRIMARY KEY,
  user_id    UUID        REFERENCES public.users(id) ON DELETE SET NULL,
  session_id UUID        REFERENCES public.user_sessions(id) ON DELETE SET NULL,
  event_type TEXT        NOT NULL CHECK (event_type IN (
                'login_success','login_failed','login_otp_sent',
                'login_otp_verified','login_otp_failed',
                'logout','token_refreshed','token_revoked',
                'password_reset_requested','password_reset_success',
                'password_changed','email_verified',
                'account_delete_requested','account_deleted',
                'account_locked','account_unlocked',
                'mfa_enabled','mfa_disabled','mfa_verified'
              )),
  identifier TEXT,
  ip_address INET,
  user_agent TEXT,
  country    TEXT,
  city       TEXT,
  metadata   JSONB       DEFAULT '{}'::jsonb,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_audit_log_user_id ON public.auth_audit_log(user_id);
CREATE INDEX idx_audit_log_event   ON public.auth_audit_log(event_type);
CREATE INDEX idx_audit_log_created ON public.auth_audit_log(created_at DESC);
CREATE INDEX idx_audit_log_ip      ON public.auth_audit_log(ip_address);

ALTER TABLE public.auth_audit_log ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Users can view own audit log"
  ON public.auth_audit_log FOR SELECT
  USING (auth.uid() = user_id);


-- ============================================================
-- 8. Función de limpieza
-- ============================================================
CREATE OR REPLACE FUNCTION public.cleanup_expired_auth_records()
RETURNS void AS $cleanup$
BEGIN
  DELETE FROM public.login_otps
    WHERE expires_at < NOW() - INTERVAL '1 day';

  DELETE FROM public.password_reset_tokens
    WHERE expires_at < NOW() - INTERVAL '1 day';

  DELETE FROM public.account_delete_codes
    WHERE expires_at < NOW() - INTERVAL '1 day';

  UPDATE public.user_sessions
    SET is_active = FALSE, revoked_at = NOW(), revoke_reason = 'expired'
    WHERE expires_at < NOW() AND is_active = TRUE;

  UPDATE public.refresh_tokens
    SET is_revoked = TRUE
    WHERE expires_at < NOW() AND is_revoked = FALSE;
END;
$cleanup$
LANGUAGE plpgsql SECURITY DEFINER;


-- ============================================================
-- 9. Vista: sesiones activas
-- ============================================================
CREATE OR REPLACE VIEW public.active_user_sessions AS
SELECT
  s.id, s.user_id, s.ip_address, s.device_type, s.device_name,
  s.os, s.browser, s.country, s.last_activity_at, s.expires_at,
  s.created_at,
  (s.expires_at > NOW()) AS is_valid
FROM public.user_sessions s
WHERE s.is_active = TRUE AND s.revoked_at IS NULL;


-- ============================================================
-- public.email_data
-- ============================================================
CREATE TABLE public.email_data (
  id          BIGSERIAL   PRIMARY KEY,
  uuid        UUID        NOT NULL DEFAULT uuid_generate_v4() UNIQUE,
  description TEXT,
  email       TEXT        NOT NULL,
  phone       TEXT,
  type        TEXT        CHECK (type IN ('Collections','Info','Admin','Support','Other')),
  user_id     UUID        NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  deleted_at  TIMESTAMPTZ
);

CREATE INDEX idx_email_data_user_id ON public.email_data(user_id);
CREATE INDEX idx_email_data_type    ON public.email_data(type);

ALTER TABLE public.email_data ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Users can view own email_data"
  ON public.email_data FOR SELECT
  USING (auth.uid() = user_id);

CREATE POLICY "Users can manage own email_data"
  ON public.email_data FOR ALL
  USING (auth.uid() = user_id);

CREATE TRIGGER email_data_updated_at
  BEFORE UPDATE ON public.email_data
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


-- ============================================================
-- RBAC — Crear tablas SIN políticas RLS por ahora
-- (las políticas se añaden después para evitar el error 42P01)
-- ============================================================

-- 10. public.roles
CREATE TABLE public.roles (
  id           UUID        PRIMARY KEY DEFAULT uuid_generate_v4(),
  name         TEXT        NOT NULL UNIQUE,
  display_name TEXT        NOT NULL,
  description  TEXT,
  is_system    BOOLEAN     NOT NULL DEFAULT FALSE,
  created_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  deleted_at   TIMESTAMPTZ
);

CREATE INDEX idx_roles_name   ON public.roles(name);
CREATE INDEX idx_roles_active ON public.roles(deleted_at) WHERE deleted_at IS NULL;

CREATE TRIGGER roles_updated_at
  BEFORE UPDATE ON public.roles
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

ALTER TABLE public.roles ENABLE ROW LEVEL SECURITY;
-- POLÍTICA se añade al final ↓


-- 11. public.permissions
CREATE TABLE public.permissions (
  id          UUID        PRIMARY KEY DEFAULT uuid_generate_v4(),
  action      TEXT        NOT NULL CHECK (action IN ('manage','create','read','update','delete')),
  subject     TEXT        NOT NULL,
  conditions  JSONB,
  fields      TEXT[],
  description TEXT,
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  deleted_at  TIMESTAMPTZ
);

CREATE UNIQUE INDEX idx_permissions_action_subject
  ON public.permissions(action, subject) WHERE deleted_at IS NULL;

CREATE INDEX idx_permissions_active ON public.permissions(deleted_at) WHERE deleted_at IS NULL;

CREATE TRIGGER permissions_updated_at
  BEFORE UPDATE ON public.permissions
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

ALTER TABLE public.permissions ENABLE ROW LEVEL SECURITY;
-- POLÍTICA se añade al final ↓


-- 12. public.role_permissions
CREATE TABLE public.role_permissions (
  id            UUID        PRIMARY KEY DEFAULT uuid_generate_v4(),
  role_id       UUID        NOT NULL REFERENCES public.roles(id)       ON DELETE CASCADE,
  permission_id UUID        NOT NULL REFERENCES public.permissions(id) ON DELETE CASCADE,
  created_at    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  deleted_at    TIMESTAMPTZ,
  CONSTRAINT uq_role_permission UNIQUE (role_id, permission_id)
);

CREATE INDEX idx_role_permissions_role_id       ON public.role_permissions(role_id);
CREATE INDEX idx_role_permissions_permission_id ON public.role_permissions(permission_id);
CREATE INDEX idx_role_permissions_active
  ON public.role_permissions(role_id, permission_id) WHERE deleted_at IS NULL;

ALTER TABLE public.role_permissions ENABLE ROW LEVEL SECURITY;
-- POLÍTICA se añade al final ↓


-- 13. public.user_roles
CREATE TABLE public.user_roles (
  id         UUID        PRIMARY KEY DEFAULT uuid_generate_v4(),
  user_id    UUID        NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  role_id    UUID        NOT NULL REFERENCES public.roles(id) ON DELETE CASCADE,
  granted_by UUID        REFERENCES public.users(id) ON DELETE SET NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  deleted_at TIMESTAMPTZ,
  CONSTRAINT uq_user_role UNIQUE (user_id, role_id)
);

CREATE INDEX idx_user_roles_user_id ON public.user_roles(user_id);
CREATE INDEX idx_user_roles_role_id ON public.user_roles(role_id);
CREATE INDEX idx_user_roles_active  ON public.user_roles(user_id) WHERE deleted_at IS NULL;

ALTER TABLE public.user_roles ENABLE ROW LEVEL SECURITY;

-- Política simple (no depende de otra tabla):
CREATE POLICY "Users can view own roles"
  ON public.user_roles FOR SELECT
  USING (auth.uid() = user_id);
-- POLÍTICA de super admin se añade al final ↓


-- ============================================================
-- 14. public.company_data
-- ============================================================
CREATE TABLE public.company_data (
  id             BIGSERIAL   PRIMARY KEY,
  uuid           UUID        NOT NULL DEFAULT uuid_generate_v4() UNIQUE,
  name           TEXT,
  company_name   TEXT        NOT NULL,
  signature_path TEXT,
  email          TEXT,
  phone          TEXT,
  address        TEXT,
  website        TEXT,
  facebook_link  TEXT,
  instagram_link TEXT,
  linkedin_link  TEXT,
  twitter_link   TEXT,
  latitude       NUMERIC(10,6),
  longitude      NUMERIC(10,6),
  user_id        UUID        NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  created_at     TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at     TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  deleted_at     TIMESTAMPTZ
);

CREATE INDEX idx_company_data_user_id ON public.company_data(user_id);
CREATE INDEX idx_company_data_email   ON public.company_data(email);
CREATE INDEX idx_company_data_active  ON public.company_data(deleted_at) WHERE deleted_at IS NULL;

CREATE TRIGGER company_data_updated_at
  BEFORE UPDATE ON public.company_data
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

ALTER TABLE public.company_data ENABLE ROW LEVEL SECURITY;
-- POLÍTICAS se añaden al final ↓


-- ============================================================
-- VISTAS (todas las tablas ya existen)
-- ============================================================
CREATE OR REPLACE VIEW public.user_effective_permissions AS
SELECT DISTINCT
  ur.user_id,
  r.name     AS role_name,
  p.action,
  p.subject,
  p.conditions,
  p.fields
FROM public.user_roles      ur
JOIN public.roles            r  ON r.id  = ur.role_id       AND r.deleted_at  IS NULL
JOIN public.role_permissions rp ON rp.role_id = r.id        AND rp.deleted_at IS NULL
JOIN public.permissions      p  ON p.id  = rp.permission_id AND p.deleted_at  IS NULL
WHERE ur.deleted_at IS NULL;


-- ============================================================
-- RLS: POLÍTICAS CON DEPENDENCIAS CRUZADAS
-- Se crean AQUÍ, cuando todas las tablas ya existen
-- ============================================================

-- Helper: verifica si el usuario actual tiene un rol dado
-- Usado en múltiples políticas para evitar repetición
CREATE OR REPLACE FUNCTION public.has_role(p_role_name TEXT)
RETURNS BOOLEAN AS $has_role$
  SELECT EXISTS (
    SELECT 1
    FROM public.user_roles ur
    JOIN public.roles r ON r.id = ur.role_id
    WHERE ur.user_id = auth.uid()
      AND r.name = p_role_name
      AND ur.deleted_at IS NULL
      AND r.deleted_at  IS NULL
  );
$has_role$
LANGUAGE sql STABLE SECURITY DEFINER;

-- public.roles
CREATE POLICY "Super admins can manage roles"
  ON public.roles FOR ALL
  USING (public.has_role('ROLE_SUPER_ADMIN'));

-- public.permissions
CREATE POLICY "Super admins can manage permissions"
  ON public.permissions FOR ALL
  USING (public.has_role('ROLE_SUPER_ADMIN'));

-- public.role_permissions
CREATE POLICY "Super admins can manage role_permissions"
  ON public.role_permissions FOR ALL
  USING (public.has_role('ROLE_SUPER_ADMIN'));

-- public.user_roles
CREATE POLICY "Super admins can manage user_roles"
  ON public.user_roles FOR ALL
  USING (public.has_role('ROLE_SUPER_ADMIN'));

-- public.company_data
CREATE POLICY "Owner or Super Admin can manage company_data"
  ON public.company_data FOR ALL
  USING (
    auth.uid() = user_id
    OR public.has_role('ROLE_SUPER_ADMIN')
  );

CREATE POLICY "Admins can read company_data"
  ON public.company_data FOR SELECT
  USING (
    public.has_role('ROLE_SUPER_ADMIN')
    OR public.has_role('ROLE_ADMIN')
  );


-- ============================================================
-- SEEDERS
-- ============================================================

INSERT INTO public.users (id, uuid, name, last_name, username, email, terms_and_conditions, is_active, email_verified_at)
VALUES
  ('a1b2c3d4-0002-0002-0002-000000000002', uuid_generate_v4(), 'Argenis',       'Gonzalez', 'argenis692',            'josegonzalezcr2794@gmail.com',       TRUE, TRUE, NOW()),
  ('a1b2c3d4-0003-0003-0003-000000000003', uuid_generate_v4(), 'Administrator', NULL,       'adminAppointment',      'admin@vidula.com',  TRUE, TRUE, NOW());

INSERT INTO public.email_data (uuid, description, email, phone, type, user_id)
VALUES
  (uuid_generate_v4(), 'Correo para colecciones y pagos',  'collection@vidula.com', '+17133646240', 'Collections', 'a1b2c3d4-0002-0002-0002-000000000002'),
  (uuid_generate_v4(), 'Correo para información general',  'info@vidula.com',       '+17135876423', 'Info',        'a1b2c3d4-0002-0002-0002-000000000002'),
  (uuid_generate_v4(), 'Correo para citas y agendamiento', 'admin@vidula.com',      '+17135876423', 'Admin',       'a1b2c3d4-0003-0003-0003-000000000003');

INSERT INTO public.roles (id, name, display_name, description, is_system)
VALUES
  ('b0000000-0001-0001-0001-000000000001', 'ROLE_SUPER_ADMIN', 'Super Admin',    'Acceso total al sistema.',                                              TRUE),
  ('b0000000-0002-0002-0002-000000000002', 'ROLE_ADMIN',       'Administrator',  'Gestión operativa: usuarios, citas, soportes y datos de empresa.',     TRUE);

INSERT INTO public.permissions (id, action, subject, description)
VALUES
  ('c0000000-0001-0001-0001-000000000001', 'manage', 'all',            'Permiso total CASL'),
  ('c0000000-0002-0001-0001-000000000001', 'create', 'User',           'Crear usuarios'),
  ('c0000000-0002-0001-0001-000000000002', 'read',   'User',           'Leer usuarios'),
  ('c0000000-0002-0001-0001-000000000003', 'update', 'User',           'Actualizar usuarios'),
  ('c0000000-0002-0001-0001-000000000004', 'delete', 'User',           'Eliminar usuarios'),
  ('c0000000-0002-0002-0002-000000000001', 'create', 'Appointment',    'Crear citas'),
  ('c0000000-0002-0002-0002-000000000002', 'read',   'Appointment',    'Leer citas'),
  ('c0000000-0002-0002-0002-000000000003', 'update', 'Appointment',    'Actualizar citas'),
  ('c0000000-0002-0002-0002-000000000004', 'delete', 'Appointment',    'Eliminar citas'),
  ('c0000000-0002-0003-0003-000000000001', 'create', 'CompanyData',    'Crear datos empresa'),
  ('c0000000-0002-0003-0003-000000000002', 'read',   'CompanyData',    'Leer datos empresa'),
  ('c0000000-0002-0003-0003-000000000003', 'update', 'CompanyData',    'Actualizar datos empresa'),
  ('c0000000-0002-0003-0003-000000000004', 'delete', 'CompanyData',    'Eliminar datos empresa'),
  ('c0000000-0002-0004-0004-000000000001', 'create', 'ContactSupport', 'Crear tickets soporte'),
  ('c0000000-0002-0004-0004-000000000002', 'read',   'ContactSupport', 'Leer tickets soporte'),
  ('c0000000-0002-0004-0004-000000000003', 'update', 'ContactSupport', 'Actualizar tickets soporte'),
  ('c0000000-0002-0004-0004-000000000004', 'delete', 'ContactSupport', 'Eliminar tickets soporte'),
  ('c0000000-0002-0005-0005-000000000001', 'create', 'EmailData',      'Crear email data'),
  ('c0000000-0002-0005-0005-000000000002', 'read',   'EmailData',      'Leer email data'),
  ('c0000000-0002-0005-0005-000000000003', 'update', 'EmailData',      'Actualizar email data'),
  ('c0000000-0002-0005-0005-000000000004', 'delete', 'EmailData',      'Eliminar email data'),
  ('c0000000-0002-0006-0006-000000000001', 'read',   'Session',        'Ver sesiones'),
  ('c0000000-0002-0006-0006-000000000002', 'delete', 'Session',        'Revocar sesiones'),
  ('c0000000-0002-0007-0007-000000000001', 'read',   'AuditLog',       'Leer audit log');

-- ROLE_SUPER_ADMIN → manage all
INSERT INTO public.role_permissions (role_id, permission_id)
VALUES ('b0000000-0001-0001-0001-000000000001', 'c0000000-0001-0001-0001-000000000001');

-- ROLE_ADMIN → todos los permisos operativos (todos excepto "manage all")
INSERT INTO public.role_permissions (role_id, permission_id)
SELECT 'b0000000-0002-0002-0002-000000000002', p.id
FROM public.permissions p
WHERE p.id <> 'c0000000-0001-0001-0001-000000000001'
  AND p.deleted_at IS NULL;

INSERT INTO public.user_roles (user_id, role_id, granted_by)
VALUES
  ('a1b2c3d4-0002-0002-0002-000000000002', 'b0000000-0001-0001-0001-000000000001', 'a1b2c3d4-0002-0002-0002-000000000002'),
  ('a1b2c3d4-0003-0003-0003-000000000003', 'b0000000-0002-0002-0002-000000000002', 'a1b2c3d4-0002-0002-0002-000000000002');

INSERT INTO public.company_data (
  uuid, name, company_name, signature_path, email, phone, address,
  website, latitude, longitude,
  facebook_link, instagram_link, linkedin_link, twitter_link, user_id
) VALUES (
  uuid_generate_v4(),
  'Argenis Gonzalez', 'Vidula LLC',
  null,
  'josegonzalezcr2794@gmail.com', '+15555555555',
  '123 Random Web Dev St, Suite 404, Tech City',
  'https://vidula.com',
  29.75516, -95.3984135,
  'https://www.facebook.com/vidula/',
  'https://www.instagram.com/vidula/',
  'https://www.linkedin.com/company/vidula/',
  'https://twitter.com/vidula',
  'a1b2c3d4-0002-0002-0002-000000000002'
) ON CONFLICT (uuid) DO NOTHING;


-- ============================================================
-- VERIFICACIONES
-- ============================================================
SELECT u.name, u.username, u.email, u.terms_and_conditions, u.is_active,
       COUNT(e.id) AS email_data_count
FROM public.users u
LEFT JOIN public.email_data e ON e.user_id = u.id
GROUP BY u.id, u.name, u.username, u.email, u.terms_and_conditions, u.is_active;

SELECT u.name, u.email, r.name AS role, p.action, p.subject
FROM public.users u
JOIN public.user_roles       ur ON ur.user_id    = u.id  AND ur.deleted_at IS NULL
JOIN public.roles             r  ON r.id          = ur.role_id AND r.deleted_at IS NULL
JOIN public.role_permissions  rp ON rp.role_id    = r.id AND rp.deleted_at IS NULL
JOIN public.permissions       p  ON p.id          = rp.permission_id AND p.deleted_at IS NULL
ORDER BY u.name, r.name, p.action, p.subject;

SELECT c.company_name, c.name AS representative, c.email, u.name AS owner
FROM public.company_data c
JOIN public.users u ON u.id = c.user_id
WHERE c.deleted_at IS NULL;
