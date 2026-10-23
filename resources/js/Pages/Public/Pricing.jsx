import Icon from '../../Components/Icon';
import { PublicLayout } from '../../Components/Layout';
import { Button } from '../../Components/ui';
import { useT } from '../../lib/i18n';

export default function Pricing({ products }) {
    const { t, n, pick } = useT();

    const plans = [
        { name: t('planFree'), price: 0, period: null, feature: t('featFree') },
        { name: pick(products.form_pack), price: products.form_pack.amount, period: t('perApplication'), feature: t('featFormPack') },
        { name: pick(products.premium_month), price: products.premium_month.amount, period: t('perMonth'), feature: t('featPremium') },
        { name: pick(products.premium_year), price: products.premium_year.amount, period: t('perYear'), feature: t('featPremium'), highlight: true },
    ];

    return (
        <PublicLayout title={t('pricingTitle')}>
            <h1 className="mb-6 text-3xl font-semibold">{t('pricingTitle')}</h1>
            <ul className="mb-6 space-y-3">
                {plans.map((plan) => (
                    <li key={plan.name} className={`rounded-2xl border bg-card p-5 ${plan.highlight ? 'border-2 border-brand' : 'border-line'}`}>
                        <div className="mb-1 flex items-baseline justify-between gap-3">
                            <h2 className="text-lg font-semibold">{plan.name}</h2>
                            <p>
                                <span className="text-2xl font-semibold">৳{n(plan.price)}</span>
                                {plan.period && <span className="text-sm text-muted"> {plan.period}</span>}
                            </p>
                        </div>
                        <p className="flex items-start gap-2 text-muted">
                            <Icon name="check" className="mt-1 size-4 shrink-0 text-ok" />
                            {plan.feature}
                        </p>
                    </li>
                ))}
            </ul>
            <p className="mb-6 text-muted">{t('trust5')}</p>
            <Button href="/login" className="w-full">
                {t('getStarted')}
            </Button>
        </PublicLayout>
    );
}
