@theme {
    --color-cloud: #F8FAFC;
    --color-tranquil: #8DB3E2;
    --color-navy: #1E3A8A;
    --font-sans: 'Montserrat', ui-sans-serif, system-ui, sans-serif;
    --font-brand: 'Poetsen One', 'Montserrat', sans-serif;
    --shadow-soft: 0 2px 12px -2px rgb(30 58 138 / 0.08);
    --shadow-lift: 0 18px 40px -12px rgb(30 58 138 / 0.22);
    --spacing-18: 4.5rem;
    --animate-float: float 7s ease-in-out infinite;
    --animate-drift: drift 24s ease-in-out infinite;

    @keyframes float {
        0%, 100% { translate: 0 0; }
        50% { translate: 0 -12px; }
    }

    @keyframes drift {
        0%, 100% { translate: 0 0; }
        50% { translate: 40px -10px; }
    }
}

/* Brand wave swash under accent words in headings (same curve as the wave component). */
.wave-underline {
    padding-bottom: 0.18em;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 14' preserveAspectRatio='none'%3E%3Cpath d='M3 9C36 4 72 2 114 6.5C144 9.5 166 12 197 7' fill='none' stroke='%238DB3E2' stroke-width='4' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 0 100%;
    background-size: 100% 0.32em;
    -webkit-box-decoration-break: clone;
    box-decoration-break: clone;
}
.wave-underline.on-dark {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 14' preserveAspectRatio='none'%3E%3Cpath d='M3 9C36 4 72 2 114 6.5C144 9.5 166 12 197 7' fill='none' stroke='%23FFFFFF' stroke-opacity='.55' stroke-width='4' stroke-linecap='round'/%3E%3C/svg%3E");
}

/* Admin-authored rich text (pages, news). */
.rich-text { line-height: 1.8; color: rgb(30 58 138 / 0.85); }
.rich-text > * + * { margin-top: 1.1em; }
.rich-text h2 { margin-top: 2em; font-family: var(--font-brand); font-size: 1.6rem; line-height: 1.25; color: var(--color-navy); }
.rich-text h3 { margin-top: 1.6em; font-size: 1.15rem; font-weight: 700; color: var(--color-navy); }
.rich-text h2:first-child, .rich-text h3:first-child { margin-top: 0; }
.rich-text a { font-weight: 600; text-decoration: underline; text-underline-offset: 4px; color: var(--color-navy); }
.rich-text strong { color: var(--color-navy); }
.rich-text ul, .rich-text ol { padding-left: 1.25rem; }
.rich-text ul { list-style: disc; }
.rich-text ol { list-style: decimal; }
.rich-text li + li { margin-top: 0.4em; }
.rich-text li::marker { color: var(--color-tranquil); }
.rich-text blockquote { border-left: 4px solid var(--color-tranquil); padding-left: 1rem; font-style: italic; }
.rich-text img { border-radius: 1.5rem; }

@media (prefers-reduced-motion: reduce) {
    .animate-float, .animate-drift { animation: none; }
}
