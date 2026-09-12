<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Eye, MessageCircle, Search, X } from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

type ReviewStatus = 'pending' | 'approved' | 'rejected';
type QueueStatus = 'all' | 'new' | 'in_review' | 'escalated' | 'resolved';
type FeedbackCategory = 'Praise' | 'Complaint' | 'Concern';

type ReviewItem = {
    id: number;
    service: string;
    rating: number;
    user: string;
    email?: string;
    country: string;
    source: string;
    category: FeedbackCategory;
    status: ReviewStatus;
    date: string;
    comment: string;
    event_id?: number | null;
    event_title?: string;
};

const props = defineProps<{
    initialReviewsConcerns?: ReviewItem[];
}>();

const feedbackItems = computed(() => props.initialReviewsConcerns || []);
const serviceFilter = ref('all');
const statusFilter = ref<QueueStatus>('all');
const search = ref('');
const activeReview = ref<ReviewItem | null>(null);
const queueStatuses: { value: QueueStatus; label: string }[] = [
    { value: 'all', label: 'All' },
    { value: 'new', label: 'New' },
    { value: 'in_review', label: 'In Review' },
    { value: 'escalated', label: 'Escalated' },
    { value: 'resolved', label: 'Resolved' },
];

const services = computed(() => {
    const names = feedbackItems.value.map((item) => item.service || 'Events & Tickets');
    return [...new Set(names)].sort();
});

const filteredItems = computed(() => {
    const q = search.value.trim().toLowerCase();

    return feedbackItems.value
        .filter((item) => serviceFilter.value === 'all' || item.service === serviceFilter.value)
        .filter((item) => statusFilter.value === 'all' || queueStatus(item) === statusFilter.value)
        .filter((item) => {
            if (!q) return true;
            return [item.comment, item.user, item.email, item.service, item.source, item.country, item.category, item.status]
                .filter(Boolean)
                .join(' ')
                .toLowerCase()
                .includes(q);
        })
        .sort((a, b) => b.date.localeCompare(a.date) || b.id - a.id);
});

const averageRating = computed(() => {
    if (!feedbackItems.value.length) return '0.0';
    const total = feedbackItems.value.reduce((sum, item) => sum + Number(item.rating || 0), 0);
    return (total / feedbackItems.value.length).toFixed(1);
});

const openConcerns = computed(() => feedbackItems.value.filter((item) => item.status === 'pending' && item.rating <= 3).length);
const resolvedRate = computed(() => {
    if (!feedbackItems.value.length) return 0;
    return Math.round((feedbackItems.value.filter((item) => item.status === 'approved').length / feedbackItems.value.length) * 100);
});
const negativeCount = computed(() => feedbackItems.value.filter((item) => item.rating <= 2).length);

const serviceTiles = computed(() => {
    return services.value.map((service) => {
        const items = feedbackItems.value.filter((item) => item.service === service);
        const average = items.length ? items.reduce((sum, item) => sum + item.rating, 0) / items.length : 0;
        return {
            service,
            count: items.length,
            average,
            concerns: items.filter((item) => item.status === 'pending' && item.rating <= 3).length,
        };
    });
});

const queueStatus = (item: ReviewItem): Exclude<QueueStatus, 'all'> => {
    if (item.status === 'approved') return 'resolved';
    if (item.status === 'rejected') return 'escalated';
    return item.rating <= 3 ? 'new' : 'in_review';
};

const queueStatusLabel = (status: Exclude<QueueStatus, 'all'>) => {
    return {
        new: 'New',
        in_review: 'In Review',
        escalated: 'Escalated',
        resolved: 'Resolved',
    }[status];
};

const statusClass = (status: Exclude<QueueStatus, 'all'>) => {
    return {
        new: 'bg-rose-50 text-rose-700',
        in_review: 'bg-amber-50 text-amber-700',
        escalated: 'bg-purple-50 text-purple-700',
        resolved: 'bg-green-50 text-green-700',
    }[status];
};

const categoryClass = (category: FeedbackCategory) => {
    return {
        Praise: 'bg-green-50 text-green-700',
        Complaint: 'bg-rose-50 text-rose-700',
        Concern: 'bg-orange-50 text-orange-700',
    }[category];
};

const ratingClass = (rating: number) => {
    if (rating <= 2) return 'text-rose-600';
    if (rating === 3) return 'text-amber-500';
    return 'text-green-600';
};

const starText = (rating: number) => {
    const rounded = Math.max(0, Math.min(5, Math.round(Number(rating || 0))));
    return '★'.repeat(rounded) + '☆'.repeat(5 - rounded);
};

