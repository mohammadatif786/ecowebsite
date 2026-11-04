<template>
  <!-- LinkUp Live Dialog -->
  <div v-if="isOpen" 
    class="fixed inset-0 z-50" 
    :aria-hidden="!isOpen"
    @click="handleBackdropClick">
      
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm fade-in"></div>

      <div class="relative min-h-full flex items-end sm:items-center justify-center p-4">
        <div role="dialog" 
          aria-modal="true" 
          aria-labelledby="liveTitle"
          @click.stop
          class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-soft slide-up">
          
          <div class="h-1.5" style="background:linear-gradient(90deg, #0ea5e9, #38bdf8, #dfff00);"></div>

          <!-- Header -->
          <div class="p-5 sm:p-6 flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
              <div class="h-20 w-40 rounded-2xl flex items-center justify-center border"
                style="border-color:rgba(14,165,233,.25);">
                <img src="/storage/avatars/livelogo.png" alt="Live Logo" class="w-full h-full" />
              </div>
              <div>
                <h2 id="liveTitle" class="text-slate-900 text-lg sm:text-xl font-black tracking-tight">
                  Welcome to LinkUp Live
                </h2>
                <p class="text-slate-500 text-xs sm:text-sm mt-1">
                  A family, community, and social space — go live with purpose.
                </p>
              </div>
            </div>

            <button @click="closeDialog"
              class="h-10 w-10 rounded-2xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 active:scale-[.98] transition"
              aria-label="Close">
              ✕
            </button>
          </div>

          <!-- Body -->
          <div class="px-5 sm:px-6 pb-6 space-y-4">

            <!-- Rules / Purpose -->
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-sm text-slate-800 leading-relaxed">
                LinkUp Live is for <span class="font-extrabold">news, community updates, and positive content</span>.
                This is a <span class="font-extrabold">family and community</span> platform.
              </p>

              <div class="mt-3 space-y-2">
                <p class="text-sm text-slate-800 leading-relaxed">
                  <span class="font-extrabold">No nudity, no pornography, and no sexually explicit content</span> — ever.
                </p>
                <p class="text-sm text-slate-800 leading-relaxed">
                  <span class="font-extrabold">No "OnlyFans-style" activity</span> (adult content, sexual performances, or content created to sell explicit material).
                </p>
                <p class="text-sm text-slate-800 leading-relaxed">
                  Violations may result in <span class="font-extrabold text-red-600">permanent removal</span> from LinkUp.
                </p>
              </div>
            </div>

            <!-- Subscription + Gifts -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
              <div class="flex items-start gap-3">
                <div class="h-11 w-11 rounded-2xl flex items-center justify-center border"
                     style="background:rgba(223,255,0,.22); border-color:rgba(223,255,0,.45);">
                  <span class="text-xs font-black text-slate-900">GIFTS</span>
                </div>
                <div class="flex-1">
                  <div class="text-slate-900 font-black tracking-tight">Subscription required</div>
                  <div class="text-slate-500 text-sm mt-1 leading-relaxed">
                    To go live, you must be subscribed. Subscriptions enable the <span class="font-extrabold">gifting system</span>,
                    help reduce spam, and support safety and moderation.
                  </div>
                  <div class="text-slate-500 text-sm mt-2 leading-relaxed">
                    When eligible, you can <span class="font-extrabold">earn money through gifts</span> received during your live streams.
                  </div>

                  <div class="mt-3 flex flex-wrap items-center gap-2">
                    <button @click="handleSubscribe"
                      class="rounded-2xl px-4 py-2.5 text-sm font-extrabold text-white transition active:scale-[.99] glow-blue"
                      style="background:linear-gradient(180deg, #0ea5e9, #0284c7);">
                      {{ isSubscribed ? 'Subscribed' : 'Subscribe' }}
                    </button>

                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-extrabold text-slate-800">
                      <span class="h-2.5 w-2.5 rounded-full" :class="isSubscribed ? 'bg-emerald-500' : 'bg-red-500'"></span>
                      {{ isSubscribed ? 'Subscribed' : 'Not subscribed' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Agreement -->
            <label class="flex items-start gap-3 text-sm text-slate-700 select-none">
              <input v-model="hasAgreed" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300">
              <span>
                I understand and agree to follow LinkUp Live community rules.
              </span>
            </label>

            <!-- Actions -->
            <div class="grid grid-cols-2 gap-3">
              <button @click="closeDialog"
                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 font-extrabold text-slate-700 hover:bg-slate-50 active:scale-[.99] transition">
                Cancel
              </button>

              <button @click="handleContinue" 
                :disabled="!canContinue"
                class="rounded-2xl px-4 py-3 font-extrabold text-white transition active:scale-[.99]"
                :class="canContinue ? '' : 'opacity-60 cursor-not-allowed'"
                style="background:linear-gradient(90deg, #0ea5e9, #0284c7);">
                Continue
              </button>
            </div>

            <p class="text-[11px] text-slate-500 leading-relaxed">
              Tip: If you see inappropriate content, report it. We protect the community first.
            </p>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

// Props
const props = defineProps({
  // You can pass initial subscription status from parent
  initialSubscribed: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['open', 'close', 'subscribe', 'continue'])

// State
const isOpen = ref(false)
const isSubscribed = ref(props.initialSubscribed)
const hasAgreed = ref(false)

// Local storage key
const LS_KEY = 'linkup_live_subscribed'

// Computed
const canContinue = computed(() => {
  return isSubscribed.value && hasAgreed.value
})

// Methods
const openDialog = () => {
  isOpen.value = true
  hasAgreed.value = false
  emit('open')
}

const closeDialog = () => {
  isOpen.value = false
  hasAgreed.value = false
  emit('close')
}

const handleBackdropClick = (event) => {
  if (event.target === event.currentTarget) {
    closeDialog()
  }
}

const handleSubscribe = () => {
  // Replace with your real subscription flow
  // For demo, we'll just toggle the subscription status
  isSubscribed.value = !isSubscribed.value
  localStorage.setItem(LS_KEY, isSubscribed.value.toString())
  emit('subscribe', isSubscribed.value)
}

const handleContinue = () => {
  if (!canContinue.value) return
  
  closeDialog()
  // Replace with your real "Go Live" flow
  emit('continue')
}

const handleEscapeKey = (event) => {
  if (event.key === 'Escape' && isOpen.value) {
    closeDialog()
  }
}

// Lifecycle
onMounted(() => {
  // Load subscription status from localStorage
  const saved = localStorage.getItem(LS_KEY)
  if (saved !== null) {
    isSubscribed.value = saved === 'true'
  }
  
  // Add escape key listener
  window.addEventListener('keydown', handleEscapeKey)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscapeKey)
})

// Expose methods for parent component
defineExpose({
  openDialog,
  closeDialog
})
</script>

<style scoped>
.shadow-soft {
  box-shadow: 0 18px 55px rgba(2, 16, 28, 0.22);
}

.glow-blue {
  box-shadow: 0 18px 40px rgba(14, 165, 233, 0.28);
}

.fade-in {
  animation: fadeIn 0.16s ease-out;
}

.slide-up {
  animation: slideUp 0.18s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    transform: translateY(14px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
</style>
