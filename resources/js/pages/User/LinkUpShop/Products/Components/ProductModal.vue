<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import { formatPrice } from '@/composables/formatPrice';
import { ArrowLeftIcon, HeartIcon, PlusIcon, UserIcon } from 'lucide-vue-next';
import axios from 'axios';
import { toast } from 'vue-sonner';

declare global {
    interface Window {
        lucide?: {
            createIcons: () => void;
        };
    }
}

const props = defineProps<{
    product: any;
    favoriteSellerIds: number[];
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'add-to-cart', productId: number): void;
    (e: 'update:favoriteSellerIds', ids: number[]): void;
}>();

const countries = ref<Record<string, string>>({});
const states = ref<Record<string, Record<string, string>>>({});

onMounted(async () => {
    try {
        const response = await fetch('/proxy/countries');
        const data = await response.json();

        let countriesList = [];
        if (data.countries) {
            countriesList = data.countries;
        } else if (Array.isArray(data)) {
            countriesList = data;
        } else if (data.data && Array.isArray(data.data)) {
            countriesList = data.data;
        }

        countries.value = countriesList.reduce((acc: Record<string, string>, country: any) => {
            const code = country.iso2 || country.code || country.country_code || country.id;
            const name = country.name || country.country_name || country.title;

            if (code && name) {
                acc[code] = name;
            }
            return acc;
        }, {});

    } catch (error) {
        console.error('Failed to fetch countries:', error);
    }
});

const stateNames = ref<Record<string, string>>({});

const fetchStatesForCountry = async (countryCode: string) => {
    if (states.value[countryCode]) {
        return states.value[countryCode];
    }

    try {
        const response = await fetch(`/proxy/states/${countryCode}`);
        const data = await response.json();

        let statesList = [];
        if (data.states) {
            statesList = data.states;
        } else if (Array.isArray(data)) {
            statesList = data;
        } else if (data.data && Array.isArray(data.data)) {
            statesList = data.data;
        }

        const stateMap = statesList.reduce((acc: Record<string, string>, state: any) => {
            const stateCode = state.iso2 || state.code || state.id;
            const stateName = state.name || state.state_name || state.title;

            if (stateCode && stateName) {
                acc[stateCode] = stateName;
            }
            return acc;
        }, {});

        states.value[countryCode] = stateMap;

        return stateMap;
    } catch (error) {
        console.error(`Failed to fetch states for ${countryCode}:`, error);
        return {};
    }
};

watch(() => props.product, async (newProduct) => {
    if (newProduct?.merchant?.country) {
        const countryCode = newProduct.merchant.country;
        const stateCode = newProduct.merchant.state;

        if (countryCode && stateCode) {
            const countryStates = await fetchStatesForCountry(countryCode);

            let stateName = countryStates[stateCode];

            if (!stateName && stateCode) {
                stateName = countryStates[stateCode.toUpperCase()];
            }
            if (!stateName && stateCode) {
                stateName = countryStates[stateCode.toLowerCase()];
            }

            const key = `${countryCode}_${stateCode}`;
            stateNames.value[key] = stateName || stateCode;
        }
    }
}, { immediate: true });

const badge = computed(() => {
    if (!props.product) return { text: "Individual", cls: "tag badge-individual" };

    const type = props.product?.listing_type || "Individual";
    if (type === "Administrative") return { text: "Admin", cls: "tag badge-admin" };
    if (type === "Store") return { text: "Store", cls: "tag badge-store" };
    if (type === "Carnival Group") return { text: "Carnival Group", cls: "tag badge-group" };
    return { text: "Individual", cls: "tag badge-individual" };
});

const sellerName = computed(() => {
    let sellername = ""
    if (!props.product) return "";

    if (props.product.listing_type === "Store") {
        sellername = props.product.merchant?.name;
    }
    if (props.product.listing_type === "Carnival Group") {
        sellername = props.product.merchant?.name;
    }
    if (props.product.listing_type === "Administrative") {
        sellername = "LinkUp Admin";
    }
    if (props.product.listing_type === "Individual") {
        sellername = "Individual Seller";
    }
    return sellername;
});

const sellerLocation = computed(() => {
    if (!props.product) return "";

    if (props.product.listing_type === "Store") {
        const merchant = props.product.merchant;
        if (!merchant) return "";
        const city = merchant.city || "";
        const state = sellerState.value || "";
        const country = sellerCountry.value || "";
        return [city, state, country].filter(Boolean).join(", ");
    }
    if (props.product.listing_type === "Carnival Group") {
        const merchant = props.product.merchant;
        if (!merchant) return "";
        const city = merchant.city || "";
        const state = sellerState.value || "";
        const country = sellerCountry.value || "";
        return [city, state, country].filter(Boolean).join(", ");
    }
    if (props.product.listing_type === "Administrative") {
        return "LinkUp Network";
    }
    if (props.product.listing_type === "Individual") {
        return "Individual Seller";
    }
    return "";
});

