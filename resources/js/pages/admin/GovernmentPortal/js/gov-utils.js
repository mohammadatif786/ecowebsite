const fmt = n => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const num = n => new Intl.NumberFormat('en-US').format(Math.round(n));

function scale() {
    const p = document.getElementById('periodFilter')?.value || 'Monthly';
    return p === 'Today' ? 1 / 30 : p === 'Weekly' ? .25 : p === 'Quarterly' ? 3 : p === 'Yearly' ? 12 : p === '5-Year' ? 60 : 1;
}

function userAvatar(name, size) {
    size = size || 38;
    const parts = (name || '?').trim().split(/\s+/);
    const ini = ((parts[0] || '')[0] || '') + ((parts[1] || '')[0] || '');
    let h = 0;
    for (let i = 0; i < (name || '').length; i++) { h = (h * 31 + name.charCodeAt(i)) >>> 0; }
    const hues = [(h % 360), ((h * 7) % 360)];
    const bg = 'linear-gradient(135deg,hsl(' + hues[0] + ',70%,55%),hsl(' + hues[1] + ',70%,45%))';
    return '<div style="width:' + size + 'px;height:' + size + 'px;border-radius:9999px;background:' + bg + ';display:grid;place-items:center;color:#fff;font-weight:900;font-size:' + (size * 0.38) + 'px;box-shadow:0 2px 6px rgba(0,0,0,.15);flex-shrink:0">' + ini.toUpperCase() + '</div>';
}

function userRegion(country) {
    if (country === 'United States' || country === 'Canada') return 'North America';
    if (typeof LAC_GEO !== 'undefined' && LAC_GEO[country]) return LAC_GEO[country].region;
    return 'Other';
}

function rows() {
    const rf = document.getElementById('regionFilter')?.value || 'All';
    const cf = document.getElementById('countryFilter')?.value || 'All Countries';
    return countries.filter(c => (rf === 'All' || c.region === rf) && (cf === 'All Countries' || c.country === cf));
}

function countryFin(c) {
    let gross = 0, platform = 0, bank = 0, cost = 0;
    const s = scale();
    units.forEach(u => {
        const v = (c[u.key] || 0) * s;
        gross += v;
        platform += v * u.platformRate;
        bank += v * u.bankRate;
        cost += v * u.costRate;
    });
    const top = units.map(u => [u.name, (c[u.key] || 0) * s]).sort((a, b) => b[1] - a[1])[0][0];
    return { gross, platform, bank, cost, net: platform - cost, top };
}

function totals() {
    const rs = rows().map(countryFin);
    const s = scale();
    return {
        gross: rs.reduce((s, x) => s + x.gross, 0),
        platform: rs.reduce((s, x) => s + x.platform, 0),
        bank: rs.reduce((s, x) => s + x.bank, 0),
        cost: rs.reduce((s, x) => s + x.cost, 0),
        net: rs.reduce((s, x) => s + x.net, 0),
        users: rows().reduce((s, c) => s + c.users, 0) * s,
        merchants: rows().reduce((s, c) => s + c.merchants, 0),
        organizers: rows().reduce((s, c) => s + c.organizers, 0),
        countries: rows().length
    };
}

function logActivity(msg, details) {
    console.log('Activity:', msg, details);
}
