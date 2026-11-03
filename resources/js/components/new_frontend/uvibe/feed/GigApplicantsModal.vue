<template>
  <!-- Your Campus Gig: who applied, and paying them from your wallet (with your transaction PIN). -->
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div v-if="post">
      <div class="p-4 flex items-center justify-between border-b border-slate-100">
        <div class="min-w-0">
          <h3 class="text-lg font-black truncate">Applicants</h3>
          <p class="text-[11px] text-slate-400 truncate">{{ post.title }} · {{ money(pay) }} gig pay each</p>
        </div>
        <button type="button" @click="close" class="text-slate-400" aria-label="Close"><X class="w-5 h-5" /></button>
      </div>

      <p v-if="loading" class="p-6 text-center text-sm font-bold text-slate-500">Loading applicants…</p>
      <p v-else-if="!applicants.length" class="p-6 text-center text-sm font-bold text-slate-500">No one has applied yet.</p>

      <ul v-else class="p-3 space-y-2">
        <li v-for="a in applicants" :key="a.id" class="rounded-2xl border border-slate-100 p-3">
          <div class="flex items-center gap-3">
            <span class="h-10 w-10 rounded-full bg-violet-100 text-violet-700 grid place-items-center font-black shrink-0">{{ a.name.slice(0, 1).toUpperCase() }}</span>
            <div class="min-w-0 flex-1">
              <p class="font-black text-sm truncate">{{ a.name }}</p>
              <p class="text-[11px] text-slate-500">Applied {{ a.applied }}</p>
            </div>
            <span v-if="a.status === 'paid'" class="chip shrink-0" style="background:#05966914;color:#059669;border-color:#05966922">✓ Paid {{ money(a.paid_amount) }}</span>
            <button v-else-if="payingId !== a.id" type="button" @click="startPay(a)" class="btn btn-primary px-3 py-1.5 text-xs shrink-0">Pay {{ money(pay) }}</button>
          </div>

          <!-- PIN step -->
          <form v-if="payingId === a.id" class="mt-3 flex items-center gap-2" @submit.prevent="confirmPay(a)">
            <input v-model="pin" type="password" inputmode="numeric" autocomplete="off" maxlength="4" placeholder="4-digit PIN"
                   class="flex-1 min-w-0 rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold outline-none focus:border-[#2f9bef]" />
            <button type="submit" :disabled="paying || pin.length !== 4" class="btn btn-primary px-3 py-2 text-xs disabled:opacity-60">
              {{ paying ? 'Paying…' : 'Confirm' }}
            </button>
            <button type="button" @click="cancelPay" class="text-xs font-black text-slate-500 px-1">Cancel</button>
          </form>
          <p v-if="payingId === a.id" class="mt-1.5 text-[11px] text-slate-500">Paid from your wallet balance to {{ a.name }}.</p>
        </li>
      </ul>

      <p v-if="error" class="px-4 pb-3 text-sm font-bold text-rose-600">{{ error }}</p>
    </div>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import { X } from 'lucide-vue-next';
import Modal from '../../ui/Modal.vue';
import { firstError, notify } from '../../../../services/uvibe';
import { fetchApplicants, payApplicant } from '../../../../services/uvibePosts';
import { money } from '../../../../services/uvibeFormat';

// updated: the post with its new applicant / paid counts.
const emit = defineEmits(['updated']);

const modalRef = ref(null);
const post = ref(null);
const applicants = ref([]);
const pay = ref(0);
const loading = ref(false);
const error = ref('');
const payingId = ref(null);
const pin = ref('');
const paying = ref(false);

const open = async (p) => {
  post.value = p;
  applicants.value = [];
  error.value = '';
  cancelPay();
  loading.value = true;
  modalRef.value?.open();
  try {
    const res = await fetchApplicants(p.id);
    applicants.value = res.applicants;
    pay.value = res.pay;
  } catch (e) {
    error.value = firstError(e, 'Could not load applicants');
  } finally {
    loading.value = false;
  }
};
const close = () => modalRef.value?.close();

const startPay = (a) => { error.value = ''; pin.value = ''; payingId.value = a.id; };
const cancelPay = () => { payingId.value = null; pin.value = ''; };

const confirmPay = async (a) => {
  if (paying.value || pin.value.length !== 4) return;
  paying.value = true;
  error.value = '';
  try {
    const res = await payApplicant(post.value.id, a.id, pin.value);
    Object.assign(a, { status: 'paid', paid_amount: res.application.paid_amount, paid_at: res.application.paid_at });
    notify(res.message);
    cancelPay();
    const paid = applicants.value.filter((x) => x.status === 'paid').length;
    emit('updated', { ...post.value, gig: { ...post.value.gig, applicants: applicants.value.length, paid } });
  } catch (e) {
    error.value = firstError(e, 'Could not send the payment');
    pin.value = '';
  } finally {
    paying.value = false;
  }
};

defineExpose({ open, close });
</script>
