import { useForm, usePage } from '@inertiajs/react';
import { AuthLayout } from '../../Components/Layout';
import { Alert, Button, TextField } from '../../Components/ui';
import { useT } from '../../lib/i18n';

export default function Mobile() {
    const { t } = useT();
    const { flash } = usePage().props;
    const form = useForm({ mobile: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post('/login');
    };

    return (
        <AuthLayout title={t('welcome')}>
            {flash.status && (
                <Alert tone="success" className="mb-5">
                    {flash.status}
                </Alert>
            )}
            <h1 className="mb-2 text-3xl font-semibold">{t('welcome')}</h1>
            <p className="mb-8 text-muted">{t('mobileIntro')}</p>
            <form onSubmit={submit} className="space-y-5">
                <TextField
                    id="mobile"
                    type="tel"
                    inputMode="tel"
                    autoComplete="tel"
                    placeholder="01XXXXXXXXX"
                    label={t('mobileLabel')}
                    hint={t('mobileHint')}
                    value={form.data.mobile}
                    onChange={(e) => form.setData('mobile', e.target.value)}
                    error={form.errors.mobile}
                    autoFocus
                    required
                />
                <Button type="submit" className="w-full" disabled={form.processing}>
                    {t('sendCode')}
                </Button>
            </form>
        </AuthLayout>
    );
}
