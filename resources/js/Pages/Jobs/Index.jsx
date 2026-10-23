import { Link } from '@inertiajs/react';
import Icon from '../../Components/Icon';
import Layout from '../../Components/Layout';
import { Button, Card } from '../../Components/ui';
import { useT } from '../../lib/i18n';

export default function Index({ kits }) {
    const { t, date } = useT();

    return (
        <Layout title={t('jobsTitle')}>
            <div className="mb-2 flex items-center justify-between gap-3">
                <h1 className="text-2xl font-semibold">{t('jobsTitle')}</h1>
                <Button href="/jobs/new">
                    <Icon name="plus" />
                    {t('newJobKit')}
                </Button>
            </div>
            <p className="mb-5 text-muted">{t('jobsIntro')}</p>

            {kits.length === 0 ? (
                <Card>
                    <p className="text-muted">{t('noKits')}</p>
                </Card>
            ) : (
                <ul className="space-y-2">
                    {kits.map((kit) => (
                        <li key={kit.id}>
                            <Link href={`/jobs/${kit.id}`} className="flex items-center gap-3 rounded-xl border border-line bg-card p-4 hover:border-brand">
                                <span className="min-w-0 flex-1" lang="en">
                                    <span className="block truncate font-medium">{kit.jobTitle || '—'}</span>
                                    <span className="block text-sm text-muted">
                                        {[kit.company, date(kit.date)].filter(Boolean).join(' · ')}
                                    </span>
                                </span>
                                <Icon name="arrow" className="size-5 shrink-0 text-muted" />
                            </Link>
                        </li>
                    ))}
                </ul>
            )}

            <p className="mt-6">
                <Link href="/career" className="font-medium text-brand underline">
                    {t('editCareer')}
                </Link>
            </p>
        </Layout>
    );
}
