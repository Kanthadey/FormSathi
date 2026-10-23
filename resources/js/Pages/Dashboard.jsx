import { Link } from '@inertiajs/react';
import Icon from '../Components/Icon';
import Layout from '../Components/Layout';
import { Button, Card } from '../Components/ui';
import { useT } from '../lib/i18n';

function NextStep({ documentCount, needsReview, completeness }) {
    const { t } = useT();

    if (documentCount === 0) {
        return (
            <Card className="border-brand bg-brand-soft">
                <p className="mb-1 text-sm font-medium text-brand-strong">{t('nextStep')}</p>
                <h2 className="mb-1 text-xl font-semibold">{t('startWithNid')}</h2>
                <p className="mb-4 text-muted">{t('startWithNidHint')}</p>
                <Button href="/documents/new?type=nid">
                    <Icon name="camera" />
                    {t('addPaper')}
                </Button>
            </Card>
        );
    }

    if (needsReview > 0) {
        return (
            <Link href="/profile" className="block rounded-2xl border-2 border-danger bg-danger-soft p-5 text-danger">
                <span className="flex items-center gap-3 text-lg font-semibold">
                    <Icon name="alert" className="size-6 shrink-0" />
                    {t('reviewMismatches', { count: needsReview })}
                </span>
            </Link>
        );
    }

    if (completeness < 100) {
        return (
            <Link href="/profile" className="flex items-center justify-between rounded-2xl border border-line bg-card p-5">
                <span className="text-lg font-semibold">{t('fillProfile')}</span>
                <Icon name="arrow" className="size-6 text-brand" />
            </Link>
        );
    }

    return null;
}

export default function Dashboard({ completeness, documentCount, needsReview, expiring, types, draft }) {
    const { t, n, date, pick } = useT();

    return (
        <Layout title={t('navHome')}>
            <h1 className="mb-5 text-2xl font-semibold">{t('hello')}</h1>

            <div className="mb-5 grid grid-cols-2 gap-3">
                <Card>
                    <p className="text-sm text-muted">{t('profileReady')}</p>
                    <p className="text-3xl font-semibold">{n(completeness)}%</p>
                    <div className="mt-2 h-2 overflow-hidden rounded-full bg-line" aria-hidden="true">
                        <div className="h-full rounded-full bg-brand" style={{ width: `${completeness}%` }} />
                    </div>
                </Card>
                <Card>
                    <p className="text-sm text-muted">{t('papersStored')}</p>
                    <p className="text-3xl font-semibold">{n(documentCount)}</p>
                </Card>
            </div>

            <div className="mb-5">
                <NextStep documentCount={documentCount} needsReview={needsReview} completeness={completeness} />
            </div>

            {expiring.length > 0 && (
                <section className="mb-5">
                    <h2 className="mb-3 text-lg font-semibold">{t('expiryTitle')}</h2>
                    <ul className="space-y-2">
                        {expiring.map((doc) => (
                            <li key={doc.id}>
                                <Link
                                    href={`/documents/${doc.id}`}
                                    className="flex items-center gap-3 rounded-xl border border-line bg-card p-4"
                                >
                                    <Icon name="clock" className={`size-6 shrink-0 ${doc.expired ? 'text-danger' : 'text-warn'}`} />
                                    <span className="min-w-0 flex-1">
                                        <span className="block font-medium">{doc.title || pick(types[doc.type])}</span>
                                        <span className={`text-sm ${doc.expired ? 'text-danger' : 'text-warn'}`}>
                                            {t(doc.expired ? 'expiredOn' : 'expiresOn', { date: date(doc.expiresAt) })}
                                        </span>
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                </section>
            )}

            <Link
                href={draft ? `/applications/${draft.id}` : '/forms'}
                className="flex items-center gap-4 rounded-2xl border border-line bg-card p-5 hover:border-brand"
            >
                <span className="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-brand">
                    <Icon name="form" className="size-6" />
                </span>
                <span className="min-w-0 flex-1">
                    <span className="block text-lg font-semibold">
                        {draft ? t('continueApplication', { name: pick(draft.name) }) : t('fillAForm')}
                    </span>
                    {!draft && <span className="block text-muted">{t('fillAFormHint')}</span>}
                </span>
                <Icon name="arrow" className="size-6 shrink-0 text-brand" />
            </Link>
        </Layout>
    );
}
