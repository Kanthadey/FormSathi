import { Link } from '@inertiajs/react';
import Icon from './Icon';

const buttonStyles = {
    primary: 'bg-brand text-white hover:bg-brand-strong disabled:opacity-60',
    secondary: 'bg-card text-ink border border-line hover:border-brand hover:text-brand',
    danger: 'bg-danger text-white hover:opacity-90',
    ghost: 'text-brand hover:bg-brand-soft',
};

export function Button({ variant = 'primary', href, external = false, className = '', children, ...props }) {
    const classes = `inline-flex min-h-12 items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-base font-semibold transition-colors ${buttonStyles[variant]} ${className}`;

    if (href && external) {
        return (
            <a href={href} target="_blank" rel="noopener noreferrer" className={classes} {...props}>
                {children}
            </a>
        );
    }

    if (href) {
        return (
            <Link href={href} className={classes} {...props}>
                {children}
            </Link>
        );
    }

    return (
        <button className={classes} {...props}>
            {children}
        </button>
    );
}

export function Card({ className = '', children }) {
    return <div className={`rounded-2xl border border-line bg-card p-5 ${className}`}>{children}</div>;
}

export function FieldError({ message, id }) {
    if (!message) return null;
    return (
        <p id={id} className="mt-1.5 flex items-start gap-1.5 text-sm text-danger" role="alert">
            <Icon name="alert" className="mt-0.5 size-4 shrink-0" />
            {message}
        </p>
    );
}

export function TextField({ label, error, hint, id, as = 'input', className = '', ...props }) {
    const Tag = as;
    const describedBy = [error && `${id}-error`, hint && `${id}-hint`].filter(Boolean).join(' ') || undefined;

    return (
        <div className={className}>
            <label htmlFor={id} className="mb-1.5 block font-medium">
                {label}
            </label>
            <Tag
                id={id}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={describedBy}
                className={`block w-full rounded-xl border bg-white px-4 py-3 text-lg outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 ${
                    error ? 'border-danger' : 'border-line'
                }`}
                {...props}
            />
            {hint && (
                <p id={`${id}-hint`} className="mt-1.5 text-sm text-muted">
                    {hint}
                </p>
            )}
            <FieldError message={error} id={`${id}-error`} />
        </div>
    );
}

/** A numeric code or PIN field: big, spaced, numeric keypad on phones. */
export function CodeField(props) {
    return (
        <TextField
            inputMode="numeric"
            autoComplete="one-time-code"
            maxLength={6}
            className="[&_input]:text-center [&_input]:text-2xl [&_input]:tracking-[0.5em]"
            {...props}
        />
    );
}

const alertStyles = {
    info: 'bg-brand-soft text-brand-strong',
    success: 'bg-ok-soft text-ok',
    warn: 'bg-warn-soft text-warn',
    danger: 'bg-danger-soft text-danger',
};

const alertIcons = { info: 'shield', success: 'check', warn: 'clock', danger: 'alert' };

export function Alert({ tone = 'info', children, className = '' }) {
    return (
        <div className={`flex items-start gap-3 rounded-xl p-4 ${alertStyles[tone]} ${className}`} role="status">
            <Icon name={alertIcons[tone]} className="mt-0.5 size-5 shrink-0" />
            <div className="min-w-0">{children}</div>
        </div>
    );
}
