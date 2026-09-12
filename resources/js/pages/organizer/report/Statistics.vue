<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import {
    BarChart2,
    Download,
    Search,
    Calendar,
    Filter,
    TrendingUp,
    Users,
    DollarSign,
    MoreHorizontal,
    ChevronRight,
    Loader2,
    Info,
    RotateCcw
} from 'lucide-vue-next';
import { Chart } from 'chart.js/auto';

interface EventStats {
  id: number;
  slug: string;
  title: string;
  event_date: string;
  location?: string;
  status: string;
  total_tickets_sold: number;
  total_revenue: number;
  checked_in_count: number;
}

interface Statistics {
  summary: {
    total_events: number;
    total_tickets: number;
    total_revenue: number;
    avg_revenue_per_event: number;
  };
  events: EventStats[];
  charts: {
    revenue_over_time: any[];
    status_breakdown: Record<string, number>;
    payment_methods: Record<string, number>;
    top_events: any[];
  };
}

const props = defineProps<{
  statistics: Statistics;
  events: any[]; // List of all events for filter
  initialFilters?: {
    event_id?: string;
    from_date?: string;
    to_date?: string;
    status?: string;
    payment_methods?: string[];
    sort_by?: string;
  };
}>();

// State
const loading = ref(false);
const statistics = ref<Statistics>(props.statistics);
const allEventsList = ref(props.events || []);

const filters = ref({
  event_id: props.initialFilters?.event_id || '',
  from_date: props.initialFilters?.from_date || '',
  to_date: props.initialFilters?.to_date || '',
  status: props.initialFilters?.status || '',
  sort_by: props.initialFilters?.sort_by || 'event_date_desc'
});

// Chart references
const revenueChart = ref<HTMLCanvasElement>();
const statusChart = ref<HTMLCanvasElement>();

// Chart instances
let revenueChartInstance: Chart | null = null;
let statusChartInstance: Chart | null = null;

// Methods
const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0);
};

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US').format(num || 0);
};

const formatEventDate = (dateString: string) => {
  if (!dateString) return 'TBD';
  const d = new Date(dateString);
  return d.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const applyFilters = async () => {
  loading.value = true;
  try {
    await router.get(route('organizer.report.statistics'), filters.value, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: (page) => {
        statistics.value = page.props.statistics as Statistics;
        nextTick(() => {
          initializeCharts();
        });
      },
      onFinish: () => {
        loading.value = false;
      }
    });
  } catch (error) {
    toast.error('Failed to load statistics');
    loading.value = false;
  }
};

const resetFilters = () => {
  filters.value = {
    event_id: '',
    from_date: '',
    to_date: '',
    status: '',
    sort_by: 'event_date_desc'
  };
  applyFilters();
};

const exportReport = () => {
  const params = new URLSearchParams();
  Object.entries(filters.value).forEach(([key, value]) => {
    if (value) params.append(key, value.toString());
  });

  window.open(route('organizer.report.export') + '?' + params.toString());
  toast.success('✅ Report export started (CSV)');
};

const initializeCharts = () => {
  /* Graphs commented out to match HTML prototype (line 4903)
  if (revenueChartInstance) revenueChartInstance.destroy();
  if (statusChartInstance) statusChartInstance.destroy();

  if (!statistics.value.charts) return;

  // Revenue Over Time
  if (revenueChart.value) {
    const ctx = revenueChart.value.getContext('2d');
    if (ctx) {
      revenueChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: statistics.value.charts.revenue_over_time.map((item: any) => item.date),
          datasets: [{
            label: 'Revenue',
            data: statistics.value.charts.revenue_over_time.map((item: any) => item.revenue),
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: '#fff',
            pointBorderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#f1f5f9' },
                ticks: { font: { weight: 'bold' } }
            },
            x: { grid: { display: false } }
          }
        }
      });
    }
  }

  // Status Breakdown
  if (statusChart.value) {
    const ctx = statusChart.value.getContext('2d');
    if (ctx) {
      statusChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: Object.keys(statistics.value.charts.status_breakdown),
          datasets: [{
            data: Object.values(statistics.value.charts.status_breakdown),
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#3b82f6'],
            borderWidth: 0,
            hoverOffset: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
                position: 'bottom',
                labels: { boxWidth: 12, usePointStyle: true, font: { weight: 'bold', size: 11 } }
            }
          },
          cutout: '70%'
        }
      });
    }
  }
  */
};

