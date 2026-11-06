// Small display helpers shared by the Vibes components.
const compactFormat = new Intl.NumberFormat('en', { notation: 'compact', maximumFractionDigits: 1 });

/** 48200 → "48.2K" (counts on posts and reels). */
export const compact = (n) => compactFormat.format(Number(n || 0));

/** "$18.00" */
export const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

/** "3h", "2d"… from an ISO date; text that isn't a date ("5 minutes ago") is shown as it is. */
export function timeAgo(value) {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);
    const s = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));
    if (s < 60) return 'now';
    const steps = [[31536000, 'y'], [2592000, 'mo'], [604800, 'w'], [86400, 'd'], [3600, 'h'], [60, 'm']];
    for (const [size, unit] of steps) {
        if (s >= size) return Math.floor(s / size) + unit;
    }
    return 'now';
}

/** Splits a caption into plain text and #tags, so tags can be shown bold and tapped. */
export function captionParts(text) {
    const parts = [];
    const re = /#[\p{L}\p{N}_]+/gu;
    let last = 0;
    for (const m of String(text || '').matchAll(re)) {
        if (m.index > last) parts.push({ text: text.slice(last, m.index) });
        parts.push({ tag: m[0].toLowerCase(), text: m[0] });
        last = m.index + m[0].length;
    }
    if (last < String(text || '').length) parts.push({ text: String(text).slice(last) });
    return parts;
}

/** A reel's file is a video when the reel says so or the file looks like one. */
export const isVideoReel = (reel) => reel?.type === 'video' || /\.(mp4|mov|webm|m4v)(\?|$)/i.test(reel?.file_path || '');
