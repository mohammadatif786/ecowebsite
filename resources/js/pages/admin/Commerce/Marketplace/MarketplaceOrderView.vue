<script setup lang="ts">
import NewAppHeader from '@/pages/admin/components/NewAppHeader.vue';
import NewAppSidebar from '@/pages/admin/components/NewAppSidebar.vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import {
    ShoppingCart, ArrowLeft, Receipt, Clock,
    CircleDot, CheckCircle2, Package, MapPin,
    User, DollarSign, Wallet
} from 'lucide-vue-next';
import { nextTick, onMounted, onUnmounted, ref } from 'vue';
import moment from 'moment';

// Import custom styles
import '@/../../resources/css/new_admin.css';

const props = defineProps<{
    initialUnits: any[];
    initialCountries: any[];
    order: any;
}>();

const sidebarVisible = ref(true);
const toggleSidebar = () => {
    sidebarVisible.value = !sidebarVisible.value;
};

// Data Model
const countries = ref(props.initialCountries);

const form = useForm({
    status: props.order.status
});

const updateOrderStatus = () => {
    form.post(route('admin.commerce.marketplace.orders.update.status', props.order.id), {
        preserveState: true,
        onSuccess: () => {
            // Success feedback could be added here
        }
    });
};

const fmt = (n: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n);

const renderAll = async () => {
    for (let i = 0; i < 3; i++) {
        await nextTick();
        const rs = countries.value;
        const t = rs.reduce((a, c) => {
            props.initialUnits.forEach(u => { a.gtv += (Number(c[u.key]) || 0); });
            return a;
        }, { gtv: 0 });

        const set = (id: string, v: string) => {
            const e = document.getElementById(id);
            if (e) { e.textContent = v; return true; }
            return false;
        };
        set('sideGTV', fmt(t.gtv));
        if (set('rGTV', fmt(t.gtv))) break;
        await new Promise(r => setTimeout(r, 100));
    }
};

onMounted(() => {
    document.body.classList.add('new-admin-body');
    renderAll();
});

onUnmounted(() => {
    document.body.classList.remove('new-admin-body');
});
</script>

