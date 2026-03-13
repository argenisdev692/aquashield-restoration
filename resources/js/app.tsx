import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { QueryClientProvider } from '@tanstack/react-query';
import { ReactQueryDevtools } from '@tanstack/react-query-devtools';
import { queryClient } from '@/lib/queryClient';
import '../css/app.css';
import './bootstrap';
import 'sileo/styles.css';
import { Toaster } from 'sileo';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./pages/**/*.tsx', { eager: true });
        return pages[`./pages/${name}.tsx`];
    },
    setup({ el, App, props }) {
        createRoot(el).render(
            <QueryClientProvider client={queryClient}>
                <App {...props} />
                <Toaster 
                    position="top-right"
                    options={{
                        fill: 'var(--bg-card)',
                        roundness: 8,
                        styles: {
                            title: 'text-[14px] font-sans font-semibold text-[var(--text-primary)]',
                            description: 'text-[13px] font-sans text-[var(--text-muted)]'
                        }
                    }}
                />
                <ReactQueryDevtools initialIsOpen={false} />
            </QueryClientProvider>
        );
    },
});
