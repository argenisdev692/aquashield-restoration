import * as React from 'react';
import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/Pages/layouts/AppLayout';
import type { AuthPageProps } from '@/types/auth';

// ══════════════════════════════════════════════════════════════════
// Types
// ══════════════════════════════════════════════════════════════════

type KanbanColumnId = 'backlog' | 'todo' | 'in_progress' | 'done';

interface KanbanTask {
  id: string;
  title: string;
  description: string;
  priority: 'low' | 'medium' | 'high' | 'urgent';
  assignee: string;
  dueDate: string;
}

interface KanbanColumn {
  id: KanbanColumnId;
  title: string;
  color: string;
  dotColor: string;
  tasks: KanbanTask[];
}

interface MetricCard {
  title: string;
  value: string;
  change: string;
  changeType: 'positive' | 'negative' | 'neutral';
  icon: string;
  gradient: string;
}

// ══════════════════════════════════════════════════════════════════
// Sample Data
// ══════════════════════════════════════════════════════════════════

const INITIAL_COLUMNS: KanbanColumn[] = [
  {
    id: 'backlog',
    title: 'Backlog',
    color: 'var(--text-muted)',
    dotColor: 'var(--text-disabled)',
    tasks: [
      { id: 'T-001', title: 'Setup monitoring alerts', description: 'Configure OTel spans for auth flows', priority: 'medium', assignee: 'JD', dueDate: 'Mar 5' },
      { id: 'T-002', title: 'Database backup automation', description: 'Schedule nightly encrypted backups', priority: 'high', assignee: 'MK', dueDate: 'Mar 8' },
    ],
  },
  {
    id: 'todo',
    title: 'To Do',
    color: 'var(--accent-info)',
    dotColor: '#38bdf8',
    tasks: [
      { id: 'T-003', title: 'Implement user export', description: 'Excel + PDF export with date range filters', priority: 'high', assignee: 'AS', dueDate: 'Mar 1' },
      { id: 'T-004', title: 'Design contractor dashboard', description: 'Wireframe + Figma prototype', priority: 'medium', assignee: 'LP', dueDate: 'Mar 3' },
      { id: 'T-005', title: 'API rate limiting review', description: 'Audit all public endpoints', priority: 'low', assignee: 'JD', dueDate: 'Mar 10' },
    ],
  },
  {
    id: 'in_progress',
    title: 'In Progress',
    color: 'var(--accent-warning)',
    dotColor: '#f59e0b',
    tasks: [
      { id: 'T-006', title: 'Claims module backend', description: 'CQRS implementation with domain events', priority: 'urgent', assignee: 'MK', dueDate: 'Feb 28' },
      { id: 'T-007', title: 'Auth interface frontend', description: 'Login, OTP, Forgot Password pages', priority: 'high', assignee: 'AS', dueDate: 'Feb 27' },
    ],
  },
  {
    id: 'done',
    title: 'Done',
    color: 'var(--accent-success)',
    dotColor: '#22c55e',
    tasks: [
      { id: 'T-008', title: 'User model + migrations', description: 'Complete with soft deletes and roles', priority: 'high', assignee: 'MK', dueDate: 'Feb 25' },
      { id: 'T-009', title: 'Email templates branded', description: 'OTP, password reset, credentials', priority: 'medium', assignee: 'LP', dueDate: 'Feb 24' },
    ],
  },
];

const METRIC_CARDS: MetricCard[] = [
  {
    title: 'Total Users',
    value: '1,284',
    change: '+12.5%',
    changeType: 'positive',
    icon: 'users',
    gradient: 'linear-gradient(135deg, var(--color-aqua) 0%, var(--color-aqua-dark) 100%)',
  },
  {
    title: 'Active Claims',
    value: '347',
    change: '+8.2%',
    changeType: 'positive',
    icon: 'file',
    gradient: 'linear-gradient(135deg, var(--accent-success) 0%, #16a34a 100%)',
  },
  {
    title: 'Revenue',
    value: '$48,520',
    change: '-2.4%',
    changeType: 'negative',
    icon: 'dollar',
    gradient: 'linear-gradient(135deg, var(--accent-warning) 0%, #d97706 100%)',
  },
  {
    title: 'Completion Rate',
    value: '94.2%',
    change: '+1.8%',
    changeType: 'positive',
    icon: 'check',
    gradient: 'linear-gradient(135deg, var(--accent-secondary) 0%, #7c3aed 100%)',
  },
];

