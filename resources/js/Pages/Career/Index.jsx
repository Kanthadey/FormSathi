import { Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Icon from '../../Components/Icon';
import Layout from '../../Components/Layout';
import { Button, Card, TextField } from '../../Components/ui';
import { useT } from '../../lib/i18n';

const inputProps = {
    month: { type: 'month' },
    number: { inputMode: 'numeric' },
    textarea: { as: 'textarea', rows: 4 },
    select: { as: 'select' },
    text: {},
};

function ItemForm({ kind, config, item, onDone }) {
    const { t, pick, locale } = useT();
    const form = useForm({ kind, data: item?.data ?? {} });

    const submit = (e) => {
        e.preventDefault();
        const options = { preserveScroll: true, onSuccess: onDone };
        item ? form.put(`/career/${item.id}`, options) : form.post('/career', options);
    };

    return (
        <form onSubmit={submit} className="space-y-3 rounded-xl border border-brand bg-card p-4">
            {Object.entries(config.fields).map(([key, field]) => (
                <TextField
                    key={key}
                    id={`${kind}-${item?.id ?? 'new'}-${key}`}
                    label={`${field[locale]}${field.required ? '' : ` (${t('optional')})`}`}
                    value={form.data.data[key] ?? ''}
                    onChange={(e) => form.setData('data', { ...form.data.data, [key]: e.target.value })}
                    error={form.errors[`data.${key}`]}
                    required={field.required}
                    lang={kind === 'skill' || kind === 'work' || kind === 'education' ? 'en' : undefined}
                    {...inputProps[field.type]}
                >
                    {field.type === 'select' && (
                        <>
                            <option value="">{t('choose')}</option>
                            {Object.entries(field.options).map(([value, label]) => (
                                <option key={value} value={value}>
                                    {pick(label)}
                                </option>
                            ))}
                        </>
                    )}
                </TextField>
            ))}
            <div className="flex gap-2">
                <Button type="submit" disabled={form.processing}>
                    {t('save')}
                </Button>
                <Button type="button" variant="secondary" onClick={onDone}>
                    {t('cancel')}
                </Button>
            </div>
        </form>
    );
}

function describe(kind, data, levels, pick) {
    switch (kind) {
        case 'education':
            return [data.degree, [data.institution, data.subject, data.year, data.result].filter(Boolean).join(' · ')];
        case 'work':
            return [`${data.title}, ${data.employer}`, `${data.start} – ${data.end ?? '…'}${data.location ? ` · ${data.location}` : ''}`];
        case 'language':
            return [data.name, data.level ? pick(levels[data.level]) : null];
        case 'training':
            return [data.name, [data.provider, data.year].filter(Boolean).join(' · ')];
        default:
            return [data.name, null];
    }
}

export default function Index({ kinds, items }) {
    const { t, pick } = useT();
    const [editing, setEditing] = useState(null);
    const levels = kinds.language.fields.level.options;

    return (
        <Layout title={t('careerTitle')}>
            <Link href="/profile" className="mb-3 inline-flex items-center gap-1.5 font-medium text-muted">
                <Icon name="back" className="size-4" />
                {t('profileTitle')}
            </Link>
            <h1 className="mb-1 text-2xl font-semibold">{t('careerTitle')}</h1>
            <p className="mb-6 text-muted">{t('careerIntro')}</p>

            {Object.entries(kinds).map(([kind, config]) => {
                const list = items.filter((i) => i.kind === kind);
                return (
                    <section key={kind} className="mb-6">
                        <div className="mb-3 flex items-center justify-between gap-3">
                            <h2 className="text-lg font-semibold">{pick(config)}</h2>
                            {editing !== `new-${kind}` && (
                                <Button variant="ghost" className="min-h-10 py-1" onClick={() => setEditing(`new-${kind}`)}>
                                    <Icon name="plus" className="size-4" />
                                    {t('addItem')}
                                </Button>
                            )}
                        </div>
                        <ul className="space-y-2">
                            {list.map((item) =>
                                editing === item.id ? (
                                    <li key={item.id}>
                                        <ItemForm kind={kind} config={config} item={item} onDone={() => setEditing(null)} />
                                    </li>
                                ) : (
                                    <li key={item.id} className="flex items-start justify-between gap-3 rounded-xl border border-line bg-card p-4">
                                        {(() => {
                                            const [title, detail] = describe(kind, item.data, levels, pick);
                                            return (
                                                <span className="min-w-0" lang="en">
                                                    <span className="block font-medium break-words">{title}</span>
                                                    {detail && <span className="block text-sm text-muted">{detail}</span>}
                                                </span>
                                            );
                                        })()}
                                        <span className="flex shrink-0 gap-1">
                                            <button type="button" className="rounded-lg px-2 py-1 text-sm font-medium text-brand hover:bg-brand-soft" onClick={() => setEditing(item.id)}>
                                                {t('edit')}
                                            </button>
                                            <button
                                                type="button"
                                                className="rounded-lg px-2 py-1 text-sm font-medium text-danger hover:bg-danger-soft"
                                                onClick={() => router.delete(`/career/${item.id}`, { preserveScroll: true })}
                                            >
                                                {t('delete')}
                                            </button>
                                        </span>
                                    </li>
                                ),
                            )}
                            {list.length === 0 && editing !== `new-${kind}` && <li className="text-muted">{t('noItems')}</li>}
                            {editing === `new-${kind}` && (
                                <li>
                                    <ItemForm kind={kind} config={config} onDone={() => setEditing(null)} />
                                </li>
                            )}
                        </ul>
                    </section>
                );
            })}

            <Card className="border-dashed">
                <Button href="/jobs/new" className="w-full">
                    {t('newJobKit')}
                    <Icon name="arrow" className="size-4" />
                </Button>
            </Card>
        </Layout>
    );
}
