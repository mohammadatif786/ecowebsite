<template>
  <div v-if="modelValue" class="fixed inset-0 z-[120] flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm p-2 sm:p-4 fade">
    <div class="card w-full max-w-lg overflow-hidden shadow-2xl bg-white border-none flex flex-col max-h-[92vh] sm:max-h-[85vh]">

      <!-- HEADER -->
      <div class="shrink-0 p-5 flex items-center gap-3 border-b border-slate-100">
        <button @click="$emit('update:modelValue', false)" class="h-10 w-10 rounded-xl border border-slate-200 grid place-items-center hover:bg-slate-50 transition shrink-0">
          <i data-lucide="arrow-left" class="w-5 h-5 text-slate-600"></i>
        </button>
        <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 grid place-items-center shrink-0">
          <i data-lucide="user" class="w-5 h-5"></i>
        </div>
        <h3 class="text-xl font-black text-slate-900 flex-1">Account</h3>
        <button @click="$emit('update:modelValue', false)" class="h-10 w-10 rounded-xl hover:bg-slate-100 grid place-items-center transition shrink-0">
          <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
        </button>
      </div>

      <!-- FORM BODY -->
      <div class="flex-1 overflow-y-auto p-6 space-y-5 custom-scroll">

        <div class="space-y-1.5">
          <label class="text-[13px] font-black text-slate-500 ml-1">Full Name</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="e.g. Cassius"
            class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50/30 px-4 py-3.5 font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white transition"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-[13px] font-black text-slate-500 ml-1">Handle</label>
          <input
            v-model="form.handle"
            type="text"
            placeholder="@username"
            class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50/30 px-4 py-3.5 font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white transition"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-[13px] font-black text-slate-500 ml-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="you@example.com"
            class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50/30 px-4 py-3.5 font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white transition"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-[13px] font-black text-slate-500 ml-1">Phone</label>
          <input
            v-model="form.phone"
            type="tel"
            placeholder="(242) 555-0100"
            class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50/30 px-4 py-3.5 font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white transition"
          />
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div class="space-y-1.5">
            <label class="text-[13px] font-black text-slate-500 ml-1">City</label>
            <input
              v-model="form.city"
              type="text"
              placeholder="Nassau"
              class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50/30 px-3 py-3.5 font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white transition text-sm"
            />
          </div>
          <div class="space-y-1.5">
            <label class="text-[13px] font-black text-slate-500 ml-1">State</label>
            <input
              v-model="form.state"
              type="text"
              placeholder="N.P."
              class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50/30 px-3 py-3.5 font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white transition text-sm"
            />
          </div>
          <div class="space-y-1.5">
            <label class="text-[13px] font-black text-slate-500 ml-1">Country</label>
            <input
              v-model="form.country"
              type="text"
              placeholder="Bahamas"
              class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50/30 px-3 py-3.5 font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white transition text-sm"
            />
          </div>
        </div>

      </div>

      <!-- FOOTER ACTION -->
      <div class="shrink-0 p-6 pt-2">
        <button
          @click="handleSave"
          :disabled="saving"
          class="w-full py-4 rounded-[20px] bg-blue-600 text-white font-black text-lg hover:bg-blue-700 active:scale-[0.98] transition disabled:opacity-50 shadow-xl shadow-blue-200"
        >
          {{ saving ? 'Saving Changes...' : 'Save Changes' }}
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { DB } from '@/components/new_frontend/MockDataStore.js';

const props = defineProps({
  modelValue: Boolean,
  profileUser: Object
});

const emit = defineEmits(['update:modelValue', 'toast', 'profile-updated']);

const form = reactive({
  name: props.profileUser?.name || '',
  handle: props.profileUser?.handle || '',
  email: props.profileUser?.email || '',
  phone: props.profileUser?.phone || '',
  city: props.profileUser?.city || '',
  state: props.profileUser?.state || '',
  country: props.profileUser?.country || '',
});

const saving = ref(false);

const handleSave = () => {
  saving.value = true;

  // Simulate API call or use Inertia
  router.put(route('new_frontend.profile.update'), form, {
    preserveScroll: true,
    onSuccess: () => {
      DB.set('lk_user', { ...props.profileUser, ...form });
      emit('profile-updated', { ...props.profileUser, ...form });
      emit('toast', 'Account updated successfully');
      emit('update:modelValue', false);
    },
    onFinish: () => saving.value = false
  });
};

onMounted(() => {
  if (window.lucide) window.lucide.createIcons();
});

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    // Sync form with props when opened
    Object.assign(form, {
      name: props.profileUser?.name || '',
      handle: props.profileUser?.handle || '',
      email: props.profileUser?.email || '',
      phone: props.profileUser?.phone || '',
      city: props.profileUser?.city || '',
      state: props.profileUser?.state || '',
      country: props.profileUser?.country || '',
    });
    setTimeout(() => { if (window.lucide) window.lucide.createIcons(); }, 10);
  }
});
</script>

<style scoped>
.custom-scroll::-webkit-scrollbar {
  width: 5px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.card {
  border-radius: 32px;
}
@media (max-width: 640px) {
  .card {
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
  }
}
</style>
