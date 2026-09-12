<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
    label: string
    options: any[]
    modelValue: any[]
}>()

const emit = defineEmits(['update:modelValue'])

const selectedItem = ref<any>(null)

const updateQty = (index: number, qty: number) => {
    const list = [...props.modelValue]
    list[index].qty = qty
    emit('update:modelValue', list)
}

const addItem = () => {
    if (!selectedItem.value) return

    const list = [...props.modelValue]

    const exists = list.find(i => i.id === selectedItem.value.id)

    if (exists) {
        exists.qty++
    } else {
        list.push({
            id: selectedItem.value.id,
            name: selectedItem.value.name,
            amount: selectedItem.value.price || 0,
            qty: 1
        })
    }

    emit('update:modelValue', list)
    selectedItem.value = null
}

const removeItem = (index: number) => {
    const list = [...props.modelValue]
    list.splice(index, 1)
    emit('update:modelValue', list)
}

</script>

<template>
    <div>
        <label class="block font-bold text-sm mt-2">{{ label }}</label>

        <div class="flex gap-5">
            <select v-model="selectedItem" class="w-full p-2 border rounded-lg bg-gray-50 text-sm">

                <option disabled value="">Select {{ label }}</option>

                <option v-for="item in options" :key="item.id" :value="item">
                    {{ item.name }} {{ item.amount ? `- $${item.amount}` : '' }}
                </option>
            </select>

            <button @click="addItem" class="bg-green-600 text-white px-2 rounded-full">
                Add
            </button>
        </div>

        <div class="mt-5">
            <div v-for="(item, index) in modelValue" :key="index" class="flex justify-between gap-5 mb-3">

                <div class="w-full">
                    <label>{{ item.name }} {{ item.amount ? `- $${item.amount}` : '' }}</label>
                </div>

                <div class="flex gap-2">
                    <input type="number" min="1" :value="item.qty"
                        @input="updateQty(index, Number(($event.target as HTMLInputElement).value))"
                        class="p-2 border border-gray-300 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">

                    <button @click="removeItem(index)" class="bg-red-600 text-white px-2 rounded-full">
                        Remove
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
