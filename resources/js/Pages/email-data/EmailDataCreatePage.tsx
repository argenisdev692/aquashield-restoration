import * as React from "react";
import { Head, router } from "@inertiajs/react";
import { Mail } from "lucide-react";
import { useCreateEmailData } from "@/modules/email-data/hooks/useEmailDataMutations";
import type { EmailDataFormData } from "@/modules/email-data/types";
import AppLayout from "@/pages/layouts/AppLayout";
import EmailDataForm from "./components/EmailDataForm";

export default function EmailDataCreatePage(): React.JSX.Element {
    const createEmailData = useCreateEmailData();

    async function handleSubmit(data: EmailDataFormData): Promise<void> {
        await createEmailData.mutateAsync(data);
    }

    return (
        <>
            <Head title="Create Email Data" />
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
                            <Mail size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                Create email data
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Add a new email destination or contact inbox for the CRM.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <EmailDataForm
                            onSubmit={handleSubmit}
                            isSubmitting={createEmailData.isPending}
                            onCancel={() => router.visit("/email-data")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
