<template>
    <AppLayout>
        <div class="">

            <!-- Header -->
            <div class="flex items-center justify-end mb-4 flex-wrap gap-2">
                <Link :href="route('organizer.event.create')" class="btn px-5 py-2.5 font-black text-white rounded-xl"
                    style="background:linear-gradient(135deg,#2dd4bf,#f59e0b)">
                    <Plus class="w-3 h-3 inline mr-1" /> Add New Event
                </Link>
            </div>

            <!-- Filters -->
            <div class="card p-3 mb-3 flex flex-wrap gap-2 bg-white rounded-xl border border-slate-200">
                <select v-model="sortBy"
                    class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold outline-none">
                    <option value="created_desc">Creation date (Desc)</option>
                    <option value="created_asc">Creation date (Asc)</option>
                </select>
                <select v-model="statusFilter"
                    class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold outline-none">
                    <option value="all">All status</option>
                    <option value="live">Live</option>
                    <option value="draft">Draft</option>
                </select>
            </div>

            <!-- Results count -->
            <p class="text-[11px] font-black text-slate-400 uppercase mb-2">
                {{ filteredEvents.length }} result{{ filteredEvents.length === 1 ? '' : 's' }} found
            </p>

            <SampleDataBanner message="Showing sample events — publish your own event to manage it here."
                variant="warning" />

            <!-- Events Table -->
            <div class="card overflow-visible bg-white rounded-xl border border-slate-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] font-black text-slate-400 uppercase border-b border-slate-100">
                            <th class="px-4 py-3">Event</th>
                            <th class="px-4 py-3">Organizer</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="event in filteredEvents" :key="event.id"
                            class="border-b border-slate-50 last:border-0">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img :src="`${props.appURL}${event.image_object}`"
                                        class="h-10 w-10 rounded-xl object-cover shrink-0" alt="Event Image" />
                                    <div class="min-w-0">
                                        <p class="font-black truncate">{{ event?.title }}</p>
                                        <p class="text-[11px] text-slate-400 truncate">{{ event?.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 font-bold">
                                {{ event?.organizer?.organizer_name }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-black"
                                    :class="(event?.status ?? 'live').toLowerCase() === 'live' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500'">
                                    {{ (event?.status ?? 'Live').toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right relative">
                                <button
                                    class="h-8 w-8 rounded-lg border border-slate-200 grid place-items-center hover:bg-slate-50 ml-auto"
                                    @click.stop="onDropdownClick($event, event)">
                                    <MoreHorizontal class="w-4 h-4" />
                                </button>

                                <!-- Inline dropdown matching reference -->
                                <div v-if="activeDropdown === event.id"
                                    class="absolute right-4 top-10 z-30 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 text-left"
                                    @click.stop>

                                    <Link :href="route('organizer.event.report.statistics', event?.slug)"
                                        class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 no-underline">
                                        <BarChart2 class="w-4 h-4 shrink-0" /> Statistics
                                    </Link>

                                    <Link :href="route('organizer.event.report.drinks-inventory', event?.slug)"
                                        class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 no-underline">
                                        <Martini class="w-4 h-4 shrink-0" /> Drinks inventory
                                    </Link>

                                    <Link :href="route('organizer.payout.request')"
                                        class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 no-underline">
                                        <Send class="w-4 h-4 shrink-0" /> Request payout
                                    </Link>

                                    <Link :href="route('organizer.event.show', event?.slug)"
                                        class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 no-underline">
                                        <Info class="w-4 h-4 shrink-0" /> Details
                                    </Link>

                                    <Link :href="route('organizer.event.edit', event?.slug)"
                                        class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 no-underline">
                                        <Pencil class="w-4 h-4 shrink-0" /> Edit
                                    </Link>

                                    <Link :href="route('organizer.event.report.attendees', event?.slug)"
                                        class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 no-underline">
                                        <Users class="w-4 h-4 shrink-0" /> Attendees
                                    </Link>

                                    <Link :href="route('organizer.review.index')"
                                        class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700 no-underline">
                                        <Star class="w-4 h-4 shrink-0" /> Reviews
                                    </Link>

                                    <button v-if="event?.status === 'live'" @click="toggleEventStatus(event)"
                                        class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700">
                                        <Archive class="w-4 h-4 shrink-0" /> Draft
                                    </button>
                                    <button v-else @click="toggleEventStatus(event)"
                                        class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-slate-50 text-slate-700">
                                        <Upload class="w-4 h-4 shrink-0" /> Publish
                                    </button>

                                    <div class="my-1 border-t border-slate-100"></div>

                                    <button @click="deleteEvent(event)"
                                        class="w-full flex items-center gap-2 px-4 py-2.5 text-left text-sm font-bold hover:bg-rose-50 text-rose-600">
                                        <Trash2 class="w-4 h-4 shrink-0" /> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredEvents.length === 0">
                            <td colspan="4" class="px-4 py-10 text-center text-slate-400 font-bold">No events match this
                                filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import SampleDataBanner from '@/components/organizer/SampleDataBanner.vue';
import { Link, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import {
    MoreHorizontal, BarChart2, Ticket, Martini, Send,
    Info, Pencil, Users, Star, Archive, Upload, Trash2, Plus
} from 'lucide-vue-next';

const props = defineProps<{
    events: Array<Record<string, any>>;
    appURL: string;
}>();

const activeDropdown = ref<number | null>(null);
const activeEvent = ref<Record<string, any> | null>(null);
const dropdownPosition = ref<{ top: number; left: number }>({ top: 0, left: 0 });

// Toggle dropdown visibility & position using button coordinates
const onDropdownClick = (e: MouseEvent, event: any) => {
    const target = e.currentTarget as HTMLElement | null;
    if (target) {
        const rect = target.getBoundingClientRect();
        // Position menu aligned to the right of the button, slightly below
        dropdownPosition.value = {
            top: rect.bottom + window.scrollY,
            left: rect.right + window.scrollX - 200,
        };
    }
    if (activeDropdown.value === event.id) {
        activeDropdown.value = null;
        activeEvent.value = null;
    } else {
        activeDropdown.value = event.id;
        activeEvent.value = event;
    }
};

// Close dropdown when clicking outside
const closeDropdown = (event: Event) => {
    const target = event.target as Element;
    if (!target.closest('.dropdown') && !target.closest('.dropdown-menu')) {
        activeDropdown.value = null;
        activeEvent.value = null;
    }
};

// Toggle event status (draft/live)
const toggleEventStatus = (event: any) => {
    const newStatus = event.status === 'live' ? 'draft' : 'live';

    router.patch(route('organizer.event.toggle-status', { event_id: event.id }), {
        status: newStatus
    }, {
        onSuccess: () => {
            toast.success(`Event ${newStatus === 'live' ? 'live' : 'drafted'} successfully!`);
            activeDropdown.value = null;
        },
        onError: () => {
            toast.error('Failed to update event status.');
        }
    });
};

// Delete event
const deleteEvent = (event: any) => {
    toast.warning(`Event "${event.title}" will be deleted`, {
        action: {
            label: 'Delete',
            onClick: () => {
                router.delete(route('organizer.event.delete', event.id), {
                    onError: () => toast.error("Failed to delete")
                });
            }
        },
        duration: 5000
    });
};

// Mount and unmount event listeners
onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', closeDropdown);
});

// ----------------
// Filters & Sorting
// ----------------
const sortBy = ref<'created_desc' | 'created_asc'>('created_desc');
const statusFilter = ref<'all' | 'live' | 'draft'>('all');
const elapsedFilter = ref<'all' | 'elapsed' | 'upcoming'>('all');

const noop = () => { };

const normalizeStatus = (status?: string) => {
    const s = (status || '').toLowerCase();
    if (s === 'live' || s === 'live') return 'live';
    if (s === 'draft') return 'draft';
    return s || 'live';
};

const toDate = (val: any): Date | null => {
    if (!val) return null;
    const d = new Date(val);
    return isNaN(d.getTime()) ? null : d;
};

const filteredEvents = computed(() => {
    const now = new Date();
    let list = [...(props.events || [])];

    // status filter
    if (statusFilter.value !== 'all') {
        list = list.filter(e => normalizeStatus(e?.status) === statusFilter.value);
    }

    // elapsed filter by event_date
    if (elapsedFilter.value !== 'all') {
        list = list.filter(e => {
            const d = toDate(e?.event_date || e?.date || e?.start_date);
            if (!d) return false; // if no date, exclude when filtering by elapsed/upcoming
            return elapsedFilter.value === 'elapsed' ? d < now : d >= now;
        });
    }

    // sorting
    list.sort((a, b) => {
        const adCreated = toDate(a?.created_at)?.getTime() ?? 0;
        const bdCreated = toDate(b?.created_at)?.getTime() ?? 0;

        switch (sortBy.value) {
            case 'created_asc':
                return adCreated - bdCreated;
            case 'created_desc':
            default:
                return bdCreated - adCreated;
        }
    });

    return list;
});

</script>
