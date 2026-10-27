<template>
  <div v-if="isOpen"
       class="fixed inset-0 z-[1050] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-300"
       @click.self="close">
    <div class="bg-white rounded-[2rem] max-w-md w-full p-6 shadow-2xl relative border border-slate-100 flex flex-col">
      <!-- Header -->
      <div class="flex items-start justify-between gap-3 mb-2">
        <div>
          <h3 class="text-xl font-black text-slate-900 flex items-center gap-2">
            🎓 My universities
          </h3>
        </div>
        <button @click="close" class="h-8 w-8 rounded-full bg-slate-100 hover:bg-slate-200 grid place-items-center text-slate-500 transition shrink-0">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>
      </div>

      <p class="text-xs sm:text-sm text-slate-500 font-semibold leading-relaxed">
        The schools you attend or attended. In U Vibe you can post to these — and only these. Your main one is selected first.
      </p>

      <!-- Content area -->
      <div class="my-6">
        <div v-if="myUniversities.length === 0" class="text-center py-4">
          <p class="font-black text-slate-800 text-sm mb-5">
            You haven’t added a university yet.
          </p>
          <button @click="handleAddUniversity"
                  class="w-full py-3.5 px-4 rounded-2xl border-2 border-dashed border-violet-300 text-violet-600 font-black text-sm hover:bg-violet-50 transition flex items-center justify-center gap-2 active:scale-95 shadow-sm">
            + Add a university
          </button>
        </div>

        <div v-else class="space-y-2.5 max-h-60 overflow-y-auto hide-scroll pr-1">
          <div v-for="(uni, index) in myUniversities" :key="uni.name"
               @click="selectUniversity(uni)"
               class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-slate-100/60 transition cursor-pointer">
            <div class="flex items-center gap-3 min-w-0">
              <span class="w-9 h-9 rounded-xl bg-violet-100 text-violet-700 font-black text-xs grid place-items-center shrink-0">
                {{ uni.flag || '🎓' }}
              </span>
              <div class="min-w-0">
                <p class="font-black text-sm text-slate-900 truncate">{{ uni.name }}</p>
                <p class="text-[11px] font-bold text-slate-400">{{ index === 0 ? 'Main University' : 'Secondary' }}</p>
              </div>
            </div>
            <button @click.stop="removeUniversity(uni)" class="text-slate-400 hover:text-rose-500 transition p-1.5 rounded-lg hover:bg-rose-50" title="Remove">
              <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
          </div>

          <button @click="handleAddUniversity"
                  class="w-full mt-3 py-3 px-4 rounded-2xl border-2 border-dashed border-violet-200 text-violet-600 font-black text-xs hover:bg-violet-50 transition flex items-center justify-center gap-1.5 active:scale-95">
            + Add another university
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const isOpen = ref(false);
const myUniversities = ref([]);

const emit = defineEmits(['addUniversity', 'universitySelected', 'openCompose']);

const open = (unis = []) => {
  isOpen.value = true;
  if (unis && unis.length) {
    myUniversities.value = [...unis];
  } else {
    try {
      const saved = localStorage.getItem('lk_my_universities');
      myUniversities.value = saved ? JSON.parse(saved) : [];
    } catch (e) {
      myUniversities.value = [];
    }
  }
};

const close = () => {
  isOpen.value = false;
};

const handleAddUniversity = () => {
  close();
  emit('addUniversity');
};

const selectUniversity = (uni) => {
  emit('universitySelected', uni);
  close();
};

const removeUniversity = (uni) => {
  myUniversities.value = myUniversities.value.filter(u => u.name !== uni.name);
  try {
    localStorage.setItem('lk_my_universities', JSON.stringify(myUniversities.value));
  } catch (e) {}
};

defineExpose({
  open,
  close,
  isOpen
});
</script>
