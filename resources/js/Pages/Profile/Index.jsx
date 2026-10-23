import { router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Icon from '../../Components/Icon';
import Layout from '../../Components/Layout';
import { Button, TextField } from '../../Components/ui';
import { useT } from '../../lib/i18n';

const sections = [
    {
        title: 'sectionNames',
        keys: [
            'name_bn', 'name_en', 'given_name_en', 'surname_en',
            'father_name_bn', 'father_name_en', 'father_profession', 'father_nid_number',
            'mother_name_bn', 'mother_name_en', 'mother_profession', 'mother_nid_number',
            'spouse_name_en',
        ],
    },
    { title: 'sectionPersonal', keys: ['date_of_birth', 'district_of_birth', 'gender', 'marital_status', 'religion', 'profession'] },
    { title: 'sectionIds', keys: ['nid_number', 'birth_reg_number', 'passport_number'] },
    {
        title: 'sectionPresent',
        keys: ['present_address', 'present_district', 'present_police_station', 'present_post_office', 'present_post_code', 'present_village_road'],
    },
    {
        title: 'sectionPermanent',
        keys: ['permanent_address', 'permanent_district', 'permanent_police_station', 'permanent_post_office', 'permanent_post_code', 'permanent_village_road'],
    },
    {
        title: 'sectionContact',
        keys: ['email', 'emergency_contact_name', 'emergency_contact_relation', 'emergency_contact_mobile', 'emergency_contact_address'],
    },
];

const inputProps = {
    date: { type: 'date' },
    number: { inputMode: 'numeric' },
    tel: { type: 'tel', inputMode: 'tel' },
    email: { type: 'email', inputMode: 'email' },
    textarea: { as: 'textarea', rows: 3 },
    select: { as: 'select' },
    text: {},
};

function mask(value) {
    return value.length <= 4 ? '••••' : '•'.repeat(Math.min(value.length - 3, 10)) + value.slice(-3);
}

function save(key, value, sourceDocumentId = null) {
    router.put(`/profile/${key}`, { value, source_document_id: sourceDocumentId }, { preserveScroll: true });
}

function FieldRow({ field, types, startEditing }) {
    const { t, date, pick, locale } = useT();
    const [editing, setEditing] = useState(startEditing);
    const [revealed, setRevealed] = useState(!field.sensitive);
    const form = useForm({ value: field.value ?? '' });

    const paperName = (doc) => doc.title || pick(types[doc.type]);
    const show = (value) => {
        if (field.type === 'date') return date(value);
        if (field.type === 'select') return pick(field.options[value]) || value;
        return revealed ? value : mask(value);
    };
    const source = field.candidates.flatMap((c) => c.documents).find((d) => d.id === field.sourceDocumentId);

    const border = field.needsReview ? 'border-2 border-danger' : field.conflict ? 'border-2 border-warn' : 'border border-line';

    return (
        <li id={`field-${field.key}`} className={`scroll-mt-24 rounded-xl bg-card p-4 ${border}`}>
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0 flex-1">
                    <p className="text-sm text-muted">{field.label[locale]}</p>
                    {editing ? null : field.value ? (
                        <p className="text-lg font-medium break-words" lang={field.key.endsWith('_en') ? 'en' : undefined}>
                            {show(field.value)}
                        </p>
                    ) : (
                        <p className="text-muted italic">{t('empty')}</p>
                    )}
                    {field.value && !editing && (
                        <p className="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                            <span className="text-muted">{source ? t('fromPaper', { paper: paperName(source) }) : field.sourceDocumentId ? '' : t('typedByYou')}</span>
                            <span className={`inline-flex items-center gap-1 ${field.confirmed ? 'text-ok' : 'text-warn'}`}>
                                <Icon name={field.confirmed ? 'check' : 'clock'} className="size-4" />
                                {field.confirmed ? t('confirmed') : t('notConfirmed')}
                            </span>
                        </p>
                    )}
                </div>
                {!editing && (
                    <div className="flex shrink-0 gap-1">
                        {field.sensitive && field.value && (
                            <button type="button" onClick={() => setRevealed(!revealed)} className="rounded-lg px-2 py-1 text-sm font-medium text-brand hover:bg-brand-soft">
                                {revealed ? t('hide') : t('show')}
                            </button>
                        )}
                        <button
                            type="button"
                            onClick={() => {
                                form.setData('value', field.value ?? '');
                                setEditing(true);
                            }}
                            className="rounded-lg px-2 py-1 text-sm font-medium text-brand hover:bg-brand-soft"
                        >
                            {field.value ? t('edit') : t('add')}
                        </button>
                    </div>
                )}
            </div>

            {editing && (
                <form
                    className="mt-2 space-y-3"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.put(`/profile/${field.key}`, { preserveScroll: true, onSuccess: () => setEditing(false) });
                    }}
                >
                    <TextField
                        id={`profile-${field.key}`}
                        label={field.label[locale]}
                        value={form.data.value}
                        onChange={(e) => form.setData('value', e.target.value)}
                        error={form.errors.value}
                        autoFocus
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
                    <div className="flex gap-2">
                        <Button type="submit" disabled={form.processing}>
                            {t('save')}
                        </Button>
                        <Button type="button" variant="secondary" onClick={() => setEditing(false)}>
                            {t('cancel')}
                        </Button>
                    </div>
                </form>
            )}

            {!editing && field.value && !field.confirmed && !field.conflict && (
                <Button variant="secondary" className="mt-3" onClick={() => save(field.key, field.value, field.sourceDocumentId)}>
                    <Icon name="check" />
                    {t('confirm')}
                </Button>
            )}

            {field.conflict && !editing && (
                <div className="mt-3">
                    {field.needsReview ? (
                        <div className="mb-3 text-danger">
                            <p className="flex items-center gap-2 font-semibold">
                                <Icon name="alert" className="size-5 shrink-0" />
                                {t('mismatchTitle')}
                            </p>
                            <p className="text-sm">{t('mismatchHint')}</p>
                        </div>
                    ) : (
                        <p className="mb-3 flex items-start gap-2 text-sm text-warn">
                            <Icon name="alert" className="mt-0.5 size-4 shrink-0" />
                            {t('resolvedHint')}
                        </p>
                    )}
                    <ul className="space-y-2">
                        {field.candidates.map((candidate) => {
                            const isChosen = field.confirmed && field.value === candidate.value;
                            return (
                                <li key={candidate.value} className="rounded-lg border border-line p-3">
                                    <p className="font-medium break-words">{show(candidate.value)}</p>
                                    <p className="mb-2 text-sm text-muted">
                                        {t('onPapers')}: {candidate.documents.map(paperName).join(', ')}
                                    </p>
                                    {isChosen ? (
                                        <span className="inline-flex items-center gap-1 text-sm font-semibold text-ok">
                                            <Icon name="check" className="size-4" />
                                            {t('chosen')}
                                        </span>
                                    ) : (
                                        <Button
                                            variant="secondary"
                                            className="min-h-10 py-1.5 text-sm"
                                            onClick={() => save(field.key, candidate.value, candidate.documents[0].id)}
                                        >
                                            {t('useThis')}
                                        </Button>
                                    )}
                                </li>
                            );
                        })}
                    </ul>
                </div>
            )}
        </li>
    );
}

