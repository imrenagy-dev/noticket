import { useSyncExternalStore } from 'react';

export type ResolvedAppearance = 'light' | 'dark';
export type Appearance = ResolvedAppearance | 'system' | 'brown' | 'blue' | 'azure' | 'green-dark' | 'green-light' | 'green-dim' | 'dark-dim' | 'brown-dim' | 'blue-dim';

// ── Per-theme favicon paths ────────────────────────────────────────────────
type FavSet = { ico: string; p32: string; svg: string; apple: string };

const FAVICON: Record<Appearance, FavSet> = {
    system:      { ico: '/favicon.ico',        p32: '/favicon-32x32.png',    svg: '/favicon.svg',        apple: '/apple-touch-icon.png'     },
    dark:        { ico: '/favicon.ico',        p32: '/favicon-32x32.png',    svg: '/favicon.svg',        apple: '/apple-touch-icon.png'     },
    light:       { ico: '/favicons/light.ico', p32: '/favicons/light-32.png',svg: '/favicons/light.svg', apple: '/favicons/light-apple.png' },
    brown:       { ico: '/favicons/brown.ico', p32: '/favicons/brown-32.png',svg: '/favicons/brown.svg', apple: '/favicons/brown-apple.png' },
    blue:        { ico: '/favicons/blue.ico',  p32: '/favicons/blue-32.png', svg: '/favicons/blue.svg',  apple: '/favicons/blue-apple.png'  },
    azure:       { ico: '/favicons/azure.ico', p32: '/favicons/azure-32.png',svg: '/favicons/azure.svg', apple: '/favicons/azure-apple.png' },
    'green-dark':  { ico: '/favicon.ico',        p32: '/favicon-32x32.png',    svg: '/favicon.svg',        apple: '/apple-touch-icon.png'     },
    'green-light': { ico: '/favicons/light.ico', p32: '/favicons/light-32.png',svg: '/favicons/light.svg', apple: '/favicons/light-apple.png' },
    'green-dim':   { ico: '/favicon.ico',        p32: '/favicon-32x32.png',    svg: '/favicon.svg',        apple: '/apple-touch-icon.png'     },
    'dark-dim':    { ico: '/favicon.ico',        p32: '/favicon-32x32.png',    svg: '/favicon.svg',        apple: '/apple-touch-icon.png'     },
    'brown-dim':   { ico: '/favicon.ico',        p32: '/favicon-32x32.png',    svg: '/favicon.svg',        apple: '/apple-touch-icon.png'     },
    'blue-dim':    { ico: '/favicon.ico',        p32: '/favicon-32x32.png',    svg: '/favicon.svg',        apple: '/apple-touch-icon.png'     },
};

const setFav = (key: string, href: string): void => {
    document.querySelector<HTMLLinkElement>(`link[data-fav="${key}"]`)?.setAttribute('href', href);
};

const applyFavicon = (appearance: Appearance): void => {
    const f = FAVICON[appearance];
    setFav('ico',   f.ico);
    setFav('p32',   f.p32);
    setFav('svg',   f.svg);
    setFav('apple', f.apple);
};

export type UseAppearanceReturn = {
    readonly appearance: Appearance;
    readonly resolvedAppearance: ResolvedAppearance;
    readonly updateAppearance: (mode: Appearance) => void;
};

const listeners = new Set<() => void>();
let currentAppearance: Appearance = 'system';

const prefersDark = (): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const setCookie = (name: string, value: string, days = 365): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const getStoredAppearance = (): Appearance => {
    if (typeof window === 'undefined') {
        return 'system';
    }

    return (localStorage.getItem('appearance') as Appearance) || 'system';
};

const isDarkMode = (appearance: Appearance): boolean => {
    return appearance === 'dark' || appearance === 'brown' || appearance === 'blue'
        || appearance === 'green-dark' || appearance === 'green-dim'
        || appearance === 'dark-dim' || appearance === 'brown-dim' || appearance === 'blue-dim'
        || (appearance === 'system' && prefersDark());
};

const applyTheme = (appearance: Appearance): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const isDark = isDarkMode(appearance);

    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.classList.toggle('theme-brown', appearance === 'brown');
    document.documentElement.classList.toggle('theme-blue', appearance === 'blue');
    document.documentElement.classList.toggle('theme-azure', appearance === 'azure');
    document.documentElement.classList.toggle('theme-green-dark',  appearance === 'green-dark');
    document.documentElement.classList.toggle('theme-green-light', appearance === 'green-light');
    document.documentElement.classList.toggle('theme-green-dim',   appearance === 'green-dim');
    document.documentElement.classList.toggle('theme-dark-dim',    appearance === 'dark-dim');
    document.documentElement.classList.toggle('theme-brown-dim',   appearance === 'brown-dim');
    document.documentElement.classList.toggle('theme-blue-dim',    appearance === 'blue-dim');
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';

    applyFavicon(appearance);
};

const subscribe = (callback: () => void) => {
    listeners.add(callback);

    return () => listeners.delete(callback);
};

const notify = (): void => listeners.forEach((listener) => listener());

const mediaQuery = (): MediaQueryList | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const handleSystemThemeChange = (): void => applyTheme(currentAppearance);

export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    if (!localStorage.getItem('appearance')) {
        localStorage.setItem('appearance', 'system');
        setCookie('appearance', 'system');
    }

    currentAppearance = getStoredAppearance();
    applyTheme(currentAppearance);

    // Set up system theme change listener
    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
}

export function useAppearance(): UseAppearanceReturn {
    const appearance: Appearance = useSyncExternalStore(
        subscribe,
        () => currentAppearance,
        () => 'system',
    );

    const resolvedAppearance: ResolvedAppearance = isDarkMode(appearance)
        ? 'dark'
        : 'light';

    const updateAppearance = (mode: Appearance): void => {
        currentAppearance = mode;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', mode);

        // Store in cookie for SSR...
        setCookie('appearance', mode);

        applyTheme(mode);
        notify();
    };

    return { appearance, resolvedAppearance, updateAppearance } as const;
}
