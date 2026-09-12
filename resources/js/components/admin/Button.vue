<template>
    <button :type="type" :class="[
    'px-2 md:px-2 cursor-pointer py-1 text-sm sm:text-sm md:text-xl font-bold rounded',
    variantClass,
    size,
    { 'opacity-50 cursor-not-allowed': disabled }
    ]" :disabled="disabled" @click="handleClick">
        <slot />
    </button>
</template>

<script setup lang="ts">
import { computed, defineProps, defineEmits } from 'vue'

const props = defineProps({
    type: { type: String, default: 'button' },
    variant: {
        type: String,
        default: 'primary', // options: primary, secondary, danger
    },
    disabled: Boolean,
    size: String
})

const emit = defineEmits(['click'])

const handleClick = (e: Event) => {
    if (!props.disabled) emit('click', e)
}

const variantClass = computed(() => {
    switch (props.variant) {
        case 'secondary':
            return 'bg-gray-200 text-gray-800 hover:bg-gray-300'
        case 'danger':
            return 'bg-red-500 text-white hover:bg-red-600'
        case 'primary':
        default:
            return 'text-white hover:bg-secondaryColor bg-PimaryColor'
    }
})
</script>
