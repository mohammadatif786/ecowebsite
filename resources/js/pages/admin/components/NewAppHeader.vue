<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Check, HelpCircle, LifeBuoy, Lightbulb, ListChecks, LogOut, Menu, Search, Star, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    title: string;
    countries: any[];
    helpContext?: string;
    metrics?: {
        gtv: string | number;
        linkupRev: string | number;
        procPool: string | number;
        netProfit: string | number;
        users: string | number;
        merchants: string | number;
        organizers: string | number;
        countries: string | number;
    };
}>();

const emit = defineEmits(['toggle-sidebar', 'filter-change', 'search']);

const region = ref('All');
const country = ref('All Countries');
const period = ref('Monthly');
const searchQuery = ref('');
const showHelpModal = ref(false);
const showRateModal = ref(false);
const selectedRating = ref(0);
const hoveredRating = ref(0);
const ratingComment = ref('');
const ratingSubmitted = ref(false);

const helpContent = computed(() => {
    const isSmtpSettings = props.helpContext === 'settingsSmtpCommand'
        || props.title.trim().toLowerCase().includes('smtp');

    if (isSmtpSettings) {
        return {
            description: 'Configure the outbound email server used for campaigns and transactional email.',
            instructions: [
                'Enter SMTP host, port, and credentials.',
                'Send a test email.',
                'Save once verified.',
            ],
            tips: ["A failed test here means emails won't send — fix before saving."],
        };
    }

    return {
        description: 'The top-level cockpit for the whole platform. It rolls up revenue (GTV), users, merchants, events, and country performance into one view so an administrator can gauge the health of the business instantly.',
        instructions: [
            'Scan the KPI cards (GTV, Users, Net Profit, Countries) for the current period.',
            'Use the period selector (Monthly/Quarterly) and Region filter to reframe every metric.',
            'Read the charts to spot trends; hover for exact values.',
            'Use global search to jump to any user, event, merchant, or country.',
        ],
        tips: [
            'This is a read/monitor view — operational changes happen in the dedicated sections.',
            'Set the region filter first; it cascades to the figures below.',
        ],
    };
});

const onFilterChange = () => {
    emit('filter-change', {
        region: region.value,
        country: country.value,
        period: period.value,
    });
};

const onSearch = () => {
    emit('search', searchQuery.value);
};

const toggleSidebar = () => {
    emit('toggle-sidebar');
};

const openHelp = () => {
    showHelpModal.value = true;
};

const openRate = () => {
    selectedRating.value = 0;
    hoveredRating.value = 0;
    ratingComment.value = '';
    ratingSubmitted.value = false;
    showRateModal.value = true;
};

const closeHelp = () => {
    showHelpModal.value = false;
};

const closeRate = () => {
    showRateModal.value = false;
};

const submitRating = () => {
    if (!selectedRating.value) return;
    ratingSubmitted.value = true;
};

const logout = () => {
    router.post(route('admin.logout'));
};

const closeActiveModal = () => {
    if (showRateModal.value) closeRate();
    else if (showHelpModal.value) closeHelp();
};

const onKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') closeActiveModal();
};

watch([showHelpModal, showRateModal], ([helpOpen, rateOpen]) => {
    document.body.style.overflow = helpOpen || rateOpen ? 'hidden' : '';
});

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});

const downloadCSV = () => {
    alert('Export CSV is available in the full app.');
};

const exportCurrentView = () => {
    alert('Export View is available in the full app.');
};
</script>

