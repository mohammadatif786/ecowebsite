<template>
  <!-- Text card: live preview, palette / style / motion / stickers, one-tap starters and Shuffle. -->
  <div class="rounded-2xl border border-slate-200 p-3">
    <div class="flex flex-wrap gap-3.5 justify-center">
      <div class="w-[168px] shrink-0">
        <UVibeTextCard :card="card" :calm="calm" placeholder="Your text will appear here…"
                       class="aspect-square rounded-[18px] shadow-[0_8px_24px_rgba(15,23,42,.18)]" />
      </div>
      <div class="flex-[1_1_210px] min-w-0">
        <span class="text-[11px] font-bold text-slate-400">Need a spark?</span>
        <div class="flex gap-1.5 overflow-x-auto hide-scroll pt-1.5 pb-0.5">
          <button v-for="(s, i) in options.starters" :key="s" type="button" @click="useStarter(i)"
                  class="shrink-0 rounded-full bg-slate-100 text-slate-700 text-[11px] font-bold px-2.5 py-1.5">
            {{ s.replace(/:? $/, '') }}…
          </button>
        </div>
      </div>
    </div>

    <div class="mt-2.5">
      <div class="flex items-center justify-between gap-2">
        <span class="text-xs font-black text-slate-700">🎨 {{ palette.name }}</span>
        <button type="button" @click="shuffle" :class="chip(false)">🎲 Shuffle look</button>
      </div>

      <div class="flex items-center gap-2 mt-2">
        <span class="text-[11px] font-black text-slate-500 shrink-0 w-[52px]">Palette</span>
        <div class="flex gap-1.5 overflow-x-auto hide-scroll p-[3px] flex-1 min-w-0">
          <button v-for="(p, i) in options.palettes" :key="p.name" type="button" :title="p.name" :aria-label="p.name + ' palette'"
                  @click="set({ pal: i })" class="shrink-0 w-[30px] h-[30px] rounded-full border-2 border-white"
                  :style="{ background: `linear-gradient(135deg,${p.colors.join(',')})`, boxShadow: `0 0 0 2px ${i === modelValue.pal ? '#2563eb' : '#e2e8f0'}` }"></button>
        </div>
      </div>

      <div class="flex items-center gap-2 mt-2">
        <span class="text-[11px] font-black text-slate-500 shrink-0 w-[52px]">Style</span>
        <div class="flex gap-1.5 overflow-x-auto hide-scroll p-[3px] flex-1 min-w-0">
          <button v-for="(f, i) in options.fonts" :key="f.label" type="button" @click="set({ font: i })"
                  :class="chip(i === modelValue.font)" :style="previewCss(f.css)">{{ f.label }}</button>
        </div>
      </div>

      <div class="flex items-center gap-2 mt-2">
        <span class="text-[11px] font-black text-slate-500 shrink-0 w-[52px]">Motion</span>
        <div class="flex gap-1.5 overflow-x-auto hide-scroll p-[3px] flex-1 min-w-0">
          <button v-for="m in options.motions" :key="m.id" type="button" @click="set({ motion: m.id })"
                  :class="chip(m.id === modelValue.motion)">{{ m.label }}</button>
        </div>
      </div>

      <div class="flex items-center gap-2 mt-2">
        <span class="text-[11px] font-black text-slate-500 shrink-0 w-[52px]">Stickers</span>
        <div class="flex gap-1.5 overflow-x-auto hide-scroll p-[3px] flex-1 min-w-0">
          <button type="button" @click="set({ sticker: '' })" :class="chip(!modelValue.sticker)">None</button>
          <button v-for="s in options.stickers" :key="s" type="button" @click="set({ sticker: s })"
                  :class="chip(s === modelValue.sticker)" style="font-size:15px;padding:3px 9px">{{ s }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import UVibeTextCard from '../UVibeTextCard.vue';

const props = defineProps({
  // The look chosen: { pal, font, motion, sticker } (numbers index into the lists in `options`).
  modelValue: { type: Object, required: true },
  // The words — the same text as the description box.
  text: { type: String, default: '' },
  // { palettes, fonts, motions, stickers, starters } from the backend
  options: { type: Object, required: true },
});
const emit = defineEmits(['update:modelValue', 'update:text', 'starter']);

const palette = computed(() => props.options.palettes[props.modelValue.pal] || props.options.palettes[0]);

// The card as the feed will draw it (same shape the backend sends as `text_card`).
const card = computed(() => {
  const font = props.options.fonts[props.modelValue.font] || props.options.fonts[0];
  return {
    text: props.text,
    bg: `linear-gradient(135deg,${palette.value.colors.join(',')})`,
    ink: palette.value.ink,
    font_css: font.css.replace(/\{ink\}/g, palette.value.ink),
    motion: props.modelValue.motion,
    sticker: props.modelValue.sticker,
  };
});

// Typing doesn't re-run the pop-in on every key; changing the look does.
const calm = ref(false);
watch(() => props.text, () => { calm.value = true; });

const set = (change) => {
  calm.value = false;
  emit('update:modelValue', { ...props.modelValue, ...change });
};

const shuffle = () => {
  const r = (n) => Math.floor(Math.random() * n);
  let pal;
  do { pal = r(props.options.palettes.length); } while (pal === props.modelValue.pal && props.options.palettes.length > 1);
  set({
    pal,
    font: r(props.options.fonts.length),
    motion: props.options.motions[r(props.options.motions.length)].id,
    sticker: Math.random() < 0.5 ? '' : props.options.stickers[r(props.options.stickers.length)],
  });
};

// A starter replaces an earlier starter but keeps what the member already typed.
const useStarter = (i) => {
  let rest = props.text || '';
  props.options.starters.forEach((s) => { if (rest.startsWith(s)) rest = rest.slice(s.length); });
  calm.value = true;
  emit('update:text', props.options.starters[i] + rest);
  emit('starter');
};

const chip = (on) => [
  'shrink-0 rounded-full text-[11px] font-black px-[11px] py-1.5 border-2 text-slate-900 transition',
  on ? 'border-lkblue bg-blue-50' : 'border-slate-200 bg-white',
];
// Each style's name drawn in its own font (without the glow/outline that needs a coloured card).
const previewCss = (css) => css.replace(/\{ink\}/g, '#0f172a').replace(/text-shadow:[^;]*;|color:transparent;/g, '');
</script>
