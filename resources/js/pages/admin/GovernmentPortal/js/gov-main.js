function renderHeaderRibbon() {
    const t = totals();
    const set = (id, v) => { const e = document.getElementById(id); if (e) e.textContent = v; };
    set('rGTV', fmt(t.gross));
    set('rLinkUp', fmt(t.platform));
    set('rBank', fmt(t.bank));
    set('rNet', fmt(t.net + t.bank * (1 - SCOTIA_SHARE)));
    set('rUsers', num(t.users));
    set('rMerchants', num(t.merchants));
    set('rOrganizers', num(t.organizers));
    set('rCountries', num(t.countries));
    set('sideGTV', fmt(t.gross));
}

function renderAll() {
    renderHeaderRibbon();
    renderGovAll();
    if (window.lucide) lucide.createIcons();
}

function showView(id) {
    document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
    const view = document.getElementById(id);
    if (view && id !== '__placeholder') {
        view.classList.remove('hidden');
    } else {
        const ph = document.getElementById('__placeholder');
        if (ph) {
            ph.classList.remove('hidden');
            const n = document.getElementById('__phName');
            if (n) n.textContent = labelFor(id);
        }
    }
    activeSidebarId = id;
    const pt = document.getElementById('pageTitle');
    if (pt) pt.textContent = labelFor(id);
    renderSidebar(id);
    renderAll();
}

function labelFor(id) {
    const allNav = [].concat(pages, govPages, adManagementPages, eventManagementPages, organizerPages, marketplacePages, walletPages, eatsPages, emailPages, newsPages, taxPages, adminPages, settingsPages, feesPages, merchantPages, feedPages);
    const m = allNav.find(p => p[0] === id);
    return m ? m[2] : id;
}

function runGlobalSearch() {
    const r = document.getElementById('searchResults');
    if (r) r.classList.add('hidden');
}

function downloadCSV() { alert('Export CSV is available in the full app.'); }
function exportCurrentView() { alert('Export View is available in the full app.'); }
function openHelp() { alert('Help center is available in the full app.'); }
function openRate() { alert('Live FX rates are managed in the full app.'); }

// Initialize
function init() {
    console.log('Initializing application...');
    ['cat_finance', 'govMenuOpen'].forEach(k => { try { if (localStorage.getItem(k) === null) localStorage.setItem(k, 'true'); } catch (e) { } });

    const cf = document.getElementById('countryFilter');
    if (cf) {
        cf.innerHTML = '<option>All Countries</option>' + countries.map(c => `<option>${c.country}</option>`).join('');
    }

    // Ensure constants are loaded before rendering
    if (typeof govPages === 'undefined') {
        console.error('govPages is not defined during init');
    }

    showView('govDashboardCommand');
    console.log('Application initialized.');
}

window.addEventListener('DOMContentLoaded', init);
