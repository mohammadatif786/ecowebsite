<template>
    <button :disabled="loading" :type="type" :class="[
        'px-2 md:px-4 cursor-pointer py-1 text-sm sm:text-sm md:text-2xl font-bold hover:bg-secondaryColor bg-primary dark:bg-lime-300 text-blue-500 rounded',
        variant === 'primary'
            ? 'hover:bg-lime-500'
            : variant === 'secondary'
                ? 'bg-gray-200 text-gray-800 hover:bg-gray-300'
                : 'bg-white text-black',
    ]">
        <div class="flex items-center justify-center">
            <slot />
            <div v-if="loading" :class="[
                'ml-1 border-3  border-t-transparent rounded-full animate-spin',
                'w-' + size,
                'h-' + size,
            ]"></div>
        </div>
    </button>
</template>
<script setup lang="ts">
    import { ButtonHTMLAttributes } from 'vue';

    defineProps({
        loading: {
            type: Boolean,
            default: false,
        },
        size: {
            type: Number,
            default: 6,
        },
        variant: {
            type: String,
            default: "primary", // 'primary' | 'secondary' | 'default'
        },
        type: {
            type: String as () => ButtonHTMLAttributes['type'],
            default: 'button'
        }
    });
</script>
