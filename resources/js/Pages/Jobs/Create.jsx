import { Link, useForm } from '@inertiajs/react';
import Icon from '../../Components/Icon';
import Layout from '../../Components/Layout';
import { Alert, Button, TextField } from '../../Components/ui';
import { useT } from '../../lib/i18n';

export default function Create({ aiAvailable, career }) {
    const { t } = useT();
    const form = useForm({ advert: '' });
    const thin = career.work + career.education === 0 || career.skill === 0;

    return (
        <Layout title={t('newJobKit')}>
            <Link href="/jobs" className="mb-3 inline-flex items-center gap-1.5 font-medium text-muted">
                <Icon name="back" className="size-4" />
                {t('jobsTitle')}
            </Link>
            <h1 className="mb-1 text-2xl font-semibold">{t('newJobKit')}</h1>
            <p className="mb-5 text-muted">{t('jobsIntro')}</p>

            {!aiAvailable && (
                <Alert tone="warn" className="mb-5">
                    {t('aiOff')}
                </Alert>
            )}

            <Alert tone={thin ? 'warn' : 'info'} className="mb-5">
                {thin && <p className="font-semibold">{t('careerMissing')}</p>}
                <p>{t('careerCounts', career)}</p>
                <Link href="/career" className="font-medium underline">
                    {t('editCareer')}
                </Link>
            </Alert>

            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post('/jobs');
                }}
                className="space-y-4"
            >
                <TextField
                    id="advert"
                    as="textarea"
                    rows={12}
                    label={t('advertLabel')}
                    hint={t('advertHint')}
                    value={form.data.advert}
                    onChange={(e) => form.setData('advert', e.target.value)}
                    error={form.errors.advert}
                    maxLength={12000}
                    required
                    className="[&_textarea]:text-base"
                />
                <Button type="submit" className="w-full" disabled={!aiAvailable || form.processing || form.data.advert.trim().length < 80}>
                    {form.processing ? t('generating') : t('generate')}
                </Button>
            </form>
        </Layout>
    );
}
