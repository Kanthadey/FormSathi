import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import Icon from '../../Components/Icon';
import Layout from '../../Components/Layout';
import { Alert, Button } from '../../Components/ui';
import { useT } from '../../lib/i18n';

/** Clipboard API first; the textarea fallback covers older Android browsers. */
async function copyText(text) {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch {
        const area = document.createElement('textarea');
        area.value = text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();
        const ok = document.execCommand('copy');
        area.remove();
        return ok;
    }
}

function FieldCard({ field, number, applicationId, completed }) {
    const { t, n, locale } = useT();
    const [state, setState] = useState(null);
    const copied = field.copied || state === 'copied';

    const copy = async () => {
        if (!(await copyText(field.value))) {
            setState('failed');
            return;
        }
        setState('copied');
        if (!completed && !field.copied) {
            router.post(`/applications/${applicationId}/copied`, { key: field.key }, { preserveScroll: true, preserveState: true });
        }
    };

    const tone = field.needsReview
        ? 'border-2 border-danger'
        : field.missing
          ? 'border border-dashed border-muted/50'
          : copied && !completed
            ? 'border border-ok bg-ok-soft/40'
            : 'border border-line';

    return (
        <li id={`card-${field.key}`} className={`rounded-xl bg-card p-4 ${tone}`}>
            <div className="flex items-start gap-3">
                <span
                    className={`inline-flex size-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold ${
                        copied && !completed ? 'bg-ok text-white' : 'bg-brand-soft text-brand-strong'
                    }`}
                    aria-hidden="true"
                >
                    {copied && !completed ? <Icon name="check" className="size-4" /> : n(number)}
                </span>
                <div className="min-w-0 flex-1">
                    <p className="font-semibold">
                        {field.label[locale]}{' '}
                        {!field.required && <span className="text-sm font-normal text-muted">({t('optional')})</span>}
                    </p>
                    <p className="text-sm text-muted">
                        {t('onSite')}: <span lang="en">“{field.site}”</span>
                    </p>
                </div>
            </div>

            {field.missing ? (
                <div className="mt-3 flex flex-wrap items-center justify-between gap-2">
                    <span className={`flex items-center gap-1.5 text-sm font-medium ${field.required ? 'text-danger' : 'text-muted'}`}>
                        <Icon name="alert" className="size-4" />
                        {t('notInProfile')}
                    </span>
                    {field.profileKey !== 'account_mobile' && (
                        <Link href={`/profile#field-${field.profileKey}`} className="font-medium text-brand underline">
                            {t('addInProfile')}
                        </Link>
                    )}
                </div>
            ) : (
                <>
                    <p lang="en" className="mt-3 rounded-lg bg-surface px-3 py-2.5 text-lg font-medium break-words select-all">
                        {field.value}
                    </p>
                    {field.needsReview && (
                        <Link href={`/profile#field-${field.profileKey}`} className="mt-2 flex items-start gap-1.5 text-sm font-medium text-danger underline">
                            <Icon name="alert" className="mt-0.5 size-4 shrink-0" />
                            {t('differsWarning')}
                        </Link>
                    )}
                    <Button
                        variant={copied ? 'secondary' : 'primary'}
                        className={`mt-3 w-full ${copied ? 'border-ok text-ok' : ''}`}
                        onClick={copy}
                        aria-live="polite"
                    >
                        <Icon name={copied ? 'check' : 'copy'} />
                        {copied ? t('copiedTick') : t('copy')}
                    </Button>
                    {state === 'failed' && <p className="mt-2 text-sm text-danger">{t('copyFailed')}</p>}
                </>
            )}

            {field.help && <p className="mt-2 text-sm text-muted">{field.help[locale]}</p>}
        </li>
    );
}

