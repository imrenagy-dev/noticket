import { Head } from '@inertiajs/react';
import { CheckCircle2, Coffee, Gem, Leaf, Monitor, Moon, Sparkles, Sprout, Sun } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';

// ── Colour palettes sampled directly from app.css CSS variables ───────────

interface Palette {
    bg: string; sidebar: string; card: string; border: string;
    primary: string; muted: string; mutedFg: string; sidebarFg: string;
}

const P: Record<Appearance, Palette> = {
    //                      bg          sidebar     card        border      primary     muted       mutedFg     sidebarFg
    light:        { bg:'#FFFFFF', sidebar:'#F9F9F9', card:'#FFFFFF', border:'#E4E4E4', primary:'#252525', muted:'#F5F5F5', mutedFg:'#6E6E6E', sidebarFg:'#1A1A1A' },
    dark:         { bg:'#171717', sidebar:'#252525', card:'#171717', border:'#333333', primary:'#F0F0F0', muted:'#333333', mutedFg:'#8A8A8A', sidebarFg:'#F0F0F0' },
    brown:        { bg:'#1B1009', sidebar:'#130B05', card:'#221308', border:'#3D2010', primary:'#C89050', muted:'#2C180A', mutedFg:'#907060', sidebarFg:'#E8D0B8' },
    blue:         { bg:'#080C18', sidebar:'#05091A', card:'#0C1028', border:'#1C2850', primary:'#4080DF', muted:'#101830', mutedFg:'#5070A8', sidebarFg:'#D8DDF5' },
    azure:        { bg:'#F0F5FF', sidebar:'#DCE9FF', card:'#FFFFFF', border:'#BDDAFF', primary:'#2B63C8', muted:'#E0EEFF', mutedFg:'#4870A8', sidebarFg:'#1A2A50' },
    'green-dark': { bg:'#0A1A0C', sidebar:'#071008', card:'#0F2012', border:'#1C3522', primary:'#19BB5A', muted:'#122016', mutedFg:'#4E7A5A', sidebarFg:'#E6F5EB' },
    'green-light':{ bg:'#F0FAF3', sidebar:'#E2F5E8', card:'#FFFFFF', border:'#C6E8D4', primary:'#0E8840', muted:'#E6F5EB', mutedFg:'#477856', sidebarFg:'#0A2212' },
    system:       { bg:'#171717', sidebar:'#252525', card:'#171717', border:'#333333', primary:'#F0F0F0', muted:'#333333', mutedFg:'#8A8A8A', sidebarFg:'#F0F0F0' },
};

// ── Theme descriptors ─────────────────────────────────────────────────────

const THEMES: { value: Appearance; icon: LucideIcon; label: string; desc: string }[] = [
    { value: 'light',       icon: Sun,      label: 'Light',   desc: 'Clean & crisp'      },
    { value: 'dark',        icon: Moon,     label: 'Dark',    desc: 'Easy on the eyes'   },
    { value: 'brown',       icon: Coffee,   label: 'Brown',   desc: 'Warm & cozy'        },
    { value: 'blue',        icon: Sparkles, label: 'Blue',    desc: 'Deep & electric'    },
    { value: 'azure',       icon: Gem,      label: 'Azure',   desc: 'Fresh & polished'   },
    { value: 'green-dark',  icon: Leaf,     label: 'Forest',  desc: 'Rich & natural'     },
    { value: 'green-light', icon: Sprout,   label: 'Meadow',  desc: 'Fresh & calm'       },
    { value: 'system',      icon: Monitor,  label: 'System',  desc: 'Follows your OS'    },
];

// ── Mini app preview (sidebar + header + kanban mock) ─────────────────────

