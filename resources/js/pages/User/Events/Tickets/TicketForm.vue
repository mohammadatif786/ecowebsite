<template>
  <main class="flex-1 bg-gray-100 p-2 md:p-8 rounded-lg w-full">
    <h2 class="text-2xl font-semibold text-center mb-6 text-gray-800">{{ ticket?.id ? 'Edit Ticket' : 'Create Ticket' }}
    </h2>
    <form @submit.prevent="handleSubmit">
      <div class="flex justify-start gap-4 mb-4">
        <BaseSelect name="event" label="Event Name" v-model="form.link_up_event_id" :options="eventOptions"
          placeholder="Select Event" :error="form.errors.link_up_event_id" />
      </div>
      <div class=" mb-2 w-full">
        <BaseInput name="name" label="Ticket Name" class="w-full" v-model="form.name" placeholder="Ticket Name" />
        <span v-if="form.errors.name" class="mt-1 text-sm text-red-600 inline"> {{ form.errors.name }} </span>
      </div>
      <div class="flex justify-between gap-4 mb-2 w-full">
        <BaseInput name="min_qty_per_order" label="Min Qty Per Order" class="w-full" type="number"
          v-model="form.min_qty_per_order" placeholder="Min Qty Per Order" />
        <BaseInput name="max_qty_per_order" label="Max Qty Per Order" class="w-full" type="number"
          v-model="form.max_qty_per_order" placeholder="Max Qty Per Order" />
      </div>
      <div class="  mb-2 w-full">
        <BaseInput name="qty" label="Quantity" class="w-full" v-model="form.qty" placeholder="Quantity" />
        <p v-if="form.errors.qty" class="mt-1 text-sm text-red-600"> {{ form.errors.qty }} </p>
        <!-- <BaseSelect name="no_of_seats_early_birds" label="# of Seats" v-model="form.no_of_early_bird_gen_seats"
          :options="seatsOptions" placeholder="# of Seats" /> -->
      </div>
      <div class="flex items-center gap-4 mb-2 w-full">
        <div class="flex items-center gap-2">
          <input type="checkbox" id="is_free" v-model="form.is_free" class="h-4 w-4" />
          <Label for="is_free">Is&nbsp;Free</Label>
        </div>
        <BaseInput v-if="!form.is_free" class="w-full mt-2" v-model="form.price" type="number" id="price"
          placeholder="Price" />
        <!-- <BaseInput v-model="form.price" type="number" id="price" placeholder="Price" />
        <div class="bg-amber-200 h-fit" v-if="!form.is_free">
          <InputError :message="form.errors.price" />
        </div> -->
      </div>
      <!-- <div class="flex justify-between gap-4 mb-2 w-full">
        <BaseInput name="early_bird_general_price" label="Early Bird General (Price)" class="w-full"
          v-model="form.early_bird_general_price" placeholder="Early Bird General (Price)" />
        <BaseSelect name="no_of_seats_early_birds" label="# of Seats" v-model="form.no_of_early_bird_gen_seats"
          :options="seatsOptions" placeholder="# of Seats" />
      </div> -->
      <!-- <div class="flex justify-between gap-4 mb-2 w-full">
        <BaseInput name="early_bird_vip_price" label="Early Bird VIP (Price)" class="w-full"
          v-model="form.early_bird_vip_price" placeholder="Early Bird VIP (Price)" />
        <BaseSelect name="no_of_early_bird_vip_seats" label="# of Seats" v-model="form.no_of_early_bird_vip_seats"
          :options="seatsOptions" placeholder="# of Seats" />
      </div> -->
      <!-- <div class="flex justify-between gap-4 mb-2 w-full">
        <BaseInput name="general_price" label="General (Price)" class="w-full" v-model="form.general_price"
          placeholder="General (Price)" />
        <BaseSelect name="general_price" label="# of Seats" v-model="form.no_of_gen_seats" :options="seatsOptions"
          placeholder="# of Seats" />
      </div> -->
      <!-- <div class="flex justify-between gap-4 mb-2 w-full">
        <BaseInput name="vip_price" label="VIP (Price)" class="w-full" v-model="form.vip_price"
          placeholder="VIP (Price)" />
        <BaseSelect name="vip_price" label="# of Seats" v-model="form.no_of_vip_seats" :options="seatsOptions"
          placeholder="# of Seats" />
      </div> -->
      <div class="flex flex-wrap lg:flex-nowrap gap-4 mb-4">
        <div class="w-full">
          <DatePicker v-model="form.sales_start" placeholder="Sale Start Date / Time"
            :input-class="'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'"
            :format="'yyyy-MM-dd hh:mm'" />
          <p v-if="form.errors.sales_start" class="mt-1 text-sm text-red-600"> {{ form.errors.sales_start }} </p>
        </div>
        <div class="w-full">
          <DatePicker v-model="form.sales_end" placeholder="Sale End Date / Time" :format="'yyyy-MM-dd hh:mm'" />
          <p v-if="form.errors.sales_end" class="mt-1 text-sm text-red-600"> {{ form.errors.sales_end }} </p>
        </div>
      </div>
      <!-- Status -->
      <div class="flex flex-col gap-2 border-b-1 w-fit text-green-400">
        <select v-model="form.status" id="status" class="border-none dark:text-gray-400  rounded-md w-fit  px-2 py-2">
          <option value="">Select Status</option>
          <option value=1>Active</option>
          <option value=0>In-Active</option>
        </select>
        <InputError :message="form.errors.status" />
      </div>
      <div class="flex justify-between gap-4 mt-4 w-full">
        <BaseTextarea name="description" label="Description" class="w-full" v-model="form.description"
          placeholder="Description"></BaseTextarea>
      </div>
      <div class="flex justify-center mt-6">
        <LoadingButton type="submit" class="w-full mt-10 text-white" :loading="form.processing"> {{ ticket?.id ?
          'Update' : 'Save' }} </LoadingButton>
      </div>
    </form>
  </main>
