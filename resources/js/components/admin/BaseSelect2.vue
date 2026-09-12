<script setup lang="ts">
import { ref, computed } from 'vue'
import { Icon } from '@iconify/vue'
import {
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectItemIndicator,
    SelectItemText,
    SelectLabel,
    SelectPortal,
    SelectRoot,
    SelectScrollDownButton,
    SelectScrollUpButton,
    SelectSeparator,
    SelectTrigger,
    SelectValue,
    SelectViewport,
} from 'reka-ui'

defineProps<{
    modelValue: string | undefined
    options: { label: string; items: string[]; disabledItems?: string[] }[]
    placeholder?: string
}>()

defineEmits(['update:modelValue'])

const isDisabled = (group: any, item: string) =>
    group.disabledItems?.includes?.(item)
</script>

<template>
    <SelectRoot v-model="modelValue" @update:modelValue="$emit('update:modelValue', $event)">
        <SelectTrigger
            class="inline-flex min-w-[160px] items-center justify-between rounded-lg px-[15px] text-xs leading-none h-[35px] gap-[5px] bg-white text-grass11 hover:bg-stone-50 border shadow-sm focus:shadow-[0_0_0_2px] focus:shadow-black data-[placeholder]:text-green9 outline-none"
            aria-label="Select an option">
            <SelectValue :placeholder="placeholder || 'Select...'" />
            <Icon icon="radix-icons:chevron-down" class="h-3.5 w-3.5" />
        </SelectTrigger>

        <SelectPortal>
            <SelectContent
                class="min-w-[160px] bg-white rounded-lg border shadow-sm z-[100] will-change-[opacity,transform] data-[side=top]:animate-slideDownAndFade data-[side=right]:animate-slideLeftAndFade data-[side=bottom]:animate-slideUpAndFade data-[side=left]:animate-slideRightAndFade"
                :side-offset="5">
                <SelectScrollUpButton
                    class="flex items-center justify-center h-[25px] bg-white text-violet11 cursor-default">
                    <Icon icon="radix-icons:chevron-up" />
                </SelectScrollUpButton>

                <SelectViewport class="p-[5px]">
                    <template v-for="(group, groupIndex) in options" :key="groupIndex">
                        <SelectLabel class="px-[25px] text-xs leading-[25px] text-mauve11">
                            {{ group.label }}
                        </SelectLabel>
                        <SelectGroup>
                            <SelectItem v-for="(item, itemIndex) in group.items" :key="itemIndex"
                                class="text-xs leading-none text-grass11 rounded-[3px] flex items-center h-[25px] pr-[35px] pl-[25px] relative select-none data-[disabled]:text-mauve8 data-[disabled]:pointer-events-none data-[highlighted]:outline-none data-[highlighted]:bg-green9 data-[highlighted]:text-green1"
                                :value="item" :disabled="isDisabled(group, item)">
                                <SelectItemIndicator
                                    class="absolute left-0 w-[25px] inline-flex items-center justify-center">
                                    <Icon icon="radix-icons:check" />
                                </SelectItemIndicator>
                                <SelectItemText>{{ item }}</SelectItemText>
                            </SelectItem>
                        </SelectGroup>
                        <SelectSeparator class="h-[1px] bg-green6 m-[5px]" v-if="groupIndex < options.length - 1" />
                    </template>
                </SelectViewport>

                <SelectScrollDownButton
                    class="flex items-center justify-center h-[25px] bg-white text-violet11 cursor-default">
                    <Icon icon="radix-icons:chevron-down" />
                </SelectScrollDownButton>
            </SelectContent>
        </SelectPortal>
    </SelectRoot>
</template>