onMounted(() => {
  nextTick(() => {
    initializeCharts();
  });
});
</script>

<template>
    <Head title="My Reports" />

    <AppLayout>
        <!-- Section Header matching reference line 4914 -->
        <div class="rounded-3xl overflow-hidden mb-6" style="background:linear-gradient(120deg,#2563eb,#0ea5e9)">
            <div class="p-5 flex items-center justify-between flex-wrap gap-3 text-white">
                <div class="flex items-center gap-4">
                    <span class="h-12 w-12 rounded-2xl bg-white/20 grid place-items-center shrink-0">
                        <BarChart2 class="w-6 h-6" />
                    </span>
                    <div>
                        <p class="text-xl font-black leading-tight">My Reports</p>
                        <p class="text-white/80 text-[13px] font-bold mt-0.5">Comprehensive analytics for all your events</p>
                    </div>
                </div>
                <button @click="exportReport"
                    class="btn bg-white text-blue-600 px-6 py-2.5 rounded-full font-black text-sm flex items-center gap-2 hover:bg-blue-50 transition shadow-lg shadow-blue-900/20 active:scale-95 shrink-0">
                    <Download class="w-4 h-4" />
                    Export Report
                </button>
            </div>
        </div>

        <div class="space-y-6 mb-6">
            <!-- Summary Overview Cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="card p-5 bg-white border border-slate-100 rounded-[24px] shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Revenue</p>
                    <p class="text-2xl font-black text-slate-800">{{ formatCurrency(statistics.summary.total_revenue) }}</p>
                    <div class="flex items-center gap-1 mt-2 text-emerald-500 font-bold text-[10px]">
                        <TrendingUp class="w-3 h-3" />
                        All-time gross
                    </div>
                </div>
                <div class="card p-5 bg-white border border-slate-100 rounded-[24px] shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tickets Sold</p>
                    <p class="text-2xl font-black text-slate-800">{{ formatNumber(statistics.summary.total_tickets) }}</p>
                    <div class="flex items-center gap-1 mt-2 text-blue-500 font-bold text-[10px]">
                        <Users class="w-3 h-3" />
                        Attendees
                    </div>
                </div>
                <div class="card p-5 bg-white border border-slate-100 rounded-[24px] shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Avg per Event</p>
                    <p class="text-2xl font-black text-slate-800">{{ formatCurrency(statistics.summary.avg_revenue_per_event) }}</p>
                    <div class="flex items-center gap-1 mt-2 text-indigo-500 font-bold text-[10px]">
                        <BarChart2 class="w-3 h-3" />
                        Performance
                    </div>
                </div>
            </div>

            <!-- Filters Card matching reference line 4917 -->
            <div class="card p-6 bg-white border border-slate-100 rounded-[24px] shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <p class="font-black text-lg text-slate-800 flex items-center gap-2">
                        <Filter class="w-5 h-5 text-blue-500" />
                        Filters
                    </p>
                    <button @click="resetFilters" class="text-[11px] font-black text-slate-400 hover:text-rose-500 uppercase flex items-center gap-1.5 transition-colors">
                        <RotateCcw class="w-3 h-3" />
                        Reset Filters
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Event</label>
                        <select v-model="filters.event_id" @change="applyFilters"
                            class="w-full rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-xs font-bold outline-none focus:border-blue-400 transition">
                            <option value="">All Events</option>
                            <option v-for="ev in allEventsList" :key="ev.id" :value="ev.id">{{ ev.title }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-1">From Date</label>
                        <input type="date" v-model="filters.from_date" @change="applyFilters"
                            class="w-full rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-xs font-bold outline-none focus:border-blue-400 transition" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-1">To Date</label>
                        <input type="date" v-model="filters.to_date" @change="applyFilters"
                            class="w-full rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-xs font-bold outline-none focus:border-blue-400 transition" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Status</label>
                        <select v-model="filters.status" @change="applyFilters"
                            class="w-full rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-xs font-bold outline-none focus:border-blue-400 transition">
                            <option value="">All Status</option>
                            <option value="published">Live</option>
                            <option value="draft">Draft</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3 border-t border-slate-50 pt-4">
                    <div class="flex-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-1 mb-1 block">Sort Performance By</label>
                        <select v-model="filters.sort_by" @change="applyFilters"
                            class="max-w-[240px] rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-xs font-bold outline-none focus:border-blue-400 transition">
                            <option value="event_date_desc">Event Date (Latest)</option>
                            <option value="event_date_asc">Event Date (Earliest)</option>
                            <option value="revenue_desc">Revenue (Highest)</option>
                            <option value="tickets_desc">Tickets Sold (Most)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Event Details Table matching reference line 4930 -->
            <div class="card bg-white border border-slate-100 rounded-[24px] shadow-sm overflow-hidden relative">
                <div v-if="loading" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 grid place-items-center">
                    <Loader2 class="w-8 h-8 text-blue-500 animate-spin" />
                </div>

                <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-800">Event Details</h2>
                        <p class="text-[11px] text-slate-400 font-bold uppercase">{{ statistics.events.length }} event(s) found</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] font-black text-slate-400 uppercase border-b border-slate-100 bg-slate-50/30">
                                <th class="px-6 py-4">Event</th>
                                <th class="px-4 py-4">Date</th>
                                <th class="px-4 py-4 text-center">Status</th>
                                <th class="px-4 py-4 text-center">Sold</th>
                                <th class="px-4 py-4">Revenue</th>
                                <th class="px-4 py-4">Check-ins</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="event in statistics.events" :key="event.id" class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <p class="font-black text-slate-800 leading-tight">{{ event.title }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold mt-0.5 truncate max-w-[200px]">{{ event.location || 'Online' }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-xs font-bold text-slate-600">{{ formatEventDate(event.event_date) }}</p>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider"
                                        :class="event.status === 'published' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600'">
                                        {{ event.status === 'published' ? 'Live' : event.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center font-black text-slate-700">
                                    {{ formatNumber(event.total_tickets_sold) }}
                                </td>
                                <td class="px-4 py-4 font-black text-emerald-600">
                                    {{ formatCurrency(event.total_revenue) }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-700">{{ event.checked_in_count }} / {{ event.total_tickets_sold }}</span>
                                        <span class="text-[10px] text-slate-400 font-black">{{ Math.round((event.checked_in_count / (event.total_tickets_sold || 1)) * 100) }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <Link :href="route('organizer.event.report.statistics', event.slug)" class="text-[#004aad] font-bold text-sm hover:underline">
                                            View Details
                                        </Link>
                                        <Link :href="route('organizer.event.report.attendees', event.slug)" class="text-emerald-600 font-bold text-sm hover:underline">
                                            Attendees
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!statistics.events.length">
                                <td colspan="7" class="px-6 py-20 text-center">
                                    <p class="text-slate-400 font-black italic">No events match these filters.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- GRAPHS SECTION (Commented out to match HTML prototype)
        <div class="grid lg:grid-cols-2 gap-6 mt-6">
            <div class="card p-6 bg-white border border-slate-100 rounded-[28px] shadow-sm">
                <p class="font-black text-lg text-slate-800 mb-6 flex items-center gap-2">
                    <TrendingUp class="w-5 h-5 text-emerald-500" />
                    Revenue Trend
                </p>
                <div class="h-[240px] relative">
                    <canvas ref="revenueChart"></canvas>
                </div>
            </div>

            <div class="card p-6 bg-white border border-slate-100 rounded-[28px] shadow-sm">
                <p class="font-black text-lg text-slate-800 mb-6 flex items-center gap-2">
                    <RotateCcw class="w-5 h-5 text-indigo-500" />
                    Sale Status
                </p>
                <div class="h-[200px] relative">
                    <canvas ref="statusChart"></canvas>
                </div>
            </div>
        </div>
        -->

    </AppLayout>
</template>

<style scoped>
/* Animations */
.animate-in {
    animation-duration: 0.4s;
    animation-fill-mode: both;
}
.fade-in {
    animation-name: fadeIn;
}
.slide-in-from-right-4 {
    animation-name: slideInFromRight;
}
.slide-in-from-top-2 {
    animation-name: slideInFromTop;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInFromRight {
    from { transform: translateX(1rem); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideInFromTop {
    from { transform: translateY(-0.5rem); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Custom Scrollbar for Table */
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
</style>
