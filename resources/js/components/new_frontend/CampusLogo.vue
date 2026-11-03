<template>
  <!-- Animated mortarboard used as the U Vibe / campus logo (same glyph + motion as the design). -->
  <span class="cp-logo" aria-hidden="true" :style="{ width: size + 'px', height: size + 'px', fontSize: size + 'px' }">
    <svg :width="size" :height="size" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path class="cp-base" d="M6 12.5V16a6 3 0 0 0 12 0v-3.5" />
      <path class="cp-cap" d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z" />
      <path class="cp-tassel" d="M22 10v6" />
    </svg>
    <span class="cp-spark" style="top:-12%;left:-6%">✦</span>
    <span class="cp-spark" style="top:-18%;right:2%;animation-delay:.18s">✦</span>
    <span class="cp-spark" style="top:26%;right:-16%;animation-delay:.32s;font-size:.3em">✦</span>
  </span>
</template>

<script setup>
defineProps({
  size: { type: Number, default: 30 },
});
</script>

<style scoped>
.cp-logo { position: relative; display: inline-grid; place-items: center; line-height: 0; }
.cp-logo svg { overflow: visible; }
.cp-logo .cp-cap, .cp-logo .cp-tassel, .cp-logo .cp-base { transform-box: fill-box; }
.cp-logo .cp-cap { transform-origin: 50% 60%; animation: cpToss 4.6s cubic-bezier(.3, .7, .3, 1) infinite; }
.cp-logo .cp-tassel { transform-origin: 50% 0; animation: cpTassel 4.6s ease-in-out infinite; }
.cp-logo .cp-base { transform-origin: 50% 100%; animation: cpNod 4.6s ease-in-out infinite; }
.cp-logo .cp-spark { position: absolute; color: #fde68a; font-size: .42em; line-height: 1; opacity: 0; animation: cpTwinkle 4.6s ease-in-out infinite; pointer-events: none; }

@keyframes cpToss {
  0%, 52%, 100% { transform: none; }
  62% { transform: translateY(-5px) rotate(-16deg); }
  72% { transform: translateY(-7px) rotate(12deg); }
  84% { transform: translateY(0) rotate(0); }
  90% { transform: translateY(-1.5px); }
  96% { transform: none; }
}
@keyframes cpTassel {
  0%, 52%, 100% { transform: none; }
  64% { transform: translateY(-5px) rotate(26deg); }
  76% { transform: translateY(-6px) rotate(-18deg); }
  86% { transform: rotate(10deg); }
  94% { transform: rotate(-4deg); }
}
@keyframes cpNod {
  0%, 80%, 100% { transform: none; }
  86% { transform: scaleY(.9); }
  92% { transform: scaleY(1.04); }
}
@keyframes cpTwinkle {
  0%, 58%, 100% { opacity: 0; transform: scale(.3) rotate(0); }
  70% { opacity: 1; transform: scale(1.25) rotate(40deg); }
  84% { opacity: 0; transform: scale(.5) rotate(80deg); }
}
</style>