export default function Index({ fields, completeness, types }) {
    const { t, n } = useT();
    const byKey = Object.fromEntries(fields.map((f) => [f.key, f]));

    // Arriving from a form's "Add to profile" link: jump to that field and open it.
    const [target] = useState(() => (typeof window !== 'undefined' ? window.location.hash.replace('#field-', '') : ''));
    useEffect(() => {
        if (target) document.getElementById(`field-${target}`)?.scrollIntoView({ block: 'start' });
    }, [target]);

    return (
        <Layout title={t('profileTitle')}>
            <h1 className="mb-1 text-2xl font-semibold">{t('profileTitle')}</h1>
            <p className="mb-4 text-muted">{t('profileIntro')}</p>

            <div className="mb-6 flex items-center gap-3">
                <div className="h-2.5 flex-1 overflow-hidden rounded-full bg-line" aria-hidden="true">
                    <div className="h-full rounded-full bg-brand" style={{ width: `${completeness}%` }} />
                </div>
                <span className="font-semibold">{n(completeness)}%</span>
            </div>

            {sections.map((section) => (
                <section key={section.title} className="mb-6">
                    <h2 className="mb-3 text-lg font-semibold">{t(section.title)}</h2>
                    <ul className="space-y-2">
                        {section.keys.filter((key) => byKey[key]).map((key) => (
                            <FieldRow key={key} field={byKey[key]} types={types} startEditing={key === target && !byKey[key].value} />
                        ))}
                    </ul>
                </section>
            ))}
        </Layout>
    );
}
