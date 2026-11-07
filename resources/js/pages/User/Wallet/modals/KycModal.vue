<template>
  <div v-if="isOpen" class="fixed inset-0 z-[125] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center" @click.self="close">
    <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col max-h-[92vh]">
      <div class="p-5 text-white shrink-0" style="background:linear-gradient(135deg,#0b2942,#1e3a5f)">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-black flex items-center gap-2"><i data-lucide="shield-check" class="w-5 h-5"></i>Verify your identity</h3>
          <button @click="close" class="hover:bg-white/20 p-1 rounded transition" aria-label="Close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <p class="text-white/70 text-xs mt-1 font-bold">Unlocks Top Up, Send, Request, Coins and bank transfers.</p>
        <div v-if="mode === 'form'" class="flex gap-1.5 mt-3">
          <span v-for="n in 2" :key="n" :class="['h-1.5 flex-1 rounded-full', n <= step ? 'bg-white' : 'bg-white/25']"></span>
        </div>
      </div>

      <div class="p-5 overflow-y-auto">
        <!-- Under review -->
        <div v-if="mode === 'pending'" class="text-center py-4">
          <span class="h-14 w-14 rounded-2xl bg-amber-100 text-amber-600 grid place-items-center mx-auto"><i data-lucide="hourglass" class="w-7 h-7"></i></span>
          <p class="font-black text-lg mt-3">We're reviewing your details</p>
          <p class="text-sm text-slate-500 font-bold mt-1">This usually takes 1–2 business days. We'll notify you when it's done.</p>
          <button @click="close" class="btn btn-ghost w-full py-3 mt-5">Close</button>
        </div>

        <!-- Form -->
        <template v-else>
          <div v-if="kycStatus === 'rejected' && step === 1" class="rounded-2xl bg-rose-50 border border-rose-200 p-3 text-rose-700 text-[12px] font-bold mb-4">
            Your last submission wasn't approved. Please check your details and upload clear photos.
          </div>

          <!-- Step 1: details -->
          <div v-if="step === 1" class="space-y-3">
            <div>
              <label class="text-xs font-black text-slate-600 ml-1">Full legal name</label>
              <input v-model="form.full_name" autocomplete="name" placeholder="As shown on your ID" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-lkblue" />
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="text-xs font-black text-slate-600 ml-1">Date of birth</label>
                <input v-model="form.dob" type="date" :max="maxDob" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-3 font-bold outline-none focus:border-lkblue" />
              </div>
              <div>
                <label class="text-xs font-black text-slate-600 ml-1">ID type</label>
                <select v-model="form.id_type" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-3 font-bold outline-none bg-white focus:border-lkblue">
                  <option value="" disabled>Choose</option>
                  <option value="passport">Passport</option>
                  <option value="national_id">National ID</option>
                  <option value="drivers_license">Driver's licence</option>
                </select>
              </div>
            </div>
            <div>
              <label class="text-xs font-black text-slate-600 ml-1">ID number</label>
              <input v-model="form.id_number" autocomplete="off" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-lkblue" />
            </div>
            <div>
              <label class="text-xs font-black text-slate-600 ml-1">Home address</label>
              <textarea v-model="form.address" rows="2" autocomplete="street-address" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-lkblue resize-none"></textarea>
            </div>
            <div>
              <label class="text-xs font-black text-slate-600 ml-1">Tax ID <span class="text-slate-400">(optional)</span></label>
              <input v-model="form.tax_id" autocomplete="off" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold outline-none focus:border-lkblue" />
            </div>
          </div>

          <!-- Step 2: documents -->
          <div v-else class="space-y-3">
            <p class="text-sm font-bold text-slate-500">Clear, well-lit photos. JPG or PNG, up to 5 MB each.</p>
            <label v-for="slot in slots" :key="slot.key"
              class="flex items-center gap-3 rounded-2xl border-2 border-dashed p-3 cursor-pointer transition"
              :class="files[slot.key] ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 hover:border-lkblue'">
              <img v-if="previews[slot.key]" :src="previews[slot.key]" class="h-14 w-20 rounded-xl object-cover" alt="" />
              <span v-else class="h-14 w-20 rounded-xl bg-slate-100 text-slate-400 grid place-items-center"><i :data-lucide="slot.icon" class="w-6 h-6"></i></span>
              <span class="flex-1 min-w-0">
                <span class="block font-black text-sm">{{ slot.label }} <span v-if="slot.optional" class="text-slate-400 font-bold">(optional)</span></span>
                <span class="block text-[11px] font-bold" :class="files[slot.key] ? 'text-emerald-600' : 'text-slate-400'">{{ files[slot.key] ? '✓ ' + files[slot.key].name : slot.hint }}</span>
              </span>
              <input type="file" accept="image/jpeg,image/png" :capture="slot.key === 'selfie' ? 'user' : undefined" class="hidden" @change="pick(slot.key, $event)" />
            </label>
            <label class="flex items-start gap-2 text-[12px] font-bold text-slate-600 pt-1">
              <input v-model="form.terms_acknowledged" type="checkbox" class="mt-0.5 rounded" />
              I confirm these details are true and agree that LinkUp may verify them.
            </label>
          </div>

          <p v-if="error" class="text-[12px] font-bold text-rose-600 mt-3 bg-rose-50 p-2 rounded-xl">{{ error }}</p>

          <div class="flex gap-2 mt-5">
            <button v-if="step === 2" @click="step = 1; icons()" class="btn btn-ghost px-5 py-3">Back</button>
            <button v-if="step === 1" @click="toDocuments" class="btn btn-primary flex-1 py-3">Continue</button>
            <button v-else @click="submit" :disabled="form.processing" class="btn btn-primary flex-1 py-3 disabled:opacity-60">{{ form.processing ? 'Uploading…' : 'Submit for review' }}</button>
          </div>
          <p class="text-[11px] text-slate-400 font-bold text-center mt-3 flex items-center justify-center gap-1"><i data-lucide="lock" class="w-3 h-3"></i>Your documents are stored privately and only seen by our review team.</p>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, reactive, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  kycStatus: { type: String, default: 'none' }, // none | pending | approved | rejected
  defaultName: { type: String, default: '' },
});
const emit = defineEmits(['submitted']);

