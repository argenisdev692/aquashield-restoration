import * as React from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import { ChevronLeft, ChevronRight, Send, Loader2, AlertCircle } from 'lucide-react';
import { useClaimWizardStore } from '@/modules/claims/stores/claimWizardStore';
import type { WizardStep } from '@/modules/claims/stores/claimWizardStore';
import { WizardStepper } from './WizardStepper';
import { Step1Property } from './steps/Step1Property';
import { Step2Customers } from './steps/Step2Customers';
import { Step3ClaimInfo } from './steps/Step3ClaimInfo';
import { Step4Companies } from './steps/Step4Companies';
import { Step5Additional } from './steps/Step5Additional';
import { Step6Review } from './steps/Step6Review';
import { useCreateClaim, useUpdateClaim } from '@/modules/claims/hooks/useClaimMutations';
import type { ClaimStorePayload } from '@/modules/claims/types';

const slideVariants = {
    enterForward:  { x: 40,  opacity: 0 },
    enterBackward: { x: -40, opacity: 0 },
    center:        { x: 0,   opacity: 1 },
    exitForward:   { x: -40, opacity: 0 },
    exitBackward:  { x: 40,  opacity: 0 },
};

interface ClaimWizardProps {
    mode: 'create' | 'edit';
}

export function ClaimWizard({ mode }: ClaimWizardProps): React.JSX.Element {
    const {
        step, direction, form, isSubmitting,
        editUuid, nextStep, prevStep, setStep, setSubmitting,
    } = useClaimWizardStore();

    const [stepValid, setStepValid] = React.useState(false);
    const [showValidationHint, setShowValidationHint] = React.useState(false);
    const [shaking, setShaking] = React.useState(false);
    const createMutation = useCreateClaim();
    const updateMutation = useUpdateClaim();

    const isPending = createMutation.isPending || updateMutation.isPending || isSubmitting;

    React.useEffect(() => {
        setStepValid(false);
        setShowValidationHint(false);
    }, [step]);

    const initial  = direction === 'forward' ? 'enterForward'  : 'enterBackward';
    const exit     = direction === 'forward' ? 'exitForward'   : 'exitBackward';

    function handleStepClick(s: WizardStep): void {
        if (s < step) setStep(s, 'backward');
    }

    function triggerShake(): void {
        setShaking(true);
        setShowValidationHint(true);
        setTimeout(() => setShaking(false), 500);
    }

    function buildPayload(): ClaimStorePayload {
        return {
            property_id:      form.property_id!,
            signature_path_id: form.signature_path_id,
            type_damage_id:   form.type_damage_id!,
            user_id_ref_by:   form.user_id_ref_by!,
            claim_status:     form.claim_status!,
            policy_number:    form.policy_number,
            claim_number:     form.claim_number || null,
            date_of_loss:     form.date_of_loss || null,
            description_of_loss: form.description_of_loss || null,
            number_of_floors: form.number_of_floors ? Number(form.number_of_floors) : null,
            claim_date:       form.claim_date || null,
            work_date:        form.work_date || null,
            damage_description: form.damage_description || null,
            scope_of_work:    form.scope_of_work || null,
            customer_reviewed: form.customer_reviewed,
            cause_of_loss_ids: form.cause_of_loss_ids.length > 0 ? form.cause_of_loss_ids : null,
            service_request_ids: form.service_request_ids.length > 0 ? form.service_request_ids : null,
        };
    }

    async function handleSubmit(): Promise<void> {
        setSubmitting(true);
        const payload = buildPayload();

        try {
            if (mode === 'edit' && editUuid) {
                await updateMutation.mutateAsync({ uuid: editUuid, data: payload });
            } else {
                await createMutation.mutateAsync(payload);
            }
        } finally {
            setSubmitting(false);
        }
    }

    const stepContent: Record<WizardStep, React.ReactNode> = {
        1: <Step1Property   onValidChange={setStepValid} />,
        2: <Step2Customers  onValidChange={setStepValid} />,
        3: <Step3ClaimInfo  onValidChange={setStepValid} />,
        4: <Step4Companies  onValidChange={setStepValid} />,
        5: <Step5Additional onValidChange={setStepValid} />,
        6: <Step6Review     onValidChange={setStepValid} onEditStep={(s) => setStep(s, 'backward')} isSubmitting={isPending} />,
    };

    const isLastStep = step === 6;
    const canProceed = stepValid && !isPending;

    const STEP_HINTS: Record<number, string> = {
        1: 'Please select a property to continue.',
        2: 'An Owner customer must be assigned to continue.',
        3: 'Policy number, damage type, and claim status are required.',
        4: '',
        5: '',
        6: '',
    };

    return (
        <div
            style={{
                display: 'flex',
                flexDirection: 'column',
                gap: 0,
                background: 'var(--bg-surface)',
                borderRadius: 'var(--radius-lg)',
                border: '1px solid var(--border-default)',
                overflow: 'hidden',
                width: '100%',
                margin: '0 auto',
                boxShadow: '0 8px 32px rgba(0,0,0,0.25)',
            }}
        >
            {/* Header */}
            <div
                style={{
                    borderBottom: '1px solid var(--border-subtle)',
                    background: 'var(--bg-elevated)',
                    overflow: 'hidden',
                }}
            >
                {/* Title row */}
                <div
                    style={{
                        padding: '20px 28px 16px',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        gap: 16,
                    }}
                >
                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                        {/* Icon badge */}
                        <div style={{
                            width: 38, height: 38, borderRadius: 10, flexShrink: 0,
                            background: 'color-mix(in srgb, var(--accent-primary) 18%, var(--bg-card))',
                            border: '1px solid color-mix(in srgb, var(--accent-primary) 30%, transparent)',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                        }}>
                            <span style={{ fontSize: 16 }}>📋</span>
                        </div>
                        <div>
                            <h2 style={{
                                margin: 0,
                                fontSize: 18,
                                fontWeight: 800,
                                color: 'var(--text-primary)',
                                fontFamily: 'var(--font-sans)',
                                letterSpacing: '-0.01em',
                                lineHeight: 1.2,
                            }}>
                                {mode === 'edit' ? 'Edit Claim' : 'New Claim'}
                            </h2>
                            <p style={{
                                margin: '2px 0 0',
                                fontSize: 12,
                                color: 'var(--text-muted)',
                                fontFamily: 'var(--font-sans)',
                            }}>
                                Complete all steps to {mode === 'edit' ? 'update' : 'create'} the claim
                            </p>
                        </div>
                    </div>

                    {/* Step counter pill */}
                    <div style={{
                        display: 'flex', alignItems: 'center', gap: 8, flexShrink: 0,
                    }}>
                        <div style={{
                            padding: '6px 14px',
                            borderRadius: 999,
                            background: 'color-mix(in srgb, var(--accent-primary) 14%, var(--bg-card))',
                            border: '1px solid color-mix(in srgb, var(--accent-primary) 28%, transparent)',
                            display: 'flex', alignItems: 'center', gap: 6,
                        }}>
                            <span style={{
                                fontSize: 13, fontWeight: 700,
                                color: 'var(--accent-primary)',
                                fontFamily: 'var(--font-sans)',
                            }}>
                                {step}
                            </span>
                            <span style={{
                                fontSize: 12, color: 'var(--text-muted)',
                                fontFamily: 'var(--font-sans)',
                            }}>
                                / 6
                            </span>
                        </div>
                        {/* Percentage */}
                        <span style={{
                            fontSize: 11, fontWeight: 600,
                            color: 'var(--text-muted)',
                            fontFamily: 'var(--font-sans)',
                        }}>
                            {Math.round(((step - 1) / 5) * 100)}%
                        </span>
                    </div>
                </div>

                {/* Progress bar */}
                <div style={{
                    height: 3,
                    background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                    position: 'relative',
                    margin: '0 0 2px',
                }}>
                    <div style={{
                        position: 'absolute', left: 0, top: 0, bottom: 0,
                        width: `${((step - 1) / 5) * 100}%`,
                        background: 'linear-gradient(90deg, var(--accent-primary), var(--accent-secondary))',
                        borderRadius: '0 2px 2px 0',
                        transition: 'width 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)',
                        boxShadow: '0 0 8px color-mix(in srgb, var(--accent-primary) 60%, transparent)',
                    }} />
                </div>

                {/* Stepper */}
                <div style={{ padding: '18px 20px 20px' }}>
                    <WizardStepper currentStep={step} onStepClick={handleStepClick} />
                </div>
            </div>

            {/* Step content */}
            <div
                style={{
                    padding: '28px 32px',
                    minHeight: 360,
                    position: 'relative',
                    overflow: 'hidden',
                }}
            >
                <AnimatePresence mode="wait" initial={false}>
                    <motion.div
                        key={step}
                        variants={slideVariants}
                        initial={initial}
                        animate="center"
                        exit={exit}
                        transition={{ duration: 0.22, ease: [0.25, 0.46, 0.45, 0.94] }}
                    >
                        {stepContent[step]}
                    </motion.div>
                </AnimatePresence>
            </div>

            {/* Footer navigation */}
            <div
                style={{
                    padding: '16px 32px',
                    borderTop: '1px solid var(--border-subtle)',
                    background: 'var(--bg-elevated)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    gap: 12,
                }}
            >
                {/* Back */}
                <button
                    type="button"
                    onClick={prevStep}
                    disabled={step === 1 || isPending}
                    aria-label="Previous step"
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 6,
                        padding: '9px 18px',
                        borderRadius: 'var(--radius-md)',
                        border: '1px solid var(--border-default)',
                        background: 'transparent',
                        color: step === 1 ? 'var(--text-disabled)' : 'var(--text-secondary)',
                        fontSize: 13,
                        fontFamily: 'var(--font-sans)',
                        fontWeight: 500,
                        cursor: step === 1 || isPending ? 'not-allowed' : 'pointer',
                        transition: 'all 0.15s ease',
                        opacity: step === 1 ? 0.5 : 1,
                    }}
                >
                    <ChevronLeft size={15} /> Back
                </button>

                {/* Step dots */}
                <div style={{ display: 'flex', gap: 6 }}>
                    {([1, 2, 3, 4, 5, 6] as WizardStep[]).map((s) => (
                        <div
                            key={s}
                            style={{
                                width: s === step ? 20 : 6,
                                height: 6,
                                borderRadius: 3,
                                background: s < step
                                    ? 'var(--accent-success)'
                                    : s === step
                                    ? 'var(--accent-primary)'
                                    : 'var(--border-default)',
                                transition: 'all 0.25s ease',
                            }}
                        />
                    ))}
                </div>

                {/* Next / Submit */}
                {isLastStep ? (
                    <motion.button
                        type="button"
                        onClick={() => { if (!canProceed) { triggerShake(); return; } void handleSubmit(); }}
                        aria-label="Submit claim"
                        animate={shaking ? { x: [0, -8, 8, -6, 6, -3, 3, 0] } : {}}
                        transition={{ duration: 0.45 }}
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: 8,
                            padding: '9px 22px',
                            borderRadius: 'var(--radius-md)',
                            border: 'none',
                            background: canProceed
                                ? 'var(--accent-primary)'
                                : 'var(--text-disabled)',
                            color: '#fff',
                            fontSize: 13,
                            fontFamily: 'var(--font-sans)',
                            fontWeight: 700,
                            cursor: canProceed ? 'pointer' : 'not-allowed',
                            transition: 'background 0.15s ease',
                            letterSpacing: '0.02em',
                        }}
                    >
                        {isPending ? (
                            <><Loader2 size={14} className="animate-spin" /> Submitting...</>
                        ) : (
                            <><Send size={14} /> Submit Claim</>
                        )}
                    </motion.button>
                ) : (
                    <motion.button
                        type="button"
                        onClick={() => { if (!canProceed) { triggerShake(); return; } nextStep(); }}
                        aria-label="Next step"
                        animate={shaking ? { x: [0, -8, 8, -6, 6, -3, 3, 0] } : {}}
                        transition={{ duration: 0.45 }}
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: 6,
                            padding: '9px 20px',
                            borderRadius: 'var(--radius-md)',
                            border: 'none',
                            background: canProceed
                                ? 'var(--accent-primary)'
                                : 'color-mix(in srgb, var(--accent-primary) 40%, var(--bg-elevated))',
                            color: canProceed ? '#fff' : 'var(--text-disabled)',
                            fontSize: 13,
                            fontFamily: 'var(--font-sans)',
                            fontWeight: 600,
                            cursor: canProceed ? 'pointer' : 'not-allowed',
                            transition: 'background 0.15s ease',
                        }}
                    >
                        Next <ChevronRight size={15} />
                    </motion.button>
                )}
            </div>

            {/* Validation hint */}
            <AnimatePresence>
                {showValidationHint && STEP_HINTS[step] && (
                    <motion.div
                        initial={{ opacity: 0, y: -8 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -8 }}
                        transition={{ duration: 0.2 }}
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: 8,
                            padding: '10px 20px',
                            background: 'color-mix(in srgb, var(--accent-error) 10%, var(--bg-card))',
                            borderTop: '1px solid color-mix(in srgb, var(--accent-error) 25%, transparent)',
                            fontSize: 12,
                            color: 'var(--accent-error)',
                            fontFamily: 'var(--font-sans)',
                        }}
                    >
                        <AlertCircle size={13} />
                        {STEP_HINTS[step]}
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
}
