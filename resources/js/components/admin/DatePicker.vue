<template>
    <!-- v-model="date" -->
    <VueDatePicker date-picker />
</template>
<script setup>
import { ref } from 'vue'
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

</script>
<style>
.dp__input {
    border: none !important;
    border-bottom: 1px solid gray !important;
    background-color: transparent !important;
        border-radius: unset !important;
        border-color: rgb(156 163 175 / var(--tw-border-opacity, 1)) !important
    }

    .dp__input:focus {
        border: none !important;
    }
</style>
