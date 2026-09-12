<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
        @click="emit('close')">
        <!-- Modal -->
        <div
          class="w-full max-w-md bg-white rounded-3xl shadow-[0_30px_80px_rgba(0,0,0,0.12)] border border-slate-200 overflow-hidden"
          @click.stop>
          <!-- Header -->
          <div class="flex items-start justify-between px-6 py-5 border-b">
            <div>
              <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">
                Verify your phone
              </h2>
              <p class="mt-2 text-sm text-slate-600">
                Enter the 6-digit code sent to
                <span class="font-semibold text-slate-900">
                  {{ currentPhone }}
                </span>
              </p>
            </div>

            <button class="text-slate-400 hover:text-slate-600 text-xl leading-none" @click="emit('close')">
              ✕
            </button>
          </div>

          <!-- Body -->
          <div class="p-6">
            <!-- OTP Inputs -->
            <div class="flex justify-center gap-3 mt-2" @paste.prevent="onOtpPaste">
              <input v-for="(_, i) in 6" :key="i" ref="otpRefs" type="text" inputmode="numeric" maxlength="1" class="w-14 h-16 text-center text-2xl font-bold border-2 rounded-xl border-slate-300
                       focus:border-brand-focus focus:ring-2 focus:ring-brand-focus/30 outline-none transition"
                @input="onOtpInput($event, i)" @keydown.backspace="onOtpBackspace($event, i)" />
            </div>

            <!-- Verify Button -->
            <button @click="verify" class="mt-6 w-full h-14 rounded-xl font-bold
                     bg-gradient-to-r from-brand-darker to-brand-focus
                     shadow-lg hover:brightness-110 active:scale-[0.99] transition">
              Verify & Continue
            </button>

            <!-- Toast -->
            <div v-if="modalToast" :class="['mt-4 px-4 py-3 rounded-xl text-sm border', modalToastTypeClass]">
              {{ modalToast }}
            </div>

            <!-- Footer -->
            <div class="mt-4 flex items-center justify-between text-sm font-semibold">
              <button :disabled="resendDisabled" @click="resend"
                class="text-brand-darker hover:underline disabled:opacity-50">
                Resend code
              </button>

              <span class="text-slate-500">{{ timerText }}</span>
            </div>

            <!-- Secondary actions -->
            <div class="mt-5 flex gap-3">
              <button @click="changePhone" class="flex-1 py-2.5 rounded-xl bg-slate-100 border font-semibold">
                Change number
              </button>

              <button @click="emit('close')" class="flex-1 py-2.5 rounded-xl bg-slate-100 border font-semibold">
                Cancel
              </button>
            </div>

            <!-- Change Phone Modal -->
            <ChangePhoneModal v-if="showChangeModal" :open="showChangeModal" :phone="currentPhone || undefined"
              @close="showChangeModal = false" @submit="onChangePhoneSubmit" />
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import ChangePhoneModal from './modals/ChangePhoneModal.vue'

const props = withDefaults(defineProps<{
  open?: boolean
  user: any
  token?: string
  redirectOnSuccess?: boolean
}>(), {
  open: true,
})

const emit = defineEmits(['close', 'verified'])

const effectiveToken = computed(() => props.token ?? (props.user?.token as string | undefined))

/* ---------------- State ---------------- */
const otpInput = ref('')
const otpRefs = ref<HTMLInputElement[]>([])
const currentPhone = ref<string | undefined>(props.user?.phone ?? props.user?.phone_number)
const showChangeModal = ref(false)

/* ---------------- Toast ---------------- */
const modalToast = ref('')
const modalToastType = ref<'success' | 'error' | 'warn' | null>(null)

/* ---------------- Timer ---------------- */
const resendDisabled = ref(false)
const timerText = ref('')
let cooldownTimer: number | null = null

const now = () => Date.now()

/* ---------------- OTP Input Logic ---------------- */
function onOtpInput(e: Event, index: number) {
  const el = e.target as HTMLInputElement

  if (!/^\d$/.test(el.value)) {
    el.value = ''
    return
  }

  otpInput.value =
    otpInput.value.substring(0, index) +
    el.value +
    otpInput.value.substring(index + 1)

  if (index < 5) {
    otpRefs.value[index + 1]?.focus()
  }

  if (index === 5 && otpInput.value.length === 6) {
    verify()
  }
}

