<template>
  <div v-if="show && current" class="modal-backdrop">
    <div class="modal-box">
      <div class="modal-header">
        <h2>Payment Details</h2>
        <button class="close-btn" @click="$emit('close')" aria-label="Close">&times;</button>
      </div>

      <div class="content">
        <div class="pay-summary">
          <div>
            <div class="meta-label">Total Paid</div>
            <div class="pay-total">{{ currencySymbol }}{{ currentTotalPaid.toFixed(2) }}</div>
          </div>
          <div>
            <div class="pay-status">✔ Paid • {{ paymentMethod }}</div>
          </div>
        </div>

        <div class="pay-method-card">
          <div>
            <div class="pay-method-main">{{ paymentChannel }}</div>
            <div class="pay-method-sub">Processed via {{ paymentMethod }} on {{ paidAt }}</div>
          </div>
          <div style="text-align:right;font-size:12px;">
            <div class="meta-label">Payment ID</div>
            <div class="meta-value">{{ paymentId }}</div>
          </div>
        </div>

        <div class="pay-grid">
          <div>
            <div class="meta-label">Order Reference</div>
            <div class="meta-value">{{ current.ticket_qrcode_id }}</div>
          </div>
          <div>
            <div class="meta-label">Gateway Reference</div>
            <div class="meta-value">{{ gatewayRef }}</div>
          </div>
          <div>
            <div class="meta-label">Attendee</div>
            <div class="meta-value">{{ current.user?.name }}</div>
          </div>
          <div>
            <div class="meta-label">Email</div>
            <div class="meta-value">{{ current.user?.email }}</div>
          </div>
        </div>

        <div class="details-fees">
          <div class="fee-row" v-if="(serviceFee + processingFee) > 0 && !isCurrentRowFree">
            <span>Service &amp; Processing Fee</span>
            <span>{{ currencySymbol }}{{ (serviceFee + processingFee).toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="drinkFees > 0">
            <span>Drink Processing Fees</span>
            <span>{{ currencySymbol }}{{ drinkFees.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="bottleFees > 0">
            <span>Bottle Processing Fees</span>
            <span>{{ currencySymbol }}{{ bottleFees.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="vipFees > 0">
            <span>Table Processing Fees</span>
            <span>{{ currencySymbol }}{{ vipFees.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="mobileFee > 0">
            <span>Mobile Fee</span>
            <span>{{ currencySymbol }}{{ mobileFee.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="current?.event_tax > 0 && !isCurrentRowFree">
            <span>Tax</span>
            <span>{{ currencySymbol }}{{ Number(current?.event_tax || 0).toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="discount > 0">
            <span>Discount</span>
            <span>- {{ currencySymbol }}{{ discount.toFixed(2) }}</span>
          </div>
          <div class="fee-row total">
            <span>Net to Organizer</span>
            <span>{{ currencySymbol }}{{ netToOrganizer.toFixed(2) }}</span>
          </div>
        </div>
      </div>

      <div class="print-actions">
        <button class="btn" @click="$emit('close')">Close</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import moment from 'moment';

const props = defineProps<{
  show: boolean;
  current: any | null;
  group: any[];
  feesSummary: any | null;
}>();


const current = computed(() => props.current);
const group = computed(() => Array.isArray(props.group) ? props.group : []);
const feesSummary = computed(() => props.feesSummary || {});

const currencySymbol = computed(() => current.value?.event?.currency_symbol || '$');

const paymentMethod = computed(() => current.value?.payment_method || 'Online');
const paymentChannel = computed(() => {
  // Best-effort text; adapt if you store a channel description separately
  return paymentMethod.value === 'stripe' ? 'Card (Stripe)' : paymentMethod.value;
});
const paymentId = computed(() => current.value?.stripe_id || '-');
const gatewayRef = computed(() => current.value?.gateway_ref || '-');
const paidAt = computed(() => current.value?.created_at ? moment(current.value.created_at).format('MMMM D, YYYY – h:mm A') : '-');

// Utilities mirroring PrintTicket
const toNumber = (v: any) => Number(v ?? 0) || 0;

const feeBreakdownFor = (row: any) => {
  const raw = row?.fee_breakdown;
  if (!raw) return {};
  if (typeof raw === 'string') {
    try {
      const parsed = JSON.parse(raw);
      return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (e) {
      return {};
    }
  }
  return (typeof raw === 'object') ? raw : {};
};

const baseSubtotalOf = (row: any) => {
  const sub = toNumber(row?.sub_total);
  if (sub > 0) return sub;
  const tables = Array.isArray(row?.table_addons)
    ? row.table_addons.reduce((s: number, t: any) => s + toNumber(t?.total_price), 0)
    : toNumber(row?.tables_total);
  return tables > 0 ? tables : 0;
};
const hasDrinkAddons = computed(() => Array.isArray(current.value?.drink_addons) && current.value.drink_addons.length > 0);
const isVipPackage = computed(() => {
  const hasTables = Array.isArray(current.value?.table_addons) && current.value.table_addons.length > 0;
  return hasTables;
});
const isCurrentRowFree = computed(() => {
  const baseIsZero = baseSubtotalOf(current.value) === 0;
  const noPackage = !current.value?.package_data;
  const noDrinks = !hasDrinkAddons.value;
  const noTables = !Array.isArray(current.value?.table_addons) || current.value.table_addons.length === 0;
  return baseIsZero && noPackage && noDrinks && noTables;
});

// Per-ticket drink/bottle/vip fees
const feeRates = computed(() => ({
  drink_fee_pct: toNumber(feesSummary.value?.drink_fee_pct),
  bottle_fee_pct: toNumber(feesSummary.value?.bottle_fee_pct),
  vip_fee_pct: toNumber(feesSummary.value?.vip_fee_pct),
}));
const perRowDrinkFees = computed(() => {
  const rate = feeRates.value.drink_fee_pct / 100;
  if (rate <= 0) return 0;
  const addons = current.value?.drink_addons;
  if (!Array.isArray(addons)) return 0;
  const cats = ['mixDrinks', 'wines', 'beers', 'waters', 'softDrinks'];
  return addons.reduce((sum: number, a: any) => sum + (cats.includes(a?.category) ? toNumber(a?.total_price) * rate : 0), 0);
});
const perRowBottleFees = computed(() => {
  const rate = feeRates.value.bottle_fee_pct / 100;
  if (rate <= 0) return 0;
  const addons = current.value?.drink_addons;
  if (!Array.isArray(addons)) return 0;
  return addons.reduce((sum: number, a: any) => sum + (((a?.category || '').toLowerCase() === 'bottles') ? toNumber(a?.total_price) * rate : 0), 0);
});
const perRowVipFee = computed(() => {
  const rate = feeRates.value.vip_fee_pct / 100;
  if (rate <= 0 || !isVipPackage.value) return 0;
  return baseSubtotalOf(current.value) * rate;
});

// Service/Processing: prefer per-ticket values; else allocate proportionally from group totals
const perTicketServiceFee = computed(() => toNumber(current.value?.fee));
const perTicketProcessingFee = computed(() => toNumber(current.value?.tax));
const groupServiceFeeTotal = computed(() => toNumber(feesSummary.value?.service_fee_total) || group.value.reduce((s, t: any) => s + toNumber(t?.fee), 0));
const groupProcessingFeeTotal = computed(() => toNumber(feesSummary.value?.processing_fee_total) || group.value.reduce((s, t: any) => s + toNumber(t?.tax), 0));
const wellnessAddonsFor = (row: any) => {
  const raw = row?.wellness_addons;
  if (Array.isArray(raw)) return raw;
  if (typeof raw === 'string') {
    try {
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }
  return [];
};
const wellnessTotalForRow = (row: any) => {
  const explicit = toNumber(row?.wellness_total);
  if (explicit > 0) return explicit;
  return wellnessAddonsFor(row).reduce((sum: number, addon: any) => sum + toNumber(addon?.total_price), 0);
};
const isWellnessRow = (row: any) => {
  if (!row) return false;
  const name = (row?.ticket_name || '').toLowerCase();
  const breakdown = row?.fee_breakdown;
  const hasWellnessAddons = wellnessAddonsFor(row).length > 0;
  // Don't mark as wellness if it's a cookout ticket
  if (name.includes('cookout') || isCookoutRow(row)) return false;
  return name.includes('wellness') || hasWellnessAddons || wellnessTotalForRow(row) > 0;
};

const isCookoutRow = (row: any) => {
  if (!row) return false;
  const name = (row?.ticket_name || '').toLowerCase();
  const hasCookoutAddons = Array.isArray(row?.cookout_addons) && row.cookout_addons.length > 0;
  const hasCookoutTotal = toNumber(row?.cookout_total) > 0;
  const hasIncludedProtein = !!row?.cookout_included_protein;
  return name.includes('cookout') || hasCookoutAddons || hasCookoutTotal || hasIncludedProtein;
};

const cookoutAddonsFor = (row: any) => {
  const raw = row?.cookout_addons;
  if (Array.isArray(raw)) return raw;
  if (typeof raw === 'string') {
    try {
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }
  return [];
};

const cookoutTotalForRow = (row: any) => {
  const explicit = toNumber(row?.cookout_total);
  if (explicit > 0) return explicit;
  return cookoutAddonsFor(row).reduce((sum: number, addon: any) => sum + toNumber(addon?.total_price), 0);
};
const wellnessTotal = computed(() => wellnessTotalForRow(current.value));
const isWellnessTicket = computed(() => isWellnessRow(current.value));
const isCookoutTicket = computed(() => isCookoutRow(current.value));
const cookoutTotal = computed(() => cookoutTotalForRow(current.value));
const visibleSubtotalOf = (row: any) => {
  if (!row) return 0;
  const base = baseSubtotalOf(row);
  const drinks = toNumber(row?.drinks_total);
  const tables = toNumber(row?.tables_total);
  const wellness = wellnessTotalForRow(row);
  const cookout = cookoutTotalForRow(row);
  return base + drinks + tables + wellness + cookout;
};
const currentVisibleSubtotal = computed(() => visibleSubtotalOf(current.value));
const groupVisibleSubtotal = computed(() => group.value.reduce((sum: number, row: any) => sum + visibleSubtotalOf(row), 0));
const nonWellnessVisibleSubtotal = computed(() => group.value.filter((row: any) => !isWellnessRow(row)).reduce((sum: number, row: any) => sum + visibleSubtotalOf(row), 0));
const serviceFee = computed(() => {
  if (perRowVipFee.value > 0) return 0;

  if (perTicketServiceFee.value > 0) {
    return perTicketServiceFee.value;
  }

  const total = groupServiceFeeTotal.value;
  if (total <= 0) return 0;

  const pool = nonWellnessVisibleSubtotal.value;
  if (pool <= 0) return total;

  return total * (currentVisibleSubtotal.value / pool);
});
const processingFee = computed(() => {
  if (perRowVipFee.value > 0) return 0;

  if (perTicketProcessingFee.value > 0) {
    return perTicketProcessingFee.value;
  }

  const total = groupProcessingFeeTotal.value;
  if (total <= 0) return 0;

  const pool = nonWellnessVisibleSubtotal.value;
  if (pool <= 0) return total;

  return total * (currentVisibleSubtotal.value / pool);
});

// Totals per current ticket
const drinkFees = computed(() => hasDrinkAddons.value ? perRowDrinkFees.value : 0);
const bottleFees = computed(() => hasDrinkAddons.value ? perRowBottleFees.value : 0);
const vipFees = computed(() => isVipPackage.value ? perRowVipFee.value : 0);
const groupMobileFeeTotal = computed(() => {
  const fsTotal = toNumber(feesSummary.value?.mobile_fee_total);
  if (fsTotal > 0) return fsTotal;
  return group.value.reduce((sum, t: any) => {
    const breakdown = feeBreakdownFor(t);
    return sum + toNumber(breakdown?.mobile_fee_amount) + toNumber(t?.mobile_fee);
  }, 0);
});
const mobileFee = computed(() => {
  if (!isWellnessTicket.value) return 0;
  const total = groupMobileFeeTotal.value;
  const breakdown = feeBreakdownFor(current.value);
  const fallback = toNumber(breakdown?.mobile_fee_amount);
  const fromColumn = toNumber(current.value?.mobile_fee);
  const baseFallback = fallback > 0 ? fallback : fromColumn;

  if (total <= 0) {
    const addons = wellnessAddonsFor(current.value);
    const hasMobileMode = Array.isArray(addons) && addons.some((a: any) => {
      const cat = String(a?.category || '').toLowerCase();
      const name = String(a?.name || '').toLowerCase();
      return cat === 'wellness_mode' && name.includes('mobile');
    });
    if (!hasMobileMode) return baseFallback;

    const cfg = current.value?.ticket?.wellness?.booking?.mobileFee
      ?? current.value?.ticket?.extraSetting?.wellness?.booking?.mobileFee
      ?? 0;
    const mobileFeeCfg = toNumber(cfg);
    if (mobileFeeCfg <= 0) return baseFallback;
    return mobileFeeCfg * toNumber(current.value?.no_of_tickets || 1);
  }
  const wellnessRows = group.value.filter((row: any) => isWellnessRow(row));
  if (wellnessRows.length <= 1) {
    return baseFallback > 0 ? baseFallback : total;
  }
  const share = total / wellnessRows.length;
  return wellnessRows.some(row => row.id === current.value?.id) ? share : 0;
});
const discount = computed(() => toNumber(current.value?.coupan_amount));

const calculatedGrossTotal = computed(() => {
  const visibleSubtotal = currentVisibleSubtotal.value;
  const includeStandardFees = !isCurrentRowFree.value;

  const servicePortion = includeStandardFees ? serviceFee.value : 0;
  const processingPortion = includeStandardFees ? processingFee.value : 0;
  const taxPortion = includeStandardFees ? toNumber(current.value?.event_tax) : 0;

  const total = visibleSubtotal
    + drinkFees.value
    + bottleFees.value
    + vipFees.value
    + mobileFee.value
    + servicePortion
    + processingPortion
    + taxPortion
    - discount.value;

  return Math.max(0, total);
});

const backendTotalPaid = computed(() => toNumber(current.value?.stripe_price ?? current.value?.total));

// Current ticket total paid
const currentTotalPaid = computed(() => {
  const backend = backendTotalPaid.value;
  const calculated = calculatedGrossTotal.value;
  if (backend > 0 && Math.abs(backend - calculated) <= 0.01) {
    return backend;
  }
  return calculated;
});

// Net = current ticket total paid minus applicable/visible fees
const netToOrganizer = computed(() => {
  const visibleService = perRowVipFee.value > 0 ? 0 : (isCurrentRowFree.value ? 0 : serviceFee.value);
  const visibleProcessing = perRowVipFee.value > 0 ? 0 : (isCurrentRowFree.value ? 0 : processingFee.value);
  return Math.max(0, currentTotalPaid.value - visibleService - visibleProcessing - drinkFees.value - bottleFees.value - vipFees.value - mobileFee.value);
});
</script>

<style scoped>
.modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,0.5);display:flex;align-items:center;justify-content:center;z-index:999}
.modal-box{background:#fff;border-radius:18px;padding:18px 18px 14px;max-width:720px;width:100%;box-shadow:0 20px 55px rgba(15,23,42,0.4)}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.modal-header h2{font-size:16px;margin:0}
.close-btn{border:none;background:transparent;font-size:20px;cursor:pointer}
.print-actions{margin-top:12px;display:flex;justify-content:flex-end;gap:8px}
.btn{padding:7px 12px;border-radius:999px;border:1px solid #e5e7eb;font-size:12px;cursor:pointer;background:#fff}
.content{font-size:13px}
.pay-summary{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:14px}
.pay-total{font-size:22px;font-weight:700}
.pay-status{font-size:12px;padding:4px 10px;border-radius:999px;background:#ecfdf3;color:#166534;font-weight:500;display:inline-flex;align-items:center;gap:6px}
.pay-method-card{border-radius:12px;border:1px solid #e5e7eb;padding:10px 12px;margin-bottom:12px;font-size:13px;display:flex;justify-content:space-between;gap:10px}
.pay-method-main{font-weight:600}
.pay-method-sub{font-size:12px;color:#6b7280}
.pay-grid{display:grid;grid-template-columns:1.3fr 1.3fr;gap:10px;font-size:12px;margin-bottom:10px}
.meta-label{color:#6b7280;margin-bottom:2px}
.meta-value{font-weight:500}
.details-fees{border-radius:12px;border:1px solid #e5e7eb;padding:10px 12px;margin-top:6px;font-size:13px}
.fee-row{display:flex;justify-content:space-between;font-size:13px;margin-top:6px}
.fee-row.total{margin-top:10px;font-weight:700}
</style>
