<script setup lang="ts">
defineProps<{
    label: string;
    value: string | number;
    sub?: string;
    icon: string;
    gradient: string;
    trend?: number | null; // positive = up, negative = down
}>();
</script>

<template>
    <div class="stat-card card p-5 flex items-start gap-4">
        <!-- <div class="stat-icon" :style="`background: ${gradient}`">
            <span>{{ icon }}</span>
        </div> -->
        <div class="flex-1 min-w-0">
            <div class="stat-label">{{ label }}</div>
            <div class="stat-value">{{ value }}</div>
            <div v-if="sub" class="stat-sub">{{ sub }}</div>
            <div v-if="trend !== null && trend !== undefined" class="stat-trend" :class="trend >= 0 ? 'trend-up' : 'trend-down'">
                {{ trend >= 0 ? '▲' : '▼' }} {{ Math.abs(trend) }}% vs last period
            </div>
        </div>
    </div>
</template>

<style scoped>
.stat-card {
    background: #ffffff;
    border-radius: 22px;
    box-shadow: 0 8px 32px rgba(2, 6, 23, .08);
    border: 1px solid rgba(148, 163, 184, .25);
    transition: transform .2s, box-shadow .2s;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 40px rgba(2, 6, 23, .12);
}
.stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}
.stat-label {
    font-size: .78rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 2px;
}
.stat-value {
    font-size: 1.6rem;
    font-weight: 900;
    color: #0f172a;
    line-height: 1.1;
}
.stat-sub {
    font-size: .75rem;
    color: #94a3b8;
    margin-top: 2px;
}
.stat-trend {
    font-size: .72rem;
    font-weight: 800;
    margin-top: 4px;
    border-radius: 999px;
    padding: 2px 8px;
    display: inline-block;
}
.trend-up   { background: rgba(34,197,94,.12); color: #15803d; }
.trend-down { background: rgba(239,68,68,.12);  color: #b91c1c; }
</style>
