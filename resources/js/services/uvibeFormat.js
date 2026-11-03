/** Small display helpers the U Vibe feed components share. */

export const num = (n) => Number(n || 0).toLocaleString();

export const money = (n) =>
    '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const MONTHS = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

/** "2026-11-05" -> "NOV 5" (empty when it isn't a date). */
export const fmtDate = (d) => {
    const dt = new Date(d);
    return isNaN(dt) ? '' : `${MONTHS[dt.getMonth()]} ${dt.getDate()}`;
};

/** The name a post is shown under (a person, an organization, a group or the university). */
export const posterName = (p) => {
    const nameOf = (v) => (typeof v === 'string' ? v : v?.name || '');
    const group = nameOf(p.group);
    return group && group !== 'You' ? group : nameOf(p.org) || 'University';
};
