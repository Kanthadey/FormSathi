const paths = {
    home: 'M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z',
    papers: 'M7 3h7l5 5v12a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm7 0v5h5M9 13h7M9 17h5',
    user: 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm-7 9a7 7 0 0 1 14 0',
    settings: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm7.4-3a7.4 7.4 0 0 0-.1-1.2l2-1.6-2-3.4-2.4 1a7 7 0 0 0-2-1.2L14.5 3h-5l-.4 2.6a7 7 0 0 0-2 1.2l-2.4-1-2 3.4 2 1.6a7.4 7.4 0 0 0 0 2.4l-2 1.6 2 3.4 2.4-1a7 7 0 0 0 2 1.2l.4 2.6h5l.4-2.6a7 7 0 0 0 2-1.2l2.4 1 2-3.4-2-1.6c.1-.4.1-.8.1-1.2z',
    lock: 'M6 11h12v10H6zm2 0V7a4 4 0 0 1 8 0v4',
    camera: 'M4 7h3l2-3h6l2 3h3a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1zm8 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8z',
    image: 'M4 4h16v16H4zm0 12 5-5 4 4 2-2 5 5M15 9.5a1.5 1.5 0 1 0 0-.01',
    alert: 'M12 3 2 20h20zm0 6v5m0 3v.01',
    check: 'm5 12 5 5 9-10',
    plus: 'M12 5v14M5 12h14',
    clock: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zm0-13v4l3 2',
    shield: 'M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6zm-3 9 2 2 4-4',
    eye: 'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6z',
    trash: 'M4 7h16M10 11v6m4-6v6M6 7l1 13h10l1-13M9 7V4h6v3',
    arrow: 'M5 12h14m-6-6 6 6-6 6',
    back: 'M19 12H5m6 6-6-6 6-6',
    form: 'M9 3h6v3H9zM7 4.5H5V21h14V4.5h-2M8 11h8M8 15h8M8 19h5',
    copy: 'M9 9h11v11H9zM5 15H4V4h11v1',
    print: 'M7 9V3h10v6M7 17H4v-7h16v7h-3M7 14h10v7H7z',
    external: 'M14 4h6v6m0-6-9 9m7 1v6H4V6h6',
};

export default function Icon({ name, className = 'size-5' }) {
    return (
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="1.8"
            strokeLinecap="round"
            strokeLinejoin="round"
            className={className}
            aria-hidden="true"
        >
            <path d={paths[name]} />
        </svg>
    );
}