function onOtpBackspace(_: KeyboardEvent, index: number) {
  if (!otpRefs.value[index].value && index > 0) {
    otpRefs.value[index - 1]?.focus()
  }
}

/* ---------------- Paste Support ---------------- */
function onOtpPaste(e: ClipboardEvent) {
  const pasted = e.clipboardData?.getData('text') || ''
  if (!/^\d{6}$/.test(pasted)) return

  otpInput.value = pasted

  pasted.split('').forEach((d, i) => {
    if (otpRefs.value[i]) otpRefs.value[i].value = d
  })

  otpRefs.value[5]?.focus()
  verify()
}

/* ---------------- Verify ---------------- */
async function verify() {
  if (otpInput.value.length !== 6) return

  if (!effectiveToken.value) {
    showToast('error', 'Missing verification token. Please request a new code.')
    return
  }

  try {
    const res = await axios.post(
      `/verify/${effectiveToken.value}`,
      { otp: otpInput.value },
      { headers: { Accept: 'application/json' } }
    )

    showToast('success', 'Verified successfully')

    const shouldRedirect = props.redirectOnSuccess !== false
    if (shouldRedirect) {
      window.location.href = res.data?.redirect || '/'
      return
    }

    emit('verified')
  } catch (err: any) {
    showToast(
      'error',
      err.response?.data?.message || 'Verification failed'
    )
  }
}

/* ---------------- Resend ---------------- */
function startResendCooldown(seconds: number) {
  resendDisabled.value = true
  const end = now() + seconds * 1000

  const tick = () => {
    const remaining = Math.max(0, end - now())
    if (!remaining) {
      resendDisabled.value = false
      timerText.value = ''
      clearInterval(cooldownTimer!)
      cooldownTimer = null
      return
    }
    timerText.value = `Resend in ${Math.ceil(remaining / 1000)}s`
  }

  tick()
  cooldownTimer = setInterval(tick, 250)
}

async function resend() {
  if (!effectiveToken.value) {
    showToast('error', 'Missing verification token. Please request a new code.')
    return
  }

  startResendCooldown(30)
  await axios.post(`/verify-otp/${effectiveToken.value}/resend`)
  showToast('success', 'A new code has been sent')

  otpInput.value = ''
  otpRefs.value.forEach(i => (i.value = ''))
  otpRefs.value[0]?.focus()
}

/* ---------------- Change Phone ---------------- */
function changePhone() {
  showChangeModal.value = true
}

async function onChangePhoneSubmit(phone: string) {
  if (!effectiveToken.value) {
    showToast('error', 'Missing verification token. Please request a new code.')
    return
  }

  await axios.post(`/verify-otp/${effectiveToken.value}/change-phone`, { phone })
  currentPhone.value = phone
  showToast('success', 'Phone updated and OTP sent')
  showChangeModal.value = false
  startResendCooldown(30)
}

/* ---------------- Toast Helper ---------------- */
function showToast(type: 'success' | 'error' | 'warn', msg: string) {
  modalToastType.value = type
  modalToast.value = msg
  setTimeout(() => (modalToast.value = ''), 4000)
}

const modalToastTypeClass = computed(() => ({
  success: 'bg-green-50 border-green-300 text-green-800',
  error: 'bg-red-50 border-red-300 text-red-800',
  warn: 'bg-amber-50 border-amber-300 text-amber-800',
}[modalToastType.value ?? 'success']))

/* ---------------- ESC Close ---------------- */
function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') emit('close')
}

/* ---------------- Lifecycle ---------------- */
onMounted(() => {
  document.addEventListener('keydown', onKeydown)
  startResendCooldown(30)
  requestAnimationFrame(() => otpRefs.value[0]?.focus())
})

onUnmounted(() => {
  document.removeEventListener('keydown', onKeydown)
  if (cooldownTimer) {
    clearInterval(cooldownTimer)
    cooldownTimer = null
  }
})

</script>
