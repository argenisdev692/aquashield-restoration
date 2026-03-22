import * as React from "react";
import { Head, router, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { PencilLine } from "lucide-react";
import { useUpdateClaimStatus } from "@/modules/claim-statuses/hooks/useClaimStatusMutations";
import type { ClaimStatus, ClaimStatusFormData } from "@/modules/claim-statuses/types";
import AppLayout from "@/pages/layouts/AppLayout";
import ClaimStatusForm from "./components/ClaimStatusForm";

interface ClaimStatusEditPageProps extends PageProps {
    claimStatus: ClaimStatus;
}

export default function ClaimStatusEditPage(): React.JSX.Element {
    const { claimStatus } = usePage<ClaimStatusEditPageProps>().props;
    const updateClaimStatus = useUpdateClaimStatus();

    async function handleSubmit(data: ClaimStatusFormData): Promise<void> {
        await updateClaimStatus.mutateAsync({
            uuid: claimStatus.uuid,
            data,
        });
    }

    return (
        <>
            <Head title={`Edit ${claimStatus.claim_status_name}`} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex items-start gap-4">
                        <div
                            className="flex h-14 w-14 items-center justify-center rounded-2xl"
                            style={{
                                background:
                                    "color-mix(in srgb, var(--accent-primary) 12%, transparent)",
                                color: "var(--accent-primary)",
                            }}
                        >
                            <PencilLine size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1
                                className="text-3xl font-extrabold"
                                style={{ color: "var(--text-primary)" }}
                            >
                                Edit claim status
                            </h1>
                            <p
                                className="text-sm"
                                style={{ color: "var(--text-muted)" }}
                            >
                                Update the current claim status information.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <ClaimStatusForm
                            initialData={{
                                claim_status_name: claimStatus.claim_status_name,
                                background_color: claimStatus.background_color ?? "#3B82F6",
                            }}
                            onSubmit={handleSubmit}
                            isSubmitting={updateClaimStatus.isPending}
                            onCancel={() => router.visit("/claim-statuses")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
