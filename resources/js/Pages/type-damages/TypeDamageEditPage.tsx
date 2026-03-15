import { Head, router, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { ShieldCheck } from "lucide-react";
import { useUpdateTypeDamage } from "@/modules/type-damages/hooks/useTypeDamageMutations";
import type { TypeDamage, TypeDamageFormData } from "@/modules/type-damages/types";
import AppLayout from "@/pages/layouts/AppLayout";
import TypeDamageForm from "./components/TypeDamageForm";

interface TypeDamageEditPageProps extends PageProps {
    typeDamage: TypeDamage;
}

export default function TypeDamageEditPage(): React.JSX.Element {
    const { typeDamage } = usePage<TypeDamageEditPageProps>().props;
    const updateTypeDamage = useUpdateTypeDamage();

    async function handleSubmit(data: TypeDamageFormData): Promise<void> {
        await updateTypeDamage.mutateAsync({
            uuid: typeDamage.uuid,
            data,
        });
    }

    return (
        <>
            <Head title={`Edit ${typeDamage.type_damage_name}`} />
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
                            <ShieldCheck size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                Edit type damage
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Update the current type damage information.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <TypeDamageForm
                            initialData={{
                                type_damage_name: typeDamage.type_damage_name,
                                description: typeDamage.description ?? "",
                                severity: typeDamage.severity,
                            }}
                            onSubmit={handleSubmit}
                            isSubmitting={updateTypeDamage.isPending}
                            onCancel={() => router.visit("/type-damages")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
