<template>
  <div v-if="isOpen"
       class="fixed inset-0 z-[1050] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-300 overflow-y-auto"
       @click.self="close">
    <div class="bg-white rounded-[2rem] max-w-lg w-full shadow-2xl overflow-hidden flex flex-col border border-slate-100 my-auto transform transition-all">
      <!-- Header Section with Gradient -->
      <div class="p-6 sm:p-7 text-center text-white relative"
           style="background: linear-gradient(135deg, #f97316 0%, #e11d48 55%, #c026d3 100%)">
        <div class="text-3xl sm:text-4xl mb-3 flex items-center justify-center gap-3">🌴 🤝 🌺</div>
        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight mb-2">Keep the vibes clean</h2>
        <p class="text-white/95 text-xs sm:text-sm font-semibold leading-relaxed max-w-md mx-auto">
          A friendly reminder: LinkUp is a family-oriented community, built to bring the Caribbean together through music, food, events, campus life and culture.
        </p>
      </div>

      <!-- Rules Body Section -->
      <div class="p-5 sm:p-6 space-y-4 bg-white">
        <!-- Rule 1 -->
        <div class="flex items-start gap-4 pb-3.5 border-b border-slate-100">
          <div class="w-11 h-11 rounded-2xl bg-amber-50 flex items-center justify-center text-xl shrink-0 border border-amber-100/80 shadow-sm mt-0.5">
            🚫
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-black text-slate-900 text-sm sm:text-base leading-snug">No nudity. No pornography.</h3>
            <p class="text-xs sm:text-sm text-slate-600 font-medium leading-relaxed mt-0.5">
              Nude, sexually explicit or pornographic photos, videos, text or links are strictly prohibited — in Vibes, U Vibe, Reels, comments and messages.
            </p>
          </div>
        </div>

        <!-- Rule 2 -->
        <div class="flex items-start gap-4 pb-3.5 border-b border-slate-100">
          <div class="w-11 h-11 rounded-2xl bg-amber-50 flex items-center justify-center text-xl shrink-0 border border-amber-100/80 shadow-sm mt-0.5">
            🪙
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-black text-slate-900 text-sm sm:text-base leading-snug">Coins are for good vibes</h3>
            <p class="text-xs sm:text-sm text-slate-600 font-medium leading-relaxed mt-0.5">
              Boosts, Big Ups and coins reward creativity and community. Posting explicit content to earn them is not allowed.
            </p>
          </div>
        </div>

        <!-- Rule 3 -->
        <div class="flex items-start gap-4 pb-3.5 border-b border-slate-100">
          <div class="w-11 h-11 rounded-2xl bg-amber-50 flex items-center justify-center text-xl shrink-0 border border-amber-100/80 shadow-sm mt-0.5">
            ⛔
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-black text-slate-900 text-sm sm:text-base leading-snug">Break the rule, lose it all</h3>
            <p class="text-xs sm:text-sm text-slate-600 font-medium leading-relaxed mt-0.5">
              All of your coins will be seized, your posts removed, and your account deleted from the app.
            </p>
          </div>
        </div>

        <!-- Rule 4 -->
        <div class="flex items-start gap-4 pb-3.5 border-b border-slate-100">
          <div class="w-11 h-11 rounded-2xl bg-amber-50 flex items-center justify-center text-xl shrink-0 border border-amber-100/80 shadow-sm mt-0.5">
            🚩
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-black text-slate-900 text-sm sm:text-base leading-snug">See something that crosses the line?</h3>
            <p class="text-xs sm:text-sm text-slate-600 font-medium leading-relaxed mt-0.5">
              Tap <span class="font-black text-slate-800">…</span> on the post and choose Report. Our team reviews every report.
            </p>
          </div>
        </div>

        <!-- Footer Statement -->
        <div class="text-center pt-2 pb-1">
          <p class="text-xs sm:text-sm font-black text-slate-800 max-w-sm mx-auto leading-snug">
            Our grandmothers, little cousins and classmates are all on here. Let’s keep it clean. 💛
          </p>
        </div>

        <!-- Action Button -->
        <button @click="close"
                class="w-full py-3.5 px-6 rounded-2xl text-white font-black text-sm sm:text-base shadow-lg shadow-rose-500/20 hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2"
                style="background: linear-gradient(90deg, #f97316 0%, #e11d48 50%, #c026d3 100%)">
          I’ll keep it clean 🤝
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const isOpen = ref(false);

const open = () => {
  isOpen.value = true;
};

const close = () => {
  isOpen.value = false;
};

defineExpose({
  open,
  close,
  isOpen
});
</script>
