<template>
  <div class="flex flex-col gap-3 justify-center">
    <div
      class="w-24 h-24 md:w-28 md:h-28 lg:w-40 lg:h-40 border-4 border-primary rounded-full flex items-center self-center justify-center text-4xl text-sky-500 cursor-pointer hover:bg-sky-50 hover:border-2 transition group"
      @click="triggerFileInput">
      <img v-if="previewUrl || props.defaultImage" :src="previewUrl ? previewUrl : props.defaultImage" alt="Preview"
        class="relative w-full h-full object-cover rounded-full shadow group-hover:scale-110 group-hover:opacity-80 transition-transform duration-300" />
      <i v-if="!(previewUrl || props.defaultImage)" class="ri-add-fill text-7xl"></i>
      <!-- Hidden input for file selection -->
      <input ref="fileInput" class="opacity-0 absolute w-full h-full cursor-pointer hidden" type="file"
        @change="onFileChange" accept="image/*">
    </div>
    <p v-if="props.error" class="mt-1 text-sm text-red-600 self-center">{{ error }}</p>
  </div>
</template>
<script setup>

  import { ref, watch, onBeforeUnmount } from 'vue';
  const emit = defineEmits(['update:modelValue']);


  const props = defineProps({
    modelValue: File,
    error: '',
    defaultImage: null
  });

  watch(() => props.defaultImage, (newVal, oldval) => {
    console.log(oldval, newVal);
    previewUrl.value = props.defaultImage;
  });

  const fileInput = ref(null);
  const previewUrl = ref(null);
  const fileObjectUrl = ref(null)



  const triggerFileInput = (event) => {
    event.stopPropagation();
    fileInput.value.click();
  };




  const onFileChange = (event) => {
    const file = event.target.files[0]
    if (!file) return
    emit('update:modelValue', file)
    if (fileObjectUrl.value) {
      URL.revokeObjectURL(fileObjectUrl.value)
    }
    fileObjectUrl.value = URL.createObjectURL(file)
    previewUrl.value = fileObjectUrl.value
  }

  onBeforeUnmount(() => {
    if (fileObjectUrl.value) {
      URL.revokeObjectURL(fileObjectUrl.value)
    }
  });


</script>
