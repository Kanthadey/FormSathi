import { router } from '@inertiajs/react';
import { AuthLayout } from '../Components/Layout';
import { Alert, Button, Card } from '../Components/ui';
import { useT } from '../lib/i18n';

/** Development stand-in for bKash's payment page. */
export default function FakeCheckout({ payment }) {
    const { t, n, pick } = useT();
    const back = (status) => router.get('/payments/callback', { paymentID: payment.paymentId, status });

    return (
        <AuthLayout title={t('fakeCheckoutTitle')}>
            <h1 className="mb-4 text-2xl font-semibold">{t('fakeCheckoutTitle')}</h1>
            <Alert tone="warn" className="mb-5">
                {t('fakeCheckoutHint')}
            </Alert>
            <Card className="mb-5">
                <p className="font-medium">{pick(payment.product)}</p>
                <p className="text-3xl font-semibold">৳{n(payment.amount)}</p>
                <p lang="en" className="text-sm text-muted">
                    {payment.paymentId}
                </p>
            </Card>
            <div className="space-y-3">
                <Button className="w-full" onClick={() => back('success')}>
                    {t('fakePay')}
                </Button>
                <Button variant="secondary" className="w-full" onClick={() => back('cancel')}>
                    {t('fakeCancel')}
                </Button>
            </div>
        </AuthLayout>
    );
}
