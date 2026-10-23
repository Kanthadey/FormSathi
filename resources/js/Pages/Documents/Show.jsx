import { Link, router, useForm } from '@inertiajs/react';
import Icon from '../../Components/Icon';
import Layout from '../../Components/Layout';
import { Button, Card, CodeField, TextField } from '../../Components/ui';
import { useT } from '../../lib/i18n';

const inputProps = {
    date: { type: 'date' },
    number: { inputMode: 'numeric' },
    tel: { type: 'tel', inputMode: 'tel' },
    textarea: { as: 'textarea', rows: 3 },
    text: {},
};

function PaperViewer({ document, canView }) {
    const { t } = useT();
    const form = useForm({ pin: '' });
    const src = `/documents/${document.id}/file`;

    if (!canView) {
        return (
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post('/pin/confirm', { preserveScroll: true, onError: () => form.reset() });
                }}
                className="space-y-4"
            >
                <p className="flex items-center gap-2 font-medium">
                    <Icon name="lock" />
                    {t('pinToView')}
                </p>
                <CodeField
                    id="view-pin"
                    type="password"
                    autoComplete="current-password"
                    label={t('pinLabel')}
                    value={form.data.pin}
                    onChange={(e) => form.setData('pin', e.target.value)}
                    error={form.errors.pin}
                    required
                />
                <Button type="submit" variant="secondary" className="w-full" disabled={form.processing}>
                    <Icon name="eye" />
                    {t('viewPaper')}
                </Button>
            </form>
        );
    }

    return (
        <div>
            {document.mime === 'application/pdf' ? (
                <iframe src={src} title={t('viewPaper')} className="h-96 w-full rounded-xl border border-line" />
            ) : (
                <img src={src} alt={t('viewPaper')} className="max-h-[28rem] w-full rounded-xl border border-line object-contain" />
            )}
            <a href={src} target="_blank" rel="noopener" className="mt-2 inline-block font-medium text-brand underline">
                {t('openFull')}
            </a>
        </div>
    );
}

export default function Show({ document, fields, canView, types }) {
    const { t, date, pick, locale } = useT();
    const name = document.title || pick(types[document.type]);

    const form = useForm({
        fields: Object.fromEntries(fields.map((f) => [f.key, f.value ?? ''])),
    });

    const save = (e) => {
        e.preventDefault();
        form.put(`/documents/${document.id}/fields`);
    };

    const destroy = () => {
        if (window.confirm(t('deleteConfirm'))) {
            router.delete(`/documents/${document.id}`);
        }
    };

    return (
        <Layout title={name}>
            <Link href="/documents" className="mb-3 inline-flex items-center gap-1.5 font-medium text-muted">
                <Icon name="back" className="size-4" />
                {t('papersTitle')}
            </Link>
            <h1 className="mb-1 text-2xl font-semibold">{name}</h1>
            {document.expiresAt && (
                <p className={`mb-5 ${document.expired ? 'text-danger' : document.expiringSoon ? 'text-warn' : 'text-muted'}`}>
                    {t(document.expired ? 'expiredOn' : 'expiresOn', { date: date(document.expiresAt) })}
                </p>
            )}

            <Card className="mt-4 mb-5">
                <PaperViewer document={document} canView={canView} />
            </Card>

            <Card className="mb-5">
                <h2 className="mb-1 text-lg font-semibold">{t('detailsOnPaper')}</h2>
                {fields.length === 0 ? (
                    <p className="text-muted">{t('noFieldsForType')}</p>
                ) : (
                    <form onSubmit={save} className="space-y-4">
                        <p className="mb-2 text-sm text-muted">{t('detailsOnPaperHint')}</p>
                        {fields.map((field) => (
                            <TextField
                                key={field.key}
                                id={`field-${field.key}`}
                                label={field.label[locale]}
                                value={form.data.fields[field.key]}
                                onChange={(e) => form.setData('fields', { ...form.data.fields, [field.key]: e.target.value })}
                                error={form.errors[`fields.${field.key}`]}
                                lang={field.key.endsWith('_bn') ? 'bn' : field.key.endsWith('_en') ? 'en' : undefined}
                                {...inputProps[field.label.type]}
                            />
                        ))}
                        <Button type="submit" className="w-full" disabled={form.processing}>
                            {form.processing ? t('saving') : t('saveDetails')}
                        </Button>
                    </form>
                )}
            </Card>

            {canView && (
                <Button variant="ghost" onClick={destroy} className="text-danger hover:bg-danger-soft">
                    <Icon name="trash" />
                    {t('deletePaper')}
                </Button>
            )}
        </Layout>
    );
}
