<template>
  <nav v-if="links && links.length > 3" class="pagination">
    <template v-for="(link, index) in links" :key="index">
      <Link
        v-if="link.url"
        :href="link.url"
        :class="[
          'pagination-link',
          { 'active': link.active, 'disabled': !link.url }
        ]"
        v-html="link.label"
      />
      <span
        v-else
        :class="[
          'pagination-link',
          { 'active': link.active, 'disabled': !link.url }
        ]"
        v-html="link.label"
      />
    </template>
  </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}>();
</script>

<style scoped>
.pagination {
  display: flex;
  gap: 0.25rem;
  align-items: center;
}

.pagination-link {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  background-color: white;
  color: #374151;
  text-decoration: none;
  border-radius: 0.375rem;
  transition: all 0.2s;
}

.pagination-link:hover:not(.disabled) {
  background-color: #f3f4f6;
}

.pagination-link.active {
  background-color: #00AEEF;
  color: white;
  border-color: #00AEEF;
}

.pagination-link.disabled {
  color: #9CA3AF;
  cursor: not-allowed;
}
</style>