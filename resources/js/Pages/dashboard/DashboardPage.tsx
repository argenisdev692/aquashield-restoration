import * as React from 'react';
import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import type { AuthPageProps } from '@/types/auth';
import {
  Area,
  AreaChart,
  Pie,
  PieChart,
  XAxis,
  YAxis,
  CartesianGrid,
  Label
} from "recharts";

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/shadcn/card";

import {
  ChartConfig,
  ChartContainer,
  ChartTooltip,
  ChartTooltipContent,
} from "@/shadcn/chart";

// ══════════════════════════════════════════════════════════════════
// Types & Data
// ══════════════════════════════════════════════════════════════════

interface MetricCard {
  title: string;
  value: string;
  change: string;
  changeType: 'positive' | 'negative' | 'neutral';
  icon: string;
  gradient: string;
}

const METRIC_CARDS: MetricCard[] = [
  {
    title: 'Total Users',
    value: '1,284',
    change: '+12.5%',
    changeType: 'positive',
    icon: 'users',
    gradient: 'linear-gradient(135deg, var(--color-chart-1) 0%, oklch(0.5 0.2 264) 100%)',
  },
  {
    title: 'Active Claims',
    value: '347',
    change: '+8.2%',
    changeType: 'positive',
    icon: 'file',
    gradient: 'linear-gradient(135deg, var(--color-chart-2) 0%, oklch(0.6 0.15 162) 100%)',
  },
  {
    title: 'Revenue',
    value: '$48,520',
    change: '-2.4%',
    changeType: 'negative',
    icon: 'dollar',
    gradient: 'linear-gradient(135deg, var(--color-chart-3) 0%, oklch(0.6 0.2 70) 100%)',
  },
  {
    title: 'Completion Rate',
    value: '94.2%',
    change: '+1.8%',
    changeType: 'positive',
    icon: 'check',
    gradient: 'linear-gradient(135deg, var(--color-chart-4) 0%, oklch(0.5 0.25 303) 100%)',
  },
];

const REVENUE_DATA = [
  { month: "Jan", revenue: 15400, target: 14000 },
  { month: "Feb", revenue: 22100, target: 18000 },
  { month: "Mar", revenue: 18500, target: 20000 },
  { month: "Apr", revenue: 28900, target: 25000 },
  { month: "May", revenue: 35200, target: 30000 },
  { month: "Jun", revenue: 48520, target: 40000 },
];

const NEW_CUSTOMER_CLAIMS = [
  { customer: "Sophia Martinez", claim: "AQ-2031", category: "Water Damage", city: "Miami, FL", submitted_at: "2 min ago" },
  { customer: "Daniel Carter", claim: "AQ-2030", category: "Roof Leak", city: "Orlando, FL", submitted_at: "8 min ago" },
  { customer: "Olivia Bennett", claim: "AQ-2029", category: "Mold Remediation", city: "Tampa, FL", submitted_at: "14 min ago" },
  { customer: "Ethan Brooks", claim: "AQ-2028", category: "Storm Damage", city: "Naples, FL", submitted_at: "21 min ago" },
  { customer: "Ava Richardson", claim: "AQ-2027", category: "Fire Cleanup", city: "Sarasota, FL", submitted_at: "34 min ago" },
  { customer: "Noah Peterson", claim: "AQ-2026", category: "Flood Extraction", city: "Fort Myers, FL", submitted_at: "42 min ago" },
];

const USER_DIST_DATA = [
  { role: "Contractors", count: 45, fill: "var(--color-chart-4)" },
  { role: "Clients", count: 30, fill: "var(--color-chart-1)" },
  { role: "Managers", count: 15, fill: "var(--color-chart-2)" },
  { role: "Admins", count: 10, fill: "var(--color-chart-5)" },
];

const revenueChartConfig = {
  revenue: {
    label: "Revenue",
    color: "var(--color-chart-4)",
  },
  target: {
    label: "Target",
    color: "var(--color-chart-5)",
  },
} satisfies ChartConfig;

