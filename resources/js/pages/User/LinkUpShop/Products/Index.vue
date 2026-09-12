<script setup lang="ts">
import Header from './Components/Header.vue';
import Filter from './Components/Filter.vue';
import ProductCard from './Components/ProductCard.vue';
import ProductModal from './Components/ProductModal.vue';
import CartModal from './Components/CartModal.vue';
import CheckoutModal from './Components/CheckoutModal.vue';
import OrderSummaryModal from './Components/OrderSummaryModal.vue';
import OrdersModal from './Components/OrdersModal.vue';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import axios from 'axios';
import { ref, watch, computed, watchEffect } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    products: any[];
    category: any[];
    merchants: any[];
    userProducts: any[];
    countries: any[];
    shopFee?: any;
    processFee?: any;
    favoriteSellerIds?: number[];
    filters?: {
        search?: string;
        category?: string;
        country?: string;
        sort?: string;
    };
}>();
const products = ref(props.products);
watch(() => props.products, (newProducts) => {
    products.value = newProducts;
}, { immediate: true });

const handleRefresh = () => {
    router.reload();
};

const selectedProduct = ref<any>(null);
const showProductModal = ref(false);

const CART_KEY = 'lu_shop_cart_v1';
const cart = ref<Array<{ productId: number; qty: number }>>(JSON.parse(localStorage.getItem(CART_KEY) || '[]'));

const saveCart = () => {
    localStorage.setItem(CART_KEY, JSON.stringify(cart.value));
};

const addToCart = (productId: number, qty: number = 1) => {
    const existing = cart.value.find(c => c.productId === productId);
    if (existing) {
        existing.qty += qty;
        toast.success('Product added to cart');
    } else {
        cart.value.push({ productId, qty });
        toast.success('Product added to cart');
    }
    saveCart();
};

const cartCount = computed(() => cart.value.reduce((sum, i) => sum + (i.qty || 0), 0));

const showCart = ref(false);
const openCart = () => { showCart.value = true; };
const closeCart = () => { showCart.value = false; };
const updateCartFromModal = (value: Array<{ productId: number; qty: number }>) => { cart.value = value; saveCart(); };

const showCheckout = ref(false);
const openCheckout = () => { showCheckout.value = true; };
const closeCheckout = () => { showCheckout.value = false; };
const showOrderSummary = ref(false);
const showOrders = ref(false);
const lastOrder = ref<any>(null);
const onOrderPlaced = (order: any) => {
    cart.value = [];
    saveCart();
    showCheckout.value = false;
    showCart.value = false;
    lastOrder.value = order;
    showOrderSummary.value = true;
};

const openOrders = () => { showOrders.value = true; };
const closeOrders = () => { showOrders.value = false; };

const onSearch = async (value: { search: string; category: string; country: string; sort: string }) => {

    try {
        const response = await axios.get(route('frontend.products.search'), {
            params: {
                search: value.search,
                category: value.category,
                country: value.country,
                sort: value.sort
            }
        });

        products.value = response.data.products;
    } catch (error) {
        console.error(error);
    }
};

const initialFilters = {
    search: props.filters?.search || '',
    category: props.filters?.category || '',
    country: props.filters?.country || '',
    sort: props.filters?.sort || 'new'
};

const onViewProduct = (productId: number) => {
    const product = products.value.find(p => p.id === productId);
    selectedProduct.value = product;
    showProductModal.value = true;
};

const onCloseModal = () => {
    showProductModal.value = false;
    selectedProduct.value = null;
};

const onAddToCartFromModal = (productId: number) => {
    addToCart(productId, 1);
    onCloseModal();
};

const onAddToCart = (productId: number) => {
    addToCart(productId, 1);
};

// Handle flash messages
const page = usePage();
watchEffect(() => {
    const flash = page.props?.flash as any;
    if (flash?.success) {
        toast.success(flash.success);
        if (flash.success === 'Payment successful') {
            cart.value = [];
            saveCart();
        }
    } else if (flash?.error) {
        toast.error(flash.error);
    } else if (flash?.message) {
        toast(flash.message);
    }
});
const favoriteSellerIds = ref([...props.favoriteSellerIds]);
</script>
<template>
    <!-- Top Bar -->
    <Header :count="cartCount" @open-cart="openCart" @open-orders="openOrders" @refresh="handleRefresh"
        :merchants="merchants" :categories="category" :user-products="userProducts" />
    <main class="w-full px-4 py-6 grid gap-6">
        <!-- Filters -->
        <Filter :categories="category" :countries="countries" :initial-filters="initialFilters" @search-product="onSearch" />
        <!-- Products grid -->
        <section>
            <div class="flex items-center justify-between mb-3">
                <div class="font-black text-xl">Products</div>
                <div class="text-sm" style="color:#64748b">
                    Showing <span id="shownCount" class="font-black">{{ products.length }}</span> items
                </div>
            </div>
            <div v-if="products.length > 0" id="grid" class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <ProductCard v-for="product in products" :key="product.id" :product="product" @view="onViewProduct"
                    @add="onAddToCart" />
            </div>
            <div v-else class="text-center py-12">
                <div class="text-gray-500 text-lg">No products found</div>
                <div class="text-gray-400 text-sm mt-2">Try adjusting your filters</div>
            </div>
        </section>
    </main>

    <!-- Product Modal -->
    <ProductModal :favoriteSellerIds="favoriteSellerIds" :product="selectedProduct" :show="showProductModal" @close="onCloseModal"
        @add-to-cart="onAddToCartFromModal" @update:favoriteSellerIds="favoriteSellerIds = $event"/>

    <!-- Cart Modal -->
    <CartModal :show="showCart" :products="products" :cart="cart" @close="closeCart" @update-cart="updateCartFromModal"
        @checkout="openCheckout" />

    <!-- Checkout Modal -->
    <CheckoutModal :show="showCheckout" :products="products" :cart="cart" :shop-fee="props.shopFee" :process-fee="props.processFee"
        @close="closeCheckout" @order-placed="onOrderPlaced" />

    <!-- Order Summary Modal -->
    <OrderSummaryModal :show="showOrderSummary" :order="lastOrder" @close="() => showOrderSummary = false" />

    <!-- Orders Modal -->
    <OrdersModal :show="showOrders" @close="closeOrders" />

    <!-- Toast Container -->
    <Toaster position="top-center" />

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
    .lg\:grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

@media (min-width: 1280px) {
    .xl\:grid-cols-5 {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }
}
</style>
