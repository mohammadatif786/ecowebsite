<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { DollarSign, Globe2, MapPin, Users } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model from Backend
const SCOTIA_SHARE = 0.4;
const units = ref(props.initialUnits);
const countries = ref(props.initialCountries);

const filters = ref({
    region: 'All',
    country: 'All Countries',
    period: 'Monthly',
});

// Map Constants & Data Structures
const MAP_COORDS: Record<string, [number, number]> = {
    'Bahamas': [25.0, -77.4],
    'Jamaica': [18.1, -77.3],
    'Trinidad & Tobago': [10.7, -61.2],
    'Barbados': [13.2, -59.5],
    'Guyana': [4.9, -58.9],
    'Dominican Republic': [18.7, -70.2],
    'United States': [39.8, -98.6],
    'Canada': [56.1, -106.3],
    'Brazil': [-14.2, -51.9],
    'Colombia': [4.6, -74.1],
    'Mexico': [23.6, -102.5],
    'Panama': [8.5, -80.1],
    'Haiti': [19.0, -72.3],
    'Saint Lucia': [13.9, -60.98],
    'Grenada': [12.1, -61.7]
};

const MAP_NAME2OURS: Record<string, string> = {
    'United States of America': 'United States',
    'United States': 'United States',
    'Bahamas': 'Bahamas',
    'The Bahamas': 'Bahamas',
    'Jamaica': 'Jamaica',
    'Trinidad and Tobago': 'Trinidad & Tobago',
    'Dominican Republic': 'Dominican Republic',
    'Guyana': 'Guyana',
    'Brazil': 'Brazil',
    'Colombia': 'Colombia',
    'Canada': 'Canada',
    'Barbados': 'Barbados',
    'Haiti': 'Haiti',
    'Mexico': 'Mexico',
    'Panama': 'Panama'
};

const GEO_REGION: Record<string, string> = {
    'United States': 'North America',
    'Canada': 'North America',
    'Mexico': 'North America',
    'Bahamas': 'Caribbean',
    'Jamaica': 'Caribbean',
    'Trinidad & Tobago': 'Caribbean',
    'Barbados': 'Caribbean',
    'Dominican Republic': 'Caribbean',
    'Haiti': 'Caribbean',
    'Brazil': 'South America',
    'Colombia': 'South America',
    'Guyana': 'South America',
    'Panama': 'Central America'
};

const GEO_COLOR: Record<string, string> = {
    'North America': '#3b82f6',
    'Caribbean': '#10b981',
    'South America': '#8b5cf6',
    'Central America': '#f59e0b'
};

const handleFilterChange = (newFilters: any) => {
    filters.value = newFilters;
    renderAll();
};

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
        const v = (Number(c[u.key]) || 0) * getScale();
        gross += v;
        platform += v * u.platformRate;
        bank += v * u.bankRate;
        cost += v * u.costRate;
    });
    const topUnit = units.value.map((u) => ({ name: u.name, val: (Number(c[u.key]) || 0) * getScale() })).sort((a, b) => b.val - a.val)[0];
    const top = topUnit ? topUnit.name : 'N/A';
    return { gross, platform, bank, cost, net: platform - cost, top };
};

const getTotals = () => {
    const rs = getFilteredCountries();
    const fins = rs.map(countryFin);
    return {
        gross: fins.reduce((s, x) => s + x.gross, 0),
        platform: fins.reduce((s, x) => s + x.platform, 0),
        bank: fins.reduce((s, x) => s + x.bank, 0),
        cost: fins.reduce((s, x) => s + x.cost, 0),
        net: fins.reduce((s, x) => s + x.net, 0),
        users: rs.reduce((s, c) => s + (Number(c.users) || 0), 0) * getScale(),
        merchants: rs.reduce((s, c) => s + (Number(c.merchants) || 0), 0),
        organizers: rs.reduce((s, c) => s + (Number(c.organizers) || 0), 0),
        countries: rs.length,
    };
};

const mapMetric = ref<'revenue' | 'users'>('revenue');
const setMapMetric = (m: 'revenue' | 'users') => {
    mapMetric.value = m;
    renderAll();
};

