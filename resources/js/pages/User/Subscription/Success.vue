<template>
  <AuthenticatedLayout>
    <Head title="Payment Success" />
    <main
      class="relative mx-auto max-w-none bg-white w-[778px] py-10 px-4 max-md:w-full max-md:px-2 text-center rounded-xl shadow-md"
    >
      <div class="flex flex-col items-center space-y-6">
        <svg
          class="w-20 h-20 text-green-500"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>

        <h1 class="text-3xl font-bold text-gray-800">Payment Successful</h1>
        <p class="text-gray-600">
          Thank you! Your payment has been processed successfully.
        </p>

        <button
          @click="goToDashboard"
          class="mt-6 bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-6 rounded-lg transition-all duration-200"
        >
          Go to Dashboard
        </button>
      </div>
    </main>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";

const goToDashboard = () => {
  router.visit(route("frontend.findmatch")); // Adjust the route name as per your app
};
</script>

<style scoped>
/* You can customize or add animations/transitions here */
</style>
