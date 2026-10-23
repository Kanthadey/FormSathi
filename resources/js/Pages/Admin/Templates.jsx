import { Link } from '@inertiajs/react';
import AdminShell from './AdminShell';

export default function Templates({ templates }) {
    return (
        <AdminShell title="Form templates">
            <div className="overflow-x-auto rounded-xl border border-line bg-card">
                <table className="w-full text-sm">
                    <thead className="bg-surface text-left text-muted">
                        <tr>
                            <th className="p-3 font-medium">Template</th>
                            <th className="p-3 font-medium">Version</th>
                            <th className="p-3 font-medium">Status</th>
                            <th className="p-3 font-medium">Checked</th>
                            <th className="p-3 text-right font-medium">Fields</th>
                            <th className="p-3 text-right font-medium">Used</th>
                        </tr>
                    </thead>
                    <tbody>
                        {templates.map((t) => (
                            <tr key={t.id} className="border-t border-line">
                                <td className="p-3">
                                    <Link href={`/admin/templates/${t.id}`} className="font-medium text-brand underline">
                                        {t.name}
                                    </Link>
                                    <span className="block text-xs text-muted">{t.slug}</span>
                                </td>
                                <td className="p-3">v{t.version}</td>
                                <td className="p-3">{t.active ? <span className="font-medium text-ok">Live</span> : <span className="text-muted">Inactive</span>}</td>
                                <td className="p-3">{t.verifiedAt ?? <span className="text-warn">Not checked</span>}</td>
                                <td className="p-3 text-right tabular-nums">{t.fields}</td>
                                <td className="p-3 text-right tabular-nums">{t.applications}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <p className="mt-4 text-sm text-muted">
                Editing a template always saves a new version. To add a brand-new form, copy a file in <code>database/templates/</code> and run{' '}
                <code>php artisan db:seed</code>.
            </p>
        </AdminShell>
    );
}