function PreCheck({ application, totals }) {
    const { t } = useT();
    const ready = totals.missingRequired === 0 && totals.needsReview === 0;

    return (
        <section className="mb-5 space-y-3" aria-labelledby="precheck">
            <h2 id="precheck" className="text-lg font-semibold">
                {t('checkTitle')}
            </h2>
            {totals.missingRequired > 0 && (
                <Alert tone="danger">
                    <p className="font-semibold">{t('missingRequired', { count: totals.missingRequired })}</p>
                </Alert>
            )}
            {totals.needsReview > 0 && (
                <Alert tone="danger">
                    <p className="font-semibold">{t('reviewNeeded', { count: totals.needsReview })}</p>
                    <Link href="/profile" className="font-medium underline">
                        {t('fixInProfile')}
                    </Link>
                </Alert>
            )}
            {ready && <Alert tone="success">{t('allReady')}</Alert>}
            {!application.templateVerified && <Alert tone="warn">{t('unverifiedTemplate')}</Alert>}
            {application.officialUrl && (
                <Button href={application.officialUrl} external variant="secondary" className="w-full">
                    <Icon name="external" />
                    {t('openOfficial')}
                </Button>
            )}
        </section>
    );
}

export default function Show({ application, sections, totals }) {
    const { t, date, pick } = useT();
    const completed = application.status === 'completed';
    let number = 0;

    const markSubmitted = () => {
        if (window.confirm(t('markSubmittedConfirm'))) {
            router.post(`/applications/${application.id}/complete`);
        }
    };

    const destroy = () => {
        if (window.confirm(t('deleteApplicationConfirm'))) {
            router.delete(`/applications/${application.id}`);
        }
    };

    return (
        <Layout title={pick(application.name)}>
            <Link href="/forms" className="mb-3 inline-flex items-center gap-1.5 font-medium text-muted">
                <Icon name="back" className="size-4" />
                {t('formsTitle')}
            </Link>
            <h1 className="mb-4 text-2xl font-semibold">{pick(application.name)}</h1>

            {completed ? (
                <Alert tone="success" className="mb-5">
                    <p className="font-semibold">{t('statusCompleted', { date: date(application.completedAt) })}</p>
                    <p>{t('submittedNote')}</p>
                </Alert>
            ) : (
                <PreCheck application={application} totals={totals} />
            )}

            {!completed && (
                <div className="sticky top-[57px] z-[5] -mx-4 mb-4 border-b border-line bg-surface/95 px-4 py-2 backdrop-blur">
                    <div className="flex items-center gap-3">
                        <div className="h-2 flex-1 overflow-hidden rounded-full bg-line" aria-hidden="true">
                            <div
                                className="h-full rounded-full bg-ok transition-all"
                                style={{ width: `${totals.filled ? (totals.copied / totals.filled) * 100 : 0}%` }}
                            />
                        </div>
                        <span className="text-sm font-medium" aria-live="polite">
                            {t('progressCopied', { done: totals.copied, total: totals.filled })}
                        </span>
                    </div>
                </div>
            )}

            {sections.map((section) => (
                <section key={section.key} className="mb-6">
                    <h2 className="mb-3 text-lg font-semibold">{pick(section.label)}</h2>
                    <ol className="space-y-3">
                        {section.fields.map((field) => {
                            number += 1;
                            return (
                                <FieldCard
                                    key={field.key}
                                    field={field}
                                    number={number}
                                    applicationId={application.id}
                                    completed={completed}
                                />
                            );
                        })}
                    </ol>
                </section>
            ))}

            <div className="space-y-3 border-t border-line pt-5">
                <Button href={`/applications/${application.id}/print`} variant="secondary" className="w-full">
                    <Icon name="print" />
                    {t('printSummary')}
                </Button>
                {!completed && (
                    <Button className="w-full" onClick={markSubmitted}>
                        <Icon name="check" />
                        {t('markSubmitted')}
                    </Button>
                )}
                <div className="flex flex-wrap justify-between gap-2 pt-2">
                    {!completed && totals.copied > 0 && (
                        <button
                            type="button"
                            className="font-medium text-muted underline"
                            onClick={() => router.delete(`/applications/${application.id}/copied`, { preserveScroll: true })}
                        >
                            {t('resetTicks')}
                        </button>
                    )}
                    <button type="button" className="font-medium text-danger underline" onClick={destroy}>
                        {t('deleteApplication')}
                    </button>
                </div>
            </div>
        </Layout>
    );
}
