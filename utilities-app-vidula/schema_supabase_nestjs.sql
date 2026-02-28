-- ============================================================
-- SCHEMA ROBUSTO PARA SUPABASE & NESTJS
-- USERS, COMPANY_DATA, RBAC, OTP & SESSION MANAGEMENT
-- VERSION 2.0 - 2026 (Con todas las mejoras aplicadas)
-- ============================================================
-- MEJORAS INCLUIDAS:
--   ✅ deleted_at en TODAS las tablas (soft delete consistente)
--   ✅ user_roles con deleted_at (fix has_role())
--   ✅ OTPs hasheados con pgcrypto (no en texto plano)
--   ✅ generate_otp() invalida OTPs previos automáticamente
--   ✅ Índices completos para queries NestJS
--   ✅ RLS policies completas en todas las tablas
--   ✅ pg_cron para limpieza automática de sesiones y OTPs
--   ✅ date_of_birth como DATE (no TEXT)
--   ✅ company_data usa UUID como clave de negocio
--   ✅ Función verify_otp() centralizada
--   ✅ Roles y permisos seed iniciales
-- ============================================================

-- ============================================================
-- EXTENSIONES
-- ============================================================
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "pg_cron";   -- Para cleanup automático

-- ============================================================
-- 0. FUNCIONES AUXILIARES
-- ============================================================

-- Auto-actualiza updated_at en cualquier tabla
CREATE OR REPLACE FUNCTION public.set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Soft delete helper: marca deleted_at = NOW()
CREATE OR REPLACE FUNCTION public.soft_delete()
RETURNS TRIGGER AS $$
BEGIN
  NEW.deleted_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ============================================================
-- 1. public.users  (Integrado con auth.users de Supabase)
-- ============================================================
CREATE TABLE public.users (
  id                        UUID         PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
  uuid                      UUID         NOT NULL DEFAULT uuid_generate_v4() UNIQUE,
  name                      TEXT         NOT NULL,
  last_name                 TEXT,
  username                  TEXT         UNIQUE,
  email                     TEXT         NOT NULL UNIQUE,
  email_verified_at         TIMESTAMPTZ,
  phone                     TEXT,
  date_of_birth             DATE,                                -- ✅ DATE en vez de TEXT
  address                   TEXT,
  zip_code                  TEXT,
  city                      TEXT,
  state                     TEXT,
  country                   TEXT,
  gender                    TEXT         CHECK (gender IN ('male','female','non_binary','prefer_not_to_say','other')),
  profile_photo_path        TEXT,
  latitude                  NUMERIC(10,6),
  longitude                 NUMERIC(10,6),
  terms_and_conditions      BOOLEAN      NOT NULL DEFAULT FALSE,

  -- 2FA
  two_factor_secret         TEXT,
  two_factor_recovery_codes TEXT,
  two_factor_confirmed_at   TIMESTAMPTZ,

  -- Control de acceso
  is_active                 BOOLEAN      NOT NULL DEFAULT TRUE,
  failed_login_attempts     INTEGER      NOT NULL DEFAULT 0,
  locked_until              TIMESTAMPTZ,

  -- Timestamps
  created_at                TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
  updated_at                TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
  deleted_at                TIMESTAMPTZ                          -- ✅ Soft delete
);

ALTER TABLE public.users ENABLE ROW LEVEL SECURITY;

-- Índices de users
CREATE INDEX idx_users_email         ON public.users(email)    WHERE deleted_at IS NULL;
CREATE INDEX idx_users_username      ON public.users(username) WHERE deleted_at IS NULL AND username IS NOT NULL;
CREATE INDEX idx_users_is_active     ON public.users(is_active) WHERE deleted_at IS NULL;
CREATE INDEX idx_users_phone         ON public.users(phone)    WHERE phone IS NOT NULL AND deleted_at IS NULL;

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
  device_name      TEXT,                                        -- ej: "iPhone 15 de Juan"
  is_active        BOOLEAN     NOT NULL DEFAULT TRUE,
  last_activity_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  expires_at       TIMESTAMPTZ NOT NULL DEFAULT (NOW() + INTERVAL '30 days'),
  created_at       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  revoked_at       TIMESTAMPTZ,
  deleted_at       TIMESTAMPTZ                                  -- ✅ Soft delete
);

