<template>
  <Dialog v-model:open="localOpen">
    <DialogTrigger as-child>
      <span></span>
      <!-- Dummy trigger to enable programmatic open -->
    </DialogTrigger>
    <DialogContent>
      <form class="space-y-6" @submit="deleteUser">
        <DialogHeader class="space-y-3">
          <DialogTitle>Are you sure you want to delete this record?</DialogTitle>
          <DialogDescription>
            Once deleted, this action cannot be undone. Please confirm to permanently
            delete the record.
          </DialogDescription>
        </DialogHeader>

        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button variant="secondary" @click="closeModal">Cancel</Button>
          </DialogClose>

          <Button variant="destructive" :disabled="form.processing" type="submit">
            Delete Record
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { Button } from "@/components/admin/ui/button";
import { ref } from "vue";
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/admin/ui/dialog";

const props = defineProps<{
  id: number;
  route: string;
  open: boolean;
  extraParams?: Record<string, any>;
}>();
const emit = defineEmits(["update:open"]);
const localOpen = ref(props.open);
const form = useForm({});

const deleteUser = (e: Event) => {
  e.preventDefault();

  form.delete(route(props.route, { id: props.id, ...(props.extraParams || {}) }), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onFinish: () => form.reset(),
  });
};

const closeModal = () => {
  form.clearErrors();
  form.reset();
  emit("update:open", false);
};
</script>
