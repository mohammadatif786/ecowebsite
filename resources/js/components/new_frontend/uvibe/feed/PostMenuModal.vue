<template>
  <!-- The "⋯" window on a post. Your own posts: Edit / Delete / Copy link. Other people's: Copy link / Hide / Report. -->
  <Modal ref="modalRef" maxWidth="max-w-sm">
    <div v-if="post" class="p-2">
      <template v-if="post.mine">
        <MenuRow :icon="Pencil" label="Edit post" @click="edit" />
        <MenuRow :icon="Trash2" :label="confirming ? 'Tap again to delete this post' : 'Delete post'" danger :disabled="busy" @click="remove" />
        <MenuRow :icon="Link" label="Copy link" @click="copy" />
      </template>
      <template v-else>
        <MenuRow :icon="Link" label="Copy link" @click="copy" />
        <MenuRow :icon="EyeOff" label="Hide this post" :disabled="busy" @click="hide" />
        <MenuRow :icon="Flag" label="Report post" @click="report" />
      </template>
      <MenuRow :icon="X" label="Cancel" @click="close" />
    </div>
  </Modal>
</template>

<script setup>
import { defineComponent, h, ref } from 'vue';
import { EyeOff, Flag, Link, Pencil, Trash2, X } from 'lucide-vue-next';
import Modal from '../../ui/Modal.vue';
import { firstError, notify } from '../../../../services/uvibe';
import { copyPostLink, deletePost, hidePost } from '../../../../services/uvibePosts';

const emit = defineEmits(['edit', 'report', 'deleted', 'hidden']);

// One row of the window.
const MenuRow = defineComponent({
  props: { icon: [Object, Function], label: String, danger: Boolean, disabled: Boolean },
  setup: (p, { emit: e }) => () => h('button', {
    type: 'button',
    disabled: p.disabled,
    onClick: () => e('click'),
    class: ['w-full flex items-center gap-3 rounded-2xl p-3 text-left disabled:opacity-60', p.danger ? 'text-rose-600 hover:bg-rose-50' : 'hover:bg-slate-50'],
  }, [h(p.icon, { class: ['w-5 h-5', p.danger ? '' : 'text-slate-500'] }), h('span', { class: 'font-bold' }, p.label)]),
  emits: ['click'],
});

const modalRef = ref(null);
const post = ref(null);
const busy = ref(false);
const confirming = ref(false);

const open = (p) => {
  post.value = p;
  confirming.value = false;
  busy.value = false;
  modalRef.value?.open();
};
const close = () => modalRef.value?.close();

const edit = () => { const p = post.value; close(); emit('edit', p); };
const report = () => { const p = post.value; close(); emit('report', p); };
const copy = async () => { await copyPostLink(post.value); close(); };

// Deleting takes a second tap, so a slip doesn't lose a post.
const remove = async () => {
  if (!confirming.value) { confirming.value = true; return; }
  busy.value = true;
  try {
    const res = await deletePost(post.value.id);
    notify(res.message);
    emit('deleted', post.value.id);
    close();
  } catch (e) {
    notify(firstError(e, 'Could not delete the post'));
    confirming.value = false;
  } finally {
    busy.value = false;
  }
};

const hide = async () => {
  busy.value = true;
  try {
    const res = await hidePost(post.value.id);
    notify(res.message);
    emit('hidden', post.value.id);
    close();
  } catch (e) {
    notify(firstError(e, 'Could not hide the post'));
  } finally {
    busy.value = false;
  }
};

defineExpose({ open, close });
</script>
