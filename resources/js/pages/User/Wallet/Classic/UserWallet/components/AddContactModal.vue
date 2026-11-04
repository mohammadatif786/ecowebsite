<template>
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden">
    <div class="p-8">
      <div class="flex justify-between mb-6">
        <h3 class="text-2xl font-bold">Add Contact</h3>
        <button @click="$emit('close')">
          <X class="w-5 h-5" />
        </button>
      </div>

      <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Name</label>
      <input v-model="contactName" type="text" placeholder="e.g. Dad"
        class="w-full p-4 bg-slate-50 rounded-xl mb-4 font-medium outline-none border border-transparent focus:bg-white focus:border-linkup-blue" />

      <!-- Search Section integrated with LinkTag styling -->
      <label class="block text-xs font-bold uppercase text-slate-400 mb-2">LinkTag Search</label>
      <div class="relative mb-8">
        <input v-model="searchQuery" @input="handleSearch" @focus="handleSearchFocus" type="text"
          placeholder="e.g. ~Dad"
          class="w-full p-4 bg-slate-50 rounded-xl font-medium outline-none border border-transparent focus:bg-white focus:border-linkup-blue" />
        <div class="absolute right-4 top-1/2 transform -translate-y-1/2">
          <Search v-if="!searching" class="w-5 h-5 text-slate-400" />
          <div v-else class="w-5 h-5 border-2 border-linkup-blue border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Dropdown reused from existing but styled -->
        <div v-if="showDropdown && (searchResults.length > 0 || (searchQuery && !searching))"
          class="absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto mt-2 z-50">
          <div v-for="user in filteredResults" :key="user.id" @click="selectUser(user)"
            class="flex items-center gap-3 p-3 hover:bg-slate-50 cursor-pointer transition-colors border-b border-slate-100 last:border-b-0">
            <div
              class="w-8 h-8 rounded-full bg-linkup-blue/10 flex items-center justify-center flex-shrink-0 font-bold text-linkup-blue text-xs">
              {{ user.name.substring(0, 2).toUpperCase() }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-bold text-slate-700 text-sm truncate">{{ user.name }}</div>
              <div class="text-xs text-slate-500 truncate">{{ user.display_tag || user.linkup_id }}</div>
            </div>
          </div>
          <div v-if="searchResults.length === 0 && searchQuery && !searching" class="p-4 text-center">
            <p class="text-slate-500 text-xs">No users found</p>
          </div>
        </div>
      </div>

      <button @click="handleSubmit"
        class="w-full py-4 bg-linkup-blue text-white rounded-xl font-bold text-lg hover:opacity-90 transition-opacity">
        Save Contact
      </button>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { X, Search } from 'lucide-vue-next';
import axios from 'axios';

interface User {
  id: number;
  name: string;
  linkup_id: string;
  type: string;
  avatar?: string;
  email: string;
  display_tag: string;
}

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'submit', data: { name: string; userId: number }): void;
}>();

const searchQuery = ref('');
const searchResults = ref<User[]>([]);
const selectedUser = ref<User | null>(null);
const contactName = ref('');
const searching = ref(false);
const searchType = ref('all');
const showDropdown = ref(false);
const dropdownPosition = ref({ top: 0, left: 0 });

const filteredResults = computed(() => {
  if (searchType.value === 'all') {
    return searchResults.value;
  }
  return searchResults.value.filter(user => user.type === searchType.value);
});

let searchTimeout: number;

const handleSearch = () => {
  clearTimeout(searchTimeout);

  if (!searchQuery.value.trim()) {
    searchResults.value = [];
    selectedUser.value = null;
    return;
  }

  searchTimeout = setTimeout(async () => {
    searching.value = true;
    try {
      const response = await axios.get('/search/linkup-id', {
        params: {
          query: searchQuery.value,
          search_type: searchType.value === 'linkup_id' ? 'linkup_id' : searchType.value === 'name' ? 'name' : 'all'
        }
      });

      if (response.data.success) {
        searchResults.value = response.data.users;
      }
    } catch (error) {
      console.error('Search error:', error);
      searchResults.value = [];
    } finally {
      searching.value = false;
    }
  }, 500);
};

const handleSearchFocus = (event: FocusEvent) => {
  showDropdown.value = true;
  // Position dropdown below the search input
  const target = event.target as HTMLInputElement;
  const rect = target.getBoundingClientRect();
  dropdownPosition.value = {
    top: rect.bottom + window.scrollY + 4,
    left: rect.left + window.scrollX
  };
};

const selectUser = (user: User) => {
  selectedUser.value = user;
  // Don't auto-fill name field - keep it independent
  searchQuery.value = user.name; // Show selected user's name in search field
  showDropdown.value = false;
};

const handleSubmit = () => {

  if (selectedUser.value && contactName.value.trim()) {
    emit('submit', {
      name: contactName.value.trim(),
      userId: selectedUser.value.id
    });
  }
};

watch(searchType, () => {
  if (searchQuery.value.trim()) {
    handleSearch();
  }
});

</script>
