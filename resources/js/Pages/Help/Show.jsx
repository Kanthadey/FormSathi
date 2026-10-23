import { Link, useForm } from '@inertiajs/react';
import Icon from '../../Components/Icon';
import Layout from '../../Components/Layout';
import { Button, TextField } from '../../Components/ui';
import { useT } from '../../lib/i18n';
import { statusTone } from './Index';

/** A ticket thread; also used by the admin panel with staff wording. */
export function Thread({ ticket, staffView = false }) {
    const { t } = useT();

    return (
        <ol className="mb-5 space-y-3">
            {ticket.messages.map((m) => {
                const mine = staffView ? m.fromStaff : !m.fromStaff;
                return (
                    <li
                        key={m.id}
                        className={`rounded-2xl px-4 py-3 ${mine ? 'ml-8 rounded-br-sm bg-brand-soft' : 'mr-8 rounded-bl-sm border border-line bg-card'}`}
                    >
                        <p className="mb-1 text-xs font-medium text-muted">
                            {m.fromStaff ? (staffView ? 'Staff' : t('staffReply')) : staffView ? 'User' : t('you')} · {m.at}
                        </p>
                        <p className="whitespace-pre-wrap">{m.body}</p>
                    </li>
                );
            })}
        </ol>
    );
}

export default function Show({ ticket }) {
    const { t } = useT();
    const form = useForm({ body: '' });

    return (
        <Layout title={ticket.subject}>
            <Link href="/help" className="mb-3 inline-flex items-center gap-1.5 font-medium text-muted">
                <Icon name="back" className="size-4" />
                {t('helpTitle')}
            </Link>
            <h1 className="mb-1 text-xl font-semibold break-words">{ticket.subject}</h1>
            <p className={`mb-5 text-sm ${statusTone[ticket.status]}`}>{t(`ticket_${ticket.status}`)}</p>

            <Thread ticket={ticket} />

            {ticket.status !== 'closed' && (
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post(`/help/${ticket.id}`, { preserveScroll: true, onSuccess: () => form.reset() });
                    }}
                    className="space-y-3"
                >
                    <TextField
                        id="reply"
                        as="textarea"
                        rows={4}
                        label={t('reply')}
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
            )}
        </Layout>
    );
}