const updateStatus = (item: ReviewItem, status: ReviewStatus, label?: string) => {
    router.patch(
        route('admin.trust.reviews.status', { review: item.id }),
        { status },
        {
            preserveScroll: true,
            onSuccess: () => toast.success(label || 'Review updated.'),
            onError: () => toast.error('Unable to update review status.'),
        },
    );
};

const startReview = (item: ReviewItem) => {
    if (item.status === 'pending') {
        toast.success(`${item.user} review is in queue.`);
        return;
    }

    updateStatus(item, 'pending', 'Review moved back to queue.');
};

const messageReviewer = (item: ReviewItem) => {
    toast.info(item.email ? `Message ${item.user} at ${item.email}.` : `No email found for ${item.user}.`);
};
</script>

<template>
    <div class="space-y-6">
        <Toaster rich-colors position="top-right" />

        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h3 class="text-3xl font-black text-slate-950">Reviews & Concerns - Voice of Customer</h3>
                <p class="mt-1 max-w-4xl text-slate-500">
                    Live event reviews from the database. Moderate pending feedback, approve positive reviews, reject inappropriate entries, and watch low ratings as concerns.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Avg Rating</p>
                <h3 class="mt-1 text-4xl font-black text-amber-500">{{ averageRating }}</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">out of 5</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Total Reviews</p>
                <h3 class="mt-1 text-4xl font-black">{{ feedbackItems.length }}</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Open Concerns</p>
                <h3 class="mt-1 text-4xl font-black text-rose-600">{{ openConcerns }}</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">pending 1-3 rating</p>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Approved Rate</p>
                <h3 class="mt-1 text-4xl font-black text-green-600">{{ resolvedRate }}%</h3>
            </div>
            <div class="card rounded-3xl p-5">
                <p class="font-bold text-slate-500">Negative (1-2)</p>
                <h3 class="mt-1 text-4xl font-black text-rose-600">{{ negativeCount }}</h3>
            </div>
        </div>

        <section class="card rounded-3xl p-6">
            <h3 class="mb-4 text-xl font-black">By Service</h3>
            <div v-if="serviceTiles.length" class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                <button
                    v-for="tile in serviceTiles"
                    :key="tile.service"
                    class="rounded-2xl border p-3 text-left transition"
                    :class="serviceFilter === tile.service ? 'border-purple-400 bg-purple-50' : 'border-slate-100 bg-slate-50 hover:bg-white'"
                    @click="serviceFilter = serviceFilter === tile.service ? 'all' : tile.service"
                >
                    <p class="truncate text-sm font-black">{{ tile.service }}</p>
                    <div class="mt-1 flex items-center justify-between">
                        <span class="text-lg font-black" :class="ratingClass(tile.average)">{{ tile.average.toFixed(1) }}</span>
                        <span class="text-xs text-slate-500">{{ tile.count }} rev</span>
                    </div>
                    <p class="mt-0.5 text-xs font-bold" :class="tile.concerns ? 'text-rose-600' : 'text-green-600'">
                        {{ tile.concerns ? `${tile.concerns} concern${tile.concerns > 1 ? 's' : ''}` : 'clear' }}
                    </p>
                </button>
            </div>
            <p v-else class="rounded-2xl bg-slate-50 p-5 text-center font-bold text-slate-400">No database reviews found yet.</p>
        </section>

        <section class="card rounded-3xl p-6">
            <div class="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <h3 class="text-xl font-black">Feedback Queue</h3>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input v-model="search" class="w-full rounded-2xl border border-slate-200 py-2.5 pl-10 pr-4 text-sm sm:w-80" placeholder="Search comment, user, service..." />
                    </div>
                    <select v-model="serviceFilter" class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-black">
                        <option value="all">All Services</option>
                        <option v-for="service in services" :key="service">{{ service }}</option>
                    </select>
                </div>
            </div>

            <div class="mb-4 flex flex-wrap gap-2">
                <button
                    v-for="status in queueStatuses"
                    :key="status.value"
                    class="rounded-full px-4 py-1.5 text-xs font-black"
                    :class="statusFilter === status.value ? 'bg-slate-950 text-white' : 'bg-slate-100 text-slate-600'"
                    @click="statusFilter = status.value"
                >
                    {{ status.label }}
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1180px] text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500">
                        <tr>
                            <th class="w-24 py-3">Rating</th>
                            <th class="w-40">Service</th>
                            <th class="min-w-[330px]">Feedback</th>
                            <th class="w-48">User</th>
                            <th class="w-44">Source</th>
                            <th class="w-36">Category</th>
                            <th class="w-32">Status</th>
                            <th class="w-72">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in filteredItems" :key="item.id" class="align-top border-t">
                            <td class="py-4">
                                <div class="whitespace-nowrap text-sm font-black tracking-normal" :class="ratingClass(item.rating)">{{ starText(item.rating) }}</div>
                            </td>
                            <td class="font-black">{{ item.service }}</td>
                            <td class="max-w-md">
                                <p class="text-slate-700">{{ item.comment }}</p>
                                <p v-if="item.status === 'approved'" class="mt-1 text-xs font-black text-purple-600">↳ Thanks for your feedback.</p>
                                <p v-else-if="item.status === 'rejected'" class="mt-1 text-xs font-black text-purple-600">↳ Escalated to trust team for review.</p>
                            </td>
                            <td>
                                <p class="font-black">{{ item.user }}</p>
                                <p class="text-xs text-slate-400">{{ item.email || item.country }}</p>
                            </td>
                            <td>
                                <p class="text-xs font-bold text-slate-600">{{ item.source }}</p>
                                <p class="text-xs text-slate-400">{{ item.country }}</p>
                            </td>
                            <td>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-black" :class="categoryClass(item.category)">{{ item.category }}</span>
                            </td>
                            <td>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-black" :class="statusClass(queueStatus(item))">{{ queueStatusLabel(queueStatus(item)) }}</span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    <button v-if="queueStatus(item) === 'new'" class="inline-flex items-center gap-1 rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-black text-amber-700" @click="startReview(item)">
                                        Start
                                    </button>
                                    <button class="inline-flex items-center gap-1 rounded-lg bg-sky-100 px-2.5 py-1 text-xs font-black text-sky-700" @click="messageReviewer(item)">
                                        <MessageCircle class="h-3 w-3" /> Message
                                    </button>
                                    <button v-if="queueStatus(item) !== 'resolved'" class="inline-flex items-center gap-1 rounded-lg bg-purple-100 px-2.5 py-1 text-xs font-black text-purple-700" @click="updateStatus(item, 'rejected', 'Review escalated.')">
                                        Escalate
                                    </button>
                                    <button v-if="queueStatus(item) !== 'resolved'" class="inline-flex items-center gap-1 rounded-lg bg-green-100 px-2.5 py-1 text-xs font-black text-green-700" @click="updateStatus(item, 'approved', 'Review resolved.')">
                                        Resolve
                                    </button>
                                    <button class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700" @click="activeReview = item">
                                        <Eye class="h-3 w-3" /> View
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!filteredItems.length">
                            <td colspan="8" class="py-8 text-center font-bold text-slate-400">No matching database reviews.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div v-if="activeReview" class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/50 p-5">
            <div class="w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between bg-gradient-to-r from-purple-600 to-fuchsia-500 p-6 text-white">
                    <div>
                        <h3 class="text-2xl font-black">Review #{{ activeReview.id }}</h3>
                        <p class="text-sm text-purple-100">{{ activeReview.source }} - {{ activeReview.country }}</p>
                    </div>
                    <button @click="activeReview = null"><X class="h-7 w-7" /></button>
                </div>
                <div class="space-y-4 p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase text-slate-400">Rating</p>
                            <b :class="ratingClass(activeReview.rating)">{{ starText(activeReview.rating) }}</b>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase text-slate-400">Status</p>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-black" :class="statusClass(queueStatus(activeReview))">{{ queueStatusLabel(queueStatus(activeReview)) }}</span>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase text-slate-400">Category</p>
                            <b>{{ activeReview.category }}</b>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-black text-slate-500">Reviewer</p>
                        <p class="font-bold text-slate-900">{{ activeReview.user }}</p>
                        <p v-if="activeReview.email" class="text-sm text-slate-500">{{ activeReview.email }}</p>
                    </div>

                    <div>
                        <p class="text-sm font-black text-slate-500">Comment</p>
                        <p class="mt-1 rounded-2xl bg-slate-50 p-4 text-slate-700">{{ activeReview.comment }}</p>
                    </div>

                    <div class="flex flex-wrap justify-end gap-2">
                        <button class="rounded-2xl bg-slate-100 px-5 py-2.5 font-black" @click="activeReview = null">Close</button>
                        <button v-if="queueStatus(activeReview) !== 'resolved'" class="rounded-2xl bg-purple-600 px-5 py-2.5 font-black text-white" @click="updateStatus(activeReview, 'rejected', 'Review escalated.'); activeReview = null">Escalate</button>
                        <button v-if="queueStatus(activeReview) !== 'resolved'" class="rounded-2xl bg-green-600 px-5 py-2.5 font-black text-white" @click="updateStatus(activeReview, 'approved', 'Review resolved.'); activeReview = null">Resolve</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaf0f7;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}
</style>
