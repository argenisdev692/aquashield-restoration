import * as React from 'react';
import { usePage } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useClaimWizardStore } from '@/modules/claims/stores/claimWizardStore';
import { ClaimWizard } from './components/wizard/ClaimWizard';
import type { Claim, CustomerSlotRole } from '@/modules/claims/types';
import { DEFAULT_WIZARD_FORM } from '@/modules/claims/types';

interface ClaimEditPageProps {
    claim: { data: Claim };
}

function mapClaimToFormData(claim: Claim): Partial<typeof DEFAULT_WIZARD_FORM> {
    return {
        property_id:         claim.property_id,
        property_address:    claim.property_address ?? '',
        property_lat:        null,
        property_lng:        null,
        policy_number:       claim.policy_number,
        claim_number:        claim.claim_number ?? '',
        date_of_loss:        claim.date_of_loss ?? '',
        description_of_loss: claim.description_of_loss ?? '',
        number_of_floors:    claim.number_of_floors?.toString() ?? '',
        claim_date:          claim.claim_date ?? '',
        work_date:           claim.work_date ?? '',
        type_damage_id:      claim.type_damage_id,
        claim_status:        claim.claim_status_id,
        damage_description:  claim.damage_description ?? '',
        scope_of_work:       claim.scope_of_work ?? '',
        customer_reviewed:   claim.customer_reviewed ?? false,
        cause_of_loss_ids:   claim.causes_of_loss.map((c) => c.id),
        service_request_ids: claim.service_requests.map((s) => s.id),
        user_id_ref_by:      claim.user_id_ref_by,
        insurance_company_id:   claim.insurance_company_assignment?.company_id ?? null,
        insurance_company_name: claim.insurance_company_assignment?.company_name ?? '',
        public_company_id:      claim.public_company_assignment?.company_id ?? null,
        public_company_name:    claim.public_company_assignment?.company_name ?? '',
        alliance_company_id:    claim.claim_alliance?.alliance_company_id ?? null,
        alliance_company_name:  claim.claim_alliance?.alliance_company_name ?? '',
        customer_slots: [
            {
                role: 'owner' as CustomerSlotRole,
                customer_id: claim.customers[0]?.id ?? null,
                customer_uuid: claim.customers[0]?.uuid ?? null,
                customer_label: claim.customers[0]?.full_name ?? 'Owner',
            },
            {
                role: 'co_owner' as CustomerSlotRole,
                customer_id: claim.customers[1]?.id ?? null,
                customer_uuid: claim.customers[1]?.uuid ?? null,
                customer_label: claim.customers[1]?.full_name ?? 'Co-Owner',
            },
            {
                role: 'extra' as CustomerSlotRole,
                customer_id: claim.customers[2]?.id ?? null,
                customer_uuid: claim.customers[2]?.uuid ?? null,
                customer_label: claim.customers[2]?.full_name ?? 'Extra Contact',
            },
        ],
    };
}

export default function ClaimEditPage(): React.JSX.Element {
    const { claim } = usePage().props as unknown as ClaimEditPageProps;
    const initEdit = useClaimWizardStore((s) => s.initEdit);

    React.useEffect(() => {
        initEdit(claim.data.uuid, mapClaimToFormData(claim.data));
    }, [claim.data, initEdit]);

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
                <ClaimWizard mode="edit" />
            </div>
        </AppLayout>
    );
}
