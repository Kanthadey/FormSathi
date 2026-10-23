import { Link } from '@inertiajs/react';
import Icon from '../../Components/Icon';
import Layout from '../../Components/Layout';
import { Button, Card } from '../../Components/ui';
import { useT } from '../../lib/i18n';

function DocumentRow({ doc, types }) {
    const { t, date, pick } = useT();
    const missing = doc.fieldsTotal > 0 && doc.fieldsCount === 0;

    return (
        <li>
            <Link href={`/documents/${doc.id}`} className="flex items-center gap-3 rounded-xl border border-line bg-card p-4 hover:border-brand">
                <span className="inline-flex size-11 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-brand">
                    <Icon name={doc.mime === 'application/pdf' ? 'papers' : 'image'} className="size-6" />
                </span>
                <span className="min-w-0 flex-1">
                    <span className="block truncate font-medium">{doc.title || pick(types[doc.type])}</span>
                    {doc.expiresAt && (
                        <span className={`block text-sm ${doc.expired ? 'text-danger' : doc.expiringSoon ? 'text-warn' : 'text-muted'}`}>
                            {t(doc.expired ? 'expiredOn' : 'expiresOn', { date: date(doc.expiresAt) })}
                        </span>
                    )}
                    {missing ? (
                        <span className="block text-sm text-warn">{t('needsDetails')}</span>
                    ) : (
                        doc.fieldsTotal > 0 && (
                            <span className="block text-sm text-muted">
                                {t('detailsFilled', { done: doc.fieldsCount, total: doc.fieldsTotal })}
                            </span>
                        )
                    )}
                </span>
                <Icon name="arrow" className="size-5 shrink-0 text-muted" />
            </Link>
        </li>
    );
}

export default function Index({ documents, categories, types }) {
    const { t, pick } = useT();

    return (
        <Layout title={t('papersTitle')}>
            <div className="mb-5 flex items-center justify-between gap-3">
                <h1 className="text-2xl font-semibold">{t('papersTitle')}</h1>
                <Button href="/documents/new">
                    <Icon name="plus" />
                    {t('addPaper')}
                </Button>
            </div>

            {documents.length === 0 && (
                <Card>
                    <p className="text-muted">{t('noPapers')}</p>
                </Card>
            )}

            {Object.entries(categories).map(([key, label]) => {
                const inFolder = documents.filter((d) => d.category === key);
                if (inFolder.length === 0) return null;
                return (
                    <section key={key} className="mb-6">
                        <h2 className="mb-3 text-lg font-semibold">{pick(label)}</h2>
                        <ul className="space-y-2">
                            {inFolder.map((doc) => (
                                <DocumentRow key={doc.id} doc={doc} types={types} />
                            ))}
                        </ul>
                    </section>
                );
            })}
        </Layout>
    );
}
