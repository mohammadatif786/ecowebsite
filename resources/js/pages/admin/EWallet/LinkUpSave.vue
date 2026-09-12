<script setup lang="ts">
import { Landmark, Percent, PiggyBank, SlidersHorizontal, Target, TrendingUp, UserPlus, Users, X } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n || 0);
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n || 0));

const COUNTRIES = ['Bahamas', 'Jamaica', 'Trinidad & Tobago', 'Barbados', 'Guyana', 'Dominican Republic', 'United States', 'Canada', 'Brazil', 'Colombia'];
const DAY_MS = 86400000;

// All client-side/local state — LinkUp Save has no real backend tables yet
// (no Saver/SavingsTier/SavingsGoal models), so nothing here persists.
const savingsConfig = reactive({
    wholesale: 5.0,
    minBalance: 1000,
    topUpMin: 100,
    lockDays: 90,
    earlyPenalty: 1.5,
    allowEarly: true,
    tiers: [
        { name: 'Standard', min: 1000, apy: 3.0, lock: 30 },
        { name: 'Premium', min: 5000, apy: 4.0, lock: 90 },
        { name: 'Elite', min: 25000, apy: 4.5, lock: 180 },
    ],
});

const now = Date.now();
const savers = ref([
    { id: 1, name: 'Island Vibes Events', type: 'Event Organizer', country: 'Bahamas', balance: 18400, interestMTD: 0, interestYTD: 612, status: 'Active', lockUntil: now + 62 * DAY_MS, bankName: 'Scotiabank Bahamas', bankLast4: '4821', autoSavePct: 0, opened: true },
    { id: 2, name: 'Bahama Grill', type: 'Eats Restaurant', country: 'Bahamas', balance: 7200, interestMTD: 0, interestYTD: 188, status: 'Active', lockUntil: now - 5 * DAY_MS, bankName: 'Scotiabank Bahamas', bankLast4: '1190', autoSavePct: 5, opened: true },
    { id: 3, name: 'Trini Flavors', type: 'Eats Restaurant', country: 'Trinidad & Tobago', balance: 3100, interestMTD: 0, interestYTD: 74, status: 'Active', lockUntil: now + 18 * DAY_MS, bankName: 'Scotiabank T&T', bankLast4: '7733', autoSavePct: 0, opened: true },
    { id: 4, name: 'Kemar (Seller)', type: 'Marketplace Seller', country: 'Jamaica', balance: 5600, interestMTD: 0, interestYTD: 140, status: 'Active', lockUntil: now - 30 * DAY_MS, bankName: 'Scotiabank Jamaica', bankLast4: '2055', autoSavePct: 10, opened: true },
    { id: 5, name: 'DJ Sasha (Live)', type: 'Live Creator', country: 'Barbados', balance: 1200, interestMTD: 0, interestYTD: 0, status: 'Pending KYC', lockUntil: now + 80 * DAY_MS, bankName: '', bankLast4: '', autoSavePct: 0, opened: true },
    { id: 6, name: 'Carnival Link Caribbean', type: 'Event Organizer', country: 'Trinidad & Tobago', balance: 31250, interestMTD: 0, interestYTD: 980, status: 'Active', lockUntil: now + 150 * DAY_MS, bankName: 'Scotiabank T&T', bankLast4: '9902', autoSavePct: 0, opened: true },
]);

const savingsWithdrawals = ref<any[]>([]);
const savingsTxns = ref<any[]>(
    savers.value.map((s) => ({
        id: 'TX-' + s.id + '-OPEN',
        saverId: s.id,
        ts: now - 150 * DAY_MS,
        date: new Date(now - 150 * DAY_MS).toLocaleString(),
        type: 'Deposit',
        amount: s.balance,
        source: 'LinkUp Wallet',
        note: 'Opening deposit',
        balanceAfter: s.balance,
    })),
);
const savingsGoals = ref([
    { id: 1, saverId: 1, name: 'New venue deposit', target: 25000, saved: 18400 },
    { id: 2, saverId: 4, name: 'Inventory restock', target: 8000, saved: 5600 },
]);
const savWalletBalance = ref(12480); // demo: user's LinkUp Wallet available balance

const savLog = (saverId: number, type: string, amount: number, opts: { source?: string; note?: string } = {}) => {
    const s = savers.value.find((x) => x.id === saverId);
    savingsTxns.value.unshift({
        id: 'TX-' + Date.now().toString(36).toUpperCase().slice(-6) + Math.floor(Math.random() * 90 + 10),
        saverId,
        ts: Date.now(),
        date: new Date().toLocaleString(),
        type,
        amount,
        source: opts.source || '',
        note: opts.note || '',
        balanceAfter: s ? s.balance : 0,
    });
};

const savingsTierFor = (bal: number) => {
    if (bal < savingsConfig.minBalance) return { name: 'Below Min', apy: 0, lock: savingsConfig.lockDays };
    let t = { name: 'Standard', apy: 0, lock: savingsConfig.lockDays };
    [...savingsConfig.tiers].sort((a, b) => a.min - b.min).forEach((x) => {
        if (bal >= x.min) t = { name: x.name, apy: x.apy, lock: x.lock || savingsConfig.lockDays };
    });
    return t;
};
const savingsMoInterest = (s: any) => (s.status === 'Active' ? (s.balance * savingsTierFor(s.balance).apy) / 1200 : 0);
const savingsMoSpread = (s: any) => (s.status === 'Active' ? (s.balance * Math.max(0, savingsConfig.wholesale - savingsTierFor(s.balance).apy)) / 1200 : 0);
const savLockDaysLeft = (s: any) => (s.lockUntil ? Math.max(0, Math.ceil((s.lockUntil - Date.now()) / DAY_MS)) : 0);
const savIsLocked = (s: any) => savLockDaysLeft(s) > 0;
const savEstPayout = (s: any) => Math.round(s.balance * 0.5); // demo: est monthly earnings

// ---- KPIs & reconciliation ----
const totalDeposit = computed(() => savers.value.reduce((a, s) => a + s.balance, 0));
const activeSaverCount = computed(() => savers.value.filter((s) => s.status === 'Active').length);
const moInterestTotal = computed(() => savers.value.reduce((a, s) => a + savingsMoInterest(s), 0));
const moSpreadTotal = computed(() => savers.value.reduce((a, s) => a + savingsMoSpread(s), 0));
const avgApy = computed(() => {
    const total = totalDeposit.value;
    return total > 0 ? savers.value.reduce((a, s) => a + savingsTierFor(s.balance).apy * s.balance, 0) / total : 0;
});
const grossBankInterest = computed(() => (totalDeposit.value * savingsConfig.wholesale) / 1200);

const postSavingsInterest = () => {
    const active = savers.value.filter((s) => s.status === 'Active');
    if (!active.length) {
        alert('No active savers to post interest for.');
        return;
    }
    let total = 0;
    active.forEach((s) => {
        const interest = savingsMoInterest(s);
        if (interest <= 0) return;
        s.balance += interest;
        s.interestMTD = interest;
        s.interestYTD = (s.interestYTD || 0) + interest;
        total += interest;
        savLog(s.id, 'Interest', interest, { source: 'Monthly posting', note: savingsTierFor(s.balance).apy.toFixed(2) + '% APY' });
    });
    alert(`✅ Posted ${fmt(total)} in interest across ${active.length} saver(s).`);
};

// ---- Tiers config ----
const addSavingsTier = () => savingsConfig.tiers.push({ name: 'New Tier', min: 50000, apy: 4.0, lock: 180 });
const removeSavingsTier = (i: number) => savingsConfig.tiers.splice(i, 1);

