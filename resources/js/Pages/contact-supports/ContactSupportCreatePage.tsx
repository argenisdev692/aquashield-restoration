import { Head, router } from "@inertiajs/react";
import { MessageSquareText } from "lucide-react";
import { useCreateContactSupport } from "@/modules/contact-supports/hooks/useContactSupportMutations";
import type { ContactSupportFormData } from "@/modules/contact-supports/types";
import AppLayout from "@/pages/layouts/AppLayout";
import ContactSupportForm from "./components/ContactSupportForm";

export default function ContactSupportCreatePage(): React.JSX.Element {
    const createContactSupport = useCreateContactSupport();

    async function handleSubmit(data: ContactSupportFormData): Promise<void> {
        await createContactSupport.mutateAsync(data);
    }

    return (
        <>
            <Head title="Create Contact Support" />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex items-start gap-4">
                        <div className="flex h-14 w-14 items-center justify-center rounded-2xl" style={{ background: "color-mix(in srgb, var(--accent-primary) 12%, transparent)", color: "var(--accent-primary)" }}>
                            <MessageSquareText size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                Create contact support
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Add a new support contact record for internal follow-up.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <ContactSupportForm
                            onSubmit={handleSubmit}
                            isSubmitting={createContactSupport.isPending}
                            onCancel={() => router.visit("/contact-supports")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
