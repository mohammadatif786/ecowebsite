<script setup lang="ts">
import { BadgeCheck, Ticket, MapPin, Calendar, Pencil, QrCode, BarChart3, Briefcase, Users, Sparkles } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import moment from "moment";

const props = defineProps<{
    user: any;
    purchased_tickets: number;
    cancelled_tickets: number;
    market_place: number;
    matches: number;
    gifts_purchased: number;
    gifts_collected_amount: number;
    like_matches: number;
}>();

const editProfile = () => {
    router.visit(route("frontend.user.edit.profile"));
};

</script>
<template>
    <section
        class="bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] overflow-hidden shadow-[0_18px_40px_rgba(2,6,23,.10)]">
        <!-- Adjusted top frame: removed fixed height so snapshot cards never get clipped -->
        <div class="relative min-h-[380px] sm:min-h-[410px]">
            <div class="absolute inset-0 bg-gradient-to-r from-sky-400 via-blue-500 to-indigo-500"></div>
            <div class="absolute inset-0 opacity-30"
                style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,.6) 0, rgba(255,255,255,0) 35%), 

                                 radial-gradient(circle at 80% 20%, rgba(255,255,255,.45) 0, rgba(255,255,255,0) 40%), 

                                 radial-gradient(circle at 70% 80%, rgba(255,255,255,.35) 0, rgba(255,255,255,0) 45%);">
            </div>
            <div class="relative px-5 sm:px-7 py-6 flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <!-- Bigger profile pic -->
                        <div
                            class="w-32 h-32 sm:w-40 sm:h-40 rounded-[34px] bg-white/90 p-1 border border-white/60 shadow-lg">
                            <div class="w-full h-full rounded-[30px] bg-slate-200 overflow-hidden">
                                <img id="avatar" class="w-full h-full object-cover"
                                    :src="props.user.avatar ?? 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=420&h=420&fit=crop'"
                                    alt="avatar" />
                            </div>
                        </div>

                        <div class="text-white">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">{{ props.user.name }}</h1>
                                <span
                                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-white/70 rounded-full px-[0.65rem] py-[0.35rem] bg-white/90 text-xs font-extrabold text-slate-900">ID:
                                    {{ props.user.linkup_id ?? props.user.email }}
                                </span>

                                <span
                                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-emerald-200 rounded-full px-[0.65rem] py-[0.35rem] bg-emerald-50 text-xs font-extrabold text-emerald-700">
                                    <BadgeCheck class="w-3.5 h-3.5" /> Verified
                                </span>

                                <span
                                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-sky-200 rounded-full px-[0.65rem] py-[0.35rem] bg-sky-50 text-xs font-extrabold text-sky-700 hidden">
                                    <Ticket class="w-3.5 h-3.5" /> Organizer
                                </span>
                            </div>

                            <div class="mt-2 flex flex-wrap gap-2">
                                <span
                                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-white/70 rounded-full px-[0.65rem] py-[0.35rem] bg-white/90 text-xs font-extrabold text-slate-900">
                                    <MapPin class="w-3.5 h-3.5" />
                                    <span> {{ props.user.city ?? '—' }}</span>, <span>{{ props.user.country ??
                                        '—'}}</span>
                                </span>

                                <span
                                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-white/70 rounded-full px-[0.65rem] py-[0.35rem] bg-white/90 text-xs font-extrabold text-slate-900">
                                    <Calendar class="w-3.5 h-3.5" /> Joined <span>{{
                                        moment(props.user?.created_at).format("MMM, YYYY") ?? '—' }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button @click="editProfile"
                            class="relative overflow-hidden flex items-center gap-[0.55rem] justify-center rounded-2xl border border-sky-300/28 bg-gradient-to-b from-white to-[#fbfdff] font-black px-[0.9rem] py-[0.7rem] transition-all duration-150 shadow-[0_10px_22px_rgba(2,6,23,.06)] hover:-translate-y-px hover:shadow-[0_16px_30px_rgba(2,6,23,.10)] active:translate-y-0 bg-gradient-to-b from-[#dfff00] to-[rgba(223,255,0,.86)] border-[rgba(223,255,0,.55)] text-slate-900">
                            <Pencil class="w-5 h-5" /> Edit
                        </button>
                        <button
                            class="relative overflow-hidden flex items-center gap-[0.55rem] justify-center rounded-2xl border border-sky-300/28 bg-gradient-to-b from-white to-[#fbfdff] font-black px-[0.9rem] py-[0.7rem] transition-all duration-150 shadow-[0_10px_22px_rgba(2,6,23,.06)] hover:-translate-y-px hover:shadow-[0_16px_30px_rgba(2,6,23,.10)] active:translate-y-0">
                            <QrCode class="w-5 h-5" /> My QR
                        </button>
                    </div>
                </div>
                <!-- Snapshot ONLY -->
                <div class="bg-white/16 border border-white/22 backdrop-blur-[14px] rounded-[26px] p-4 sm:p-5">
                    <div class="text-white/95 font-black text-[1.02rem] flex items-center gap-2">
                        <BarChart3 class="w-5 h-5 animate-[dashGrow_1.8s_ease-in-out_infinite]" /> Dashboard Snapshot
                    </div>
                    <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div
                            class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                            <div class="text-xs text-slate-500 font-black">Tickets</div>
                            <div class="mt-1 text-[1.65rem] font-black">
                                <span>{{ props.purchased_tickets ?? 0 }}</span>
                            </div>
                            <div class="text-xs text-slate-500 font-bold">
                                <span class="text-rose-600"> {{ props.cancelled_tickets ?? 0 }}</span> cancelled
                            </div>
                        </div>
                        <div
                            class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                            <div class="text-xs text-slate-500 font-black">Marketplace</div>
                            <div class="mt-1 text-[1.65rem] font-black"><span>{{ props.market_place }}</span></div>
                            <div class="text-xs text-slate-500 font-bold">items purchased</div>
                        </div>
                        <!-- <div
                            class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">

                            <div class="text-xs text-slate-500 font-black">News</div>

                            <div class="mt-1 text-[1.65rem] font-black"><span>0</span></div>

                            <div class="text-xs text-slate-500 font-bold">subscriptions</div>

                        </div> -->
                        <div
                            class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                            <div class="text-xs text-slate-500 font-black">Matches</div>
                            <div class="mt-1 text-[1.65rem] font-black"><span>{{ props.matches }}</span></div>
                            <div class="text-xs text-slate-500 font-bold"><span>{{ props.like_matches ?? 0 }}</span>
                                likes</div>
                        </div>

                        <div
                            class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                            <div class="text-xs text-slate-500 font-black">Gifts</div>
                            <div class="mt-1 text-[1.65rem] font-black"><span>{{ props.gifts_purchased ?? 0 }}</span>
                            </div>
                            <div class="text-xs text-slate-500 font-bold"><span>${{
                                    props.gifts_collected_amount}}</span> collected
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-auto text-white/90 text-xs font-bold flex flex-wrap gap-2">
                    <span
                        class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-white/70 rounded-full px-[0.65rem] py-[0.35rem] bg-white/90 text-xs font-extrabold text-slate-900">
                        <Briefcase class="w-3.5 h-3.5" /> <span>{{ props.user.job ?? '—' }}</span>
                    </span>
                    <span
                        class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-white/70 rounded-full px-[0.65rem] py-[0.35rem] bg-white/90 text-xs font-extrabold text-slate-900">
                        <Users class="w-3.5 h-3.5" /> Gender: <span>{{ props.user.gender ?? '—' }}</span>
                    </span>

                    <span
                        class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border border-white/70 rounded-full px-[0.65rem] py-[0.35rem] bg-white/90 text-xs font-extrabold text-slate-900">
                        <Sparkles class="w-3.5 h-3.5" /> Status: <span>{{ props.user.status ?? '—' }}</span>
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>