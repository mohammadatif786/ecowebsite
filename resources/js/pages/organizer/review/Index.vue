<script setup lang="ts">
import { ref, computed } from "vue";
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import { Star, MessageSquare, Search } from 'lucide-vue-next';

interface Review {
    id: number;
    event_id: number;
    user_id: number;
    rating: number;
    review_text: string;
    reviewer_name: string;
    status: string;
    created_at: string;
    is_visible: boolean;
    event_title: string;
    user_avatar?: string;
}

interface Event {
    id: number;
    title: string;
    organizer_name: string;
    user_avatar: string;
    reviews: Review[];
}

const props = defineProps<{
    events: Event[];
    reviews: Review[];
}>();

const searchQuery = ref('');

const allReviews = computed(() => {
    return props.reviews.map(review => {
        const event = props.events.find(e => e.id === review.event_id);
        return {
            ...review,
            event_title: event?.title || 'Unknown Event',
            user_avatar: review.user_avatar || '/placeholder-profile-pic2.jpg'
        };
    });
});

const filteredReviews = computed(() => {
    if (!searchQuery.value) return allReviews.value;
    const q = searchQuery.value.toLowerCase();
    return allReviews.value.filter(r =>
        r.reviewer_name.toLowerCase().includes(q) ||
        r.review_text.toLowerCase().includes(q) ||
        r.event_title.toLowerCase().includes(q)
    );
});

const averageRating = computed(() => {
    if (props.reviews.length === 0) return "0.0";
    const total = props.reviews.reduce((acc, r) => acc + r.rating, 0);
    return (total / props.reviews.length).toFixed(1);
});

function formatDate(dateString: string) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}
</script>

<template>
    <Head title="Events Review" />

    <AppLayout>

        <!-- Summary Card matching line 2514 -->
        <div class="card p-6 flex items-center gap-6 mb-4 bg-white rounded-[20px] border border-slate-100 shadow-sm">
            <div class="text-5xl font-black text-slate-800">{{ averageRating }}</div>
            <div>
                <div class="flex gap-1 mb-1">
                    <Star v-for="i in 5" :key="i"
                        class="w-5 h-5"
                        :class="i <= Math.round(Number(averageRating)) ? 'text-amber-400 fill-amber-400' : 'text-slate-200'" />
                </div>
                <p class="text-[13px] text-slate-400 font-bold uppercase tracking-tight">
                    {{ props.reviews.length }} reviews across your events
                </p>
            </div>
        </div>

        <!-- Search Filter -->
        <div class="relative mb-4">
            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
                v-model="searchQuery"
                placeholder="Search reviews by name, event or comment..."
                class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3.5 text-sm font-medium outline-none focus:border-amber-400 transition bg-white shadow-sm"
            />
        </div>

        <!-- Review List matching line 2516 -->
        <div class="space-y-3">
            <div v-for="review in filteredReviews" :key="review.id"
                class="card p-5 bg-white border border-slate-100 rounded-[20px] shadow-sm hover:shadow-md transition-shadow group">

                <div class="flex items-start justify-between gap-4">
                    <div class="flex gap-3">
                        <div class="h-10 w-10 rounded-full overflow-hidden shrink-0 border-2 border-slate-50">
                            <img :src="review.user_avatar" class="w-full h-full object-cover" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-black text-slate-800">{{ review.reviewer_name }}</p>
                                <div class="flex gap-0.5">
                                    <Star v-for="i in 5" :key="i"
                                        class="w-3 h-3"
                                        :class="i <= review.rating ? 'text-amber-400 fill-amber-400' : 'text-slate-100'" />
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400 font-black uppercase tracking-tighter mt-0.5 flex items-center gap-1.5">
                                <MessageSquare class="w-3 h-3" />
                                {{ review.event_title }}
                            </p>
                        </div>
                    </div>
                    <span class="text-[10px] font-black text-slate-300 uppercase shrink-0">{{ formatDate(review.created_at) }}</span>
                </div>

                <div class="mt-3 text-[14px] text-slate-600 leading-relaxed font-medium pl-1 bg-slate-50/50 p-3 rounded-xl border border-slate-50 italic">
                    "{{ review.review_text }}"
                </div>
            </div>

            <div v-if="filteredReviews.length === 0" class="py-20 text-center bg-white rounded-[20px] border border-dashed border-slate-200">
                <Star class="w-12 h-12 text-slate-100 mx-auto mb-3" />
                <p class="text-slate-400 font-bold">No reviews match your search.</p>
            </div>
        </div>

    </AppLayout>
</template>

<style scoped>
.card {
    background: #ffffff;
}

/* Custom scrollbar for better feel */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}

/* Animations */
.animate-in {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}
.fade-in {
    animation-name: fadeIn;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