const userChartConfig = {
  count: { label: "Users" },
  Contractors: { label: "Contractors", color: "var(--color-chart-4)" },
  Clients: { label: "Clients", color: "var(--color-chart-1)" },
  Managers: { label: "Managers", color: "var(--color-chart-2)" },
  Admins: { label: "Admins", color: "var(--color-chart-5)" },
} satisfies ChartConfig;

const dashboardPanelStyle: React.CSSProperties = {
  background: 'color-mix(in srgb, var(--bg-elevated) 92%, transparent)',
  borderColor: 'color-mix(in srgb, var(--border-default) 72%, transparent)',
  backdropFilter: 'blur(14px)',
  boxShadow: '0 20px 44px -30px color-mix(in srgb, var(--bg-base) 80%, transparent)',
};

// ══════════════════════════════════════════════════════════════════
// Metric Card Icon
// ══════════════════════════════════════════════════════════════════

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
// Premium Metric Card Component (2026 Edition)
// ══════════════════════════════════════════════════════════════════

interface MetricCardProps {
  card: MetricCard;
}

function PremiumMetricCard({ card }: MetricCardProps): React.JSX.Element {
  const changeTone = card.changeType === 'positive'
    ? 'var(--accent-success)'
    : card.changeType === 'negative'
      ? 'var(--accent-error)'
      : 'var(--text-muted)';

  const changeBackground = card.changeType === 'neutral'
    ? 'color-mix(in srgb, var(--text-primary) 6%, transparent)'
    : `color-mix(in srgb, ${changeTone} 12%, transparent)`;

  const changeBorder = card.changeType === 'neutral'
    ? 'color-mix(in srgb, var(--border-default) 75%, transparent)'
    : `color-mix(in srgb, ${changeTone} 18%, transparent)`;

  return (
    <div
      className="group relative flex flex-col overflow-hidden rounded-2xl border p-6 transition-all duration-300 hover:-translate-y-1"
      style={{
        background: 'color-mix(in srgb, var(--bg-elevated) 94%, transparent)',
        borderColor: 'color-mix(in srgb, var(--border-default) 72%, transparent)',
        backdropFilter: 'blur(10px)',
        boxShadow: '0 20px 42px -32px color-mix(in srgb, var(--bg-base) 84%, transparent)',
      }}
    >
      <div 
        className="absolute inset-x-0 top-0 h-px"
        style={{
          background: 'linear-gradient(90deg, transparent 0%, color-mix(in srgb, var(--accent-primary) 35%, transparent) 50%, transparent 100%)',
        }}
      />

      <div className="flex items-center justify-between">
        <div 
          className="flex h-12 w-12 items-center justify-center rounded-xl shadow-lg transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3"
          style={{ 
            background: card.gradient,
            boxShadow: '0 12px 24px -10px color-mix(in srgb, var(--bg-base) 45%, transparent)',
          }}
        >
          <div style={{ color: 'var(--color-white)' }}>
            <CardIcon name={card.icon} />
          </div>
        </div>
        
        <div 
          className="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold tracking-tight shadow-sm"
          style={{
            background: changeBackground,
            color: changeTone,
            border: `1px solid ${changeBorder}`,
          }}
        >
          {card.changeType === 'positive' ? '↑' : card.changeType === 'negative' ? '↓' : '•'}
          {card.change.replace(/[+-]/, '')}
        </div>
      </div>

      <div className="mt-5">
        <p className="text-xs font-medium uppercase tracking-widest" style={{ color: 'var(--text-secondary)' }}>
          {card.title}
        </p>
        <div className="flex items-baseline gap-2 mt-1">
          <h3 className="text-3xl font-black tracking-tighter" style={{ color: 'var(--text-primary)' }}>
            {card.value}
          </h3>
          <span className="text-[10px] font-medium uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
            USD
          </span>
        </div>
      </div>
    </div>
  );
}

