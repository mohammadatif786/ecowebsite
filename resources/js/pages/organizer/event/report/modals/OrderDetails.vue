es <template>
  <div v-if="show && current" class="modal-backdrop">
    <div class="modal-box">
      <div class="modal-header">
        <h2>Order Details</h2>
        <button class="close-btn" @click="$emit('close')" aria-label="Close">&times;</button>
      </div>

      <div class="content">
        <div class="section-title">Order Overview</div>
        <div class="pay-grid">
          <div>
            <div class="meta-label">Order Reference</div>
            <div class="meta-value">{{ orderRef }}</div>
          </div>
          <div>
            <div class="meta-label">Gateway Reference</div>
            <div class="meta-value">{{ gatewayRef }}</div>
          </div>
          <div>
            <div class="meta-label">Attendee</div>
            <div class="meta-value">{{ attendeeName }}</div>
          </div>
          <div>
            <div class="meta-label">Email</div>
            <div class="meta-value">{{ attendeeEmail }}</div>
          </div>
        </div>

        <div class="divider"></div>

        <div class="section-title">Event Details</div>
        <div class="pay-grid">
          <div>
            <div class="meta-label">Start Date &amp; Time</div>
            <div class="meta-value">{{ eventStart }}</div>
          </div>
          <div>
            <div class="meta-label">End Date &amp; Time</div>
            <div class="meta-value">{{ eventEnd }}</div>
          </div>
          <div style="grid-column: 1 / span 2;">
            <div class="meta-label">Location</div>
            <div class="meta-value">{{ eventLocation }}</div>
          </div>
        </div>

        <div class="divider"></div>

        <div class="section-title">Ticket &amp; Fees (This Ticket)</div>
        <div class="details-fees">
          <div class="fee-row">
            <span>Ticket Subtotal</span>
            <span>{{ currencySymbol }}{{ baseSubtotal.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="drinksTotal > 0">
            <span>Drinks Total</span>
            <span>{{ currencySymbol }}{{ drinksTotal.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="(serviceFeeRow + processingFeeRow) > 0">
            <span>Service &amp; Processing Fee</span>
            <span>{{ currencySymbol }}{{ (serviceFeeRow + processingFeeRow).toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="drinkFeesRow > 0">
            <span>Drink Processing Fees</span>
            <span>{{ currencySymbol }}{{ drinkFeesRow.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="bottleFeesRow > 0">
            <span>Bottle Processing Fees</span>
            <span>{{ currencySymbol }}{{ bottleFeesRow.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="vipFeesRow > 0">
            <span>Table Processing Fees</span>
            <span>{{ currencySymbol }}{{ vipFeesRow.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="mobileFeeRow > 0">
            <span>Mobile Fee</span>
            <span>{{ currencySymbol }}{{ mobileFeeRow.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="current?.event_tax > 0">
            <span>Tax</span>
            <span>{{ currencySymbol }}{{ Number(current?.event_tax || 0).toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="discountRow > 0">
            <span>Discount</span>
            <span>- {{ currencySymbol }}{{ discountRow.toFixed(2) }}</span>
          </div>
          <div class="fee-row total">
            <span>Total Paid</span>
            <span>{{ currencySymbol }}{{ totalPaidRow.toFixed(2) }}</span>
          </div>
        </div>

        <div class="status-row" style="margin-top:10px;">
          <span class="status-label">Status</span>
          <span class="status-value">{{ current?.ticket_status }}</span>
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

const currencySymbol = computed(() => current.value?.event?.currency_symbol || '$');

const toNumber = (v: any) => {
  const n = Number(v ?? 0);
  return Number.isFinite(n) ? n : 0;
};

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

const orderRef = computed(() => current.value?.ticket_qrcode_id || '-');
const attendeeName = computed(() => current.value?.user?.name || 'N/A');
const attendeeEmail = computed(() => current.value?.user?.email || 'N/A');
const gatewayRef = computed(() => current.value?.gateway_ref || '-');

const eventLocation = computed(() => {
  const e = current.value?.event || {};
  return [e?.venue, e?.city, e?.state, e?.country].filter(Boolean).join(', ');
});

// Wellness helpers mirror PaymentDetails modal
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
const wellnessTotal = computed(() => wellnessTotalForRow(current.value));
const isWellnessTicket = computed(() => {
  const name = (current.value?.ticket_name || '').toLowerCase();
  // Don't mark as wellness if it's a cookout ticket
  if (name.includes('cookout') || isCookoutTicket.value) return false;
  return name.includes('wellness') || wellnessTotal.value > 0;
});

const isCookoutTicket = computed(() => {
  const name = (current.value?.ticket_name || '').toLowerCase();
  const hasCookoutAddons = Array.isArray(current.value?.cookout_addons) && current.value.cookout_addons.length > 0;
  const hasCookoutTotal = toNumber(current.value?.cookout_total) > 0;
  const hasIncludedProtein = !!current.value?.cookout_included_protein;
  return name.includes('cookout') || hasCookoutAddons || hasCookoutTotal || hasIncludedProtein;
});

const cookoutTotal = computed(() => toNumber(current.value?.cookout_total));
const groupMobileFeeTotal = computed(() => {
  const fsTotal = toNumber(props.feesSummary?.mobile_fee_total);
  if (fsTotal > 0) return fsTotal;
  return group.value.reduce((sum, row: any) => {
    const breakdown = feeBreakdownFor(row);
    return sum + toNumber(breakdown?.mobile_fee_amount) + toNumber(row?.mobile_fee);
  }, 0);
});

const eventDetails = computed(() => current.value?.event?.event_details || null);
const eventStart = computed(() => {
  const d = eventDetails.value;
  if (!d) return '-';
  if (d.event_type === 'single') {
    const date = d.single_event_date; const time = d.single_start_time;
    if (!date) return '-';
    const dm = moment(date);
    if (time) {
      const t = time.length === 5 ? `${time}:00` : time;
      return moment(`${dm.format('YYYY-MM-DD')} ${t}`).format('dddd, Do MMM YYYY, h:mm A');
    }
    return dm.format('dddd, Do MMM YYYY');
  }
  if (d.event_type === 'recurring') {
    const date = d.recurr_start_date; if (!date) return '-';
    return moment(date).format('dddd, Do MMM YYYY');
  }
  return '-';
});
const eventEnd = computed(() => {
  const d = eventDetails.value;
  if (!d) return '-';
  if (d.event_type === 'single') {
    const date = d.single_event_date; const time = d.single_end_time;
    if (!date) return '-';
    const dm = moment(date);
    if (time) {
      const t = time.length === 5 ? `${time}:00` : time;
      return moment(`${dm.format('YYYY-MM-DD')} ${t}`).format('dddd, Do MMM YYYY, h:mm A');
    }
    return dm.format('dddd, Do MMM YYYY');
  }
  if (d.event_type === 'recurring') {
    const date = d.recurr_end_date; if (!date) return '-';
    return moment(date).format('dddd, Do MMM YYYY');
  }
  return '-';
});

// Totals (per current row)
const baseSubtotal = computed(() => {
  const sub = toNumber(current.value?.sub_total);
  if (sub > 0) return sub;
  const tables = toNumber(current.value?.tables_total);
  return tables > 0 ? tables : 0;
});
const drinksTotal = computed(() => toNumber(current.value?.drinks_total));
const wellnessTotalRow = computed(() => wellnessTotalForRow(current.value));

// VIP Package detection (same as other files)
const isVipPackage = computed(() => {
  const hasTables = Array.isArray(current.value?.table_addons) && current.value.table_addons.length > 0;
  return hasTables;
});

// Fee rates from feesSummary
const feeRates = computed(() => ({
  drink_fee_pct: toNumber(props.feesSummary?.drink_fee_pct),
  bottle_fee_pct: toNumber(props.feesSummary?.bottle_fee_pct),
  vip_fee_pct: toNumber(props.feesSummary?.vip_fee_pct),
}));

// Calculate VIP fee dynamically
const perRowVipFee = computed(() => {
  const rate = feeRates.value.vip_fee_pct / 100;
  if (rate <= 0 || !isVipPackage.value) return 0;
  return baseSubtotal.value * rate;
});

// Calculate drink and bottle fees dynamically
const hasDrinkAddons = computed(() => Array.isArray(current.value?.drink_addons) && current.value.drink_addons.length > 0);
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

// Conditional service and processing fees (same logic as other files)
const feeBreakdownRow = computed(() => (current.value?.fee_breakdown || {}));
const serviceFeeRow = computed(() => {
  if (perRowVipFee.value > 0) return 0;
  return toNumber(current.value?.fee);
});
const processingFeeRow = computed(() => {
  if (perRowVipFee.value > 0) return 0;
  return toNumber(current.value?.tax);
});

// Dynamic fee calculations
const drinkFeesRow = computed(() => hasDrinkAddons.value ? perRowDrinkFees.value : toNumber(feeBreakdownRow.value?.drink_fee_amount));
const bottleFeesRow = computed(() => hasDrinkAddons.value ? perRowBottleFees.value : toNumber(feeBreakdownRow.value?.bottle_fee_amount));
const vipFeesRow = computed(() => hasDrinkAddons.value ? 0 : perRowVipFee.value);
const discountRow = computed(() => toNumber(current.value?.coupan_amount));
const mobileFeeRow = computed(() => {
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
  const wellnessRows = group.value.filter((row: any) => {
    const name = (row?.ticket_name || '').toLowerCase();
    return name.includes('wellness') || wellnessTotalForRow(row) > 0;
  });
  if (wellnessRows.length <= 1) {
    return baseFallback > 0 ? baseFallback : total;
  }
  const share = total / wellnessRows.length;
  return wellnessRows.some(row => row.id === current.value?.id) ? share : 0;
});

// Calculate total paid dynamically based on actual fees
const totalPaidRow = computed(() => {
  const baseTotal = baseSubtotal.value + drinksTotal.value + wellnessTotalRow.value + cookoutTotal.value;
  const taxTotal = toNumber(current.value?.event_tax);
  const calculatedTotal = Math.max(0, baseTotal + drinkFeesRow.value + bottleFeesRow.value + vipFeesRow.value + mobileFeeRow.value + serviceFeeRow.value + processingFeeRow.value + taxTotal - discountRow.value);
  
  // Use backend total if it matches closely, otherwise use calculated total
  const backendTotal = toNumber(current.value?.stripe_price ?? current.value?.total);
  return (Math.abs(backendTotal - calculatedTotal) <= 0.01 && backendTotal > 0) ? backendTotal : calculatedTotal;
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
.section-title{font-size:14px;font-weight:600;margin-bottom:10px;display:flex;align-items:center;gap:6px}
.pay-grid{display:grid;grid-template-columns:1.3fr 1.3fr;gap:10px;font-size:12px;margin-bottom:10px}
.meta-label{color:#6b7280;margin-bottom:2px}
.meta-value{font-weight:500}
.details-fees{border-radius:12px;border:1px solid #e5e7eb;padding:10px 12px;margin-top:6px;font-size:13px}
.fee-row{display:flex;justify-content:space-between;font-size:13px;margin-top:6px}
.fee-row.total{margin-top:10px;font-weight:700}
.divider{margin:20px 0 18px;border-bottom:1px dashed #e5e7eb}
.status-label{font-weight:600}
.status-value{color:#16a34a;font-weight:600;margin-left:4px}
</style>
