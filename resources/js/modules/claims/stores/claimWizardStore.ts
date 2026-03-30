import { create } from 'zustand';
import type { ClaimWizardFormData } from '../types';
import { DEFAULT_WIZARD_FORM } from '../types';

export type WizardStep = 1 | 2 | 3 | 4 | 5 | 6;

interface ClaimWizardState {
    step: WizardStep;
    direction: 'forward' | 'backward';
    form: ClaimWizardFormData;
    isSubmitting: boolean;
    editUuid: string | null;
}

interface ClaimWizardActions {
    setStep: (step: WizardStep, direction?: 'forward' | 'backward') => void;
    nextStep: () => void;
    prevStep: () => void;
    updateForm: (partial: Partial<ClaimWizardFormData>) => void;
    setSubmitting: (val: boolean) => void;
    initEdit: (uuid: string, data: Partial<ClaimWizardFormData>) => void;
    reset: () => void;
}

const INITIAL_STATE: ClaimWizardState = {
    step: 1,
    direction: 'forward',
    form: { ...DEFAULT_WIZARD_FORM },
    isSubmitting: false,
    editUuid: null,
};

export const useClaimWizardStore = create<ClaimWizardState & ClaimWizardActions>()((set, get) => ({
    ...INITIAL_STATE,

    setStep: (step, direction = 'forward') => set({ step, direction }),

    nextStep: () => {
        const current = get().step;
        if (current < 6) {
            set({ step: (current + 1) as WizardStep, direction: 'forward' });
        }
    },

    prevStep: () => {
        const current = get().step;
        if (current > 1) {
            set({ step: (current - 1) as WizardStep, direction: 'backward' });
        }
    },

    updateForm: (partial) =>
        set((state) => ({ form: { ...state.form, ...partial } })),

    setSubmitting: (val) => set({ isSubmitting: val }),

    initEdit: (uuid, data) =>
        set({
            step: 1,
            direction: 'forward',
            editUuid: uuid,
            isSubmitting: false,
            form: { ...DEFAULT_WIZARD_FORM, ...data },
        }),

    reset: () => set({ ...INITIAL_STATE, form: { ...DEFAULT_WIZARD_FORM } }),
}));
