import * as React from 'react';
import { Save, X } from 'lucide-react';
import type { ZoneFormData, ZoneType } from '@/modules/zones/types';
import { ZONE_TYPE_LABELS } from '@/modules/zones/types';

interface ZoneFormProps {
    initialData?: Partial<ZoneFormData>;
    onSubmit: (data: ZoneFormData) => Promise<void>;
    isSubmitting: boolean;
    onCancel: () => void;
}

const ZONE_TYPES: ZoneType[] = [
    'interior',
    'exterior',
    'basement',
    'attic',
    'garage',
    'crawlspace',
];

const inputStyle: React.CSSProperties = {
    width: '100%',
    height: 'var(--input-height)',
    background: 'var(--input-bg)',
    border: '1px solid var(--input-border)',
    borderRadius: 'var(--input-radius)',
    color: 'var(--input-text)',
    fontFamily: 'var(--font-sans)',
    fontSize: 'var(--input-font-size)',
    padding: '0 var(--input-padding-x)',
    outline: 'none',
    transition: 'border-color var(--transition)',
};

const labelStyle: React.CSSProperties = {
    display: 'block',
    fontSize: '11px',
    fontWeight: 600,
    letterSpacing: '1.5px',
    textTransform: 'uppercase',
    color: 'var(--text-secondary)',
    marginBottom: '6px',
    fontFamily: 'var(--font-sans)',
};

