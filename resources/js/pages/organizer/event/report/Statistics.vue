<script setup lang="ts">
import { computed } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import {
    BarChart2,
    ArrowLeft,
    Download,
    Users,
    DollarSign,
    CheckCircle2,
    TrendingUp,
    Megaphone,
    Pencil
} from "lucide-vue-next";
import dayjs from 'dayjs';

const props = defineProps<{
  event: any;
  statistics: {
    total_sales: number;
    total_revenue: number;
    status_breakdown: Record<string, number>;
    payment_methods: Record<string, number>;
    daily_sales: Record<string, number>;
    checked_in_count: number;
    pending_count: number;
    average_ticket_price: number;
  };
}>();

const attendanceRate = computed(() => {
  if (!props.statistics.total_sales) return 0;
  return Math.round((props.statistics.checked_in_count / props.statistics.total_sales) * 100);
});

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0);
};

const formatDate = (dateString: string) => {
  if (!dateString) return '—';
  return dayjs(dateString).format('MMM D, YYYY');
};

const exportReport = () => {
  window.open(route('organizer.event.report.export', props.event.slug));
  toast.success('✅ Report exported (CSV)');
};

const sendEventUpdate = () => {
  toast.info('Broadcast functionality is available on the Attendees page');
};
</script>

<template>
    <Head title="Event Statistics" />

    <AppLayout>
        <!-- Top Navigation matching reference line 4571 -->
        <div class="flex items-center justify-between mb-4">
            <Link :href="route('organizer.report.statistics')" class="h-9 w-9 rounded-lg border border-slate-200 grid place-items-center hover:bg-slate-50 transition shadow-sm">
                <ArrowLeft class="w-4 h-4 text-slate-600" />
            </Link>
            <div class="flex gap-2">
                <Link :href="route('organizer.event.report.attendees', event?.slug)" class="btn btn-ghost px-4 py-2 text-sm font-black text-slate-600 hover:bg-slate-100 transition rounded-xl">
                    View Attendees
                </Link>
                <button @click="exportReport" class="btn bg-indigo-600 text-white px-5 py-2 text-sm font-black rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/20">
                    Export Report
                </button>
            </div>
        </div>

        <!-- Section Header matching reference line 4575 -->
        <div class="rounded-3xl overflow-hidden mb-6" style="background:linear-gradient(120deg,#2563eb,#7c3aed)">
            <div class="p-6 flex items-center justify-between flex-wrap gap-3 text-white">
                <div class="flex items-center gap-4">
                    <span class="h-12 w-12 rounded-2xl bg-white/20 grid place-items-center shrink-0">
                        <BarChart2 class="w-6 h-6" />
                    </span>
                    <div>
                        <p class="text-xl font-black leading-tight">{{ event?.title }}</p>
                        <p class="text-white/80 text-[13px] font-bold mt-0.5 uppercase tracking-wider">Event Statistics & Performance</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat Cards Grid (Sync line 4576) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            <div class="card p-5 bg-white border border-slate-100 rounded-[20px] shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Sales</p>
                <p class="text-2xl font-black text-slate-800">{{ statistics.total_sales }}</p>
                <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase">Tickets sold</p>
            </div>
            <div class="card p-5 bg-white border border-slate-100 rounded-[20px] shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Revenue</p>
                <p class="text-2xl font-black text-emerald-600">{{ formatCurrency(statistics.total_revenue) }}</p>
                <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase">Gross earnings</p>
            </div>
            <div class="card p-5 bg-white border border-slate-100 rounded-[20px] shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Checked In</p>
                <p class="text-2xl font-black text-indigo-600">{{ statistics.checked_in_count }}</p>
                <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase">At the door</p>
            </div>
            <div class="card p-5 bg-white border border-slate-100 rounded-[20px] shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Avg. Ticket Price</p>
                <p class="text-2xl font-black text-slate-800">{{ formatCurrency(statistics.average_ticket_price) }}</p>
                <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase">Per ticket</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <!-- Status Breakdown -->
            <div class="card p-6 bg-white border border-slate-100 rounded-[24px] shadow-sm">
                <p class="font-black text-lg text-slate-800 mb-4 flex items-center gap-2">
                    <TrendingUp class="w-5 h-5 text-indigo-500" />
                    Ticket Status Breakdown
                </p>
                <div class="space-y-3">
                    <div v-for="(count, status) in statistics.status_breakdown" :key="status"
                        class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="h-2.5 w-2.5 rounded-sm" :class="status === 'confirmed' ? 'bg-emerald-500' : 'bg-slate-400'"></div>
                            <span class="text-sm font-bold text-slate-600 capitalize">{{ status }}</span>
                        </div>
                        <b class="text-slate-800 text-lg font-black">{{ count }}</b>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="card p-6 bg-white border border-slate-100 rounded-[24px] shadow-sm">
                <p class="font-black text-lg text-slate-800 mb-4 flex items-center gap-2">
                    <CreditCard class="w-5 h-5 text-violet-500" />
                    Payment Methods
                </p>
                <div class="space-y-3">
                    <div v-for="(count, method) in statistics.payment_methods" :key="method"
                        class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="h-2.5 w-2.5 rounded-sm bg-violet-500"></div>
                            <span class="text-sm font-bold text-slate-600 capitalize">{{ method || 'Wallet' }}</span>
                        </div>
                        <b class="text-slate-800 text-lg font-black">{{ count }}</b>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Summary List -->
            <div class="card p-6 bg-white border border-slate-100 rounded-[24px] shadow-sm">
                <p class="font-black text-lg text-slate-800 mb-4 flex items-center gap-2">
                    <CheckCircle2 class="w-5 h-5 text-emerald-500" />
                    Summary
                </p>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-1">
                        <span class="text-sm font-bold text-slate-500 uppercase tracking-tighter">Attendance Rate</span>
                        <b class="text-lg font-black text-slate-800">{{ attendanceRate }}%</b>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-sm font-bold text-slate-500 uppercase tracking-tighter">Pending Payments</span>
                        <b class="text-lg font-black text-amber-500">{{ statistics.pending_count || 0 }}</b>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-sm font-bold text-slate-500 uppercase tracking-tighter">Total Tickets Sold</span>
                        <b class="text-lg font-black text-slate-800">{{ statistics.total_sales }}</b>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card p-6 bg-white border border-slate-100 rounded-[24px] shadow-sm">
                <p class="font-black text-lg text-slate-800 mb-4 flex items-center gap-2">
                    <TrendingUp class="w-5 h-5 text-indigo-500" />
                    Quick Actions
                </p>
                <div class="grid grid-cols-1 gap-3">
                    <Link :href="route('organizer.event.report.attendees', event?.slug)"
                          class="w-full btn btn-ghost py-3 rounded-2xl text-sm font-black flex items-center justify-center gap-2 border border-slate-100 hover:bg-slate-50 transition">
                        <Users class="w-4 h-4" /> Manage Attendees
                    </Link>
                    <Link :href="route('organizer.event.edit', event?.slug)"
                          class="w-full btn bg-slate-900 text-white py-3 rounded-2xl text-sm font-black flex items-center justify-center gap-2 transition hover:bg-black">
                        <Pencil class="w-4 h-4" /> Edit Event
                    </Link>
                    <button @click="sendEventUpdate"
                            class="w-full btn btn-ghost py-3 rounded-2xl text-sm font-black flex items-center justify-center gap-2 border border-slate-100 hover:bg-slate-50 transition">
                        <Megaphone class="w-4 h-4" /> Send Update to All
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<style scoped>
.card { background: #fff; }
</style>
