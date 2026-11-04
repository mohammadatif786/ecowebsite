<script setup lang="ts">
import { ArrowLeft, Check } from 'lucide-vue-next';
import { useCountryStateCity } from "@/composables/useCountryStateCity";
import { ref, watch, onMounted, nextTick } from "vue";
import { useForm } from '@inertiajs/vue3';
import DeleteModal from '../../../components/DeleteModal.vue';


const props = defineProps<{
    activeTab: string;
    merchants?: any[];
}>();

const modelValue = defineModel<{
    _method: string;
    country: string;
    state: string;
    city: string;
    st_name: string;
    st_owner: string;
    st_pickups: string[];
    merchant_type: string;
}>({
    default: {
        _method: 'POST',
        country: "",
        state: "",
        city: "",
        st_name: "",
        st_pickups: [""],
        st_owner: "",
        merchant_type: "group",
    }
});

const form = useForm(modelValue.value);

const {
    countries,
    states,
    cities,
    fetchCountries,
    fetchStates,
    fetchCities,
    resetStatesAndCities,
    resetCities,
    isLoadingState,
    isLoadingCity
} = useCountryStateCity();


onMounted(async () => {
    await fetchCountries();

    if (countries.value.length > 0) {
        if (form.country && !countries.value.some(c => c.value === form.country)) {
            const match = countries.value.find(c => c.label === form.country);
            if (match) form.country = match.value as any;
        }

        if (form.country) {
            await fetchStates(form.country);

            if (form.state && !states.value.some(s => s.value === form.state)) {
                const sMatch = states.value.find(s => s.label === form.state);
                if (sMatch) form.state = sMatch.value as any;
            }

            await fetchCities(form.country, form.state);

            if (form.city && !cities.value.some(ci => ci.value === form.city)) {
                const cMatch = cities.value.find(ci => ci.label === form.city);
                if (cMatch) form.city = cMatch.value as any;
            }
        }
    }

    await nextTick();
});

const countryNormalized = ref(false);
const stateNormalized = ref(false);
const cityNormalized = ref(false);
const selectedProductId = ref<number | null>(null);

const selectStore = (merchant: any) => {
    selectedProductId.value = merchant.id;
    form.st_name = merchant.name;
    form.st_owner = merchant.owner_name;
    form.merchant_type = merchant.merchant_type;
    if (merchant.country) form.country = merchant.country;
    if (merchant.state) form.state = merchant.state;
    if (merchant.city) form.city = merchant.city;
    if (merchant.pickup_locations) {
        try {
            const parsed = JSON.parse(merchant.pickup_locations);
            form.st_pickups = parsed;
        } catch (e) {
            // Not JSON, use as-is
            form.st_pickups = merchant.pickup_locations;
        }
    }
}
watch([countries, () => form.country], async ([countryOptions, currentCountry]) => {
    if (countryNormalized.value) return;
    if (!countryOptions?.length || !currentCountry) return;
    if (!countryOptions.some((c: any) => c.value === currentCountry)) {
        const match = countryOptions.find((c: any) => c.label === currentCountry);
        if (match) form.country = match.value as any;
    }
    countryNormalized.value = true;
    await fetchStates(form.country);
}, { immediate: false });

watch([states, () => form.state, () => form.country], async ([stateOptions, currentState, currentCountry]) => {
    if (!currentCountry || !stateOptions) return;
    if (stateOptions.length === 0) return;
    if (!stateNormalized.value && currentState) {
        if (!stateOptions.some((s: any) => s.value === currentState)) {
            const sMatch = stateOptions.find((s: any) => s.label === currentState);
            if (sMatch) form.state = sMatch.value as any;
        }
        stateNormalized.value = true;
        await fetchCities(form.country, form.state);
    }
}, { immediate: false });

watch([cities, () => form.city, () => form.state, () => form.country], ([cityOptions, currentCity, currentState, currentCountry]) => {
    if (!currentCountry || !currentState || !cityOptions) return;
    if (cityOptions.length === 0 || cityNormalized.value || !currentCity) return;
    if (!cityOptions.some((ci: any) => ci.value === currentCity)) {
        const cMatch = cityOptions.find((ci: any) => ci.label === currentCity);
        if (cMatch) form.city = cMatch.value as any;
    }
    cityNormalized.value = true;
}, { immediate: false });

