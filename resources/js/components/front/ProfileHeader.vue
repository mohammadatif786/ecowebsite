<template>
  <header class="relative w-full">
    <h1
      class="absolute top-8 text-xs tracking-wide text-black left-[30px] w-[124px] max-md:left-5 max-sm:left-5 max-sm:text-sm"
    >
      Other users profiles
    </h1>

    <div class="absolute top-8 right-[60px] max-md:right-[60px] flex items-center gap-2">
      <button
        class="text-sm tracking-wide text-black"
        @click="$emit('applyFilters')"
        aria-label="Apply filters to user profiles"
      >
        Apply Filters
      </button>

      <i class="ti ti-filter w-6 text-black h-[22px] max-sm:hidden"></i>
    </div>
  </header>
</template>

<script setup lang="ts">
defineEmits<{
  applyFilters: [];
}>();
</script>