ALTER TABLE public.user_sessions ENABLE ROW LEVEL SECURITY;

-- Índices de sesiones
CREATE INDEX idx_sessions_user_active   ON public.user_sessions(user_id)       WHERE is_active = TRUE AND revoked_at IS NULL AND deleted_at IS NULL;
CREATE INDEX idx_sessions_token         ON public.user_sessions(session_token)  WHERE deleted_at IS NULL;
CREATE INDEX idx_sessions_expires       ON public.user_sessions(expires_at)     WHERE deleted_at IS NULL;

-- ============================================================
-- 3. public.login_otps
-- ============================================================
CREATE TABLE public.login_otps (
  id              UUID        PRIMARY KEY DEFAULT uuid_generate_v4(),
  user_id         UUID        REFERENCES public.users(id) ON DELETE CASCADE,
  identifier      TEXT        NOT NULL,                         -- Email o Phone
  identifier_type TEXT        NOT NULL CHECK (identifier_type IN ('email','phone')),
  otp_hash        TEXT        NOT NULL,                         -- ✅ Hash bcrypt, no texto plano
  purpose         TEXT        NOT NULL CHECK (purpose IN ('login','signup','2fa','password_reset','email_change')),
  attempts        SMALLINT    NOT NULL DEFAULT 0,
  max_attempts    SMALLINT    NOT NULL DEFAULT 5,               -- ✅ Configurable
  is_used         BOOLEAN     NOT NULL DEFAULT FALSE,
  expires_at      TIMESTAMPTZ NOT NULL DEFAULT (NOW() + INTERVAL '10 minutes'),
  used_at         TIMESTAMPTZ,                                  -- ✅ Cuándo fue usado
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  deleted_at      TIMESTAMPTZ                                   -- ✅ Soft delete
);

ALTER TABLE public.login_otps ENABLE ROW LEVEL SECURITY;

-- Índices de OTPs
CREATE INDEX idx_otps_lookup ON public.login_otps(identifier, purpose, is_used)
  WHERE is_used = FALSE AND deleted_at IS NULL;
CREATE INDEX idx_otps_user    ON public.login_otps(user_id)    WHERE deleted_at IS NULL;
CREATE INDEX idx_otps_expires ON public.login_otps(expires_at) WHERE is_used = FALSE;

-- ============================================================
-- FUNCIÓN: generate_otp()
-- ✅ Invalida OTPs previos | ✅ Hashea con bcrypt
-- Solo llamar desde NestJS con service_role key
-- ============================================================
CREATE OR REPLACE FUNCTION public.generate_otp(
  p_identifier  TEXT,
  p_type        TEXT,
  p_purpose     TEXT,
  p_user_id     UUID DEFAULT NULL
)
RETURNS TEXT AS $$
DECLARE
  v_code TEXT;
BEGIN
  -- Invalidar todos los OTPs previos no usados del mismo identifier+purpose
  UPDATE public.login_otps
  SET is_used = TRUE, used_at = NOW()
  WHERE identifier = p_identifier
    AND purpose    = p_purpose
    AND is_used    = FALSE
    AND deleted_at IS NULL;

  -- Generar código de 6 dígitos
  v_code := lpad(floor(random() * 1000000)::TEXT, 6, '0');

  -- Insertar con hash bcrypt (work factor 8 — balance velocidad/seguridad para OTP)
  INSERT INTO public.login_otps (user_id, identifier, identifier_type, otp_hash, purpose)
  VALUES (
    p_user_id,
    p_identifier,
    p_type,
    crypt(v_code, gen_salt('bf', 8)),
    p_purpose
  );

  -- Devuelve el código en claro SOLO en este momento (para enviarlo por email/SMS)
  RETURN v_code;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- ============================================================
-- FUNCIÓN: verify_otp()
-- Valida código, controla intentos y marca como usado
-- ============================================================
CREATE OR REPLACE FUNCTION public.verify_otp(
  p_identifier TEXT,
  p_code       TEXT,
  p_purpose    TEXT
)
RETURNS JSONB AS $$
DECLARE
  v_otp   public.login_otps%ROWTYPE;
  v_result JSONB;
