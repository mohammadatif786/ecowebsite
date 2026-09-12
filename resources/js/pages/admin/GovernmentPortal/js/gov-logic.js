function govPersist() {
    try {
        localStorage.setItem('linkupGovConfig', JSON.stringify(govConfig));
        localStorage.setItem('linkupGovFloat', JSON.stringify(govFloat));
        localStorage.setItem('linkupGovBatches', JSON.stringify(govBatches));
        localStorage.setItem('linkupGovBeneficiaries', JSON.stringify(govBeneficiaries));
    } catch (e) { }
}

function govRegFilter(arr) {
    var rf = document.getElementById('regionFilter')?.value || 'All';
    var cf = document.getElementById('countryFilter')?.value || 'All Countries';
    return arr.filter(function (x) {
        var c = countries.find(function (y) { return y.country === x.country; });
        return (rf === 'All' || (c && c.region === rf)) && (cf === 'All Countries' || x.country === cf);
    });
}

function govBelongs(programName) {
    var c = govAgencyContext;
    if (!c) return true;
    var p = govPrograms.find(function (x) { return x.name === programName; });
    return p && p.agency === c.agency && p.country === c.country;
}

function govScopePrograms() {
    var c = govAgencyContext;
    return c ? govPrograms.filter(function (p) { return p.agency === c.agency && p.country === c.country; }) : govRegFilter(govPrograms);
}

function govScopeBen() {
    var c = govAgencyContext;
    return c ? govBeneficiaries.filter(function (b) { return govBelongs(b.program); }) : govRegFilter(govBeneficiaries);
}

function govScopeBatches() {
    var c = govAgencyContext;
    return c ? govBatches.filter(function (b) { return govBelongs(b.program); }) : govBatches;
}

function govMonthlyPayouts(p) {
    return p.beneficiaries * (p.schedule === 'Monthly' ? 1 : p.schedule === 'Bi-Monthly' ? 0.5 : p.schedule === 'Weekly' ? 4 : 1);
}

function govFeePer(amount) {
    return govConfig.flat + Math.min(amount * govConfig.pct / 100, govConfig.cap);
}

function govDisbursementFeesMonthly(list) {
    return list.reduce(function (a, p) { return a + govMonthlyPayouts(p) * govFeePer(p.avg); }, 0);
}

function govSaasMonthly(list) {
    var ag = {};
    list.forEach(function (p) { ag[p.agency + '|' + p.country] = 1; });
    return Object.keys(ag).length * govConfig.saas;
}

function govFloatYieldMonthly() {
    return govFloat.balance * (govConfig.yield / 100) / 12 * (govConfig.split / 100);
}

function govPayoutVolMonthly(list) {
    return list.reduce(function (a, p) { return a + govMonthlyPayouts(p) * p.avg; }, 0);
}

function govLinkupRevenue() {
    var list = govRegFilter(govPrograms);
    return (govDisbursementFeesMonthly(list) + govSaasMonthly(list) + govFloatYieldMonthly()) * scale();
}

function govPreFund() {
    if (govAgencyContext) return;
    var amt = parseFloat(document.getElementById('govFundAmt')?.value || '0') || 0;
    if (amt <= 0) return;
    govFloat.balance += amt;
    govPersist();
    logActivity('Pre-funded Government Treasury float $' + amt.toLocaleString(), { category: 'Update', module: 'Government Portal', record: 'float' });
    var n = document.getElementById('govFundNote');
    if (n) n.textContent = '✓ $' + amt.toLocaleString() + ' added to Scotiabank float.';
    var i = document.getElementById('govFundAmt');
    if (i) i.value = '';
    renderGovDashboard();
}

function govSaveFees() {
    if (govAgencyContext) return;
    var g = function (id) { return parseFloat(document.getElementById(id)?.value || '0') || 0; };
    govConfig = { flat: g('govFeeFlat'), pct: g('govFeePct'), cap: g('govFeeCap'), cashout: g('govFeeCashout'), saas: g('govFeeSaas'), yield: g('govFeeYield'), split: g('govFeeSplit') };
    govPersist();
    logActivity('Updated Government Portal fee schedule', { category: 'Update', module: 'Government Portal', record: 'fees' });
    var n = document.getElementById('govFeeNote');
    if (n) { n.textContent = '✓ Fee schedule saved — applied to revenue everywhere.'; setTimeout(function () { n.textContent = ''; }, 3000); }
    renderAll();
}

