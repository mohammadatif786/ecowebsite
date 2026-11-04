<template>
  <Modal ref="modalRef" maxWidth="max-w-[460px]">
    <div v-if="sale" class="w-full">
      <div class="p-5 relative">
        <div class="flex items-start justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-blue-50 text-[11px] font-black text-blue-600">T</span>
              <h2 class="text-xl font-black text-slate-950">E-Tickets</h2>
            </div>
            <p class="text-xs text-slate-400 font-bold">{{ quantity }} ticket{{ quantity !== 1 ? 's' : '' }} in this purchase</p>
          </div>
          <button @click="close" class="p-1 text-slate-500 hover:text-slate-900">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <h3 class="text-xl font-black text-center mt-4 mb-5 text-slate-950">{{ eventTitle }}</h3>

        <div class="grid grid-cols-2 gap-x-6 gap-y-4 text-[12px]">
          <InfoCell label="Start Date & Time" :value="fmtFullDate(event?.start_time || sale.created_at)" />
          <InfoCell label="Event Name" :value="eventTitle" />
          <InfoCell label="End Date & Time" :value="fmtFullDate(event?.end_time || event?.start_time || sale.created_at)" />
          <InfoCell label="Location" :value="locationText" />
        </div>

        <Divider dashed />

        <div class="space-y-1.5 text-sm">
          <Row label="Full Name" :value="user?.name || 'Guest'" />
          <Row label="Phone" :value="user?.phone_number || 'N/A'" />
          <Row label="Email" :value="user?.email || 'N/A'" value-class="break-all" />
          <Row label="Total Tickets in Purchase" :value="quantity" />
        </div>

        <Divider />

        <section>
          <SectionTitle title="Tickets in This Purchase" />
          <div class="bg-slate-50 rounded-xl p-3">
            <div class="flex justify-between items-start gap-3 mb-1">
              <div class="min-w-0">
                <div class="flex items-center gap-2">
                  <p class="font-black text-sm text-slate-950 truncate">{{ ticketLabel }}</p>
                  <span v-if="isCheckedIn" class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-600 text-[9px] font-black">Scanned</span>
                </div>
                <p class="text-[10px] text-slate-400 font-bold mt-1">{{ quantity }} x {{ ticketLabel }}</p>
              </div>
              <p class="text-emerald-600 font-black text-sm">{{ money(ticketSubtotal) }}</p>
            </div>

            <div class="flex justify-between items-center pt-3 mt-3 border-t border-slate-200">
              <span class="text-[11px] font-bold text-slate-500">Subtotal (Ticket)</span>
              <b class="text-slate-950 text-xs">{{ money(ticketSubtotal) }}</b>
            </div>

            <div class="flex gap-2 mt-3">
              <button @click="openQR" class="btn bg-white border border-slate-200 px-3 py-1.5 text-[11px] font-black flex items-center gap-1.5">
                <i data-lucide="qr-code" class="w-3.5 h-3.5"></i> QR
              </button>
              <button v-if="!isCancelled" @click="cancel" class="btn bg-white border border-rose-100 px-3 py-1.5 text-[11px] font-black text-rose-500">
                Cancel
              </button>
            </div>
          </div>
        </section>

        <DetailSection v-if="hasPackage" title="Package Details">
          <DetailLine v-for="(item, index) in packageLines" :key="`pkg-${index}`" :label="item.label" :value="item.value" />
        </DetailSection>

        <DetailSection v-if="tableAddons.length" title="Table Reservations">
          <DetailLine v-for="(table, index) in tableAddons" :key="`table-${index}`" :label="tableLabel(table)" :value="money(Number(table.total_price || table.unit_price || 0))" />
        </DetailSection>

        <DetailSection v-if="drinkAddons.length" title="Drink Addons">
          <DetailLine v-for="(drink, index) in drinkAddons" :key="`drink-${index}`" :label="`${drink.name || 'Drink'} x ${Number(drink.quantity || 1)}`" :value="money(Number(drink.total_price || 0))" />
        </DetailSection>

        <DetailSection v-if="hasCookoutData" title="Cookout Details">
          <DetailLine v-if="sale.cookout_included_protein" :label="`${sale.cookout_included_protein} (Included Plate) x 1`" :value="money(0)" />
          <DetailLine v-if="cookoutSides.length" label="Included Sides" :value="cookoutSides.join(', ')" />
          <DetailLine v-for="(addon, index) in cookoutAddons" :key="`cookout-${index}`" :label="`${addon.name || 'Cookout add-on'} (${prettyCookoutCategory(addon.category)}) x ${Number(addon.quantity || 1)}`" :value="money(Number(addon.total_price || 0))" />
        </DetailSection>

        <DetailSection v-if="wellnessAddons.length" title="Wellness Services">
          <DetailLine v-for="(addon, index) in wellnessAddons" :key="`wellness-${index}`" :label="`${formatWellnessAddonName(addon.name)} (${prettyWellnessCategory(addon.category)}) x ${Number(addon.quantity || 1)}`" :value="money(Number(addon.total_price || 0))" />
        </DetailSection>

        <Divider />

        <div class="space-y-2 text-sm">
          <Row v-if="servicesSubtotal > 0" label="Services Subtotal" :value="money(servicesSubtotal)" />
          <Row v-if="drinksSubtotal > 0" label="Drinks Subtotal" :value="money(drinksSubtotal)" />
          <Row label="Subtotal" :value="orderSubtotal > 0 ? money(orderSubtotal) : 'Free'" />
          <Row v-if="discountTotal > 0" label="Discount" :value="`-${money(discountTotal)}`" value-class="text-emerald-600" />
          <Divider compact />
          <Row v-if="serviceAndProcessingFee > 0" label="Service & Processing Fee" :value="money(serviceAndProcessingFee)" />
          <Row v-if="tableProcessingFee > 0" label="Table Processing Fees" :value="money(tableProcessingFee)" />
          <Row v-if="mobileFee > 0" label="Mobile Fee" :value="money(mobileFee)" />
          <Row v-if="drinkProcessingFee > 0" label="Drink Processing Fees" :value="money(drinkProcessingFee)" />
          <Row v-if="bottleProcessingFee > 0" label="Bottle Processing Fees" :value="money(bottleProcessingFee)" />
          <Row v-if="taxTotal > 0" label="Tax" :value="money(taxTotal)" />
        </div>

        <Divider />

        <div class="flex justify-between items-start gap-3">
          <p class="text-lg font-black text-slate-950">Total Paid for All Tickets</p>
          <p class="text-xl font-black text-slate-950">{{ totalPaid > 0 ? money(totalPaid) : 'Free' }}</p>
        </div>
        <div class="flex justify-between items-center mt-1">
          <span class="text-sm text-slate-500">Status</span>
          <b :class="isCancelled ? 'text-rose-500' : 'text-emerald-500'" class="font-black">{{ statusLabel }}</b>
        </div>

        <div class="bg-blue-50 rounded-xl p-3 text-[11px] font-bold text-blue-700 leading-relaxed mt-4">
          When you download/print, each ticket will be displayed separately with its own QR code.
        </div>

        <button @click="download" class="btn w-full py-4 mt-3 bg-violet-600 hover:bg-violet-700 text-white rounded-xl font-black text-sm">
          Download E-Tickets
        </button>
      </div>
    </div>
  </Modal>

  <div v-if="sale" ref="printArea" class="hidden">
    <div class="page-break">
      <div
        class="w-full max-w-2xl mx-auto rounded-2xl bg-white p-8 shadow-xl border border-gray-200 relative overflow-hidden"
        :class="{ 'opacity-80 grayscale': isCheckedIn }"
      >
        <div class="grid grid-cols-2 gap-6 py-6">
          <div class="flex justify-start items-center">
            <img :src="`${appBaseUrl}/storage/events/logo_for_e_tickets.png`" alt="E-Tickets Logo" class="w-40 h-auto object-contain rounded-lg" />
          </div>
          <div class="flex flex-col justify-center items-center text-center" style="margin-left: -100%; margin-top: 17%;">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-wide">E-Ticket</h1>
            <p class="text-sm text-gray-500 mt-1">Please present this ticket at the event</p>
            <p class="text-xs text-gray-400 mt-1">Ticket 1 of 1</p>
          </div>
        </div>

        <div v-if="isCheckedIn" class="absolute inset-0 pointer-events-none" style="z-index:9999;">
          <div
            class="absolute"
            style="top:35%;left:10%;opacity:.1;transform:rotate(-30deg);font-size:6rem;font-weight:800;color:red;"
          >
            {{ statusLabel }}
          </div>
        </div>

        <div class="mb-6 text-center">
          <h2 class="text-2xl font-bold text-gray-800">{{ ticketLabel }}</h2>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6 text-sm">
          <div>
            <p class="text-gray-500 font-medium">Start Date &amp; Time</p>
            <p class="text-gray-800 font-semibold">{{ fmtFullDate(event?.start_time || sale.created_at) }}</p>
            <p class="text-gray-500 font-medium mt-3">End Date &amp; Time</p>
            <p class="text-gray-800 font-semibold">{{ fmtFullDate(event?.end_time || event?.start_time || sale.created_at) }}</p>
          </div>
          <div>
            <p class="text-gray-500 font-medium">Event Name</p>
            <p class="text-gray-800 font-semibold">{{ eventTitle }}</p>
            <p class="text-gray-500 font-medium mt-3">Location</p>
            <p class="text-gray-800 font-semibold">{{ locationText }}</p>
          </div>
        </div>

        <hr class="my-6 border-dashed border-gray-300" />

        <div class="mb-6 space-y-3 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-500 font-medium">Full Name</span>
            <span class="text-gray-800 font-semibold">{{ user?.name || 'Guest' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500 font-medium">Phone</span>
            <span class="text-gray-800 font-semibold">{{ user?.phone_number || 'N/A' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500 font-medium">Email</span>
            <span class="text-gray-800 font-semibold">{{ user?.email || 'N/A' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500 font-medium">Seats</span>
            <span class="text-gray-800 font-semibold">{{ quantity }} x {{ ticketLabel }}</span>
          </div>
        </div>

        <hr class="my-6 border-dashed border-gray-300" />

        <div v-if="hasPackage" class="mt-3 space-y-2">
          <h2 class="text-sm font-semibold text-gray-700">{{ packageData.name || 'Package Details' }}</h2>
          <div v-for="(item, index) in packageLines" :key="`print-pkg-${index}`">
            <p class="text-xs font-medium text-gray-600">{{ item.label }}:</p>
            <p class="ml-3 text-xs text-gray-500">{{ item.value }}</p>
          </div>
        </div>

        <hr v-if="hasPackage" class="my-6 border-dashed border-gray-300" />

        <div v-if="sale?.cookout_included_protein" class="mb-6">
          <p class="text-sm font-semibold text-gray-700 mb-2">Cookout Included Plate</p>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500 font-medium">{{ sale.cookout_included_protein }} (Included Plate) x 1</span>
            <span class="text-gray-800 font-semibold">{{ money(0) }}</span>
          </div>
        </div>

        <div v-if="cookoutAddons.length" class="mb-6">
          <p class="text-sm font-semibold text-gray-700 mb-2">Cookout Add-ons</p>
          <div v-for="(addon, index) in cookoutAddons" :key="`print-cookout-${index}`" class="flex justify-between text-sm">
            <span class="text-gray-500 font-medium">{{ addon?.name }} ({{ prettyCookoutCategory(addon?.category) }}) x {{ addon?.quantity || 1 }}</span>
            <span class="text-gray-800 font-semibold">{{ money(addon?.total_price || 0) }}</span>
          </div>
        </div>

        <div v-if="cookoutSides.length" class="mb-6">
          <p class="text-sm font-semibold text-gray-700 mb-2">Included Sides (Grab &amp; Go)</p>
          <div class="text-sm text-gray-600">{{ cookoutSides.join(', ') }}</div>
        </div>

        <div v-if="wellnessAddons.length" class="mb-6">
          <p class="text-sm font-semibold text-gray-700 mb-2">Wellness Services</p>
          <div v-for="(addon, index) in wellnessAddons" :key="`print-wellness-${index}`" class="flex justify-between text-sm">
            <span class="text-gray-500 font-medium">{{ formatWellnessAddonName(addon?.name) }} ({{ prettyWellnessCategory(addon?.category) }}) x {{ addon?.quantity || 1 }}</span>
            <span class="text-gray-800 font-semibold">{{ money(addon?.total_price || 0) }}</span>
          </div>
        </div>

        <hr v-if="hasCookoutData || wellnessAddons.length" class="my-6 border-dashed border-gray-300" />

        <div v-if="tableAddons.length || drinkAddons.length" class="mb-6">
          <p class="text-sm font-semibold text-gray-700 mb-2">Table Reservations</p>
          <div class="space-y-2 text-sm">
            <div v-for="(table, index) in tableAddons" :key="`print-table-${index}`">
              <div class="flex justify-between">
                <span class="text-gray-500 font-medium">{{ tableLabel(table) }}</span>
                <span class="text-gray-800 font-semibold">{{ money(table.total_price || table.unit_price || 0) }}</span>
              </div>
              <div v-for="(drink, drinkIndex) in sectionDrinks(table.section)" :key="`print-table-drink-${index}-${drinkIndex}`" class="ml-4 flex justify-between">
                <span class="text-gray-500 font-medium">{{ drink.name }} x {{ drink.quantity || 1 }}</span>
                <span class="text-gray-800 font-semibold">{{ money(drink.total_price || 0) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="unassignedDrinks.length" class="mb-6">
          <p class="text-sm font-semibold text-gray-700 mb-2">Drink Addons</p>
          <div v-for="(drink, index) in unassignedDrinks" :key="`print-drink-${index}`" class="flex justify-between text-sm">
            <span class="text-gray-500 font-medium">{{ drink.name }} x {{ drink.quantity || 1 }}</span>
            <span class="text-gray-800 font-semibold">{{ money(drink.total_price || 0) }}</span>
          </div>
        </div>

        <hr v-if="tableAddons.length || drinkAddons.length" class="my-6 border-dashed border-gray-300" />

        <div class="mb-6 space-y-3 text-sm">
          <div v-if="servicesSubtotal > 0" class="flex justify-between">
            <span class="text-gray-500 font-medium">Services Subtotal</span>
            <span class="text-gray-800 font-semibold">{{ money(servicesSubtotal) }}</span>
          </div>
          <div v-if="drinksBaseSubtotal > 0" class="flex justify-between">
            <span class="text-gray-500 font-medium">Drinks Subtotal</span>
            <span class="text-gray-800 font-semibold">{{ money(drinksBaseSubtotal) }}</span>
          </div>
          <div v-if="bottlesBaseSubtotal > 0" class="flex justify-between">
            <span class="text-gray-500 font-medium">Bottles Subtotal</span>
            <span class="text-gray-800 font-semibold">{{ money(bottlesBaseSubtotal) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500 font-medium">Subtotal</span>
            <span class="text-gray-800 font-semibold">{{ orderSubtotal > 0 ? money(orderSubtotal) : 'Free' }}</span>
          </div>
          <div v-if="discountTotal > 0" class="flex justify-between text-green-600">
            <span class="font-medium">Discount</span>
            <span class="font-semibold">-{{ money(discountTotal) }}</span>
          </div>
          <hr class="border-gray-300" />
          <div v-if="serviceAndProcessingFee > 0" class="flex justify-between">
            <span class="text-gray-500 font-medium">Service &amp; Processing Fee</span>
            <span class="text-gray-800 font-semibold">{{ money(serviceAndProcessingFee) }}</span>
          </div>
          <div v-if="tableProcessingFee > 0" class="flex justify-between">
            <span class="text-gray-500 font-medium">Table Processing Fees</span>
            <span class="text-gray-800 font-semibold">{{ money(tableProcessingFee) }}</span>
          </div>
          <div v-if="mobileFee > 0" class="flex justify-between">
            <span class="text-gray-500 font-medium">Mobile Fee</span>
            <span class="text-gray-800 font-semibold">{{ money(mobileFee) }}</span>
          </div>
          <div v-if="drinkProcessingFee > 0" class="flex justify-between">
            <span class="text-gray-500 font-medium">Drink Processing Fees</span>
            <span class="text-gray-800 font-semibold">{{ money(drinkProcessingFee) }}</span>
          </div>
          <div v-if="bottleProcessingFee > 0" class="flex justify-between">
            <span class="text-gray-500 font-medium">Bottle Processing Fees</span>
            <span class="text-gray-800 font-semibold">{{ money(bottleProcessingFee) }}</span>
          </div>
          <div v-if="taxTotal > 0" class="flex justify-between">
            <span class="text-gray-500 font-medium">Tax</span>
            <span class="text-gray-800 font-semibold">{{ money(taxTotal) }}</span>
          </div>
          <hr class="border-gray-300" />
          <div class="flex justify-between">
            <span class="text-gray-700 font-semibold">Total Paid</span>
            <span class="text-lg font-bold text-gray-900">{{ totalPaid > 0 ? money(totalPaid) : 'Free' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500 font-medium">Status</span>
            <span class="font-semibold" :class="isCancelled ? 'text-red-600' : 'text-green-600'">{{ statusLabel }}</span>
          </div>
        </div>

        <hr class="my-6 border-dashed border-gray-300" />

        <div class="flex justify-center w-full">
          <div v-if="!isCancelled && buildScanUrl" class="text-center mt-4 relative inline-block">
            <QrcodeVue :value="buildScanUrl" :size="160" level="H" render-as="svg" class="mx-auto" />
            <img
              :src="`${appBaseUrl}/storage/events/logo_for_e_tickets.png`"
              alt="Logo"
              class="absolute inset-0 m-auto"
              style="width:65px;height:65px;border-radius:8px;padding:4px;"
            />
          </div>
          <div v-else class="w-40 h-40 border-4 border-red-500 rounded-lg flex items-center justify-center bg-red-50">
            <div class="text-center">
              <div class="text-red-600 text-5xl mb-2">X</div>
              <div class="text-red-600 font-bold text-sm">CANCELLED</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, defineComponent, h } from 'vue';
import QrcodeVue from 'qrcode.vue';
import Modal from '../ui/Modal.vue';

const emit = defineEmits(['qr', 'cancel']);

const modalRef = ref(null);
const printArea = ref(null);
const sale = ref(null);

const event = computed(() => sale.value?.event || {});
const user = computed(() => sale.value?.user || {});
const currencySymbol = computed(() => event.value?.currency_symbol || '$');
const quantity = computed(() => Number(sale.value?.no_of_tickets || sale.value?.quantity || 1));
const ticketLabel = computed(() => sale.value?.ticket_name || sale.value?.ticket_type || 'Ticket');
const eventTitle = computed(() => event.value?.title || sale.value?.event_name || ticketLabel.value);
const ticketSubtotal = computed(() => Number(sale.value?.sub_total || 0));
const totalPaid = computed(() => Number(sale.value?.total ?? sale.value?.paid ?? sale.value?.stripe_price ?? ticketSubtotal.value));
const statusLabel = computed(() => sale.value?.ticket_status || sale.value?.status || 'confirmed');
const isCancelled = computed(() => ['cancelled', 'canceled'].includes(String(statusLabel.value).toLowerCase()));
const isCheckedIn = computed(() => Boolean(sale.value?.checkin || sale.value?.checkins?.length));
const locationText = computed(() => {
  const parts = [event.value?.venue, event.value?.city || event.value?.country].filter(Boolean);
  return parts.length ? parts.join(', ') : 'TBA';
});

const asArray = (value) => {
  if (Array.isArray(value)) return value;
  if (typeof value === 'string') {
    try {
      const parsed = JSON.parse(value);
      return Array.isArray(parsed) ? parsed : [];
    } catch (_) {
      return [];
    }
  }
  return [];
};

const asObject = (value) => {
  if (value && typeof value === 'object' && !Array.isArray(value)) return value;
  if (typeof value === 'string') {
    try {
      const parsed = JSON.parse(value);
      return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
    } catch (_) {
      return {};
    }
  }
  return {};
};

const feeBreakdown = computed(() => asObject(sale.value?.fee_breakdown));
const drinkAddons = computed(() => asArray(sale.value?.drink_addons));
const tableAddons = computed(() => asArray(sale.value?.table_addons));
const wellnessAddons = computed(() => asArray(sale.value?.wellness_addons));
const cookoutAddons = computed(() => asArray(sale.value?.cookout_addons));
const cookoutSides = computed(() => asArray(sale.value?.cookout_included_sides));
const packageData = computed(() => asObject(sale.value?.package_data));
const hasPackage = computed(() => Object.keys(packageData.value).length > 0);
const hasCookoutData = computed(() => Boolean(sale.value?.cookout_included_protein) || cookoutSides.value.length > 0 || cookoutAddons.value.length > 0);

const sum = (items) => items.reduce((total, item) => total + Number(item?.total_price || 0), 0);
const drinksSubtotal = computed(() => sum(drinkAddons.value));
const tablesSubtotal = computed(() => sum(tableAddons.value) || Number(sale.value?.tables_total || 0));
const cookoutSubtotal = computed(() => Number(sale.value?.cookout_total || 0));
const wellnessSubtotal = computed(() => Number(sale.value?.wellness_total || 0));
const servicesSubtotal = computed(() => ticketSubtotal.value + cookoutSubtotal.value + wellnessSubtotal.value);
const orderSubtotal = computed(() => servicesSubtotal.value + drinksSubtotal.value);
const discountTotal = computed(() => Number(sale.value?.coupan_amount || sale.value?.discount || 0));
const serviceAndProcessingFee = computed(() => Number(sale.value?.fee || 0));
const tableProcessingFee = computed(() => Number(feeBreakdown.value.vip_package_fee_amount || 0));
const mobileFee = computed(() => Number(feeBreakdown.value.mobile_fee_amount || 0));
const drinkProcessingFee = computed(() => Number(feeBreakdown.value.drink_fee_amount || 0));
const bottleProcessingFee = computed(() => Number(feeBreakdown.value.bottle_fee_amount || 0));
const taxTotal = computed(() => Number(sale.value?.event_tax || feeBreakdown.value.tax_total || 0));
const appBaseUrl = computed(() => window.location.origin);
const buildScanUrl = computed(() => {
  const rawCode = sale.value?.ticket_qrcode_id || sale.value?.ticket_qrcode || sale.value?.qr || '';
  return rawCode ? `${appBaseUrl.value}/organizer/scanner/scan?code=${encodeURIComponent(String(rawCode))}` : '';
});
const drinksBaseSubtotal = computed(() => {
  return drinkAddons.value
    .filter((drink) => (drink?.category || '') !== 'bottles')
    .reduce((total, drink) => total + Number(drink?.total_price || 0), 0);
});
const bottlesBaseSubtotal = computed(() => {
  return drinkAddons.value
    .filter((drink) => (drink?.category || '') === 'bottles')
    .reduce((total, drink) => total + Number(drink?.total_price || 0), 0);
});
const unassignedDrinks = computed(() => drinkAddons.value.filter((drink) => !drink?.section || drink.section === '-'));

const packageLines = computed(() => {
  const data = packageData.value;
  const lines = [];
  const pushList = (label, values) => {
    const list = asArray(values).map(item => item?.name || item).filter(Boolean);
    if (list.length) lines.push({ label, value: list.join(', ') });
  };

  pushList('Bottles', data.main_bottles);
  pushList('Chasers', data.chasers_or_mixers);
  pushList('Waters', data.water_options);
  pushList('Table Seating', data.table_seating);
  if (data.notes) lines.push({ label: 'Notes', value: data.notes });
  return lines;
});

const money = (n) => currencySymbol.value + Number(n || 0).toFixed(2);

const ordinal = (day) => {
  const modTen = day % 10;
  const modHundred = day % 100;
  if (modTen === 1 && modHundred !== 11) return `${day}st`;
  if (modTen === 2 && modHundred !== 12) return `${day}nd`;
  if (modTen === 3 && modHundred !== 13) return `${day}rd`;
  return `${day}th`;
};

const fmtFullDate = (value) => {
  if (!value) return 'TBA';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return 'TBA';
  const weekday = date.toLocaleDateString('en-US', { weekday: 'long' });
  const month = date.toLocaleDateString('en-US', { month: 'short' });
  return `${weekday}, ${ordinal(date.getDate())} ${month} ${date.getFullYear()}`;
};

const tableLabel = (table) => {
  const capacity = Number(table?.capacity || 0);
  const section = table?.section && table.section !== '-' ? ` - ${table.section}` : '';
  const qty = Number(table?.quantity || 1);
  return `${capacity > 0 ? `Table for ${capacity}` : 'Table'}${section} x ${qty}`;
};

const sectionDrinks = (section) => {
  const key = section || '-';
  return drinkAddons.value.filter((drink) => (drink?.section || '-') === key);
};

const prettyCookoutCategory = (cat = '') => ({
  cookout_protein: 'Cookout Protein',
  cookout_extra: 'Cookout Extra',
  cookout_drink: 'Cookout Drink',
  cookout_manual: 'Cookout Extra',
  cookout_included_plate: 'Included Plate',
}[cat] || cat || 'Cookout');

const prettyWellnessCategory = (cat = '') => ({
  wellness_service: 'Wellness Service',
  wellness_manual: 'Wellness Add-on',
  wellness_included_service: 'Included Service',
  wellness_slot: 'Wellness Slot',
  wellness_mode: 'Service Mode',
  wellness_contact_phone: 'Contact Phone',
}[cat] || cat || 'Wellness');

const formatWellnessAddonName = (name = '') => {
  return String(name).replace(/\((\d{4}-\d{2}-\d{2}T[^\)]+)\)/, (_, rawDate) => {
    const date = new Date(rawDate);
    return Number.isNaN(date.getTime()) ? `(${rawDate})` : `(${date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })})`;
  });
};

const open = (ticketSale) => {
  sale.value = ticketSale;
  if (modalRef.value) {
    modalRef.value.open();
    nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
  }
};

const close = () => {
  if (modalRef.value) modalRef.value.close();
};

const openQR = () => {
  emit('qr', { id: sale.value?.ticket_qrcode || sale.value?.qr || sale.value?.id, event: eventTitle.value });
};

const cancel = () => {
  emit('cancel', sale.value);
};

const download = async () => {
  await nextTick();
  const printContents = printArea.value?.innerHTML;
  if (!printContents) {
    window.print();
    return;
  }

  const printWindow = window.open('', '', 'height=700,width=900');
  if (!printWindow) {
    window.print();
    return;
  }

  printWindow.document.write(`
    <html>
      <head>
        <title>Print E-Tickets</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <style>
          body { background: #fff; padding: 2rem; }
          @media print {
            .page-break {
              page-break-after: always;
              page-break-inside: avoid;
            }
          }
        </style>
      </head>
      <body>
        ${printContents}
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => printWindow.print(), 250);
};

const InfoCell = defineComponent({
  props: { label: String, value: [String, Number] },
  setup(props) {
    return () => h('div', [
      h('p', { class: 'text-[10px] font-black text-slate-400' }, props.label),
      h('p', { class: 'font-black text-slate-950 leading-tight' }, props.value || 'N/A')
    ]);
  }
});

const Row = defineComponent({
  props: { label: String, value: [String, Number], valueClass: String },
  setup(props) {
    return () => h('div', { class: 'flex justify-between gap-3' }, [
      h('span', { class: 'text-slate-500' }, props.label),
      h('b', { class: ['text-right text-slate-950', props.valueClass] }, props.value)
    ]);
  }
});

const Divider = defineComponent({
  props: { dashed: Boolean, compact: Boolean },
  setup(props) {
    return () => h('div', { class: [props.compact ? 'my-2' : 'my-4', 'border-t', props.dashed ? 'border-dashed' : '', 'border-slate-200'] });
  }
});

const SectionTitle = defineComponent({
  props: { title: String },
  setup(props) {
    return () => h('p', { class: 'text-sm font-black text-slate-900 mb-3' }, props.title);
  }
});

const DetailLine = defineComponent({
  props: { label: String, value: [String, Number] },
  setup(props) {
    return () => h('div', { class: 'flex justify-between gap-3 text-xs py-1' }, [
      h('span', { class: 'text-slate-500 font-semibold min-w-0' }, props.label),
      h('span', { class: 'text-right text-slate-900 font-bold max-w-[48%] break-words' }, props.value)
    ]);
  }
});

const DetailSection = defineComponent({
  props: { title: String },
  setup(props, { slots }) {
    return () => h('section', { class: 'mt-4 rounded-xl border border-slate-100 bg-slate-50/70 p-3' }, [
      h('p', { class: 'text-sm font-black text-slate-900 mb-2' }, props.title),
      ...(slots.default?.() || [])
    ]);
  }
});

defineExpose({ open, close });
</script>
