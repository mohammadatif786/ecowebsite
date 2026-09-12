<template>
    <svg xmlns="http://www.w3.org/2000/svg" :width="size" :height="size" :viewBox="`0 0 128 128`" :class="circleClass">
        <circle cx="64" cy="64" r="62" :fill="bgColor" />
        <text x="50%" y="50%" alignment-baseline="middle" text-anchor="middle" :font-size="fontSize" :fill="textColor">
            {{ initials }}
        </text>
    </svg>
</template>

<script setup lang="ts">
import { computed, defineProps } from 'vue';
const props = defineProps({
    firstName: { type: String, required: true },
    lastName: { type: String, required: true },
    size: { type: String, default: '128' },
    bgColor: { type: String, default: 'oklch(0.86 0.18 111.71)' },
    textColor: { type: String, default: '#ffffff' },
});

const initials = computed(() => {
    const firstLetter = props.firstName?.charAt(0)?.toUpperCase();
    const lastLetter = props.lastName?.charAt(0)?.toUpperCase();
    return `${firstLetter}${lastLetter}`;
});

const fontSize = computed(() => {
    return Math.floor(parseInt(props.size) / 2);
});

const circleClass = computed(() => {
    return 'rounded-full'; // Ensures the circle is fully rounded
});
</script>
