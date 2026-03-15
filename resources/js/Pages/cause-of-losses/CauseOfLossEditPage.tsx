import { Head, router, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { AlertTriangle } from "lucide-react";
import { useUpdateCauseOfLoss } from "@/modules/cause-of-losses/hooks/useCauseOfLossMutations";
import type { CauseOfLoss, CauseOfLossFormData } from "@/modules/cause-of-losses/types";
import AppLayout from "@/pages/layouts/AppLayout";
import CauseOfLossForm from "./components/CauseOfLossForm";

interface CauseOfLossEditPageProps extends PageProps {
    causeOfLoss: CauseOfLoss;
}

export default function CauseOfLossEditPage(): React.JSX.Element {
    const { causeOfLoss } = usePage<CauseOfLossEditPageProps>().props;
    const updateCauseOfLoss = useUpdateCauseOfLoss();

    async function handleSubmit(data: CauseOfLossFormData): Promise<void> {
        await updateCauseOfLoss.mutateAsync({
            uuid: causeOfLoss.uuid,
            data,
        });
    }

    return (
        <>
            <Head title={`Edit ${causeOfLoss.cause_loss_name}`} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex items-start gap-4">
                        <div
                            className="flex h-14 w-14 items-center justify-center rounded-2xl"
                            style={{
                                background: "color-mix(in srgb, var(--accent-primary) 12%, transparent)",
                                color: "var(--accent-primary)",
                            }}
                        >
                            <AlertTriangle size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                Edit cause of loss
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Update the current cause of loss information.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <CauseOfLossForm
                            initialData={{
                                cause_loss_name: causeOfLoss.cause_loss_name,
                                description: causeOfLoss.description ?? "",
                                severity: causeOfLoss.severity,
                            }}
                            onSubmit={handleSubmit}
                            isSubmitting={updateCauseOfLoss.isPending}
                            onCancel={() => router.visit("/cause-of-losses")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
