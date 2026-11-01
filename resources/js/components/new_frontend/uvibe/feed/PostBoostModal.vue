<template>
  <!-- "Send a Boost": coins only — appreciation for the poster, never cash. -->
  <Modal ref="modalRef" maxWidth="max-w-md">
    <div v-if="post">
      <div class="p-4 flex items-center justify-between border-b border-slate-100">
        <div>
          <h3 class="text-lg font-black flex items-center gap-1.5"><Zap class="w-5 h-5 text-orange-500" />Send a Boost</h3>
          <p class="text-[11px] text-slate-400">Send a boost to a university student · Balance: 🪙 {{ num(coins) }}</p>
        </div>
        <button type="button" @click="close" class="text-slate-400" aria-label="Close"><X class="w-5 h-5" /></button>
      </div>
      <p v-if="loading" class="p-6 text-center text-sm font-bold text-slate-500">Loading Boosts…</p>
      <div v-else class="p-3 grid grid-cols-3 gap-2">
        <button v-for="b in boosts" :key="b.key" type="button" :disabled="b.coins > coins || sending" @click="send(b)"
                class="rounded-2xl border-2 border-slate-100 p-2.5 text-center disabled:opacity-40">
          <div class="text-2xl">{{ b.emoji }}</div>
          <p class="text-[11px] font-black mt-1 leading-tight">{{ b.label }}</p>
          <span class="inline-flex items-center gap-1 mt-1 rounded-full text-white px-2 py-0.5 text-[10px] font-black bg-gradient-to-r from-orange-500 to-amber-500">
            <Zap class="w-3 h-3" />{{ b.coins }}
          </span>
        </button>
      </div>
      <p v-if="error" class="px-4 pb-3 text-sm font-bold text-rose-600">{{ error }}</p>
    </div>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import { X, Zap } from 'lucide-vue-next';
import Modal from '../../ui/Modal.vue';
import { firstError, notify } from '../../../../services/uvibe';
import { fetchBoosts, sendBoost } from '../../../../services/uvibePosts';
import { num } from '../../../../services/uvibeFormat';

// boosted: {post_id, boost_coins} — the post's new Boost total.
const emit = defineEmits(['boosted']);

const modalRef = ref(null);
const post = ref(null);
const boosts = ref([]);
const coins = ref(0);
const loading = ref(false);
const sending = ref(false);
const error = ref('');

const open = async (p) => {
  post.value = p;
  error.value = '';
  loading.value = true;
  modalRef.value?.open();
  try {
    const res = await fetchBoosts();
    boosts.value = res.boosts;
    coins.value = res.coins;
  } catch (e) {
    error.value = firstError(e, 'Could not load Boosts');
  } finally {
    loading.value = false;
  }
};
const close = () => modalRef.value?.close();

const send = async (boost) => {
  if (sending.value) return;
  if (coins.value < boost.coins) { notify('Not enough coins — top up your wallet first.'); return; }
  sending.value = true;
  error.value = '';
  try {
    const res = await sendBoost(post.value.id, boost.key);
    coins.value = res.coins;
    notify(res.message);
    emit('boosted', { post_id: res.post_id, boost_coins: res.boost_coins });
    close();
  } catch (e) {
    error.value = firstError(e, 'Could not send the Boost');
  } finally {
    sending.value = false;
  }
};

defineExpose({ open, close });
</script>
