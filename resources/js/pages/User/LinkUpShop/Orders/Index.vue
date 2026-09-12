<template>
    <AuthenticatedLayout>

        <Head title="Orders" />

        <!-- ORDERS VIEW -->
        <section id="view-orders" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Your Orders</h3>
                <button id="exportOrders"
                    class="px-4 py-2 rounded-xl border border-slate-300 text-white hover:text-black hover:bg-slate-100 text-sm font-medium"
                    @click="exportOrders">
                    Export JSON
                </button>
            </div>

            <div v-if="orders.length === 0" class="text-slate-600">
                No orders yet.
            </div>

            <div id="ordersList" class="grid gap-4">
                <div v-for="o in [...orders].reverse()" :key="o.no"
                    class="p-5 bg-white rounded-2xl shadow-glass border border-black/5">
                    <!-- Top row -->
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <div class="font-semibold text-[color:var(--ink)]">
                                Order #{{ o.id }}
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ new Date(o.date).toLocaleString() }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-slate-500">
                                {{ o.items.length }} item(s)
                            </div>
                            <div class="font-semibold">
                                {{ fmt(o.total, o.currency) }}
                            </div>
                        </div>
                    </div>

                    <!-- Shipping -->
                    <div class="mt-3 text-sm text-slate-600">
                        Ship to: {{ o.ship.name }}, {{ o.ship.address }}, {{ o.ship.city }}
                    </div>

                    <!-- Payment + Status -->
                    <div class="mt-2 text-sm">
                        Payment:
                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-xs">
                            {{ o.payment || "card" }}
                        </span>
                        ·
                        Status:
                        <span class="px-2 py-0.5 rounded-full text-xs" :class="{
                            'bg-yellow-100 text-yellow-700': o.status === 'pending' || o.status === 'processing',
                            'bg-green-100 text-green-700': o.status === 'delivered',
                            'bg-red-100 text-red-700': o.status === 'cancelled',
                        }">
                            {{ o.status }}
                        </span>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'

const props = defineProps<{
    orders: Array<any>
}>()

// Use the orders from backend
const orders = props.orders

function fmt(amount: number, currency: string) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency
    }).format(amount)
}

function exportOrders() {
    const blob = new Blob([JSON.stringify(orders, null, 2)], {
        type: 'application/json'
    })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'linkup-orders.json'
    a.click()
    URL.revokeObjectURL(url)
}
</script>