// Map State
const lkMap = ref<any>(null);
const lkLayer = ref<any>(null);
const lkGeo = ref<any>(null);
const lkMarkers = ref<any[]>([]);
const lkLeaderMarker = ref<any>(null);
const lkViewerDot = ref<any>(null);
const lkViewerMarker = ref<any>(null);
const viewerLoc = ref<any>(null);

const ourCountryRow = (name: string) => {
    const oc = MAP_NAME2OURS[name];
    if (!oc) return null;
    return countries.value.find((c) => c.country === oc) || null;
};

const mapMetricVal = (row: any) => {
    return mapMetric.value === 'users' ? Math.round((Number(row.users) || 0) * getScale()) : Math.round(countryFin(row).gross);
};

const getMapFilteredCountries = () => {
    return countries.value.filter((c) => filters.value.region === 'All' || c.region === filters.value.region);
};

const getMapMax = () => {
    return Math.max(...getMapFilteredCountries().map(mapMetricVal), 1);
};

const getMapLeaderCountry = () => {
    let best = null, bv = -1;
    getMapFilteredCountries().forEach((c) => {
        const v = mapMetricVal(c);
        if (v > bv) {
            bv = v;
            best = c.country;
        }
    });
    return best;
};

const lkStyle = (feature: any) => {
    const row = ourCountryRow(feature.properties.name);
    if (!row) return { fillColor: '#13304d', fillOpacity: 0.4, color: '#1f3b57', weight: 0.5 };

    const reg = GEO_REGION[row.country] || 'Caribbean';
    const sel = filters.value.country === row.country;
    const leader = row.country === getMapLeaderCountry();
    const intensity = 0.35 + 0.6 * (mapMetricVal(row) / getMapMax());

    return {
        fillColor: GEO_COLOR[reg] || '#10b981',
        fillOpacity: Math.min(0.97, intensity),
        color: leader ? '#facc15' : (sel ? '#ffffff' : '#06121f'),
        weight: leader ? 3.5 : (sel ? 2.5 : 1)
    };
};

const lkTipHtml = (row: any) => {
    const fin = countryFin(row);
    const leader = row.country === getMapLeaderCountry();
    const region = GEO_REGION[row.country] || '';
    const metricLabel = mapMetric.value === 'users' ? 'by users' : 'by revenue';

    return `
        <div style="font-weight:900;font-size:14px">${leader ? '👑 ' : ''}${row.country}</div>
        <div style="opacity:.65;font-size:11px;margin-bottom:4px">${region}${leader ? ' • Top ' + metricLabel : ''}</div>
        GTV <b>${fmt(fin.gross)}</b><br>
        LinkUp Rev. <b>${fmt(fin.platform)}</b><br>
        Users <b>${num((Number(row.users) || 0) * getScale())}</b>
        <div style="font-size:10px;opacity:.6;margin-top:3px">Click to filter the platform</div>
    `;
};

