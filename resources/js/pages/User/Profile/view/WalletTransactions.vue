<script setup lang="ts">
import { LiveMessagesTable, UserTable } from "@/client";
import Datatable from "@/components/admin/Datatable.vue";
import { User } from "@/types";
import axios from "axios";
import moment from "moment";
import { onMounted, ref } from "vue";
import Button from "@/components/admin/ui/button/Button.vue";
import { ChevronLeft, ChevronRight } from "lucide-vue-next";

const props = defineProps<{
  user: User;
}>();



const transactions = ref<LiveMessagesTable>(null);

onMounted(() => {
  fetchTransactions();
});

const fetchTransactions = () => {
  axios.get(route("frontend.user.wallet.profile", props.user.id)).then((response) => {
    // console.log(response);
    transactions.value = response.data.transactions;
  });
};
defineExpose({ fetchTransactions });

const handlePageChange = (page: number) => {
    axios
        .get(route("frontend.user.wallet.profile", props.user.id), {
            params: {
                page: page,
            },
        })
        .then((response) => {
            // console.log(response);
            transactions.value = response.data.transactions;
        });
};

// Define columns
const columns = [
  {
    header: "ID",
    cell: (row: any) => row.id,
  },
  {
    header: "Amount",
    classHead: "text-right",
    classCell: "text-right",
    cell: (row: any) => row.amount,
  },
  {
    header: "Created At",
    classHead: "text-right",
    classCell: "text-right",
    cell: (row: any) => {
      return row.created_at ? moment(row.created_at).format("MMM YYYY ddd") : "";
    },
  },
];
</script>
<template>
    <Datatable v-if="transactions" :columns="columns" :rows="transactions.data" :selectable="false" :add-border="false"
        :pagination="true" />
    <div class="flex justify-between items-center mt-4" v-if="transactions">
        <span class="text-sm text-gray-500">
            Showing {{ transactions.from }} to {{ transactions.to }} of
            {{ transactions.total }} results
        </span>

        <div class="space-x-2 flex flex-row items-center">
            <Button variant="outline" size="sm" :disabled="transactions.current_page === 1"
                @click="handlePageChange(transactions.current_page - 1)">
                <ChevronLeft /> Prev
            </Button>
            <Button variant="outline" size="sm" :disabled="transactions.current_page === transactions.last_page"
                @click="handlePageChange(transactions.current_page + 1)">
                Next
                <ChevronRight />
            </Button>
        </div>
    </div>
</template>
