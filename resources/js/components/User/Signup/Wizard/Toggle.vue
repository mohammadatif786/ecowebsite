<script setup lang="ts">
// On/off row ("Show my age on my profile").
import { useId } from 'vue';
import { switchKnob, switchTrack } from './styles';

const on = defineModel<boolean>({ required: true });
defineProps<{ label: string }>();
const id = useId();
</script>

<template>
    <div
        style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 18px; border-radius: 14px; background: #ffffff; border: 1.5px solid #dce3f0"
    >
        <span :id="id" style="font-size: 15px; font-weight: 700">{{ label }}</span>
        <button type="button" role="switch" :aria-checked="on ? 'true' : 'false'" :aria-labelledby="id" :style="switchTrack(on)" @click="on = !on">
            <span :style="switchKnob(on)"></span>
        </button>
    </div>
</template>