const ribbonMetrics = computed(() => {
    const t = getTotals();
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

const mapSelect = (countryName: string) => {
    filters.value.country = filters.value.country === countryName ? 'All Countries' : countryName;
    renderAll();
};

const addLeaderCrown = () => {
    if (lkLeaderMarker.value) {
        try { lkLeaderMarker.value.remove(); } catch (e) { }
        lkLeaderMarker.value = null;
    }

    // @ts-ignore
    if (typeof L === 'undefined' || !lkMap.value) return;

    const lc = getMapLeaderCountry();
    const co = lc && MAP_COORDS[lc];
    if (!co) return;

    const rowv = getMapFilteredCountries().find((c) => c.country === lc);
    const v = rowv ? mapMetricVal(rowv) : 0;
    const label = mapMetric.value === 'users' ? num(v) + ' users' : fmt(v);

    // @ts-ignore
    lkLeaderMarker.value = L.marker([co[0], co[1]], {
        // @ts-ignore
        icon: L.divIcon({
            className: '',
            html: `<div style="transform:translate(-50%,-150%);background:#facc15;color:#0b1f33;font-weight:900;font-size:11px;padding:3px 9px;border-radius:9999px;white-space:nowrap;box-shadow:0 4px 12px rgba(0,0,0,.35)">👑 ${lc} · ${label}</div>`
        }),
        interactive: false
    }).addTo(lkMap.value);
};

const addMarkers = () => {
    lkMarkers.value.forEach((m) => m.remove());
    lkMarkers.value = [];

    const present: Record<string, boolean> = {};
    if (lkGeo.value) {
        lkGeo.value.features.forEach((f: any) => {
            const r = ourCountryRow(f.properties.name);
            if (r) present[r.country] = true;
        });
    }

    const rs = getFilteredCountries();
    rs.forEach((c) => {
        if (present[c.country]) return;
        const co = MAP_COORDS[c.country];
        if (!co) return;

        const reg = GEO_REGION[c.country] || 'Caribbean';
        // @ts-ignore
        const mk = L.circleMarker([co[0], co[1]], {
            radius: 7,
            fillColor: GEO_COLOR[reg] || '#10b981',
            color: '#fff',
            weight: 1.5,
            fillOpacity: 0.9
        }).addTo(lkMap.value);

        mk.bindTooltip(lkTipHtml(c), { className: 'mapcountry-tip', sticky: true });
        mk.on('click', () => mapSelect(c.country));
        lkMarkers.value.push(mk);
    });
};

const addViewerMarker = (fly = false) => {
    if (!viewerLoc.value || typeof L === 'undefined' || !lkMap.value) return;

    if (lkViewerDot.value) { try { lkViewerDot.value.remove(); } catch (e) { } }
    if (lkViewerMarker.value) { try { lkViewerMarker.value.remove(); } catch (e) { } }

    const loc = viewerLoc.value;
    // @ts-ignore
    lkViewerDot.value = L.marker([loc.lat, loc.lng], {
        // @ts-ignore
        icon: L.divIcon({ className: '', html: '<div class="gps-dot"></div>' }),
        interactive: false,
        zIndexOffset: 1000
    }).addTo(lkMap.value);

    // @ts-ignore
    lkViewerMarker.value = L.marker([loc.lat, loc.lng], {
        // @ts-ignore
        icon: L.divIcon({
            className: '',
            html: `<div style="transform:translate(-50%,-185%);background:#0891b2;color:#fff;font-weight:900;font-size:11px;padding:3px 9px;border-radius:9999px;white-space:nowrap;box-shadow:0 4px 12px rgba(0,0,0,.35)">📍 You are here · ${(loc.city ? loc.city + ', ' : '') + (loc.country || '')}${loc.ip ? ' (' + loc.ip + ')' : ''}</div>`
        }),
        interactive: false,
        zIndexOffset: 1001
    }).addTo(lkMap.value);

    if (fly) {
        try { lkMap.value.flyTo([loc.lat, loc.lng], 4, { duration: 1.1 }); } catch (e) { }
    }
};

const locateViewer = (force = false) => {
    // @ts-ignore
    if (typeof L === 'undefined') return;
    if (viewerLoc.value && !force) {
        addViewerMarker(false);
        return;
    }

    fetch('https://ipapi.co/json/')
        .then((r) => r.json())
        .then((d) => {
            if (d && d.latitude) {
                viewerLoc.value = { lat: d.latitude, lng: d.longitude, city: d.city, country: d.country_name, ip: d.ip };
                addViewerMarker(!!force);
            } else throw 0;
        })
        .catch(() => {
            fetch('https://ipwho.is/')
                .then((r) => r.json())
                .then((d) => {
                    if (d && (d.latitude || d.lat)) {
                        viewerLoc.value = { lat: d.latitude || d.lat, lng: d.longitude || d.lon, city: d.city, country: d.country, ip: d.ip };
                        addViewerMarker(!!force);
                    }
                })
                .catch(() => { });
        });
};

const renderCountryMap = () => {
    const box = document.getElementById('worldMap');
    if (!box) return;

    // @ts-ignore
    if (typeof L === 'undefined') return;

    if (!lkMap.value) {
        box.innerHTML = '';
        // @ts-ignore
        lkMap.value = L.map(box, {
            zoomControl: true,
            attributionControl: false,
            scrollWheelZoom: false,
            minZoom: 2,
            maxZoom: 6
        });
        lkMap.value.setView([8, -68], 3);
    }

    const applyGeo = (geo: any) => {
        lkGeo.value = geo;
        if (lkLayer.value) lkLayer.value.remove();

        // @ts-ignore
        lkLayer.value = L.geoJSON(geo, {
            style: lkStyle,
            onEachFeature: (f, layer) => {
                const row = ourCountryRow(f.properties.name);
                if (!row) return;
                layer.bindTooltip(lkTipHtml(row), { className: 'mapcountry-tip', sticky: true });
                layer.on('mouseover', () => {
                    layer.setStyle({ weight: 2.5, color: '#fff', fillOpacity: 0.95 });
                });
                layer.on('mouseout', () => {
                    layer.setStyle(lkStyle(f));
                });
                layer.on('click', () => mapSelect(row.country));
            }
        }).addTo(lkMap.value);

        addMarkers();
        addLeaderCrown();
        if (viewerLoc.value) {
            addViewerMarker(false);
        } else {
            locateViewer(false);
        }

        try {
            // @ts-ignore
            lkMap.value.fitBounds(L.latLngBounds([[-34, -118], [55, -34]]));
        } catch (e) { }

        setTimeout(() => {
            try { lkMap.value.invalidateSize(); } catch (e) { }
        }, 80);
    };

    if (lkGeo.value) {
        if (lkLayer.value) {
            lkLayer.value.setStyle(lkStyle);
            lkLayer.value.eachLayer((layer: any) => {
                const row = ourCountryRow(layer.feature.properties.name);
                if (row) layer.setTooltipContent(lkTipHtml(row));
            });
            addMarkers();
            addLeaderCrown();
            if (viewerLoc.value) {
                addViewerMarker(false);
            } else {
                locateViewer(false);
            }
        } else applyGeo(lkGeo.value);

        setTimeout(() => {
            try { lkMap.value.invalidateSize(); } catch (e) { }
        }, 80);
        return;
    }

    fetch('https://cdn.jsdelivr.net/gh/johan/world.geo.json@master/countries.geo.json')
        .then((r) => r.json())
        .then(applyGeo)
        .catch(() => { });
};

// Charts
const charts = ref<any>({});
const countryChartRef = ref<HTMLCanvasElement | null>(null);
const regionChartRef = ref<HTMLCanvasElement | null>(null);

const renderCharts = () => {
    Object.values(charts.value).forEach((c: any) => c && c.destroy());

    const rs = getFilteredCountries();

    // Country Chart
    const countryCtx = countryChartRef.value;
    if (countryCtx) {
        const labels = rs.map(c => c.country);
        const data = rs.map(c => mapMetric.value === 'users' ? Number(c.users) * getScale() : countryFin(c).gross);
        const maxVal = Math.max(...data, 1);
        const backgroundColors = data.map(v => v === maxVal ? '#facc15' : (mapMetric.value === 'users' ? '#28A8FF' : '#00C853'));

        charts.value.country = new Chart(countryCtx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: mapMetric.value === 'users' ? 'Users' : 'GTV',
                    data,
                    backgroundColor: backgroundColors,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#eef2f7' } }, x: { grid: { color: '#eef2f7' } } },
            }
        });
    }

    // Region Chart
    const regionCtx = regionChartRef.value;
    if (regionCtx) {
        const regs = ['Local', 'Regional', 'International'];
        const data = regs.map(r => countries.value.filter(c => c.region === r).reduce((s, c) => s + countryFin(c).gross, 0));

        charts.value.region = new Chart(regionCtx, {
            type: 'bar',
            data: {
                labels: regs,
                datasets: [{
                    label: 'GTV',
                    data,
                    backgroundColor: '#28A8FF',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#eef2f7' } }, x: { grid: { color: '#eef2f7' } } },
            }
        });
    }
};

const renderAll = async () => {
    await nextTick();
    const t = getTotals();
    const totalRev = t.platform + t.bank * (1 - SCOTIA_SHARE);
    const net = totalRev - t.cost;

    const set = (id: string, v: string) => {
        const e = document.getElementById(id);
        if (e) e.textContent = v;
    };
    set('rGTV', fmt(t.gross));
    set('rLinkUp', fmt(t.platform));
    set('rBank', fmt(t.bank));
    set('rNet', fmt(net));
    set('rUsers', num(t.users));
    set('rMerchants', num(t.merchants));
    set('rOrganizers', num(t.organizers));
    set('rCountries', num(t.countries));
    set('sideGTV', fmt(t.gross));

    renderCharts();
    renderCountryMap();
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
    renderAll();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});

// Computed tables
const usersByCountryTable = computed(() => {
    const rs = getFilteredCountries();
    const data = rs.map(c => ({
        country: c.country,
        region: c.region,
        users: Math.round(Number(c.users) * getScale())
    }));
    const total = data.reduce((s, x) => s + x.users, 0) || 1;
    data.sort((a, b) => b.users - a.users);
    const maxU = data.length ? data[0].users : 0;
    return { data, total, maxU };
});

const revenueByCountryTable = computed(() => {
    const rs = getFilteredCountries();
    const data = rs.map(c => {
        const f = countryFin(c);
        return {
            country: c.country,
            gtv: f.gross,
            platform: f.platform,
            bank: f.bank
        };
    });
    data.sort((a, b) => b.gtv - a.gtv);
    const totals = {
        gtv: data.reduce((s, x) => s + x.gtv, 0),
        platform: data.reduce((s, x) => s + x.platform, 0),
        bank: data.reduce((s, x) => s + x.bank, 0)
    };
    const maxG = data.length ? data[0].gtv : 0;
    return { data, totals, maxG };
});

const countryCommandTable = computed(() => {
    return getFilteredCountries().map(c => ({
        country: c.country,
        region: c.region,
        users: Number(c.users) * getScale(),
        merchants: Number(c.merchants),
        fin: countryFin(c)
    }));
});
</script>

<template>
    <Head title="Countries Dashboard" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="countries" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader
                title="Countries Dashboard"
                :countries="countries"
                :metrics="ribbonMetrics"
                @toggle-sidebar="toggleSidebar"
                @filter-change="handleFilterChange"
            />

            <section class="space-y-6 p-5 lg:p-8">
                <!-- Map Placeholder (Matching Prototype Layout) -->
                <div class="card rounded-3xl p-6">
                    <div class="mb-3 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <h3 class="flex items-center gap-2 text-xl font-black">
                                <Globe2 class="h-5 w-5 text-sky-600" /> Operating Map —
                                <span>{{ mapMetric === 'revenue' ? 'Revenue' : 'Users' }}</span> by Country
                            </h3>
                            <p class="text-sm text-slate-500">Hover a country for its numbers; the leader is crowned 👑.</p>
                        </div>
                        <div class="flex items-center gap-2 text-xs font-bold">
                            <div class="inline-flex rounded-2xl bg-slate-100 p-1">
                                <button
                                    @click="setMapMetric('revenue')"
                                    class="rounded-xl px-3 py-1.5 font-black transition"
                                    :class="mapMetric === 'revenue' ? 'bg-white shadow' : 'text-slate-500'"
                                >
                                    Revenue
                                </button>
                                <button
                                    @click="setMapMetric('users')"
                                    class="rounded-xl px-3 py-1.5 font-black transition"
                                    :class="mapMetric === 'users' ? 'bg-white shadow' : 'text-slate-500'"
                                >
                                    Users
                                </button>
                            </div>
                            <button
                                @click="locateViewer(true)"
                                class="flex items-center gap-1 rounded-xl border border-sky-200 bg-sky-50 px-3 py-1.5 font-black text-sky-700 hover:bg-sky-100 transition-colors"
                            >
                                <MapPin class="h-4 w-4" /> Locate me
                            </button>
                            <button
                                @click="filters.country = 'All Countries'; filters.region = 'All'; renderAll();"
                                class="rounded-xl border border-slate-200 px-3 py-1.5 font-black hover:bg-slate-50 transition-colors"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                    <!-- Map Box -->
                    <div id="worldMap" class="relative h-[460px] w-full overflow-hidden rounded-2xl bg-slate-900 shadow-inner z-0">
                        <div class="flex h-full items-center justify-center text-white/20">
                            <div class="text-center">
                                <Globe2 class="h-16 w-16 mx-auto mb-4 opacity-20" />
                                <p class="font-black text-xl uppercase tracking-widest">Loading Interactive Map...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Country {{ mapMetric === 'revenue' ? 'Revenue' : 'Users' }}</h3>
                        <div class="relative h-[240px] w-full">
                            <canvas ref="countryChartRef"></canvas>
                        </div>
                    </div>
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-4 text-xl font-black">Regional Revenue</h3>
                        <div class="relative h-[240px] w-full">
                            <canvas ref="regionChartRef"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Summary Tables Row -->
                <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
                    <!-- Users by Country Table -->
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-1 flex items-center gap-2 text-xl font-black">
                            <Users class="h-5 w-5 text-sky-600" /> Users by Country
                        </h3>
                        <p class="mb-4 text-sm text-slate-500">Total signups per country — leader highlighted.</p>
                        <div class="scrollbar overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs font-black text-slate-500 uppercase">
                                    <tr>
                                        <th class="py-2">Country</th>
                                        <th>Region</th>
                                        <th>Users</th>
                                        <th>% of Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="x in usersByCountryTable.data" :key="x.country" class="border-t" :class="{ 'bg-amber-50': x.users === usersByCountryTable.maxU && x.users > 0 }">
                                        <td class="py-2 font-black">{{ x.users === usersByCountryTable.maxU ? '👑 ' : '' }}{{ x.country }}</td>
                                        <td class="text-slate-500">{{ x.region }}</td>
                                        <td class="font-black">{{ num(x.users) }}</td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <span class="w-10">{{ (x.users / usersByCountryTable.total * 100).toFixed(1) }}%</span>
                                                <div class="h-2 flex-1 min-w-[50px] overflow-hidden rounded-full bg-slate-100">
                                                    <div
                                                        class="h-full"
                                                        :class="x.users === usersByCountryTable.maxU ? 'bg-amber-500' : 'bg-sky-500'"
                                                        :style="{ width: (x.users / usersByCountryTable.maxU * 100) + '%' }"
                                                    ></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="border-t-2 border-slate-300 font-black">
                                        <td class="py-2">Total</td>
                                        <td></td>
                                        <td>{{ num(usersByCountryTable.total) }}</td>
                                        <td>100%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Revenue by Country Table -->
                    <div class="card rounded-3xl p-6">
                        <h3 class="mb-1 flex items-center gap-2 text-xl font-black">
                            <DollarSign class="h-5 w-5 text-emerald-600" /> Revenue by Country
                        </h3>
                        <p class="mb-4 text-sm text-slate-500">GTV, LinkUp revenue & bank revenue per country.</p>
                        <div class="scrollbar overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs font-black text-slate-500 uppercase">
                                    <tr>
                                        <th class="py-2">Country</th>
                                        <th>GTV</th>
                                        <th>LinkUp Rev.</th>
                                        <th>Bank Rev.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="x in revenueByCountryTable.data" :key="x.country" class="border-t" :class="{ 'bg-emerald-50': x.gtv === revenueByCountryTable.maxG && x.gtv > 0 }">
                                        <td class="py-2 font-black">{{ x.gtv === revenueByCountryTable.maxG ? '👑 ' : '' }}{{ x.country }}</td>
                                        <td class="font-black">{{ fmt(x.gtv) }}</td>
                                        <td class="text-emerald-700">{{ fmt(x.platform) }}</td>
                                        <td class="text-amber-600">{{ fmt(x.bank) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="border-t-2 border-slate-300 font-black">
                                        <td class="py-2">Total</td>
                                        <td>{{ fmt(revenueByCountryTable.totals.gtv) }}</td>
                                        <td class="text-emerald-700">{{ fmt(revenueByCountryTable.totals.platform) }}</td>
                                        <td class="text-amber-600">{{ fmt(revenueByCountryTable.totals.bank) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Country Command Table -->
                <div class="card rounded-3xl p-6">
                    <h3 class="mb-4 text-xl font-black">Country Command Table</h3>
                    <div class="scrollbar overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs font-black text-slate-500 uppercase">
                                <tr>
                                    <th class="py-3">Country</th>
                                    <th>Region</th>
                                    <th>Users</th>
                                    <th>Merchants</th>
                                    <th>GTV</th>
                                    <th>LinkUp Rev.</th>
                                    <th>Bank Rev.</th>
                                    <th>Top Product</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in countryCommandTable" :key="c.country" class="border-t">
                                    <td class="py-3 font-black">{{ c.country }}</td>
                                    <td>{{ c.region }}</td>
                                    <td>{{ num(c.users) }}</td>
                                    <td>{{ num(c.merchants) }}</td>
                                    <td>{{ fmt(c.fin.gross) }}</td>
                                    <td class="font-black text-sky-600">{{ fmt(c.fin.platform) }}</td>
                                    <td class="text-amber-600">{{ fmt(c.fin.bank) }}</td>
                                    <td class="text-xs font-bold">{{ c.fin.top }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