BEGIN
  -- Buscar OTP vigente
  SELECT * INTO v_otp
  FROM public.login_otps
  WHERE identifier  = p_identifier
    AND purpose     = p_purpose
    AND is_used     = FALSE
    AND expires_at  > NOW()
    AND deleted_at  IS NULL
  ORDER BY created_at DESC
  LIMIT 1;

  -- OTP no encontrado
  IF v_otp.id IS NULL THEN
    RETURN jsonb_build_object('success', FALSE, 'error', 'otp_not_found');
  END IF;

  -- Demasiados intentos
  IF v_otp.attempts >= v_otp.max_attempts THEN
    UPDATE public.login_otps SET is_used = TRUE WHERE id = v_otp.id;
    RETURN jsonb_build_object('success', FALSE, 'error', 'max_attempts_exceeded');
  END IF;

  -- Código incorrecto
  IF v_otp.otp_hash != crypt(p_code, v_otp.otp_hash) THEN
    UPDATE public.login_otps SET attempts = attempts + 1 WHERE id = v_otp.id;
    RETURN jsonb_build_object(
      'success',       FALSE,
      'error',         'invalid_code',
      'attempts_left', v_otp.max_attempts - v_otp.attempts - 1
    );
  END IF;

  -- ✅ Código correcto — marcar como usado
  UPDATE public.login_otps
  SET is_used = TRUE, used_at = NOW()
  WHERE id = v_otp.id;

  RETURN jsonb_build_object('success', TRUE, 'user_id', v_otp.user_id);
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- ============================================================
-- 4. public.auth_audit_log
-- ============================================================
CREATE TABLE public.auth_audit_log (
  id         BIGSERIAL   PRIMARY KEY,
  user_id    UUID        REFERENCES public.users(id) ON DELETE SET NULL,
  event_type TEXT        NOT NULL CHECK (event_type IN (
               'login_success','login_failed','logout',
               'otp_requested','otp_verified','otp_failed',
               'password_reset','2fa_enabled','2fa_disabled',
               'session_revoked','account_locked','account_unlocked',
               'signup','profile_updated','role_assigned','role_removed'
             )),
  ip_address INET,
  user_agent TEXT,
  metadata   JSONB       DEFAULT '{}'::jsonb,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
  -- No deleted_at: el audit log NUNCA se borra
);

ALTER TABLE public.auth_audit_log ENABLE ROW LEVEL SECURITY;

-- Índices de audit
CREATE INDEX idx_audit_user_id   ON public.auth_audit_log(user_id, created_at DESC);
CREATE INDEX idx_audit_event     ON public.auth_audit_log(event_type, created_at DESC);
CREATE INDEX idx_audit_ip        ON public.auth_audit_log(ip_address) WHERE ip_address IS NOT NULL;

-- ============================================================
-- 5. RBAC — Roles, Permissions, user_roles
-- ============================================================

CREATE TABLE public.roles (
  id           UUID  PRIMARY KEY DEFAULT uuid_generate_v4(),
  name         TEXT  UNIQUE NOT NULL,          -- 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN', etc.
  display_name TEXT  NOT NULL,
  description  TEXT,
  is_system    BOOLEAN DEFAULT FALSE,          -- Los roles de sistema no se pueden borrar
  created_at   TIMESTAMPTZ DEFAULT NOW(),
  updated_at   TIMESTAMPTZ DEFAULT NOW(),
  deleted_at   TIMESTAMPTZ                     -- ✅ Soft delete
);

CREATE TABLE public.permissions (
  id          UUID  PRIMARY KEY DEFAULT uuid_generate_v4(),
  action      TEXT  NOT NULL CHECK (action IN ('create','read','update','delete','manage')),
  subject     TEXT  NOT NULL,                  -- 'User', 'Company', 'all', 'Report', etc.
  conditions  JSONB DEFAULT NULL,              -- Para permisos condicionales (CASL)
  description TEXT,
  created_at  TIMESTAMPTZ DEFAULT NOW(),
  updated_at  TIMESTAMPTZ DEFAULT NOW(),
  deleted_at  TIMESTAMPTZ,                     -- ✅ Soft delete
  UNIQUE (action, subject)
);

CREATE TABLE public.role_permissions (
  role_id       UUID REFERENCES public.roles(id)       ON DELETE CASCADE,
  permission_id UUID REFERENCES public.permissions(id) ON DELETE CASCADE,
  created_at    TIMESTAMPTZ DEFAULT NOW(),
  deleted_at    TIMESTAMPTZ,                            -- ✅ Soft delete
  PRIMARY KEY (role_id, permission_id)
);

