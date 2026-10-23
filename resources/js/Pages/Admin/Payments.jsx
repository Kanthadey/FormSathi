import AdminShell from './AdminShell';

const tone = { completed: 'text-ok', initiated: 'text-muted', failed: 'text-danger', cancelled: 'text-muted' };

export default function Payments({ payments }) {
    return (
        <AdminShell title="Payments">
            {payments.length === 0 ? (
                <p className="text-muted">No payments yet.</p>
            ) : (
                <div className="overflow-x-auto rounded-xl border border-line bg-card">
                    <table className="w-full text-sm">
                        <thead className="bg-surface text-left text-muted">
                            <tr>
                                <th className="p-3 font-medium">When</th>
                                <th className="p-3 font-medium">Invoice</th>
                                <th className="p-3 font-medium">User</th>
                                <th className="p-3 font-medium">Product</th>
                                <th className="p-3 text-right font-medium">৳</th>
                                <th className="p-3 font-medium">Status</th>
                                <th className="p-3 font-medium">TrxID</th>
                            </tr>
                        </thead>
                        <tbody>
                            {payments.map((p) => (
                                <tr key={p.id} className="border-t border-line">
                                    <td className="p-3 whitespace-nowrap">{p.at}</td>
                                    <td className="p-3 font-mono text-xs">{p.invoice}</td>
                                    <td className="p-3 whitespace-nowrap">{p.user}</td>
                                    <td className="p-3">{p.product}</td>
                                    <td className="p-3 text-right tabular-nums">{p.amount}</td>
                                    <td className={`p-3 capitalize ${tone[p.status]}`}>
                                        {p.status}
                                        {p.gateway === 'fake' && <span className="text-xs text-warn"> (test)</span>}
                                    </td>
                                    <td className="p-3 font-mono text-xs">{p.trxId ?? '—'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </AdminShell>
    );
}
