<template>
  <AppLayout>
    <div class="">
      <!-- Create Button -->
      <div class="flex items-center justify-end mb-4 flex-wrap gap-2">
        <Link :href="route('organizer.event.coupons.create')"
          class="btn px-5 py-2.5 font-black text-white flex items-center gap-2 rounded-xl"
          style="background: linear-gradient(90deg,#2f9bef,#eab308)">
          <Plus class="w-4 h-4" />
          Create New Coupon
        </Link>
      </div>

      <!-- Search -->
      <input
        type="text"
        placeholder="Search coupons by code or title..."
        v-model="searchQuery"
        @input="searchCoupons"
        class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-400 mb-4"
      />

      <!-- Grid -->
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="coupon in coupons.data"
          :key="coupon.id"
          class="card overflow-hidden border-none"
          :style="`border-left: 4px solid ${isExpired(coupon.expiry_date) ? '#e11d48' : '#2dd4bf'}`"
        >
          <!-- Header -->
          <div class="p-3 flex items-center justify-between">
            <span
              class="px-3 py-1 rounded-full text-[11px] font-black"
              :class="isExpired(coupon.expiry_date) ? 'bg-rose-500 text-white' : 'bg-emerald-500 text-white'"
            >
              {{ isExpired(coupon.expiry_date) ? 'EXPIRED' : 'ACTIVE' }}
            </span>
            <div class="flex gap-1.5">
              <Link :href="route('organizer.event.coupon.edit', coupon.id)"
                class="h-8 w-8 rounded-lg border border-slate-200 grid place-items-center hover:bg-slate-50">
                <Pencil class="w-4 h-4" />
              </Link>
              <button @click="deleteCoupon(coupon)"
                class="h-8 w-8 rounded-lg border border-slate-200 grid place-items-center hover:bg-slate-50">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Image -->
          <img
            :src="coupon.image_object ? `${appURL}${coupon.image_object}` : ''"
            class="w-full h-36 object-cover"
            :alt="coupon.title"
          />

          <!-- Uses Count -->
          <div class="px-4 pt-3">
            <div class="rounded-xl bg-blue-50 text-center py-2.5 font-black text-indigo-600">
              {{ coupon.uses || 0 }}
            </div>
          </div>

          <!-- Content -->
          <div class="p-4">
            <p class="font-black">{{ coupon.title }}</p>
            <p class="text-sm text-slate-500 mt-1">{{ coupon.code }}</p>

            <p class="mt-2">
              <span class="text-2xl font-black text-indigo-600">
                {{ coupon.discount_type === 'percentage' || coupon.discount_type === 'percentage' ? coupon.discount + '%' : '$' + coupon.discount }}
              </span>
              <span class="text-sm font-bold text-slate-400">OFF</span>
            </p>

            <p v-if="coupon.event?.title"
              class="text-[12px] text-slate-500 mt-2 flex items-center gap-1.5">
              <Calendar class="w-3 h-3" />
              {{ coupon.event.title }}
            </p>

            <p class="text-[12px] text-slate-400 mt-1 flex items-center gap-1.5">
              <Clock class="w-3 h-3" />
              {{ isExpired(coupon.expiry_date) ? 'Expired' : 'Expires' }}: {{ formatDate(coupon.expiry_date) }}
            </p>
          </div>
        </div>

        <div v-if="!coupons.data || coupons.data.length === 0"
          class="col-span-full text-center text-slate-400 font-bold py-10">
          No coupons found.
        </div>
      </div>

      <!-- Pagination -->
      <div class="flex justify-center mt-6" v-if="coupons.data && coupons.data.length > 0">
        <Pagination :links="coupons.links" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/organizer/AppLayout.vue';
import Pagination from '@/components/ui/Pagination.vue';
import { Plus, Pencil, Trash2, Tag, Calendar, Clock } from 'lucide-vue-next';

const props = defineProps<{
  coupons: {
    data: Array<any>;
    links: Array<any>;
  };
  appURL: string;
}>();

const searchQuery = ref('');

const isExpired = (date: string) => {
  return new Date(date) < new Date();
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const searchCoupons = () => {
  router.get(route('organizer.event.coupon.index'), {
    search: searchQuery.value
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

const deleteCoupon = (coupon: any) => {
  if (confirm(`Are you sure you want to delete the coupon "${coupon.title}"?`)) {
    router.delete(route('organizer.event.coupon.destroy', coupon.id), {
      onSuccess: () => {
        toast.success('Coupon deleted successfully!');
      },
      onError: () => {
        toast.error('Failed to delete coupon.');
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
