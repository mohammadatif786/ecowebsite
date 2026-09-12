<script setup lang="ts">
import { computed } from 'vue';
import {
    Activity,
    Bell,
    ChartNoAxesCombined,
    CreditCard,
    Database,
    FileCheck2,
    Mail,
    MessageCircle,
    Radio,
    ShieldCheck,
    Ticket,
    Wallet,
} from 'lucide-vue-next';

type HealthStatus = 'Operational' | 'Monitor bandwidth';

type ServiceHealth = {
    name: string;
    status: HealthStatus;
    detail: string;
    uptime: string;
    icon: any;
};

const services: ServiceHealth[] = [
    { name: 'API Status', status: 'Operational', detail: 'Core endpoints responding normally', uptime: '99.99%', icon: Activity },
    { name: 'Database', status: 'Operational', detail: 'Primary and replica clusters healthy', uptime: '99.98%', icon: Database },
    { name: 'Wallet Rail', status: 'Operational', detail: 'Ledger writes and balance checks stable', uptime: '99.99%', icon: Wallet },
    { name: 'Payment Gateway', status: 'Operational', detail: 'Card, QR, and merchant payments live', uptime: '99.97%', icon: CreditCard },
    { name: 'Email', status: 'Operational', detail: 'Transactional and campaign mail flowing', uptime: '99.80%', icon: Mail },
    { name: 'SMS / WhatsApp', status: 'Operational', detail: 'OTP and notification queues clear', uptime: '99.70%', icon: MessageCircle },
    { name: 'Push Notifications', status: 'Operational', detail: 'Mobile push delivery is within SLA', uptime: '99.90%', icon: Bell },
    { name: 'Live Streaming', status: 'Monitor bandwidth', detail: 'Bandwidth usage elevated during live events', uptime: '98.90%', icon: Radio },
    { name: 'Ticketing', status: 'Operational', detail: 'Event scans and QR validation online', uptime: '99.95%', icon: Ticket },
    { name: 'KYC', status: 'Operational', detail: 'Identity checks and review queues active', uptime: '99.60%', icon: FileCheck2 },
    { name: 'Fraud Scoring', status: 'Operational', detail: 'Rules engine scoring transactions', uptime: '99.92%', icon: ShieldCheck },
    { name: 'Reporting', status: 'Operational', detail: 'Dashboards and exports available', uptime: '99.95%', icon: ChartNoAxesCombined },
];

const summary = computed(() => {
    const monitored = services.filter((service) => service.status !== 'Operational').length;
    return {
        total: services.length,
        operational: services.length - monitored,
        monitored,
    };
});

const cardClass = (status: HealthStatus) =>
    status === 'Operational'
        ? 'border-green-100 bg-green-50 text-green-700'
        : 'border-amber-100 bg-amber-50 text-amber-700';

const iconClass = (status: HealthStatus) =>
    status === 'Operational'
        ? 'bg-green-100 text-green-700'
        : 'bg-amber-100 text-amber-700';
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h3 class="text-3xl font-black text-slate-950">Operations</h3>
                <p class="mt-1 text-slate-500">Operational health for core LinkUp services, rails, messaging, compliance, and reporting.</p>
            </div>

            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <p class="text-xs font-black uppercase text-slate-500">Services</p>
                    <b class="text-xl text-slate-950">{{ summary.total }}</b>
                </div>
                <div class="rounded-2xl border border-green-100 bg-green-50 px-4 py-3 shadow-sm">
                    <p class="text-xs font-black uppercase text-green-700">Operational</p>
                    <b class="text-xl text-green-700">{{ summary.operational }}</b>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 shadow-sm">
                    <p class="text-xs font-black uppercase text-amber-700">Monitor</p>
                    <b class="text-xl text-amber-700">{{ summary.monitored }}</b>
                </div>
            </div>
        </div>

        <section class="card rounded-3xl p-6">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-black text-slate-950">Operational Health</h3>
                    <p class="text-sm text-slate-500">Current platform service status by operational area.</p>
                </div>
                <span class="rounded-full bg-slate-950 px-3 py-1.5 text-xs font-black text-white">Live</span>
            </div>

            <div id="opsGrid" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="service in services"
                    :key="service.name"
                    class="rounded-2xl border p-4"
                    :class="cardClass(service.status)"
                >
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div class="grid h-10 w-10 place-items-center rounded-2xl" :class="iconClass(service.status)">
                            <component :is="service.icon" class="h-5 w-5" />
                        </div>
                        <span class="rounded-full bg-white/80 px-2.5 py-1 text-xs font-black">{{ service.uptime }}</span>
                    </div>

                    <b class="block text-slate-950">{{ service.name }}</b>
                    <p class="mt-1 text-sm font-black">{{ service.status }}</p>
                    <p class="mt-2 text-sm text-slate-600">{{ service.detail }}</p>
                </div>
            </div>
        </section>
    </div>
</template>
