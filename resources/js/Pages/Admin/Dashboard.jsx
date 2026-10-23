import { Card } from '../../Components/ui';
import AdminShell from './AdminShell';

const tiles = [
    ['users', 'Registered users'],
    ['usersThisMonth', 'New this month'],
    ['premium', 'Premium users'],
    ['documents', 'Papers stored'],
    ['applications', 'Form applications'],
    ['applicationsSubmitted', 'Marked submitted'],
    ['jobKits', 'Job kits'],
    ['revenueThisMonth', 'Revenue this month (৳)'],
    ['openTickets', 'Open tickets'],
];

export default function Dashboard({ stats }) {
    return (
        <AdminShell title="Overview">
            <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                {tiles.map(([key, label]) => (
                    <Card key={key} className="p-4">
                        <p className="text-sm text-muted">{label}</p>
                        <p className="text-2xl font-semibold tabular-nums">{stats[key].toLocaleString('en-US')}</p>
                    </Card>
                ))}
            </div>
            <p className="mt-5 text-sm text-muted">
                Counts only. Staff have no access to anyone's papers or profile; ticket authors are shown by masked mobile number.
            </p>
        </AdminShell>
    );
}
