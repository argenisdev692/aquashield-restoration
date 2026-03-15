import { Head, router } from "@inertiajs/react";
import { ShieldPlus } from "lucide-react";
import { useCreateTypeDamage } from "@/modules/type-damages/hooks/useTypeDamageMutations";
import type { TypeDamageFormData } from "@/modules/type-damages/types";
import AppLayout from "@/pages/layouts/AppLayout";
import TypeDamageForm from "./components/TypeDamageForm";

export default function TypeDamageCreatePage(): React.JSX.Element {
    const createTypeDamage = useCreateTypeDamage();

    async function handleSubmit(data: TypeDamageFormData): Promise<void> {
        await createTypeDamage.mutateAsync(data);
    }

    return (
        <>
            <Head title="Create Type Damage" />
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
                            <ShieldPlus size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                Create type damage
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Add a new type damage record for your new reference catalog.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <TypeDamageForm
                            onSubmit={handleSubmit}
                            isSubmitting={createTypeDamage.isPending}
                            onCancel={() => router.visit("/type-damages")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
