<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head } from '@inertiajs/vue3';
import { Star, MessageSquare, Utensils, Bike } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const countries = [
    { country: 'Bahamas', region: 'Local', users: 100000, merchants: 600, organizers: 120, tickets: 1000000, subscriptions: 50000, marketplace: 250000, eats: 500000, merchantPay: 2000000, wallet: 1500000, live: 100000, ads: 25000, wellness: 150000, cookouts: 90000, linkup360: 40000, coinsPurchased: 250000, coinsRedeemed: 90000 },
];

const handleFilterChange = () => {};

// Reviews State
const appReviews = ref([
    { id: 1, restaurant: 'Bahama Grill', customer: 'Aaliyah Clarke', rating: 5, comment: 'The Grilled Salmon Bowl was incredible! Fast delivery too.', date: 'Jun 1, 2:15 PM' },
    { id: 2, restaurant: 'Island Jerk Kitchen', customer: 'Marcus Johnson', rating: 4, comment: 'Great jerk chicken but a bit too spicy for me.', date: 'Jun 1, 1:40 PM' },
    { id: 3, restaurant: 'Trini Flavors', customer: 'Tanya Baptiste', rating: 5, comment: 'Best doubles in town. Driver was very polite.', date: 'Jun 1, 12:55 PM' }
]);

const restaurantRatings = ref([
    { name: 'Bahama Grill', rating: 4.8, reviews: 1242 },
    { name: 'Island Jerk Kitchen', rating: 4.7, reviews: 984 },
    { name: 'Trini Flavors', rating: 4.6, reviews: 721 }
]);

const driverRatings = ref([
    { name: 'Andre Johnson', rating: 4.9 },
    { name: 'Devon Singh', rating: 4.8 },
    { name: 'Liam Clarke', rating: 4.7 }
]);

onMounted(() => {
    document.body.classList.add('new-admin-body');
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="Eats Reviews & Ratings" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="eatsReviewsCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Eats Reviews & Ratings" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="handleFilterChange" />

            <section class="p-5 lg:p-8 space-y-6">
                <div>
                    <h3 class="text-3xl font-black text-orange-600">Reviews & Ratings</h3>
                    <p class="text-slate-500 font-medium mt-1">Monitor restaurant quality, driver behavior, and customer satisfaction levels.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Restaurant Ratings -->
                    <div class="card rounded-[40px] p-8 border-slate-100 shadow-sm bg-white">
                        <h4 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-3">
                            <Utensils class="w-6 h-6 text-orange-600" /> Restaurant Ratings
                        </h4>
                        <div class="space-y-4">
                            <div v-for="r in restaurantRatings" :key="r.name" class="flex justify-between items-center rounded-3xl bg-slate-50 p-5 border border-slate-100/50 hover:bg-slate-100 transition">
                                <b class="text-slate-900">{{ r.name }}</b>
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-orange-600">{{ r.rating }} ★</span>
                                    <span class="text-xs text-slate-400 font-bold uppercase tracking-widest">/ {{ r.reviews }} reviews</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Driver Ratings -->
                    <div class="card rounded-[40px] p-8 border-slate-100 shadow-sm bg-white">
                        <h4 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-3">
                            <Bike class="w-6 h-6 text-sky-600" /> Driver Ratings
                        </h4>
                        <div class="space-y-4">
                            <div v-for="d in driverRatings" :key="d.name" class="flex justify-between items-center rounded-3xl bg-slate-50 p-5 border border-slate-100/50 hover:bg-slate-100 transition">
                                <b class="text-slate-900">{{ d.name }}</b>
                                <span class="font-black text-sky-600">{{ d.rating }} ★</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Reviews List -->
                <div class="card rounded-[40px] p-8 border-slate-100 shadow-sm bg-white">
                    <div class="flex items-center justify-between mb-8">
                        <h4 class="text-xl font-black text-slate-800 flex items-center gap-3">
                            <MessageSquare class="w-6 h-6 text-amber-500" /> Customer Reviews from the App
                        </h4>
                        <span class="rounded-full bg-slate-100 px-4 py-1.5 text-xs font-black text-slate-400 uppercase tracking-widest">{{ appReviews.length }} RECENT</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <div v-for="r in appReviews" :key="r.id" class="rounded-[32px] bg-slate-50 p-6 border border-slate-100/50 relative group hover:bg-white hover:shadow-xl hover:border-orange-100 transition-all duration-300">
                            <div class="flex justify-between items-start gap-4 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-orange-100 text-orange-600 grid place-items-center font-black text-xs uppercase">{{ r.customer[0] }}</div>
                                    <div>
                                        <b class="text-slate-900 block leading-tight">{{ r.customer }}</b>
                                        <p class="text-[10px] text-slate-400 font-black uppercase mt-1 tracking-tighter">{{ r.restaurant }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 text-amber-500 font-black text-sm">
                                    {{ r.rating }} <Star class="w-4 h-4 fill-amber-500" />
                                </div>
                            </div>
                            <p class="text-sm text-slate-600 font-medium leading-relaxed italic">"{{ r.comment }}"</p>
                            <div class="mt-4 pt-4 border-t border-slate-200/50 flex justify-between items-center">
                                <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">{{ r.date }}</span>
                                <button class="text-[10px] font-black text-orange-600 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">Flag Review</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
