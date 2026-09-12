import { ref, computed } from 'vue';

export function useGovernmentData(props: {
    filters: any;
    countries: any[];
    fmt: (n: number) => string;
    num: (n: number) => string;
    getScale: () => number;
}) {
    const govPrograms = ref([
        { id: 'GP-NIB-BS', name: 'NIB Old-Age Pension', agency: 'National Insurance Board', country: 'Bahamas', beneficiaries: 4200, avg: 520, schedule: 'Monthly', status: 'Active' },
        { id: 'GP-DIS-BS', name: 'Disability Grant', agency: 'Dept of Social Services', country: 'Bahamas', beneficiaries: 1100, avg: 300, schedule: 'Monthly', status: 'Active' },
        { id: 'GP-NIS-JM', name: 'NIS Pension', agency: 'Ministry of Labour & SS', country: 'Jamaica', beneficiaries: 9800, avg: 240, schedule: 'Monthly', status: 'Active' },
        { id: 'GP-PATH-JM', name: 'PATH Cash Grant', agency: 'Ministry of Labour & SS', country: 'Jamaica', beneficiaries: 15200, avg: 90, schedule: 'Bi-Monthly', status: 'Active' },
        { id: 'GP-SCP-TT', name: 'Senior Citizens Pension', agency: 'Ministry of Social Development', country: 'Trinidad and Tobago', beneficiaries: 7400, avg: 430, schedule: 'Monthly', status: 'Active' },
        { id: 'GP-SUP-DR', name: 'Programa Supérate', agency: 'Gabinete de Política Social', country: 'Dominican Republic', beneficiaries: 22000, avg: 65, schedule: 'Monthly', status: 'Active' },
        { id: 'GP-CM-CO', name: 'Colombia Mayor', agency: 'Prosperidad Social', country: 'Colombia', beneficiaries: 31000, avg: 50, schedule: 'Bi-Monthly', status: 'Pilot' }
    ]);

    const govBeneficiaries = ref([
        { id: 'GB-0001', name: 'Eleanor Rolle', natId: 'BS-441902', program: 'NIB Old-Age Pension', country: 'Bahamas', monthly: 520, wallet: 'Linked', kyc: 'Tier 2', pol: 'Verified' },
        { id: 'GB-0002', name: 'Winston Bain', natId: 'BS-338210', program: 'NIB Old-Age Pension', country: 'Bahamas', monthly: 520, wallet: 'Auto-Created', kyc: 'Tier 1', pol: 'Due' },
        { id: 'GB-0003', name: 'Cynthia Munroe', natId: 'BS-552014', program: 'Disability Grant', country: 'Bahamas', monthly: 300, wallet: 'Linked', kyc: 'Tier 2', pol: 'Verified' },
        { id: 'GB-0004', name: 'Devon Campbell', natId: 'JM-770145', program: 'NIS Pension', country: 'Jamaica', monthly: 240, wallet: 'Auto-Created', kyc: 'Tier 1', pol: 'Verified' },
        { id: 'GB-0005', name: 'Marcia Brown', natId: 'JM-690877', program: 'PATH Cash Grant', country: 'Jamaica', monthly: 90, wallet: 'Linked', kyc: 'Tier 1', pol: 'Due' },
        { id: 'GB-0006', name: 'Anil Persad', natId: 'TT-220984', program: 'Senior Citizens Pension', country: 'Trinidad and Tobago', monthly: 430, wallet: 'Linked', kyc: 'Tier 2', pol: 'Verified' },
        { id: 'GB-0007', name: 'Lystra Joseph', natId: 'TT-118235', program: 'Senior Citizens Pension', country: 'Trinidad and Tobago', monthly: 430, wallet: 'Auto-Created', kyc: 'Tier 1', pol: 'Overdue' },
        { id: 'GB-0008', name: 'Altagracia Núñez', natId: 'DR-905512', program: 'Programa Supérate', country: 'Dominican Republic', monthly: 65, wallet: 'Auto-Created', kyc: 'Tier 1', pol: 'Verified' },
        { id: 'GB-0009', name: 'Ramón Castillo', natId: 'DR-771230', program: 'Programa Supérate', country: 'Dominican Republic', monthly: 65, wallet: 'Linked', kyc: 'Tier 1', pol: 'Due' },
        { id: 'GB-0010', name: 'Rosa Gutiérrez', natId: 'CO-4471902', program: 'Colombia Mayor', country: 'Colombia', monthly: 50, wallet: 'Auto-Created', kyc: 'Tier 1', pol: 'Verified' },
        { id: 'GB-0011', name: 'Doris Williams', natId: 'JM-551447', program: 'NIS Pension', country: 'Jamaica', monthly: 240, wallet: 'Linked', kyc: 'Tier 2', pol: 'Verified' },
        { id: 'GB-0012', name: 'Patrick Ferguson', natId: 'BS-220015', program: 'Disability Grant', country: 'Bahamas', monthly: 300, wallet: 'Auto-Created', kyc: 'Tier 1', pol: 'Due' }
    ]);

    const govBatches = ref([
        { id: 'GD-5012', program: 'NIB Old-Age Pension', country: 'Bahamas', count: 4200, net: 2184000, fee: 9870, method: 'Wallet (Scotiabank float)', status: 'Disbursed', date: '2026-05-28' },
        { id: 'GD-5011', program: 'NIS Pension', country: 'Jamaica', count: 9800, net: 2352000, fee: 14210, method: 'Wallet (Scotiabank float)', status: 'Disbursed', date: '2026-05-27' },
        { id: 'GD-5010', program: 'Programa Supérate', country: 'Dominican Republic', count: 22000, net: 1430000, fee: 18700, method: 'Wallet (Scotiabank float)', status: 'Disbursed', date: '2026-05-26' }
    ]);

    const govConfig = ref({
        flat: 0.50,
        pct: 1.0,
        cap: 5.0,
        cashout: 1.5,
        saas: 2500,
        yield: 4.5,
        split: 60
    });

    const govFloat = ref({
        balance: 14250000
    });

    // Filtering logic
    const filteredPrograms = computed(() => {
        return govPrograms.value.filter(p => {
            const countriesArr = Array.isArray(props.countries) ? props.countries : props.countries.value;
            const filtersVal = props.filters.value || props.filters;

            const c = countriesArr?.find((x: any) => x.country === p.country);
            return (filtersVal.region === 'All' || c?.region === filtersVal.region) &&
                   (filtersVal.country === 'All Countries' || p.country === filtersVal.country);
        });
    });

    const filteredBeneficiaries = computed(() => {
        return govBeneficiaries.value.filter(b => {
            const countriesArr = Array.isArray(props.countries) ? props.countries : props.countries.value;
            const filtersVal = props.filters.value || props.filters;

            const c = countriesArr?.find((x: any) => x.country === b.country);
            return (filtersVal.region === 'All' || c?.region === filtersVal.region) &&
                   (filtersVal.country === 'All Countries' || b.country === filtersVal.country);
        });
    });

    const filteredBatches = computed(() => {
        return govBatches.value.filter(b => {
            const countriesArr = Array.isArray(props.countries) ? props.countries : props.countries.value;
            const filtersVal = props.filters.value || props.filters;

            const c = countriesArr?.find((x: any) => x.country === b.country);
            return (filtersVal.region === 'All' || c?.region === filtersVal.region) &&
                   (filtersVal.country === 'All Countries' || b.country === filtersVal.country);
        });
    });

    // Calculations
    const govMonthlyPayouts = (p: any) => {
        return p.beneficiaries * (p.schedule === 'Monthly' ? 1 : p.schedule === 'Bi-Monthly' ? 0.5 : p.schedule === 'Weekly' ? 4 : 1);
    };

    const govFeePer = (amount: number) => {
        return govConfig.value.flat + Math.min(amount * govConfig.value.pct / 100, govConfig.value.cap);
    };

    const govDisbursementFeesMonthly = (list: any[]) => {
        return list.reduce((a, p) => a + govMonthlyPayouts(p) * govFeePer(p.avg), 0);
    };

    const govSaasMonthly = (list: any[]) => {
        const agencies = new Set(list.map(p => `${p.agency}|${p.country}`));
        return agencies.size * govConfig.value.saas;
    };

    const govFloatYieldMonthly = () => {
        return govFloat.value.balance * (govConfig.value.yield / 100) / 12 * (govConfig.value.split / 100);
    };

    const govPayoutVolMonthly = (list: any[]) => {
        return list.reduce((a, p) => a + govMonthlyPayouts(p) * p.avg, 0);
    };

    const govLinkupRevenue = computed(() => {
        const list = filteredPrograms.value;
        return (govDisbursementFeesMonthly(list) + govSaasMonthly(list) + govFloatYieldMonthly()) * props.getScale();
    });

    const govStats = computed(() => {
        const list = filteredPrograms.value;
        return {
            totalBeneficiaries: list.reduce((a, p) => a + p.beneficiaries, 0),
            totalDisbursed: govPayoutVolMonthly(list) * props.getScale(),
            revenue: govLinkupRevenue.value,
            floatBalance: govFloat.value.balance
        };
    });

    return {
        govPrograms,
        govBeneficiaries,
        govBatches,
        govConfig,
        govFloat,
        filteredPrograms,
        filteredBeneficiaries,
        filteredBatches,
        govStats,
        govMonthlyPayouts,
        govFeePer
    };
}
