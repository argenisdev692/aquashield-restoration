import * as React from 'react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useClaimWizardStore } from '@/modules/claims/stores/claimWizardStore';
import { ClaimWizard } from './components/wizard/ClaimWizard';

export default function ClaimCreatePage(): React.JSX.Element {
    const reset = useClaimWizardStore((s) => s.reset);

    React.useEffect(() => {
        reset();
    }, [reset]);

    return (
        <AppLayout>
            <div
                style={{
                    padding: '32px 24px',
                    maxWidth: 900,
                    margin: '0 auto',
                    width: '100%',
                    fontFamily: 'var(--font-sans)',
                }}
            >
                <ClaimWizard mode="create" />
            </div>
        </AppLayout>
    );
}