export default function ZoneForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: ZoneFormProps): React.JSX.Element {
    const [form, setForm] = React.useState<ZoneFormData>({
        zone_name:   initialData?.zone_name   ?? '',
        zone_type:   initialData?.zone_type   ?? 'interior',
        code:        initialData?.code        ?? '',
        description: initialData?.description ?? '',
        user_id:     initialData?.user_id     ?? 0,
    });

    const [errors, setErrors] = React.useState<Partial<Record<keyof ZoneFormData, string>>>({});

    function validate(): boolean {
        const next: Partial<Record<keyof ZoneFormData, string>> = {};

        if (!form.zone_name.trim()) {
            next.zone_name = 'Zone name is required.';
        }
        if (!form.zone_type) {
            next.zone_type = 'Zone type is required.';
        }

        setErrors(next);
        return Object.keys(next).length === 0;
    }

    async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
        event.preventDefault();

        if (!validate()) return;

        await onSubmit(form);
    }

    function handleFocus(event: React.FocusEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>): void {
        event.currentTarget.style.borderColor = 'var(--input-border-focus)';
        event.currentTarget.style.boxShadow = '0 0 0 2px color-mix(in srgb, var(--accent-primary) 20%, transparent)';
    }

    function handleBlur(event: React.FocusEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>): void {
        event.currentTarget.style.borderColor = 'var(--input-border)';
        event.currentTarget.style.boxShadow = 'none';
    }

    return (
        <form onSubmit={(e) => { void handleSubmit(e); }} noValidate>
            <div className="grid gap-6 p-6 md:grid-cols-2">

                {/* Zone Name */}
                <div className="md:col-span-2">
                    <label htmlFor="zone_name" style={labelStyle}>
                        Zone Name <span style={{ color: 'var(--accent-error)' }}>*</span>
                    </label>
                    <input
                        id="zone_name"
                        type="text"
                        value={form.zone_name}
                        onChange={(e) => {
                            setForm((prev) => ({ ...prev, zone_name: e.target.value }));
                            if (errors.zone_name) setErrors((prev) => ({ ...prev, zone_name: undefined }));
                        }}
                        onFocus={handleFocus}
                        onBlur={handleBlur}
                        placeholder="e.g. Main Living Area"
                        style={{
                            ...inputStyle,
                            ...(errors.zone_name ? { borderColor: 'var(--input-border-error)' } : {}),
                        }}
                        aria-describedby={errors.zone_name ? 'zone_name_error' : undefined}
                        aria-invalid={Boolean(errors.zone_name)}
                    />
                    {errors.zone_name ? (
                        <p id="zone_name_error" className="mt-1 text-xs" style={{ color: 'var(--accent-error)' }}>
                            {errors.zone_name}
                        </p>
                    ) : null}
                </div>

                {/* Zone Type */}
                <div>
                    <label htmlFor="zone_type" style={labelStyle}>
                        Zone Type <span style={{ color: 'var(--accent-error)' }}>*</span>
                    </label>
                    <select
                        id="zone_type"
                        value={form.zone_type}
                        onChange={(e) => {
                            setForm((prev) => ({ ...prev, zone_type: e.target.value as ZoneType }));
                            if (errors.zone_type) setErrors((prev) => ({ ...prev, zone_type: undefined }));
                        }}
                        onFocus={handleFocus}
                        onBlur={handleBlur}
                        style={{
                            ...inputStyle,
                            colorScheme: 'dark',
                            ...(errors.zone_type ? { borderColor: 'var(--input-border-error)' } : {}),
                        }}
                        aria-describedby={errors.zone_type ? 'zone_type_error' : undefined}
                        aria-invalid={Boolean(errors.zone_type)}
                    >
                        {ZONE_TYPES.map((type) => (
                            <option key={type} value={type}>
                                {ZONE_TYPE_LABELS[type]}
                            </option>
                        ))}
                    </select>
                    {errors.zone_type ? (
                        <p id="zone_type_error" className="mt-1 text-xs" style={{ color: 'var(--accent-error)' }}>
                            {errors.zone_type}
                        </p>
                    ) : null}
                </div>

                {/* Code */}
                <div>
                    <label htmlFor="code" style={labelStyle}>
                        Code <span style={{ color: 'var(--text-muted)', fontWeight: 400, textTransform: 'none' }}>(optional)</span>
                    </label>
                    <input
                        id="code"
                        type="text"
                        value={form.code}
                        onChange={(e) => setForm((prev) => ({ ...prev, code: e.target.value }))}
                        onFocus={handleFocus}
                        onBlur={handleBlur}
                        placeholder="e.g. Z-001"
                        style={inputStyle}
                    />
                </div>

                {/* Description */}
                <div className="md:col-span-2">
                    <label htmlFor="description" style={labelStyle}>
                        Description <span style={{ color: 'var(--text-muted)', fontWeight: 400, textTransform: 'none' }}>(optional)</span>
                    </label>
                    <textarea
                        id="description"
                        rows={3}
                        value={form.description}
                        onChange={(e) => setForm((prev) => ({ ...prev, description: e.target.value }))}
                        onFocus={handleFocus}
                        onBlur={handleBlur}
                        placeholder="Brief description of this zone..."
                        style={{
                            ...inputStyle,
                            height: 'auto',
                            padding: '10px var(--input-padding-x)',
                            resize: 'vertical',
                        }}
                    />
                </div>
            </div>

            {/* Footer actions */}
            <div
                className="flex items-center justify-end gap-3 px-6 py-4"
                style={{ borderTop: '1px solid var(--border-subtle)' }}
            >
                <button
                    type="button"
                    onClick={onCancel}
                    disabled={isSubmitting}
                    className="btn-ghost inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold disabled:opacity-50"
                >
                    <X size={15} />
                    Cancel
                </button>
                <button
                    type="submit"
                    disabled={isSubmitting}
                    className="btn-primary inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold disabled:opacity-60"
                >
                    {isSubmitting ? (
                        <>
                            <span
                                className="h-4 w-4 animate-spin rounded-full border-2 border-t-transparent"
                                style={{ borderColor: 'currentColor', borderTopColor: 'transparent' }}
                            />
                            Saving…
                        </>
                    ) : (
                        <>
                            <Save size={15} />
                            Save zone
                        </>
                    )}
                </button>
            </div>
        </form>
    );
}
