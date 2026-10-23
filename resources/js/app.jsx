import { createInertiaApp, router } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

// Pages load on demand, so a phone on a slow network fetches only what it shows.
const pages = import.meta.glob('./Pages/**/*.jsx');

createInertiaApp({
    title: (title) => (title ? `${title} · FormSathi` : 'FormSathi'),
    resolve: (name) => pages[`./Pages/${name}.jsx`](),
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#0f6e62',
    },
});

// Keep <html lang> right after switching language, for screen readers and fonts.
// "success" also covers the language switch, which redirects back to the same page.
const syncLang = (event) => {
    document.documentElement.lang = event.detail.page.props.locale ?? 'bn';
};
router.on('navigate', syncLang);
router.on('success', syncLang);
