import { Link } from '@inertiajs/react';
import AdminShell from './AdminShell';

const filters = ['open', 'answered', 'closed', 'all'];

export default function Tickets({ tickets, status }) {
    return (
        <AdminShell title="Tickets">
            <div className="mb-4 flex gap-1">
                {filters.map((f) => (
                    <Link
                        key={f}
                        href={`/admin/tickets?status=${f}`}
                        className={`rounded-lg px-3 py-1.5 text-sm font-medium capitalize ${status === f ? 'bg-brand-soft text-brand-strong' : 'text-muted'}`}
                    >
                        {f}
                    </Link>
                ))}
            </div>
            {tickets.length === 0 ? (
                <p className="text-muted">No tickets.</p>
            ) : (
                <ul className="divide-y divide-line rounded-xl border border-line bg-card">
                    {tickets.map((t) => (
                        <li key={t.id}>
                            <Link href={`/admin/tickets/${t.id}`} className="block p-4 hover:bg-surface">
                                <span className="block font-medium break-words">{t.subject}</span>
                                <span className="text-sm text-muted">
                                    #{t.id} · {t.user} · <span className="capitalize">{t.status}</span> · {t.updatedAt}
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </AdminShell>
    );
}
