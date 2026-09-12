<template>
  <div class="addon-selector">
    <h3 class="text-lg font-semibold mb-4">Add-ons</h3>

    <div v-if="addons.length === 0" class="text-gray-500 text-center py-4">
      No add-ons available for this event
    </div>

    <div v-else class="space-y-4">
      <div v-for="addon in addons" :key="addon.id" class="border rounded-lg p-4" :class="{
        'border-red-200 bg-red-50': !addon.is_available,
        'border-gray-200 bg-white': addon.is_available
      }">
        <div class="flex justify-between items-start mb-2">
          <div>
            <h4 class="font-medium text-gray-900">{{ addon.name }}</h4>
            <p v-if="addon.description" class="text-sm text-gray-600 mt-1">
              {{ addon.description }}
            </p>
          </div>
          <div class="text-right">
            <div class="text-lg font-semibold text-gray-900">
              Price: ${{ (addon.price).toFixed(2) }}
            </div>
            <label>(minimum order per click) {{ addon.step }} item{{ addon.step > 1 ? 's' : '' }}</label>
          </div>
        </div>

        <div v-if="addon.is_available" class="flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <button @click="decreaseQuantity(addon.id)" :disabled="getSelectedQuantity(addon.id) < addon.step"
              class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              -
            </button>
            <span class="w-8 text-center">{{ getSelectedQuantity(addon.id) }}</span>
            <button @click="increaseQuantity(addon.id)"
              :disabled="getSelectedQuantity(addon.id) + addon.step > addon.max_quantity_per_ticket || getSelectedQuantity(addon.id) + addon.step > addon.available_quantity"
              class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              +
            </button>
          </div>

          <div v-if="getSelectedQuantity(addon.id) > 0" class="text-sm text-gray-600">
            Total: ${{ (addon.price * getSelectedQuantity(addon.id)).toFixed(2) }}
          </div>
        </div>

        <div v-else class="text-sm text-red-500">
          {{ addon.is_required ? 'Required - Sold out' : 'Sold out' }}
        </div>
      </div>
    </div>

    <div v-if="totalAddonPrice > 0" class="mt-6 p-4 bg-blue-50 rounded-lg">
      <div class="flex justify-between items-center">
        <span class="font-medium text-gray-900">Total Add-ons:</span>
        <span class="text-lg font-semibold text-blue-600">
          ${{ totalAddonPrice.toFixed(2) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'

interface Addon {
  id: number
  name: string
  description: string
  price: number
  available_quantity: number
  is_required: boolean
  max_quantity_per_ticket: number
  is_available: boolean
  step: number
}

interface Props {
  ticketId: number
  modelValue: AddonSelection[]
  drinkAddons?: any
}

interface AddonSelection {
  addon_id: number
  quantity: number
  price: number
}

const props = defineProps<Props>()
const emit = defineEmits<{
  'update:modelValue': [value: AddonSelection[]]
  'price-change': [price: number]
}>()

const addons = ref<Addon[]>([])
const selectedAddons = ref<AddonSelection[]>([])

const totalAddonPrice = computed(() => {
  return selectedAddons.value.reduce((total, selection) => {
    const addon = addons.value.find(a => a.id === selection.addon_id)
    return total + (addon ? addon.price * selection.quantity : 0)
  }, 0)
})

const processAddons = () => {
  addons.value = []
  if (!props.drinkAddons || !props.drinkAddons.enabled || !props.drinkAddons.items) return;

  let idCounter = 1;
  const categories = Object.keys(props.drinkAddons.items);
  categories.forEach((category) => {
    const items = props.drinkAddons.items[category] || [];
    console.log(items);
    items.forEach((item: { name: string; qty: number; cost: string }) => {
      if (item.qty > 0) {
        addons.value.push({
          id: idCounter++,
          name: item.name,
          description: '',
          price: Number(item.cost), // per item price
          available_quantity: 1000, // Assume unlimited; adjust if data available
          is_required: false,
          max_quantity_per_ticket: 1000, // Assume unlimited
          is_available: true,
          step: item.qty,
        });
      }
    });
  });
}

const getSelectedQuantity = (addonId: number) => {
  const selection = selectedAddons.value.find(s => s.addon_id === addonId)
  return selection ? selection.quantity : 0
}

const increaseQuantity = (addonId: number) => {
  const addon = addons.value.find(a => a.id === addonId)
  if (!addon) return

  const step = addon.step || 1
  const currentQuantity = getSelectedQuantity(addonId)
  const newQuantity = currentQuantity + step
  const maxQuantity = Math.min(addon.max_quantity_per_ticket, addon.available_quantity)

  if (newQuantity > maxQuantity) return

  let selection = selectedAddons.value.find(s => s.addon_id === addonId)
  if (!selection) {
    selection = { addon_id: addonId, quantity: 0, price: addon.price }
    selectedAddons.value.push(selection)
  }
  selection.quantity = newQuantity
  updateModelValue()
}

const decreaseQuantity = (addonId: number) => {
  const addon = addons.value.find(a => a.id === addonId)
  if (!addon) return

  const step = addon.step || 1
  const currentQuantity = getSelectedQuantity(addonId)
  const newQuantity = currentQuantity - step

  const selection = selectedAddons.value.find(s => s.addon_id === addonId)
  if (selection) {
    if (newQuantity < step) {
      selectedAddons.value = selectedAddons.value.filter(s => s.addon_id !== addonId)
    } else {
      selection.quantity = newQuantity
    }
    updateModelValue()
  }
}

const updateModelValue = () => {
  // ensure price stays in sync with source addon list
  selectedAddons.value = selectedAddons.value.map(sel => {
    const src = addons.value.find(a => a.id === sel.addon_id)
    return src ? { ...sel, price: src.price } : sel
  })

  emit('update:modelValue', selectedAddons.value.filter(s => s.quantity > 0))
  emit('price-change', totalAddonPrice.value)
}

watch(() => props.modelValue, (newValue) => {
  selectedAddons.value = [...newValue]
}, { immediate: true })

onMounted(() => {
  processAddons()
})
</script>