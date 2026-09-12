<script setup lang="js">
import { ref, watch } from 'vue'
import { Search } from 'lucide-vue-next'

const searchQuery = ref('')

const emit = defineEmits(['search'])

watch(searchQuery, (newValue) => {
    emit('search', newValue)
})
</script>
<template>
    <div class="flex items-center gap-2 w-1/2">
        <div class="relative w-full">
            <input v-model="searchQuery" type="text" placeholder="Search events..."
                class="w-full rounded-xl border border-indigo-200 bg-white/90 px-3 py-2 text-sm shadow-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200" />
            <Search class="w-4 h-4 text-slate-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" />
        </div>
    </div>
</template>