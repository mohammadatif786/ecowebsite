<script setup lang="ts">
import { LiveMessagesTable, UserTable } from "@/client";
import Datatable from "@/components/admin/Datatable.vue";
import { router, useForm } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import { User } from "@/types";
import axios from "axios";
import moment from "moment";
import Button from "@/components/admin/ui/button/Button.vue";

const props = defineProps<{
  user: User;
}>();

const events = ref<LiveMessagesTable>(null);

onMounted(() => {
  axios.get(route("frontend.user.events.profile", props.user.id)).then((response) => {
    // console.log(response);
    events.value = response.data.events;
  });
});

const handlePageChange = (page: number) => {
    axios
        .get(route("frontend.user.events.profile", props.user.id), {
            params: {
                page: page,
            },
        })
        .then((response) => {
            events.value = response.data.events;
        });
};

// Define columns
const columns = [
  {
    header: "ID",
    cell: (row: any) => row.id,
  },
  {
    header: "Event Name",
    cell: (row: any) => row.title,
  },
  {
    header: "Type",
    classCell: "capitalize",
    cell: (row: any) => row.type,
  },
  {
    header: "Created At",
    cell: (row: any) => {
      return row.created_at ? moment(row.created_at).format("MMM YYYY ddd") : "";
    },
  },
];
</script>
<template>
    <Datatable v-if="events" :columns="columns" :rows="events.data" :selectable="false" :add-border="false" />
    <div class="flex justify-between items-center mt-4" v-if="events">
        <span class="text-sm text-gray-500">
            Showing {{ events.from }} to {{ events.to }} of
            {{ events.total }} results
        </span>

        <div class="space-x-2 flex flex-row items-center">
            <Button variant="outline" size="sm" :disabled="events.current_page === 1"
                @click="handlePageChange(events.current_page - 1)">
                <ChevronLeft /> Prev
            </Button>
            <Button variant="outline" size="sm" :disabled="events.current_page === events.last_page"
                @click="handlePageChange(events.current_page + 1)">
                Next
                <ChevronRight />
            </Button>
        </div>
    </div>
</template>
