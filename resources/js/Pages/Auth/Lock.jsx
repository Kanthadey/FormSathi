import { router, useForm, usePage } from '@inertiajs/react';
import { AuthLayout } from '../../Components/Layout';
import Icon from '../../Components/Icon';
import { Button, CodeField } from '../../Components/ui';
import { useT } from '../../lib/i18n';

export default function Lock() {
    const { t, n } = useT();
    const { auth } = usePage().props;
    const form = useForm({ pin: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post('/unlock', { onError: () => form.reset() });
    };

    return (
        <AuthLayout title={t('lockedTitle')}>
            <div className="mb-4 inline-flex size-14 items-center justify-center rounded-2xl bg-brand-soft text-brand">
                <Icon name="lock" className="size-7" />
            </div>
            <h1 className="mb-2 text-3xl font-semibold">{t('lockedTitle')}</h1>
            <p className="mb-6 text-muted">{t('lockedHint')}</p>
            <p className="mb-6 font-medium">{n(auth.user.mobile)}</p>
            <form onSubmit={submit} className="space-y-5">
                <CodeField
                    id="pin"
                    type="password"
                    autoComplete="current-password"
                    label={t('pinLabel')}
                    value={form.data.pin}
                    onChange={(e) => form.setData('pin', e.target.value)}
                    error={form.errors.pin}
                    autoFocus
                    required
                />
                <Button type="submit" className="w-full" disabled={form.processing}>
                    {t('unlock')}
                </Button>
            </form>
            <button type="button" onClick={() => router.post('/logout')} className="mt-6 font-medium text-muted underline">
                {t('logout')}
            </button>
        </AuthLayout>
    );
}
