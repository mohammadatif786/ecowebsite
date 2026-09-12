<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import '@/../../resources/css/new_admin.css';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
    title: string;
    activeId: string;
    initialUnits: any[];
    initialCountries: any[];
}>();

const sidebarVisible = ref(true);
const filters = ref({ region: 'All', country: 'All Countries', period: 'Monthly' });

const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(Number(n || 0));
const num = (n: number) => new Intl.NumberFormat('en-US').format(Math.round(Number(n || 0)));

const scale = computed(() => {
    const period = filters.value.period;
    if (period === 'Today') return 1 / 30;
    if (period === 'Weekly') return 0.25;
    if (period === 'Quarterly') return 3;
    if (period === 'Yearly') return 12;
    if (period === '5-Year') return 60;
    return 1;
});

const filteredCountries = computed(() => props.initialCountries.filter((country) => {
    return (filters.value.region === 'All' || country.region === filters.value.region)
        && (filters.value.country === 'All Countries' || country.country === filters.value.country);
}));

const ribbonMetrics = computed(() => {
    const totals = filteredCountries.value.reduce((carry, country) => {
        props.initialUnits.forEach((unit) => {
            carry.gtv += Number(country[unit.key] || 0);
        });
        carry.users += Number(country.users || 0);
        carry.merchants += Number(country.merchants || 0);
        carry.organizers += Number(country.organizers || 0);
        return carry;
    }, { gtv: 0, users: 0, merchants: 0, organizers: 0 });

    const gtv = totals.gtv * scale.value;
    return {
        gtv: fmt(gtv),
        linkupRev: fmt(gtv * 0.12),
        procPool: fmt(gtv * 0.03),
        netProfit: fmt(gtv * 0.09),
        users: num(totals.users * scale.value),
        merchants: num(totals.merchants),
        organizers: num(totals.organizers),
        countries: num(filteredCountries.value.length),
    };
});

const handleFilterChange = (nextFilters: any) => {
    filters.value = nextFilters;
};

onMounted(() => document.body.classList.add('new-admin-body'));
onUnmounted(() => document.body.classList.remove('new-admin-body'));

defineExpose({ fmt, num, scale, filteredCountries });
</script>

<template>
    <Head :title="title" />
    <div class="flex min-h-screen">
        <NewAppSidebar :active-id="activeId" v-show="sidebarVisible" />
        <main class="flex-1 overflow-x-hidden transition-all duration-300">
            <NewAppHeader
                :title="title"
                :countries="initialCountries"
                :metrics="ribbonMetrics"
                @toggle-sidebar="toggleSidebar"
                @filter-change="handleFilterChange"
            />
            <section class="p-5 lg:p-8">
                <slot :fmt="fmt" :num="num" :scale="scale" :filtered-countries="filteredCountries" />
            </section>
        </main>
    </div>
</template>