function NewCustomerClaimsMarquee(): React.JSX.Element {
  const claims = [...NEW_CUSTOMER_CLAIMS, ...NEW_CUSTOMER_CLAIMS];

  return (
    <div
      className="relative h-[248px] overflow-hidden rounded-[20px]"
      style={{
        background: 'color-mix(in srgb, var(--bg-subtle) 88%, transparent)',
        border: '1px solid color-mix(in srgb, var(--border-default) 68%, transparent)',
      }}
    >
      <style>{`
        @keyframes dashboard-claims-marquee {
          from { transform: translateY(0); }
          to { transform: translateY(-50%); }
        }
      `}</style>
      <div
        className="absolute inset-x-0 top-0 z-10 h-12"
        style={{ background: 'linear-gradient(180deg, var(--bg-elevated) 0%, transparent 100%)' }}
      />
      <div
        className="absolute inset-x-0 bottom-0 z-10 h-12"
        style={{ background: 'linear-gradient(0deg, var(--bg-elevated) 0%, transparent 100%)' }}
      />
      <div
        className="flex flex-col gap-2.5 p-3.5"
        style={{ animation: 'dashboard-claims-marquee 20s linear infinite' }}
      >
        {claims.map((claim, index) => (
          <div
            key={`${claim.claim}-${index}`}
            className="rounded-[18px] px-4 py-3.5"
            style={{
              background: 'color-mix(in srgb, var(--bg-elevated) 86%, transparent)',
              border: '1px solid color-mix(in srgb, var(--border-default) 64%, transparent)',
              boxShadow: '0 12px 24px -20px color-mix(in srgb, var(--bg-base) 75%, transparent)',
            }}
          >
            <div className="flex items-start justify-between gap-3">
              <div className="min-w-0">
                <p className="truncate text-[13px] font-semibold" style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                  {claim.customer}
                </p>
                <p className="mt-1 text-[10px] uppercase tracking-[0.2em]" style={{ color: 'var(--accent-secondary)', fontFamily: 'var(--font-sans)' }}>
                  {claim.claim}
                </p>
              </div>
              <span
                className="shrink-0 rounded-full px-2.5 py-1 text-[9px] font-semibold uppercase tracking-[0.18em]"
                style={{
                  background: 'color-mix(in srgb, var(--accent-primary) 14%, transparent)',
                  color: 'var(--accent-primary)',
                  border: '1px solid color-mix(in srgb, var(--accent-primary) 24%, transparent)',
                }}
              >
                New
              </span>
            </div>
            <div className="mt-2.5 flex items-center justify-between gap-3 text-[11px]" style={{ color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)' }}>
              <span className="truncate">{claim.category}</span>
              <span className="shrink-0">{claim.city}</span>
            </div>
            <div className="mt-1.5 text-[10px]" style={{ color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
              Submitted {claim.submitted_at}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

// ══════════════════════════════════════════════════════════════════
// Dashboard Page
// ══════════════════════════════════════════════════════════════════

export default function DashboardPage(): React.JSX.Element {
  const { auth } = usePage<AuthPageProps>().props;

  return (
    <>
      <Head title="Dashboard — AquaShield" />
      <AppLayout>
        <div className="relative min-h-full overflow-hidden">
          {/* ── Content layer ── */}
          <div className="relative">

          {/* ── Header ── */}
          <div className="mb-6">
            <div>
              <h1 className="text-xl font-bold md:text-2xl" style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                Welcome back, {auth.user?.name ?? 'User'} 👋
              </h1>
              <p className="mt-1 text-sm" style={{ color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                Here's your projects and revenue overview for today.
              </p>
            </div>
          </div>

          {/* ═══════════════════════════════════════
              METRIC CARDS (Upgraded to Modern 2026 Style)
              ═══════════════════════════════════════ */}
          <div className="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            {METRIC_CARDS.map((card) => (
              <PremiumMetricCard key={card.title} card={card} />
            ))}
          </div>

          {/* ═══════════════════════════════════════
              DASHBOARD CHARTS (Replaces Kanban)
              ═══════════════════════════════════════ */}
          <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
            
            {/* Linear Chart - Wide (Takes 2 columns on desktop) */}
            <Card className="col-span-1 md:col-span-2 shadow-sm border-border bg-card" style={dashboardPanelStyle}>
              <CardHeader>
                <CardTitle>Revenue & Targets</CardTitle>
                <CardDescription>
                  Tracking revenue growth for the last 6 months
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ChartContainer config={revenueChartConfig} className="h-[300px] w-full">
                  <AreaChart
                    accessibilityLayer
                    data={REVENUE_DATA}
                    margin={{ left: 12, right: 12 }}
                  >
                    <CartesianGrid vertical={false} strokeDasharray="3 3" />
                    <XAxis
                      dataKey="month"
                      tickLine={false}
                      axisLine={false}
                      tickMargin={8}
                    />
                    <YAxis
                      tickLine={false}
                      axisLine={false}
                      tickMargin={8}
                      tickFormatter={(value) => `$${value}`}
                    />
                    <ChartTooltip
                      cursor={false}
                      content={<ChartTooltipContent indicator="dot" />}
                    />
                    <Area
                      dataKey="target"
                      type="monotone"
                      fill="var(--color-chart-5)"
                      fillOpacity={0.1}
                      stroke="var(--color-chart-5)"
                      strokeWidth={2}
                    />
                    <Area
                      dataKey="revenue"
                      type="monotone"
                      fill="var(--color-chart-4)"
                      fillOpacity={0.4}
                      stroke="var(--color-chart-4)"
                      strokeWidth={2}
                    />
                  </AreaChart>
                </ChartContainer>
              </CardContent>
            </Card>

            <div className="flex flex-col gap-4">
              {/* Circular Chart 1 - Donut */}
              <Card className="flex-1 flex flex-col shadow-sm border-border bg-card" style={dashboardPanelStyle}>
                <CardHeader className="items-center pb-0">
                  <CardTitle>New customers claims</CardTitle>
                  <CardDescription>Live intake queue</CardDescription>
                </CardHeader>
                <CardContent className="mt-4 flex-1 pb-0">
                  <NewCustomerClaimsMarquee />
                </CardContent>
              </Card>

              <Card className="flex-1 flex flex-col shadow-sm border-border bg-card" style={dashboardPanelStyle}>
                <CardHeader className="items-center pb-0">
                  <CardTitle>User Distribution</CardTitle>
                  <CardDescription>Active platform roles</CardDescription>
                </CardHeader>
                <CardContent className="flex-1 pb-0 mt-4">
                  <ChartContainer
                    config={userChartConfig}
                    className="mx-auto aspect-4/3 max-h-[220px]"
                  >
                    <PieChart>
                      <ChartTooltip
                        cursor={false}
                        content={<ChartTooltipContent hideLabel />}
                      />
                      <Pie
                        data={USER_DIST_DATA}
                        dataKey="count"
                        nameKey="role"
                        innerRadius={50}
                        outerRadius={75}
                        strokeWidth={2}
                        stroke="var(--bg-elevated)"
                      >
                        <Label
                          content={({ viewBox }) => {
                            if (viewBox && "cx" in viewBox && "cy" in viewBox) {
                              return (
                                <text
                                  x={viewBox.cx}
                                  y={viewBox.cy}
                                  textAnchor="middle"
                                  dominantBaseline="middle"
                                >
                                  <tspan
                                    x={viewBox.cx}
                                    y={viewBox.cy}
                                    className="fill-foreground text-3xl font-bold"
                                  >
                                    100
                                  </tspan>
                                  <tspan
                                    x={viewBox.cx}
                                    y={(viewBox.cy || 0) + 24}
                                    className="fill-muted-foreground text-xs"
                                  >
                                    Users
                                  </tspan>
                                </text>
                              )
                            }
                          }}
                        />
                      </Pie>
                    </PieChart>
                  </ChartContainer>
                </CardContent>
              </Card>
            </div>
            
          </div>

          </div>{/* z-10 content layer */}
        </div>{/* relative overflow container */}
      </AppLayout>
    </>
  );
}
