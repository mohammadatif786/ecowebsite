<script setup lang="ts">
import AppLayout from "@/layouts/app/AppSidebarLayout.vue";
import type { BreadcrumbItemType } from "@/types";
import { Toaster } from "@/components/admin/ui/sonner";
import "vue-sonner/style.css";
import useMessages from "@/composables/useMessages";

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
});

useMessages();
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <slot />
  </AppLayout>
  <Toaster position="top-right" />
</template>
