import * as React from 'react';
import { motion } from 'framer-motion';
import { Check, MapPin, Users, FileText, Building2, Layers, ClipboardCheck } from 'lucide-react';
import type { WizardStep } from '@/modules/claims/stores/claimWizardStore';

interface WizardStepConfig {
    step: WizardStep;
    label: string;
    sublabel: string;
    icon: React.ReactNode;
}

const STEPS: WizardStepConfig[] = [
    { step: 1, label: 'Property',   sublabel: 'Location & Map',     icon: <MapPin size={15} /> },
    { step: 2, label: 'Customers',  sublabel: 'Owner & Co-owner',   icon: <Users size={15} /> },
    { step: 3, label: 'Claim Info', sublabel: 'Policy & Dates',     icon: <FileText size={15} /> },
    { step: 4, label: 'Companies',  sublabel: 'Insurance & Public', icon: <Building2 size={15} /> },
    { step: 5, label: 'Additional', sublabel: 'Damages & Scope',    icon: <Layers size={15} /> },
    { step: 6, label: 'Review',     sublabel: 'Confirm & Submit',   icon: <ClipboardCheck size={15} /> },
];

interface WizardStepperProps {
    currentStep: WizardStep;
    onStepClick?: (step: WizardStep) => void;
}

export function WizardStepper({ currentStep, onStepClick }: WizardStepperProps): React.JSX.Element {
    return (
        <div
            role="navigation"
            aria-label="Claim wizard steps"
            style={{
                display: 'flex',
                alignItems: 'center',
                width: '100%',
                gap: 0,
                padding: '0 4px',
                overflowX: 'auto',
            }}
        >
            {STEPS.map((s, idx) => {
                const isDone    = s.step < currentStep;
                const isActive  = s.step === currentStep;
                const isPending = s.step > currentStep;
                const isLast    = idx === STEPS.length - 1;
                const canClick  = onStepClick !== undefined && (isDone || isActive);

                return (
                    <React.Fragment key={s.step}>
                        {/* ── Step item ── */}
                        <div
                            style={{
                                display: 'flex',
                                flexDirection: 'column',
                                alignItems: 'center',
                                gap: 10,
                                flex: '0 0 auto',
                                position: 'relative',
                            }}
                        >
                            {/* Bubble */}
                            <motion.button
                                type="button"
                                onClick={canClick ? () => onStepClick(s.step) : undefined}
                                disabled={!canClick}
                                aria-label={`Step ${s.step}: ${s.label}`}
                                aria-current={isActive ? 'step' : undefined}
                                whileHover={canClick ? { scale: 1.1, y: -1 } : undefined}
                                whileTap={canClick ? { scale: 0.93 } : undefined}
                                transition={{ duration: 0.15, ease: 'easeOut' }}
                                style={{
                                    width: isActive ? 52 : 42,
                                    height: isActive ? 52 : 42,
                                    borderRadius: '50%',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    border: `2px solid ${
                                        isDone    ? 'var(--accent-success)'                                    :
                                        isActive  ? 'var(--accent-primary)'                                   :
                                                    'color-mix(in srgb, var(--border-default) 60%, transparent)'
                                    }`,
                                    background: isDone
                                        ? 'color-mix(in srgb, var(--accent-success) 20%, var(--bg-card))'
                                        : isActive
                                        ? 'color-mix(in srgb, var(--accent-primary) 22%, var(--bg-card))'
                                        : 'var(--bg-card)',
                                    color: isDone    ? 'var(--accent-success)'  :
                                           isActive  ? 'var(--accent-primary)'  :
                                                       'var(--text-disabled)',
                                    cursor: canClick ? 'pointer' : 'default',
                                    boxShadow: isActive
                                        ? `0 0 0 5px color-mix(in srgb, var(--accent-primary) 18%, transparent),
                                           0 4px 18px color-mix(in srgb, var(--accent-primary) 35%, transparent)`
                                        : isDone
                                        ? `0 0 0 3px color-mix(in srgb, var(--accent-success) 15%, transparent)`
                                        : 'none',
                                    transition: 'all 0.28s cubic-bezier(0.34, 1.56, 0.64, 1)',
                                    flexShrink: 0,
                                    outline: 'none',
                                    position: 'relative',
                                    zIndex: 1,
                                }}
                            >
                                {isDone ? (
                                    <motion.span
                                        initial={{ scale: 0, rotate: -30, opacity: 0 }}
                                        animate={{ scale: 1, rotate: 0, opacity: 1 }}
                                        transition={{ duration: 0.3, type: 'spring', stiffness: 400, damping: 18 }}
                                        style={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                                    >
                                        <Check size={17} strokeWidth={2.8} />
                                    </motion.span>
                                ) : isActive ? (
                                    <motion.span
                                        initial={{ scale: 0.6, opacity: 0 }}
                                        animate={{ scale: 1, opacity: 1 }}
                                        transition={{ duration: 0.22, ease: 'backOut' }}
                                        style={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                                    >
                                        {s.icon}
                                    </motion.span>
                                ) : (
                                    <span style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', opacity: 0.45 }}>
                                        {s.icon}
                                    </span>
                                )}

                                {/* Active pulse ring */}
                                {isActive && (
                                    <motion.span
                                        initial={{ scale: 0.8, opacity: 0.6 }}
                                        animate={{ scale: 1.7, opacity: 0 }}
                                        transition={{ duration: 1.4, repeat: Infinity, ease: 'easeOut' }}
                                        style={{
                                            position: 'absolute',
                                            inset: 0,
                                            borderRadius: '50%',
                                            border: '2px solid var(--accent-primary)',
                                            pointerEvents: 'none',
                                        }}
                                    />
                                )}
                            </motion.button>

                            {/* Labels */}
                            <div style={{ textAlign: 'center', minWidth: 72 }}>
                                {/* Step number pill for active */}
                                {isActive && (
                                    <motion.div
                                        initial={{ opacity: 0, y: -4 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        transition={{ duration: 0.2 }}
                                        style={{
                                            display: 'inline-flex',
                                            alignItems: 'center',
                                            gap: 4,
                                            marginBottom: 4,
                                            padding: '1px 7px',
                                            borderRadius: 999,
                                            background: 'color-mix(in srgb, var(--accent-primary) 18%, var(--bg-card))',
                                            border: '1px solid color-mix(in srgb, var(--accent-primary) 35%, transparent)',
                                            fontSize: 9,
                                            fontWeight: 700,
                                            letterSpacing: '0.08em',
                                            textTransform: 'uppercase' as const,
                                            color: 'var(--accent-primary)',
                                            fontFamily: 'var(--font-sans)',
                                        }}
                                    >
                                        Step {s.step}
                                    </motion.div>
                                )}

                                <p
                                    style={{
                                        fontSize: isActive ? 13 : 11,
                                        fontWeight: isActive ? 700 : isDone ? 600 : 400,
                                        color: isActive  ? 'var(--text-primary)'   :
                                               isDone    ? 'var(--accent-success)'  :
                                                           'var(--text-disabled)',
                                        fontFamily: 'var(--font-sans)',
                                        margin: 0,
                                        lineHeight: '1.3',
                                        whiteSpace: 'nowrap',
                                        transition: 'all 0.25s ease',
                                    }}
                                >
                                    {s.label}
                                </p>
                                <p
                                    style={{
                                        fontSize: 10,
                                        color: isActive ? 'var(--text-muted)' : isDone ? 'color-mix(in srgb, var(--accent-success) 70%, var(--text-muted))' : 'var(--text-disabled)',
                                        fontFamily: 'var(--font-sans)',
                                        margin: '2px 0 0',
                                        lineHeight: '1.3',
                                        whiteSpace: 'nowrap',
                                        opacity: isPending ? 0.5 : 1,
                                        transition: 'all 0.25s ease',
                                    }}
                                >
                                    {isDone ? '✓ Done' : s.sublabel}
                                </p>
                            </div>
                        </div>

                        {/* ── Connector ── */}
                        {!isLast && (
                            <div
                                style={{
                                    flex: '1 1 auto',
                                    minWidth: 20,
                                    height: 2,
                                    marginBottom: 38,
                                    borderRadius: 2,
                                    background: isDone
                                        ? 'linear-gradient(90deg, var(--accent-success), color-mix(in srgb, var(--accent-success) 60%, transparent))'
                                        : isActive
                                        ? 'linear-gradient(90deg, color-mix(in srgb, var(--accent-primary) 40%, transparent), color-mix(in srgb, var(--border-default) 30%, transparent))'
                                        : 'color-mix(in srgb, var(--border-default) 30%, transparent)',
                                    position: 'relative',
                                    overflow: 'hidden',
                                    transition: 'background 0.4s ease',
                                }}
                            >
                                {isDone && (
                                    <motion.div
                                        initial={{ scaleX: 0 }}
                                        animate={{ scaleX: 1 }}
                                        transition={{ duration: 0.35, ease: 'easeOut' }}
                                        style={{
                                            position: 'absolute',
                                            inset: 0,
                                            background: 'linear-gradient(90deg, var(--accent-success), color-mix(in srgb, var(--accent-success) 70%, var(--accent-primary)))',
                                            transformOrigin: 'left',
                                        }}
                                    />
                                )}
                            </div>
                        )}
                    </React.Fragment>
                );
            })}
        </div>
    );
}