const pickupLocations = computed(() => {
    if (!props.product) return [];

    if (props.product.listing_type === "Store") {
        const locations = props.product.merchant?.pickup_locations || [];
        if (typeof locations === 'string') {
            return locations.split(',').map(loc => loc.trim().replace(/^["']|["']$/g, '')).filter(loc => loc);
        }
        return locations;
    }
    if (props.product.listing_type === "Carnival Group") {
        const locations = props.product.merchant?.pickup_locations || [];
        if (typeof locations === 'string') {
            return locations.split(',').map(loc => loc.trim().replace(/^["']|["']$/g, '')).filter(loc => loc);
        }
        return locations;
    }
    if (props.product.listing_type === "Administrative") {
        return ["LinkUp HQ Pickup"];
    }
    if (props.product.listing_type === "Individual") {
        return ["Individual Seller"];
    }
    return [];
});

const pickupHint = computed(() => {
    const pickups = props.product.listing_type;
    return (pickups === "Store" || pickups === "Carnival Group") ? "Pickup available" : "Delivery only";
});

const subtitle = computed(() => {
    if (!props.product) return "";
    if (showSeller.value) {
        return sellerLocation.value || "";
    }
    const parts = [
        props.product.category?.name || "Other",
        `$${formatPrice(props.product.price || 0)}`,
        sellerName.value
    ];
    if (sellerLocation.value) parts.push(sellerLocation.value);
    return parts.join(" • ");
});

const showSeller = ref(false);

const openSeller = async () => {
    showSeller.value = true;
    await nextTick();
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
};

const backToProduct = async () => {
    showSeller.value = false;
    await nextTick();
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
};

const sellerCountry = computed(() => {
    const p = props.product;
    if (!p) return '';
    if (p.listing_type === 'Store') {
        const countryCode = p.merchant?.country || '';
        return countries.value[countryCode] || countryCode;
    }
    if (p.listing_type === 'Carnival Group') {
        const countryCode = p.merchant?.country || '';
        return countries.value[countryCode] || countryCode;
    }
    if (p.listing_type === 'Administrative') return '—';
    return '';
});

const sellerState = computed(() => {
    const p = props.product;
    if (!p) return '';

    if (p.listing_type === 'Store') {
        const countryCode = p.merchant?.country || '';
        const stateCode = p.merchant?.state || '';

        if (!countryCode || !stateCode) return stateCode;

        const key = `${countryCode}_${stateCode}`;
        return stateNames.value[key] || stateCode;
    }

    if (p.listing_type === 'Carnival Group') {
        const countryCode = p.merchant?.country || '';
        const stateCode = p.merchant?.state || '';

        if (!countryCode || !stateCode) return stateCode;

        const key = `${countryCode}_${stateCode}`;
        return stateNames.value[key] || stateCode;
    }

    if (p.listing_type === 'Administrative') return '—';
    return '';
});

const sellerCity = computed(() => {
    const p = props.product;
    if (!p) return '';
    if (p.listing_type === 'Store') return p.merchant?.city || '';
    if (p.listing_type === 'Carnival Group') return p.merchant?.city || '';
    if (p.listing_type === 'Administrative') return '—';
    return '';
});

const closeModal = () => {
    emit('close');
};

const addToCart = () => {
    if (props.product) {
        emit('add-to-cart', props.product.id);
    }
};
const onBackdropClick = (event: MouseEvent) => {
    if (event.target === event.currentTarget) {
        closeModal();
    }
};

watch(() => props.show, async (newShow) => {

    if (newShow) {
        showSeller.value = false;
        await nextTick();
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }
});

const AddConditionToDisplayHideSellerLocation = computed(() => {
    let sellerLocation = ""
    if (!props.product) {
        return "";
    }
    if (props.product.listing_type === "Store") {
        sellerLocation = 'Store';
    } else if (props.product.listing_type === "Carnival Group") {
        sellerLocation = 'Carnival Group';
    } else if (props.product.listing_type === "Administrative") {
        sellerLocation = 'Administrative';
    } else if (props.product.listing_type === "Individual") {
        sellerLocation = 'Individual';
    } else {
        sellerLocation = props.product.listing_type || 'Unknown';
    }
    return sellerLocation;
});

const followSeller = async () => {
    if (!props.product) return;

    const sellerId = props.product.user_id;

    const { data } = await axios.post(
        route('frontend.products.toggle-favorite', { seller_id: sellerId })
    );

    if (data.status === false) {
        toast.error(data.message);
        return;
    }

    const alreadyFavorite = props.favoriteSellerIds?.includes(sellerId);

    let newFavoriteIds = [...props.favoriteSellerIds];

    if (alreadyFavorite) {
        newFavoriteIds = newFavoriteIds.filter(id => id !== sellerId);
    } else {
        newFavoriteIds.push(sellerId);
    }

    emit('update:favoriteSellerIds', newFavoriteIds);

    toast.success(data.message);
};
</script>

<template>
    <div v-if="show" class="product-modal-backdrop" @click="onBackdropClick">
        <div class="card modal p-4 md:p-6">
            <!-- Modal Header -->
            <div class="flex items-start gap-3">
                <div class="w-30 h-30 rounded-2xl grid place-items-center" stype="margin-top: -30px;">
                    <img src="/storage/avatars/marketplacelog.png" alt="Marketplace Logo" class="w-full h-full" />
                </div>
                <div class="flex-1">
                    <div class="text-xl font-black">{{ showSeller ? (sellerName || "—") : (product?.name || "Untitled")
                        }}</div>
                    <div class="text-sm mt-1" style="color:#64748b;">{{ subtitle }}</div>
                </div>
                <button class="btn2" @click="closeModal">
                    <span class="inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            data-lucide="x" class="lucide lucide-x w-5 h-5">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>Close
                    </span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="mt-5">
                <div :class="['grid gap-4', showSeller ? '' : 'md:grid-cols-2']">
                    <!-- Product Image -->
                    <div v-if="!showSeller" class="card overflow-hidden">
                        <img :src="product?.cover_image || ''" :alt="product?.name || 'Product image'"
                            class="w-full h-full object-cover" />
                        <div class="p-4 flex items-center gap-2">
                            <span :class="badge.cls">{{ badge.text }}</span>
                            <span class="tag"
                                style="background:rgba(14,165,233,.12); color:#0369a1; border:1px solid rgba(14,165,233,.18);">
                                Availability: ALL
                            </span>
                            <span class="tag"
                                style="background:rgba(34,197,94,.10); color:#166534; border:1px solid rgba(34,197,94,.16);">
                                {{ pickupHint }}
                            </span>
                            <span v-if="product?.qty <= 0" class="tag"
                                style="background:#ef4444; color:white; border:1px solid #dc2626;">
                                Sold Out
                            </span>
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="card p-4">
                        <div v-if="!showSeller">
                            <div class="font-black text-lg">Description</div>
                            <div class="mt-2" style="color:#64748b; line-height:1.5;">
                                {{ product?.description || "—" }}
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <div class="card p-3">
                                    <div class="tiny" style="color:#64748b;">Seller</div>
                                    <div class="font-black">{{ sellerName }}</div>
                                </div>
                                <div class="card p-3">
                                    <div class="tiny" style="color:#64748b;">Listing Type</div>
                                    <div class="font-black">{{ product?.listing_type || "Individual" }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2" v-if="!showSeller">
                            <button class="btn flex-1" @click="addToCart" :disabled="product?.qty <= 0">
                                <span class="inline-flex items-center gap-2">
                                    <PlusIcon class="w-5 h-5" /> {{ product?.qty <= 0 ? 'Sold Out' : 'Add to Cart' }}
                                        </span>
                            </button>
                            <div class="relative group inline-block">

                                <button class="btn flex-1 w-full" @click="followSeller">
                                    <span class="inline-flex items-center gap-2">
                                        <HeartIcon class="w-5 h-5" /> {{ favoriteSellerIds.includes(product?.user_id) ?
                                            'Following' : 'Follow' }} seller
                                    </span>
                                </button>

                                <!-- Tooltip -->
                                <div
                                    class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 hidden group-hover:block bg-black text-white text-xs px-2 py-1 rounded shadow">
                                    Follow this seller to get updates
                                </div>

                            </div>
                            <button class="btn2 flex-1" @click="openSeller">
                                <span class="inline-flex items-center gap-2">
                                    <UserIcon class="w-5 h-5" /> Seller
                                </span>
                            </button>
                        </div>

                        <!-- Seller Details Panel -->
                        <div v-if="showSeller" class="mt-4 card p-3">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="font-black text-lg">Seller Details</div>
                                    <div class="text-sm mt-1" style="color:#64748b;">Listing Type: <span
                                            class="font-black">{{ product?.listing_type || 'Individual' }}</span></div>
                                </div>
                                <button class="btn2" @click="backToProduct">
                                    <span class="inline-flex items-center gap-2">
                                        <ArrowLeftIcon class="w-5 h-5" /> Back
                                    </span>
                                </button>
                            </div>

                            <div class="mt-3 grid grid-cols-3 gap-2"
                                v-if="(AddConditionToDisplayHideSellerLocation === 'Store' || AddConditionToDisplayHideSellerLocation === 'Carnival Group')">
                                <div class="pill">
                                    <div class="tiny" style="color:#64748b;">Country</div>
                                    <div class="font-black">{{ sellerCountry || '—' }}</div>
                                </div>
                                <div class="pill">
                                    <div class="tiny" style="color:#64748b;">State</div>
                                    <div class="font-black">{{ sellerState || '—' }}</div>
                                </div>
                                <div class="pill">
                                    <div class="tiny" style="color:#64748b;">City</div>
                                    <div class="font-black">{{ sellerCity || '—' }}</div>
                                </div>
                            </div>

                            <div class="mt-3 card p-3">
                                <div class="font-black">Pickup locations</div>
                                <div class="mt-2 text-sm" style="color:#64748b;">
                                    <div v-if="pickupLocations.length > 0">
                                        <div
                                            v-if="(AddConditionToDisplayHideSellerLocation === 'Store' || AddConditionToDisplayHideSellerLocation === 'Carnival Group')">
                                            <div class="mt-3 grid grid-cols-3 gap-2" v-for="location in pickupLocations"
                                                :key="location">• {{ location }}</div>
                                        </div>
                                        <div v-else>
                                            No pickup locations.
                                        </div>
                                    </div>
                                    <div v-else>
                                        No pickup locations for this seller.
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 card p-3">
                                <button class="btn w-full" @click="addToCart" :disabled="product?.qty <= 0">
                                    <span class="inline-flex items-center gap-2">
                                        <PlusIcon class="w-5 h-5" /> {{ product?.qty <= 0 ? 'Sold Out' : 'Add to Cart'
                                            }} </span>
                                </button>
                            </div>
                        </div>

                        <div v-if="!showSeller" class="mt-4 card p-3">
                            <div class="font-black">Pickup locations (inherited)</div>
                            <div class="mt-2 text-sm" style="color:#64748b;">
                                <div v-if="pickupLocations.length > 0">
                                    <div v-for="location in pickupLocations" :key="location">• {{ location }}</div>
                                </div>
                                <div v-else>
                                    No pickup locations for this listing type.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.pill {
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 9999px;
    padding: .75rem 1rem;
    background: #ffffff;
}

.product-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(2, 6, 23, .55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 5000;
    padding: 18px;
}

.modal {
    width: min(1200px, 100%);
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
.card {
    background: #ffffff;
    border-radius: 22px;
    box-shadow: 0 18px 40px rgba(2, 6, 23, .10);
    border: 1px solid rgba(148, 163, 184, .35);
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

.font-black {
    font-weight: 900;
}

.text-lg {
    font-size: 1.125rem;
}

.text-xl {
    font-size: 1.25rem;
}

.text-sm {
    font-size: .875rem;
}

.w-full {
    width: 100%;
}

.h-72 {
    height: 18rem;
}

.h-96 {
    height: 24rem;
}

.h-128 {
    height: 32rem;
}

.h-12 {
    height: 3rem;
}

.w-12 {
    width: 3rem;
}

.w-5 {
    width: 1.25rem;
}

.h-5 {
    height: 1.25rem;
}

.w-6 {
    width: 1.5rem;
}

.h-6 {
    height: 1.5rem;
}

.p-3 {
    padding: .75rem;
}

.p-4 {
    padding: 1rem;
}

.p-6 {
    padding: 1.5rem;
}

.mt-1 {
    margin-top: .25rem;
}

.mt-2 {
    margin-top: .5rem;
}

.mt-4 {
    margin-top: 1rem;
}

.mt-5 {
    margin-top: 1.25rem;
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

.grid {
    display: grid;
}

.grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.flex {
    display: flex;
}

.flex-1 {
    flex: 1 1 0%;
}

.items-center {
    align-items: center;
}

.items-start {
    align-items: flex-start;
}

.place-items-center {
    place-items: center;
}

.rounded-2xl {
    border-radius: 1rem;
}

.object-cover {
    object-fit: cover;
}

.overflow-hidden {
    overflow: hidden;
}

.relative {
    position: relative;
}

.absolute {
    position: absolute;
}

.text-center {
    text-align: center;
}

.inline-flex {
    display: inline-flex;
}

@media (min-width: 768px) {
    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .md\:p-5 {
        padding: 1.25rem;
    }

    .md\:p-6 {
        padding: 1.5rem;
    }
}
</style>
