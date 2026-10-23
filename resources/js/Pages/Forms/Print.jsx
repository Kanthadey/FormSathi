import { Head, Link, usePage } from '@inertiajs/react';
import Icon from '../../Components/Icon';
import { Button } from '../../Components/ui';
import { useT } from '../../lib/i18n';

/**
 * A plain, printable list of what goes in each field, in form order.
 * It is the user's own checklist, not the official summary the passport
 * office asks for; the footer says so.
 */
export default function Print({ application, sections }) {
    const { t, n, date, pick, locale } = useT();
    const { company } = usePage().props;
    const today = new Date().toISOString().slice(0, 10);
    let number = 0;

    return (
        <>
            <Head title={`${t('printSummary')} · ${pick(application.name)}`} />
            <div className="mx-auto max-w-3xl bg-white px-4 py-6 print:max-w-none print:p-0">
                <div className="mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden">
                    <Link href={`/applications/${application.id}`} className="inline-flex items-center gap-1.5 font-medium text-muted">
                        <Icon name="back" className="size-4" />
                        {pick(application.name)}
                    </Link>
                    <Button onClick={() => window.print()}>
                        <Icon name="print" />
                        {t('print')}
                    </Button>
                </div>

                <header className="mb-5 border-b-2 border-ink pb-3">
                    <h1 className="text-2xl font-semibold">{pick(application.name)}</h1>
                    <p className="text-sm text-muted">
                        {t('printedOn', { date: date(today) })}
                        {application.completedAt && ` · ${t('statusCompleted', { date: date(application.completedAt) })}`}
                    </p>
                </header>

                {sections.map((section) => (
                    <section key={section.key} className="mb-5 break-inside-avoid">
                        <h2 className="mb-1 text-base font-semibold">{pick(section.label)}</h2>
                        <table className="w-full border-collapse text-sm">
                            <tbody>
                                {section.fields.map((field) => {
                                    number += 1;
                                    return (
                                        <tr key={field.key} className="border-b border-line align-top">
                                            <td className="w-8 py-1.5 pr-2 text-muted">{n(number)}</td>
                                            <td className="w-[45%] py-1.5 pr-3">
                                                <span className="block">{field.label[locale]}</span>
                                                <span lang="en" className="block text-xs text-muted">
                                                    {field.site}
                                                </span>
                                            </td>
                                            <td lang="en" className="py-1.5 font-medium break-words">
                                                {field.value ?? <span className="font-normal text-muted">—</span>}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </section>
                ))}

                <footer className="mt-6 border-t border-line pt-3 text-xs text-muted">
                    <p>{t('summaryFooter')}</p>
                    <p>
                        {company.name} · {company.helpline}
                    </p>
                </footer>
            </div>
        </>
    );
}