const handleCountryChange = () => {
    resetStatesAndCities();
    fetchStates(form.country);
};

const handleStateChange = () => {
    resetCities();
    fetchCities(form.country, form.state);
};
const emit = defineEmits(['back', 'refresh', 'close']);

const validateGroup = () => {
    const errors: Record<string, string> = {};

    if (!form.st_name?.trim()) errors.st_name = 'Group name is required.';
    if (!form.st_owner?.trim()) errors.st_owner = 'Owner / leader is required.';
    if (!form.country) errors.country = 'Country is required.';
    if (!form.state) errors.state = 'State is required.';
    if (!form.city) errors.city = 'City is required.';

    form.clearErrors();
    if (Object.keys(errors).length) {
        form.setError(errors);
        return false;
    }

    return true;
};

const handleSaveStore = () => {
    if (!validateGroup()) return;

    if (selectedProductId.value) {
        form._method = 'PUT';
        form.post(route('frontend.update.store', selectedProductId.value), {
            forceFormData: true,
            onSuccess: () => {
                emit('back');
                emit('close');
                if (window.toast) window.toast(form.st_name + ' Group Created ');
            }
            ,
            onError: async () => {
                await fetchCountries();
            }
        });
    } else {
        form._method = 'POST';
        form.post(route('frontend.save.store'), {
            forceFormData: true,
            onSuccess: () => {
                emit('back');
                emit('close');
                if (window.toast) window.toast(form.st_name + ' Group Created ');

            },
            onError: async () => {
                await fetchCountries();
            }
        });
    }
}

// delete store logic
const deleteModalRef = ref();

const deleteStore = () => {
    if (!selectedProductId.value) return;
    const selectedMerchant = props.merchants?.find(m => m.id === selectedProductId.value);
    deleteModalRef.value.open(selectedMerchant?.name || 'this store', selectedProductId.value);
};

const confirmDelete = (storeId: number) => {
    form.delete(route('frontend.destroy.store', storeId), {
        onSuccess: () => {
            clearSelection();
            emit('back');
            emit('close')
            deleteModalRef.value.close();
            if (window.toast) window.toast('Group Deleted');

        }
    });
};


const clearSelection = () => {
    selectedProductId.value = null;
    form.reset();
};
</script>

