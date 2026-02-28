-- ============================================================
-- TABLAS: appointments + contact_support
-- Archivo independiente — no requiere FK a public.users
-- Ejecutar DESPUÉS del schema principal (schema_fixed_v2.sql)
-- Requiere que exista public.set_updated_at() y public.has_role()
-- ============================================================


-- ============================================================
-- public.appointments
-- Basado en la migración Laravel original
-- ============================================================
CREATE TABLE public.appointments (
  id                 BIGSERIAL    PRIMARY KEY,
  uuid               TEXT         NOT NULL UNIQUE,
  first_name         TEXT         NOT NULL,
  last_name          TEXT         NOT NULL,
  phone              TEXT         NOT NULL,
  email              TEXT,
  address            TEXT         NOT NULL,
  address_2          TEXT,
  city               TEXT         NOT NULL,
  state              TEXT         NOT NULL,
  zipcode            TEXT         NOT NULL,
  country            TEXT         NOT NULL,
  insurance_property BOOLEAN      NOT NULL DEFAULT FALSE,
  message            TEXT,
  sms_consent        BOOLEAN      NOT NULL DEFAULT FALSE,
  registration_date  TIMESTAMPTZ,
  inspection_date    DATE,
  inspection_time    TIME,
  inspection_status  TEXT         CHECK (inspection_status IN ('Confirmed','Completed','Pending','Declined')),
  status_lead        TEXT         CHECK (status_lead       IN ('New','Called','Pending','Declined')),
  lead_source        TEXT         CHECK (lead_source       IN ('Website','Facebook Ads','Reference','Retell AI')),
  follow_up_calls    JSONB,       -- array JSON con intentos de llamada y detalles
  notes              TEXT,
  owner              TEXT,
  damage_detail      TEXT,
  intent_to_claim    BOOLEAN,
  follow_up_date     DATE,
  additional_note    TEXT,
  latitude           NUMERIC(10,7),
  longitude          NUMERIC(10,7),
  created_at         TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
  updated_at         TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
  deleted_at         TIMESTAMPTZ                           -- soft delete
);

-- Índices
CREATE INDEX idx_appointments_uuid              ON public.appointments(uuid);
CREATE INDEX idx_appointments_email             ON public.appointments(email);
CREATE INDEX idx_appointments_phone             ON public.appointments(phone);
CREATE INDEX idx_appointments_inspection_date   ON public.appointments(inspection_date);
CREATE INDEX idx_appointments_inspection_status ON public.appointments(inspection_status);
CREATE INDEX idx_appointments_status_lead       ON public.appointments(status_lead);
CREATE INDEX idx_appointments_lead_source       ON public.appointments(lead_source);
CREATE INDEX idx_appointments_follow_up_date    ON public.appointments(follow_up_date);
CREATE INDEX idx_appointments_active            ON public.appointments(deleted_at) WHERE deleted_at IS NULL;

-- Trigger updated_at
CREATE TRIGGER appointments_updated_at
  BEFORE UPDATE ON public.appointments
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

-- RLS
ALTER TABLE public.appointments ENABLE ROW LEVEL SECURITY;

-- Cualquiera puede crear un appointment (formulario web público)
-- Descomenta si tu formulario es público (sin auth):
-- CREATE POLICY "Anyone can create appointments"
--   ON public.appointments FOR INSERT
--   WITH CHECK (true);

-- Solo admins pueden leer y gestionar
CREATE POLICY "Admins can manage appointments"
  ON public.appointments FOR ALL
  USING (public.has_role('ROLE_SUPER_ADMIN') OR public.has_role('ROLE_ADMIN'));


-- ============================================================
-- public.contact_support
-- ============================================================
CREATE TABLE public.contact_support (
  id          BIGSERIAL   PRIMARY KEY,
  uuid        UUID        NOT NULL DEFAULT uuid_generate_v4() UNIQUE,
  first_name  TEXT        NOT NULL,
  last_name   TEXT        NOT NULL,
  email       TEXT        NOT NULL,
  phone       TEXT,
  subject     TEXT        NOT NULL,
  message     TEXT        NOT NULL,
  status      TEXT        NOT NULL DEFAULT 'open'
                CHECK (status   IN ('open','in_progress','resolved','closed')),
  priority    TEXT        NOT NULL DEFAULT 'medium'
                CHECK (priority IN ('low','medium','high','urgent')),
  assigned_to TEXT,
  resolved_at TIMESTAMPTZ,
  ip_address  INET,
  metadata    JSONB       DEFAULT '{}'::jsonb,
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  deleted_at  TIMESTAMPTZ
);

-- Índices
CREATE INDEX idx_contact_support_email    ON public.contact_support(email);
CREATE INDEX idx_contact_support_status   ON public.contact_support(status);
CREATE INDEX idx_contact_support_priority ON public.contact_support(priority);
CREATE INDEX idx_contact_support_active   ON public.contact_support(deleted_at) WHERE deleted_at IS NULL;

-- Trigger updated_at
CREATE TRIGGER contact_support_updated_at
  BEFORE UPDATE ON public.contact_support
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

-- RLS
ALTER TABLE public.contact_support ENABLE ROW LEVEL SECURITY;

-- Cualquiera puede crear un ticket (formulario de contacto público)
CREATE POLICY "Anyone can create contact support tickets"
  ON public.contact_support FOR INSERT
  WITH CHECK (true);

-- Solo admins pueden leer y gestionar tickets
CREATE POLICY "Admins can manage contact support"
  ON public.contact_support FOR ALL
  USING (public.has_role('ROLE_SUPER_ADMIN') OR public.has_role('ROLE_ADMIN'));


-- ============================================================
-- VERIFICACIÓN
-- ============================================================
SELECT 'appointments'   AS tabla, COUNT(*) AS total
FROM public.appointments  WHERE deleted_at IS NULL
UNION ALL
SELECT 'contact_support' AS tabla, COUNT(*) AS total
FROM public.contact_support WHERE deleted_at IS NULL;
