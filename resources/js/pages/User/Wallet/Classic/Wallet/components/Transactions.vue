<template>
    <div class="lg:col-span-2 bg-white rounded-2xl p-5 shadow-glass border">
        <!-- Header -->
        <div class="flex items-center gap-3 mb-3">
            <div class="font-semibold text-lg">Transactions</div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto scrollbar-thin">
            <table class="min-w-[600px] w-full text-sm text-center">
                <thead class="text-xs text-slate-500">
                    <tr>
                        <th class="py-2">Date</th>
                        <th class="py-2">Type</th>
                        <th class="py-2">Amount</th>
                        <th class="py-2">Status</th>
                        <th class="py-2">Transaction ID</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tx in transactions" :key="tx.id" class="border-t">
                        <td class="py-2">
                            {{ new Date(tx.created_at).toLocaleString() }}
                        </td>
                        <td class="py-2 capitalize">
                            {{ tx.processor_id }}
                        </td>
                        <td class="py-2" :class="tx.amount > 0 ? 'text-green-600' : 'text-red-600'">
                            {{ tx.amount }} {{ tx.currency }}
                        </td>
                        <td class="py-2">
                            <span class="px-2 py-1 rounded text-xs" :class="{
                                'bg-green-100 text-green-600': tx.status === 'success',
                                'bg-yellow-100 text-yellow-600': tx.status === 'pending',
                                'bg-red-100 text-red-600': tx.status === 'failed'
                            }">
                                {{ tx.status }}
                            </span>
                        </td>
                        <td class="py-2 font-mono">
                            {{ tx.uuid }}
                        </td>
                    </tr>

                    <tr v-if="transactions.length === 0">
                        <td colspan="5" class="py-4 text-center text-slate-500">
                            No transactions found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</template>

<script setup>
defineProps({
    transactions: {
        type: Array,
        default: () => []
    }
})
</script>
