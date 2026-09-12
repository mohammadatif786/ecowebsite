<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ReceiptText } from 'lucide-vue-next';
import AppLayout from '@/layouts/organizer/AppLayout.vue';

const money = (amount: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
</script>

<template>
  <Head title="POS Fee Summary" />

  <AppLayout>
    <section class="max-w-[1100px] mx-auto pb-12">
      <div class="rounded-3xl overflow-hidden mb-4" style="background:linear-gradient(120deg,#059669,#14b8a6)">
        <div class="p-5 flex items-center gap-3 text-white">
          <span class="h-12 w-12 rounded-2xl bg-white/20 grid place-items-center shrink-0"><ReceiptText class="w-6 h-6" /></span>
          <div>
            <p class="text-xl font-black leading-tight">POS Fee Summary</p>
            <p class="text-white/80 text-[13px] font-bold mt-0.5">Platform fees earned, outstanding, and deducted from your Point of Sale</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <article class="summary-stat"><p>Total Fees Earned</p><b>{{ money(0) }}</b></article>
        <article class="summary-stat"><p>Outstanding</p><b>{{ money(0) }}</b></article>
        <article class="summary-stat"><p>Collected</p><b class="text-emerald-600">{{ money(0) }}</b></article>
        <article class="summary-stat"><p>Rate (Your Country)</p><b>7.7% + $0.30</b></article>
      </div>

      <div class="grid md:grid-cols-2 gap-4 mb-4">
        <article class="info-card">
          <h2>Cash Sales</h2>
          <p class="description">Fee shown as a reference line and included in the total collected as cash. Platform fee owed until settled at your next payout for that event.</p>
          <dl>
            <div><dt>Sales</dt><dd>3</dd></div>
            <div><dt>Gross</dt><dd>{{ money(0) }}</dd></div>
            <div><dt>Fees outstanding</dt><dd class="text-amber-600">{{ money(0) }}</dd></div>
          </dl>
        </article>
        <article class="info-card">
          <h2>Credit Sales</h2>
          <p class="description">Fee shown as a reference line and included in the total collected. Platform + processing fee owed until settled at your next payout for that event.</p>
          <dl>
            <div><dt>Sales</dt><dd>1</dd></div>
            <div><dt>Gross</dt><dd>{{ money(0) }}</dd></div>
            <div><dt>Fees outstanding</dt><dd class="text-amber-600">{{ money(0) }}</dd></div>
          </dl>
        </article>
      </div>

      <article class="info-card mb-4 min-h-[140px]">
        <h2>Deductions at Payout</h2>
        <p class="description">Every time outstanding POS fees are auto-deducted from a payout, it’s logged here.</p>
        <p class="empty-message">No deductions yet.</p>
      </article>

      <article class="info-card min-h-[170px]">
        <h2>Payout History</h2>
        <p class="empty-message mt-8">No payouts with POS fee deductions yet.</p>
        <button class="w-full rounded-2xl border border-slate-200 py-2.5 mt-8 text-sm font-black hover:bg-slate-50 transition">View all payouts →</button>
      </article>
    </section>
  </AppLayout>
</template>

<style scoped>
.summary-stat,.info-card{background:#fff;border:1px solid #edf1f6;border-radius:22px;padding:16px;}
.summary-stat p{color:#94a3b8;font-size:11px;font-weight:900;text-transform:uppercase;}
.summary-stat b{color:#071b3a;display:block;font-size:26px;line-height:1.2;margin-top:7px;}
.info-card h2{color:#071b3a;font-size:16px;font-weight:900;}
.description{color:#8ba0c7;font-size:12px;font-weight:500;line-height:1.4;margin-top:8px;max-width:500px;}
dl{margin-top:10px;}dl div{display:flex;justify-content:space-between;font-size:14px;line-height:1.7;}dt{color:#5373aa;}dd{color:#071b3a;font-weight:800;}
.empty-message{color:#8ba0c7;font-size:14px;text-align:center;margin-top:26px;}
</style>
