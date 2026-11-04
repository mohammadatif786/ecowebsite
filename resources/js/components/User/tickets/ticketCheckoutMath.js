const emptyFees = () => ({
  taxRatePercent: 0,
  vipPackageFeePct: 0,
  bottleSubtotal: 0,
  bottleFee: 0,
  serviceFeePercent: 0,
  serviceFeeFixed: 0,
  processingFeePercent: 0,
  processingFeeFixed: 0,
  drinkSubtotal: 0,
  drinkFees: 0,
  totalFees: 0,
  mobileFee: 0,
});

const toNumber = (value, fallback = 0) => {
  const n = Number(value);
  return Number.isFinite(n) ? n : fallback;
};

const isEnabledValue = (value) => value === true || value === 'yes' || value === 1 || value === '1';

export const getTicketRemaining = (ticket) => {
  return Math.max(0, toNumber(ticket?.quantity ?? ticket?.qty_available ?? 0));
};

export const getDiscountedTicketPrice = (ticket) => {
  const base = toNumber(ticket?.price ?? 0);
  const promo = toNumber(ticket?.promo_price ?? 0);
  const discounted = base - promo;
  return discounted > 0 ? discounted : 0;
};

export const getItemType = (sku) => {
  if (sku.startsWith('tbl_')) return 'table';
  if (
    sku.startsWith('mixdrink_') ||
    sku.startsWith('wine_') ||
    sku.startsWith('beer_') ||
    sku.startsWith('water_') ||
    sku.startsWith('softdrink_')
  ) return 'drink';
  if (sku.startsWith('bottles_')) return 'bottles';
  return 'ticket';
};

export const getTicketForSku = (tickets, sku) => {
  return tickets.find((ticket) => {
    const id = String(ticket.id);
    return sku === id ||
      sku === `tbl_${id}` ||
      sku.startsWith(`tbl_${id}_`) ||
      sku.endsWith(`_${id}`);
  }) || null;
};

export const calculateDrinkFees = (basePrice, drinkType, eventFeeSettings) => {
  if (!eventFeeSettings) return { drinkFee: 0, bottleFee: 0, totalFees: 0 };

  const drinkFeePct = toNumber(eventFeeSettings.drink_fee_pct ?? 0);
  const bottleFeePct = toNumber(eventFeeSettings.bottle_fee_pct ?? 0);
  const isDrinkType = ['wine', 'beer', 'water', 'mixdrink', 'softdrink'].includes(drinkType);
  const isBottleType = drinkType === 'bottles';
  const drinkFee = isDrinkType ? basePrice * (drinkFeePct / 100) : 0;
  const bottleFee = isBottleType ? basePrice * (bottleFeePct / 100) : 0;

  return { drinkFee, bottleFee, totalFees: drinkFee + bottleFee };
};

export const packageTablesSubtotal = (cartItems, tickets) => {
  let total = 0;

  Object.entries(cartItems).forEach(([sku, item]) => {
    const ticketId = parseInt(sku, 10);
    if (Number.isNaN(ticketId)) return;

    const ticket = tickets.find((t) => t.id === ticketId);
    if (!ticket || !ticket.package_id || !isEnabledValue(ticket.has_table)) return;

    const tablePrice = toNumber(ticket.table_price ?? 0);
    const ticketPrice = getDiscountedTicketPrice(ticket);
    if (tablePrice > 0 && tablePrice !== ticketPrice) {
      total += tablePrice * toNumber(item.quantity ?? 0);
    }
  });

  return total;
};

export const sumCartWithTableDedup = (cartItems) => {
  const baseMap = new Map();

  Object.entries(cartItems).forEach(([sku, item]) => {
    const id = parseInt(sku, 10);
    if (!Number.isNaN(id)) {
      baseMap.set(id, { price: toNumber(item.price ?? 0) });
    }
  });

  let sum = 0;
  Object.entries(cartItems).forEach(([sku, item]) => {
    if (sku.startsWith('tbl_')) {
      const parts = sku.split('_');
      const id = parseInt(parts[1], 10);
      const base = baseMap.get(id);
      if (base && toNumber(base.price) === toNumber(item.price)) return;
    }
    sum += toNumber(item.price ?? 0) * toNumber(item.quantity ?? 0);
  });

  return sum;
};

