import { Head, router, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { MessageSquareText } from "lucide-react";
import { useUpdateContactSupport } from "@/modules/contact-supports/hooks/useContactSupportMutations";
import type { ContactSupport, ContactSupportFormData } from "@/modules/contact-supports/types";
import AppLayout from "@/pages/layouts/AppLayout";
import ContactSupportForm from "./components/ContactSupportForm";

interface ContactSupportEditPageProps extends PageProps {
    contactSupport: ContactSupport;
}

export default function ContactSupportEditPage(): React.JSX.Element {
    const { contactSupport } = usePage<ContactSupportEditPageProps>().props;
    const updateContactSupport = useUpdateContactSupport();

    async function handleSubmit(data: ContactSupportFormData): Promise<void> {
        await updateContactSupport.mutateAsync({
            uuid: contactSupport.uuid,
            data,
        });
    }

    return (
        <>
            <Head title={`Edit ${contactSupport.full_name}`} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex items-start gap-4">
                        <div className="flex h-14 w-14 items-center justify-center rounded-2xl" style={{ background: "color-mix(in srgb, var(--accent-primary) 12%, transparent)", color: "var(--accent-primary)" }}>
                            <MessageSquareText size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                Edit contact support
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Update the current support contact details.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <ContactSupportForm
                            initialData={{
                                first_name: contactSupport.first_name,
                                last_name: contactSupport.last_name ?? "",
                                email: contactSupport.email,
                                phone: contactSupport.phone ?? "",
                                message: contactSupport.message,
                                sms_consent: contactSupport.sms_consent,
                                readed: contactSupport.readed,
                            }}
                            onSubmit={handleSubmit}
                            isSubmitting={updateContactSupport.isPending}
                            onCancel={() => router.visit("/contact-supports")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
