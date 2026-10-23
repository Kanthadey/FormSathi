import { Link, router, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import Icon from '../../Components/Icon';
import Layout from '../../Components/Layout';
import { Alert, Button, Card, CodeField, TextField } from '../../Components/ui';
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

const ocrMessages = { read: ['info', 'ocrRead'], nothing_found: ['warn', 'ocrNothing'], failed: ['warn', 'ocrFailed'] };

export default function Show({ document, fields, canView, types, ocrStatus }) {
    const { t, date, pick, locale } = useT();
    const name = document.title || pick(types[document.type]);

    const form = useForm({
        fields: Object.fromEntries(fields.map((f) => [f.key, f.value || f.suggestion || ''])),
    });

    // While OCR runs in the background, check back every few seconds (for up to two minutes).
    useEffect(() => {
        if (ocrStatus !== 'pending') return;
        let checks = 0;
        const timer = setInterval(() => {
            if (++checks > 40) return clearInterval(timer);
            router.reload({ only: ['fields', 'ocrStatus'] });
        }, 3000);
        return () => clearInterval(timer);
    }, [ocrStatus]);

    // When suggestions arrive, fill only the boxes the user hasn't typed in.
    useEffect(() => {
        const filled = { ...form.data.fields };
        let changed = false;
        for (const f of fields) {
            if (f.suggestion && !filled[f.key]) {
                filled[f.key] = f.suggestion;
                changed = true;
            }
        }
        if (changed) form.setData('fields', filled);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [fields]);

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
                        {ocrStatus === 'pending' && (
                            <Alert tone="info">
                                <span className="inline-flex items-center gap-2">
                                    <span className="size-3 animate-pulse rounded-full bg-brand" aria-hidden="true" />
                                    {t('ocrPending')}
                                </span>
                            </Alert>
                        )}
                        {ocrMessages[ocrStatus] && <Alert tone={ocrMessages[ocrStatus][0]}>{t(ocrMessages[ocrStatus][1])}</Alert>}
                        {fields.map((field) => {
                            const suggested = field.suggestion && form.data.fields[field.key] === field.suggestion;
                            return (
                                <div key={field.key}>
                                    <TextField
                                        id={`field-${field.key}`}
                                        label={field.label[locale]}
                                        value={form.data.fields[field.key]}
                                        onChange={(e) => form.setData('fields', { ...form.data.fields, [field.key]: e.target.value })}
                                        error={form.errors[`fields.${field.key}`]}
                                        lang={field.key.endsWith('_bn') ? 'bn' : field.key.endsWith('_en') ? 'en' : undefined}
                                        className={suggested ? '[&_input]:border-warn [&_input]:bg-warn-soft/40' : ''}
                                        {...inputProps[field.label.type]}
                                    />
                                    {suggested && (
                                        <p className="mt-1 flex items-center gap-1.5 text-sm text-warn">
                                            <Icon name="eye" className="size-4" />
                                            {t('ocrSuggested')}
                                        </p>
                                    )}
                                </div>
                            );
                        })}
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
