<template>
  <AppLayout>
    <div class="">
      <!-- Create Button -->
      <div class="flex items-center justify-end mb-4 flex-wrap gap-2">
        <Link :href="route('organizer.event.sponsor.create')"
          class="btn px-5 py-2.5 font-black text-white flex items-center gap-2 rounded-xl"
          style="background: linear-gradient(90deg,#2dd4bf,#eab308)">
          <Plus class="w-4 h-4" />
          Create New Sponsor
        </Link>
      </div>

      <!-- Search -->
      <input
        type="text"
        placeholder="Search sponsors by name or description..."
        v-model="searchQuery"
        @input="searchSponsors"
        class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-400 mb-4"
      />

      <!-- Grid -->
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="sponsor in sponsors.data"
          :key="sponsor.id"
          class="card overflow-hidden border-none"
          style="border-left: 4px solid #2dd4bf"
        >
          <!-- Header -->
          <div class="p-3 flex items-center justify-between">
            <span
              class="px-3 py-1 rounded-full text-[11px] font-black"
              :class="sponsor.status === 'active' || sponsor.status === 1 || sponsor.status === true ? 'bg-emerald-500 text-white' : 'bg-slate-300 text-slate-600'"
            >
              {{ (sponsor.status === 'active' || sponsor.status === 1 || sponsor.status === true) ? 'ACTIVE' : 'INACTIVE' }}
            </span>
            <div class="flex gap-1.5">
              <Link :href="route('organizer.event.sponsor.edit', sponsor.id)"
                class="h-8 w-8 rounded-lg border border-slate-200 grid place-items-center hover:bg-slate-50">
                <Pencil class="w-4 h-4" />
              </Link>
              <button @click="deleteSponsor(sponsor)"
                class="h-8 w-8 rounded-lg border border-slate-200 grid place-items-center hover:bg-slate-50">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Image -->
          <img
            :src="getImageUrl(sponsor)"
            class="w-full h-36 object-cover"
            :alt="sponsor.name"
          />

          <!-- Content -->
          <div class="p-4">
            <p class="font-black">{{ sponsor.name }}</p>
            <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ sponsor.description || '' }}</p>

            <p v-if="sponsor.event?.title"
              class="text-[12px] text-slate-500 mt-2 flex items-center gap-1.5">
              <Calendar class="w-3 h-3" />
              {{ sponsor.event.title }}
            </p>

            <p class="text-[12px] text-slate-400 mt-1 flex items-center gap-1.5">
              <Clock class="w-3 h-3" />
              Created: {{ formatDate(sponsor.created_at) }}
            </p>
          </div>
        </div>

        <div v-if="!sponsors.data || sponsors.data.length === 0"
          class="col-span-full text-center text-slate-400 font-bold py-10">
          No sponsors found.
        </div>
      </div>

      <!-- Pagination -->
      <div class="flex justify-center mt-6" v-if="sponsors.data && sponsors.data.length > 0">
        <Pagination :links="sponsors.links" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import Pagination from '@/components/ui/Pagination.vue';
import { Plus, Pencil, Trash2, Calendar, Clock } from 'lucide-vue-next';

const props = defineProps<{
  sponsors: {
    data: Array<any>;
    links: Array<any>;
  };
  appURL: string;
  filters?: {
    search?: string;
  };
}>();

const searchQuery = ref(props.filters?.search || '');

const getImageUrl = (sponsor: any) => {
  if (sponsor.image_url) return sponsor.image_url;
  if (sponsor.image_object) return `${props.appURL}${sponsor.image_object}`;
  return sponsor.image || '';
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const searchSponsors = () => {
  router.get(route('organizer.event.sponsor.index'), {
    search: searchQuery.value
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

const deleteSponsor = (sponsor: any) => {
  if (confirm(`Are you sure you want to delete the sponsor "${sponsor.name}"?`)) {
    router.delete(route('organizer.event.sponsor.destroy', sponsor.id), {
      onSuccess: () => {
        toast.success('Sponsor deleted successfully!');
      },
      onError: () => {
        toast.error('Failed to delete sponsor.');
      }
    });
  }
};
</script>

<style scoped>
.card {
  background: #ffffff;
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 22px;
  box-shadow: 0 10px 26px rgba(2, 6, 23, 0.08);
}
</style>