<template>
    <header class="glass sticky top-0 z-40">
        <div class="space-y-4 px-5 py-5 lg:px-8">
            <div class="flex items-center gap-2">
                <button @click="toggleSidebar" class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 font-bold">
                    <Menu class="h-4 w-4" /> Menu
                </button>
                <button
                    @click="openHelp"
                    class="flex items-center gap-2 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-2 font-black text-indigo-700 hover:bg-indigo-100"
                >
                    <HelpCircle class="h-4 w-4" /> Help
                </button>
                <button
                    @click="openRate"
                    class="flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 font-black text-amber-700 hover:bg-amber-100"
                >
                    <Star class="h-4 w-4" /> <span>Rate</span>
                </button>
                <button
                    type="button"
                    @click="logout"
                    class="ml-auto flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 font-black text-rose-700 transition hover:bg-rose-100"
                >
                    <LogOut class="h-4 w-4" /> <span>Logout</span>
                </button>
            </div>
            <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-center 2xl:justify-between">
                <div>
                    <h2 class="gradient-title text-3xl font-black">{{ title }}</h2>
                    <p class="max-w-5xl text-sm text-slate-500">
                        One command center for revenue, countries, users, merchants, events, coins, fraud, partners, operations, and forecasts.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            @input="onSearch"
                            class="w-72 rounded-2xl border border-slate-200 bg-white px-4 py-2 pr-10 font-bold"
                            placeholder="Search user, event, merchant, country..."
                        />
                        <Search class="absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    </div>
                    <select v-model="region" @change="onFilterChange" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 font-bold">
                        <option value="All">All Regions</option>
                        <option>Local</option>
                        <option>Regional</option>
                        <option>International</option>
                    </select>
                    <select v-model="country" @change="onFilterChange" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 font-bold">
                        <option>All Countries</option>
                        <option v-for="c in countries" :key="c.country">{{ c.country }}</option>
                    </select>
                    <select v-model="period" @change="onFilterChange" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 font-bold">
                        <option>Today</option>
                        <option>Weekly</option>
                        <option value="Monthly">Monthly</option>
                        <option>Quarterly</option>
                        <option>Yearly</option>
                        <option>5-Year</option>
                    </select>
                    <button @click="downloadCSV" class="rounded-2xl bg-slate-950 px-4 py-2 font-bold text-white">Export CSV</button>
                    <button @click="exportCurrentView" class="rounded-2xl border border-slate-300 bg-white px-4 py-2 font-bold">Export View ⤓</button>
                </div>
            </div>
            <div class="ribbon grid grid-cols-2 gap-3 rounded-3xl p-4 md:grid-cols-4 2xl:grid-cols-8">
                <div>
                    <p class="text-xs text-slate-300">GTV</p>
                    <b id="rGTV">{{ metrics?.gtv || '$0' }}</b>
                </div>
                <div>
                    <p class="text-xs text-slate-300">LinkUp Rev.</p>
                    <b id="rLinkUp">{{ metrics?.linkupRev || '$0' }}</b>
                </div>
                <div>
                    <p class="text-xs text-slate-300">Proc. Pool</p>
                    <b id="rBank">{{ metrics?.procPool || '$0' }}</b>
                </div>
                <div>
                    <p class="text-xs text-slate-300">Net Profit</p>
                    <b id="rNet">{{ metrics?.netProfit || '$0' }}</b>
                </div>
                <div>
                    <p class="text-xs text-slate-300">Users</p>
                    <b id="rUsers">{{ metrics?.users || '0' }}</b>
                </div>
                <div>
                    <p class="text-xs text-slate-300">Merchants</p>
                    <b id="rMerchants">{{ metrics?.merchants || '0' }}</b>
                </div>
                <div>
                    <p class="text-xs text-slate-300">Organizers</p>
                    <b id="rOrganizers">{{ metrics?.organizers || '0' }}</b>
                </div>
                <div>
                    <p class="text-xs text-slate-300">Countries</p>
                    <b id="rCountries">{{ metrics?.countries || '0' }}</b>
                </div>
            </div>
        </div>
    </header>

    <Teleport to="body">
        <Transition name="admin-modal">
            <div
                v-if="showHelpModal"
                class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-950/65 p-3 backdrop-blur-[1px] sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="admin-help-title"
                @click.self="closeHelp"
            >
                <section class="w-full max-w-[672px] overflow-hidden rounded-[26px] bg-white shadow-2xl">
                    <div class="flex items-center justify-between bg-gradient-to-r from-indigo-600 to-violet-500 px-6 py-6 text-white sm:px-7">
                        <div class="flex min-w-0 items-center gap-4">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/20">
                                <LifeBuoy class="h-6 w-6" />
                            </span>
                            <div class="min-w-0">
                                <h2 id="admin-help-title" class="truncate text-xl font-black sm:text-2xl">{{ helpContext === 'settingsSmtpCommand' ? 'SMTP Settings' : title }}</h2>
                                <p class="text-sm font-medium text-white/90">Administrator guide · how this section works</p>
                            </div>
                        </div>
                        <button type="button" class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/15 transition hover:bg-white/25" aria-label="Close help" @click="closeHelp">
                            <X class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="space-y-5 px-6 py-7 text-[15px] leading-6 text-slate-600 sm:px-7">
                        <p>{{ helpContent.description }}</p>

                        <div>
                            <h3 class="mb-2 flex items-center gap-2 font-black text-slate-800">
                                <ListChecks class="h-4 w-4 text-indigo-600" /> How to use it
                            </h3>
                            <ol class="space-y-2">
                                <li v-for="(instruction, index) in helpContent.instructions" :key="instruction" class="flex items-start gap-3">
                                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-indigo-100 text-xs font-black text-indigo-600">{{ index + 1 }}</span>
                                    <span>{{ instruction }}</span>
                                </li>
                            </ol>
                        </div>

                        <div class="rounded-2xl bg-amber-50 px-5 py-4 text-sm text-amber-800">
                            <h3 class="mb-1 flex items-center gap-2 text-base font-black"><Lightbulb class="h-4 w-4" /> Tips</h3>
                            <ul class="list-disc space-y-1 pl-4">
                                <li v-for="tip in helpContent.tips" :key="tip">{{ tip }}</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-5 py-4 sm:px-7">
                        <p class="text-xs font-bold text-slate-400">LinkUp Admin Help · context-aware</p>
                        <button type="button" class="rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700" @click="closeHelp">Got it</button>
                    </div>
                </section>
            </div>
        </Transition>

        <Transition name="admin-modal">
            <div
                v-if="showRateModal"
                class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-950/65 p-3 backdrop-blur-[1px] sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="admin-rate-title"
                @click.self="closeRate"
            >
                <section class="w-full max-w-[448px] overflow-hidden rounded-[26px] bg-white shadow-2xl">
                    <div class="flex items-center justify-between bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-5 text-white">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/20"><Star class="h-6 w-6" /></span>
                            <div class="min-w-0">
                                <h2 id="admin-rate-title" class="text-2xl font-black">Rate this service</h2>
                                <p class="truncate text-sm font-medium text-white/95">{{ title }}</p>
                            </div>
                        </div>
                        <button type="button" class="grid h-11 w-11 place-items-center rounded-2xl bg-white/20 transition hover:bg-white/30" aria-label="Close rating" @click="closeRate"><X class="h-6 w-6" /></button>
                    </div>

                    <div class="space-y-5 px-6 py-6">
                        <div v-if="ratingSubmitted" class="rounded-2xl bg-emerald-50 px-4 py-5 text-center">
                            <Check class="mx-auto mb-2 h-8 w-8 text-emerald-600" />
                            <p class="font-black text-emerald-800">Thank you for your feedback!</p>
                        </div>
                        <template v-else>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-center text-sm font-black text-slate-400">No ratings yet — be the first!</div>
                            <div class="text-center">
                                <p class="mb-3 font-black text-slate-700">How would you rate your experience?</p>
                                <div class="flex justify-center gap-2" role="radiogroup" aria-label="Rating">
                                    <button
                                        v-for="rating in 5"
                                        :key="rating"
                                        type="button"
                                        class="text-slate-300 transition hover:scale-110 focus:outline-none"
                                        :class="rating <= (hoveredRating || selectedRating) ? 'text-amber-400' : ''"
                                        :aria-label="`${rating} star${rating > 1 ? 's' : ''}`"
                                        @mouseenter="hoveredRating = rating"
                                        @mouseleave="hoveredRating = 0"
                                        @click="selectedRating = rating"
                                    >
                                        <Star class="h-9 w-9" :fill="rating <= (hoveredRating || selectedRating) ? 'currentColor' : 'none'" />
                                    </button>
                                </div>
                            </div>
                            <textarea v-model="ratingComment" rows="2" maxlength="1000" class="w-full resize-none rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100" placeholder="Tell us what's working or what we can fix (optional)"></textarea>
                            <button type="button" :disabled="!selectedRating" class="w-full rounded-2xl bg-slate-950 py-3.5 text-sm font-black text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40" @click="submitRating">Submit Rating</button>
                        </template>
                        <p class="text-center text-xs font-medium text-slate-400">Your feedback flows to the LinkUp Reviews &amp; Concerns team.</p>
                    </div>
                </section>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.admin-modal-enter-active,
.admin-modal-leave-active {
    transition: opacity 180ms ease;
}

.admin-modal-enter-from,
.admin-modal-leave-to {
    opacity: 0;
}
</style>
