<script setup lang="ts">
defineProps({
    // Add any props if needed, e.g., user data or navigation items
});
</script>

<template>
    <div class="authenticated-layout">
        <!-- Header or Navigation Bar -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            <div class="py-12">
                <slot></slot>
            </div>
        </main>

        <!-- Footer if needed -->
        <footer class="bg-white shadow mt-auto">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-center text-gray-500">
                © 2025 Your Company
            </div>
        </footer>
    </div>
</template>

<style scoped>
.authenticated-layout {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
</style>
