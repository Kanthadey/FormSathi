import { PublicLayout } from '../../Components/Layout';
import { Alert } from '../../Components/ui';
import { useT } from '../../lib/i18n';

/** Shared shell for the privacy policy and terms: sections of {bn, en} text. */
export default function LegalPage({ titleKey, updated, sections }) {
    const { t, date, locale } = useT();

    return (
        <PublicLayout title={t(titleKey)}>
            <h1 className="mb-1 text-3xl font-semibold">{t(titleKey)}</h1>
            <p className="mb-4 text-sm text-muted">{t('lastUpdated', { date: date(updated) })}</p>
            <Alert tone="warn" className="mb-6">
                {t('draftNotice')}
            </Alert>
            <div className="space-y-6">
                {sections.map((section) => (
                    <section key={section.title.en}>
                        <h2 className="mb-2 text-xl font-semibold">{section.title[locale]}</h2>
                        <ul className="list-disc space-y-1.5 pl-5">
                            {section.points.map((point) => (
                                <li key={point.en}>{point[locale]}</li>
                            ))}
                        </ul>
                    </section>
                ))}
            </div>
        </PublicLayout>
    );
}
