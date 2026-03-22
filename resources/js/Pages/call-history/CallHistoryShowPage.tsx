import { useState, useMemo } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import {
    Phone,
    PhoneIncoming,
    PhoneOutgoing,
    Play,
    Pause,
    Calendar,
    Clock,
    ArrowLeft,
    User,
    Hash,
    FileText,
    Activity,
    MessageSquare,
    Trash2,
    CheckCircle,
    RefreshCw,
} from 'lucide-react';
import AppLayout from '../layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { useCallHistoryDetail, useDeleteCallHistory, useRestoreCallHistory, useSyncCallsFromRetell } from './hooks';
import type { CallHistoryListItem } from './types';

interface PageProps {
    call: CallHistoryListItem;
    auth: {
        user: {
            permissions: string[];
        };
    };
}

export default function CallHistoryShowPage(): JSX.Element {
    const { call: initialCall, auth } = usePage<PageProps>().props;
    const [isPlaying, setIsPlaying] = useState(false);
    const [pendingDelete, setPendingDelete] = useState(false);
    const [pendingRestore, setPendingRestore] = useState(false);

    const { data: call, isPending, isError } = useCallHistoryDetail(initialCall.uuid);
    const displayCall = call ?? initialCall;

    const deleteCallHistory = useDeleteCallHistory();
    const restoreCallHistory = useRestoreCallHistory();
    const syncCallsFromRetell = useSyncCallsFromRetell();

    const isDeleted = displayCall.deletedAt !== null;

    const formattedDuration = useMemo(() => {
        const duration = displayCall.durationMs;
        if (!duration) return 'N/A';
        const minutes = Math.floor(duration / 60000);
        const seconds = Math.floor((duration % 60000) / 1000);
        return `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }, [displayCall.durationMs]);

    const formattedDate = useMemo(() => {
        const date = displayCall.startTimestamp;
        if (!date) return 'N/A';
        return new Date(date).toLocaleString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    }, [displayCall.startTimestamp]);

    const handleConfirmDelete = () => {
        deleteCallHistory.mutate(displayCall.uuid, {
            onSuccess: () => {
                router.visit('/call-history');
            },
        });
        setPendingDelete(false);
    };

    const handleConfirmRestore = () => {
        restoreCallHistory.mutate(displayCall.uuid);
        setPendingRestore(false);
    };

    const handleSync = () => {
        syncCallsFromRetell.mutate({});
    };

    const statusColors: Record<string, string> = {
        registered: 'var(--warning)',
        ongoing: 'var(--accent-primary)',
        ended: 'var(--success)',
        error: 'var(--error)',
    };

    const sentimentColors: Record<string, string> = {
        Positive: 'var(--success)',
        Neutral: 'var(--warning)',
        Negative: 'var(--error)',
    };

    return (
        <>
            <Head title={`Call ${displayCall.callId.slice(0, 8)}...`} />
            <AppLayout>
                <div className="mx-auto max-w-[1200px] space-y-6 p-6">
                    {/* Header */}
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-4">
                            <button
                                onClick={() => router.visit('/call-history')}
                                className="flex h-10 w-10 items-center justify-center rounded-xl border transition-colors hover:bg-black/5"
                                style={{ borderColor: 'var(--border-default)', color: 'var(--text-muted)' }}
                            >
                                <ArrowLeft size={20} />
                            </button>
                            <div>
                                <h1
                                    className="text-2xl font-bold"
                                    style={{ color: 'var(--text-primary)' }}
                                >
                                    Call Details
                                </h1>
                                <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                    ID: <span className="font-mono">{displayCall.callId}</span>
                                </p>
                            </div>
                        </div>

                        <div className="flex items-center gap-2">
                            <PermissionGuard permissions={['SYNC_CALL_HISTORY']}>
                                <button
                                    onClick={handleSync}
                                    disabled={syncCallsFromRetell.isPending}
                                    className="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-all hover:opacity-90 disabled:pointer-events-none disabled:opacity-50"
                                    style={{
                                        borderColor: 'var(--border-default)',
                                        background: 'var(--bg-card)',
                                        color: 'var(--text-primary)',
                                    }}
                                >
                                    <RefreshCw
                                        size={16}
                                        className={syncCallsFromRetell.isPending ? 'animate-spin' : ''}
                                    />
                                    Sync
                                </button>
                            </PermissionGuard>

                            {!isDeleted ? (
                                <PermissionGuard permissions={['DELETE_CALL_HISTORY']}>
                                    <button
                                        onClick={() => setPendingDelete(true)}
                                        className="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-all hover:opacity-90"
                                        style={{
                                            borderColor: 'var(--error)',
                                            background: 'color-mix(in srgb, var(--error) 10%, transparent)',
                                            color: 'var(--error)',
                                        }}
                                    >
                                        <Trash2 size={16} />
                                        Delete
                                    </button>
                                </PermissionGuard>
                            ) : (
                                <PermissionGuard permissions={['RESTORE_CALL_HISTORY']}>
                                    <button
                                        onClick={() => setPendingRestore(true)}
                                        className="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-all hover:opacity-90"
                                        style={{
                                            borderColor: 'var(--success)',
                                            background: 'color-mix(in srgb, var(--success) 10%, transparent)',
                                            color: 'var(--success)',
                                        }}
                                    >
                                        <CheckCircle size={16} />
                                        Restore
                                    </button>
                                </PermissionGuard>
                            )}
                        </div>
                    </div>

                    {/* Status Banner */}
                    {isDeleted && (
                        <div
                            className="rounded-xl border px-4 py-3"
                            style={{
                                borderColor: 'var(--warning)',
                                background: 'color-mix(in srgb, var(--warning) 10%, transparent)',
                            }}
                        >
                            <p className="text-sm" style={{ color: 'var(--warning)' }}>
                                This call has been deleted and is only visible to administrators.
                            </p>
                        </div>
                    )}

                    {/* Main Content */}
                    <div className="grid gap-6 lg:grid-cols-3">
                        {/* Left Column - Call Info */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Call Overview Card */}
                            <div
                                className="rounded-2xl border p-6 shadow-sm"
                                style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}
                            >
                                <h2
                                    className="mb-4 text-lg font-semibold"
                                    style={{ color: 'var(--text-primary)' }}
                                >
                                    Call Overview
                                </h2>

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="flex items-start gap-3">
                                        <div
                                            className="flex h-10 w-10 items-center justify-center rounded-xl"
                                            style={{ background: 'var(--bg-secondary)' }}
                                        >
                                            {displayCall.direction === 'inbound' ? (
                                                <PhoneIncoming size={20} style={{ color: 'var(--success)' }} />
                                            ) : (
                                                <PhoneOutgoing size={20} style={{ color: 'var(--accent-primary)' }} />
                                            )}
                                        </div>
                                        <div>
                                            <p className="text-xs uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                                Direction
                                            </p>
                                            <p className="font-medium capitalize" style={{ color: 'var(--text-primary)' }}>
                                                {displayCall.direction}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-3">
                                        <div
                                            className="flex h-10 w-10 items-center justify-center rounded-xl"
                                            style={{ background: 'var(--bg-secondary)' }}
                                        >
                                            <Activity size={20} style={{ color: 'var(--accent-primary)' }} />
                                        </div>
                                        <div>
                                            <p className="text-xs uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                                Status
                                            </p>
                                            <span
                                                className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                                style={{
                                                    background: `color-mix(in srgb, ${statusColors[displayCall.callStatus] ?? 'var(--text-muted)'} 15%, transparent)`,
                                                    color: statusColors[displayCall.callStatus] ?? 'var(--text-muted)',
                                                }}
                                            >
                                                {displayCall.callStatus}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-3">
                                        <div
                                            className="flex h-10 w-10 items-center justify-center rounded-xl"
                                            style={{ background: 'var(--bg-secondary)' }}
                                        >
                                            <Phone size={20} style={{ color: 'var(--accent-primary)' }} />
                                        </div>
                                        <div>
                                            <p className="text-xs uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                                From Number
                                            </p>
                                            <p className="font-medium" style={{ color: 'var(--text-primary)' }}>
                                                {displayCall.fromNumber ?? 'N/A'}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-3">
                                        <div
                                            className="flex h-10 w-10 items-center justify-center rounded-xl"
                                            style={{ background: 'var(--bg-secondary)' }}
                                        >
                                            <Phone size={20} style={{ color: 'var(--accent-primary)' }} />
                                        </div>
                                        <div>
                                            <p className="text-xs uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                                To Number
                                            </p>
                                            <p className="font-medium" style={{ color: 'var(--text-primary)' }}>
                                                {displayCall.toNumber ?? 'N/A'}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-3">
                                        <div
                                            className="flex h-10 w-10 items-center justify-center rounded-xl"
                                            style={{ background: 'var(--bg-secondary)' }}
                                        >
                                            <Calendar size={20} style={{ color: 'var(--accent-primary)' }} />
                                        </div>
                                        <div>
                                            <p className="text-xs uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                                Start Time
                                            </p>
                                            <p className="font-medium" style={{ color: 'var(--text-primary)' }}>
                                                {formattedDate}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-3">
                                        <div
                                            className="flex h-10 w-10 items-center justify-center rounded-xl"
                                            style={{ background: 'var(--bg-secondary)' }}
                                        >
                                            <Clock size={20} style={{ color: 'var(--accent-primary)' }} />
                                        </div>
                                        <div>
                                            <p className="text-xs uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                                Duration
                                            </p>
                                            <p className="font-medium" style={{ color: 'var(--text-primary)' }}>
                                                {formattedDuration}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Transcript Card */}
                            {displayCall.transcript && (
                                <div
                                    className="rounded-2xl border p-6 shadow-sm"
                                    style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}
                                >
                                    <div className="mb-4 flex items-center gap-3">
                                        <MessageSquare size={20} style={{ color: 'var(--accent-primary)' }} />
                                        <h2
                                            className="text-lg font-semibold"
                                            style={{ color: 'var(--text-primary)' }}
                                        >
                                            Transcript
                                        </h2>
                                    </div>
                                    <div
                                        className="max-h-96 overflow-y-auto rounded-xl p-4 text-sm leading-relaxed"
                                        style={{ background: 'var(--bg-secondary)', color: 'var(--text-primary)' }}
                                    >
                                        {displayCall.transcript}
                                    </div>
                                </div>
                            )}

                            {/* Call Analysis Card */}
                            {displayCall.callAnalysis && (
                                <div
                                    className="rounded-2xl border p-6 shadow-sm"
                                    style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}
                                >
                                    <div className="mb-4 flex items-center gap-3">
                                        <Activity size={20} style={{ color: 'var(--accent-primary)' }} />
                                        <h2
                                            className="text-lg font-semibold"
                                            style={{ color: 'var(--text-primary)' }}
                                        >
                                            AI Analysis
                                        </h2>
                                    </div>
                                    <div className="space-y-4">
                                        {displayCall.callAnalysis.call_summary && (
                                            <div>
                                                <p className="text-xs uppercase tracking-wider mb-1" style={{ color: 'var(--text-muted)' }}>
                                                    Summary
                                                </p>
                                                <p className="text-sm" style={{ color: 'var(--text-primary)' }}>
                                                    {displayCall.callAnalysis.call_summary}
                                                </p>
                                            </div>
                                        )}
                                        {displayCall.callAnalysis.user_sentiment && (
                                            <div>
                                                <p className="text-xs uppercase tracking-wider mb-1" style={{ color: 'var(--text-muted)' }}>
                                                    User Sentiment
                                                </p>
                                                <span
                                                    className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                                    style={{
                                                        background: `color-mix(in srgb, ${sentimentColors[displayCall.callAnalysis.user_sentiment] ?? 'var(--text-muted)'} 15%, transparent)`,
                                                        color: sentimentColors[displayCall.callAnalysis.user_sentiment] ?? 'var(--text-muted)',
                                                    }}
                                                >
                                                    {displayCall.callAnalysis.user_sentiment}
                                                </span>
                                            </div>
                                        )}
                                        {Object.entries(displayCall.callAnalysis.custom_analysis_data ?? {}).length > 0 && (
                                            <div>
                                                <p className="text-xs uppercase tracking-wider mb-2" style={{ color: 'var(--text-muted)' }}>
                                                    Custom Analysis
                                                </p>
                                                <div className="grid gap-2">
                                                    {Object.entries(displayCall.callAnalysis.custom_analysis_data).map(([key, value]) => (
                                                        <div
                                                            key={key}
                                                            className="flex items-center justify-between rounded-lg px-3 py-2"
                                                            style={{ background: 'var(--bg-secondary)' }}
                                                        >
                                                            <span className="text-xs capitalize" style={{ color: 'var(--text-muted)' }}>
                                                                {key.replace(/_/g, ' ')}
                                                            </span>
                                                            <span className="text-sm font-medium" style={{ color: 'var(--text-primary)' }}>
                                                                {String(value)}
                                                            </span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Right Column - Metadata */}
                        <div className="space-y-6">
                            {/* Recording Player */}
                            {displayCall.recordingUrl && (
                                <div
                                    className="rounded-2xl border p-6 shadow-sm"
                                    style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}
                                >
                                    <h2
                                        className="mb-4 text-lg font-semibold"
                                        style={{ color: 'var(--text-primary)' }}
                                    >
                                        Recording
                                    </h2>
                                    <audio
                                        controls
                                        className="w-full"
                                        src={displayCall.recordingUrl}
                                    />
                                </div>
                            )}

                            {/* Agent Info */}
                            <div
                                className="rounded-2xl border p-6 shadow-sm"
                                style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}
                            >
                                <div className="mb-4 flex items-center gap-3">
                                    <User size={20} style={{ color: 'var(--accent-primary)' }} />
                                    <h2
                                        className="text-lg font-semibold"
                                        style={{ color: 'var(--text-primary)' }}
                                    >
                                        Agent Information
                                    </h2>
                                </div>
                                <div className="space-y-3">
                                    <div>
                                        <p className="text-xs uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                            Agent Name
                                        </p>
                                        <p className="font-medium" style={{ color: 'var(--text-primary)' }}>
                                            {displayCall.agentName ?? 'N/A'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-xs uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                            Agent ID
                                        </p>
                                        <p className="font-mono text-sm" style={{ color: 'var(--text-muted)' }}>
                                            {displayCall.agentId ?? 'N/A'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-xs uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                            Call Type
                                        </p>
                                        <span
                                            className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                            style={{
                                                background: `color-mix(in srgb, var(--accent-primary) 15%, transparent)`,
                                                color: 'var(--accent-primary)',
                                            }}
                                        >
                                            {displayCall.callType}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Technical Details */}
                            <div
                                className="rounded-2xl border p-6 shadow-sm"
                                style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}
                            >
                                <div className="mb-4 flex items-center gap-3">
                                    <Hash size={20} style={{ color: 'var(--accent-primary)' }} />
                                    <h2
                                        className="text-lg font-semibold"
                                        style={{ color: 'var(--text-primary)' }}
                                    >
                                        Technical Details
                                    </h2>
                                </div>
                                <div className="space-y-3">
                                    {displayCall.disconnectionReason && (
                                        <div>
                                            <p className="text-xs uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                                Disconnection Reason
                                            </p>
                                            <p className="text-sm" style={{ color: 'var(--text-primary)' }}>
                                                {displayCall.disconnectionReason}
                                            </p>
                                        </div>
                                    )}
                                    <div>
                                        <p className="text-xs uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                            Created
                                        </p>
                                        <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                            {new Date(displayCall.createdAt).toLocaleString('en-US')}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-xs uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                            Updated
                                        </p>
                                        <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                            {new Date(displayCall.updatedAt).toLocaleString('en-US')}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Metadata */}
                            {displayCall.metadata && Object.keys(displayCall.metadata).length > 0 && (
                                <div
                                    className="rounded-2xl border p-6 shadow-sm"
                                    style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}
                                >
                                    <div className="mb-4 flex items-center gap-3">
                                        <FileText size={20} style={{ color: 'var(--accent-primary)' }} />
                                        <h2
                                            className="text-lg font-semibold"
                                            style={{ color: 'var(--text-primary)' }}
                                        >
                                            Metadata
                                        </h2>
                                    </div>
                                    <div className="space-y-2">
                                        {Object.entries(displayCall.metadata).map(([key, value]) => (
                                            <div
                                                key={key}
                                                className="flex items-center justify-between rounded-lg px-3 py-2"
                                                style={{ background: 'var(--bg-secondary)' }}
                                            >
                                                <span className="text-xs capitalize" style={{ color: 'var(--text-muted)' }}>
                                                    {key.replace(/_/g, ' ')}
                                                </span>
                                                <span className="text-sm font-medium" style={{ color: 'var(--text-primary)' }}>
                                                    {typeof value === 'object' ? JSON.stringify(value) : String(value)}
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                <DeleteConfirmModal
                    open={pendingDelete}
                    entityLabel={`call ${displayCall.callId.slice(0, 8)}...`}
                    onConfirm={handleConfirmDelete}
                    onCancel={() => setPendingDelete(false)}
                    isDeleting={deleteCallHistory.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore}
                    entityLabel="call"
                    entityName={displayCall.callId}
                    onConfirm={handleConfirmRestore}
                    onCancel={() => setPendingRestore(false)}
                    isPending={restoreCallHistory.isPending}
                />
            </AppLayout>
        </>
    );
}
