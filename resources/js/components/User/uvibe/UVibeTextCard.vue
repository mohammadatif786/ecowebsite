<template>
  <!-- A "text card" post: words on a coloured background, with an optional motion and floating stickers. -->
  <div ref="root" class="uv-tc" :class="'uv-tc-' + motion">
    <div class="uv-tc-bg" :style="bgStyle"></div>
    <span v-for="s in stickers" :key="s.i" class="uv-tc-stk" :style="s.style">{{ view.sticker }}</span>
    <p ref="txtEl" class="uv-tc-txt" :style="textStyle">
      <span v-if="!view.text" class="opacity-60">{{ placeholder }}</span>
      <template v-else-if="motion === 'pop'">
        <template v-for="(w, i) in words" :key="i">
          <span v-if="w.space">{{ w.t }}</span>
          <span v-else class="uv-tc-w" :style="{ animationDelay: w.delay + 's' }">{{ w.t }}</span>
        </template>
      </template>
      <template v-else>{{ view.text }}</template>
    </p>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
  // Ready to draw: { text, bg, ink, font_css, motion, sticker } — what the backend sends as `text_card`.
  card: { type: Object, default: null },
  // Or hand over the whole feed post and this draws its text card (older text posts have no `text_card`).
  post: { type: Object, default: null },
  placeholder: { type: String, default: '' },
  // While typing, words don't re-animate on every key.
  calm: { type: Boolean, default: false },
});

const FALLBACK_BG = 'linear-gradient(135deg,#a855f7,#ec4899)';

// What gets drawn: the card handed in, else the one inside a feed post, else a plain card from an older text post.
const view = computed(() => {
  if (props.card) return props.card;
  const p = props.post || {};
  return p.text_card || { text: p.desc || '', bg: p.text_bg || FALLBACK_BG, ink: '#ffffff', font_css: 'font-weight:900;', motion: 'still', sticker: '' };
});
const motion = computed(() => (props.calm && view.value.motion === 'pop' ? 'still' : view.value.motion || 'still'));

const bgStyle = computed(() => {
  const bg = view.value.bg || FALLBACK_BG;
  return bg.startsWith('linear-gradient') ? { backgroundImage: bg } : { backgroundColor: bg };
});

// Longer text gets smaller letters (a % of the card's width).
const size = computed(() => {
  const n = (view.value.text || props.placeholder || '').length;
  return n <= 28 ? 11.5 : n <= 60 ? 9.4 : n <= 110 ? 7.6 : n <= 160 ? 6.4 : n <= 240 ? 5.5 : n <= 400 ? 4.4 : 3.6;
});

// The steps above suit a roomy card. A feed card is only 400px / 500px tall (same as the Vibes page), so longer
// text is shrunk — just as much as needed, never below what fits with some air around it — and short text
// keeps its full size.
const root = ref(null);
const txtEl = ref(null);
const fitScale = ref(1);
let frame = 0;

const fit = async () => {
  await nextTick();
  const box = root.value;
  const p = txtEl.value;
  if (!box || !p || !box.clientHeight) return;
  const saved = p.style.cssText; // measuring below borrows the font size; put it back afterwards
  const room = box.clientHeight * 0.88;
  // Does the text fit at this scale? Uses the very same font size the card will be drawn with.
  const fits = (s) => { p.style.fontSize = `${(size.value * s).toFixed(3)}cqw`; return p.offsetHeight <= room; };
  let best = 1;
  if (!fits(1)) {
    let lo = 0.2;
    let hi = 1;
    for (let i = 0; i < 8; i += 1) { // biggest scale that still fits
      const mid = (lo + hi) / 2;
      if (fits(mid)) lo = mid; else hi = mid;
    }
    // That lands right on the edge where one more word wraps, so step down a little and check the exact value.
    best = Math.round(lo * 0.98 * 1000) / 1000;
    for (let i = 0; i < 6 && !fits(best); i += 1) best = Math.round(best * 0.97 * 1000) / 1000;
  }
  p.style.cssText = saved;
  fitScale.value = best;
};
const refit = () => { cancelAnimationFrame(frame); frame = requestAnimationFrame(fit); };

let watcher = null;
onMounted(() => {
  fit();
  if (typeof ResizeObserver !== 'undefined' && root.value) { watcher = new ResizeObserver(refit); watcher.observe(root.value); }
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(refit);
});
onBeforeUnmount(() => { cancelAnimationFrame(frame); if (watcher) watcher.disconnect(); });
watch(() => [view.value.text, view.value.font_css, motion.value, props.placeholder], fit, { flush: 'post' });

const textStyle = computed(() => `color:${view.value.ink || '#fff'};font-size:${(size.value * fitScale.value).toFixed(3)}cqw;${view.value.font_css || ''}`);

const words = computed(() => {
  let k = 0;
  return (view.value.text || '').split(/(\s+)/).filter((t) => t !== '').map((t) => {
    const space = /^\s+$/.test(t);
    return { t, space, delay: space ? 0 : Math.min(k++ * 0.11, 4).toFixed(2) };
  });
});

const stickers = computed(() => {
  if (!view.value.sticker) return [];
  return Array.from({ length: 9 }, (_, i) => ({
    i,
    style: {
      left: `${(i * 37 + 8) % 92}%`,
      animationDuration: `${5 + ((i * 3) % 5)}s`,
      animationDelay: `-${((i * 1.7) % 6).toFixed(1)}s`,
      fontSize: `${6 + ((i * 5) % 7)}cqw`,
    },
  }));
});
</script>

<style scoped>
.uv-tc { position: relative; overflow: hidden; container-type: inline-size; display: grid; place-items: center; text-align: center; }
.uv-tc-bg { position: absolute; inset: 0; }
.uv-tc-flow .uv-tc-bg { background-size: 300% 300%; animation: uv-flow 9s ease infinite; }
.uv-tc-txt { position: relative; z-index: 2; margin: 0; padding: 0 9cqw; max-width: 100%; line-height: 1.2; white-space: pre-wrap; overflow-wrap: anywhere; }
.uv-tc-pop .uv-tc-w { display: inline-block; opacity: 0; animation: uv-pop .55s cubic-bezier(.2, 1.5, .4, 1) forwards; }
.uv-tc-pulse .uv-tc-txt { animation: uv-pulse 2.6s ease-in-out infinite; }
.uv-tc-stk { position: absolute; bottom: -14cqw; z-index: 1; opacity: .9; pointer-events: none; animation: uv-rise linear infinite; }
@keyframes uv-flow { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
@keyframes uv-pop { from { opacity: 0; transform: translateY(.5em) scale(.6); } to { opacity: 1; transform: none; } }
@keyframes uv-pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
@keyframes uv-rise { to { transform: translateY(-240cqw) rotate(30deg); opacity: 0; } }
@media (prefers-reduced-motion: reduce) {
  .uv-tc-flow .uv-tc-bg, .uv-tc-pulse .uv-tc-txt, .uv-tc-stk { animation: none; }
  .uv-tc-pop .uv-tc-w { animation: none; opacity: 1; }
  .uv-tc-stk { display: none; }
}
</style>
