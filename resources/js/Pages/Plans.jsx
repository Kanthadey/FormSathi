import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Icon from '../Components/Icon';
import Layout from '../Components/Layout';
import { Alert, Button, Card } from '../Components/ui';
import { useT } from '../lib/i18n';

const features = { form_pack: 'featFormPack', premium_month: 'featPremium', premium_year: 'featPremium' };
const periods = { form_pack: 'perApplication', premium_month: 'perMonth', premium_year: 'perYear' };

export default function Plans({ plan, products, payments }) {
    const { t, n, date, pick } = useT();
    const { errors } = usePage().props;
    const [buying, setBuying] = useState(null);

    const buy = (product) => {
        setBuying(product);
        router.post('/payments', { product }, { onFinish: () => setBuying(null) });
    };

    return (
        <Layout title={t('plansTitle')}>
            <h1 className="mb-5 text-2xl font-semibold">{t('plansTitle')}</h1>

            {errors.payment && (
                <Alert tone="danger" className="mb-5">
                    {errors.payment}
                </Alert>
            )}

            <Card className="mb-6">
                <p className="text-sm text-muted">{t('currentPlan')}</p>
                <p className="mb-2 text-2xl font-semibold">
                    {plan.premium ? t('planPremium') : t('planFree')}
                    {plan.premium && <span className="ml-2 text-base font-normal text-muted">{t('premiumUntil', { date: date(plan.premiumUntil) })}</span>}
                </p>
                <ul className="space-y-1 text-muted">
                    {!plan.premium && <li>{t('freeLeft', { left: plan.freeAssistsLeft, total: plan.freeAssistsPerMonth })}</li>}
                    {plan.credits > 0 && <li>{t('creditsLeft', { count: plan.credits })}</li>}
                    <li>{t('papersUsed', { used: plan.documentsUsed, limit: plan.documentLimit })}</li>
                </ul>
            </Card>

            <ul className="mb-8 space-y-3">
                {Object.entries(products).map(([key, product]) => (
                    <li key={key}>
                        <Card className={key === 'premium_year' ? 'border-brand' : ''}>
                            <div className="mb-2 flex items-baseline justify-between gap-3">
                                <h2 className="text-lg font-semibold">{pick(product)}</h2>
                                <p className="text-right">
                                    <span className="text-2xl font-semibold">৳{n(product.amount)}</span>{' '}
                                    <span className="text-sm text-muted">{t(periods[key])}</span>
                                </p>
                            </div>
                            <p className="mb-4 text-muted">{t(features[key])}</p>
                            <Button className="w-full bg-[#e2136e] hover:bg-[#c10f5d]" disabled={buying !== null} onClick={() => buy(key)}>
                                {buying === key ? t('saving') : t('payWithBkash')}
                            </Button>
                        </Card>
                    </li>
                ))}
            </ul>

            {payments.length > 0 && (
                <section>
                    <h2 className="mb-3 text-lg font-semibold">{t('paymentHistory')}</h2>
                    <ul className="divide-y divide-line rounded-xl border border-line bg-card">
                        {payments.map((p) => (
                            <li key={p.id} className="flex items-center justify-between gap-3 p-4">
                                <span className="min-w-0">
                                    <span className="block font-medium">{pick(products[p.product])}</span>
                                    <span className="text-sm text-muted">
                                        {date(p.date)}
                                        {p.trxId && <span lang="en"> · TrxID {p.trxId}</span>}
                                    </span>
                                </span>
                                <span className="text-right">
                                    <span className="block font-medium">৳{n(p.amount)}</span>
                                    <span className={`inline-flex items-center gap-1 text-sm ${p.status === 'completed' ? 'text-ok' : 'text-muted'}`}>
                                        {p.status === 'completed' && <Icon name="check" className="size-4" />}
                                        {t(`payStatus_${p.status}`)}
                                    </span>
                                </span>
                            </li>
                        ))}
                    </ul>
                </section>
            )}
        </Layout>
    );
}