<template>
    <Head title="View Order" />
    <div class="flex min-h-screen">
        <NewAppSidebar active-id="marketplaceOrdersCommand" v-show="sidebarVisible" />

        <main class="flex-1 overflow-x-hidden">
            <NewAppHeader title="Order Details" :countries="countries" @toggle-sidebar="toggleSidebar" @filter-change="renderAll" />

            <section class="p-5 lg:p-8 space-y-6">
                <!-- Header Actions -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <button @click="router.visit(route('admin.commerce.marketplace.orders'))"
                            class="h-12 w-12 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-center justify-center text-slate-400 hover:text-slate-900 transition-colors">
                            <ArrowLeft class="w-6 h-6" />
                        </button>
                        <div>
                            <h3 class="text-3xl font-black text-slate-950 flex items-center gap-3">
                                <Receipt class="w-8 h-8" /> Order #{{ order.number }}
                            </h3>
                            <p class="text-slate-500 font-medium mt-1">Placed on {{ moment(order.created_at).format('DD MMM YYYY, HH:mm') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex flex-col items-end">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mr-1">Update Status</span>
                            <select v-model="form.status" @change="updateOrderStatus"
                                class="rounded-2xl bg-white border border-slate-100 shadow-sm py-3 px-5 font-black text-sm text-slate-900 outline-none focus:border-purple-500 transition-all">
                                <option value="new">New</option>
                                <option value="paid">Paid</option>
                                <option value="processing">Processing</option>
                                <option value="ready_for_pickup">Ready for Pickup</option>
                                <option value="picked_up">Picked Up</option>
                                <option value="out_for_delivery">Out for Delivery</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left: Order Info & Items -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Info Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="card rounded-[32px] p-6 bg-white border-slate-100 shadow-sm space-y-4">
                                <div class="flex items-center gap-3 text-slate-900">
                                    <div class="h-10 w-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                                        <User class="w-5 h-5" />
                                    </div>
                                    <h4 class="font-black">Customer Details</h4>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-400 font-bold uppercase text-[10px]">Name</span>
                                        <span class="text-slate-900 font-black">{{ order.customer?.name ?? 'Guest' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-400 font-bold uppercase text-[10px]">Email</span>
                                        <span class="text-slate-900 font-black">{{ order.customer?.email ?? '—' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-400 font-bold uppercase text-[10px]">Payment</span>
                                        <span class="text-slate-900 font-black">{{ order.payment_method || 'Wallet' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="card rounded-[32px] p-6 bg-white border-slate-100 shadow-sm space-y-4">
                                <div class="flex items-center gap-3 text-slate-900">
                                    <div class="h-10 w-10 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600">
                                        <MapPin class="w-5 h-5" />
                                    </div>
                                    <h4 class="font-black">Shipping Address</h4>
                                </div>
                                <p class="text-sm font-bold text-slate-700 leading-relaxed">
                                    {{ order.street_address }}<br />
                                    {{ order.city }}, {{ order.state }} {{ order.zipcode }}<br />
                                    <span class="text-slate-900 font-black uppercase text-[10px] tracking-widest">{{ order.country }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="card rounded-[32px] bg-white border-slate-100 shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                                        <ShoppingCart class="w-5 h-5" />
                                    </div>
                                    <h4 class="font-black text-slate-900 text-xl">Order Items</h4>
                                </div>
                                <span class="px-4 py-1.5 rounded-2xl bg-slate-50 text-slate-600 text-xs font-black uppercase">{{ order.order_items?.length || 0 }} Items</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50/50 text-[10px] font-black uppercase text-slate-400 tracking-widest">
                                        <tr>
                                            <th class="px-8 py-4">Product</th>
                                            <th class="px-8 py-4">Seller</th>
                                            <th class="px-8 py-4 text-center">Qty</th>
                                            <th class="px-8 py-4 text-right">Price</th>
                                            <th class="px-8 py-4 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="item in order.order_items" :key="item.id" class="group hover:bg-slate-50/50 transition">
                                            <td class="px-8 py-5">
                                                <Link :href="route('admin.commerce.marketplace.products.show', item.product_id)"
                                                    class="font-black text-slate-900 hover:text-purple-600 transition underline underline-offset-4 decoration-slate-200">
                                                    {{ item.product?.name ?? 'Deleted Product' }}
                                                </Link>
                                            </td>
                                            <td class="px-8 py-5 text-slate-600 font-bold">{{ item.product?.seller?.name || 'N/A' }}</td>
                                            <td class="px-8 py-5 text-center font-black text-slate-900">{{ item.qty }}</td>
                                            <td class="px-8 py-5 text-right font-bold text-slate-500">${{ Number(item.unit_price).toFixed(2) }}</td>
                                            <td class="px-8 py-5 text-right font-black text-slate-900">${{ Number(item.sub_total).toFixed(2) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-slate-950 text-white">
                                            <td colspan="4" class="px-8 py-6 text-right font-black uppercase tracking-widest text-slate-400">Grand Total</td>
                                            <td class="px-8 py-6 text-right text-2xl font-black">${{ Number(order.total).toFixed(2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Timeline & Escrow -->
                    <div class="space-y-6">
                        <!-- Escrow Card -->
                        <div class="card rounded-[32px] p-6 bg-white border-slate-100 shadow-sm space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                                    <Wallet class="w-5 h-5" />
                                </div>
                                <h4 class="font-black text-slate-900">Escrow Information</h4>
                            </div>
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Escrow Total</span>
                                    <span class="text-xl font-black text-slate-900">${{ Number(order.total).toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-4 border-t border-slate-200">
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Settlement</span>
                                    <span v-if="order.status === 'delivered'"
                                        class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-50 text-emerald-600 border border-emerald-100">Released</span>
                                    <span v-else
                                        class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-amber-50 text-amber-600 border border-amber-100">Held in Escrow</span>
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium leading-relaxed italic text-center">
                                Proceeds are automatically released to the seller's wallet once the order status is updated to "Delivered".
                            </p>
                        </div>

                        <!-- Tracking Timeline -->
                        <div class="card rounded-[32px] p-6 bg-white border-slate-100 shadow-sm space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                                    <CircleDot class="w-5 h-5" />
                                </div>
                                <h4 class="font-black text-slate-900">Tracking History</h4>
                            </div>
                            <div class="relative space-y-8 before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-slate-100">
                                <div v-for="(track, index) in order.trackings" :key="track.id" class="relative flex items-start gap-4 group">
                                    <div class="relative z-10 flex items-center justify-center w-10 h-10 rounded-full border-4 border-white bg-white shadow-sm shrink-0">
                                        <CheckCircle2 v-if="index === 0" class="w-5 h-5 text-emerald-500" />
                                        <Clock v-else class="w-5 h-5 text-slate-300" />
                                    </div>
                                    <div class="flex-1 pt-1">
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <div class="font-black text-slate-900 text-sm capitalize">{{ track.status.replace(/_/g, ' ') }}</div>
                                            <time class="text-[9px] font-black text-purple-600 uppercase tracking-tighter">
                                                {{ moment(track.last_update).format('MMM DD, HH:mm') }}
                                            </time>
                                        </div>
                                        <div class="text-xs text-slate-500 font-medium leading-relaxed">{{ track.note || 'Status updated.' }}</div>
                                    </div>
                                </div>
                                <div v-if="!order.trackings?.length" class="text-center py-4">
                                    <p class="text-sm text-slate-400 italic">No tracking history recorded.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
