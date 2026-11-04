<script setup lang="ts">
import { LiveMessagesTable, UserTable } from "@/client";
import Datatable from "@/components/admin/Datatable.vue";
import { User } from "@/types";
import { router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import moment from "moment";
import { onMounted, ref } from "vue";
import Button from "@/components/admin/ui/button/Button.vue";

const props = defineProps<{
  user: User;
  filters?: Record<string, any>;
}>();

const messages = ref<LiveMessagesTable>(null);

onMounted(() => {
  axios.get(route("frontend.user.messages.profile", props.user.id)).then((response) => {
    // console.log(response);
    messages.value = response.data.messages;
  });
});

const searchForm = useForm({
  search: props.filters?.search || "",
});

const handlePageChange = (page: number) => {
    axios
        .get(route("frontend.user.messages.profile", props.user.id), {
            params: {
                page: page,
            },
        })
        .then((response) => {
            // console.log(response);
            messages.value = response.data.messages;
        });
};
// const handlePageChange = (page: number) => {
//   router.get(
//     route("admin.users-messages"),
//     { search: searchForm.search, page },
//     { preserveScroll: true, replace: true }
//   );
// };

// Define columns
const columns = [
  {
    header: "ID",
    cell: (row: any) => row.id,
  },
  {
    header: "Content",
    cell: (row: any) => row.content,
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
    <Datatable v-if="messages" :columns="columns" :rows="messages.data" :selectable="false" :add-border="false" />
    <div class="flex justify-between items-center mt-4" v-if="messages">
        <span class="text-sm text-gray-500">
            Showing {{ messages.from }} to {{ messages.to }} of
            {{ messages.total }} results
        </span>

        <div class="space-x-2 flex flex-row items-center">
            <Button variant="outline" size="sm" :disabled="messages.current_page === 1"
                @click="handlePageChange(messages.current_page - 1)">
                <ChevronLeft /> Prev
            </Button>
            <Button variant="outline" size="sm" :disabled="messages.current_page === messages.last_page"
                @click="handlePageChange(messages.current_page + 1)">
                Next
                <ChevronRight />
            </Button>
        </div>
    </div>
</template>
