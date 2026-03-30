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
    { step: 1, label: 'Property',   sublabel: 'Location & Map',       icon: <MapPin size={16} /> },
    { step: 2, label: 'Customers',  sublabel: 'Owner & Co-owner',     icon: <Users size={16} /> },
    { step: 3, label: 'Claim Info', sublabel: 'Policy & Dates',       icon: <FileText size={16} /> },
    { step: 4, label: 'Companies',  sublabel: 'Insurance & Public',   icon: <Building2 size={16} /> },
    { step: 5, label: 'Additional', sublabel: 'Damages & Scope',      icon: <Layers size={16} /> },
    { step: 6, label: 'Review',     sublabel: 'Confirm & Submit',     icon: <ClipboardCheck size={16} /> },
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
                alignItems: 'flex-start',
                justifyContent: 'center',
                gap: 0,
                padding: '0 8px',
                width: '100%',
                overflowX: 'auto',
            }}
        >
            {STEPS.map((s, idx) => {
                const isDone    = s.step < currentStep;
                const isActive  = s.step === currentStep;
                const isPending = s.step > currentStep;
                const isLast    = idx === STEPS.length - 1;
                const canClick  = (onStepClick !== undefined) && (isDone || isActive);

                return (
                    <React.Fragment key={s.step}>
                        {/* Step node */}
                        <div
                            style={{
                                display: 'flex',
                                flexDirection: 'column',
                                alignItems: 'center',
                                gap: 8,
                                minWidth: 80,
                                flex: '0 0 auto',
                            }}
                        >
                            <motion.button
                                type="button"
                                onClick={canClick ? () => onStepClick(s.step) : undefined}
                                disabled={!canClick}
                                aria-label={`Step ${s.step}: ${s.label}`}
                                aria-current={isActive ? 'step' : undefined}
                                whileHover={canClick ? { scale: 1.08 } : undefined}
                                whileTap={canClick ? { scale: 0.96 } : undefined}
                                transition={{ duration: 0.15 }}
                                style={{
                                    width: 44,
                                    height: 44,
                                    borderRadius: '50%',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    border: `2px solid ${
                                        isDone    ? 'var(--wizard-step-done)'    :
                                        isActive  ? 'var(--wizard-step-active)'  :
                                                    'var(--wizard-step-pending)'
                                    }`,
                                    background: isDone    ? 'var(--wizard-step-bg-done)'    :
                                                isActive  ? 'var(--wizard-step-bg-active)'  :
                                                            'var(--wizard-step-bg-pending)',
                                    color: isDone    ? 'var(--wizard-step-done)'   :
                                           isActive  ? 'var(--wizard-step-active)' :
                                                       'var(--text-muted)',
                                    cursor: canClick ? 'pointer' : 'default',
                                    boxShadow: isActive
                                        ? '0 0 0 4px color-mix(in srgb, var(--accent-primary) 20%, transparent)'
                                        : 'none',
                                    transition: 'all 0.25s ease',
                                    flexShrink: 0,
                                }}
                            >
                                {isDone ? (
                                    <motion.span
                                        initial={{ scale: 0, opacity: 0 }}
                                        animate={{ scale: 1, opacity: 1 }}
                                        transition={{ duration: 0.25, type: 'spring', stiffness: 300 }}
                                    >
                                        <Check size={18} strokeWidth={2.5} />
                                    </motion.span>
                                ) : (
                                    <span>{s.icon}</span>
                                )}
                            </motion.button>

                            <div style={{ textAlign: 'center' }}>
                                <p
                                    style={{
                                        fontSize: 12,
                                        fontWeight: isActive ? 700 : 500,
                                        color: isDone    ? 'var(--accent-success)'  :
                                               isActive  ? 'var(--text-primary)'    :
                                                           isPending ? 'var(--text-muted)' : 'var(--text-muted)',
                                        fontFamily: 'var(--font-sans)',
                                        transition: 'color 0.2s ease',
                                        margin: 0,
                                        lineHeight: '1.3',
                                        whiteSpace: 'nowrap',
                                    }}
                                >
                                    {s.label}
                                </p>
                                <p
                                    style={{
                                        fontSize: 10,
                                        color: 'var(--text-muted)',
                                        fontFamily: 'var(--font-sans)',
                                        margin: 0,
                                        lineHeight: '1.3',
                                        whiteSpace: 'nowrap',
                                    }}
                                >
                                    {s.sublabel}
                                </p>
                            </div>
                        </div>

                        {/* Connector */}
                        {!isLast && (
                            <div
                                style={{
                                    flex: '1 1 auto',
                                    height: 2,
                                    marginTop: 22,
                                    minWidth: 16,
                                    borderRadius: 1,
                                    background: isDone
                                        ? 'var(--wizard-connector-done)'
                                        : 'var(--wizard-connector)',
                                    transition: 'background 0.3s ease',
                                    position: 'relative',
                                    overflow: 'hidden',
                                }}
                            >
                                {isDone && (
                                    <motion.div
                                        initial={{ scaleX: 0 }}
                                        animate={{ scaleX: 1 }}
                                        transition={{ duration: 0.3, ease: 'easeOut' }}
                                        style={{
                                            position: 'absolute',
                                            inset: 0,
                                            background: 'var(--wizard-connector-done)',
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
