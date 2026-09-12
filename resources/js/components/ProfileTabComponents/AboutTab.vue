<script setup lang="ts">
import { ref } from "vue";
import { IdCard, User, Mail, Cake, Briefcase, BadgeCheck, GraduationCap, Flag, Map, Building2, Users, MessageCircle, Globe2, Eye, Phone, Heart } from 'lucide-vue-next';
import { formatUpdatedAt } from "../../composables/dateformater";

const props = defineProps<{
    user: any;
}>();

const activeTab = ref('about')

const tabs = [
    { id: 'about', name: 'About', icon: IdCard },
    { id: 'contact', name: 'Contact', icon: Phone },
    { id: 'interests', name: 'Interests', icon: Heart }
]

</script>
<template>
    <div class="bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-5">
        <!-- Tab Navigation -->
        <div class="flex space-x-1 border-b border-slate-200 mb-6">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                    'flex items-center gap-2 px-4 py-3 text-sm font-medium transition-all border-b-2',
                    activeTab === tab.id
                        ? 'text-slate-900 border-slate-900'
                        : 'text-slate-500 border-transparent hover:text-slate-700 hover:border-slate-300'
                ]"
            >
                <component :is="tab.icon" class="w-4 h-4" />
                {{ tab.name }}
            </button>
        </div>

        <!-- About Tab -->
        <div v-if="activeTab === 'about'">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg font-black tracking-tight flex items-center gap-2">
                        <IdCard class="w-5 h-5" /> Profile Details
                    </div>
                    <div class="text-sm text-slate-500 mt-1">Personal information and background</div>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-sky-50 border border-sky-100 grid place-items-center">
                    <IdCard class="w-5 h-5 text-sky-700" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <!-- Basic Information -->
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <User class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">Full Name</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.name ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <Mail class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">Email</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.email ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <Cake class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">Birthday</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ formatUpdatedAt(props.user.birthday, false) ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <Briefcase class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">Job</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.job ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <BadgeCheck class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">Status</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.status ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <Users class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">Gender</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.gender ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Education & Location -->
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <GraduationCap class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">University</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.university ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <Flag class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">Country</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.country ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <Map class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">State</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.state ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <Building2 class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">City</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.city ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About Me - spans full width -->
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white md:col-span-2">
                    <div class="p-[14px] flex gap-3 items-start">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <MessageCircle class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">About Me</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.about_me ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preferences -->
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <Globe2 class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">Country of Affiliation</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.link_me_with_country_name ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <Eye class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">Interested In Seeing Person From</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.link_me_with ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Tab -->
        <div v-if="activeTab === 'contact'">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg font-black tracking-tight flex items-center gap-2">
                        <Phone class="w-5 h-5" /> Contact Information
                    </div>
                    <div class="text-sm text-slate-500 mt-1">How to reach you</div>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-sky-50 border border-sky-100 grid place-items-center">
                    <Phone class="w-5 h-5 text-sky-700" />
                </div>
            </div>

            <div class="space-y-4">
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <Mail class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">Email Address</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.email ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <Phone class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">Phone Number</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ props.user.phone_number ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white">
                    <div class="p-[14px] flex gap-3 items-center">
                        <div class="w-[46px] h-[46px] rounded-2xl bg-slate-50 border border-slate-300/25 grid place-items-center text-slate-700 flex-none">
                            <Map class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-slate-500 font-black tracking-[0.08em] uppercase mb-0.5">Location</div>
                            <div class="text-slate-900 font-black text-md leading-tight break-words">
                                {{ [props.user.city, props.user.state, props.user.country].filter(Boolean).join(', ') || '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interests Tab -->
        <div v-if="activeTab === 'interests'">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg font-black tracking-tight flex items-center gap-2">
                        <Heart class="w-5 h-5" /> Interests & Hobbies
                    </div>
                    <div class="text-sm text-slate-500 mt-1">What you like and enjoy</div>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-sky-50 border border-sky-100 grid place-items-center">
                    <Heart class="w-5 h-5 text-sky-700" />
                </div>
            </div>

            <div class="h-fit">
                <div class="text-sm text-slate-500 font-bold text-center py-12" v-if="!props.user.interests || props.user.interests.length === 0">
                    <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-200 grid place-items-center mx-auto mb-4">
                        <Heart class="w-8 h-8 text-slate-400" />
                    </div>
                    <div class="text-lg font-black text-slate-900 mb-2">No interests added yet</div>
                    <div class="text-sm text-slate-500">Add interests to help others know what you like</div>
                </div>
                <div v-else class="space-y-4">
                    <div class="text-xs text-slate-500 font-black mb-3">{{ props.user.interests.length }} Interests</div>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="item in props.user.interests" :key="item"
                            class="inline-flex items-center rounded-full px-3 py-[0.48rem] bg-sky-100/10 border border-sky-300/18 text-sky-900 font-black shadow-[0_10px_22px_rgba(14,165,233,.08)]">
                            {{ item }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>