function govHydrateFees() {
    var s = function (id, v) { var e = document.getElementById(id); if (e && document.activeElement !== e) e.value = v; };
    s('govFeeFlat', govConfig.flat);
    s('govFeePct', govConfig.pct);
    s('govFeeCap', govConfig.cap);
    s('govFeeCashout', govConfig.cashout);
    s('govFeeSaas', govConfig.saas);
    s('govFeeYield', govConfig.yield);
    s('govFeeSplit', govConfig.split);
}

var govBenQuery = '';
function govBenSearch(v) {
    govBenQuery = (v || '').toLowerCase();
    renderGovBeneficiaries();
}

function govOpenBeneficiary(id) {
    var m = document.getElementById('govBenModal'); if (!m) return;
    var b = id ? govBeneficiaries.find(function (x) { return x.id === id; }) : null;
    document.getElementById('govBenModalTitle').textContent = b ? ('Edit Beneficiary — ' + b.name) : 'Enroll Beneficiary';
    document.getElementById('gbEditId').value = b ? b.id : '';
    document.getElementById('gbName').value = b ? b.name : '';
    document.getElementById('gbNatId').value = b ? b.natId : '';
    var psel = document.getElementById('gbProgram'); psel.innerHTML = govPrograms.map(function (p) { return '<option' + (b && b.program === p.name ? ' selected' : '') + '>' + p.name + '</option>'; }).join('');
    document.getElementById('gbMonthly').value = b ? b.monthly : '';
    document.getElementById('gbWallet').value = b ? b.wallet : 'Auto-Created';
    document.getElementById('gbKyc').value = b ? b.kyc : 'Tier 1';
    m.classList.remove('hidden'); m.classList.add('flex'); if (window.lucide) lucide.createIcons();
}

function govCloseBeneficiary() {
    var m = document.getElementById('govBenModal');
    if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
}

function govSaveBeneficiary() {
    var id = document.getElementById('gbEditId').value; var name = document.getElementById('gbName').value.trim(); if (!name) { alert('Name required.'); return; }
    var prog = govPrograms.find(function (p) { return p.name === document.getElementById('gbProgram').value; });
    var data = { name: name, natId: document.getElementById('gbNatId').value.trim() || '—', program: document.getElementById('gbProgram').value, country: prog ? prog.country : 'Bahamas', monthly: parseFloat(document.getElementById('gbMonthly').value) || 0, wallet: document.getElementById('gbWallet').value, kyc: document.getElementById('gbKyc').value, pol: 'Due' };
    if (id) { var b = govBeneficiaries.find(function (x) { return x.id === id; }); if (b) Object.assign(b, data); logActivity('Edited beneficiary ' + name, { category: 'Update', module: 'Government Portal', record: id }); }
    else { var nid = 'GB-' + String(govBeneficiaries.reduce(function (m, x) { var n = parseInt((x.id || '').replace('GB-', '')) || 0; return n > m ? n : m; }, 0) + 1).padStart(4, '0'); data.id = nid; govBeneficiaries.push(data); logActivity('Enrolled beneficiary ' + name + (data.wallet === 'Auto-Created' ? ' (auto-created wallet)' : ''), { category: 'Create', module: 'Government Portal', record: nid }); }
    govPersist(); govCloseBeneficiary(); renderGovBeneficiaries(); if (window.lucide) lucide.createIcons();
}

function govDeleteBeneficiary(id) {
    var b = govBeneficiaries.find(function (x) { return x.id === id; }); if (!b) return;
    if (!confirm('Remove ' + b.name + ' from the registry?')) return;
    govBeneficiaries = govBeneficiaries.filter(function (x) { return x.id !== id; });
    govPersist(); logActivity('Removed beneficiary ' + b.name, { category: 'Delete', module: 'Government Portal', record: id });
    renderGovBeneficiaries(); if (window.lucide) lucide.createIcons();
}

function govVerifyPol(id) {
    var b = govBeneficiaries.find(function (x) { return x.id === id; }); if (!b) return;
    b.pol = 'Verified'; govPersist(); logActivity('Verified proof-of-life for ' + b.name, { category: 'Compliance', module: 'Government Portal', record: id });
    renderGovBeneficiaries(); if (window.lucide) lucide.createIcons();
}

