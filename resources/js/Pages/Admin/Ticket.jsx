import { useForm } from '@inertiajs/react';
import { Button, Card, TextField } from '../../Components/ui';
import { Thread } from '../Help/Show';
import AdminShell from './AdminShell';

export default function Ticket({ ticket }) {
    const form = useForm({ body: '', close: false });

    return (
        <AdminShell title={`#${ticket.id} ${ticket.subject}`}>
            <p className="mb-4 text-sm text-muted">
                {ticket.user} · <span className="capitalize">{ticket.status}</span> · opened {ticket.createdAt}
            </p>
            {ticket.context && (
                <Card className="mb-4 text-sm">
                    <pre className="whitespace-pre-wrap">{JSON.stringify(ticket.context, null, 2)}</pre>
                </Card>
            )}
            <Thread ticket={ticket} staffView />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post(`/admin/tickets/${ticket.id}`, { preserveScroll: true, onSuccess: () => form.reset() });
                }}
                className="space-y-3"
            >
                <TextField
                    id="reply"
                    as="textarea"
                    rows={5}
                    label="Reply (the user reads this)"
                    value={form.data.body}
                    onChange={(e) => form.setData('body', e.target.value)}
                    error={form.errors.body}
                    required
                />
                <label className="flex items-center gap-2">
                    <input type="checkbox" checked={form.data.close} onChange={(e) => form.setData('close', e.target.checked)} className="size-5" />
                    Close the ticket after replying
                </label>
                <Button type="submit" disabled={form.processing}>
                    Send reply
                </Button>
            </form>
        </AdminShell>
    );
}