// ---- Savers ledger (search + CRUD) ----
const savSearch = ref('');
const filteredSavers = computed(() => {
    const q = savSearch.value.toLowerCase();
    return savers.value.filter((s) => !q || [s.name, s.type, s.country, s.status].join(' ').toLowerCase().includes(q));
});

const saverModalOpen = ref(false);
const editingSaverId = ref<number | null>(null);
const saverForm = reactive({ name: '', type: 'Event Organizer', country: '', balance: 0, status: 'Active', bankName: '', bankLast4: '', autoSavePct: 0 });

const openSaverModal = (id: number | null = null) => {
    editingSaverId.value = id;
    const s = id !== null ? savers.value.find((x) => x.id === id) : null;
    Object.assign(saverForm, {
        name: s?.name || '',
        type: s?.type || 'Event Organizer',
        country: s?.country || '',
        balance: s?.balance ?? 0,
        status: s?.status || 'Active',
        bankName: s?.bankName || '',
        bankLast4: s?.bankLast4 || '',
        autoSavePct: s?.autoSavePct || 0,
    });
    saverModalOpen.value = true;
};
const closeSaverModal = () => (saverModalOpen.value = false);
const saveSaverModal = () => {
    const data = { ...saverForm, name: saverForm.name.trim() || 'New Saver', country: saverForm.country.trim() || 'Bahamas' };
    if (editingSaverId.value !== null) {
        const i = savers.value.findIndex((x) => x.id === editingSaverId.value);
        if (i !== -1) savers.value[i] = { ...savers.value[i], ...data };
    } else {
        const newId = (savers.value.reduce((m, x) => Math.max(m, x.id), 0) || 0) + 1;
        const tier = savingsTierFor(data.balance);
        savers.value.push({ id: newId, interestMTD: 0, interestYTD: 0, opened: data.balance >= savingsConfig.minBalance, lockUntil: Date.now() + tier.lock * DAY_MS, ...data });
        savLog(newId, 'Deposit', data.balance, { source: 'LinkUp Wallet', note: 'Opening deposit' });
    }
    closeSaverModal();
};
const deleteSaver = (id: number) => {
    const s = savers.value.find((x) => x.id === id);
    if (!s) return;
    if (confirm(`Remove ${s.name} from LinkUp Save?`)) savers.value = savers.value.filter((x) => x.id !== id);
};

// ---- Deposit modal ----
const depositModalOpen = ref(false);
const savDepId = ref<number | null>(null);
const savDepSource = ref('Wallet');
const savDepAmount = ref<number | null>(null);
const depositSaver = computed(() => savers.value.find((x) => x.id === savDepId.value) || null);
const depositTiers = computed(() => [...savingsConfig.tiers].sort((a, b) => a.min - b.min));
const depositProjectedBalance = computed(() => (depositSaver.value?.balance || 0) + (savDepAmount.value || 0));
const depositProjectedTier = computed(() => savingsTierFor(depositProjectedBalance.value));

const openSavingsDeposit = (id: number) => {
    savDepId.value = id;
    savDepAmount.value = null;
    savDepSource.value = 'Wallet';
    depositModalOpen.value = true;
};
const closeSavingsDeposit = () => (depositModalOpen.value = false);
const pickDepositTier = (min: number) => {
    const s = depositSaver.value;
    if (!s) return;
    const needed = Math.max(0, min - s.balance);
    savDepAmount.value = needed > 0 ? needed : Math.max(min - s.balance, 100);
};
const applySavingsDeposit = () => {
    const s = depositSaver.value;
    if (!s) return;
    const amt = savDepAmount.value || 0;
    if (amt <= 0) {
        alert('Enter an amount.');
        return;
    }
    const opened = s.opened || s.balance >= savingsConfig.minBalance;
    if (!opened) {
        if (s.balance + amt < savingsConfig.minBalance) {
            alert(`⚠️ Opening deposit must meet the ${fmt(savingsConfig.minBalance)} minimum.\nBalance after this deposit would be ${fmt(s.balance + amt)}.`);
            return;
        }
    } else if (amt < savingsConfig.topUpMin) {
        alert(`The minimum top-up is ${fmt(savingsConfig.topUpMin)}.`);
        return;
    }
    if (savDepSource.value === 'Wallet' && amt > savWalletBalance.value) {
        alert(`🚫 Your LinkUp Wallet has insufficient funds.\n\nAvailable: ${fmt(savWalletBalance.value)}\nRequested: ${fmt(amt)}`);
        return;
    }
    if (savDepSource.value === 'Wallet') savWalletBalance.value -= amt;
    s.balance += amt;
    if (s.balance >= savingsConfig.minBalance) s.opened = true;
    const tier = savingsTierFor(s.balance);
    s.lockUntil = Date.now() + tier.lock * DAY_MS;
    savLog(s.id, 'Deposit', amt, { source: savDepSource.value, note: `${tier.name} tier • locks ${tier.lock}d` });
    closeSavingsDeposit();
    alert(`✅ Deposited ${fmt(amt)} via ${savDepSource.value}.\nNew balance: ${fmt(s.balance)}\nLocked for ${tier.lock} days.`);
};

// ---- Withdraw modal ----
const withdrawModalOpen = ref(false);
const savWdId = ref<number | null>(null);
const savWdAmount = ref<number | null>(null);
const withdrawSaver = computed(() => savers.value.find((x) => x.id === savWdId.value) || null);
const withdrawPenalty = computed(() => {
    const s = withdrawSaver.value;
    const amt = savWdAmount.value || 0;
    return s && savIsLocked(s) ? amt * (savingsConfig.earlyPenalty / 100) : 0;
});
const withdrawNet = computed(() => (savWdAmount.value || 0) - withdrawPenalty.value);

const openSavingsWithdraw = (id: number) => {
    savWdId.value = id;
    savWdAmount.value = null;
    withdrawModalOpen.value = true;
};
const closeSavingsWithdraw = () => (withdrawModalOpen.value = false);
const applySavingsWithdraw = () => {
    const s = withdrawSaver.value;
    if (!s) return;
    const amt = savWdAmount.value || 0;
    if (amt <= 0) {
        alert('Enter an amount.');
        return;
    }
    if (amt > s.balance) {
        alert('Amount exceeds balance.');
        return;
    }
    if (savIsLocked(s) && !savingsConfig.allowEarly) {
        alert(`This balance is locked until ${new Date(s.lockUntil).toLocaleDateString()}.`);
        return;
    }
    if (!s.bankName) {
        alert('No linked Scotiabank account. Add one via Edit Saver first.');
        return;
    }
    const pen = withdrawPenalty.value;
    const net = amt - pen;
    if (savIsLocked(s) && !confirm(`Early withdrawal — a ${savingsConfig.earlyPenalty}% penalty (${fmt(pen)}) applies. Net to bank: ${fmt(net)}. Continue?`)) return;
    s.balance -= amt;
    const ref = 'WD-' + Date.now().toString(36).toUpperCase().slice(-6);
    savingsWithdrawals.value.unshift({ ref, saverId: s.id, saver: s.name, amount: amt, penalty: pen, net, dest: `${s.bankName} ••${s.bankLast4}`, requested: new Date().toLocaleDateString(), status: 'Pending' });
    savLog(s.id, 'Withdrawal', -amt, { source: 'To ' + s.bankName, note: ref + (pen > 0 ? ` • early-withdrawal penalty ${fmt(pen)}` : '') + ` • net ${fmt(net)}` });
    if (pen > 0) savLog(s.id, 'Penalty', -pen, { note: `Early withdrawal penalty (${savingsConfig.earlyPenalty}%)` });
    closeSavingsWithdraw();
    alert(`✅ Withdrawal request ${ref} created.\n${fmt(net)} will be sent to ${s.bankName} ••${s.bankLast4} (penalty ${fmt(pen)}).`);
};
const savWdSetStatus = (i: number, status: string) => {
    const w = savingsWithdrawals.value[i];
    if (!w) return;
    if (status === 'Rejected') {
        const s = savers.value.find((x) => x.id === w.saverId);
        if (s) {
            s.balance += w.amount;
            savLog(s.id, 'Refund', w.amount, { source: 'Withdrawal rejected', note: w.ref });
        }
    }
    w.status = status;
};
const pendingWithdrawals = computed(() => savingsWithdrawals.value.filter((w) => w.status === 'Pending').length);
const paidWithdrawals = computed(() => savingsWithdrawals.value.filter((w) => w.status === 'Paid').length);
const withdrawalStatusBadge = (status: string) => {
    if (status === 'Paid') return 'bg-green-50 text-green-700';
    if (status === 'Approved') return 'bg-sky-50 text-sky-700';
    if (status === 'Rejected') return 'bg-rose-50 text-rose-700';
    return 'bg-amber-50 text-amber-700';
};

