import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { useT } from '../lib/i18n';
import Icon from './Icon';
import { Alert } from './ui';

const nav = [
    { href: '/dashboard', label: 'navHome', icon: 'home' },
    { href: '/documents', label: 'navPapers', icon: 'papers' },
    { href: '/forms', label: 'navForms', icon: 'form', also: ['/applications', '/jobs', '/assistant'] },
    { href: '/profile', label: 'navProfile', icon: 'user', also: ['/career'] },
    { href: '/settings', label: 'navSettings', icon: 'settings', also: ['/plans', '/help'] },
];

const isActive = (url, item) => url.startsWith(item.href) || (item.also ?? []).some((path) => url.startsWith(path));

function LanguageToggle() {
    const { t, locale } = useT();
    return (
        <button
            type="button"
            onClick={() => router.post('/locale', { locale: locale === 'bn' ? 'en' : 'bn' }, { preserveScroll: true })}
            className="rounded-lg px-3 py-1.5 text-sm font-medium text-brand hover:bg-brand-soft"
        >
            {t('switchLang')}
        </button>
    );
}

function Footer({ className = 'pb-28 sm:pb-10' }) {
    const { company } = usePage().props;
    const { t } = useT();
    return (
        <footer className={`mx-auto max-w-2xl px-4 pt-8 text-sm text-muted ${className}`}>
            <nav className="mb-3 flex flex-wrap gap-x-4 gap-y-1" aria-label="Footer">
                <Link href="/pricing" className="underline">{t('pricingTitle')}</Link>
                <Link href="/privacy" className="underline">{t('privacyTitle')}</Link>
                <Link href="/terms" className="underline">{t('termsTitle')}</Link>
                <Link href="/help" className="underline">{t('navHelp')}</Link>
            </nav>
            <p className="mb-2">{t('notGovt')}</p>
            <p>
                {company.name} · {company.address} · {t('helpline')}:{' '}
                <a href={`tel:${company.helpline.replace(/[^\d+]/g, '')}`} className="underline">
                    {company.helpline}
                </a>
            </p>
        </footer>
    );
}

/** Locks the app in the browser too, so papers leave the screen. */
function useIdleLock(minutes) {
    useEffect(() => {
        let timer;
        const reset = () => {
            clearTimeout(timer);
            timer = setTimeout(() => router.post('/lock'), minutes * 60 * 1000);
        };
        const events = ['pointerdown', 'keydown', 'scroll', 'touchstart'];
        events.forEach((e) => window.addEventListener(e, reset, { passive: true }));
        reset();
        return () => {
            clearTimeout(timer);
            events.forEach((e) => window.removeEventListener(e, reset));
        };
    }, [minutes]);
}

export default function Layout({ title, children }) {
    const { url, props } = usePage();
    const { t } = useT();
    useIdleLock(props.idleLockMinutes);

    return (
        <>
            <Head title={title} />
            <header className="sticky top-0 z-10 border-b border-line bg-surface/95 backdrop-blur">
                <div className="mx-auto flex max-w-2xl items-center justify-between gap-2 px-4 py-3">
                    <Link href="/dashboard" className="text-xl font-semibold text-brand">
                        {t('appName')}
                    </Link>
                    <nav className="hidden items-center gap-1 sm:flex" aria-label="Main">
                        {nav.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`rounded-lg px-3 py-1.5 font-medium ${
                                    isActive(url, item) ? 'bg-brand-soft text-brand-strong' : 'text-muted hover:text-ink'
                                }`}
                            >
                                {t(item.label)}
                            </Link>
                        ))}
                    </nav>
                    <div className="flex items-center gap-1">
                        {props.auth.user?.isAdmin && (
                            <Link href="/admin" className="rounded-lg px-2 py-1.5 text-sm font-medium text-warn hover:bg-warn-soft">
                                Admin
                            </Link>
                        )}
                        <LanguageToggle />
                        <button
                            type="button"
                            onClick={() => router.post('/lock')}
                            className="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-muted hover:text-ink"
                        >
                            <Icon name="lock" className="size-4" />
                            {t('lockNow')}
                        </button>
                    </div>
                </div>
            </header>

            <main className="mx-auto max-w-2xl px-4 pt-5">
                {props.flash.status && (
                    <Alert tone="success" className="mb-5">
                        {props.flash.status}
                    </Alert>
                )}
                {props.flash.error && (
                    <Alert tone="danger" className="mb-5">
                        {props.flash.error}
                    </Alert>
                )}
                {children}
            </main>

            <Footer />

            {/* Bottom bar on phones: icons always carry a text label. */}
            <nav
                className="fixed inset-x-0 bottom-0 z-10 grid grid-cols-5 border-t border-line bg-card pb-[env(safe-area-inset-bottom)] sm:hidden"
                aria-label="Main"
            >
                {nav.map((item) => {
                    const active = isActive(url, item);
                    return (
                        <Link
                            key={item.href}
                            href={item.href}
                            aria-current={active ? 'page' : undefined}
                            className={`flex flex-col items-center gap-0.5 py-2 text-xs font-medium ${active ? 'text-brand' : 'text-muted'}`}
                        >
                            <Icon name={item.icon} className="size-6" />
                            {t(item.label)}
                        </Link>
                    );
                })}
            </nav>
        </>
    );
}

/** Centred single-card layout for sign-in and the lock screen. */
export function AuthLayout({ title, children }) {
    const { t } = useT();
    return (
        <>
            <Head title={title} />
            <div className="mx-auto flex min-h-screen max-w-md flex-col px-4">
                <div className="flex items-center justify-between py-4">
                    <span className="text-xl font-semibold text-brand">{t('appName')}</span>
                    <LanguageToggle />
                </div>
                <main className="flex-1 pt-6">{children}</main>
                <p className="py-6 text-sm text-muted">{t('notGovt')}</p>
            </div>
        </>
    );
}

/** Pages anyone can read: landing, pricing, privacy, terms. */
export function PublicLayout({ title, children, wide = false }) {
    const { t } = useT();
    const { auth } = usePage().props;

    return (
        <>
            <Head title={title} />
            <header className="border-b border-line bg-surface">
                <div className={`mx-auto flex items-center justify-between gap-2 px-4 py-3 ${wide ? 'max-w-5xl' : 'max-w-2xl'}`}>
                    <Link href="/" className="text-xl font-semibold text-brand">
                        {t('appName')}
                    </Link>
                    <div className="flex items-center gap-1">
                        <Link href="/pricing" className="hidden rounded-lg px-3 py-1.5 font-medium text-muted hover:text-ink sm:inline-block">
                            {t('pricingTitle')}
                        </Link>
                        <LanguageToggle />
                        <Link
                            href={auth.user ? '/dashboard' : '/login'}
                            className="rounded-lg bg-brand px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-strong"
                        >
                            {auth.user ? t('navHome') : t('login')}
                        </Link>
                    </div>
                </div>
            </header>
            <main className={wide ? '' : 'mx-auto max-w-2xl px-4 pt-6'}>{children}</main>
            <Footer className="pb-10" />
        </>
    );
}
