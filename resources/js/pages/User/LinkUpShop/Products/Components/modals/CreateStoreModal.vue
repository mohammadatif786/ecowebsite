<script setup lang="ts">
import { Plus } from 'lucide-vue-next';
import StoreTab from '../tabs/StoreTab.vue';
import StoreCarnival from '../tabs/StoreCarnival.vue';
import { ref } from 'vue';
import Product from '../tabs/Product.vue';

const props = defineProps<{
    show: boolean;
    merchants?: any[];
    categories?: any[];
    userProducts?: any[];
}>();

const activeTab = ref('');

const tabs = [
    {
        id: 'store',
        label: 'Create Store',
        description: 'Stores can offer pickup options (inherited by products).'
    },
    {
        id: 'group',
        label: 'Create Carnival Group',
        description: 'Groups can offer pickup options (inherited by products).'
    },
    {
        id: 'product',
        label: 'Add Product',
        description: 'Upload is supported (JPEG/PNG/GIF) + Category dropdown + availability ALL.'
    }
];
const emit = defineEmits(['close', 'refresh']);
</script>
<template>
    <div id="backdrop" v-if="props.show" class="modal-backdrop">

        <div class="card modal p-4 md:p-6">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-2xl grid place-items-center text-white"
                    style="background:linear-gradient(135deg, rgba(14,165,233,1), rgba(34,197,94,1));">
                    <Plus />
                </div>

                <div class="flex-1">
                    <div class="text-xl font-black" id="modalTitle">{{activeTab ? tabs.find(tab => tab.id ===
                        activeTab)?.label : 'Create'}}</div>
                    <div class="text-sm mt-1" style="color:#64748b;" id="modalSubtitle">
                        {{activeTab ? tabs.find(tab => tab.id === activeTab)?.description : 'Add a Store, Carnival Group, or Product(Individual / Store / Group / Admin)' }}
                    </div>
                </div>

                <button class="btn2" id="btnClose" @click="$emit('close')">
                    <span class="inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            data-lucide="x" class="lucide lucide-x w-5 h-5">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg> Close
                    </span>
                </button>

            </div>

            <div class="mt-5" id="modalBody">
                <div class="grid md:grid-cols-3 gap-3" v-if="activeTab == ''">

                    <button class="card p-4 text-left hover:scale-[1.01] transition" id="goStore"
                        @click="activeTab = 'store'">
                        <div class="font-black text-lg" :id="tabs[0].id">{{ tabs[0].label }}</div>
                        <div class="mt-1 text-sm" style="color:#64748b;">Includes Country / State / City + pickup
                            locations.
                        </div>
                    </button>

                    <button class="card p-4 text-left hover:scale-[1.01] transition" id="goGroup"
                        @click="activeTab = 'group'">
                        <div class="font-black text-lg" :id="tabs[1].id">{{ tabs[1].label }}</div>
                        <div class="mt-1 text-sm" style="color:#64748b;">Includes Country / State / City + band
                            pickup
                            locations.</div>
                    </button>

                    <button class="card p-4 text-left hover:scale-[1.01] transition" id="goProduct"
                        @click="activeTab = 'product'">
                        <div class="font-black text-lg" :id="tabs[2].id">{{ tabs[2].label }}</div>
                        <div class="mt-1 text-sm" style="color:#64748b;">Category dropdown • Listing type badge •
                            availability
                            ALL.</div>
                    </button>

                </div>

                <StoreTab :activeTab="activeTab" :merchants="merchants" @back="activeTab = ''" />
                <StoreCarnival :activeTab="activeTab" :merchants="merchants" @back="activeTab = ''" />
                <Product :activeTab="activeTab" :merchants="merchants" :categories="categories" :user-products="userProducts"
                    @back="activeTab = ''" @refresh="$emit('refresh')" @close="$emit('close')" />

            </div>
        </div>
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