// ---- Auto-Save ----
const autoSaveSavers = computed(() => savers.value.filter((s) => (s.autoSavePct || 0) > 0));
const runAutoSaveSweep = () => {
    const on = savers.value.filter((s) => (s.autoSavePct || 0) > 0 && s.status === 'Active');
    if (!on.length) {
        alert('No active savers have Auto-Save enabled.');
        return;
    }
    let total = 0;
    on.forEach((s) => {
        const amt = Math.round((savEstPayout(s) * s.autoSavePct) / 100);
        if (amt <= 0) return;
        s.balance += amt;
        total += amt;
        if (s.balance >= savingsConfig.minBalance) s.opened = true;
        const tier = savingsTierFor(s.balance);
        s.lockUntil = Date.now() + tier.lock * DAY_MS;
        savLog(s.id, 'Auto-Save', amt, { source: `${s.autoSavePct}% of payout`, note: 'Automatic sweep' });
    });
    alert(`✅ Auto-Save sweep complete.\n${on.length} saver(s) swept ${fmt(total)} into savings.`);
};

// ---- Goals ----
const goalModalOpen = ref(false);
const goalForm = reactive({ saverId: null as number | null, name: '', target: 0, saved: 0 });
const openGoalModal = () => {
    Object.assign(goalForm, { saverId: savers.value[0]?.id ?? null, name: '', target: 0, saved: 0 });
    goalModalOpen.value = true;
};
const closeGoalModal = () => (goalModalOpen.value = false);
const saveGoal = () => {
    if (!goalForm.name.trim() || goalForm.target <= 0) {
        alert('Enter a goal name and target.');
        return;
    }
    const newId = (savingsGoals.value.reduce((m, g) => Math.max(m, g.id), 0) || 0) + 1;
    savingsGoals.value.push({ id: newId, saverId: goalForm.saverId, name: goalForm.name.trim(), target: goalForm.target, saved: goalForm.saved || 0 });
    closeGoalModal();
};
const contributeGoal = (id: number) => {
    const g = savingsGoals.value.find((x) => x.id === id);
    if (!g) return;
    const amt = parseFloat(prompt(`Contribute amount to "${g.name}":`, '100') || '0') || 0;
    if (amt > 0) g.saved += amt;
};
const deleteGoal = (id: number) => {
    if (confirm('Delete this goal?')) savingsGoals.value = savingsGoals.value.filter((g) => g.id !== id);
};
const goalProgress = (g: any) => Math.min(100, Math.round((g.saved / g.target) * 100));
const goalSaverName = (g: any) => savers.value.find((x) => x.id === g.saverId)?.name || '—';

// ---- How It Works ----
const howModalOpen = ref(false);
const sortedTiers = computed(() => [...savingsConfig.tiers].sort((a, b) => a.min - b.min));

