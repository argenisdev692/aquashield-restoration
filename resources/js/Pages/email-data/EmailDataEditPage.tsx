import * as React from "react";
import { Head, router, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { Mail } from "lucide-react";
import { useUpdateEmailData } from "@/modules/email-data/hooks/useEmailDataMutations";
import type { EmailData, EmailDataFormData } from "@/modules/email-data/types";
import AppLayout from "@/pages/layouts/AppLayout";
import EmailDataForm from "./components/EmailDataForm";

interface EmailDataEditPageProps extends PageProps {
    emailData: EmailData;
}

export default function EmailDataEditPage(): React.JSX.Element {
    const { emailData } = usePage<EmailDataEditPageProps>().props;
    const updateEmailData = useUpdateEmailData();

    async function handleSubmit(data: EmailDataFormData): Promise<void> {
        await updateEmailData.mutateAsync({
            uuid: emailData.uuid,
            data,
        });
    }

    return (
        <>
            <Head title={`Edit ${emailData.email}`} />
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
                                Edit email data
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Update the selected email record and its contact metadata.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <EmailDataForm
                            initialData={{
                                description: emailData.description ?? "",
                                email: emailData.email,
                                phone: emailData.phone ?? "",
                                type: emailData.type ?? "",
                            }}
                            onSubmit={handleSubmit}
                            isSubmitting={updateEmailData.isPending}
                            onCancel={() => router.visit("/email-data")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
