import { Link, router, useForm } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import Icon from '../Components/Icon';
import Layout from '../Components/Layout';
import { Button } from '../Components/ui';
import { useT } from '../lib/i18n';

export function DestinationButton({ destination, variant = 'primary' }) {
    const { pick } = useT();
    const label = pick(destination.label);

    if (destination.method === 'post') {
        return (
            <Button variant={variant} onClick={() => router.post(destination.href)}>
                {label}
                <Icon name="arrow" className="size-4" />
            </Button>
        );
    }

    return (
        <Button variant={variant} href={destination.href}>
            {label}
            <Icon name="arrow" className="size-4" />
        </Button>
    );
}

/** The ask box, also used on the dashboard. */
export function AskBox({ autoFocus = false }) {
    const { t } = useT();
    const form = useForm({ message: '' });

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                form.post('/assistant', { preserveScroll: true, onSuccess: () => form.reset() });
            }}
            className="flex gap-2"
        >
            <label htmlFor="assistant-message" className="sr-only">
                {t('assistantTitle')}
            </label>
            <input
                id="assistant-message"
                value={form.data.message}
                onChange={(e) => form.setData('message', e.target.value)}
                placeholder={t('assistantPlaceholder')}
                maxLength={500}
                autoFocus={autoFocus}
                autoComplete="off"
                className="min-w-0 flex-1 rounded-xl border border-line bg-white px-4 py-3 text-lg outline-none focus:border-brand focus:ring-2 focus:ring-brand/30"
                required
            />
            <Button type="submit" disabled={form.processing || !form.data.message.trim()} aria-label={t('ask')}>
                {form.processing ? t('thinking') : t('ask')}
            </Button>
        </form>
    );
}

export default function Assistant({ turns, destinations }) {
    const { t } = useT();
    const end = useRef(null);

    useEffect(() => end.current?.scrollIntoView({ block: 'end' }), [turns.length]);

    return (
        <Layout title={t('assistantTitle')}>
            <div className="mb-4 flex items-center justify-between gap-3">
                <h1 className="text-2xl font-semibold">{t('assistantTitle')}</h1>
                {turns.length > 0 && (
                    <button type="button" className="text-sm font-medium text-muted underline" onClick={() => router.delete('/assistant')}>
                        {t('newConversation')}
                    </button>
                )}
            </div>

            {turns.length === 0 && <p className="mb-4 text-muted">{t('assistantHint')}</p>}

            <ol className="mb-4 space-y-3" aria-live="polite">
                {turns.map((turn, i) =>
                    turn.role === 'user' ? (
                        <li key={i} className="ml-10 rounded-2xl rounded-br-sm bg-brand px-4 py-3 text-white">
                            <span className="sr-only">{t('you')}: </span>
                            {turn.text}
                        </li>
                    ) : (
                        <li key={i} className="mr-6 rounded-2xl rounded-bl-sm border border-line bg-card px-4 py-3">
                            <p className="mb-2">{turn.text}</p>
                            {turn.destination && <DestinationButton destination={turn.destination} />}
                            {turn.suggest && (
                                <div className="mt-2 flex flex-wrap gap-2">
                                    {destinations.map((d) => (
                                        <DestinationButton key={d.key} destination={d} variant="secondary" />
                                    ))}
                                </div>
                            )}
                        </li>
                    ),
                )}
            </ol>
            <div ref={end} />

            <AskBox autoFocus />

            {turns.length === 0 && (
                <div className="mt-5 flex flex-wrap gap-2">
                    {destinations.map((d) => (
                        <DestinationButton key={d.key} destination={d} variant="secondary" />
                    ))}
                </div>
            )}

            <p className="mt-6 text-sm text-muted">
                <Link href="/forms" className="underline">
                    {t('formsTitle')}
                </Link>{' '}
                ·{' '}
                <Link href="/jobs" className="underline">
                    {t('jobsTitle')}
                </Link>
            </p>
        </Layout>
    );
}
