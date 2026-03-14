import * as React from 'react';
import Signature, { type SignatureCanvasRef } from '@uiw/react-signature/canvas';

interface CompanySignaturePadProps {
  value: string | null;
  onChange: (nextValue: string | null) => void;
  disabled?: boolean;
}

export default function CompanySignaturePad({
  value,
  onChange,
  disabled = false,
}: CompanySignaturePadProps): React.JSX.Element {
  const signatureRef = React.useRef<SignatureCanvasRef | null>(null);

  function handleCaptureSignature(): void {
    if (disabled) {
      return;
    }

    const canvas = signatureRef.current?.canvas;

    if (!canvas) {
      return;
    }

    const dataUrl = canvas.toDataURL('image/png');
    onChange(dataUrl);
  }

  function handleClearSignature(): void {
    if (disabled) {
      return;
    }

    signatureRef.current?.clear();
    onChange(null);
  }

  return (
    <section className="card p-4">
      <div className="mb-3 flex items-center justify-between gap-2">
        <h3 className="text-sm font-semibold" style={{ color: 'var(--text-primary)' }}>
          Signature
        </h3>
        <div className="flex items-center gap-2">
          <button
            type="button"
            onClick={handleCaptureSignature}
            disabled={disabled}
            className="btn-primary px-3 py-1.5 text-xs font-semibold disabled:opacity-50"
          >
            Save Draw
          </button>
          <button
            type="button"
            onClick={handleClearSignature}
            disabled={disabled}
            className="btn-ghost px-3 py-1.5 text-xs font-semibold disabled:opacity-50"
          >
            Clear
          </button>
        </div>
      </div>

      <div
        className="overflow-hidden rounded-lg"
        style={{
          border: '1px dashed var(--border-strong)',
          background: 'var(--bg-elevated)',
        }}
      >
        <Signature
          ref={signatureRef}
          width={760}
          height={220}
          readonly={disabled}
          style={{ width: '100%', height: '220px', display: 'block' }}
        />
      </div>

      {value && (
        <div className="mt-3">
          <p className="mb-2 text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-secondary)' }}>
            Preview
          </p>
          <img
            src={value}
            alt="Signature preview"
            className="h-20 w-full rounded-md object-contain"
            style={{
              border: '1px solid var(--border-default)',
              background: 'var(--color-white)',
            }}
          />
        </div>
      )}
    </section>
  );
}
