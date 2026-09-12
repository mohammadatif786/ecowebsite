<template>
  <AuthenticatedLayout>

    <Head title="Wallet" />
    <!-- Main Content -->
    <main class="flex flex-col">
      <Hero />
      <section class="max-w-6xl mx-auto px-4 py-6 space-y-6">
        <Balances :currentBalance="props.currentBalance" :users="props.users" />
        <Requests :receivedRequests="props.receivedRequests" :sentRequests="props.sentRequests"/>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <Earnings />
          <VirtualCard />
          <Contacts :contacts="props.contacts" :users="props.users" />
        </div>
        <Transactions :transactions="props.transactions" />
      </section>
    </main>

  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";


import Hero from './Components/Hero.vue';
import Balances from './Components/Balances.vue';
import Transactions from './Components/Transactions.vue';
import Earnings from './Components/Earnings.vue';
import VirtualCard from './Components/VirtualCard.vue';
import Contacts from './Components/Contacts.vue';
import Requests from './Components/Requests.vue';

const props = defineProps<{
  currentBalance: number;
  transactions: any;
  users: any;
  contacts: any;
  sentRequests: any;
  receivedRequests: any;
}>();

</script>

<style scoped></style>