const MAX_BYTES = 5 * 1024 * 1024;
const slots = [
  { key: 'front', label: 'ID — front', hint: 'Photo of the front of your ID', icon: 'id-card' },
  { key: 'back', label: 'ID — back', hint: 'Skip for passports', icon: 'id-card', optional: true },
  { key: 'selfie', label: 'Selfie', hint: 'Your face, clearly visible', icon: 'smile' },
];

const isOpen = ref(false);
const step = ref(1);
const error = ref('');
const files = reactive({ front: null, back: null, selfie: null });
const previews = reactive({ front: '', back: '', selfie: '' });

const form = useForm({
  full_name: '', dob: '', id_type: '', id_number: '', address: '', tax_id: '',
  terms_acknowledged: false, kyc_documents: [],
});

const mode = computed(() => (props.kycStatus === 'pending' ? 'pending' : 'form'));
const maxDob = computed(() => {
  const d = new Date(); d.setFullYear(d.getFullYear() - 18);
  return d.toISOString().slice(0, 10);
});

const icons = () => nextTick(() => window.lucide?.createIcons());

const open = () => {
  error.value = ''; step.value = 1;
  if (!form.full_name) form.full_name = props.defaultName || '';
  isOpen.value = true; icons();
};
const close = () => { isOpen.value = false; };

const pick = (key, event) => {
  const file = event.target.files?.[0];
  event.target.value = '';
  if (!file) return;
  if (!['image/jpeg', 'image/png'].includes(file.type)) { error.value = 'Please choose a JPG or PNG photo.'; return; }
  if (file.size > MAX_BYTES) { error.value = 'That photo is over 5 MB.'; return; }
  error.value = '';
  if (previews[key]) URL.revokeObjectURL(previews[key]);
  files[key] = file;
  previews[key] = URL.createObjectURL(file);
};

const toDocuments = () => {
  error.value = '';
  const f = form;
  if (!f.full_name.trim() || !f.dob || !f.id_type || !f.id_number.trim() || !f.address.trim()) {
    error.value = 'Please fill in every field (tax ID is optional).'; return;
  }
  if (f.dob > maxDob.value) { error.value = 'You must be 18 or older to use the wallet.'; return; }
  step.value = 2; icons();
};

const submit = () => {
  error.value = '';
  if (!files.front || !files.selfie) { error.value = 'Add the front of your ID and a selfie.'; return; }
  if (!form.terms_acknowledged) { error.value = 'Please tick the confirmation box.'; return; }
  form.kyc_documents = [files.front, files.back, files.selfie].filter(Boolean);
  form.post(route('frontend.user.wallet.kyc.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      close();
      window.toast?.('✅ Submitted — we’ll review it shortly');
      emit('submitted');
    },
    onError: (errors) => {
      error.value = Object.values(errors)[0] || 'Could not submit. Please try again.';
      if (errors.full_name || errors.dob || errors.id_type || errors.id_number || errors.address || errors.tax_id) step.value = 1;
      icons();
    },
  });
};

defineExpose({ open });
</script>
