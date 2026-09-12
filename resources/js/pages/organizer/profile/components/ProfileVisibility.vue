<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import { toast } from "vue-sonner";
import { Plus, Minus, Loader2 } from 'lucide-vue-next';

const page = usePage()
const props = defineProps<{
    organizer_setting: any
}>();

const form = useForm({
    show_venues_map: props.organizer_setting?.show_venues_map != null
        ? Number(props.organizer_setting.show_venues_map)
        : 1,
    show_followers: props.organizer_setting?.show_followers != null
        ? Number(props.organizer_setting.show_followers)
        : 1,
    show_reviews: props.organizer_setting?.show_reviews != null
        ? Number(props.organizer_setting.show_reviews)
        : 1,
});

const isOpen = ref(!!props.organizer_setting);

const handleSubmit = () => {
    form.post(route('organizer.profile.update', { organizer_profile_type: 'ProfileSetting' }), {
        onSuccess: () => {
            toast.success("Visibility settings updated successfully!");
        },
        onError: (errors) => {
            console.error("Form submission errors:", errors);
            toast.error("Failed to update visibility settings.");
        },
    });
}
</script>

<template>
    <div class="card p-6 bg-white border border-slate-100 rounded-[20px] shadow-sm mb-4">
        <!-- Header matching orgCardHeader reference line 5007 -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h3 class="text-xl font-black text-slate-800">Profile Visibility Settings</h3>
                <div class="h-[3px] rounded-full mt-1.5 max-w-full bg-gradient-to-r from-indigo-500 to-amber-400"></div>
            </div>
            <button @click="isOpen = !isOpen"
                class="h-9 w-9 rounded-full text-white grid place-items-center shrink-0 ml-3 bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20 active:scale-95">
                <Plus v-if="!isOpen" class="w-4 h-4" />
                <Minus v-else class="w-4 h-4" />
            </button>
        </div>

        <p class="text-slate-400 text-sm font-bold mb-4 uppercase tracking-tight" v-if="!isOpen">
            Customize what information is visible on your public organizer profile.
        </p>

        <form v-show="isOpen" @submit.prevent="handleSubmit" class="space-y-6 animate-in fade-in slide-in-from-top-2 duration-300">

            <!-- Venues Map Toggle -->
            <div class="rounded-2xl border border-slate-100 p-5 bg-slate-50/30">
                <p class="font-black text-slate-800 mb-1">Show venues map</p>
                <p class="text-[11px] text-indigo-500 font-bold uppercase tracking-tight mb-3">Show a map at the bottom of your profile page with added venues</p>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 font-bold text-sm cursor-pointer group">
                        <input type="radio" :value="1" v-model="form.show_venues_map" class="h-4 w-4 accent-indigo-600" />
                        <span class="group-hover:text-indigo-600 transition">Show</span>
                    </label>
                    <label class="flex items-center gap-2 font-bold text-sm cursor-pointer group">
                        <input type="radio" :value="0" v-model="form.show_venues_map" class="h-4 w-4 accent-indigo-600" />
                        <span class="group-hover:text-indigo-600 transition">Hide</span>
                    </label>
                </div>
            </div>

            <!-- Followers Toggle -->
            <div class="rounded-2xl border border-slate-100 p-5 bg-slate-50/30">
                <p class="font-black text-slate-800 mb-1">Show followers</p>
                <p class="text-[11px] text-indigo-500 font-bold uppercase tracking-tight mb-3">Show the number and list of people that follow you</p>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 font-bold text-sm cursor-pointer group">
                        <input type="radio" :value="1" v-model="form.show_followers" class="h-4 w-4 accent-indigo-600" />
                        <span class="group-hover:text-indigo-600 transition">Show</span>
                    </label>
                    <label class="flex items-center gap-2 font-bold text-sm cursor-pointer group">
                        <input type="radio" :value="0" v-model="form.show_followers" class="h-4 w-4 accent-indigo-600" />
                        <span class="group-hover:text-indigo-600 transition">Hide</span>
                    </label>
                </div>
            </div>

            <!-- Reviews Toggle -->
            <div class="rounded-2xl border border-slate-100 p-5 bg-slate-50/30">
                <p class="font-black text-slate-800 mb-1">Show reviews</p>
                <p class="text-[11px] text-indigo-500 font-bold uppercase tracking-tight mb-3">Show the reviews that you received for your events</p>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 font-bold text-sm cursor-pointer group">
                        <input type="radio" :value="1" v-model="form.show_reviews" class="h-4 w-4 accent-indigo-600" />
                        <span class="group-hover:text-indigo-600 transition">Show</span>
                    </label>
                    <label class="flex items-center gap-2 font-bold text-sm cursor-pointer group">
                        <input type="radio" :value="0" v-model="form.show_reviews" class="h-4 w-4 accent-indigo-600" />
                        <span class="group-hover:text-indigo-600 transition">Hide</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" :disabled="form.processing"
                    class="px-10 py-2.5 rounded-full font-black text-slate-900 transition hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 bg-amber-400">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    Update
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
/* Animations */
.animate-in {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}
.fade-in {
    animation-name: fadeIn;
}
.slide-in-from-top-2 {
    animation-name: slideInFromTop;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInFromTop {
    from { transform: translateY(-0.5rem); }
    to { transform: translateY(0); }
}
</style>
