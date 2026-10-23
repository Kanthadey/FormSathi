import { Link, useForm, usePage } from '@inertiajs/react';
import Icon from '../../Components/Icon';
import Layout from '../../Components/Layout';
import { Button, Card, TextField } from '../../Components/ui';
import { useT } from '../../lib/i18n';

export const statusTone = { open: 'text-warn', answered: 'text-ok', closed: 'text-muted' };

export default function Index({ tickets }) {
    const { t, date } = useT();
    const { company } = usePage().props;
    const form = useForm({ subject: '', body: '' });

    return (
        <Layout title={t('helpTitle')}>
            <h1 className="mb-1 text-2xl font-semibold">{t('helpTitle')}</h1>
            <p className="mb-2 text-muted">{t('helpIntro')}</p>
            <p className="mb-5">
                {t('helpline')}:{' '}
                <a href={`tel:${company.helpline.replace(/[^\d+]/g, '')}`} className="font-medium text-brand underline">
                    {company.helpline}
                </a>
            </p>

            <Card className="mb-6">
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post('/help');
                    }}
                    className="space-y-4"
                >
                    <TextField
                        id="subject"
                        label={t('subject')}
                        value={form.data.subject}
                        onChange={(e) => form.setData('subject', e.target.value)}
                        error={form.errors.subject}
                        maxLength={150}
                        required
                    />
                    <TextField
                        id="body"
                        as="textarea"
                        rows={5}
                        label={t('message')}
                        value={form.data.body}
                        onChange={(e) => form.setData('body', e.target.value)}
                        error={form.errors.body}
                        maxLength={3000}
                        required
                    />
                    <Button type="submit" disabled={form.processing}>
                        {t('send')}
                    </Button>
                </form>
            </Card>

            {tickets.length > 0 && (
                <section>
                    <h2 className="mb-3 text-lg font-semibold">{t('myTickets')}</h2>
                    <ul className="space-y-2">
                        {tickets.map((ticket) => (
                            <li key={ticket.id}>
                                <Link href={`/help/${ticket.id}`} className="flex items-center gap-3 rounded-xl border border-line bg-card p-4 hover:border-brand">
                                    <span className="min-w-0 flex-1">
                                        <span className="block truncate font-medium">{ticket.subject}</span>
                                        <span className="text-sm">
                                            <span className={statusTone[ticket.status]}>{t(`ticket_${ticket.status}`)}</span>
                                            <span className="text-muted"> · {date(ticket.updatedAt)}</span>
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