// ---- Statements & Tax ----
const savStmtPick = ref<number | null>(null);
const savingsStatement = computed(() => {
    const s = savers.value.find((x) => x.id === savStmtPick.value) || savers.value[0];
    if (!s) return 'No savers yet.';
    const t = savingsTierFor(s.balance);
    const moInt = savingsMoInterest(s);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const nowMonth = new Date().getMonth();
    let lines = `LINKUP SAVE — INTEREST STATEMENT\n${'='.repeat(40)}\nSaver: ${s.name}  (${s.type})\nCountry: ${s.country}\nLinked: ${s.bankName ? s.bankName + ' ••' + s.bankLast4 : '—'}\nTier: ${t.name} @ ${t.apy.toFixed(2)}% APY\nBalance: ${fmt(s.balance)}\n\nMonth      Interest\n${'-'.repeat(40)}\n`;
    let ytd = 0;
    for (let i = 0; i <= nowMonth; i++) {
        const v = moInt * (0.85 + (i % 3) * 0.08);
        ytd += v;
        lines += (months[i] + '        ').slice(0, 10) + fmt(v) + '\n';
    }
    lines += `${'-'.repeat(40)}\nInterest YTD (taxable): ${fmt(s.interestYTD || ytd)}\n\nReportable to tax authority as interest income.\nPrepared by LinkUp Save • Powered by Scotiabank.`;
    return lines;
});
const exportSavingsStatement = () => {
    const s = savers.value.find((x) => x.id === savStmtPick.value) || savers.value[0];
    const blob = new Blob([savingsStatement.value], { type: 'text/plain' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `LinkUpSave-${(s?.name || 'saver').replace(/[^a-z0-9]+/gi, '-')}-statement.txt`;
    a.click();
};
const sendSavingsToTax = () => alert('✅ Statement sent to the Tax module (simulated — no Tax module integration wired up yet).');

// ---- In-app savings card preview ----
const savCardPick = ref<number | null>(null);
const cardSaver = computed(() => savers.value.find((x) => x.id === savCardPick.value) || savers.value[0] || null);
const cardTier = computed(() => (cardSaver.value ? savingsTierFor(cardSaver.value.balance) : { name: '', apy: 0 }));

// ---- Transaction history modal ----
const historyModalOpen = ref(false);
const savHistId = ref<number | null>(null);
const historySaver = computed(() => savers.value.find((x) => x.id === savHistId.value) || null);
const historyTxns = computed(() => savingsTxns.value.filter((t) => t.saverId === savHistId.value));
const historyKpis = computed(() => {
    const txns = historyTxns.value;
    return {
        deposited: txns.filter((t) => ['Deposit', 'Auto-Save'].includes(t.type)).reduce((a, t) => a + t.amount, 0),
        withdrawn: txns.filter((t) => t.type === 'Withdrawal').reduce((a, t) => a + Math.abs(t.amount), 0),
        interest: txns.filter((t) => t.type === 'Interest').reduce((a, t) => a + t.amount, 0),
    };
});
const openSavingsHistory = (id: number) => {
    savHistId.value = id;
    historyModalOpen.value = true;
};
const closeSavingsHistory = () => (historyModalOpen.value = false);
const txnColor = (t: string) => ({ Deposit: 'text-emerald-600', Interest: 'text-emerald-600', 'Auto-Save': 'text-emerald-600', Refund: 'text-emerald-600', Withdrawal: 'text-rose-600', Penalty: 'text-rose-600' }[t] || 'text-slate-600');
const txnBadge = (t: string) => ({ Deposit: 'bg-emerald-50 text-emerald-700', Interest: 'bg-sky-50 text-sky-700', 'Auto-Save': 'bg-teal-50 text-teal-700', Refund: 'bg-amber-50 text-amber-700', Withdrawal: 'bg-rose-50 text-rose-700', Penalty: 'bg-rose-50 text-rose-700' }[t] || 'bg-slate-100 text-slate-600');
const exportSavingsHistory = () => {
    const s = historySaver.value;
    if (!s) return;
    const csv = 'Date,Type,Source,Note,Amount,BalanceAfter\n' + historyTxns.value.map((t) => [t.date, t.type, (t.source || '').replace(/,/g, ';'), (t.note || '').replace(/,/g, ';'), t.amount, t.balanceAfter].join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `LinkUpSave-${(s.name || 'saver').replace(/[^a-z0-9]+/gi, '-')}-transactions.csv`;
    a.click();
};

const statusBadge = (status: string) => {
    if (status === 'Active') return 'bg-green-50 text-green-700';
    if (status === 'Pending KYC') return 'bg-amber-50 text-amber-700';
    return 'bg-slate-100 text-slate-600';
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black text-emerald-600">LinkUp Save</h3>
                <p class="text-slate-500">
                    High-yield savings for organizers, merchants, sellers &amp; creators — powered by Scotiabank. Earners grow idle wallet
                    balances; LinkUp earns the spread.
                </p>
            </div>
            <div class="flex gap-2">
                <button @click="postSavingsInterest" class="flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 font-black text-white">
                    <Percent class="h-4 w-4" /> Post Monthly Interest
                </button>
                <button @click="openSaverModal()" class="flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 font-black text-white">
                    <UserPlus class="h-4 w-4" /> Add Saver
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-100 text-emerald-600"><Landmark /></div>
                <div><h3 class="text-2xl font-black">{{ fmt(totalDeposit) }}</h3><p class="text-xs text-slate-500">Total on Deposit</p></div>
            </div>
            <div class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-100 text-sky-600"><Users /></div>
                <div><h3 class="text-2xl font-black">{{ num(activeSaverCount) }}</h3><p class="text-xs text-slate-500">Active Savers</p></div>
            </div>
            <div class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-100 text-amber-600"><PiggyBank /></div>
                <div><h3 class="text-2xl font-black">{{ fmt(moInterestTotal) }}</h3><p class="text-xs text-slate-500">User Interest / mo</p></div>
            </div>
            <div class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-purple-100 text-purple-600"><TrendingUp /></div>
                <div><h3 class="text-2xl font-black">{{ fmt(moSpreadTotal) }}</h3><p class="text-xs text-slate-500">LinkUp Spread / mo</p></div>
            </div>
            <div class="card flex items-center gap-4 rounded-3xl p-5">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-rose-100 text-rose-600"><SlidersHorizontal /></div>
                <div><h3 class="text-2xl font-black">{{ avgApy.toFixed(2) }}%</h3><p class="text-xs text-slate-500">Avg User APY</p></div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="card rounded-3xl p-6 xl:col-span-2">
                <h4 class="mb-1 text-xl font-black">Yield Tiers</h4>
                <p class="mb-4 text-sm text-slate-500">Set the user APY per tier. LinkUp keeps the spread between the Scotiabank wholesale rate and the user APY.</p>
                <div class="mb-4 grid grid-cols-2 gap-3 rounded-2xl bg-slate-50 p-4 md:grid-cols-5">
                    <div>
                        <label class="text-xs font-black text-slate-600">Wholesale Rate (%)</label>
                        <input v-model.number="savingsConfig.wholesale" type="number" step="0.05" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-lg font-black" />
                    </div>
                    <div>
                        <label class="text-xs font-black text-slate-600">Opening Min ($)</label>
                        <input v-model.number="savingsConfig.minBalance" type="number" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-lg font-black" />
                    </div>
                    <div>
                        <label class="text-xs font-black text-slate-600">Top-up Min ($)</label>
                        <input v-model.number="savingsConfig.topUpMin" type="number" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-lg font-black" />
                    </div>
                    <div>
                        <label class="text-xs font-black text-slate-600">Early Withdrawal Penalty (%)</label>
                        <input v-model.number="savingsConfig.earlyPenalty" type="number" step="0.1" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-lg font-black" />
                    </div>
                    <div>
                        <label class="text-xs font-black text-slate-600">Early Withdrawal</label>
                        <select
                            :value="savingsConfig.allowEarly ? 'yes' : 'no'"
                            @change="savingsConfig.allowEarly = ($event.target as HTMLSelectElement).value === 'yes'"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 font-bold"
                        >
                            <option value="yes">Allowed (with penalty)</option>
                            <option value="no">Locked (not allowed)</option>
                        </select>
                    </div>
                </div>
                <div class="scrollbar overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-xs uppercase text-slate-500">
                            <tr><th class="py-2">Tier</th><th>Min Balance</th><th>User APY (%)</th><th>Lock (days)</th><th>LinkUp Spread</th><th></th></tr>
                        </thead>
                        <tbody>
                            <tr v-for="(t, i) in savingsConfig.tiers" :key="i" class="border-t">
                                <td class="py-2"><input v-model="t.name" class="w-28 rounded-xl border border-slate-200 px-2 py-1 font-bold" /></td>
                                <td><input v-model.number="t.min" type="number" class="w-28 rounded-xl border border-slate-200 px-2 py-1" /></td>
                                <td><input v-model.number="t.apy" type="number" step="0.05" class="w-24 rounded-xl border border-slate-200 px-2 py-1 font-black text-emerald-600" /></td>
                                <td><input v-model.number="t.lock" type="number" class="w-20 rounded-xl border border-slate-200 px-2 py-1" /></td>
                                <td class="font-bold text-purple-600">{{ Math.max(0, savingsConfig.wholesale - t.apy).toFixed(2) }}%</td>
                                <td><button @click="removeSavingsTier(i)" class="text-slate-400 hover:text-rose-600">✕</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button @click="addSavingsTier" class="mt-3 flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black">+ Add Tier</button>
            </div>
            <div class="card rounded-3xl p-6">
                <h4 class="mb-1 text-xl font-black">Scotiabank Reconciliation</h4>
                <p class="mb-4 text-sm text-slate-500">Monthly settlement view.</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="font-bold text-slate-500">Total on deposit (Scotiabank)</span><b>{{ fmt(totalDeposit) }}</b></div>
                    <div class="flex justify-between"><span class="font-bold text-slate-500">Wholesale rate</span><b>{{ savingsConfig.wholesale.toFixed(2) }}%</b></div>
                    <div class="flex justify-between"><span class="font-bold text-slate-500">Gross interest from bank / mo</span><b>{{ fmt(grossBankInterest) }}</b></div>
                    <div class="flex justify-between"><span class="font-bold text-slate-500">Interest paid to users / mo</span><b>{{ fmt(moInterestTotal) }}</b></div>
                    <div class="flex justify-between"><span class="font-bold text-slate-500">LinkUp spread / mo</span><b>{{ fmt(moSpreadTotal) }}</b></div>
                    <div class="flex justify-between">
                        <span class="font-bold text-slate-500">Settlement status</span>
                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-black text-green-700">Reconciled</span>
                    </div>
                </div>
                <div class="mt-4 rounded-2xl bg-emerald-50 p-4">
                    <p class="text-sm font-bold text-emerald-700">Net to LinkUp / mo</p>
                    <h3 class="text-3xl font-black text-emerald-700">{{ fmt(moSpreadTotal) }}</h3>
                </div>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <div class="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div><h4 class="text-xl font-black">Savings Ledger</h4><p class="text-sm text-slate-500">Every saver, balance, tier APY, and interest earned.</p></div>
                <input v-model="savSearch" class="w-full rounded-2xl border border-slate-200 px-4 py-2 xl:w-80" placeholder="Search saver, type, country..." />
            </div>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr><th class="py-2">Saver</th><th>Type</th><th>Balance</th><th>Tier</th><th>APY</th><th>Lock</th><th>Linked Bank</th><th>Interest YTD</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredSavers.length"><td colspan="10" class="py-6 text-center text-slate-400 font-bold">No savers yet.</td></tr>
                        <tr v-for="s in filteredSavers" :key="s.id" class="border-t hover:bg-slate-50">
                            <td class="py-3 font-black">{{ s.name }}<div class="text-xs font-normal text-slate-400">{{ s.country }}</div></td>
                            <td>{{ s.type }}</td>
                            <td class="font-black">{{ fmt(s.balance) }}</td>
                            <td>{{ savingsTierFor(s.balance).name }}</td>
                            <td class="font-bold text-emerald-600">{{ savingsTierFor(s.balance).apy.toFixed(2) }}%</td>
                            <td>
                                <span v-if="savLockDaysLeft(s) > 0" class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-black text-amber-700">🔒 {{ savLockDaysLeft(s) }}d</span>
                                <span v-else class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-black text-green-700">Matured</span>
                            </td>
                            <td class="text-xs">
                                <span v-if="s.bankName">{{ s.bankName }} ••{{ s.bankLast4 }}</span>
                                <span v-else class="text-rose-500">Not linked</span>
                            </td>
                            <td>{{ fmt(s.interestYTD || 0) }}</td>
                            <td><span :class="statusBadge(s.status)" class="rounded-full px-3 py-1 text-xs font-black">{{ s.status }}</span></td>
                            <td>
                                <div class="flex gap-1">
                                    <button title="Transaction History" @click="openSavingsHistory(s.id)" class="rounded-xl bg-sky-50 px-2.5 py-2 text-sky-700">🕘</button>
                                    <button title="Deposit" @click="openSavingsDeposit(s.id)" class="rounded-xl bg-emerald-50 px-2.5 py-2 text-emerald-700">+</button>
                                    <button title="Withdraw" @click="openSavingsWithdraw(s.id)" class="rounded-xl bg-slate-100 px-2.5 py-2">−</button>
                                    <button title="Edit" @click="openSaverModal(s.id)" class="rounded-xl bg-slate-100 px-2.5 py-2">✎</button>
                                    <button title="Remove" @click="deleteSaver(s.id)" class="rounded-xl bg-slate-100 px-2.5 py-2 text-slate-500">✕</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <div class="mb-4 flex items-center justify-between">
                <div><h4 class="text-xl font-black">Withdrawals to Scotiabank</h4><p class="text-sm text-slate-500">Requests route to each saver's linked Scotiabank account.</p></div>
                <div class="flex gap-2 text-sm">
                    <span class="rounded-full bg-amber-50 px-3 py-1 font-black text-amber-700">Pending: {{ pendingWithdrawals }}</span>
                    <span class="rounded-full bg-green-50 px-3 py-1 font-black text-green-700">Paid: {{ paidWithdrawals }}</span>
                </div>
            </div>
            <div class="scrollbar overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr><th class="py-2">Ref</th><th>Saver</th><th>Amount</th><th>Penalty</th><th>Net to Bank</th><th>Destination</th><th>Requested</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <tr v-if="!savingsWithdrawals.length"><td colspan="9" class="py-5 text-center text-slate-400 font-bold">No withdrawal requests.</td></tr>
                        <tr v-for="(w, i) in savingsWithdrawals" :key="w.ref" class="border-t">
                            <td class="py-2 font-black">{{ w.ref }}</td>
                            <td>{{ w.saver }}</td>
                            <td class="font-black">{{ fmt(w.amount) }}</td>
                            <td class="text-rose-600">{{ fmt(w.penalty) }}</td>
                            <td class="font-black text-emerald-700">{{ fmt(w.net) }}</td>
                            <td class="text-xs">{{ w.dest }}</td>
                            <td>{{ w.requested }}</td>
                            <td><span :class="withdrawalStatusBadge(w.status)" class="rounded-full px-3 py-1 text-xs font-black">{{ w.status }}</span></td>
                            <td>
                                <div class="flex gap-1">
                                    <template v-if="w.status === 'Pending'">
                                        <button @click="savWdSetStatus(i, 'Approved')" class="rounded-lg bg-sky-50 px-2 py-1 text-xs font-black text-sky-700">Approve</button>
                                        <button @click="savWdSetStatus(i, 'Rejected')" class="rounded-lg bg-rose-50 px-2 py-1 text-xs font-black text-rose-700">Reject</button>
                                    </template>
                                    <button v-if="w.status === 'Approved'" @click="savWdSetStatus(i, 'Paid')" class="rounded-lg bg-green-600 px-2 py-1 text-xs font-black text-white">Mark Paid</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <div class="card rounded-3xl p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div><h4 class="text-xl font-black">Auto-Save Rules</h4><p class="text-sm text-slate-500">Sweep a % of each payout into savings automatically.</p></div>
                    <button @click="runAutoSaveSweep" class="flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 font-black text-white">↻ Run Sweep</button>
                </div>
                <div class="space-y-2">
                    <p v-if="!autoSaveSavers.length" class="text-sm font-bold text-slate-400">No auto-save rules yet. Set "Auto-Save %" on a saver to enable.</p>
                    <div v-for="s in autoSaveSavers" :key="s.id" class="flex items-center justify-between rounded-2xl bg-slate-50 p-3">
                        <div>
                            <b>{{ s.name }}</b>
                            <p class="text-xs text-slate-500">{{ s.autoSavePct }}% of payouts • est. {{ fmt(Math.round((savEstPayout(s) * s.autoSavePct) / 100)) }}/mo</p>
                        </div>
                        <button @click="openSaverModal(s.id)" class="text-sm font-black text-emerald-700">Edit</button>
                    </div>
                </div>
            </div>
            <div class="card rounded-3xl p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div><h4 class="text-xl font-black">Savings Goals</h4><p class="text-sm text-slate-500">Targets that keep savers motivated.</p></div>
                    <button @click="openGoalModal" class="flex items-center gap-2 rounded-2xl bg-slate-950 px-4 py-2.5 font-black text-white">
                        <Target class="h-4 w-4" /> Add Goal
                    </button>
                </div>
                <div class="space-y-3">
                    <p v-if="!savingsGoals.length" class="text-sm font-bold text-slate-400">No goals yet.</p>
                    <div v-for="g in savingsGoals" :key="g.id" class="rounded-2xl bg-slate-50 p-3">
                        <div class="flex items-center justify-between">
                            <div><b>{{ g.name }}</b><p class="text-xs text-slate-500">{{ goalSaverName(g) }} • {{ fmt(g.saved) }} / {{ fmt(g.target) }}</p></div>
                            <div class="flex items-center gap-2">
                                <span class="font-black text-emerald-600">{{ goalProgress(g) }}%</span>
                                <button @click="contributeGoal(g.id)" title="Contribute" class="rounded-lg bg-emerald-50 px-2 py-1 text-xs font-black text-emerald-700">+</button>
                                <button @click="deleteGoal(g.id)" class="text-slate-400 hover:text-rose-600">✕</button>
                            </div>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400" :style="{ width: goalProgress(g) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card rounded-3xl p-6">
            <div class="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div><h4 class="text-xl font-black">Statements &amp; Tax</h4><p class="text-sm text-slate-500">Interest statements per saver — feed straight into the Tax module (1099-INT style).</p></div>
                <div class="flex items-center gap-2">
                    <select v-model.number="savStmtPick" class="rounded-2xl border border-slate-200 px-3 py-2 font-bold">
                        <option v-for="s in savers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <button @click="exportSavingsStatement" class="flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 font-black">⤓ Export</button>
                    <button @click="sendSavingsToTax" class="flex items-center gap-2 rounded-2xl bg-slate-950 px-4 py-2 font-black text-white">
                        <Landmark class="h-4 w-4" /> Send to Tax
                    </button>
                </div>
            </div>
            <div class="whitespace-pre-line rounded-2xl bg-slate-50 p-5 font-mono text-sm">{{ savingsStatement }}</div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="card rounded-3xl p-6 xl:col-span-2">
                <h4 class="mb-3 text-xl font-black">In-App Savings Card (what the user sees)</h4>
                <div v-if="cardSaver" class="max-w-md rounded-3xl bg-gradient-to-br from-emerald-600 to-teal-500 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 font-black"><PiggyBank class="h-5 w-5" /> LinkUp Save</span>
                        <div class="flex items-center gap-2">
                            <button @click="howModalOpen = true" title="How it works" class="grid h-7 w-7 place-items-center rounded-full bg-white/20 font-black hover:bg-white/30">?</button>
                            <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-black">Powered by Scotiabank</span>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-emerald-100">Available Savings Balance</p>
                    <h2 class="text-4xl font-black">{{ fmt(cardSaver.balance) }}</h2>
                    <div class="mt-3 flex gap-4 text-sm">
                        <span>APY <b>{{ cardTier.apy.toFixed(2) }}%</b></span>
                        <span>Earned this month <b>{{ fmt(cardSaver.interestMTD || savingsMoInterest(cardSaver)) }}</b></span>
                    </div>
                    <div class="mt-5 flex gap-2">
                        <button @click="openSavingsDeposit(cardSaver.id)" class="flex-1 rounded-2xl bg-white px-4 py-2.5 font-black text-emerald-700">Deposit</button>
                        <button @click="openSavingsWithdraw(cardSaver.id)" class="flex-1 rounded-2xl bg-white/20 px-4 py-2.5 font-black">Withdraw</button>
                        <button @click="openSavingsHistory(cardSaver.id)" title="History" class="rounded-2xl bg-white/20 px-3 py-2.5 font-black">🕘</button>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="text-xs font-black text-slate-600">Preview saver:</label>
                    <select v-model.number="savCardPick" class="rounded-xl border border-slate-200 px-3 py-1.5 font-bold">
                        <option v-for="s in savers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
            </div>
            <div class="card rounded-3xl p-6">
                <h4 class="mb-3 text-xl font-black">How it works</h4>
                <ul class="space-y-2 text-sm font-bold text-slate-600">
                    <li class="flex gap-2"><span class="text-emerald-600">●</span> Earners opt in to sweep idle wallet balance into savings.</li>
                    <li class="flex gap-2"><span class="text-emerald-600">●</span> Scotiabank custodies deposits &amp; pays the wholesale rate.</li>
                    <li class="flex gap-2"><span class="text-emerald-600">●</span> Users earn the tier APY; LinkUp keeps the spread.</li>
                    <li class="flex gap-2"><span class="text-emerald-600">●</span> Interest accrues daily, posts monthly.</li>
                    <li class="flex gap-2"><span class="text-emerald-600">●</span> KYC-verified only; interest feeds the Tax module.</li>
                </ul>
            </div>
        </div>

        <!-- Add/Edit Saver Modal -->
        <div v-if="saverModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeSaverModal">
            <div class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between bg-gradient-to-r from-emerald-600 to-teal-500 p-6 text-white">
                    <div><h3 class="text-2xl font-black">{{ editingSaverId !== null ? 'Edit Saver' : 'Add Saver' }}</h3><p class="text-sm text-emerald-100">Enroll an earner into LinkUp Save</p></div>
                    <button @click="closeSaverModal"><X class="h-6 w-6" /></button>
                </div>
                <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="text-sm font-black text-slate-600">Name</label>
                        <input v-model="saverForm.name" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" placeholder="Saver name" />
                    </div>
                    <div>
                        <label class="text-sm font-black text-slate-600">Earner Type</label>
                        <select v-model="saverForm.type" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option>Event Organizer</option><option>Merchant</option><option>Eats Restaurant</option><option>Marketplace Seller</option><option>Live Creator</option><option>User</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-black text-slate-600">Country</label>
                        <input v-model="saverForm.country" list="lacCountryList" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Start typing a country..." />
                        <datalist id="lacCountryList"><option v-for="c in COUNTRIES" :key="c" :value="c" /></datalist>
                    </div>
                    <div>
                        <label class="text-sm font-black text-slate-600">Opening Balance ($)</label>
                        <input v-model.number="saverForm.balance" type="number" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="0" />
                    </div>
                    <div>
                        <label class="text-sm font-black text-slate-600">Status</label>
                        <select v-model="saverForm.status" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option>Active</option><option>Pending KYC</option><option>Paused</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-black text-slate-600">Linked Scotiabank Account</label>
                        <input v-model="saverForm.bankName" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Scotiabank Bahamas" />
                    </div>
                    <div>
                        <label class="text-sm font-black text-slate-600">Account (last 4)</label>
                        <input v-model="saverForm.bankLast4" maxlength="4" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="0000" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm font-black text-slate-600">Auto-Save (% of each payout) <span class="font-normal text-slate-400">0 = off</span></label>
                        <input v-model.number="saverForm.autoSavePct" type="number" min="0" max="100" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="0" />
                    </div>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 p-5">
                    <button @click="closeSaverModal" class="rounded-2xl bg-slate-100 px-6 py-3 font-black">Cancel</button>
                    <button @click="saveSaverModal" class="rounded-2xl bg-emerald-600 px-6 py-3 font-black text-white">{{ editingSaverId !== null ? 'Save' : 'Add Saver' }}</button>
                </div>
            </div>
        </div>

        <!-- Deposit Modal -->
        <div v-if="depositModalOpen && depositSaver" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeSavingsDeposit">
            <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between bg-gradient-to-r from-emerald-600 to-teal-500 p-6 text-white">
                    <div><h3 class="text-2xl font-black">Deposit to Savings</h3><p class="text-sm text-emerald-100">{{ depositSaver.name }} • current {{ fmt(depositSaver.balance) }}</p></div>
                    <button @click="closeSavingsDeposit"><X class="h-6 w-6" /></button>
                </div>
                <div class="space-y-4 p-6">
                    <div>
                        <label class="text-sm font-black text-slate-600">Funding Source</label>
                        <div class="mt-1 grid grid-cols-3 gap-2">
                            <button
                                v-for="src in ['Wallet', 'Credit Card', 'Bank Transfer']"
                                :key="src"
                                type="button"
                                @click="savDepSource = src"
                                :class="savDepSource === src ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200'"
                                class="flex flex-col items-center gap-1 rounded-2xl border-2 px-2 py-3 text-xs font-black"
                            >
                                {{ src === 'Wallet' ? 'LinkUp Wallet' : src }}
                            </button>
                        </div>
                    </div>
                    <div v-if="savDepSource === 'Wallet'" class="rounded-2xl bg-slate-50 p-3 text-sm font-bold text-slate-600">
                        Available wallet balance: <span class="text-emerald-700">{{ fmt(savWalletBalance) }}</span>
                    </div>
                    <div v-else-if="savDepSource === 'Credit Card'" class="grid grid-cols-2 gap-2">
                        <input class="col-span-2 rounded-2xl border border-slate-200 px-3 py-2" placeholder="Card number" />
                        <input class="rounded-2xl border border-slate-200 px-3 py-2" placeholder="MM/YY" />
                        <input class="rounded-2xl border border-slate-200 px-3 py-2" placeholder="CVC" />
                        <p class="col-span-2 text-xs text-slate-400">A processing fee may apply to card deposits.</p>
                    </div>
                    <div>
                        <label class="text-sm font-black text-slate-600">Choose your yield tier</label>
                        <div class="mt-1 grid grid-cols-1 gap-2">
                            <button
                                v-for="t in depositTiers"
                                :key="t.name"
                                type="button"
                                @click="pickDepositTier(t.min)"
                                :class="depositProjectedTier.name === t.name ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200'"
                                class="flex items-center justify-between rounded-2xl border-2 px-3 py-2.5 text-left"
                            >
                                <div>
                                    <p class="font-black">{{ t.name }} <span class="text-emerald-600">{{ t.apy.toFixed(2) }}% APY</span></p>
                                    <p class="text-xs text-slate-500">Min balance {{ fmt(t.min) }} • {{ t.lock || savingsConfig.lockDays }}-day lock</p>
                                </div>
                                <span class="text-xs font-bold text-slate-400">
                                    {{ Math.max(0, t.min - depositSaver.balance) > 0 ? '+ ' + fmt(Math.max(0, t.min - depositSaver.balance)) + ' to qualify' : '✓ qualifies' }}
                                </span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-black text-slate-600">Amount ($)</label>
                        <input v-model.number="savDepAmount" type="number" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 text-lg font-black" placeholder="0.00" />
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 text-xs font-bold text-slate-600">
                        <template v-if="savDepAmount && savDepAmount > 0">
                            <template v-if="depositProjectedTier.name === 'Below Min'">
                                New balance {{ fmt(depositProjectedBalance) }} is below the {{ fmt(savingsConfig.minBalance) }} minimum to earn interest.
                            </template>
                            <template v-else>
                                New balance {{ fmt(depositProjectedBalance) }} → <b class="text-emerald-700">{{ depositProjectedTier.name }} tier @ {{ depositProjectedTier.apy.toFixed(2) }}% APY</b>
                                (~{{ fmt((depositProjectedBalance * depositProjectedTier.apy) / 100) }}/yr).
                            </template>
                        </template>
                        <template v-else>
                            <span v-if="depositSaver.opened || depositSaver.balance >= savingsConfig.minBalance" class="text-slate-500">
                                Account open — add any amount of <b>{{ fmt(savingsConfig.topUpMin) }}</b> or more.
                            </span>
                            <span v-else class="text-slate-500">
                                Opening deposit must be at least <b>{{ fmt(Math.max(0, savingsConfig.minBalance - depositSaver.balance)) }}</b> to activate savings.
                            </span>
                        </template>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-3 text-xs font-bold text-emerald-700">
                        🔒 Funds lock for {{ depositProjectedTier.lock || savingsConfig.lockDays }} days to earn the tier APY. Early withdrawal may incur a penalty.
                    </div>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 p-5">
                    <button @click="closeSavingsDeposit" class="rounded-2xl bg-slate-100 px-6 py-3 font-black">Cancel</button>
                    <button @click="applySavingsDeposit" class="rounded-2xl bg-emerald-600 px-6 py-3 font-black text-white">Deposit</button>
                </div>
            </div>
        </div>

        <!-- Withdraw Modal -->
        <div v-if="withdrawModalOpen && withdrawSaver" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeSavingsWithdraw">
            <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between bg-gradient-to-r from-slate-800 to-slate-600 p-6 text-white">
                    <div><h3 class="text-2xl font-black">Withdraw to Bank</h3><p class="text-sm text-slate-200">{{ withdrawSaver.name }}</p></div>
                    <button @click="closeSavingsWithdraw"><X class="h-6 w-6" /></button>
                </div>
                <div class="space-y-4 p-6">
                    <div class="rounded-2xl bg-slate-50 p-3 text-sm">
                        <div class="flex justify-between"><span class="font-bold text-slate-500">Available</span><b>{{ fmt(withdrawSaver.balance) }}</b></div>
                        <div class="mt-1 flex justify-between"><span class="font-bold text-slate-500">Destination</span><b>{{ withdrawSaver.bankName ? withdrawSaver.bankName + ' ••' + withdrawSaver.bankLast4 : 'No linked bank' }}</b></div>
                    </div>
                    <div v-if="savLockDaysLeft(withdrawSaver) > 0" class="rounded-2xl bg-amber-50 p-3 text-xs font-bold text-amber-700">
                        <template v-if="savingsConfig.allowEarly">🔒 Locked for {{ savLockDaysLeft(withdrawSaver) }} more day(s). Early withdrawal incurs a {{ savingsConfig.earlyPenalty }}% penalty.</template>
                        <template v-else>🔒 Locked for {{ savLockDaysLeft(withdrawSaver) }} more day(s). Early withdrawal is not allowed — matures {{ new Date(withdrawSaver.lockUntil).toLocaleDateString() }}.</template>
                    </div>
                    <div>
                        <label class="text-sm font-black text-slate-600">Amount ($)</label>
                        <input v-model.number="savWdAmount" type="number" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 text-lg font-black" placeholder="0.00" />
                    </div>
                    <div v-if="savWdAmount && savWdAmount > 0" class="rounded-2xl bg-slate-50 p-3 text-sm">
                        <div class="flex justify-between"><span class="font-bold text-slate-500">Early withdrawal penalty</span><b class="text-rose-600">{{ fmt(withdrawPenalty) }}</b></div>
                        <div class="mt-1 flex justify-between"><span class="font-bold text-slate-500">Net to bank</span><b class="text-emerald-700">{{ fmt(withdrawNet) }}</b></div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 p-5">
                    <button @click="closeSavingsWithdraw" class="rounded-2xl bg-slate-100 px-6 py-3 font-black">Cancel</button>
                    <button
                        @click="applySavingsWithdraw"
                        :disabled="savLockDaysLeft(withdrawSaver) > 0 && !savingsConfig.allowEarly"
                        :class="savLockDaysLeft(withdrawSaver) > 0 && !savingsConfig.allowEarly ? 'cursor-not-allowed bg-slate-300' : 'bg-slate-900'"
                        class="rounded-2xl px-6 py-3 font-black text-white"
                    >
                        Request Withdrawal
                    </button>
                </div>
            </div>
        </div>

        <!-- Goal Modal -->
        <div v-if="goalModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeGoalModal">
            <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between bg-gradient-to-r from-emerald-600 to-teal-500 p-6 text-white">
                    <h3 class="text-2xl font-black">Add Savings Goal</h3>
                    <button @click="closeGoalModal"><X class="h-6 w-6" /></button>
                </div>
                <div class="space-y-4 p-6">
                    <div>
                        <label class="text-sm font-black text-slate-600">Saver</label>
                        <select v-model.number="goalForm.saverId" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option v-for="s in savers" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-black text-slate-600">Goal Name</label>
                        <input v-model="goalForm.name" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" placeholder="e.g. New venue deposit" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="text-sm font-black text-slate-600">Target ($)</label><input v-model.number="goalForm.target" type="number" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="10000" /></div>
                        <div><label class="text-sm font-black text-slate-600">Saved so far ($)</label><input v-model.number="goalForm.saved" type="number" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="0" /></div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 p-5">
                    <button @click="closeGoalModal" class="rounded-2xl bg-slate-100 px-6 py-3 font-black">Cancel</button>
                    <button @click="saveGoal" class="rounded-2xl bg-emerald-600 px-6 py-3 font-black text-white">Add Goal</button>
                </div>
            </div>
        </div>

        <!-- How It Works Modal -->
        <div v-if="howModalOpen" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-3" @click.self="howModalOpen = false">
            <div class="my-6 w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between bg-gradient-to-br from-emerald-600 to-teal-500 p-6 text-white">
                    <div class="flex items-center gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-white/20"><PiggyBank class="h-6 w-6" /></div>
                        <div><h3 class="text-2xl font-black">How LinkUp Save Works</h3><p class="text-sm text-emerald-100">Grow your money — powered by Scotiabank</p></div>
                    </div>
                    <button @click="howModalOpen = false" class="grid h-11 w-11 place-items-center rounded-2xl bg-white/20"><X class="h-6 w-6" /></button>
                </div>
                <div class="scrollbar max-h-[72vh] space-y-4 overflow-y-auto p-6">
                    <div class="flex gap-3">
                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-emerald-100 font-black text-emerald-700">1</div>
                        <div><p class="font-black text-slate-800">Open with a minimum deposit</p><p class="text-sm text-slate-500">Start your savings with the opening minimum (currently <b>{{ fmt(savingsConfig.minBalance) }}</b>). Fund it from your LinkUp Wallet, a credit card, or a bank transfer.</p></div>
                    </div>
                    <div class="flex gap-3">
                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-emerald-100 font-black text-emerald-700">2</div>
                        <div>
                            <p class="font-black text-slate-800">Earn high-yield interest</p>
                            <p class="text-sm text-slate-500">Your balance earns interest by tier — the more you save, the higher your APY. Interest accrues daily and is paid monthly.</p>
                            <div class="mt-2 grid grid-cols-1 gap-1.5">
                                <div v-for="t in sortedTiers" :key="t.name" class="flex items-center justify-between rounded-xl bg-emerald-50 px-3 py-1.5">
                                    <span class="text-sm font-bold text-emerald-800">{{ t.name }} <span class="text-emerald-600">{{ t.apy.toFixed(2) }}% APY</span></span>
                                    <span class="text-xs font-bold text-slate-500">{{ fmt(t.min) }}+ • {{ t.lock || savingsConfig.lockDays }}-day lock</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-emerald-100 font-black text-emerald-700">3</div>
                        <div><p class="font-black text-slate-800">Top up anytime</p><p class="text-sm text-slate-500">After opening, add any amount of <b>{{ fmt(savingsConfig.topUpMin) }}</b> or more whenever you like — or turn on <b>Auto-Save</b> to sweep a % of every payout automatically.</p></div>
                    </div>
                    <div class="flex gap-3">
                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-amber-100 font-black text-amber-700">4</div>
                        <div><p class="font-black text-slate-800">Lock period</p><p class="text-sm text-slate-500">Deposits lock for a short term to earn the tier APY. Withdrawing early is allowed but applies a small <b>{{ savingsConfig.earlyPenalty }}%</b> penalty on the amount withdrawn.</p></div>
                    </div>
                    <div class="flex gap-3">
                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-sky-100 font-black text-sky-700">5</div>
                        <div><p class="font-black text-slate-800">Withdraw to your bank</p><p class="text-sm text-slate-500">Cash out to your linked Scotiabank account at any time. Matured funds have no penalty. Track every move in your Transaction History.</p></div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4 text-xs font-bold text-slate-500">🛡️ Funds are held with Scotiabank. Interest may be reportable as income — see your statements under Statements &amp; Tax.</div>
                    <button @click="howModalOpen = false" class="w-full rounded-2xl bg-emerald-600 px-6 py-3 font-black text-white">Got it</button>
                </div>
            </div>
        </div>

        <!-- Transaction History Modal -->
        <div v-if="historyModalOpen && historySaver" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-3" @click.self="closeSavingsHistory">
            <div class="my-4 w-full max-w-3xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between bg-gradient-to-r from-sky-600 to-cyan-500 p-6 text-white">
                    <div><h3 class="text-2xl font-black">Transaction History</h3><p class="text-sm text-sky-100">{{ historySaver.name }} • balance {{ fmt(historySaver.balance) }}</p></div>
                    <div class="flex gap-2">
                        <button @click="exportSavingsHistory" class="flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 font-black text-sky-600">⤓ Export</button>
                        <button @click="closeSavingsHistory" class="grid h-11 w-11 place-items-center rounded-2xl bg-white/20"><X class="h-6 w-6" /></button>
                    </div>
                </div>
                <div class="scrollbar max-h-[72vh] overflow-y-auto p-6">
                    <div class="mb-5 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-slate-50 p-3 text-center"><p class="text-lg font-black text-emerald-700">{{ fmt(historyKpis.deposited) }}</p><p class="text-xs font-bold text-slate-500">Total Deposited</p></div>
                        <div class="rounded-2xl bg-slate-50 p-3 text-center"><p class="text-lg font-black text-rose-600">{{ fmt(historyKpis.withdrawn) }}</p><p class="text-xs font-bold text-slate-500">Total Withdrawn</p></div>
                        <div class="rounded-2xl bg-slate-50 p-3 text-center"><p class="text-lg font-black text-sky-700">{{ fmt(historyKpis.interest) }}</p><p class="text-xs font-bold text-slate-500">Interest Earned</p></div>
                    </div>
                    <div class="scrollbar overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-xs tracking-wide text-slate-400 uppercase">
                                <tr><th class="py-2 pr-4 font-black">Date</th><th class="py-2 pr-4 font-black">Type</th><th class="py-2 pr-4 font-black">Source / Detail</th><th class="py-2 pl-4 text-right font-black">Amount</th><th class="py-2 pl-4 text-right font-black">Balance</th></tr>
                            </thead>
                            <tbody>
                                <tr v-if="!historyTxns.length"><td colspan="5" class="py-6 text-center text-slate-400 font-bold">No transactions yet.</td></tr>
                                <tr v-for="t in historyTxns" :key="t.id" class="border-t border-slate-100">
                                    <td class="py-4 pr-4 align-top"><div class="font-bold text-slate-700">{{ new Date(t.ts).toLocaleDateString() }}</div><div class="text-xs text-slate-400">{{ new Date(t.ts).toLocaleTimeString() }}</div></td>
                                    <td class="py-4 pr-4 align-top"><span :class="txnBadge(t.type)" class="rounded-full px-2 py-0.5 text-xs font-black">{{ t.type }}</span></td>
                                    <td class="py-4 pr-4 align-top"><div class="text-slate-600">{{ t.source || '—' }}</div><div v-if="t.note" class="mt-0.5 text-xs text-slate-400">{{ t.note }}</div></td>
                                    <td class="py-4 pl-4 text-right align-top font-black whitespace-nowrap" :class="txnColor(t.type)">{{ t.amount < 0 ? '−' : '+' }}{{ fmt(Math.abs(t.amount)) }}</td>
                                    <td class="py-4 pl-4 text-right align-top font-bold whitespace-nowrap text-slate-700">{{ fmt(t.balanceAfter) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
