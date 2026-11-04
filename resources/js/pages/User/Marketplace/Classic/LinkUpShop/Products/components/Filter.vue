<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
    categories: any;
    countries: any;
    initialFilters?: {
        search?: string;
        category?: string;
        country?: string;
        sort?: string;
    };
}>();

const emit = defineEmits<{
    (e: 'search-product', value: { search: string, category: string, country: string, sort: string }): void
}>();

const searchProduct = ref<string>(props.initialFilters?.search || '');
const searchCategory = ref<string>(props.initialFilters?.category || '');
const searchCountry = ref<string>(props.initialFilters?.country || '');
const searchSort = ref<string>(props.initialFilters?.sort || 'new');

watch([searchProduct, searchCategory, searchCountry, searchSort], () => {
    emit('search-product', { 
        search: searchProduct.value, 
        category: searchCategory.value,
        country: searchCountry.value,
        sort: searchSort.value
    });
});

const resetFilters = () => {
    searchProduct.value = '';
    searchCategory.value = '';
    searchCountry.value = '';
    searchSort.value = 'new';
};
</script>
<template>
    <section class="card p-4 md:p-5 soft glow">
        <div class="flex flex-col md:flex-row md:items-end gap-3">
            <div class="flex-1">
                <div class="font-black">Search</div>
                <input id="q" class="input mt-1" v-model="searchProduct"
                    placeholder="Search products, stores, groups…" />
            </div>
            <div class="w-full md:w-56">
                <div class="font-black">Category</div>
                <select id="catFilter" class="input mt-1" v-model="searchCategory">
                    <option value="">All Categories</option>
                    <option v-for="category in props.categories" :key="category.id" :value="category.id">{{
                        category.name }}</option>
                </select>
            </div>
            <div class="w-full md:w-56">
                <div class="font-black">Country</div>
                <select id="countryFilter" class="input mt-1" v-model="searchCountry">
                    <option value="">All Countries</option>
                    <option v-for="country in props.countries" :key="country.code" :value="country.code">{{
                        country.name }}</option>
                </select>
            </div>
            <div class="w-full md:w-56">
                <div class="font-black">Sort</div>
                <select id="sort" class="input mt-1" v-model="searchSort">
                    <option value="new">Newest</option>
                    <option value="priceAsc">Price: Low → High</option>
                    <option value="priceDesc">Price: High → Low</option>
                    <option value="nameAsc">Name: A → Z</option>
                    <option value="nameDesc">Name: Z → A</option>
                </select>
            </div>

            <button class="btn w-full md:w-auto" id="btnReset" @click="resetFilters">
                Reset
            </button>

        </div>

        <div class="mt-4 flex flex-wrap gap-2 text-sm" style="color:#64748b">
            <span class="chip">✅ Availability defaults to <b>ALL</b> (silent)</span>
            <span class="chip">📦 Pickup locations inherit from <b>Store / Carnival Group</b></span>
            <span class="chip">🧾 Buyer can track progress after checkout</span>
            <span class="chip">🔒 Seller payouts held until <b>Delivered</b> + transferred</span>
        </div>

    </section>
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
    display: none;
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
