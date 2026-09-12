<template>
  <!-- Print Ticket Modal -->
  <div v-if="show && ticket" class="modal-backdrop">
    <div class="modal-box">
      <div class="modal-header">
        <h2>Print E‑Ticket</h2>
        <button class="close-btn" @click="closePrintTicket" aria-label="Close">&times;</button>
      </div>

      <div class="ticket-page" id="ticketPrintArea">
        <div class="ticket-header">
          <div class="logo-pill">
            <img src="/storage/events/logo_for_e_tickets.png" alt="">
          </div>
          <div class="title-block">
            <h3>🎟️ E‑Tickets</h3>
            <span> {{ ticket.no_of_tickets }} ticket in this purchase</span>
          </div>
        </div>

        <div class="event-title"></div>

        <div class="event-grid">

          <div>
            <div class="field-label">Start Date &amp; Time</div>
            <div class="field-value">{{ startDateTime }}</div>
          </div>
          <div>
            <div class="field-label">Event Name</div>
            <div class="field-value">{{ ticket.event.title }}</div>
          </div>
          <div>
            <div class="field-label">End Date &amp; Time</div>
            <div class="field-value">{{ endDateTime }}</div>
          </div>
          <div>
            <div class="field-label">Location</div>
            <div class="field-value">{{ ticket.event.venue }}, {{ ticket.event.city }}, {{ ticket.event.state }}, {{
              ticket.event.country }}</div>
          </div>
          <div>
            <div class="field-label">Scanned By</div>
            <div class="field-value">{{ticket.checkins.map((c: any) => c.scanned_by_email).join(', ')}}</div>
          </div>
          <div>
            <div class="field-label">Check-in</div>
            <div class="field-value">{{ticket.checkins.map((c: any) => formatDate(c.checked_in_at)).join(', ')}}</div>
          </div>
        </div>

        <div class="divider"></div>

        <div class="two-col-info">
          <div class="info-label">
            <span>Full Name</span>
            <span>Phone</span>
            <span>Email</span>
            <span>Total Tickets in Purchase</span>
          </div>
          <div class="info-values">
            <div>{{ ticket.user.name }}</div>
            <div>{{ ticket.user.phone_number }}</div>
            <div>{{ ticket.user.email }}</div>
            <div>{{ ticket.no_of_tickets }}</div>
          </div>
        </div>

        <div class="divider"></div>

        <div class="section-title">🧾 Tickets in This Purchase</div>
        <div class="ticket-card">
          <div class="ticket-card-left">
            <div class="ticket-name">{{ ticket.ticket_name }}</div>
            <div class="ticket-meta">
              {{ ticket.no_of_tickets }} x {{ ticket.ticket_type }}
            </div>
            <div class="ticket-subtitle" v-if="ticket?.drink_addons !== null">
              🥃 <strong>Drinks:</strong>
              <div class="ticket-subtitle">
                <span v-for="drink in ticket.drink_addons" :key="drink.id">
                  {{ drink.quantity }} x {{ drink.name }} ({{ currencySymbol }}{{ Number(drink.unit_price || 0).toFixed(2) }})
                </span>
              </div>
            </div>
            <div class="ticket-subtitle" style="margin-top:4px;" v-if="ticket?.table_addons !== null">
              🪑 <strong>Tables:</strong>
              <div>
                <span v-for="table in ticket.table_addons" :key="table.id">
                  {{ table.name }} x ( {{ table.quantity }} )
                </span>
              </div>
            </div>
            <div class="ticket-subtitle" style="margin-top:4px;" v-if="ticket?.package_id !== null">
              🪑 <strong>Table Package:</strong>
              <div>
                <span>{{ ticket.package_data.name }}</span>
              </div>
              <div>
                <span v-for="package_data in ticket.package_data.bottles" :key="package_data.id">
                  {{ package_data.name }} x ( {{ package_data.qty }} )
                </span>
              </div>
              <div>
                <span v-for="package_data in ticket.package_data.chasers" :key="package_data.id">
                  {{ package_data.name }} x ( {{ package_data.qty }} )
                </span>
              </div>
              <div>
                <span>{{ ticket.package_data.notes }}</span>
              </div>
            </div>
            <div class="ticket-subtitle" style="margin-top:4px;" v-if="cookoutAddons.length > 0 || ticket?.cookout_included_protein">
              🍖 <strong>Cookout:</strong>
              <div v-if="ticket?.cookout_included_protein" class="mt-1">
                {{ ticket.cookout_included_protein }} (Included Plate) x 1 — Free
              </div>
              <div v-if="ticket?.cookout_included_sides && ticket.cookout_included_sides.length > 0" class="mt-1 text-gray-600">
                Sides: {{ ticket.cookout_included_sides.join(', ') }}
              </div>
              <div v-for="addon in cookoutAddons" :key="addon.name" class="mt-1">
                {{ addon.quantity }} × {{ addon.name }}
                <span class="text-gray-500" v-if="addon.total_price"> — {{ currencySymbol }}{{ Number(addon.total_price).toFixed(2) }}</span>
              </div>
            </div>
            <div class="ticket-subtitle" style="margin-top:4px;" v-if="wellnessAddons.length > 0">
              💆 <strong>Wellness Services:</strong>
              <div class="mt-1" v-for="addon in wellnessAddons" :key="addon.name">
                {{ addon.quantity }} × {{ addon.name }}
                <span class="text-gray-500" v-if="addon.total_price"> — {{ currencySymbol }}{{ Number(addon.total_price).toFixed(2) }}</span>
              </div>
            </div>
            <div class="ticket-subtitle" style="margin-top:8px;">
              Subtotal (Ticket)
              <span class="font-semibold">
                <template v-if="displayBasePrice === 0 && !hasAnyAddons">
                  Free
                </template>
                <template v-else>
                  {{ currencySymbol }}{{ displayBasePrice.toFixed(2) }}
                </template>
              </span>
              <br>
              <span v-if="drinksTotal > 0">
                Drinks + Bottles: {{ currencySymbol }}{{ drinksTotal.toFixed(2) }}
              </span>

              <span v-if="perRowVipFee > 0">
                <br>VIP Package Fees: {{ currencySymbol }}{{ perRowVipFee.toFixed(2) }}
              </span>
              <span v-if="tablesTotal > 0">
                <br>Tables Total: {{ currencySymbol }}{{ tablesTotal.toFixed(2) }}
              </span>
              <span v-if="wellnessTotal > 0">
                <br>Wellness Addons: {{ currencySymbol }}{{ wellnessTotal.toFixed(2) }}
              </span>
              <span v-if="discountAmount > 0" class="discount-line">
                <br>Discount: -{{ currencySymbol }}{{ discountAmount.toFixed(2) }}
              </span>
            </div>
          </div>
          <div class="ticket-card-right">
            <span v-if="ticketSubtotal === 0 && !hasAnyAddons">Free</span>
            <span v-else>{{ currencySymbol }}{{ ticketSubtotal.toFixed(2) }}</span>
          </div>
        </div>

        <div style="margin-top:18px;">
          <div class="fee-row">
            <span>Services Subtotal</span>
            <span>{{ currencySymbol }}{{ servicesSubtotal.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="drinksBaseSubtotal > 0">
            <span>Drinks Subtotal</span>
            <span>{{ currencySymbol }}{{ drinksBaseSubtotal.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="bottlesBaseSubtotal > 0">
            <span>Bottles Subtotal</span>
            <span>{{ currencySymbol }}{{ bottlesBaseSubtotal.toFixed(2) }}</span>
          </div>
          <div class="fee-row">
            <span>Subtotal</span>
            <span>{{ currencySymbol }}{{ orderSubtotal.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="hasDrinkAddons && perRowDrinkFees > 0">
            <span>Drink Processing Fees</span>
            <span>{{ currencySymbol }}{{ perRowDrinkFees.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="hasDrinkAddons && perRowBottleFees > 0">
            <span>Bottle Processing Fees</span>
            <span>{{ currencySymbol }}{{ perRowBottleFees.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="perRowVipFee > 0">
            <span>Table Processing Fees</span>
            <span>{{ currencySymbol }}{{ perRowVipFee.toFixed(2) }}</span>
          </div>

          <div class="fee-row" v-if="discountAmount > 0">
            <span>Discount</span>
            <span>- {{ currencySymbol }}{{ discountAmount.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="(serviceFee + processingFee) > 0 && !isRowFree">
            <span>Service &amp; Processing Fee</span>
            <span>{{ currencySymbol }}{{ (serviceFee + processingFee).toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="mobileFee > 0">
            <span>Mobile Fee</span>
            <span>{{ currencySymbol }}{{ mobileFee.toFixed(2) }}</span>
          </div>
          <div class="fee-row" v-if="ticket.event_tax > 0 && !isRowFree">
            <span>Tax</span>
            <span>{{ currencySymbol }}{{ Number(ticket.event_tax || 0).toFixed(2) }}</span>
          </div>

          <div class="fee-row total">
            <span>Total Paid for This Ticket</span>
            <span>
              <template v-if="Number(totalPaid) === 0">Free</template>
              <template v-else>{{ currencySymbol }}{{ totalPaid.toFixed(2) }}</template>
            </span>
          </div>
          <div class="status-row">
            <span class="status-label">Status</span>
            <span class="status-value">{{ ticket.ticket_status }}</span>
          </div>
        </div>
      </div>

      <div class="print-actions">
        <button class="btn" @click="closePrintTicket">Close</button>
        <button class="btn btn-primary" @click="printTicketNow">Print Ticket</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import moment from 'moment';
const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleString([], {
    dateStyle: 'medium',
    timeStyle: 'short'
  });
};
const props = defineProps<{
  show: boolean;
  ticket: any;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const ticket = computed<any>(() => {
  const t: any = props.ticket;
  return t?.current ?? t;
});

const ticketGroup = computed<any[]>(() => {
  const t: any = props.ticket;
  if (t?.group && Array.isArray(t.group)) return t.group;
  return ticket.value ? [ticket.value] : [];
});

const currencySymbol = computed(() => {
  return ticket.value?.event?.currency_symbol || '$';
});

const feesSummary = computed<any | null>(() => {
  const t: any = props.ticket;
  return t?.feesSummary ?? null;
});

const eventDetails = computed(() => ticket.value?.event?.event_details || null);

const startDateTime = computed(() => {
  const details = eventDetails.value;
  if (!details) return '';

  if (details.event_type === 'single') {
    const date = details.single_event_date;
    const time = details.single_start_time;

    if (!date) return '';

    const dateMoment = moment(date);
    if (time) {
      const timeWithSeconds = time.length === 5 ? `${time}:00` : time;
      const dateTimeStr = `${dateMoment.format('YYYY-MM-DD')} ${timeWithSeconds}`;
      return moment(dateTimeStr).format('dddd, Do MMM YYYY, h:mm A');
    }

    return dateMoment.format('dddd, Do MMM YYYY');
  }

  if (details.event_type === 'recurring') {
    const date = details.recurr_start_date;
    if (!date) return '';
    return moment(date).format('dddd, Do MMM YYYY');
  }

  return '';
});

const endDateTime = computed(() => {
  const details = eventDetails.value;
  if (!details) return '';

  if (details.event_type === 'single') {
    const date = details.single_event_date;
    const time = details.single_end_time;

    if (!date) return '';

    const dateMoment = moment(date);
    if (time) {
      const timeWithSeconds = time.length === 5 ? `${time}:00` : time;
      const dateTimeStr = `${dateMoment.format('YYYY-MM-DD')} ${timeWithSeconds}`;
      return moment(dateTimeStr).format('dddd, Do MMM YYYY, h:mm A');
    }

    return dateMoment.format('dddd, Do MMM YYYY');
  }

  if (details.event_type === 'recurring') {
    const date = details.recurr_end_date;
    if (!date) return '';
    return moment(date).format('dddd, Do MMM YYYY');
  }

  return '';
});

const toNumber = (value: any) => {
  const num = Number(value ?? 0);
  return Number.isFinite(num) ? num : 0;
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

const drinksTotal = computed(() => toNumber(ticket.value?.drinks_total));
const tablesTotal = computed(() => toNumber(ticket.value?.tables_total));
const wellnessTotal = computed(() => {
  const explicit = toNumber(ticket.value?.wellness_total);
  if (explicit > 0) return explicit;
  const addons = ticket.value?.wellness_addons;
  if (Array.isArray(addons)) {
    return addons.reduce((sum: number, addon: any) => sum + toNumber(addon?.total_price), 0);
  }
  if (typeof addons === 'string') {
    try {
      const parsed = JSON.parse(addons);
      if (Array.isArray(parsed)) {
        return parsed.reduce((sum: number, addon: any) => sum + toNumber(addon?.total_price), 0);
      }
    } catch (e) {
      return 0;
    }
  }
  return 0;
});

const cookoutTotal = computed(() => toNumber(ticket.value?.cookout_total));
const baseSubtotal = computed(() => {
  const sub = toNumber(ticket.value?.sub_total);
  if (sub > 0) return sub;
  const tableOnly = tablesTotal.value;
  return tableOnly > 0 ? tableOnly : 0;
});

const ticketSubtotal = computed(() => baseSubtotal.value);

const ticketAndAddonsTotal = computed(() => baseSubtotal.value + drinksTotal.value + tablesTotal.value + wellnessTotal.value + cookoutTotal.value);

const baseDrinkSplit = computed(() => {
  let drinksBase = 0;
  let bottlesBase = 0;
  const list = ticket.value?.drink_addons;
  if (Array.isArray(list)) {
    list.forEach((d: any) => {
      const amt = toNumber(d?.total_price);
      if ((d?.category || '') === 'bottles') bottlesBase += amt; else drinksBase += amt;
    });
  }
  return { drinksBase, bottlesBase };
});

const drinksBaseSubtotal = computed(() => {
  const computedBase = baseDrinkSplit.value.drinksBase;
  if (computedBase > 0) return computedBase;
  if (!Array.isArray(ticket.value?.drink_addons)) return 0;
  return 0;
});

const bottlesBaseSubtotal = computed(() => {
  const computedBase = baseDrinkSplit.value.bottlesBase;
  if (computedBase > 0) return computedBase;
  if (!Array.isArray(ticket.value?.drink_addons)) return 0;
  return 0;
});

const servicesSubtotal = computed(() => {
  const sub = toNumber(ticket.value?.sub_total);
  const tablePart = sub > 0 ? tablesTotal.value : 0;
  return baseSubtotal.value + tablePart + wellnessTotal.value + cookoutTotal.value;
});

const orderSubtotal = computed(() => servicesSubtotal.value + drinksBaseSubtotal.value + bottlesBaseSubtotal.value);

const hasAnyAddons = computed(() => {
  const t: any = ticket.value || {};
  const hasDrinks = Array.isArray(t?.drink_addons) && t.drink_addons.length > 0;
  const hasTables = Array.isArray(t?.table_addons) && t.table_addons.length > 0;
  const hasPackage = !!t?.package_data;
  const hasWellness = Array.isArray(t?.wellness_addons) && t.wellness_addons.length > 0;
  return hasDrinks || hasTables || hasPackage || hasWellness;
});

const hasDrinkAddons = computed(() => {
  const t: any = ticket.value || {};
  return Array.isArray(t?.drink_addons) && t.drink_addons.length > 0;
});

const wellnessAddons = computed(() => {
  const raw = ticket.value?.wellness_addons;
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
});

const cookoutAddons = computed(() => {
  const raw = ticket.value?.cookout_addons;
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
});

const discountAmount = computed(() => toNumber(ticket.value?.coupan_amount));

const feeRates = computed(() => ({
  drink_fee_pct: toNumber(feesSummary.value?.drink_fee_pct),
  bottle_fee_pct: toNumber(feesSummary.value?.bottle_fee_pct),
  vip_fee_pct: toNumber(feesSummary.value?.vip_fee_pct),
}));

const isVipPackage = computed(() => {
  const hasTables = Array.isArray(ticket.value?.table_addons) && ticket.value.table_addons.length > 0;
  return hasTables;
});

const perRowDrinkFees = computed(() => {
  const rate = feeRates.value.drink_fee_pct / 100;
  if (rate <= 0) return 0;
  const addons = ticket.value?.drink_addons;
  if (!Array.isArray(addons)) return 0;
  const regularCategories = ['mixDrinks', 'wines', 'beers', 'waters', 'softDrinks'];
  return addons.reduce((sum: number, addon: any) => {
    if (regularCategories.includes(addon?.category)) {
      return sum + toNumber(addon?.total_price) * rate;
    }
    return sum;
  }, 0);
});

const perRowBottleFees = computed(() => {
  const rate = feeRates.value.bottle_fee_pct / 100;
  if (rate <= 0) return 0;
  const addons = ticket.value?.drink_addons;
  if (!Array.isArray(addons)) return 0;
  return addons.reduce((sum: number, addon: any) => {
    if ((addon?.category || '').toLowerCase() === 'bottles') {
      return sum + toNumber(addon?.total_price) * rate;
    }
    return sum;
  }, 0);
});

const perRowVipFee = computed(() => {
  const rate = feeRates.value.vip_fee_pct / 100;
  if (rate <= 0 || !isVipPackage.value) return 0;

  const tables = ticket.value?.table_addons;
  if (Array.isArray(tables) && tables.length > 0) {
    return tables.reduce((sum: number, table: any) => {
      const tablePrice = toNumber(table?.unit_price) * toNumber(table?.quantity);
      return sum + (tablePrice * rate);
    }, 0);
  }

  return baseSubtotal.value * rate;
});

const displayBasePrice = computed(() => baseSubtotal.value);

const isWellnessTicket = computed(() => {
  const name = (ticket.value?.ticket_name || '').toLowerCase();
  const hasWellnessAddons = Array.isArray(ticket.value?.wellness_addons) && ticket.value.wellness_addons.length > 0;
  // Don't mark as wellness if it's a cookout ticket
  if (name.includes('cookout') || isCookoutTicket.value) return false;
  return name.includes('wellness') || wellnessTotal.value > 0 || hasWellnessAddons;
});

const isCookoutTicket = computed(() => {
  const name = (ticket.value?.ticket_name || '').toLowerCase();
  const hasCookoutAddons = Array.isArray(ticket.value?.cookout_addons) && ticket.value.cookout_addons.length > 0;
  const hasCookoutTotal = toNumber(ticket.value?.cookout_total) > 0;
  const hasIncludedProtein = !!ticket.value?.cookout_included_protein;
  return name.includes('cookout') || hasCookoutAddons || hasCookoutTotal || hasIncludedProtein;
});

const isRowFree = computed(() => {
  const baseIsZero = toNumber(baseSubtotal.value) === 0;
  const noPackage = !ticket.value?.package_data;
  const noDrinks = !Array.isArray(ticket.value?.drink_addons) || ticket.value.drink_addons.length === 0;
  const noTables = !Array.isArray(ticket.value?.table_addons) || ticket.value.table_addons.length === 0;
  return baseIsZero && noPackage && noDrinks && noTables;
});

const perTicketServiceFee = computed(() => toNumber(ticket.value?.fee));
const groupServiceFeeTotal = computed(() => {
  const fs = feesSummary.value;
  if (fs && fs.service_fee_total != null) return toNumber(fs.service_fee_total);
  return ticketGroup.value.reduce((sum, t: any) => sum + toNumber(t?.fee), 0);
});

const groupMobileFeeTotal = computed(() => {
  const fs = feesSummary.value;
  if (fs && fs.mobile_fee_total != null && toNumber(fs.mobile_fee_total) > 0) return toNumber(fs.mobile_fee_total);
  return ticketGroup.value.reduce((sum, t: any) => {
    const breakdown = feeBreakdownFor(t);
    return sum + toNumber(breakdown?.mobile_fee_amount) + toNumber(t?.mobile_fee);
  }, 0);
});

const mobileFee = computed(() => {
  if (!isWellnessTicket.value) return 0;
  const total = groupMobileFeeTotal.value;
  const breakdown = feeBreakdownFor(ticket.value);
  const fallback = toNumber(breakdown?.mobile_fee_amount);
  const fromColumn = toNumber(ticket.value?.mobile_fee);
  const baseFallback = fallback > 0 ? fallback : fromColumn;

  if (total <= 0) {
    const addons = wellnessAddons.value;
    const hasMobileMode = Array.isArray(addons) && addons.some((a: any) => {
      const cat = String(a?.category || '').toLowerCase();
      const name = String(a?.name || '').toLowerCase();
      return cat === 'wellness_mode' && name.includes('mobile');
    });
    if (!hasMobileMode) return baseFallback;

    const cfg = ticket.value?.ticket?.wellness?.booking?.mobileFee
      ?? ticket.value?.ticket?.extraSetting?.wellness?.booking?.mobileFee
      ?? 0;
    const mobileFeeCfg = toNumber(cfg);
    if (mobileFeeCfg <= 0) return baseFallback;
    return mobileFeeCfg * toNumber(ticket.value?.no_of_tickets || 1);
  }
  const wellnessRows = ticketGroup.value.filter((t: any) => {
    const name = (t?.ticket_name || '').toLowerCase();
    const addons = Array.isArray(t?.wellness_addons) ? t.wellness_addons : [];
    const hasWellnessAddon = addons.length > 0;
    // Exclude cookout tickets
    if (name.includes('cookout')) return false;
    return name.includes('wellness') || toNumber(t?.wellness_total) > 0 || hasWellnessAddon;
  });
  if (wellnessRows.length <= 1) {
    return baseFallback > 0 ? baseFallback : total;
  }
  const share = total / wellnessRows.length;
  return wellnessRows.some(row => row.id === ticket.value?.id) ? share : 0;
});

const groupVisibleSubtotal = computed(() => {
  return ticketGroup.value.reduce((sum, t: any) => {
    const sub = toNumber(t?.sub_total);
    const drinks = toNumber(t?.drinks_total);
    const tables = toNumber(t?.tables_total);
    const cookout = toNumber(t?.cookout_total);
    const wellness = toNumber(t?.wellness_total);
    const base = sub > 0 ? sub : (Array.isArray(t?.table_addons)
      ? t.table_addons.reduce((s: number, ta: any) => s + toNumber(ta?.total_price), 0)
      : tables);
    return sum + toNumber(base) + drinks + toNumber(tables) + cookout + wellness;
  }, 0);
});
const serviceFee = computed(() => {
  if (perRowVipFee.value > 0) return 0;

  if (perTicketServiceFee.value > 0) return perTicketServiceFee.value;
  const total = groupServiceFeeTotal.value;
  if (total <= 0) return 0;
  const gv = groupVisibleSubtotal.value;
  if (gv <= 0) return total;
  const share = ticketAndAddonsTotal.value / gv;
  return total * share;
});

const perTicketProcessingFee = computed(() => toNumber(ticket.value?.tax));
const groupProcessingFeeTotal = computed(() => {
  const fs = feesSummary.value;
  if (fs && fs.processing_fee_total != null) return toNumber(fs.processing_fee_total);
  return ticketGroup.value.reduce((sum, t: any) => sum + toNumber(t?.tax), 0);
});
const processingFee = computed(() => {
  if (perRowVipFee.value > 0) return 0;

  if (perTicketProcessingFee.value > 0) return perTicketProcessingFee.value;
  const total = groupProcessingFeeTotal.value;
  if (total <= 0) return 0;
  const gv = groupVisibleSubtotal.value;
  if (gv <= 0) return total; // fallback
  const share = ticketAndAddonsTotal.value / gv;
  return total * share;
});

const totalPaid = computed(() => {
  const t: any = ticket.value || {};

  const visibleSubtotal = ticketAndAddonsTotal.value;
  const visibleDrinkFees = hasDrinkAddons.value ? (perRowDrinkFees.value + perRowBottleFees.value) : 0;
  const visibleVipFee = isVipPackage.value ? perRowVipFee.value : 0;
  const visibleService = perRowVipFee.value > 0 ? 0 : (!isRowFree.value ? serviceFee.value : 0);
  const visibleProcessing = perRowVipFee.value > 0 ? 0 : (!isRowFree.value ? processingFee.value : 0);
  const visibleMobile = mobileFee.value;
  const discount = discountAmount.value;
  const visibleTax = !isRowFree.value ? toNumber(t.event_tax) : 0;

  const computedVisible = Math.max(0, visibleSubtotal + visibleDrinkFees + visibleVipFee + visibleService + visibleProcessing + visibleMobile + visibleTax - discount);

  const backend = toNumber(t?.stripe_price ?? t?.total);
  if (backend > 0 && Math.abs(backend - computedVisible) <= 0.01) {
    return backend;
  }
  return computedVisible;
});

function closePrintTicket() {
  emit('close');
}

function printTicketNow() {
  const ticketElement = document.getElementById('ticketPrintArea');
  if (!ticketElement) return;

  const printContent = ticketElement.innerHTML;

  const printWindow = window.open('', '_blank', 'width=800,height=600');
  if (!printWindow) return;

  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Print Ticket</title>
      <style>
        @page {
          size: auto;
          margin: 10mm;
        }
        body {
          font-family: Arial, sans-serif;
          margin: 0;
          padding: 20px;
          color: #333;
        }
        .ticket-page {
          max-width: 600px;
          margin: 0 auto;
          padding: 20px;
        }
        .ticket-header {
          display: flex;
          align-items: center;
          gap: 14px;
          margin-bottom: 18px;
        }
        .logo-pill img {
          max-height: 40px;
        }
        .title-block h3 {
          margin: 0;
          font-size: 22px;
          font-weight: 700;
        }
        .title-block span {
          font-size: 13px;
          color: #6b7280;
        }
        .event-grid {
          display: grid;
          grid-template-columns: 1.3fr 1.3fr;
          gap: 18px;
          font-size: 13px;
        }
        .field-label {
          font-size: 12px;
          color: #6b7280;
          margin-bottom: 2px;
        }
        .field-value {
          font-size: 13px;
          font-weight: 600;
        }
        .divider {
          margin: 20px 0 18px;
          border-bottom: 1px dashed #e5e7eb;
        }
        .two-col-info {
          display: grid;
          grid-template-columns: 1.5fr 1fr;
          gap: 18px;
          font-size: 13px;
        }
        .info-label span {
          display: block;
          margin-bottom: 6px;
          font-size: 13px;
        }
        .info-values {
          text-align: right;
          font-size: 13px;
        }
        .info-values div {
          margin-bottom: 6px;
          font-weight: 600;
        }
        .section-title {
          font-size: 14px;
          font-weight: 600;
          margin-bottom: 10px;
          display: flex;
          align-items: center;
          gap: 6px;
        }
        .ticket-card {
          border-radius: 12px;
          border: 1px solid #e5e7eb;
          padding: 12px 14px;
          display: flex;
          justify-content: space-between;
          gap: 8px;
          font-size: 13px;
        }
        .ticket-card-left {
          max-width: 70%;
        }
        .ticket-name {
          font-weight: 600;
          margin-bottom: 2px;
        }
        .ticket-meta {
          font-size: 12px;
          color: #6b7280;
          margin-bottom: 6px;
        }
        .ticket-subtitle {
          font-size: 12px;
          margin-top: 4px;
        }
        .ticket-card-right {
          text-align: right;
          font-weight: 700;
          color: #16a34a;
          font-size: 14px;
          white-space: nowrap;
        }
        .fee-row {
          display: flex;
          justify-content: space-between;
          font-size: 13px;
          margin-top: 6px;
        }
        .fee-row.total {
          margin-top: 10px;
          font-weight: 700;
        }
        .status-row {
          margin-top: 8px;
          font-size: 13px;
        }
        .status-label {
          font-weight: 600;
        }
        .status-value {
          color: #16a34a;
          font-weight: 600;
          margin-left: 4px;
        }
      </style>
    </head>
    <body>
      ${printContent}
      <script>
        window.onload = function() {
          window.print();
          setTimeout(function() {
            window.close();
          }, 100);
        };
      <\/script>
    </body>
    </html>
  `);

  printWindow.document.close();
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.modal-box {
  background: #fff;
  border-radius: 18px;
  padding: 18px 18px 14px;
  max-width: 720px;
  width: 100%;
  box-shadow: 0 20px 55px rgba(15, 23, 42, 0.4);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.modal-header h2 {
  font-size: 16px;
  margin: 0;
}

.close-btn {
  border: none;
  background: transparent;
  font-size: 20px;
  cursor: pointer;
}

.print-actions {
  margin-top: 12px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.btn {
  padding: 7px 12px;
  border-radius: 999px;
  border: 1px solid #e5e7eb;
  font-size: 12px;
  cursor: pointer;
  background: #fff;
}

.btn-primary {
  background: #007aff;
  color: #fff;
  border-color: #007aff;
}

/* Ticket layout */
.ticket-page {
  border-radius: 16px;
  border: 1px solid #e5e7eb;
  padding: 24px 26px 24px;
  background: #fff;
}

.ticket-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 18px;
}

.logo-pill {
  width: 70px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: 18px;
}

.title-block h3 {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
}

.title-block span {
  font-size: 13px;
  color: #6b7280;
}

.event-title {
  text-align: center;
  font-size: 22px;
  font-weight: 700;
  margin-bottom: 18px;
}

.event-grid {
  display: grid;
  grid-template-columns: 1.3fr 1.3fr;
  gap: 18px;
  font-size: 13px;
}

.field-label {
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 2px;
}

.field-value {
  font-size: 13px;
  font-weight: 600;
}

.divider {
  margin: 20px 0 18px;
  border-bottom: 1px dashed #e5e7eb;
}

.two-col-info {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 18px;
  font-size: 13px;
}

.info-label span {
  display: block;
  margin-bottom: 6px;
  font-size: 13px;
}

.info-values {
  text-align: right;
  font-size: 13px;
}

.info-values div {
  margin-bottom: 6px;
  font-weight: 600;
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.ticket-card {
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  padding: 12px 14px;
  display: flex;
  justify-content: space-between;
  gap: 8px;
  font-size: 13px;
}

.ticket-card-left {
  max-width: 70%;
}

.ticket-name {
  font-weight: 600;
  margin-bottom: 2px;
}

.ticket-meta {
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 6px;
}

.ticket-subtitle {
  font-size: 12px;
  margin-top: 4px;
}

.ticket-card-right {
  text-align: right;
  font-weight: 700;
  color: #16a34a;
  font-size: 14px;
  white-space: nowrap;
}

.fee-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  margin-top: 6px;
}

.fee-row.total {
  margin-top: 10px;
  font-weight: 700;
}

.status-row {
  margin-top: 8px;
  font-size: 13px;
}

.status-label {
  font-weight: 600;
}

.status-value {
  color: #16a34a;
  font-weight: 600;
  margin-left: 4px;
}

/* PRINT – ticket only */
@media print {
  body {
    background: #fff;
    padding: 0;
  }

  .modal-backdrop {
    position: static;
    background: #fff;
    box-shadow: none;
    align-items: flex-start;
  }

  .modal-box {
    box-shadow: none;
    border-radius: 0;
    max-width: none;
    padding: 0;
  }

  .print-actions,
  .modal-header {
    display: none !important;
  }

  .ticket-page {
    border: none;
    border-radius: 0;
    padding: 24px 26px;
  }
}
</style>
