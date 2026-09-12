<template>
    <button type="button" :class="[
        'relative inline-flex h-5 w-11 items-center rounded-full transition-colors cursor-pointer',
        modelValue ? 'bg-green-500' : 'bg-gray-300'
    ]" @click="toggle" :id="id">
        <span :class="[
            'inline-block h-3 w-3 transform rounded-full bg-white transition-transform',
            modelValue ? 'translate-x-6' : 'translate-x-1'
        ]"></span>
    </button>
    <span class="ml-2 text-sm font-medium text-gray-700" v-if="showLabel">
        {{ modelValue ? activeLabel : inactiveLabel }}
    </span>
</template>

<script setup>
const props = defineProps({
    id:{
        type: String,
        required: false
    },
    modelValue: {
        type: Boolean,
        required: true,
    },
    activeLabel: {
        type: String,
        default: 'Active',
    },
    inactiveLabel: {
        type: String,
        default: 'Inactive',
    },
    showLabel:{
        type:Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue']);

const toggle = () => {
    emit('update:modelValue', !props.modelValue);
};
</script>