const getCountryTaxRules = (event, taxRules) => {
  const eventCountry = String(event?.country || '').toUpperCase().trim();

  if (eventCountry === 'BAHAMAS' || eventCountry === 'BS') {
    return {
      country: 'BAHAMAS',
      taxRate: 0.10,
      taxTicket: true,
      taxFees: true,
      taxMobileFees: true,
      taxPlatformFees: true,
      taxDrinkFees: true,
      taxBottleFees: true,
    };
  }

  if (eventCountry === 'USA' || eventCountry === 'US' || eventCountry === 'UNITED STATES') {
    const defaultRules = {
      country: 'USA',
      taxTicket: true,
      taxFees: false,
      taxMobileFees: true,
      taxPlatformFees: true,
      taxDrinkFees: false,
      taxBottleFees: false,
      no_state_sales_tax: false,
      allow_local_sales_tax: false,
    };

    if (!taxRules?.US || !event?.state) return defaultRules;

    const stateRules = taxRules.US[String(event.state).toUpperCase()];
    if (!stateRules) return defaultRules;

    if (stateRules.no_state_sales_tax && stateRules.allow_local_sales_tax) {
      const localTaxRate = toNumber(event.tax_rate ?? 0);
      return {
        ...defaultRules,
        taxTicket: localTaxRate > 0,
        taxFees: stateRules.tax_fees ?? false,
        no_state_sales_tax: false,
        allow_local_sales_tax: true,
        local_tax_rate: localTaxRate,
      };
    }

    return {
      ...defaultRules,
      taxTicket: stateRules.tax_ticket ?? true,
      taxFees: stateRules.tax_fees ?? false,
      no_state_sales_tax: stateRules.no_state_sales_tax ?? false,
      allow_local_sales_tax: stateRules.allow_local_sales_tax ?? false,
    };
  }

  return {
    country: 'DEFAULT',
    taxTicket: true,
    taxFees: true,
    taxMobileFees: true,
    taxPlatformFees: true,
    taxDrinkFees: true,
    taxBottleFees: true,
  };
};