function govCreateBatch() {
    var psel = document.getElementById('govBatchProgram');
    var pname = psel?.value; var p = govPrograms.find(function (x) { return x.name === pname; }); if (!p) return;
    var count = Math.round(govMonthlyPayouts(p)); var net = count * p.avg; var fee = count * govFeePer(p.avg);
    var id = 'GD-' + Math.floor(5100 + Math.random() * 800);
    govBatches.unshift({ id: id, program: p.name, country: p.country, count: count, net: net, fee: Math.round(fee), method: 'Wallet (Scotiabank float)', status: 'Draft', date: new Date().toISOString().slice(0, 10) });
    govPersist(); logActivity('Created disbursement batch ' + id + ' for ' + p.name, { category: 'Create', module: 'Government Portal', record: id });
    var n = document.getElementById('govBatchNote'); if (n) n.textContent = 'Draft batch ' + id + ' created.';
    renderGovDisbursements(); if (window.lucide) lucide.createIcons();
}

function govApproveBatch(id) {
    var b = govBatches.find(function (x) { return x.id === id; }); if (!b) return;
    b.status = 'Approved'; govPersist(); logActivity('Approved disbursement batch ' + id, { category: 'Update', module: 'Government Portal', record: id });
    renderGovDisbursements(); if (window.lucide) lucide.createIcons();
}

function govDisburseBatch(id) {
    if (govAgencyContext) { alert('Fund release is performed by LinkUp. Your batch is queued for release.'); return; }
    var b = govBatches.find(function (x) { return x.id === id; }); if (!b) return;
    if (govFloat.balance < b.net) { alert('Insufficient Treasury float. Pre-fund $' + (b.net - govFloat.balance).toLocaleString() + ' more before disbursing.'); return; }
    govFloat.balance -= b.net; b.status = 'Disbursed'; b.date = new Date().toISOString().slice(0, 10);
    govPersist(); logActivity('Disbursed ' + b.id + ' — $' + b.net.toLocaleString() + ' to ' + b.count.toLocaleString() + ' wallets (' + b.program + ')', { category: 'Compliance', module: 'Government Portal', record: id });
    renderGovDisbursements(); renderGovDashboard(); if (window.lucide) lucide.createIcons();
}