function AppPreview({ p }: { p: Palette }) {
    return (
        <div className="flex h-full w-full" style={{ background: p.bg }}>

            {/* ── Sidebar ── */}
            <div className="flex w-[54px] shrink-0 flex-col border-r"
                 style={{ background: p.sidebar, borderColor: p.border }}>

                {/* Logo row */}
                <div className="flex items-center gap-1.5 px-2.5 py-2.5">
                    <div className="size-3 rounded-[3px]" style={{ background: p.primary }} />
                    <div className="h-1.5 w-10 rounded-full" style={{ background: p.sidebarFg + '40' }} />
                </div>

                {/* Nav items — second one is "active" */}
                <div className="flex flex-col gap-1 px-1.5 pt-0.5">
                    {[false, true, false, false, false].map((active, i) => (
                        <div key={i}
                             className="flex items-center gap-1.5 rounded px-1 py-1"
                             style={{ background: active ? p.primary + '20' : 'transparent' }}>
                            <div className="size-2 rounded-sm"
                                 style={{ background: active ? p.primary : p.sidebarFg + '55' }} />
                            <div className="h-1 flex-1 rounded-full"
                                 style={{ background: active ? p.sidebarFg + '90' : p.sidebarFg + '30' }} />
                        </div>
                    ))}
                </div>
            </div>

            {/* ── Main area ── */}
            <div className="flex flex-1 flex-col overflow-hidden">

                {/* Topbar */}
                <div className="flex h-7 shrink-0 items-center gap-2 border-b px-2.5"
                     style={{ background: p.card, borderColor: p.border }}>
                    <div className="h-1.5 w-20 rounded-full" style={{ background: p.mutedFg + '40' }} />
                    <div className="ml-auto flex items-center gap-1.5">
                        <div className="h-3.5 w-12 rounded" style={{ background: p.primary + '22' }} />
                        <div className="size-4 rounded-full" style={{ background: p.primary + '1A' }} />
                    </div>
                </div>

                {/* Body */}
                <div className="flex flex-1 flex-col gap-2 overflow-hidden p-2">

                    {/* Stat strip */}
                    <div className="flex gap-1.5">
                        {[p.primary + 'BB', p.primary + '75', p.primary + 'AA', p.primary + '55'].map((col, i) => (
                            <div key={i} className="flex-1 rounded-md p-1.5"
                                 style={{ background: p.card, border: `1px solid ${p.border}` }}>
                                <div className="mb-1 h-1.5 w-4 rounded-full" style={{ background: col }} />
                                <div className="h-2 w-5 rounded" style={{ background: p.mutedFg + '30' }} />
                            </div>
                        ))}
                    </div>

                    {/* Kanban board */}
                    <div className="flex flex-1 gap-1.5 overflow-hidden rounded-md p-1.5"
                         style={{ background: p.card, border: `1px solid ${p.border}` }}>

                        {/* Column header label + cards */}
                        {[
                            { count: 3, shade: p.mutedFg + '55' },
                            { count: 2, shade: p.primary + 'AA' },
                            { count: 1, shade: p.primary + 'CC' },
                        ].map(({ count, shade }, ci) => (
                            <div key={ci} className="flex flex-1 flex-col gap-1 overflow-hidden">
                                {/* Column label */}
                                <div className="mb-0.5 flex items-center gap-1">
                                    <div className="h-1 rounded-full" style={{ width: '55%', background: shade }} />
                                    <div className="size-2.5 rounded-full"
                                         style={{ background: shade + '50', flexShrink: 0 }} />
                                </div>
                                {/* Issue cards */}
                                {Array.from({ length: count }).map((_, ii) => (
                                    <div key={ii} className="rounded p-1.5"
                                         style={{ background: p.muted, border: `1px solid ${p.border}` }}>
                                        <div className="mb-0.5 h-1 rounded-full"
                                             style={{ background: p.mutedFg + '40' }} />
                                        <div className="h-1 w-2/3 rounded-full"
                                             style={{ background: p.mutedFg + '25' }} />
                                    </div>
                                ))}
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}

/** System theme: diagonal light ╱ dark split */
function SystemPreview() {
    return (
        <div className="relative h-full w-full overflow-hidden">
            <div className="absolute inset-0"
                 style={{ clipPath: 'polygon(0 0, 58% 0, 42% 100%, 0 100%)' }}>
                <AppPreview p={P.light} />
            </div>
            <div className="absolute inset-0"
                 style={{ clipPath: 'polygon(58% 0, 100% 0, 100% 100%, 42% 100%)' }}>
                <AppPreview p={P.dark} />
            </div>
            {/* shimmer seam */}
            <div className="pointer-events-none absolute inset-0"
                 style={{ background: 'linear-gradient(to right,transparent 48%,rgba(180,180,180,.35) 50%,transparent 52%)' }} />
        </div>
    );
}

// ── Page ─────────────────────────────────────────────────────────────────

export default function Appearance() {
    const { appearance, updateAppearance } = useAppearance();

    return (
        <>
            <Head title="Appearance" />
            <h1 className="sr-only">Appearance</h1>

            <div className="space-y-8">
                <header>
                    <h2 className="mb-0.5 text-base font-medium">Appearance</h2>
                    <p className="text-sm text-muted-foreground">
                        Choose how No Ticket looks for you. Changes apply instantly.
                    </p>
                </header>

                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    {THEMES.map(({ value, icon: Icon, label, desc }) => {
                        const active = appearance === value;
                        return (
                            <button
                                key={value}
                                onClick={() => updateAppearance(value)}
                                className={cn(
                                    'group relative flex flex-col overflow-hidden rounded-xl border-2 text-left',
                                    'transition-all duration-200',
                                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2',
                                    active
                                        ? 'border-primary shadow-lg shadow-primary/10'
                                        : 'border-border/50 hover:border-border hover:shadow-md',
                                )}
                            >
                                {/* Active checkmark badge */}
                                {active && (
                                    <span className="absolute right-2.5 top-2.5 z-10 rounded-full bg-primary p-0.5 shadow-sm">
                                        <CheckCircle2 className="size-3 text-primary-foreground" />
                                    </span>
                                )}

                                {/* ── Preview area ── */}
                                <div className="h-36 w-full overflow-hidden">
                                    {value === 'system'
                                        ? <SystemPreview />
                                        : <AppPreview p={P[value]} />
                                    }
                                </div>

                                {/* ── Label footer ── */}
                                <div className="flex items-center gap-3 border-t border-border bg-card px-3.5 py-3">
                                    <div className={cn(
                                        'flex size-8 shrink-0 items-center justify-center rounded-lg transition-colors',
                                        active
                                            ? 'bg-primary text-primary-foreground'
                                            : 'bg-muted text-muted-foreground group-hover:bg-accent',
                                    )}>
                                        <Icon className="size-4" />
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <p className={cn(
                                            'text-sm font-semibold leading-tight',
                                            active && 'text-primary',
                                        )}>
                                            {label}
                                        </p>
                                        <p className="text-xs text-muted-foreground">{desc}</p>
                                    </div>
                                </div>
                            </button>
                        );
                    })}
                </div>
            </div>
        </>
    );
}

Appearance.layout = {
    breadcrumbs: [{ title: 'Appearance settings', href: editAppearance() }],
};
