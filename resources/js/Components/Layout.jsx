import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { useT } from '../lib/i18n';
import Icon from './Icon';
import { Alert } from './ui';

const nav = [
    { href: '/dashboard', label: 'navHome', icon: 'home' },
    { href: '/documents', label: 'navPapers', icon: 'papers' },
    { href: '/forms', label: 'navForms', icon: 'form', also: '/applications' },
    { href: '/profile', label: 'navProfile', icon: 'user' },
    { href: '/settings', label: 'navSettings', icon: 'settings' },
];

const isActive = (url, item) => url.startsWith(item.href) || (item.also && url.startsWith(item.also));

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

function Footer() {
    const { company } = usePage().props;
    const { t } = useT();
    return (
        <footer className="mx-auto max-w-2xl px-4 pt-8 pb-28 text-sm text-muted sm:pb-10">
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
