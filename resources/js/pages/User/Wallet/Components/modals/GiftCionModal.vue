<template>
    <div v-if="open" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white p-5 rounded-xl w-80">
            <h2 class="text-lg font-semibold mb-3">
                Gift Coins to {{ user?.name || user?.email }}
            </h2>

            <!-- Input -->
            <input v-model="form.amount" type="number" placeholder="Enter coin amount"
                class="w-full border rounded-lg p-2 mb-1" />

            <!-- Validation Error -->
            <div v-if="form.errors.amount" class="text-red-500 text-xs mb-3">
                {{ form.errors.amount }}
            </div>

            <div class="flex justify-end gap-2">
                <button class="px-3 py-1.5 text-xs rounded-xl border" @click="$emit('close')"
                    :disabled="form.processing">
                    Cancel
                </button>

                <button class="px-3 py-1.5 text-xs rounded-xl bg-green-600 text-white disabled:opacity-50"
                    @click="sendGift" :disabled="form.processing">
                    <span v-if="!form.processing">Send</span>
                    <span v-else>Sending...</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    open: Boolean,
    user: Object,
})
const emit = defineEmits(['close', 'sent'])
console.log(props.user)
const form = useForm({
    amount: '',
    recipient_id: props.user?.id,
})

const sendGift = () => {
    form.post(route('frontend.user.send.gift'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('sent')
            form.reset()
        },
    })
}
</script>