// ══════════════════════════════════════════════════════════════════
// Priority Badge
// ══════════════════════════════════════════════════════════════════

function PriorityBadge({ priority }: { priority: KanbanTask['priority'] }): React.JSX.Element {
  const config = {
    low:    { tint: 'var(--accent-info)',    label: 'Low'    },
    medium: { tint: 'var(--accent-warning)', label: 'Medium' },
    high:   { tint: 'var(--accent-error)',   label: 'High'   },
    urgent: { tint: 'var(--accent-error)',   label: 'Urgent' },
  };
  const c = config[priority];

  return (
    <span
      className="inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
      style={{
        background: `color-mix(in srgb, ${c.tint} ${priority === 'urgent' ? '20%' : '12%'}, transparent)`,
        color: c.tint,
        border: `1px solid color-mix(in srgb, ${c.tint} 28%, transparent)`,
      }}
    >
      {c.label}
    </span>
  );
}

// ══════════════════════════════════════════════════════════════════
// Metric Card Icon
// ══════════════════════════════════════════════════════════════════

function CardIcon({ name }: { name: string }): React.JSX.Element {
  const p = { width: 22, height: 22, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round' as const, strokeLinejoin: 'round' as const };

  switch (name) {
    case 'users':
      return (<svg {...p}><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 00-3-3.87" /><path d="M16 3.13a4 4 0 010 7.75" /></svg>);
    case 'file':
      return (<svg {...p}><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><polyline points="14 2 14 8 20 8" /><line x1="16" y1="13" x2="8" y2="13" /><line x1="16" y1="17" x2="8" y2="17" /></svg>);
    case 'dollar':
      return (<svg {...p}><line x1="12" y1="1" x2="12" y2="23" /><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" /></svg>);
    case 'check':
      return (<svg {...p}><path d="M22 11.08V12a10 10 0 11-5.93-9.14" /><polyline points="22 4 12 14.01 9 11.01" /></svg>);
    default:
      return <></>;
  }
}

// ══════════════════════════════════════════════════════════════════
// Dashboard Page
// ══════════════════════════════════════════════════════════════════

export default function DashboardPage(): React.JSX.Element {
  const { auth } = usePage<AuthPageProps>().props;
  const [columns, setColumns] = React.useState<KanbanColumn[]>(INITIAL_COLUMNS);

  // ── Drag State ──
  const [draggedTask, setDraggedTask] = React.useState<KanbanTask | null>(null);
  const [dragSourceCol, setDragSourceCol] = React.useState<KanbanColumnId | null>(null);
  const [dragOverCol, setDragOverCol] = React.useState<KanbanColumnId | null>(null);

  /** ── Drag Handlers ── */
  function handleDragStart(task: KanbanTask, sourceColId: KanbanColumnId): void {
    setDraggedTask(task);
    setDragSourceCol(sourceColId);
  }

  function handleDragOver(e: React.DragEvent, colId: KanbanColumnId): void {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    setDragOverCol(colId);
  }

  function handleDragLeave(): void {
    setDragOverCol(null);
  }

  function handleDrop(e: React.DragEvent, targetColId: KanbanColumnId): void {
    e.preventDefault();
    setDragOverCol(null);

    if (!draggedTask || !dragSourceCol || dragSourceCol === targetColId) {
      setDraggedTask(null);
      setDragSourceCol(null);
      return;
    }

    setColumns((prev) =>
      prev.map((col) => {
        if (col.id === dragSourceCol) {
          return { ...col, tasks: col.tasks.filter((t) => t.id !== draggedTask.id) };
        }
        if (col.id === targetColId) {
          return { ...col, tasks: [...col.tasks, draggedTask] };
        }
        return col;
      }),
    );

    setDraggedTask(null);
    setDragSourceCol(null);
  }

  function handleDragEnd(): void {
    setDraggedTask(null);
    setDragSourceCol(null);
    setDragOverCol(null);
  }

  return (
    <>
      <Head title="Dashboard — AquaShield" />
      <AppLayout>
          {/* ── Header ── */}
          <div className="mb-6">
            <h1 className="text-xl font-bold md:text-2xl" style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
              Welcome back, {auth.user?.name ?? 'User'} 👋
            </h1>
            <p className="mt-1 text-sm" style={{ color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
              Here's what's happening with your projects today.
            </p>
          </div>

          {/* ═══════════════════════════════════════
              METRIC CARDS
              ═══════════════════════════════════════ */}
          <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {METRIC_CARDS.map((card) => (
              <div
                key={card.title}
                className="group relative overflow-hidden rounded-xl p-5 cursor-pointer"
                style={{
                  background: 'var(--bg-card)',
                  border: '1px solid var(--border-default)',
                  transition: 'all 300ms cubic-bezier(0.4, 0, 0.2, 1)',
                }}
                onMouseEnter={(e) => {
                  const el = e.currentTarget as HTMLDivElement;
                  el.style.transform = 'translateY(-4px)';
                  el.style.borderColor = 'var(--color-aqua)';
                  el.style.boxShadow = '0 12px 32px color-mix(in srgb, var(--color-aqua) 20%, transparent), 0 0 0 1px color-mix(in srgb, var(--color-aqua) 25%, transparent)';
                }}
                onMouseLeave={(e) => {
                  const el = e.currentTarget as HTMLDivElement;
                  el.style.transform = 'translateY(0)';
                  el.style.borderColor = 'var(--border-default)';
                  el.style.boxShadow = 'none';
                }}
              >
                {/* Gradient accent bar */}
                <div
                  className="absolute left-0 top-0 h-1 w-full"
                  style={{ background: card.gradient }}
                />

                <div className="flex items-start justify-between">
                  <div>
                    <p
                      className="text-xs font-semibold uppercase tracking-wider"
                      style={{ color: 'var(--text-muted)' }}
                    >
                      {card.title}
                    </p>
                    <p
                      className="mt-2 text-3xl font-extrabold tracking-tight"
                      style={{ color: 'var(--text-primary)' }}
                    >
                      {card.value}
                    </p>
                    <div className="mt-2 flex items-center gap-1">
                      <span
                        className="text-xs font-semibold"
                        style={{
                          color:
                            card.changeType === 'positive'
                              ? 'var(--accent-success)'
                              : card.changeType === 'negative'
                                ? 'var(--accent-error)'
                                : 'var(--text-muted)',
                        }}
                      >
                        {card.change}
                      </span>
                      <span className="text-xs" style={{ color: 'var(--text-disabled)' }}>
                        vs last month
                      </span>
                    </div>
                  </div>

                  {/* Icon — scales on group hover */}
                  <div
                    className="flex h-11 w-11 items-center justify-center rounded-lg transition-transform duration-300 group-hover:scale-110"
                    style={{
                      background: card.gradient,
                      boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
                      color: 'white',
                    }}
                  >
                    <CardIcon name={card.icon} />
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* ═══════════════════════════════════════
              KANBAN BOARD
              ═══════════════════════════════════════ */}
          <div className="mb-4 flex items-center justify-between">
            <div>
              <h2 className="text-lg font-bold" style={{ color: 'var(--text-primary)' }}>
                Project Board
              </h2>
              <p className="text-xs" style={{ color: 'var(--text-muted)' }}>
                Drag and drop tasks between columns
              </p>
            </div>
            <div className="flex items-center gap-2">
              <span className="text-xs font-medium" style={{ color: 'var(--text-disabled)' }}>
                {columns.reduce((acc, col) => acc + col.tasks.length, 0)} tasks
              </span>
            </div>
          </div>

          {/* Horizontally scrollable on mobile, grid on larger screens */}
          <div className="-mx-4 overflow-x-auto px-4 md:mx-0 md:overflow-visible md:px-0">
          <div className="grid min-w-[700px] grid-cols-4 gap-4 md:min-w-0 md:grid-cols-2 xl:grid-cols-4">
            {columns.map((column) => (
              <div
                key={column.id}
                className="flex flex-col rounded-xl transition-all duration-200"
                style={{
                  background: dragOverCol === column.id
                    ? 'color-mix(in srgb, var(--bg-card) 90%, var(--color-aqua))'
                    : 'var(--bg-card)',
                  border: dragOverCol === column.id
                    ? '1px solid var(--color-aqua)'
                    : '1px solid var(--border-default)',
                  minHeight: '400px',
                }}
                onDragOver={(e) => handleDragOver(e, column.id)}
                onDragLeave={handleDragLeave}
                onDrop={(e) => handleDrop(e, column.id)}
              >
                {/* Column Header */}
                <div
                  className="flex items-center justify-between px-4 py-3"
                  style={{ borderBottom: '1px solid var(--border-subtle)' }}
                >
                  <div className="flex items-center gap-2.5">
                    <div
                      className="h-2.5 w-2.5 rounded-full"
                      style={{ background: column.dotColor }}
                    />
                    <span
                      className="text-sm font-semibold"
                      style={{ color: 'var(--text-primary)' }}
                    >
                      {column.title}
                    </span>
                  </div>
                  <span
                    className="flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[10px] font-bold"
                    style={{
                      background: 'rgba(255, 255, 255, 0.06)',
                      color: 'var(--text-muted)',
                    }}
                  >
                    {column.tasks.length}
                  </span>
                </div>

                {/* Tasks */}
                <div className="flex-1 space-y-2.5 p-3">
                  {column.tasks.map((task) => (
                    <div
                      key={task.id}
                      draggable
                      onDragStart={() => handleDragStart(task, column.id)}
                      onDragEnd={handleDragEnd}
                      className="cursor-grab rounded-lg p-3.5 transition-all duration-150 active:cursor-grabbing active:scale-[0.97]"
                      style={{
                        background: 'var(--bg-surface)',
                        border: '1px solid var(--border-subtle)',
                        opacity: draggedTask?.id === task.id ? 0.4 : 1,
                      }}
                    >
                      {/* Task ID + Priority */}
                      <div className="mb-2 flex items-center justify-between">
                        <span
                          className="text-[10px] font-bold tracking-wider"
                          style={{ color: 'var(--text-disabled)', fontFamily: 'var(--font-mono)' }}
                        >
                          {task.id}
                        </span>
                        <PriorityBadge priority={task.priority} />
                      </div>

                      {/* Title */}
                      <h4
                        className="text-sm font-semibold leading-tight"
                        style={{ color: 'var(--text-primary)' }}
                      >
                        {task.title}
                      </h4>

                      {/* Description */}
                      <p
                        className="mt-1 text-xs leading-relaxed"
                        style={{ color: 'var(--text-muted)' }}
                      >
                        {task.description}
                      </p>

                      {/* Footer: Assignee + Due */}
                      <div className="mt-3 flex items-center justify-between">
                        <div className="flex items-center gap-1.5">
                          <div
                            className="flex h-5 w-5 items-center justify-center rounded-full text-[9px] font-bold"
                            style={{
                              background: 'linear-gradient(135deg, var(--color-aqua) 0%, var(--color-aqua-dark) 100%)',
                              color: 'var(--color-white)',
                            }}
                          >
                            {task.assignee}
                          </div>
                        </div>
                        <span className="flex items-center gap-1 text-[10px]" style={{ color: 'var(--text-disabled)' }}>
                          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                          </svg>
                          {task.dueDate}
                        </span>
                      </div>
                    </div>
                  ))}

                  {/* Empty state */}
                  {column.tasks.length === 0 && (
                    <div className="flex h-24 items-center justify-center rounded-lg border-2 border-dashed" style={{ borderColor: 'var(--border-subtle)' }}>
                      <p className="text-xs" style={{ color: 'var(--text-disabled)' }}>
                        Drop tasks here
                      </p>
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>{/* end scroll wrapper */}
      </AppLayout>
    </>
  );
}
