<template>
  <AuthenticatedLayout>
    <Head title="Your Coin" />
    <main
      class="flex flex-col max-w-[1000px] mx-auto bg-stone-50 py-10 px-4 rounded-lg mt-12 mb-12"
    >
      <!-- Page Title -->
      <div class="flex items-center justify-start mb-6">
        <h1 class="text-3xl font-bold text-black">Your Coin</h1>
        <img :src="diamond" alt="Diamond" class="ml-3 w-6 h-6" />
      </div>
      <!-- Coin Boxes -->
      <div class="flex flex-wrap gap-6 mb-8">
        <div
          class="flex items-center gap-2 bg-white shadow p-4 rounded-xl w-60 border border-gray-200"
        >
          <img :src="diamond" alt="Diamond" class="w-6 h-6" />
          <div>
            <div class="text-black text-xl font-bold">5000</div>
            <div class="text-black text-sm">$500</div>
          </div>
        </div>

        <div
          class="flex items-center gap-2 bg-white shadow p-4 rounded-xl w-60 border border-gray-200"
        >
          <img :src="diamond" alt="Diamond" class="w-6 h-6" />
          <div>
            <div class="text-black text-xl font-bold">1000</div>
            <div class="text-black text-sm">$100</div>
          </div>
        </div>

        <div
          class="flex items-center gap-2 bg-white shadow p-4 rounded-xl w-60 border border-gray-200"
        >
          <img :src="diamond" alt="Diamond" class="w-6 h-6" />
          <div>
            <div class="text-black text-xl font-bold">10000</div>
            <div class="text-black text-sm">$1000</div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-4 mb-10">
        <Button
          class="bg-[#039be5] text-white px-6 py-2 rounded-lg"
          @click="openAddAmountPage"
          >Topup</Button
        >
        <FilterDialog
          DialogTitleString="Withdraw"
          simple-class="bg-primary-front text-black px-6 py-2 rounded-lg"
        >
          <Label class="mb-2">Withdraw Method</Label>
          <Select>
            <SelectTrigger class="w-full">
              <SelectValue placeholder="Withdraw Method" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem value="all"> Paypal </SelectItem>
                <SelectItem value="upcomming"> Razor </SelectItem>
                <SelectItem value="live"> Stripe </SelectItem>
                <!-- <SelectItem value="completed"> Completed </SelectItem> -->
              </SelectGroup>
            </SelectContent>
          </Select>
          <div class="mt-6">
            <Label class="my-2">Withdraw Amount</Label>
            <Select>
              <SelectTrigger class="w-full">
                <SelectValue placeholder="Withdraw Amount" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem value="all"> $1000 </SelectItem>
                  <SelectItem value="upcomming"> $2000 </SelectItem>
                  <SelectItem value="live"> $5000 </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
        </FilterDialog>
        <!-- <Button class="bg-primary-front text-black px-6 py-2 rounded-lg">Withdraw</Button> -->
      </div>

      <!-- Coin Buying Info -->
      <div
        class="bg-white p-6 rounded-lg shadow text-sm text-gray-700 space-y-2 border border-gray-200 w-full"
      >
        <h1 class="text-xl font-bold">Coins Buying & Info</h1>
        <p>🔹 Coins can be used for sending gifts only…</p>
        <p>🔹 Coins don’t have any expiry date</p>
        <p>🔹 Coins can be used with all payments modes…</p>
        <p>🔹 Coins are credited to your Coin balance only…</p>
        <p>🔹 Coins can be withdrawn with the described method only…</p>
        <p>🔹 Coins cannot be transferred to any users…</p>
        <p>🔹 You need a minimum of 1000 coins to make a withdrawal…</p>
      </div>
    </main>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { Button } from "@/components/admin/ui/button";
import diamond from "../../../../assets/images/diamond.svg";
import FilterDialog from "@/components/front/FilterDialog.vue";
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/front/ui/select";
const openAddAmountPage = () => {
  router.get(route("frontend.user.wallet.add"));
};
</script>

<style scoped></style>