function renderGovDashboard() {
    if (!document.getElementById('govKpis')) return;
    var ctx = govAgencyContext; var sc = scale(); var list = govScopePrograms();
    var totalBen = list.reduce(function (a, p) { return a + p.beneficiaries; }, 0);
    var disbursed = govPayoutVolMonthly(list) * sc;
    var rev = govLinkupRevenue();

    var host = document.getElementById('govKpis'); var bn = document.getElementById('govTenantBanner');
    if (ctx) {
        if (!bn) { bn = document.createElement('div'); bn.id = 'govTenantBanner'; bn.className = 'rounded-2xl bg-red-600 text-white px-5 py-3 font-black flex items-center gap-2 mb-4'; host.parentNode.insertBefore(bn, host); }
        bn.textContent = '🏛️ ' + ctx.agency + ' — ' + ctx.country + '  ·  Agency self-service portal — funds are custodied & released by LinkUp.';
    } else if (bn) { bn.remove(); }

    var fourth = ctx
        ? '<div class="card rounded-3xl p-5"><p class="text-slate-500 font-bold">Next Pay Cycle</p><h3 class="text-3xl font-black text-sky-600">Monthly</h3><p class="text-xs text-slate-500">approve to schedule</p></div>'
        : '<div class="card rounded-3xl p-5"><p class="text-slate-500 font-bold">LinkUp Gov Revenue</p><h3 class="text-3xl font-black text-purple-600">$' + Math.round(rev).toLocaleString() + '</h3><p class="text-xs text-slate-500">fees + SaaS + float yield</p></div>';

    document.getElementById('govKpis').innerHTML =
        '<div class="card rounded-3xl p-5"><p class="text-slate-500 font-bold">Beneficiaries</p><h3 class="text-4xl font-black">' + totalBen.toLocaleString() + '</h3><p class="text-xs text-slate-500">' + list.length + ' programs</p></div>' +
        '<div class="card rounded-3xl p-5"><p class="text-slate-500 font-bold">Disbursed (period)</p><h3 class="text-3xl font-black text-green-600">$' + Math.round(disbursed).toLocaleString() + '</h3><p class="text-xs text-slate-500">to citizen wallets</p></div>' +
        '<div class="card rounded-3xl p-5"><p class="text-slate-500 font-bold">Scotiabank Float</p><h3 class="text-3xl font-black">$' + Math.round(govFloat.balance).toLocaleString() + '</h3><p class="text-xs text-slate-500">' + (ctx ? 'managed by LinkUp' : 'pre-funded escrow') + '</p></div>' +
        fourth;

    var fundAmt = document.getElementById('govFundAmt');
    if (fundAmt) { var fundRow = fundAmt.closest('div'); if (fundRow) fundRow.style.display = ctx ? 'none' : ''; }
    var fundNote = document.getElementById('govFundNote'); if (fundNote) fundNote.style.display = ctx ? 'none' : '';

    var fb = document.getElementById('govFloatBox');
    if (fb) {
        var monthOut = govPayoutVolMonthly(govPrograms);
        fb.innerHTML =
            '<div class="flex justify-between"><span class="text-slate-500 font-bold">Available Balance</span><span class="font-black text-lg">$' + Math.round(govFloat.balance).toLocaleString() + '</span></div>' +
            '<div class="flex justify-between"><span class="text-slate-500 font-bold">Monthly Payout Need</span><span class="font-black">$' + Math.round(monthOut).toLocaleString() + '</span></div>' +
            '<div class="flex justify-between"><span class="text-slate-500 font-bold">Coverage</span><span class="font-black ' + (govFloat.balance >= monthOut ? 'text-green-600' : 'text-rose-600') + '">' + (monthOut ? Math.round(govFloat.balance / monthOut * 100) : 0) + '%</span></div>' +
            '<div class="flex justify-between"><span class="text-slate-500 font-bold">Float Yield (LinkUp/mo)</span><span class="font-black text-purple-600">$' + Math.round(govFloatYieldMonthly()).toLocaleString() + '</span></div>';
    }

    var pt = document.getElementById('govProgramsTable');
    if (pt) {
        lkListRender['govprograms'] = renderGovDashboard;
        pt.innerHTML = buildCountryAccordionRows('govprograms', list, function (p) { return p.country; }, function (p) {
            return '<tr class="border-t"><td class="py-2 font-black">' + p.name + '</td><td class="text-slate-500">' + p.agency + '</td><td>' + p.country + '</td><td>' + p.beneficiaries.toLocaleString() + '</td><td class="font-black">$' + Math.round(govMonthlyPayouts(p) * p.avg).toLocaleString() + '</td><td>' + p.schedule + '</td><td><span class="rounded-full px-2.5 py-0.5 text-xs font-black ' + (p.status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700') + '">' + p.status + '</span></td></tr>';
        }, 7);
    }

    var bt = document.getElementById('govBatchesTable');
    if (bt) bt.innerHTML = govScopeBatches().slice(0, 8).map(function (b) { return '<tr class="border-t"><td class="py-2 font-black">' + b.id + '</td><td>' + b.program + '</td><td>' + b.country + '</td><td>' + b.count.toLocaleString() + '</td><td class="font-black">$' + b.net.toLocaleString() + '</td><td class="text-purple-600 font-bold">$' + b.fee.toLocaleString() + '</td><td class="text-xs text-slate-500">' + b.method + '</td><td><span class="rounded-full px-2.5 py-0.5 text-xs font-black ' + (b.status === 'Disbursed' ? 'bg-green-50 text-green-700' : b.status === 'Approved' ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-600') + '">' + b.status + '</span></td></tr>'; }).join('') || '<tr><td colspan="8" class="py-6 text-center text-slate-400 font-bold">No batches.</td></tr>';
}

function renderGovBeneficiaries() {
    if (!document.getElementById('govBenTable')) return;
    var list = govScopeBen().filter(function (b) { return !govBenQuery || (b.name + ' ' + b.natId + ' ' + b.program).toLowerCase().includes(govBenQuery); });
    var auto = list.filter(function (b) { return b.wallet === 'Auto-Created'; }).length;
    var polDue = list.filter(function (b) { return b.pol !== 'Verified'; }).length;
    var entitle = list.reduce(function (a, b) { return a + b.monthly; }, 0);
    document.getElementById('govBenKpis').innerHTML =
        '<div class="card rounded-3xl p-5"><p class="text-slate-500 font-bold">Enrolled (sample)</p><h3 class="text-4xl font-black">' + list.length + '</h3></div>' +
        '<div class="card rounded-3xl p-5"><p class="text-slate-500 font-bold">Auto-Created Wallets</p><h3 class="text-4xl font-black text-purple-600">' + auto + '</h3><p class="text-xs text-slate-500">unbanked onboarded</p></div>' +
        '<div class="card rounded-3xl p-5"><p class="text-slate-500 font-bold">Proof-of-Life Due</p><h3 class="text-4xl font-black text-amber-600">' + polDue + '</h3></div>' +
        '<div class="card rounded-3xl p-5"><p class="text-slate-500 font-bold">Monthly Entitlement</p><h3 class="text-3xl font-black text-green-600">$' + entitle.toLocaleString() + '</h3></div>';
    lkListRender['govben'] = renderGovBeneficiaries;
    document.getElementById('govBenTable').innerHTML = buildCountryAccordionRows('govben', list, function (b) { return b.country; }, function (b) {
        var wb = { 'Linked': 'bg-green-50 text-green-700', 'Auto-Created': 'bg-purple-50 text-purple-700', 'Pending': 'bg-amber-50 text-amber-700' }[b.wallet] || 'bg-slate-50';
        var pb = { 'Verified': 'bg-green-50 text-green-700', 'Due': 'bg-amber-50 text-amber-700', 'Overdue': 'bg-rose-50 text-rose-700' }[b.pol] || 'bg-slate-50';
        var polAct = b.pol !== 'Verified' ? '<button onclick="govVerifyPol(\'' + b.id + '\')" class="rounded-lg bg-green-100 text-green-700 px-2 py-1 text-xs font-black">Verify</button>' : '';
        return '<tr class="border-t align-top"><td class="py-2 font-black">' + b.name + '</td><td class="text-slate-500">' + b.natId + '</td><td>' + b.program + '</td><td class="font-black">$' + b.monthly.toLocaleString() + '</td><td><span class="rounded-full px-2.5 py-0.5 text-xs font-black ' + wb + '">' + b.wallet + '</span></td><td>' + b.kyc + '</td><td><span class="rounded-full px-2.5 py-0.5 text-xs font-black ' + pb + '">' + b.pol + '</span> ' + polAct + '</td><td><div class="flex gap-1">' + (polAct ? '' : '') + '<button onclick="govOpenBeneficiary(\'' + b.id + '\')" class="rounded-lg bg-sky-100 text-sky-700 px-2 py-1 text-xs font-black">Edit</button><button onclick="govDeleteBeneficiary(\'' + b.id + '\')" class="rounded-lg bg-rose-100 text-rose-700 px-2 py-1 text-xs font-black">Remove</button></div></td></tr>';
    }, 8);
}

function renderGovDisbursements() {
    if (!document.getElementById('govQueueTable')) return;
    var ctx = govAgencyContext; var progList = govScopePrograms();
    var psel = document.getElementById('govBatchProgram');
    if (psel) psel.innerHTML = progList.map(function (p) { return '<option>' + p.name + '</option>'; }).join('');
    var pv = document.getElementById('govBatchPreview');
    if (pv) {
        var p = progList.find(function (x) { return x.name === (psel || {}).value; }) || progList[0];
        if (p) {
            var count = Math.round(govMonthlyPayouts(p));
            pv.innerHTML = '<div class="flex justify-between"><span class="text-slate-500 font-bold">Beneficiaries</span><span class="font-black">' + count.toLocaleString() + '</span></div><div class="flex justify-between"><span class="text-slate-500 font-bold">Net to wallets</span><span class="font-black text-green-600">$' + (count * p.avg).toLocaleString() + '</span></div><div class="flex justify-between"><span class="text-slate-500 font-bold">' + (ctx ? 'Service fee' : 'LinkUp fee (agency)') + '</span><span class="font-black text-purple-600">$' + Math.round(count * govFeePer(p.avg)).toLocaleString() + '</span></div>';
        }
    }
    document.getElementById('govQueueTable').innerHTML = govScopeBatches().map(function (b) {
        var act = b.status === 'Draft' ? '<button onclick="govApproveBatch(\'' + b.id + '\')" class="rounded-lg bg-slate-950 text-white px-3 py-1 text-xs font-black">Approve</button>' : b.status === 'Approved' ? (ctx ? '<span class="rounded-full bg-amber-50 text-amber-700 px-2.5 py-1 text-xs font-black">Awaiting LinkUp release</span>' : '<button onclick="govDisburseBatch(\'' + b.id + '\')" class="rounded-lg bg-red-600 text-white px-3 py-1 text-xs font-black">Disburse</button>') : '<span class="text-slate-400 text-xs font-bold">paid ' + b.date + '</span>';
        return '<tr class="border-t"><td class="py-2 font-black">' + b.id + '</td><td>' + b.program + '</td><td>' + b.count.toLocaleString() + '</td><td class="font-black text-green-600">$' + b.net.toLocaleString() + '</td><td class="text-purple-600 font-bold">$' + b.fee.toLocaleString() + '</td><td><span class="rounded-full px-2.5 py-0.5 text-xs font-black ' + (b.status === 'Disbursed' ? 'bg-green-50 text-green-700' : b.status === 'Approved' ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-600') + '">' + b.status + '</span></td><td>' + act + '</td></tr>';
    }).join('') || '<tr><td colspan="7" class="py-6 text-center text-slate-400 font-bold">No batches yet.</td></tr>';
}

function renderGovSettings() {
    if (!document.getElementById('govFeeFlat')) return;
    var ctx = govAgencyContext;
    var grid = document.getElementById('govFeeFlat').closest('.grid');
    if (grid) grid.style.display = ctx ? 'none' : '';
    var bd0 = document.getElementById('govRevenueBreakdown');
    if (ctx) { if (bd0) bd0.innerHTML = '<div class="rounded-2xl bg-slate-50 p-6 text-center"><h3 class="text-xl font-black mb-1">Fees are managed by LinkUp</h3><p class="text-slate-500">Your agency agreement sets the per-disbursement service fee. Beneficiaries always receive 100% of their entitlement. Contact your LinkUp account manager to review terms.</p></div>'; return; }
    govHydrateFees();
    var bd = document.getElementById('govRevenueBreakdown');
    if (bd) {
        var list = govPrograms; var disb = govDisbursementFeesMonthly(list); var saas = govSaasMonthly(list); var yld = govFloatYieldMonthly(); var tot = disb + saas + yld;
        bd.innerHTML = '<h3 class="text-xl font-black mb-4">Monthly Revenue Breakdown (all programs)</h3><div class="grid grid-cols-1 md:grid-cols-4 gap-4"><div class="rounded-2xl bg-purple-50 p-4"><p class="text-purple-700 font-bold text-sm">Disbursement Fees</p><h4 class="text-2xl font-black">$' + Math.round(disb).toLocaleString() + '</h4></div><div class="rounded-2xl bg-sky-50 p-4"><p class="text-sky-700 font-bold text-sm">SaaS Licenses</p><h4 class="text-2xl font-black">$' + Math.round(saas).toLocaleString() + '</h4></div><div class="rounded-2xl bg-green-50 p-4"><p class="text-green-700 font-bold text-sm">Float Yield (LinkUp)</p><h4 class="text-2xl font-black">$' + Math.round(yld).toLocaleString() + '</h4></div><div class="rounded-2xl bg-slate-900 text-white p-4"><p class="font-bold text-sm">Total / month</p><h4 class="text-2xl font-black">$' + Math.round(tot).toLocaleString() + '</h4></div></div><p class="text-xs text-slate-500 mt-3">Annualized: <b>$' + Math.round(tot * 12).toLocaleString() + '</b>. These figures feed the Fee Revenue Dashboard, executive totals and Business Units.</p>';
    }
}

function renderGovAll() {
    try { renderGovDashboard(); } catch (e) { }
    try { renderGovBeneficiaries(); } catch (e) { }
    try { renderGovDisbursements(); } catch (e) { }
    try { renderGovSettings(); } catch (e) { }
}
