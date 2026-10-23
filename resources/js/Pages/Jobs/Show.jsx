import { Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Icon from '../../Components/Icon';
import Layout from '../../Components/Layout';
import { Alert, Button, Card, TextField } from '../../Components/ui';
import { copyText } from '../../lib/clipboard';
import { useT } from '../../lib/i18n';

function CopyButton({ text }) {
    const { t } = useT();
    const [copied, setCopied] = useState(false);

    return (
        <Button
            type="button"
            variant="secondary"
            className="min-h-10 py-1.5 text-sm"
            onClick={async () => setCopied(await copyText(text))}
        >
            <Icon name={copied ? 'check' : 'copy'} className="size-4" />
            {copied ? t('copiedTick') : t('copy')}
        </Button>
    );
}

function CvPreview({ cv, summary }) {
    const { t } = useT();

    return (
        <div lang="en" className="rounded-xl border border-line bg-white p-5 text-[15px] leading-relaxed">
            <p className="text-2xl font-semibold">{cv.contact.name || '—'}</p>
            <p className="mb-4 text-sm text-muted">{[cv.contact.phone, cv.contact.email, cv.contact.location].filter(Boolean).join(' · ')}</p>
            {summary && <p className="mb-4">{summary}</p>}
            {cv.skills.length > 0 && (
                <>
                    <h3 className="mb-1 text-sm font-semibold tracking-wide text-brand uppercase">{t('keySkills')}</h3>
                    <p className="mb-4 flex flex-wrap gap-1.5">
                        {cv.skills.map((s) => (
                            <span key={s} className="rounded border border-line px-2 py-0.5 text-sm">
                                {s}
                            </span>
                        ))}
                    </p>
                </>
            )}
            {cv.work.length > 0 && (
                <>
                    <h3 className="mb-1 text-sm font-semibold tracking-wide text-brand uppercase">{t('experience')}</h3>
                    {cv.work.map((job, i) => (
                        <div key={i} className="mb-3">
                            <p>
                                <strong>{job.title}</strong>, {job.employer} <span className="text-sm text-muted">· {job.period}</span>
                            </p>
                            <ul className="list-disc pl-5">
                                {job.bullets.map((b, j) => (
                                    <li key={j}>{b}</li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </>
            )}
            {cv.education.length > 0 && (
                <>
                    <h3 className="mb-1 text-sm font-semibold tracking-wide text-brand uppercase">{t('education')}</h3>
                    {cv.education.map((e, i) => (
                        <p key={i}>
                            <strong>{e.degree}</strong>, {e.institution}
                            {e.year && <span className="text-muted"> · {e.year}</span>}
                            {e.result && <span className="text-muted"> · {e.result}</span>}
                        </p>
                    ))}
                </>
            )}
        </div>
    );
}

export default function Show({ kit, cv, coverLetter, answers, gaps }) {
    const { t, date } = useT();
    const [tab, setTab] = useState('cv');
    const form = useForm({ summary: cv.summary, coverLetter, answers });

    const save = (e) => {
        e.preventDefault();
        form.put(`/jobs/${kit.id}`, { preserveScroll: true });
    };

    const tabs = [
        ['cv', t('tabCv')],
        ['letter', t('tabLetter')],
        ['answers', t('tabAnswers')],
        ['advert', t('tabAdvert')],
    ];

    return (
        <Layout title={kit.jobTitle || t('jobsTitle')}>
            <Link href="/jobs" className="mb-3 inline-flex items-center gap-1.5 font-medium text-muted">
                <Icon name="back" className="size-4" />
                {t('jobsTitle')}
            </Link>
            <h1 lang="en" className="text-2xl font-semibold">
                {kit.jobTitle || '—'}
            </h1>
            <p className="mb-4 text-muted">{[kit.company, date(kit.date)].filter(Boolean).join(' · ')}</p>

            {gaps.length > 0 && (
                <Alert tone="warn" className="mb-5">
                    <p className="font-semibold">{t('gapsTitle')}</p>
                    <ul className="my-1 list-disc pl-5">
                        {gaps.map((g, i) => (
                            <li key={i}>{g}</li>
                        ))}
                    </ul>
                    <p className="text-sm">{t('gapsHint')}</p>
                </Alert>
            )}

            <div role="tablist" className="mb-4 grid grid-cols-4 gap-1 rounded-xl bg-line/60 p-1">
                {tabs.map(([key, label]) => (
                    <button
                        key={key}
                        role="tab"
                        type="button"
                        aria-selected={tab === key}
                        onClick={() => setTab(key)}
                        className={`rounded-lg px-2 py-2 text-sm font-medium ${tab === key ? 'bg-card text-ink shadow-sm' : 'text-muted'}`}
                    >
                        {label}
                    </button>
                ))}
            </div>

            <form onSubmit={save}>
                {tab === 'cv' && (
                    <div className="space-y-4">
                        <CvPreview cv={cv} summary={form.data.summary} />
                        <TextField
                            id="summary"
                            as="textarea"
                            rows={4}
                            lang="en"
                            label={t('summaryLabel')}
                            hint={t('reviewEdit')}
                            value={form.data.summary}
                            onChange={(e) => form.setData('summary', e.target.value)}
                            error={form.errors.summary}
                            className="[&_textarea]:text-base"
                        />
                        <div className="flex flex-wrap gap-2">
                            <Button type="submit" disabled={form.processing || !form.isDirty}>
                                {t('saveChanges')}
                            </Button>
                            <Button external href={`/jobs/${kit.id}/cv.pdf`} variant="secondary">
                                <Icon name="papers" />
                                {t('downloadPdf')}
                            </Button>
                        </div>
                    </div>
                )}

                {tab === 'letter' && (
                    <div className="space-y-4">
                        <TextField
                            id="cover-letter"
                            as="textarea"
                            rows={16}
                            lang="en"
                            label={t('tabLetter')}
                            hint={t('reviewEdit')}
                            value={form.data.coverLetter}
                            onChange={(e) => form.setData('coverLetter', e.target.value)}
                            error={form.errors.coverLetter}
                            className="[&_textarea]:text-base"
                        />
                        <div className="flex flex-wrap gap-2">
                            <Button type="submit" disabled={form.processing || !form.isDirty}>
                                {t('saveChanges')}
                            </Button>
                            <CopyButton text={form.data.coverLetter} />
                            <Button external href={`/jobs/${kit.id}/cover-letter.pdf`} variant="secondary">
                                <Icon name="papers" />
                                {t('downloadPdf')}
                            </Button>
                        </div>
                    </div>
                )}

                {tab === 'answers' && (
                    <div className="space-y-4">
                        {form.data.answers.length === 0 && <p className="text-muted">{t('noAnswers')}</p>}
                        {form.data.answers.map((a, i) => (
                            <Card key={i}>
                                <p lang="en" className="mb-2 font-medium">
                                    {a.question}
                                </p>
                                <TextField
                                    id={`answer-${i}`}
                                    as="textarea"
                                    rows={4}
                                    lang="en"
                                    label={t('tabAnswers')}
                                    value={a.answer}
                                    onChange={(e) =>
                                        form.setData('answers', form.data.answers.map((x, j) => (j === i ? { ...x, answer: e.target.value } : x)))
                                    }
                                    className="[&_label]:sr-only [&_textarea]:text-base"
                                />
                                <div className="mt-2">
                                    <CopyButton text={a.answer} />
                                </div>
                            </Card>
                        ))}
                        {form.data.answers.length > 0 && (
                            <Button type="submit" disabled={form.processing || !form.isDirty}>
                                {t('saveChanges')}
                            </Button>
                        )}
                    </div>
                )}

                {tab === 'advert' && (
                    <Card>
                        <p lang="en" className="whitespace-pre-wrap">
                            {kit.advert}
                        </p>
                    </Card>
                )}
            </form>

            <button
                type="button"
                className="mt-6 font-medium text-danger underline"
                onClick={() => window.confirm(t('deleteApplicationConfirm')) && router.delete(`/jobs/${kit.id}`)}
            >
                {t('deleteApplication')}
            </button>
        </Layout>
    );
}