</template>
<script setup lang="ts">
  import { Head, useForm, usePage } from "@inertiajs/vue3";
  import LoadingButton from "@/components/admin/LoadingButton.vue";

  import { computed, onMounted, reactive, ref } from "vue";
  import RadioGroup from "@/components/admin/RadioGroup.vue";
  import DatePicker from '@/components/admin/DatePicker.vue';

  import BaseInput from "@/components/admin/BaseInput.vue";

  import BaseSelect from "@/components/admin/BaseSelect.vue";
  import BaseTextarea from "@/components/admin/BaseTextarea.vue";
  import Label from "@/components/admin/ui/label/Label.vue";


  const { ticket, events } = defineProps<{
    ticket: any
    events: any
  }>();
  console.log("Ticket Data:", ticket);
  const form = useForm({
    link_up_event_id: ticket?.link_up_event_id ?? null,
    name: ticket?.name ?? "",
    qty: ticket?.qty ?? "",
    is_free: ticket?.is_free != undefined ? String(Number(!!ticket?.is_free)) : "",
    sales_start: ticket?.sales_start ?? "",
    sales_end: ticket?.sales_end ?? "",
    price: ticket?.price ?? "",
    min_qty_per_order: ticket?.min_qty_per_order ?? "",
    max_qty_per_order: ticket?.max_qty_per_order ?? "",
    description: ticket?.description ?? "",
    status: ticket?.status != undefined ? String(Number(!!ticket?.status)) : "",
    _method: ticket?.id ? 'put' : 'post'
    // early_bird_general_price: "",
    // no_of_early_bird_gen_seats: "",
    // early_bird_vip_price: "",
    // no_of_early_bird_vip_seats: "",
    // general_price: "",
    // no_of_gen_seats: "",
    // vip_price: "",
    // no_of_vip_seats: "",
    // max_qty_per_order: "",
    // min_qty_per_order: "",
    // sales_start: "",
    // sales_end: "",
    // description: "",
  });

  const eventOptions = computed(() => {
    // const events = usePage().props.events as Array<{ id: number; title: string }>;
    // console.log(events);
    return events?.map((item: any) => ({
      value: item?.id,
      label: item.title,
    }));
  });

  const seatsOptions = computed(() => {
    const nums = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    return nums.map((item) => ({
      value: item,
      label: item,
    }));
  });

  // Handle form submission
  const handleSubmit = () => {
    if (ticket?.id) {
      form.post(route("frontend.ticket.update", { ticket: ticket.id }), {
        onSuccess: () => {
          form.reset();
        },
        onError: (errors) => {
          console.error("Form submission errors:", errors);
        },
      });
    } else {
      form.post(route("frontend.ticket.store"), {
        onSuccess: () => {
          form.reset();
        },
        onError: (errors) => {
          console.error("Form submission errors:", errors);
        },
      });
    }

  };
</script>
<style scoped></style>
