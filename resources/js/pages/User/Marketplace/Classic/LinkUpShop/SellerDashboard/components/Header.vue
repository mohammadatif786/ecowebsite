<script setup lang="ts">
import { onMounted, watch, ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { ListOrderedIcon, Plus, ShoppingBagIcon, Menu, X } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

const emit = defineEmits<{ (e: 'open-earnings'): void; (e: 'open-wallet'): void; (e: 'open-analytics'): void; (e: 'open-store'): void; (e: 'refresh'): void }>();

const page = usePage();
const currentPath = computed(() => page.url);
const isActive = (path: string) => {
    return currentPath.value.startsWith(path);
};

const props = defineProps<{ count?: number | string; merchants?: any[]; categories?: any[]; userProducts?: any[] }>();
const showModal = ref(false);
const mobileMenuOpen = ref(false);

const userProfile = computed(() => {
    const authUser = (usePage().props.auth as any).user;
    return {
        name: authUser?.name || 'New User',
        tag: authUser?.tag || '~NewUser',
        linkup_id: authUser?.linkup_id || 'newuser',
        avatar: authUser?.avatar || null,
    };
});

onMounted(() => {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
});

const handleModalClose = () => {
    showModal.value = false;
};

const handleRefresh = () => {
    emit('refresh');
};

const goHome = () => {
    window.location.href = '/products';
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

watch(() => props.count, () => {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
});

</script>

<template>
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b rounded-4xl"
        style="border-color:rgba(148,163,184,.35);">
        <div class="w-full px-4 py-3 flex items-center gap-3">
            <div class="flex items-center gap-2 flex-1 md:flex-initial">
                <!-- User Details -->
                <div class="flex items-center gap-2 p-2 rounded-xl bg-gray-50 border border-gray-200">
                    <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0">
                        <img v-if="userProfile.avatar" :src="userProfile.avatar" :alt="userProfile.name"
                            class="w-full h-full object-cover" />
                        <div v-else
                            class="w-full h-full bg-gradient-to-br from-blue-500 to-green-500 text-white text-xs font-bold flex items-center justify-center">
                            {{ userProfile.name?.charAt(0).toUpperCase() || 'U' }}
                        </div>
                    </div>
                    <div class="flex-1 overflow-hidden hidden sm:block">
                        <div class="text-sm font-bold truncate">{{ userProfile.name }}</div>
                        <div class="text-xs text-gray-500 truncate">~{{ userProfile.linkup_id }}</div>
                        <div class="text-xs hidden lg:block" style="color:#64748b">Individuals • Stores • Carnival Groups • Admin </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu Button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden ml-auto p-2 rounded-lg border border-gray-200">
                <Menu v-if="!mobileMenuOpen" class="w-5 h-5" />
                <X v-else class="w-5 h-5" />
            </button>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex ml-auto items-center gap-2">
                <button class="btn2 flex items-center gap-2 whitespace-nowrap" @click="goHome()"
                    :class="isActive('/home') ? 'text-gray-800 font-bold' : 'text-gray-500'">
                     Products
                </button>
                <button class="btn2 flex items-center gap-2 whitespace-nowrap" @click="router.visit('/seller/dashboard')"
                    :class="isActive('/seller/dashboard') || isActive('/seller-dashboard') ? 'text-gray-800 font-bold' : 'text-gray-500'">
                     Analytics
                </button>
                <button class="btn2 flex items-center gap-2 whitespace-nowrap" @click="router.visit('/seller/earnings')"
                    :class="isActive('/seller/earnings') ? 'text-gray-800 font-bold' : 'text-gray-500'">
                     Earnings
                </button>
                <button class="btn2 flex items-center gap-2 whitespace-nowrap" @click="router.visit('/seller/orders')"
                    :class="isActive('/seller/orders') ? 'text-gray-800 font-bold' : 'text-gray-500'">
                    Orders
                </button>
                <button class="btn2 flex items-center gap-2 whitespace-nowrap" @click="router.visit('/seller/store')"
                    :class="isActive('/seller/store') ? 'text-gray-800 font-bold' : 'text-gray-500'">
                     Store
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-200 bg-white/95 backdrop-blur">
            <div class="flex flex-col p-4 gap-2">
                <button class="btn2 flex items-center gap-2 justify-center w-full" @click="goHome(); closeMobileMenu()"
                    :class="isActive('/home') ? 'text-gray-800 font-bold' : 'text-gray-500'"> Products</button>
                <button class="btn2 flex items-center gap-2 justify-center w-full" @click="router.visit('/seller/dashboard'); closeMobileMenu()"
                    :class="isActive('/seller/dashboard') ? 'text-gray-800 font-bold' : 'text-gray-500'"> Analytics</button>
                <button class="btn2 flex items-center gap-2 justify-center w-full" @click="router.visit('/seller/earnings'); closeMobileMenu()"
                    :class="isActive('/seller/earnings') ? 'text-gray-800 font-bold' : 'text-gray-500'"> Earnings</button>
                <button class="btn2 flex items-center gap-2 justify-center w-full" @click="router.visit('/seller/orders'); closeMobileMenu()"
                    :class="isActive('/seller/orders') ? 'text-gray-800 font-bold' : 'text-gray-500'">Orders</button>
                <button class="btn2 flex items-center gap-2 justify-center w-full" @click="router.visit('/seller/store'); closeMobileMenu()"
                    :class="isActive('/seller/store') ? 'text-gray-800 font-bold' : 'text-gray-500'">Store</button>
            </div>
        </div>
    </header>

</template>

<style scoped>
/* Your existing styles remain exactly the same */
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