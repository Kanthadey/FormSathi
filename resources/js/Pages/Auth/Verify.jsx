import { Link, router, useForm, usePage } from '@inertiajs/react';
import { AuthLayout } from '../../Components/Layout';
import { Alert, Button, CodeField } from '../../Components/ui';
import { useT } from '../../lib/i18n';

export default function Verify({ mobile }) {
    const { t, n } = useT();
    const { flash } = usePage().props;
    const form = useForm({ code: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post('/login/verify', { onError: () => form.reset('code') });
    };

    return (
        <AuthLayout title={t('codeTitle')}>
            <h1 className="mb-2 text-3xl font-semibold">{t('codeTitle')}</h1>
            <p className="mb-6 text-muted">{t('codeSentTo', { mobile: n(mobile) })}</p>

            {flash.devOtp && (
                <Alert tone="warn" className="mb-5">
                    {t('devOtp', { code: flash.devOtp })}
                </Alert>
            )}
            {flash.status && (
                <Alert tone="success" className="mb-5">
                    {flash.status}
                </Alert>
            )}

            <form onSubmit={submit} className="space-y-5">
                <CodeField
                    id="code"
                    label={t('codeLabel')}
                    value={form.data.code}
                    onChange={(e) => form.setData('code', e.target.value)}
                    error={form.errors.code}
                    autoFocus
                    required
                />
                <Button type="submit" className="w-full" disabled={form.processing}>
                    {t('verify')}
                </Button>
            </form>

            <div className="mt-6 flex flex-wrap justify-between gap-3">
                <button type="button" className="font-medium text-brand underline" onClick={() => router.post('/login/resend')}>
                    {t('resend')}
                </button>
                <Link href="/login" className="font-medium text-muted underline">
                    {t('changeNumber')}
                </Link>
            </div>
        </AuthLayout>
    );
}
