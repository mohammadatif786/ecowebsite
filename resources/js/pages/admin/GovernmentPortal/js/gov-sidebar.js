let lkGroupOpen = {}, lkListRender = {};
let activeSidebarId = 'govDashboardCommand';

const roleAccess = {
    'Super Admin': ['overview', 'events', 'commerce', 'finance', 'marketing', 'trust', 'admin', 'government'],
    'Finance': ['overview', 'finance', 'admin', 'government'],
    'Marketing': ['overview', 'marketing'],
    'Commerce Ops': ['overview', 'commerce', 'events'],
    'Trust & Safety': ['overview', 'trust', 'admin'],
    'News Manager': ['overview', 'marketing'],
    'Gov Agency — NIB Bahamas': ['government'],
    'Gov Agency — NIS Jamaica': ['government'],
    'Gov Agency — Supérate (DR)': ['government']
};

const govTenants = {
    'Gov Agency — NIB Bahamas': { agency: 'National Insurance Board', country: 'Bahamas' },
    'Gov Agency — NIS Jamaica': { agency: 'Ministry of Labour & SS', country: 'Jamaica' },
    'Gov Agency — Supérate (DR)': { agency: 'Gabinete de Política Social', country: 'Dominican Republic' }
};

let currentRole = (function () { try { return localStorage.getItem('linkupRole') || 'Super Admin'; } catch (e) { return 'Super Admin'; } })();
if (!roleAccess[currentRole]) currentRole = 'Super Admin';
let govAgencyContext = govTenants[currentRole] || null;

function setAdminRole(r) {
    currentRole = roleAccess[r] ? r : 'Super Admin';
    try { localStorage.setItem('linkupRole', currentRole); } catch (e) { }
    govAgencyContext = govTenants[currentRole] || null;
    logActivity('Switched view to role: ' + currentRole, { category: 'Auth', module: 'Access', record: currentRole });
    if (govAgencyContext) {
        showView('govDashboardCommand');
    } else {
        renderSidebar(activeSidebarId);
    }
    renderGovAll();
}

function lkToggle(ns, key) {
    lkGroupOpen[key] = !(lkGroupOpen[key] !== undefined ? lkGroupOpen[key] : (key.indexOf(':R:') > -1));
    var f = lkListRender[ns];
    if (f) {
        f();
        if (window.lucide) lucide.createIcons();
    }
}

function buildCountryAccordionRows(ns, items, countryFn, rowFn, colspan) {
    if (!items.length) return '<tr><td colspan="' + colspan + '" class="py-6 text-center text-slate-400 font-bold">No records.</td></tr>';
    var rm = {};
    items.forEach(function (it) {
        var c = countryFn(it) || 'Other';
        var r = userRegion(c);
        ((rm[r] = rm[r] || {})[c] = rm[r][c] || []).push(it);
    });
    var out = '';
    Object.keys(rm).sort().forEach(function (r) {
        var cs = rm[r];
        var total = Object.keys(cs).reduce(function (a, c) { return a + cs[c].length; }, 0);
        var rk = ns + ':R:' + r;
        var ropen = (lkGroupOpen[rk] !== undefined ? lkGroupOpen[rk] : true);
        out += '<tr><td colspan="' + colspan + '" onclick="lkToggle(\'' + ns + '\',\'' + rk + '\')" style="cursor:pointer" class="py-2 px-3 font-black text-white bg-gradient-to-r from-slate-800 to-slate-600">' + (ropen ? '▾' : '▸') + ' 🌎 ' + r + ' <span class="opacity-60">(' + total + ')</span></td></tr>';
        if (ropen) {
            Object.keys(cs).sort(function (a, b) { return cs[b].length - cs[a].length || a.localeCompare(b); }).forEach(function (c) {
                var ck = ns + ':C:' + c;
                var copen = (lkGroupOpen[ck] !== undefined ? lkGroupOpen[ck] : false);
                out += '<tr><td colspan="' + colspan + '" onclick="lkToggle(\'' + ns + '\',\'' + ck + '\')" style="cursor:pointer" class="py-2 px-6 font-black text-slate-700 bg-slate-100">' + (copen ? '▾' : '▸') + ' ' + c + ' <span class="opacity-50">(' + cs[c].length + ')</span></td></tr>';
                if (copen) out += cs[c].map(rowFn).join('');
            });
        }
    });
    return out;
}

