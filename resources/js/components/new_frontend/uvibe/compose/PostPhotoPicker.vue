<template>
  <div>
    <div v-if="modelValue.length" class="flex gap-2 overflow-x-auto hide-scroll">
      <div v-for="(m, i) in modelValue" :key="m.url" class="relative shrink-0">
        <img :src="m.url" class="h-24 w-24 rounded-xl object-cover" />
        <button type="button" @click="remove(i)" aria-label="Remove photo"
                class="absolute -top-1.5 -right-1.5 h-5 w-5 rounded-full bg-rose-500 text-white grid place-items-center">
          <X class="w-3 h-3" />
        </button>
        <span v-if="modelValue.length > 1" class="absolute bottom-1 left-1 bg-black/55 text-white text-[9px] font-black px-1.5 rounded-full">{{ i + 1 }}</span>
      </div>
      <label v-if="modelValue.length < max" class="h-24 w-24 shrink-0 rounded-xl border-2 border-dashed border-violet-200 text-violet-600 grid place-items-center cursor-pointer hover:bg-violet-50">
        <input type="file" accept="image/*" multiple class="hidden" @change="pick" />
        <Plus class="w-5 h-5" />
      </label>
    </div>

    <label v-else class="w-full rounded-2xl border-2 border-dashed border-violet-200 text-violet-600 font-black py-3 flex items-center justify-center gap-2 cursor-pointer hover:bg-violet-50 transition">
      <input type="file" accept="image/*" multiple class="hidden" @change="pick" />
      <ImagePlus class="w-4 h-4" />Add photos (select multiple for a carousel)
    </label>
  </div>
</template>

<script setup>
import { onBeforeUnmount, watch } from 'vue';
import { ImagePlus, Plus, X } from 'lucide-vue-next';
import { notify } from '../../../../services/uvibe';

const props = defineProps({
  modelValue: { type: Array, default: () => [] }, // [{ file, url }]
  max: { type: Number, default: 10 },
  maxKb: { type: Number, default: 10 * 1024 },
});
const emit = defineEmits(['update:modelValue']);

const pick = (e) => {
  const room = props.max - props.modelValue.length;
  const picked = Array.from(e.target.files || []);
  e.target.value = '';

  const images = picked.filter((f) => f.type.startsWith('image/'));
  if (images.length < picked.length) notify('Only photos can be added here');
  const small = images.filter((f) => f.size <= props.maxKb * 1024);
  if (small.length < images.length) notify(`Photos must be under ${Math.round(props.maxKb / 1024)} MB`);
  if (small.length > room) notify(`You can add up to ${props.max} photos`);

  const added = small.slice(0, Math.max(room, 0)).map((file) => ({ file, url: URL.createObjectURL(file) }));
  if (added.length) emit('update:modelValue', [...props.modelValue, ...added]);
};

const remove = (i) => emit('update:modelValue', props.modelValue.filter((_, idx) => idx !== i));

// Free a preview as soon as its photo is gone (removed, or the form was cleared).
watch(() => props.modelValue, (now, before = []) => {
  before.filter((m) => !now.includes(m)).forEach((m) => URL.revokeObjectURL(m.url));
});
onBeforeUnmount(() => props.modelValue.forEach((m) => URL.revokeObjectURL(m.url)));
</script>