export const calculateOrderFees = ({
  event,
  tickets,
  cartItems,
  eventFeeSettings,
  taxRules,
  wellnessSelections = {},
  cookoutTicketIds = new Set(),
}) => {
  if (!eventFeeSettings) return emptyFees();

  const hasPaidTickets = Object.entries(cartItems).some(([sku]) => {
    if (getItemType(sku) !== 'ticket') return false;
    const ticket = tickets.find((t) => String(t.id) === sku);
    return ticket && ticket.is_free !== 'yes' && getDiscountedTicketPrice(ticket) > 0;
  });

  if (!hasPaidTickets) return emptyFees();

  let drinkSubtotal = 0;
  let bottleSubtotal = 0;
  let drinkFees = 0;
  let bottleFee = 0;
  let mobileFee = 0;
  let vipTicketSubtotal = 0;
  let standardTicketSubtotal = 0;

  Object.entries(cartItems).forEach(([sku, item]) => {
    const itemType = getItemType(sku);
    const itemTotal = toNumber(item.price ?? 0) * toNumber(item.quantity ?? 0);

    if (itemType === 'drink') {
      drinkSubtotal += itemTotal;
      const feeCalc = calculateDrinkFees(toNumber(item.price ?? 0), sku.split('_')[0], eventFeeSettings);
      drinkFees += feeCalc.totalFees * toNumber(item.quantity ?? 0);
      return;
    }

    if (itemType === 'bottles') {
      bottleSubtotal += itemTotal;
      const feeCalc = calculateDrinkFees(toNumber(item.price ?? 0), sku.split('_')[0], eventFeeSettings);
      bottleFee += feeCalc.totalFees * toNumber(item.quantity ?? 0);
      return;
    }

    if (itemType !== 'ticket' || toNumber(item.quantity ?? 0) <= 0) return;

    const ticket = tickets.find((t) => String(t.id) === sku);
    if (!ticket) return;

    const selection = wellnessSelections[ticket.id] || {};
    if (ticket?.wellness?.booking?.mobileFee && selection.serviceMode === 'mobile') {
      mobileFee += toNumber(ticket.wellness.booking.mobileFee) * toNumber(item.quantity ?? 0);
    }

    const hasTable = isEnabledValue(ticket.has_table);
    const isPackageTableTicket = hasTable && Boolean(ticket.package_id);
    const isTableSelected = hasTable && (
      isPackageTableTicket ||
      Object.entries(cartItems).some(([tableSku, tableItem]) => {
        return tableSku.startsWith(`tbl_${ticket.id}_`) && toNumber(tableItem?.quantity ?? 0) > 0;
      })
    );

    const vipPct = toNumber(eventFeeSettings.vip_fee_pct ?? 0) / 100;
    if (isTableSelected && vipPct > 0 && !cookoutTicketIds.has(ticket.id)) {
      const tablePrice = toNumber(ticket.table_price ?? 0);
      vipTicketSubtotal += tablePrice > 0 && tablePrice !== toNumber(item.price ?? 0)
        ? tablePrice * toNumber(item.quantity ?? 0)
        : itemTotal;
      return;
    }

    standardTicketSubtotal += itemTotal;
  });

  let serviceFeePercent = 0;
  let serviceFeeFixed = 0;
  let processingFeePercent = 0;
  let processingFeeFixed = 0;
  let vipPackageFeePct = 0;
  let taxRatePercent = 0;

  if (standardTicketSubtotal > 0) {
    serviceFeePercent = standardTicketSubtotal * (toNumber(eventFeeSettings.service_fee_pct ?? 0) / 100);
    serviceFeeFixed = toNumber(eventFeeSettings.service_fee_fixed ?? 0);
    processingFeePercent = standardTicketSubtotal * (toNumber(eventFeeSettings.processing_fee_pct ?? 0) / 100);
    processingFeeFixed = toNumber(eventFeeSettings.processing_fee_fixed ?? 0);
  }

  if (vipTicketSubtotal > 0) {
    vipPackageFeePct = vipTicketSubtotal * (toNumber(eventFeeSettings.vip_fee_pct ?? 0) / 100);
  }

  const countryTaxRules = getCountryTaxRules(event, taxRules);
  let taxRate = 0;
  let taxBase = 0;

  if (countryTaxRules.country === 'BAHAMAS') {
    taxRate = countryTaxRules.taxRate;
    taxBase = standardTicketSubtotal + vipTicketSubtotal +
      serviceFeePercent + serviceFeeFixed + processingFeePercent + processingFeeFixed +
      mobileFee + drinkFees + bottleFee + vipPackageFeePct;
    taxRatePercent = taxBase * taxRate;
  } else if (countryTaxRules.country === 'USA') {
    taxRate = toNumber(event?.event_details?.tax_rate ?? 0);

    if (countryTaxRules.no_state_sales_tax && countryTaxRules.allow_local_sales_tax && countryTaxRules.local_tax_rate) {
      taxRate = countryTaxRules.local_tax_rate;
    } else if (countryTaxRules.no_state_sales_tax) {
      return emptyFees();
    }

    if (taxRate === 0 && countryTaxRules.rate) taxRate = countryTaxRules.rate;

    const taxIncluded = event?.event_details?.tax_included === 'yes';
    if (taxIncluded) {
      taxBase = standardTicketSubtotal + vipTicketSubtotal;
      if (countryTaxRules.taxFees) taxBase += serviceFeePercent + serviceFeeFixed + processingFeePercent + processingFeeFixed;
      if (countryTaxRules.taxMobileFees) taxBase += mobileFee;
      if (countryTaxRules.taxPlatformFees) taxBase += vipPackageFeePct;
      if (countryTaxRules.taxDrinkFees) taxBase += drinkFees;
      if (countryTaxRules.taxBottleFees) taxBase += bottleFee;
      const multiplier = taxRate > 1 ? taxRate / 100 : taxRate;
      taxRatePercent = taxBase * multiplier;
    }
  } else {
    taxRate = toNumber(event?.event_details?.tax_rate ?? 0.10);
    const taxIncluded = event?.event_details?.tax_included !== 'no';
    if (taxIncluded) {
      taxBase = standardTicketSubtotal + vipTicketSubtotal +
        serviceFeePercent + serviceFeeFixed + processingFeePercent + processingFeeFixed +
        mobileFee + drinkFees + bottleFee + vipPackageFeePct;
      const multiplier = taxRate > 1 ? taxRate / 100 : taxRate;
      taxRatePercent = taxBase * multiplier;
    }
  }

  const standardFeesTotal = serviceFeePercent + serviceFeeFixed + processingFeePercent + processingFeeFixed + taxRatePercent;

  return {
    taxRatePercent,
    serviceFeePercent,
    serviceFeeFixed,
    processingFeePercent,
    processingFeeFixed,
    drinkSubtotal,
    drinkFees,
    bottleFee,
    bottleSubtotal,
    vipPackageFeePct,
    mobileFee,
    totalFees: standardFeesTotal + drinkFees + bottleFee + vipPackageFeePct + mobileFee,
  };
};

export const calculateCheckoutTotals = ({
  event,
  tickets,
  cartItems,
  eventFeeSettings,
  taxRules,
  wellnessSelections,
  cookoutTicketIds,
  discount = 0,
  cookoutAddonsTotal = 0,
  wellnessAddonsTotal = 0,
}) => {
  const packageTables = packageTablesSubtotal(cartItems, tickets);
  const baseTotal = sumCartWithTableDedup(cartItems);
  const subtotal = baseTotal + packageTables + cookoutAddonsTotal + wellnessAddonsTotal;
  const fees = calculateOrderFees({
    event,
    tickets,
    cartItems,
    eventFeeSettings,
    taxRules,
    wellnessSelections,
    cookoutTicketIds,
  });
  const total = baseTotal - discount + fees.totalFees + packageTables + cookoutAddonsTotal + wellnessAddonsTotal;
  const servicesSubtotal = subtotal - fees.drinkSubtotal - fees.bottleSubtotal;

  return {
    subtotal,
    servicesSubtotal,
    total: Math.max(total, 0),
    fees,
  };
};
