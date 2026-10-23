import { Link } from '@inertiajs/react';
import Icon from '../../Components/Icon';
import { PublicLayout } from '../../Components/Layout';
import { Button } from '../../Components/ui';
import { useT } from '../../lib/i18n';

const steps = [
    ['camera', 'how1', 'how1Text'],
    ['alert', 'how2', 'how2Text'],
    ['copy', 'how3', 'how3Text'],
];

const features = [
    { icon: 'papers', bn: 'ডিজিটাল ওয়ালেট', en: 'Digital wallet', textBn: 'এনআইডি, সনদ, পাসপোর্ট — মেয়াদ শেষের আগে এসএমএস রিমাইন্ডার।', textEn: 'NID, certificates, passport — SMS reminders before they expire.' },
    { icon: 'user', bn: 'মাস্টার প্রোফাইল', en: 'Master profile', textBn: 'বাংলা ও ইংরেজিতে সব তথ্য এক জায়গায়, প্রতিটির উৎস কাগজসহ।', textEn: 'Every detail in Bangla and English, each linked to its paper.' },
    { icon: 'form', bn: 'ই-পাসপোর্ট ফর্ম', en: 'E-passport form', textBn: 'ফর্মের ক্রমে প্রতিটি ঘর, এক ট্যাপে কপি, প্রিন্টযোগ্য সারাংশ।', textEn: 'Every field in the form’s order, one-tap copy, printable summary.' },
    { icon: 'papers', bn: 'চাকরির আবেদন কিট', en: 'Job application kit', textBn: 'বিজ্ঞাপন পেস্ট করুন — সেই চাকরির জন্য সিভি ও কভার লেটার।', textEn: 'Paste an advert — get a CV and cover letter for that job.' },
];

const trust = ['trust1', 'trust2', 'trust3', 'trust4', 'trust5'];

export default function Home({ products }) {
    const { t, n, pick, locale } = useT();

    return (
        <PublicLayout title={t('heroTitle')} wide>
            <section className="bg-brand-soft">
                <div className="mx-auto max-w-5xl px-4 py-12 sm:py-20">
                    <h1 className="mb-4 max-w-2xl text-3xl leading-tight font-semibold text-brand-strong sm:text-5xl">{t('heroTitle')}</h1>
                    <p className="mb-8 max-w-2xl text-lg text-ink/80">{t('heroText')}</p>
                    <div className="flex flex-wrap gap-3">
                        <Button href="/login" className="px-7 text-lg">
                            {t('getStarted')}
                            <Icon name="arrow" />
                        </Button>
                        <Button href="/pricing" variant="secondary">
                            {t('pricingTitle')}
                        </Button>
                    </div>
                    <p className="mt-6 max-w-2xl text-sm text-muted">{t('notGovt')}</p>
                </div>
            </section>

            <section className="mx-auto max-w-5xl px-4 py-12">
                <h2 className="mb-6 text-2xl font-semibold">{t('howTitle')}</h2>
                <ol className="grid gap-4 sm:grid-cols-3">
                    {steps.map(([icon, title, text], i) => (
                        <li key={title} className="rounded-2xl border border-line bg-card p-5">
                            <span className="mb-3 inline-flex size-11 items-center justify-center rounded-xl bg-brand text-white">
                                <Icon name={icon} className="size-6" />
                            </span>
                            <p className="mb-1 text-sm text-muted">{n(i + 1)}</p>
                            <h3 className="mb-1 text-lg font-semibold">{t(title)}</h3>
                            <p className="text-muted">{t(text)}</p>
                        </li>
                    ))}
                </ol>
            </section>

            <section className="mx-auto max-w-5xl px-4 pb-12">
                <h2 className="mb-6 text-2xl font-semibold">{t('whatTitle')}</h2>
                <ul className="grid gap-4 sm:grid-cols-2">
                    {features.map((f) => (
                        <li key={f.en} className="flex gap-4 rounded-2xl border border-line bg-card p-5">
                            <span className="inline-flex size-11 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-brand">
                                <Icon name={f.icon} className="size-6" />
                            </span>
                            <span>
                                <span className="block text-lg font-semibold">{f[locale]}</span>
                                <span className="text-muted">{locale === 'bn' ? f.textBn : f.textEn}</span>
                            </span>
                        </li>
                    ))}
                </ul>
            </section>

            <section className="bg-ink text-white">
                <div className="mx-auto max-w-5xl px-4 py-12">
                    <h2 className="mb-6 flex items-center gap-3 text-2xl font-semibold">
                        <Icon name="shield" className="size-7" />
                        {t('trustTitle')}
                    </h2>
                    <ul className="grid gap-3 sm:grid-cols-2">
                        {trust.map((key) => (
                            <li key={key} className="flex items-start gap-2">
                                <Icon name="check" className="mt-1 size-5 shrink-0 text-[#7fd1c3]" />
                                {t(key)}
                            </li>
                        ))}
                    </ul>
                    <Link href="/privacy" className="mt-6 inline-block font-medium text-[#7fd1c3] underline">
                        {t('privacyTitle')}
                    </Link>
                </div>
            </section>

            <section className="mx-auto max-w-5xl px-4 py-12 text-center">
                <h2 className="mb-2 text-2xl font-semibold">{t('pricingTitle')}</h2>
                <p className="mb-6 text-muted">
                    {t('planFree')} · {pick(products.form_pack)} ৳{n(products.form_pack.amount)} · {t('planPremium')} ৳{n(products.premium_month.amount)}/
                    {t('perMonth')}
                </p>
                <Button href="/login" className="px-7 text-lg">
                    {t('getStarted')}
                </Button>
            </section>
        </PublicLayout>
    );
}
