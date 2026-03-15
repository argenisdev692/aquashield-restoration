import { Head, router } from "@inertiajs/react";
import { AlertTriangle } from "lucide-react";
import { useCreateCauseOfLoss } from "@/modules/cause-of-losses/hooks/useCauseOfLossMutations";
import type { CauseOfLossFormData } from "@/modules/cause-of-losses/types";
import AppLayout from "@/pages/layouts/AppLayout";
import CauseOfLossForm from "./components/CauseOfLossForm";

export default function CauseOfLossCreatePage(): React.JSX.Element {
    const createCauseOfLoss = useCreateCauseOfLoss();

    async function handleSubmit(data: CauseOfLossFormData): Promise<void> {
        await createCauseOfLoss.mutateAsync(data);
    }

    return (
        <>
            <Head title="Create Cause of Loss" />
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
                                Create cause of loss
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Add a new cause of loss record for your reference catalog.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <CauseOfLossForm
                            onSubmit={handleSubmit}
                            isSubmitting={createCauseOfLoss.isPending}
                            onCancel={() => router.visit("/cause-of-losses")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
