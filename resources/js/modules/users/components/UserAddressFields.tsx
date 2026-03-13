import * as React from 'react';
import { cn } from '@/lib/utils';
import { useGoogleMapsAddressAutocomplete } from '@/modules/users/hooks/useGoogleMapsAddressAutocomplete';

interface AddressFormValue {
  address?: string;
  address_2?: string;
  city?: string;
  state?: string;
  country?: string;
  zip_code?: string;
}

interface UserAddressFieldsProps<TForm extends AddressFormValue> {
  form: TForm;
  errors: Partial<Record<keyof TForm | 'address' | 'address_2' | 'city' | 'state' | 'country' | 'zip_code', string | undefined>>;
  onChange: (event: React.ChangeEvent<HTMLInputElement>) => void;
  onAddressAutofill: (value: Pick<AddressFormValue, 'address' | 'city' | 'state' | 'country' | 'zip_code'>) => void;
  variant?: 'premium' | 'compact';
}

interface AddressInputProps {
  label: string;
  name: 'address' | 'address_2' | 'city' | 'state' | 'country' | 'zip_code';
  value: string;
  onChange: (event: React.ChangeEvent<HTMLInputElement>) => void;
  error?: string;
  readOnly?: boolean;
  inputRef?: React.RefObject<HTMLInputElement | null>;
  placeholder?: string;
  variant: 'premium' | 'compact';
}

function AddressInput({
  label,
  name,
  value,
  onChange,
  error,
  readOnly = false,
  inputRef,
  placeholder,
  variant,
}: AddressInputProps): React.JSX.Element {
  const inputClasses = cn(
    'w-full text-sm outline-none transition-all',
    variant === 'premium'
      ? 'rounded-xl px-4 py-3 bg-(--bg-card) border border-(--border-default) shadow-sm placeholder:text-(--text-disabled) text-(--text-primary) hover:border-(--accent-primary)'
      : 'rounded-lg px-3 py-2.5 border',
    variant === 'premium'
      ? 'focus:ring-2 focus:ring-(--accent-primary) focus:ring-offset-2 focus:ring-offset-(--bg-surface)'
      : 'focus:ring-2 focus:ring-(--accent-primary)',
    readOnly ? 'cursor-default bg-(--bg-surface) text-(--text-secondary)' : '',
    error ? 'border-(--accent-error)' : '',
  );

  const labelClasses = cn(
    variant === 'premium'
      ? 'text-[11px] font-bold uppercase tracking-widest text-(--text-muted)'
      : 'block text-[12px] font-semibold uppercase tracking-wider',
  );

  return (
    <div className={variant === 'premium' ? 'flex flex-col gap-2' : 'space-y-1.5'}>
      <label htmlFor={name} className={labelClasses} style={variant === 'compact' ? { color: 'var(--text-secondary)' } : undefined}>
        {label}
      </label>
      <input
        ref={inputRef}
        id={name}
        name={name}
        value={value}
        onChange={onChange}
        readOnly={readOnly}
        placeholder={placeholder}
        className={inputClasses}
        style={variant === 'compact'
          ? {
              background: readOnly ? 'var(--bg-surface)' : 'var(--bg-surface)',
              borderColor: error ? 'var(--accent-error)' : 'var(--border-default)',
              color: readOnly ? 'var(--text-secondary)' : 'var(--text-primary)',
              fontFamily: 'var(--font-sans)',
            }
          : { fontFamily: 'var(--font-sans)' }}
      />
      {error ? (
        <span
          className={variant === 'premium' ? 'text-[11px] font-medium text-(--accent-error)' : 'text-[11px]'}
          style={variant === 'compact' ? { color: 'var(--accent-error)' } : undefined}
        >
          {error}
        </span>
      ) : null}
    </div>
  );
}

export function UserAddressFields<TForm extends AddressFormValue>({
  form,
  errors,
  onChange,
  onAddressAutofill,
  variant = 'premium',
}: UserAddressFieldsProps<TForm>): React.JSX.Element {
  const addressInputRef = React.useRef<HTMLInputElement | null>(null);
  const address2InputRef = React.useRef<HTMLInputElement | null>(null);

  const handleAddressSelected = React.useCallback((value: Pick<AddressFormValue, 'address' | 'city' | 'state' | 'country' | 'zip_code'>): void => {
    onAddressAutofill(value);
    window.setTimeout(() => {
      address2InputRef.current?.focus();
    }, 0);
  }, [onAddressAutofill]);

  const { isLoading, isReady, errorMessage } = useGoogleMapsAddressAutocomplete({
    inputRef: addressInputRef,
    onAddressSelected: handleAddressSelected,
  });

  return (
    <div className="grid gap-4 sm:grid-cols-2">
      <div className="sm:col-span-2">
        <AddressInput
          label="Address"
          name="address"
          value={form.address ?? ''}
          onChange={onChange}
          error={errors.address}
          inputRef={addressInputRef}
          placeholder="Start typing a USA address"
          variant={variant}
        />
      </div>

      <div className="sm:col-span-2">
        <AddressInput
          label="Address 2"
          name="address_2"
          value={form.address_2 ?? ''}
          onChange={onChange}
          error={errors.address_2}
          inputRef={address2InputRef}
          placeholder="Apartment, suite, unit, building, floor"
          variant={variant}
        />
      </div>

      <AddressInput
        label="City"
        name="city"
        value={form.city ?? ''}
        onChange={onChange}
        error={errors.city}
        readOnly
        variant={variant}
      />

      <AddressInput
        label="State"
        name="state"
        value={form.state ?? ''}
        onChange={onChange}
        error={errors.state}
        readOnly
        variant={variant}
      />

      <AddressInput
        label="Country"
        name="country"
        value={form.country ?? ''}
        onChange={onChange}
        error={errors.country}
        readOnly
        variant={variant}
      />

      <AddressInput
        label="Zip Code"
        name="zip_code"
        value={form.zip_code ?? ''}
        onChange={onChange}
        error={errors.zip_code}
        readOnly
        variant={variant}
      />

      <div className="sm:col-span-2">
        <p className="text-xs" style={{ color: 'var(--text-muted)' }}>
          {errorMessage ?? (isReady ? 'Autocomplete limited to USA addresses.' : isLoading ? 'Loading Google Maps autocomplete...' : 'Google Maps autocomplete is preparing...')}
        </p>
      </div>
    </div>
  );
}