function renderSidebar(activeId = 'executive') {
    console.log('Rendering sidebar for:', activeId);
    activeSidebarId = activeId;
    const sidebarNav = document.getElementById('sidebarNav');
    if (!sidebarNav) {
        console.error('sidebarNav element not found');
        return;
    }

    var allowed = roleAccess[currentRole] || ['overview', 'events', 'commerce', 'finance', 'marketing', 'trust', 'admin', 'government'];
    console.log('Current role:', currentRole, 'Allowed sections:', allowed);

    var roleSel = '<div class="px-3 pb-2 mb-1"><p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">View As</p><select onchange="setAdminRole(this.value)" class="w-full rounded-2xl border border-slate-200 px-3 py-2 font-bold text-sm">' + Object.keys(roleAccess).map(function (r) { return '<option' + (r === currentRole ? ' selected' : '') + '>' + r + '</option>'; }).join('') + '</select></div>';

    const overviewActive = ['executive', 'feeRevenueCommand', 'users', 'business', 'countries', 'forecast', 'assistant'].includes(activeId);
    const overviewHtml = [
        navButton(['executive', 'layout-dashboard', 'Dashboard'], activeId),
        navButton(['feeRevenueCommand', 'trending-up', 'Fee Revenue Dashboard'], activeId),
        navButton(['users', 'users', 'Users'], activeId),
        navButton(['business', 'boxes', 'Business Units'], activeId),
        navButton(['countries', 'globe-2', 'Countries'], activeId),
        navButton(['forecast', 'trending-up', 'Forecasting'], activeId),
        navButton(['assistant', 'bot', 'Ask AI'], activeId)
    ].join('');

    const hasGovPages = typeof govPages !== 'undefined';
    const isGovPage = hasGovPages && govPages.some(p => p[0] === activeId);
    const financeActive = isGovPage || ['remittanceCommand', 'settlementCommand', 'payoutCommand', 'pricingCommand', 'scotiaPortal'].includes(activeId);

    let financeItems = [];
    if (hasGovPages) {
        financeItems.push(accordionBlock('Government Portal', 'landmark', localStorage.getItem('govMenuOpen') === 'true', isGovPage, 'toggleGovMenu', govPages, activeId));
    } else {
        console.warn('govPages not defined');
    }

    financeItems.push(navButton(['remittanceCommand', 'send', 'Remittance'], activeId));
    financeItems.push(navButton(['settlementCommand', 'receipt', 'Settlements'], activeId));
    financeItems.push(navButton(['payoutCommand', 'banknote', 'Payout Ops'], activeId));
    financeItems.push(navButton(['pricingCommand', 'sliders-horizontal', 'Pricing'], activeId));
    financeItems.push(navButton(['scotiaPortal', 'landmark', 'Scotiabank Portal'], activeId));

    const html = [
        roleSel,
        allowed.includes('overview') ? categoryBlock('Overview', 'layout-dashboard', 'overview', overviewHtml, overviewActive) : '',
        (allowed.includes('finance') || allowed.includes('government')) ? categoryBlock('Finance & Government', 'landmark', 'finance', financeItems.join(''), financeActive) : ''
    ].join('');

    sidebarNav.innerHTML = html;
    console.log('Sidebar HTML updated. Content length:', html.length);
    if (window.lucide) lucide.createIcons();
}

function accordionBlock(label, icon, isOpen, isActive, toggleFn, children, activeId) {
    return `
    <div class="space-y-1">
      <button onclick="${toggleFn}()" class="w-full flex items-center justify-between px-4 py-3 rounded-2xl font-bold ${isActive ? 'nav-active' : ''}">
        <span class="flex gap-3 items-center"><i data-lucide="${icon}"></i> ${label}</span>
        <i data-lucide="${isOpen || isActive ? 'chevron-down' : 'chevron-right'}" class="w-4 h-4"></i>
      </button>
      <div class="${isOpen || isActive ? '' : 'hidden'} ml-5 pl-3 border-l border-slate-200 space-y-1">
        ${children.map(child => `<button onclick="showView('${child[0]}')" class="nav-btn ${activeId === child[0] ? 'nav-active' : ''} w-full flex gap-3 items-center px-4 py-2.5 rounded-2xl text-sm font-bold"><i data-lucide="${child[1]}" class="w-4 h-4"></i> ${child[2]}</button>`).join('')}
      </div>
    </div>`;
}

function categoryBlock(label, icon, key, innerHtml, active) {
    const isOpen = localStorage.getItem('cat_' + key) === 'true' || active;
    return `
    <div class="space-y-1">
      <button onclick="toggleCat('${key}')" class="w-full flex items-center justify-between px-3 py-2 mt-1 rounded-2xl text-xs font-black uppercase tracking-wide ${active ? 'text-purple-700' : 'text-slate-500'} hover:bg-slate-50">
        <span class="flex gap-2 items-center"><i data-lucide="${icon}" class="w-4 h-4"></i> ${label}</span>
        <i data-lucide="${isOpen ? 'chevron-down' : 'chevron-right'}" class="w-4 h-4"></i>
      </button>
      <div class="${isOpen ? '' : 'hidden'} space-y-1 pb-1">${innerHtml}</div>
    </div>`;
}

function navButton(p, activeId) {
    return `<button onclick="showView('${p[0]}')" class="nav-btn ${activeId === p[0] ? 'nav-active' : ''} w-full flex gap-3 items-center px-4 py-3 rounded-2xl font-bold"><i data-lucide="${p[1]}"></i> ${p[2]}</button>`;
}

function toggleCat(key) {
    const cur = localStorage.getItem('cat_' + key) === 'true';
    localStorage.setItem('cat_' + key, String(!cur));
    if (key === 'government') {
        localStorage.setItem('govMenuOpen', String(!cur));
    }
    renderSidebar(activeSidebarId);
}

function toggleGovMenu() {
    const cur = localStorage.getItem('govMenuOpen') === 'true';
    localStorage.setItem('govMenuOpen', String(!cur));
    renderSidebar(activeSidebarId);
}
