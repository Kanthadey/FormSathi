import { Link, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Icon from '../Components/Icon';
import Layout from '../Components/Layout';
import { Button, Card, CodeField } from '../Components/ui';
import { useT } from '../lib/i18n';

export default function Settings() {
    const { t, n, locale } = useT();
    const { auth } = usePage().props;
    const [deleting, setDeleting] = useState(false);
    const form = useForm({ pin: '' });

    return (
        <Layout title={t('settingsTitle')}>
            <h1 className="mb-5 text-2xl font-semibold">{t('settingsTitle')}</h1>

            <Card className="mb-4">
                <h2 className="mb-3 text-lg font-semibold">{t('language')}</h2>
                <div className="grid grid-cols-2 gap-2">
                    {[
                        ['bn', 'বাংলা'],
                        ['en', 'English'],
                    ].map(([code, label]) => (
                        <Button
                            key={code}
                            variant={locale === code ? 'primary' : 'secondary'}
                            aria-pressed={locale === code}
                            onClick={() => router.post('/locale', { locale: code })}
                        >
                            {label}
                        </Button>
                    ))}
                </div>
            </Card>

            <nav className="mb-4 divide-y divide-line overflow-hidden rounded-2xl border border-line bg-card" aria-label={t('more')}>
                {[
                    ['/plans', 'plansTitle', auth.user.premium ? t('planPremium') : t('planFree')],
                    ['/career', 'careerTitle'],
                    ['/help', 'helpTitle'],
                    ['/privacy', 'privacyTitle'],
                    ['/terms', 'termsTitle'],
                ].map(([href, label, note]) => (
                    <Link key={href} href={href} className="flex items-center justify-between gap-3 px-5 py-4 hover:bg-surface">
                        <span className="font-medium">{t(label)}</span>
                        <span className="flex items-center gap-2 text-muted">
                            {note}
                            <Icon name="arrow" className="size-5" />
                        </span>
                    </Link>
                ))}
            </nav>

            <Card className="mb-4">
                <h2 className="mb-1 text-lg font-semibold">{t('account')}</h2>
                <p className="mb-4 text-muted">{n(auth.user.mobile)}</p>
                <Button variant="secondary" onClick={() => router.post('/logout')}>
                    {t('logout')}
                </Button>
            </Card>

            <Card className="border-danger">
                <h2 className="mb-1 text-lg font-semibold text-danger">{t('deleteAll')}</h2>
                <p className="mb-4 text-muted">{t('deleteAllHint')}</p>
                {deleting ? (
                    <form
                        className="space-y-4"
                        onSubmit={(e) => {
                            e.preventDefault();
                            form.delete('/account', { onError: () => form.reset() });
                        }}
                    >
                        <CodeField
                            id="delete-pin"
                            type="password"
                            autoComplete="current-password"
                            label={t('pinLabel')}
                            value={form.data.pin}
                            onChange={(e) => form.setData('pin', e.target.value)}
                            error={form.errors.pin}
                            autoFocus
                            required
                        />
                        <div className="flex flex-wrap gap-2">
                            <Button type="submit" variant="danger" disabled={form.processing}>
                                <Icon name="trash" />
                                {t('deleteAllConfirm')}
                            </Button>
                            <Button type="button" variant="secondary" onClick={() => setDeleting(false)}>
                                {t('cancel')}
                            </Button>
                        </div>
                    </form>
                ) : (
                    <Button variant="danger" onClick={() => setDeleting(true)}>
                        <Icon name="trash" />
                        {t('deleteAll')}
                    </Button>
                )}
            </Card>
        </Layout>
    );
}
