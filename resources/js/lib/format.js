export function money(amount, currency = 'GBP') {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(Number(amount || 0));
}

export function date(value, opts = { day: '2-digit', month: 'short', year: 'numeric' }) {
    if (!value) return '—';
    return new Intl.DateTimeFormat('en-GB', opts).format(new Date(value));
}

export function ago(value) {
    if (!value) return '—';
    const s = Math.round((Date.now() - new Date(value).getTime()) / 1000);
    const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });
    const units = [
        ['year', 31536000],
        ['month', 2592000],
        ['week', 604800],
        ['day', 86400],
        ['hour', 3600],
        ['minute', 60],
    ];
    for (const [unit, secs] of units) {
        if (Math.abs(s) >= secs) return rtf.format(-Math.round(s / secs), unit);
    }
    return 'just now';
}

export const pad = (n, len = 2) => String(n).padStart(len, '0');

export function bytes(n) {
    if (n < 1024) return `${n} B`;
    if (n < 1048576) return `${(n / 1024).toFixed(1)} KB`;
    return `${(n / 1048576).toFixed(1)} MB`;
}
