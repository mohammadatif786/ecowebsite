import { computed, ref } from 'vue';

export function useEWalletData(props: {
    initialUnits: any[];
    initialCountries: any[];
    initialWalletMovements: any[];
    initialWalletUsers: any[];
    initialWalletUserStats: any;
    initialWalletCountryBalances: any[];
    initialCurrencyExposure: any[];
    initialWalletBalanceSummary: any;
    initialSettlements: any[];
    initialSettlementSummary: any;
    initialAsueCircles: any[];
    initialAsueSummary: any;
    initialPayoutQueue: any[];
    initialPayoutQueueSummary: any;
}) {
    const units = ref(props.initialUnits);
    const countries = ref(props.initialCountries);

    const filters = ref({
        region: 'All',
        country: 'All Countries',
        period: 'Monthly',
    });

    const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
    const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(n));

    const getScale = () => {
        const p = filters.value.period;
        return p === 'Today' ? 1 / 30 : p === 'Weekly' ? 0.25 : p === 'Quarterly' ? 3 : p === 'Yearly' ? 12 : p === '5-Year' ? 60 : 1;
    };

    const getFilteredCountries = () => {
        return countries.value.filter(
            (c) =>
                (filters.value.region === 'All' || c.region === filters.value.region) &&
                (filters.value.country === 'All Countries' || c.country === filters.value.country),
        );
    };

    const countryFin = (c: any) => {
        let gross = 0, platform = 0, bank = 0, cost = 0;
        units.value.forEach((u) => {
            const v = (c[u.key] || 0) * getScale();
            gross += v;
            platform += v * u.platformRate;
            bank += v * u.bankRate;
            cost += v * u.costRate;
        });
        return { gross, platform, bank, cost, net: platform - cost };
    };

    const getTotals = () => {
        const rs = getFilteredCountries();
        const fins = rs.map(countryFin);
        const SCOTIA_SHARE = 0.4;
        return {
            gross: fins.reduce((s, x) => s + x.gross, 0),
            platform: fins.reduce((s, x) => s + x.platform, 0),
            bank: fins.reduce((s, x) => s + x.bank, 0),
            cost: fins.reduce((s, x) => s + x.cost, 0),
            net: fins.reduce((s, x) => s + x.net, 0),
            users: rs.reduce((s, c) => s + c.users, 0) * getScale(),
            merchants: rs.reduce((s, c) => s + c.merchants, 0),
            organizers: rs.reduce((s, c) => s + c.organizers, 0),
            countries: rs.length,
        };
    };

    const ribbonMetrics = computed(() => {
        const t = getTotals();
        const SCOTIA_SHARE = 0.4;
        const totalRev = t.platform + t.bank * (1 - SCOTIA_SHARE);
        const net = totalRev - t.cost;
        return {
            gtv: fmt(t.gross),
            linkupRev: fmt(t.platform),
            procPool: fmt(t.bank),
            netProfit: fmt(net),
            users: num(t.users),
            merchants: num(t.merchants),
            organizers: num(t.organizers),
            countries: num(t.countries)
        };
    });

    // Wallet Movements — sourced from the real wallet ledger (Transaction model), mapped
    // server-side in AdminOverviewService::getWalletMissionControlData().
    const walletMovements = ref(props.initialWalletMovements);

    // Wallet User accounts (KYC/balance/linked bank & card/risk) — real per-user directory
    // for the "Wallet Users" page, distinct from walletUserDirectory (which is derived
    // client-side from wallet movements for Mission Control's ledger-based directory).
    const walletUserAccounts = ref(props.initialWalletUsers);
    const walletUserAccountStats = ref(props.initialWalletUserStats);

    // Real per-country stored value + currency exposure — from the wallet package's
    // `balances` table, for the "Wallet Balances" page.
    const walletCountryBalances = ref(props.initialWalletCountryBalances);
    const currencyExposure = ref(props.initialCurrencyExposure);
    const walletBalanceSummary = ref(props.initialWalletBalanceSummary);

    const walletFilter = ref({
        type: 'All Types',
        status: 'All Statuses',
        search: ''
    });

    const filteredMovements = computed(() => {
        const q = walletFilter.value.search.toLowerCase();
        return walletMovements.value.filter(w => {
            const c = countries.value.find(x => x.country === w.country);
            const okRegion = filters.value.region === 'All' || c?.region === filters.value.region;
            const okCountry = filters.value.country === 'All Countries' || w.country === filters.value.country;
            const okType = walletFilter.value.type === 'All Types' || w.type === walletFilter.value.type;
            const okStatus = walletFilter.value.status === 'All Statuses' || w.status === walletFilter.value.status;
            const okSearch = !q || [w.id, w.date, w.country, w.user, w.type, w.direction, w.channel, w.status].join(' ').toLowerCase().includes(q);
            return okRegion && okCountry && okType && okStatus && okSearch;
        });
    });

    const walletStats = computed(() => {
        const movementRows = filteredMovements.value;
        const balances = getFilteredCountries().reduce((s, c) => s + (c.wallet || 0) * getScale(), 0);
        const movementVolume = movementRows.reduce((s, w) => s + w.amount, 0) * getScale();
        const fees = movementRows.reduce((s, w) => s + w.fee, 0) * getScale();
        const loads = movementRows.filter(w => w.type === 'Wallet Load').reduce((s, w) => s + w.amount, 0) * getScale();
        const sent = movementRows.filter(w => w.type === 'Send Money' || w.type === 'FX Transfer').reduce((s, w) => s + w.amount, 0) * getScale();
        const received = movementRows.filter(w => w.type === 'Receive Money' || w.type === 'Refund').reduce((s, w) => s + w.amount, 0) * getScale();
        const cashouts = movementRows.filter(w => w.type === 'Cash Out' || w.type === 'Merchant Settlement').reduce((s, w) => s + w.amount, 0) * getScale();
        const merchantPayments = movementRows.filter(w => ['Merchant Pay', 'Bill Payment', 'Event Payment', 'Marketplace Purchase', 'Coin Purchase'].includes(w.type)).reduce((s, w) => s + w.amount, 0) * getScale();
        const availableReserves = balances * 1.16;
        const reserveRatio = balances ? availableReserves / balances * 100 : 0;
        return { balances, movementVolume, fees, loads, sent, received, cashouts, merchantPayments, availableReserves, reserveRatio };
    });

    const walletCountryStats = (countryName: string) => {
        const c = countries.value.find(x => x.country === countryName);
        const movementRows = walletMovements.value.filter(w => w.country === countryName);
        const totalBalance = (c?.wallet || 0) * getScale();
        const walletUsers = (c?.users || 0) * 0.64 * getScale();
        const loads = movementRows.filter(w => w.type === 'Wallet Load').reduce((s, w) => s + w.amount, 0) * getScale();
        const sent = movementRows.filter(w => w.type === 'Send Money' || w.type === 'FX Transfer').reduce((s, w) => s + w.amount, 0) * getScale();
        const received = movementRows.filter(w => w.type === 'Receive Money' || w.type === 'Refund').reduce((s, w) => s + w.amount, 0) * getScale();
        const cashouts = movementRows.filter(w => w.type === 'Cash Out' || w.type === 'Merchant Settlement').reduce((s, w) => s + w.amount, 0) * getScale();
        const merchantPay = movementRows.filter(w => ['Merchant Pay', 'Bill Payment', 'Event Payment', 'Marketplace Purchase', 'Coin Purchase'].includes(w.type)).reduce((s, w) => s + w.amount, 0) * getScale();
        const fees = movementRows.reduce((s, w) => s + w.fee, 0) * getScale();
        const reserveRequired = totalBalance * 1.10;
        return { totalBalance, walletUsers, loads, sent, received, cashouts, merchantPay, fees, reserveRequired };
    };

    const walletUserDirectory = computed(() => {
        const grouped: any = {};
        walletMovements.value.forEach(t => {
            if (!grouped[t.user]) grouped[t.user] = { user: t.user, country: t.country, tx: 0, balance: t.balanceAfter, last: t.date, status: 'Active' };
            grouped[t.user].tx++;
            if (t.date >= grouped[t.user].last) {
                grouped[t.user].last = t.date;
                grouped[t.user].balance = t.balanceAfter;
                grouped[t.user].country = t.country;
            }
            if (t.status === 'Review') grouped[t.user].status = 'Review';
            if (t.status === 'Pending' && grouped[t.user].status !== 'Review') grouped[t.user].status = 'Pending';
        });
        return Object.values(grouped);
    });

    // Settlement Queue — sourced from real Payout (event organizer) records, mapped
    // server-side in AdminOverviewService::getSettlementCenterData().
    const settlements = ref(props.initialSettlements);
    const settlementSummary = ref(props.initialSettlementSummary);

    // ASUE (rotating savings "hand") circles — real Asue records, mapped server-side
    // in AdminOverviewService::getAsueDrawerData().
    const asueCircles = ref(props.initialAsueCircles);
    const asueSummary = ref(props.initialAsueSummary);

    // Wallet Payout Queue — real cash-out requests merged from BankWithdrawal (User
    // Wallet), Payout (Organizer Wallet), and Asue "hand" payout transactions (ASU
    // Drawer), mapped server-side in AdminOverviewService::getPayoutQueueData().
    const payoutQueue = ref(props.initialPayoutQueue);
    const payoutQueueSummary = ref(props.initialPayoutQueueSummary);

    const complianceCases = ref([
        { id: 'AML-3002', date: '2026-06-05', user: 'Lucas Silva', country: 'Brazil', type: 'Cross-border FX', amount: 8400, risk: 'High', status: 'Open', reason: 'Rapid cross-border FX pattern exceeds $5,000 threshold.' },
        { id: 'AML-3007', date: '2026-06-05', user: 'Renata Costa', country: 'Brazil', type: 'Structuring', amount: 9600, risk: 'High', status: 'Investigating', reason: '5 cash-ins of ~$1,900 in 24h — possible structuring.' },
        { id: 'AML-3011', date: '2026-06-04', user: 'Andre Charles', country: 'Jamaica', type: 'Velocity', amount: 27300, risk: 'High', status: 'Open', reason: 'Daily out-flow $27,300 exceeds $25,000 velocity threshold.' },
        { id: 'KYC-1209', date: '2026-06-04', user: 'New Wallet User', country: 'Jamaica', type: 'KYC Gap', amount: 1200, risk: 'Medium', status: 'Pending Docs', reason: 'Loaded funds before completing address verification.' },
        { id: 'AML-3014', date: '2026-06-03', user: 'Marcus Joseph', country: 'Trinidad & Tobago', type: 'Cashout Delay', amount: 1500, risk: 'Medium', status: 'Open', reason: 'Large cashout on a 3-day-old account.' }
    ]);

    const billProviders = ref([
        { name: 'BPL Power', status: 'Active', color: '#F59E0B', apiBase: 'https://api.reloadly.com/utility', billerId: 'BPL-242', services: 'Electricity', rails: 'Card, Wallet, Bank', settlement: 'Scotiabank ••2290', pci: true },
        { name: 'BTC Mobile', status: 'Active', color: '#28A8FF', apiBase: 'https://topups.reloadly.com', billerId: 'BTC-242', services: 'Mobile, Data', rails: 'Card, Wallet', settlement: 'Scotiabank ••2290', pci: true },
        { name: 'Flow', status: 'Active', color: '#6366F1', apiBase: 'https://api.flow.com/billpay', billerId: 'FLOW-TT', services: 'Internet, Cable, Mobile', rails: 'Card, Wallet, Bank', settlement: 'Scotiabank ••7741', pci: true }
    ]);

    const auditLogs = ref([
        { ts: '2026-06-01 08:01', admin: 'Finance Admin', role: 'Super Admin', category: 'Update', action: 'Changed event fee setting', module: 'Pricing', record: 'Bahamas', result: 'Success', device: 'Chrome • Admin Console' },
        { ts: '2026-06-01 09:12', admin: 'Compliance Officer', role: 'Compliance', category: 'Compliance', action: 'Reviewed remittance alert', module: 'Compliance', record: 'AML-3001', result: 'Success', device: 'Chrome • Admin Console' },
        { ts: '2026-06-01 10:33', admin: 'Settlement Manager', role: 'Finance', category: 'Update', action: 'Approved payout', module: 'Settlements', record: 'SET-9003', result: 'Success', device: 'Chrome • Admin Console' }
    ]);

    const systemLogs = ref([
        { ts: '2026-06-01 11:45', level: 'INFO', service: 'wallet-ledger', msg: 'Reconciliation completed in 1.2s.' },
        { ts: '2026-06-01 11:40', level: 'INFO', service: 'bill-gateway', msg: 'Heartbeat OK (200).' },
        { ts: '2026-06-01 11:35', level: 'WARN', service: 'asu-engine', msg: 'ASU group #418 exceeded monthly contribution threshold.' },
        { ts: '2026-06-01 11:30', level: 'ERROR', service: 'fx-rate', msg: 'FX feed timeout for MXN; fell back to cached rate.' }
    ]);

    const reconItems = ref([
        { id: 'REC-5001', date: '2026-06-05', country: 'Bahamas', type: 'Bank File Delay', ref: 'ST-1002', amount: 0, status: 'Open', detail: 'Scotiabank batch file pending for merchant settlement ST-1002.' },
        { id: 'REC-5002', date: '2026-06-05', country: 'Brazil', type: 'Amount Mismatch', ref: 'WLT-9006', amount: 420, status: 'Open', detail: 'Cross-border transfer: ledger vs bank file differ by $420.' }
    ]);

    const riskRules = ref([
        { id: 'velocity', name: 'Velocity Rule', tmpl: 'More than {v} transfers in 30 minutes triggers Review.', value: 5, enabled: true },
        { id: 'largeTxn', name: 'Large Transaction Rule', tmpl: 'Single wallet movement above ${v} requires enhanced review.', value: 2000, enabled: true },
        { id: 'asu', name: 'ASU Limit Rule', tmpl: 'Contribution above ${v}/month creates a compliance case.', value: 6000, enabled: true }
    ]);

    const savers = ref([
        { id: 1, name: 'Island Vibes Events', type: 'Event Organizer', country: 'Bahamas', balance: 18400, interestYTD: 612, status: 'Active', lockDays: 62, bank: 'Scotiabank Bahamas ••4821', apy: 4.0 },
        { id: 2, name: 'Bahama Grill', type: 'Eats Restaurant', country: 'Bahamas', balance: 7200, interestYTD: 188, status: 'Active', lockDays: 0, bank: 'Scotiabank Bahamas ••1190', apy: 3.0 },
        { id: 3, name: 'Trini Flavors', type: 'Eats Restaurant', country: 'Trinidad & Tobago', balance: 3100, interestYTD: 74, status: 'Active', lockDays: 18, bank: 'Scotiabank T&T ••7733', apy: 3.0 }
    ]);

    const savingsWithdrawals = ref([
        { ref: 'WD-7A2X9B', saver: 'Island Vibes Events', amount: 5000, penalty: 0, net: 5000, dest: 'Scotiabank Bahamas ••4821', requested: '2026-06-02', status: 'Pending' },
        { ref: 'WD-3F8K1L', saver: 'Bahama Grill', amount: 1200, penalty: 18, net: 1182, dest: 'Scotiabank Bahamas ••1190', requested: '2026-06-01', status: 'Paid' }
    ]);

    const cryptoPurchases = ref([
        { ref: 'CRX-5001', user: 'Aaliyah Clarke', asset: 'BTC', usd: 250, crypto: 0.003731, provFee: 3.75, markup: 2.50, status: 'Settled' },
        { ref: 'CRX-5002', user: 'David Miller', asset: 'USDC', usd: 500, crypto: 492.50, provFee: 7.50, markup: 5.00, status: 'Settled' }
    ]);

    return {
        units,
        countries,
        filters,
        fmt,
        num,
        getScale,
        getFilteredCountries,
        getTotals,
        ribbonMetrics,
        walletMovements,
        walletFilter,
        filteredMovements,
        walletStats,
        walletCountryStats,
        walletUserDirectory,
        walletUserAccounts,
        walletUserAccountStats,
        walletCountryBalances,
        currencyExposure,
        walletBalanceSummary,
        settlements,
        settlementSummary,
        asueCircles,
        asueSummary,
        payoutQueue,
        payoutQueueSummary,
        complianceCases,
        billProviders,
        auditLogs,
        systemLogs,
        reconItems,
        riskRules,
        savers,
        savingsWithdrawals,
        cryptoPurchases
    };
}