<template>
    <div class="grid gap-3" v-if="props.activeTab === 'group'">

        <!-- list of store -->
        <div v-if="props.merchants && props.merchants.length > 0" class="mb-4   overflow-hidden">
            <div class="font-black mb-2">My Group (Select to Edit)</div>
            <div class="flex gap-3 overflow-x-auto pb-3 custom-scrollbar">
                <div class="flex-shrink-0 cursor-pointer opacity-70 hover:opacity-100 transition-all duration-200 mt-2"
                    @click="clearSelection">
                    <div
                        class="w-20 h-20 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50">
                        <span class="text-2xl text-gray-400">+</span>
                    </div>
                    <!-- <div class="text-[10px] font-bold mt-1 text-center w-20">New</div> -->
                </div>
                <!-- merchant items -->
                <div v-for="merchant in props.merchants.filter(m => m.merchant_type === 'group')"
                    @click="selectedProductId = merchant.id"
                    class="flex-shrink-0 mt-2  cursor-pointer transition-all duration-200 h-20 w-20" :class="[
                        selectedProductId === merchant.id
                            ? ''
                            : 'opacity-70 hover:opacity-100'
                    ]">
                    <div class="rounded-xl shadow-sm p-3 bg-white text-center"
                        :class="selectedProductId === merchant.id ? '' : 'border'" :style="selectedProductId === merchant.id ? {
                            'outline': '1px solid transparent',
                            'outline-offset': '-1px',
                            'box-shadow': '0 0 0 2px rgb(14, 165, 233), 0 0 0 4px rgb(34, 197, 94)'
                        } : {}" @click="selectStore(merchant)">
                        <!-- Merchant Type -->
                        <div class="text-[10px] text-gray-400 uppercase">
                            {{ merchant.merchant_type }}
                        </div>

                        <!-- Name -->
                        <div class="text-sm font-bold truncate">
                            {{ merchant.name }}
                        </div>

                        <!-- Owner -->
                        <div class="text-[11px] text-gray-500 truncate">
                            {{ merchant.owner_name }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-2">
            <div>
                <div class="font-black">Group Name</div>
                <input class="input mt-1" id="st_name" v-model="form.st_name" placeholder="e.g., Island Streetwear" />
                <p v-if="form.errors.st_name" class="text-sm text-red-500 mt-1">{{ form.errors.st_name }}</p>
            </div>

            <div>
                <div class="font-black">Owner / Leader</div>
                <input class="input mt-1" id="st_owner" v-model="form.st_owner" placeholder="e.g., Jordan" />
                <p v-if="form.errors.st_owner" class="text-sm text-red-500 mt-1">{{ form.errors.st_owner }}</p>
            </div>

        </div>
        <div class="grid md:grid-cols-3 gap-2">
            <div>
                <div class="font-black">Country</div>
                <select class="input mt-1" id="st_country" v-model="form.country" @change="handleCountryChange">
                    <option value="">Select Country</option>
                    <option v-for="country in countries" :key="country.value" :value="country.value">
                        {{ country.label }}
                    </option>
                </select>
                <p v-if="form.errors.country" class="text-sm text-red-500 mt-1">{{ form.errors.country }}</p>
            </div>

            <div>
                <div class="font-black">State</div>
                <select class="input mt-1" id="st_state" v-model="form.state" @change="handleStateChange"
                    :disabled="!states.length && !isLoadingState">
                    <option value="">Select State</option>
                    <option v-for="state in states" :key="state.value" :value="state.value">
                        {{ state.label }}
                    </option>
                </select>
                <p v-if="!isLoadingState && states.length === 0 && form.country" class="text-sm text-red-500 mt-1">No
                    states found</p>
                <p v-if="form.errors.state" class="text-sm text-red-500 mt-1">{{ form.errors.state }}</p>

            </div>

            <div>
                <div class="font-black">City</div>
                <select class="input mt-1" id="st_city" v-model="form.city"
                    :disabled="!cities.length && !isLoadingCity">
                    <option value="">Select City</option>
                    <option v-for="city in cities" :key="city.value" :value="city.value">
                        {{ city.label }}
                    </option>
                </select>
                <p v-if="!isLoadingCity && cities.length === 0 && form.state" class="text-sm text-red-500 mt-1">No
                    cities found</p>
                <p v-if="form.errors.city" class="text-sm text-red-500 mt-1">{{ form.errors.city }}</p>

            </div>
        </div>

        <div>
            <div class="font-black">Pickup Locations (comma-separated)</div>
            <input class="input mt-1" id="st_pickups" v-model="form.st_pickups"
                placeholder="e.g., Downtown Nassau Pickup, Harbour Bay Pickup" />
            <p v-if="form.errors.st_pickups" class="text-sm text-red-500 mt-1">{{ form.errors.st_pickups }}</p>
            <div class="tiny mt-1" style="color:#64748b;">Pickup locations live on the Group (not on each product).
            </div>
        </div>

        <div class="flex gap-2">
            <button class="btn flex-1" id="st_save" @click="handleSaveStore">
                <span class="inline-flex items-center gap-2">
                    <Check class="w-5 h-5" />
                    {{ selectedProductId ? "Update Group" : "Save Group" }}
                </span>
            </button>
            <button v-if="selectedProductId" class="btn2 flex-1 text-red-600 border-red-200 hover:bg-red-50"
                @click="deleteStore" :disabled="form.processing">
                <span class="inline-flex items-center gap-2">
                    <Trash2Icon class="w-5 h-5" />
                    Delete
                </span>
            </button>
            <button class="btn2 flex-1" id="st_cancel" @click="$emit('back')">
                <span class="inline-flex items-center gap-2">
                    <ArrowLeft class="w-5 h-5" />
                    Back
                </span>
            </button>
        </div>
        <!-- Add DeleteModal at the end of template -->
        <DeleteModal ref="deleteModalRef" title="Delete Store"
            message="Are you sure you want to delete this store? This action cannot be undone."
            @confirm="confirmDelete" />
    </div>
</template>
<style scoped>
body {
    background: #f5f7fb;
    color: #0f172a;
}

.card {
    background: #ffffff;
    border-radius: 22px;
    box-shadow: 0 18px 40px rgba(2, 6, 23, .10);
    border: 1px solid rgba(148, 163, 184, .35);
}

.chip {
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 999px;
    padding: .35rem .7rem;
    font-weight: 800;
    font-size: .75rem;
}

.btn {
    background: linear-gradient(135deg,
            rgba(14, 165, 233, 1),
            rgba(34, 197, 94, 1));
    color: #ffffff;
    border-radius: 16px;
    font-weight: 900;
    padding: .75rem 1rem;
    box-shadow: 0 16px 35px rgba(14, 165, 233, .22);
}

.btn:disabled {
    opacity: .5;
    filter: grayscale(.2);
    cursor: not-allowed;
}

.btn2 {
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 16px;
    font-weight: 900;
    padding: .75rem 1rem;
    background: #ffffff;
}

.input {
    width: 100%;
    border: 1px solid rgba(148, 163, 184, .45);
    border-radius: 16px;
    padding: .75rem .9rem;
    outline: none;
    background: #ffffff;
}

.input:focus {
    border-color: rgba(14, 165, 233, .7);
    box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
}

.soft {
    background: linear-gradient(180deg,
            rgba(14, 165, 233, .10),
            rgba(34, 197, 94, .06));
    border: 1px solid rgba(14, 165, 233, .18);
}

.glow {
    box-shadow:
        0 0 0 4px rgba(14, 165, 233, .08),
        0 20px 40px rgba(2, 6, 23, .10);
}

.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(2, 6, 23, .55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 18px;
}

.modal {
    width: min(920px, 100%);
    max-height: 90vh;
    overflow: auto;
}

.tiny {
    font-size: .75rem;
}

.tag {
    font-size: .72rem;
    font-weight: 900;
    padding: .30rem .55rem;
    border-radius: 999px;
}

.badge-admin {
    background: #0f172a;
    color: #ffffff;
}

.badge-store {
    background: #0284c7;
    color: #ffffff;
}

.badge-group {
    background: #a855f7;
    color: #ffffff;
}

.badge-individual {
    background: #e2e8f0;
    color: #0f172a;
}

/* Network-safe utility fallbacks */

.min-h-screen {
    min-height: 100vh;
}

.sticky {
    position: sticky;
}

.top-0 {
    top: 0;
}

.z-50 {
    z-index: 50;
}

.mx-auto {
    margin-left: auto;
    margin-right: auto;
}

.px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
}

