<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DialogClose,
} from "@/components/front/ui/dialog";
import { Button } from "@/components/front/ui/button";
const props = defineProps<{
  DialogTitleString?: string | undefined;
  simpleClass?: string | undefined;
}>();
</script>
<template>
  <Dialog>
    <DialogTrigger as-child>
      <Button variant="ghost" class="font-medium" :class="props.simpleClass">
        <span class="font-bold">{{ props.DialogTitleString || "Apply Filters" }}</span>
        <svg
          v-if="!props.DialogTitleString"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="lucide lucide-funnel-icon lucide-funnel"
        >
          <path
            d="M10 20a1 1 0 0 0 .553.895l2 1A1 1 0 0 0 14 21v-7a2 2 0 0 1 .517-1.341L21.74 4.67A1 1 0 0 0 21 3H3a1 1 0 0 0-.742 1.67l7.225 7.989A2 2 0 0 1 10 14z"
          />
        </svg>
      </Button>
    </DialogTrigger>
    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{ props.DialogTitleString || "Apply Filters" }}</DialogTitle>
      </DialogHeader>
      <div class="py-3">
        <slot></slot>
      </div>
      <!-- <DialogFooter>
        <DialogClose as-child>
          <Button class="font-medium" @click="$emit('apply')">Apply</Button>
        </DialogClose>
      </DialogFooter> -->
    </DialogContent>
  </Dialog>
</template>
