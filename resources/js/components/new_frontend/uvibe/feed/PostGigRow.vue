<template>
  <!--
    Campus Gig: the pay, and what you can do about it.
      - someone else's gig: Apply -> "Applied" (waiting for the poster) -> "Paid"
      - your own gig: see who applied and pay them (from your wallet, with your PIN)
  -->
  <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
    <p class="text-xs font-bold text-slate-500">{{ money(post.price) }} gig pay</p>

    <template v-if="post.mine">
      <button type="button" @click="$emit('applicants', post)" class="btn btn-primary px-3 py-1.5 text-xs">
        Applicants · {{ gig.applicants || 0 }}<template v-if="gig.paid"> ({{ gig.paid }} paid)</template>
      </button>
    </template>
    <span v-else-if="gig.status === 'paid'" class="chip" style="background:#05966914;color:#059669;border-color:#05966922">✓ Paid</span>
    <span v-else-if="gig.status === 'applied'" class="chip" style="background:#f59e0b14;color:#b45309;border-color:#f59e0b33">⏳ Applied · waiting for pay</span>
    <button v-else type="button" @click="apply" :disabled="busy" class="btn btn-primary px-3 py-1.5 text-xs disabled:opacity-60">
      {{ busy ? 'Applying…' : 'Apply' }}
    </button>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { money } from '../../../../services/uvibeFormat';
import { firstError, notify } from '../../../../services/uvibe';
import { applyToGig } from '../../../../services/uvibePosts';

const props = defineProps({ post: { type: Object, required: true } });
const emit = defineEmits(['applicants', 'updated']);

const gig = computed(() => props.post.gig || {});
const busy = ref(false);

const apply = async () => {
  if (busy.value) return;
  busy.value = true;
  try {
    const res = await applyToGig(props.post.id);
    notify(res.message);
    emit('updated', { ...props.post, gig: { ...gig.value, status: res.status || 'applied' } });
  } catch (e) {
    notify(firstError(e, 'Could not apply'));
  } finally {
    busy.value = false;
  }
};
</script>
