<script setup lang="ts">
    import { computed } from 'vue';
    import { Link } from '@inertiajs/vue3';

    const props = defineProps<{
        href: string;
        active?: boolean;
        preserveScroll?: boolean
    }>();

    const classes = computed(() =>
        props.active
            ? 'router-link-active router-link-exact-active relative flex cursor-pointer items-center pt-2 text-sm sm:text-base group hover:text-primary'
            : 'relative flex cursor-pointer items-center pt-2 text-sm sm:text-base group hover:text-primary',
    );

    // router-link-active router-link-exact-active relative flex cursor-pointer items-center py-2 text-sm sm:text-base group hover:text-primary
</script>
<template>
    <Link :href="href" :class="classes" :preserve-scroll="preserveScroll ? preserveScroll : true">
    <slot />
    </Link>
</template>
