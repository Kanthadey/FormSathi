import { useForm } from '@inertiajs/react';
import { AuthLayout } from '../../Components/Layout';
import { Button, CodeField } from '../../Components/ui';
import { useT } from '../../lib/i18n';

/** Sets the PIN on first sign-in, asks for it after that. */
export default function Pin({ mode }) {
    const { t } = useT();
    const setting = mode === 'set';
    const form = useForm({ pin: '', pin_confirmation: '' });
    const title = setting ? t('pinSetTitle') : t('pinEnterTitle');

    const submit = (e) => {
        e.preventDefault();
        form.post('/login/pin', { onError: () => form.reset() });
    };

    return (
        <AuthLayout title={title}>
            <h1 className="mb-2 text-3xl font-semibold">{title}</h1>
            {setting && <p className="mb-6 text-muted">{t('pinSetHint')}</p>}
            <form onSubmit={submit} className="mt-6 space-y-5">
                <CodeField
                    id="pin"
                    type="password"
                    autoComplete={setting ? 'new-password' : 'current-password'}
                    label={t('pinLabel')}
                    value={form.data.pin}
                    onChange={(e) => form.setData('pin', e.target.value)}
                    error={form.errors.pin}
                    autoFocus
                    required
                />
                {setting && (
                    <CodeField
                        id="pin_confirmation"
                        type="password"
                        autoComplete="new-password"
                        label={t('pinAgain')}
                        value={form.data.pin_confirmation}
                        onChange={(e) => form.setData('pin_confirmation', e.target.value)}
                        required
                    />
                )}
                <Button type="submit" className="w-full" disabled={form.processing}>
                    {t('continue')}
                </Button>
            </form>
        </AuthLayout>
    );
}
