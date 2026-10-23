import { Link, router, usePage } from '@inertiajs/react';
import Icon from '../../Components/Icon';
import Layout from '../../Components/Layout';
import { Alert, Button, Card } from '../../Components/ui';
import { useT } from '../../lib/i18n';

export default function Index({ templates, applications }) {
    const { t, date, pick } = useT();
    const { errors } = usePage().props;

    return (
        <Layout title={t('formsTitle')}>
            <h1 className="mb-1 text-2xl font-semibold">{t('formsTitle')}</h1>
            <p className="mb-5 text-muted">{t('formsIntro')}</p>

            {errors.form && (
                <Alert tone="danger" className="mb-5">
                    <p>{errors.form}</p>
                    <Link href="/plans" className="font-medium underline">
                        {t('getMore')}
                    </Link>
                </Alert>
            )}

            <ul className="mb-8 space-y-3">
                {templates.map((template) => (
                    <li key={template.slug}>
                        <Card className="flex items-center gap-4">
                            <span className="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-brand">
                                <Icon name="form" className="size-6" />
                            </span>
                            <span className="min-w-0 flex-1 text-lg font-semibold">{pick(template.name)}</span>
                            {template.draftId ? (
                                <Button href={`/applications/${template.draftId}`}>{t('resumeForm')}</Button>
                            ) : (
                                <Button onClick={() => router.post(`/forms/${template.slug}`)}>{t('startForm')}</Button>
                            )}
                        </Card>
                    </li>
                ))}
                <li>
                    <Card className="flex items-center gap-4">
                        <span className="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-brand">
                            <Icon name="papers" className="size-6" />
                        </span>
                        <span className="min-w-0 flex-1 text-lg font-semibold">{t('jobsTitle')}</span>
                        <Button href="/jobs">{t('open')}</Button>
                    </Card>
                </li>
            </ul>

            <p className="mb-8">
                <Link href="/assistant" className="font-medium text-brand underline">
                    {t('assistantTitle')}
                </Link>
            </p>

            {applications.length > 0 && (
                <section>
                    <h2 className="mb-3 text-lg font-semibold">{t('myApplications')}</h2>
                    <ul className="space-y-2">
                        {applications.map((application) => (
                            <li key={application.id}>
                                <Link
                                    href={`/applications/${application.id}`}
                                    className="flex items-center gap-3 rounded-xl border border-line bg-card p-4 hover:border-brand"
                                >
                                    <Icon
                                        name={application.status === 'completed' ? 'check' : 'clock'}
                                        className={`size-6 shrink-0 ${application.status === 'completed' ? 'text-ok' : 'text-warn'}`}
                                    />
                                    <span className="min-w-0 flex-1">
                                        <span className="block font-medium">{pick(application.name)}</span>
                                        <span className="text-sm text-muted">
                                            {application.status === 'completed'
                                                ? t('statusCompleted', { date: date(application.completedAt) })
                                                : t('statusDraft', { date: date(application.createdAt) })}
                                        </span>
                                    </span>
                                    <Icon name="arrow" className="size-5 shrink-0 text-muted" />
                                </Link>
                            </li>
                        ))}
                    </ul>
                </section>
            )}
        </Layout>
    );
}
