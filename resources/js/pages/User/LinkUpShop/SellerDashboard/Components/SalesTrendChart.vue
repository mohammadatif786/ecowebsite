<script setup lang="ts">
import { computed } from 'vue';

interface DataPoint { date: string; revenue: number; orders: number }

const props = defineProps<{ data: DataPoint[]; days: number }>();

const sorted = computed(() => [...props.data].sort((a, b) => a.date.localeCompare(b.date)));

const maxRevenue = computed(() => Math.max(...sorted.value.map(d => d.revenue), 1));

const W = 800; const H = 220; const PAD = 40;
const plotW = computed(() => W - PAD * 2);
const plotH = computed(() => H - PAD * 2);

const points = computed(() =>
    sorted.value.map((d, i) => ({
        x: PAD + (i / Math.max(sorted.value.length - 1, 1)) * plotW.value,
        y: PAD + plotH.value - (d.revenue / maxRevenue.value) * plotH.value,
        ...d,
    }))
);

const pathD = computed(() => {
    if (!points.value.length) return '';
    return points.value.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x.toFixed(1)} ${p.y.toFixed(1)}`).join(' ');
});

const areaD = computed(() => {
    if (!points.value.length) return '';
    const base = PAD + plotH.value;
    return `${pathD.value} L ${points.value.at(-1)!.x.toFixed(1)} ${base} L ${PAD} ${base} Z`;
});

const formatCurrency = (v: number) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(v);

const tooltip = { index: -1 } as any;
</script>

<template>
    <div class="chart-wrap">
        <svg :viewBox="`0 0 ${W} ${H}`" class="chart-svg" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%"   stop-color="#0ea5e9" stop-opacity=".35"/>
                    <stop offset="100%" stop-color="#22c55e" stop-opacity=".03"/>
                </linearGradient>
                <linearGradient id="lineGrad" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%"   stop-color="#0ea5e9"/>
                    <stop offset="100%" stop-color="#22c55e"/>
                </linearGradient>
            </defs>

            <!-- Grid lines -->
            <g>
                <line v-for="n in 4" :key="n"
                    :x1="PAD" :x2="W - PAD"
                    :y1="PAD + (plotH / 4) * (n-1)" :y2="PAD + (plotH / 4) * (n-1)"
                    stroke="rgba(148,163,184,.2)" stroke-dasharray="4 4"/>
            </g>

            <!-- Area fill -->
            <path v-if="areaD" :d="areaD" fill="url(#areaGrad)"/>

            <!-- Line -->
            <path v-if="pathD" :d="pathD" fill="none" stroke="url(#lineGrad)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>

            <!-- Dots -->
            <g v-for="p in points" :key="p.date">
                <circle :cx="p.x" :cy="p.y" r="4" fill="#fff" stroke="#0ea5e9" stroke-width="2"/>
                <title>{{ p.date }}: {{ formatCurrency(p.revenue) }} ({{ p.orders }} orders)</title>
            </g>

            <!-- X-axis labels (sample every N) -->
            <g v-for="(p, i) in points" :key="'lbl-'+i">
                <text v-if="i % Math.max(1, Math.floor(points.length / 6)) === 0"
                    :x="p.x" :y="H - 8"
                    text-anchor="middle" font-size="11" fill="#94a3b8">
                    {{ p.date.slice(5) }}
                </text>
            </g>
        </svg>

        <div v-if="!data.length" class="chart-empty">
            No sales data for this period yet.
        </div>
    </div>
</template>

<style scoped>
.chart-wrap { position: relative; width: 100%; }
.chart-svg  { width: 100%; height: auto; display: block; }
.chart-empty {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    color: #94a3b8; font-size: .9rem; font-weight: 600;
}
</style>