CREATE TABLE public.user_roles (
  user_id    UUID REFERENCES public.users(id) ON DELETE CASCADE,
  role_id    UUID REFERENCES public.roles(id) ON DELETE CASCADE,
  assigned_by UUID REFERENCES public.users(id) ON DELETE SET NULL,  -- ✅ Trazabilidad
  created_at TIMESTAMPTZ DEFAULT NOW(),
  deleted_at TIMESTAMPTZ,                                            -- ✅ CRITICAL FIX: has_role() lo necesitaba
  PRIMARY KEY (user_id, role_id)
);

-- Índices RBAC
CREATE INDEX idx_user_roles_user   ON public.user_roles(user_id)  WHERE deleted_at IS NULL;
CREATE INDEX idx_user_roles_role   ON public.user_roles(role_id)  WHERE deleted_at IS NULL;
CREATE INDEX idx_role_perms_role   ON public.role_permissions(role_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_roles_name        ON public.roles(name)          WHERE deleted_at IS NULL;

-- ============================================================
-- 6. public.company_data
-- ============================================================
CREATE TABLE public.company_data (
  id             BIGSERIAL    NOT NULL,                          -- Interno, no exponer
  uuid           UUID         PRIMARY KEY DEFAULT uuid_generate_v4(),  -- ✅ UUID como PK de negocio
  company_name   TEXT         NOT NULL,
  tax_id         TEXT,                                           -- RFC / NIT / CIF / etc.
  email          TEXT,
  phone          TEXT,
  address        TEXT,
  city           TEXT,
  state          TEXT,
  country        TEXT,
  zip_code       TEXT,
  website        TEXT,
  logo_path      TEXT,
  is_active      BOOLEAN      NOT NULL DEFAULT TRUE,
  user_id        UUID         NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
  created_at     TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
  updated_at     TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
  deleted_at     TIMESTAMPTZ                                     -- ✅ Soft delete
);

ALTER TABLE public.company_data ENABLE ROW LEVEL SECURITY;

-- Índices de company
CREATE INDEX idx_company_user    ON public.company_data(user_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_company_active  ON public.company_data(is_active) WHERE deleted_at IS NULL;
CREATE INDEX idx_company_name    ON public.company_data(company_name) WHERE deleted_at IS NULL;

-- ============================================================
-- 7. TRIGGER: AUTO-CREATE USER ON AUTH SIGNUP
-- ============================================================
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER AS $$
BEGIN
  INSERT INTO public.users (id, email, name)
  VALUES (
    NEW.id,
    NEW.email,
    COALESCE(
      NEW.raw_user_meta_data->>'name',
      split_part(NEW.email, '@', 1)
    )
  )
  ON CONFLICT (id) DO NOTHING;  -- Seguridad ante reintentos
  RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

CREATE TRIGGER on_auth_user_created
  AFTER INSERT ON auth.users
  FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();

-- ============================================================
-- 8. TRIGGERS updated_at
-- ============================================================
CREATE TRIGGER tr_users_upd
  BEFORE UPDATE ON public.users
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE TRIGGER tr_company_upd
  BEFORE UPDATE ON public.company_data
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE TRIGGER tr_roles_upd
  BEFORE UPDATE ON public.roles
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE TRIGGER tr_permissions_upd
  BEFORE UPDATE ON public.permissions
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

-- ============================================================
-- 9. FUNCIONES RBAC
-- ============================================================

-- Verifica si el usuario autenticado tiene un rol específico
CREATE OR REPLACE FUNCTION public.has_role(p_role_name TEXT)
RETURNS BOOLEAN AS $$
  SELECT EXISTS (
    SELECT 1
    FROM public.user_roles ur
    JOIN public.roles r ON r.id = ur.role_id
    WHERE ur.user_id    = auth.uid()
      AND r.name        = p_role_name
      AND ur.deleted_at IS NULL
      AND r.deleted_at  IS NULL
  );
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Devuelve todos los roles del usuario actual
CREATE OR REPLACE FUNCTION public.get_my_roles()
RETURNS TEXT[] AS $$
  SELECT ARRAY_AGG(r.name)
  FROM public.user_roles ur
  JOIN public.roles r ON r.id = ur.role_id
  WHERE ur.user_id    = auth.uid()
    AND ur.deleted_at IS NULL
    AND r.deleted_at  IS NULL;
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- Verifica si el usuario tiene un permiso específico
CREATE OR REPLACE FUNCTION public.has_permission(p_action TEXT, p_subject TEXT)
RETURNS BOOLEAN AS $$
  SELECT EXISTS (
    SELECT 1
    FROM public.user_roles ur
    JOIN public.role_permissions rp ON rp.role_id = ur.role_id
    JOIN public.permissions p       ON p.id       = rp.permission_id
    WHERE ur.user_id    = auth.uid()
      AND ur.deleted_at IS NULL
      AND rp.deleted_at IS NULL
      AND p.deleted_at  IS NULL
      AND (
        (p.action = p_action AND p.subject = p_subject)
        OR (p.action = 'manage' AND p.subject = 'all')  -- Super admin
      )
  );
$$ LANGUAGE sql STABLE SECURITY DEFINER;

-- ============================================================
-- 10. RLS POLICIES
-- ============================================================

-- ── public.users ──────────────────────────────────────────
CREATE POLICY "users_select_own"
  ON public.users FOR SELECT
  USING (auth.uid() = id AND deleted_at IS NULL);

CREATE POLICY "users_update_own"
  ON public.users FOR UPDATE
  USING (auth.uid() = id AND deleted_at IS NULL);

CREATE POLICY "users_super_admin_all"
  ON public.users FOR ALL
  USING (public.has_role('ROLE_SUPER_ADMIN'));

-- ── public.user_sessions ──────────────────────────────────
CREATE POLICY "sessions_select_own"
  ON public.user_sessions FOR SELECT
  USING (auth.uid() = user_id AND deleted_at IS NULL);

CREATE POLICY "sessions_update_own"
  ON public.user_sessions FOR UPDATE
  USING (auth.uid() = user_id);

CREATE POLICY "sessions_super_admin_all"
  ON public.user_sessions FOR ALL
  USING (public.has_role('ROLE_SUPER_ADMIN'));

-- ── public.login_otps ─────────────────────────────────────
-- OTPs solo accesibles desde NestJS con service_role key.
-- No hay política para authenticated/anon (acceso denegado por defecto).
-- Si necesitas acceso desde cliente, descomenta:
-- CREATE POLICY "otps_select_own"
--   ON public.login_otps FOR SELECT
--   USING (auth.uid() = user_id AND deleted_at IS NULL);

-- ── public.auth_audit_log ─────────────────────────────────
CREATE POLICY "audit_select_own"
  ON public.auth_audit_log FOR SELECT
  USING (auth.uid() = user_id);

CREATE POLICY "audit_super_admin_all"
  ON public.auth_audit_log FOR ALL
  USING (public.has_role('ROLE_SUPER_ADMIN'));

-- ── public.company_data ───────────────────────────────────
CREATE POLICY "company_manage_own"
  ON public.company_data FOR ALL
  USING (
    (auth.uid() = user_id AND deleted_at IS NULL)
    OR public.has_role('ROLE_SUPER_ADMIN')
    OR public.has_role('ROLE_ADMIN')
  );

-- ── public.roles & permissions (solo admins) ──────────────
ALTER TABLE public.roles       ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.permissions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.role_permissions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.user_roles  ENABLE ROW LEVEL SECURITY;

CREATE POLICY "roles_read_authenticated"
  ON public.roles FOR SELECT
  USING (auth.role() = 'authenticated' AND deleted_at IS NULL);

CREATE POLICY "roles_admin_all"
  ON public.roles FOR ALL
  USING (public.has_role('ROLE_SUPER_ADMIN'));

CREATE POLICY "permissions_read_authenticated"
  ON public.permissions FOR SELECT
  USING (auth.role() = 'authenticated' AND deleted_at IS NULL);

CREATE POLICY "permissions_admin_all"
  ON public.permissions FOR ALL
  USING (public.has_role('ROLE_SUPER_ADMIN'));

CREATE POLICY "role_permissions_admin_all"
  ON public.role_permissions FOR ALL
  USING (public.has_role('ROLE_SUPER_ADMIN'));

CREATE POLICY "user_roles_select_own"
  ON public.user_roles FOR SELECT
  USING (auth.uid() = user_id AND deleted_at IS NULL);

CREATE POLICY "user_roles_admin_all"
  ON public.user_roles FOR ALL
  USING (public.has_role('ROLE_SUPER_ADMIN') OR public.has_role('ROLE_ADMIN'));

-- ============================================================
-- 11. CLEANUP AUTOMÁTICO con pg_cron
-- ============================================================

-- Limpiar sesiones expiradas o revocadas (cada día a las 3am UTC)
SELECT cron.schedule(
  'cleanup-expired-sessions',
  '0 3 * * *',
  $$
    UPDATE public.user_sessions
    SET deleted_at = NOW(), is_active = FALSE
    WHERE deleted_at IS NULL
      AND (expires_at < NOW() OR revoked_at IS NOT NULL);
  $$
);

-- Limpiar OTPs expirados (cada hora)
SELECT cron.schedule(
  'cleanup-expired-otps',
  '0 * * * *',
  $$
    UPDATE public.login_otps
    SET deleted_at = NOW()
    WHERE deleted_at IS NULL
      AND (expires_at < NOW() OR is_used = TRUE);
  $$
);

-- ============================================================
-- 12. SEED: Roles y Permisos base del sistema
-- ============================================================

-- Roles del sistema
INSERT INTO public.roles (name, display_name, description, is_system) VALUES
  ('ROLE_SUPER_ADMIN', 'Super Administrador', 'Acceso total al sistema', TRUE),
  ('ROLE_ADMIN',       'Administrador',       'Gestión de usuarios y empresa', TRUE),
  ('ROLE_MANAGER',     'Gerente',             'Gestión operativa', FALSE),
  ('ROLE_USER',        'Usuario',             'Acceso básico', TRUE)
ON CONFLICT (name) DO NOTHING;

-- Permisos base (acción + sujeto)
INSERT INTO public.permissions (action, subject, description) VALUES
  ('manage', 'all',         'Control total del sistema'),
  ('create', 'User',        'Crear usuarios'),
  ('read',   'User',        'Ver usuarios'),
  ('update', 'User',        'Editar usuarios'),
  ('delete', 'User',        'Eliminar usuarios'),
  ('create', 'Company',     'Crear empresa'),
  ('read',   'Company',     'Ver empresa'),
  ('update', 'Company',     'Editar empresa'),
  ('delete', 'Company',     'Eliminar empresa'),
  ('read',   'AuditLog',    'Ver logs de auditoría'),
  ('manage', 'Role',        'Gestionar roles y permisos')
ON CONFLICT (action, subject) DO NOTHING;

-- Asignar 'manage all' a SUPER_ADMIN
INSERT INTO public.role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM public.roles r, public.permissions p
WHERE r.name = 'ROLE_SUPER_ADMIN'
  AND p.action = 'manage' AND p.subject = 'all'
ON CONFLICT DO NOTHING;

-- Asignar permisos de gestión de usuarios y empresa a ADMIN
INSERT INTO public.role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM public.roles r
JOIN public.permissions p ON p.subject IN ('User','Company','AuditLog','Role')
WHERE r.name = 'ROLE_ADMIN'
ON CONFLICT DO NOTHING;

-- Asignar lectura básica a ROLE_USER
INSERT INTO public.role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM public.roles r
JOIN public.permissions p ON p.action = 'read' AND p.subject IN ('User','Company')
WHERE r.name = 'ROLE_USER'
ON CONFLICT DO NOTHING;

-- ============================================================
-- FIN DEL SCHEMA
-- ============================================================
-- Notas de uso desde NestJS:
--
--  • Usa SIEMPRE la service_role key en el backend (NestJS).
--    NUNCA expongas service_role en el cliente.
--
--  • Para generar OTP:
--    SELECT public.generate_otp('user@email.com', 'email', 'login', '<user_uuid>');
--
--  • Para verificar OTP:
--    SELECT public.verify_otp('user@email.com', '123456', 'login');
--    Devuelve: { "success": true, "user_id": "uuid" }
--                o { "success": false, "error": "invalid_code", "attempts_left": 3 }
--
--  • Para verificar roles en NestJS Guard:
--    SELECT public.has_role('ROLE_ADMIN');
--    SELECT public.has_permission('read', 'Company');
--    SELECT public.get_my_roles();
-- ============================================================
