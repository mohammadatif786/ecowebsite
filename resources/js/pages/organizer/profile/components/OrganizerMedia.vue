<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import { toast } from "vue-sonner";
import { Plus, Minus, Loader2 } from 'lucide-vue-next';

const page = usePage()
const props = defineProps<{
    organizer_contacts: Record<string, any>
}>()

const form = useForm({
    website: props.organizer_contacts?.website ?? '',
    facebook: props.organizer_contacts?.facebook ?? '',
    twitter: props.organizer_contacts?.twitter ?? '',
    instagram: props.organizer_contacts?.instagram ?? '',
    linkedin: props.organizer_contacts?.linkedin ?? '',
    youtube: props.organizer_contacts?.youtube ?? '',
});

const isOpen = ref(!!props.organizer_contacts);

const handleSubmit = () => {
    form.post(route('organizer.profile.update', { organizer_profile_type: 'profileContacts' }), {
        onSuccess: () => {
            toast.success("Social media handles updated successfully!");
        },
        onError: (errors) => {
            console.error("Form submission errors:", errors);
            toast.error("Failed to update social handles.");
        },
    });
}
</script>

<template>
    <div class="card p-6 bg-white border border-slate-100 rounded-[20px] shadow-sm mb-4">
        <!-- Header matching orgCardHeader reference line 5007 -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h3 class="text-xl font-black text-slate-800">Organizer Social Media Handles</h3>
                <div class="h-[3px] rounded-full mt-1.5 max-w-full bg-gradient-to-r from-indigo-500 to-amber-400"></div>
            </div>
            <button @click="isOpen = !isOpen"
                class="h-9 w-9 rounded-full text-white grid place-items-center shrink-0 ml-3 bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20 active:scale-95">
                <Plus v-if="!isOpen" class="w-4 h-4" />
                <Minus v-else class="w-4 h-4" />
            </button>
        </div>

        <p class="text-slate-400 text-sm font-bold mb-4 uppercase tracking-tight" v-if="!isOpen">
            Add links to your social media profiles to connect with your audience.
        </p>

        <form v-show="isOpen" @submit.prevent="handleSubmit" class="space-y-4 animate-in fade-in slide-in-from-top-2 duration-300">
            <!-- Social Rows matching orgSocialRow reference line 5022 -->

            <div>
                <label class="text-xs font-black text-slate-500 uppercase ml-1">Facebook</label>
                <input v-model="form.facebook" placeholder="https://www.facebook.com"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
            </div>

            <div>
                <label class="text-xs font-black text-slate-500 uppercase ml-1">Twitter</label>
                <input v-model="form.twitter" placeholder="https://www.twitter.com"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
            </div>

            <div>
                <label class="text-xs font-black text-slate-500 uppercase ml-1">Instagram</label>
                <input v-model="form.instagram" placeholder="https://www.instagram.com"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
            </div>

            <div>
                <label class="text-xs font-black text-slate-500 uppercase ml-1">LinkedIn</label>
                <input v-model="form.linkedin" placeholder="www.linkedin.com"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
            </div>

            <div>
                <label class="text-xs font-black text-slate-500 uppercase ml-1">Youtube video url</label>
                <p class="text-[11px] text-indigo-500 font-bold uppercase tracking-tight mt-0.5">Add an activity video: https://www.youtube.com/watch?v=…</p>
                <input v-model="form.youtube" type="url" placeholder="Enter Youtube URL"
                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
            </div>

            <div>
                <label class="text-xs font-black text-slate-500 uppercase ml-1">Website</label>
                <input v-model="form.website" placeholder="https://www.yoursite.com"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
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
