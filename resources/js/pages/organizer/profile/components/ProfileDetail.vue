<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { useCountryStateCity } from "@/composables/useCountryStateCity";
import { Plus, Minus, Loader2 } from 'lucide-vue-next';

const page = usePage()

const props = defineProps<{
    organizer_detail: any;
}>();

const {
    countries,
    states,
    cities,
    fetchCountries,
    fetchStates,
    fetchCities,
    resetStatesAndCities,
    resetCities
} = useCountryStateCity();

const form = useForm({
    organizer_name: props.organizer_detail?.organizer_name ?? '',
    date_of_birth: props.organizer_detail?.date_of_birth ? new Date(props.organizer_detail.date_of_birth).toISOString().split('T')[0] : '',
    nationality: props.organizer_detail?.nationality ?? '',
    address: props.organizer_detail?.address ?? '',
    telephone: props.organizer_detail?.telephone ?? '',
    ssn: props.organizer_detail?.ssn ?? '',
    email: props.organizer_detail?.contacts?.email ?? '',
    country: props.organizer_detail?.contacts?.country ?? '',
    state: props.organizer_detail?.contacts?.state ?? '',
    city: props.organizer_detail?.contacts?.city ?? '',
})

const isOpen = ref(!!props.organizer_detail);

onMounted(async () => {
    await fetchCountries();
    if (form.country) {
        await fetchStates(form.country);
        if (form.state) {
            await fetchCities(form.country, form.state);
        }
    }
});

const handleCountryChange = () => {
    resetStatesAndCities();
    fetchStates(form.country);
};

const handleStateChange = () => {
    resetCities();
    fetchCities(form.country, form.state);
};

const handleSubmit = () => {
    form.post(route('organizer.profile.update', { organizer_profile_type: 'profileDetail' }), {
        onSuccess: () => {
            toast.success("Profile details updated successfully!");
        },
        onError: (errors) => {
            console.error("Form submission errors:", errors);
            toast.error("Please check the form for errors.");
        },
    });
}
</script>

<template>
    <div class="card p-6 bg-white border border-slate-100 rounded-[20px] shadow-sm mb-4">
        <!-- Header matching orgCardHeader reference line 5007 -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h3 class="text-xl font-black text-slate-800">Profile Details</h3>
                <div class="h-[3px] rounded-full mt-1.5 max-w-full bg-gradient-to-r from-indigo-500 to-amber-400"></div>
            </div>
            <button @click="isOpen = !isOpen"
                class="h-9 w-9 rounded-full text-white grid place-items-center shrink-0 ml-3 bg-indigo-600 hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20 active:scale-95">
                <Plus v-if="!isOpen" class="w-4 h-4" />
                <Minus v-else class="w-4 h-4" />
            </button>
        </div>

        <p class="text-slate-400 text-sm font-bold mb-4 uppercase tracking-tight" v-if="!isOpen">
            Enter your organiser name, location, and contact details.
        </p>

        <form v-show="isOpen" @submit.prevent="handleSubmit" class="space-y-4 animate-in fade-in slide-in-from-top-2 duration-300">
            <!-- Form Fields matching reference style -->
            <div>
                <label class="text-xs font-black text-slate-500 uppercase ml-1">Organiser name</label>
                <input type="text" v-model="form.organizer_name"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                <p v-if="form.errors.organizer_name" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors.organizer_name }} </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-black text-slate-500 uppercase ml-1">Date of Birth</label>
                    <input type="date" v-model="form.date_of_birth"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                    <p v-if="form.errors.date_of_birth" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors.date_of_birth }} </p>
                </div>

                <div>
                    <label class="text-xs font-black text-slate-500 uppercase ml-1">Telephone</label>
                    <input type="tel" placeholder="304-34532" v-model="form.telephone"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                    <p v-if="form.errors.telephone" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors.telephone }} </p>
                </div>
            </div>

            <div>
                <label class="text-xs font-black text-slate-500 uppercase ml-1">Social Security Number / National ID</label>
                <input type="text" placeholder="Enter SSN or National ID" v-model="form.ssn"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                <p v-if="form.errors.ssn" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors.ssn }} </p>
            </div>

            <div>
                <label class="text-xs font-black text-slate-500 uppercase ml-1">Email</label>
                <input type="email" v-model="form.email" placeholder="Enter email address"
                    class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                <p v-if="form.errors.email" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors.email }} </p>
            </div>

            <!-- Location Block matching reference line 5015 -->
            <div class="rounded-2xl border border-slate-100 p-5 bg-slate-50/30">
                <p class="font-black text-slate-800 mb-4 flex items-center gap-2">
                    <span class="text-xl text-indigo-500">○</span> Location
                </p>

                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Nationality</label>
                        <input type="text" placeholder="Enter nationality" v-model="form.nationality"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition" />
                        <p v-if="form.errors.nationality" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors.nationality }} </p>
                    </div>

                    <div>
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Address</label>
                        <textarea rows="2" placeholder="Enter full address" v-model="form.address"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold outline-none focus:border-indigo-400 transition resize-none"></textarea>
                        <p v-if="form.errors.address" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors.address }} </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-xs font-black text-slate-500 uppercase ml-1">Country</label>
                            <select v-model="form.country" @change="handleCountryChange"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-bold outline-none focus:border-indigo-400 bg-white">
                                <option value="">Select Country</option>
                                <option v-for="country in countries" :key="country.value" :value="country.value">
                                    {{ country.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.country" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors.country }} </p>
                        </div>

                        <div>
                            <label class="text-xs font-black text-slate-500 uppercase ml-1">State / Island</label>
                            <select v-model="form.state" @change="handleStateChange" :disabled="!states.length"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-bold outline-none focus:border-indigo-400 bg-white disabled:bg-slate-100 disabled:text-slate-400">
                                <option value="">Select State</option>
                                <option v-for="state in states" :key="state.value" :value="state.value">
                                    {{ state.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.state" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors.state }} </p>
                        </div>

                        <div>
                            <label class="text-xs font-black text-slate-500 uppercase ml-1">City / Settlement</label>
                            <select v-model="form.city" :disabled="!cities.length"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-bold outline-none focus:border-indigo-400 bg-white disabled:bg-slate-100 disabled:text-slate-400">
                                <option value="">Select City</option>
                                <option v-for="city in cities" :key="city.value" :value="city.value">
                                    {{ city.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.city" class="mt-1 text-xs text-rose-500 font-bold ml-1"> {{ form.errors.city }} </p>
                        </div>
                    </div>
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
