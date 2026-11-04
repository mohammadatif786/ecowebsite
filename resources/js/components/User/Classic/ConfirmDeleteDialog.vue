<script setup lang="ts">
import { ref, watch } from 'vue'
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/admin/ui/dialog';
import { Button } from '@/components/admin/ui/button'

    const props = defineProps<{
        form: any
        title?: string
        description?: string
        processing?: boolean
        modelValue?: boolean // <-- External open state
    }>()

    const emit = defineEmits<{
        (e: 'submit'): void
        (e: 'update:modelValue', value: boolean): void // <-- Sync back open state
    }>()

    const open = ref(props.modelValue ?? false)

    // Sync with v-model
    watch(() => props.modelValue, (val) => {
        open.value = val
    })

    watch(open, (val) => {
        emit('update:modelValue', val)
    })
</script>
<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <slot name="trigger">
                <!-- <Button variant="destructive">Delete</Button> -->
            </slot>
        </DialogTrigger>
        <DialogContent>
            <form class="space-y-6" @submit.prevent="emit('submit')">
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{ props.title }}</DialogTitle>
                    <DialogDescription> {{ props.description }} </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex flex-row justify-between gap-2">
                    <Button variant="destructive" :disabled="form.processing" type="submit"> Delete </Button>
                    <DialogClose as-child>
                        <Button variant="default" class="bg-gray-300 text-gary-700 hover:bg-gray-200"
                            @click="open==false">Cancel</Button>
                    </DialogClose>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
