import { Link, usePage } from '@inertiajs/react';
import Layout from '../../Components/Layout';

const links = [
    ['/admin', 'Overview'],
    ['/admin/templates', 'Form templates'],
    ['/admin/tickets', 'Tickets'],
    ['/admin/payments', 'Payments'],
];

/** Staff pages (English only). No page here can show a user's papers. */
export default function AdminShell({ title, children }) {
    const { url } = usePage();

    return (
        <Layout title={`Admin · ${title}`}>
            <nav className="mb-5 flex flex-wrap gap-1 rounded-xl border border-warn/40 bg-warn-soft p-1" aria-label="Admin">
                {links.map(([href, label]) => {
                    const active = href === '/admin' ? url === '/admin' : url.startsWith(href);
                    return (
                        <Link
                            key={href}
                            href={href}
                            className={`rounded-lg px-3 py-1.5 text-sm font-medium ${active ? 'bg-card text-ink shadow-sm' : 'text-warn'}`}
                        >
                            {label}
                        </Link>
                    );
                })}
            </nav>
            <h1 className="mb-4 text-2xl font-semibold break-words">{title}</h1>
            <div lang="en">{children}</div>
        </Layout>
    );
}