.py-3 {
    padding-top: .75rem;
    padding-bottom: .75rem;
}

.py-6 {
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
}

.border-b {
    border-bottom: 1px solid rgba(148, 163, 184, .35);
}

.flex {
    display: flex;
}

.grid {
    display: grid;
}

.gap-2 {
    gap: .5rem;
}

.gap-3 {
    gap: .75rem;
}

.gap-4 {
    gap: 1rem;
}

.gap-6 {
    gap: 1.5rem;
}

.items-center {
    align-items: center;
}

.items-start {
    align-items: flex-start;
}

.justify-between {
    justify-content: space-between;
}

.ml-auto {
    margin-left: auto;
}

.text-sm {
    font-size: .875rem;
}

.text-lg {
    font-size: 1.125rem;
}

.text-xl {
    font-size: 1.25rem;
}

.font-black {
    font-weight: 900;
}

.overflow-hidden {
    overflow: hidden;
}

.w-full {
    width: 100%;
}

.text-center {
    text-align: center;
}

.col-span-full {
    grid-column: 1 / -1;
}

@media (min-width: 640px) {
    .sm\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 768px) {
    .md\:p-5 {
        padding: 1.25rem;
    }

    .md\:p-6 {
        padding: 1.5rem;
    }

    .md\:flex-row {
        flex-direction: row;
    }

    .md\:items-end {
        align-items: flex-end;
    }

    .md\:w-56 {
        width: 14rem;
    }

    .md\:w-auto {
        width: auto;
    }

    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .md\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .lg\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
</style>
