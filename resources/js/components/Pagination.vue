<template>
  <nav v-if="links.length > 3" class="flex justify-center mt-4">
    <template v-for="(link, key) in links">
      <div
        v-if="link.url === null"
        :key="key"
        class="px-3 py-2 text-gray-500 border rounded"
        v-html="link.label"
      />
      <Link
        v-else
        :key="key + 1"
        :href="link.url"
        class="px-3 py-2 border rounded"
        :class="{ 'bg-blue-500 text-white': link.active }"
        v-html="link.label"
      />
    </template>
  </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
  links: { url: string | null; label: string; active: boolean }[];
}>();
</script>
