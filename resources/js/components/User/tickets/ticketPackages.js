const toNumber = (value, fallback = 0) => {
  const n = Number(value);
  return Number.isFinite(n) ? n : fallback;
};

export const isEnabledValue = (value) => value === true || value === 'yes' || value === 1 || value === '1';

export const getTicketPackage = (ticket) => ticket?.drink_package || ticket?.drinkPackage || null;

export const isPackageTicket = (ticket) => {
  return Boolean(ticket?.package_id) && isEnabledValue(ticket?.has_table) && Boolean(getTicketPackage(ticket));
};

export const getPackagePrice = (ticket) => {
  const tablePrice = toNumber(ticket?.table_price ?? 0);
  const ticketPrice = toNumber(ticket?.price ?? 0) - toNumber(ticket?.promo_price ?? 0);

  if (tablePrice > 0) return tablePrice;
  return Math.max(0, ticketPrice);
};

export const getPackageGroups = (ticket) => {
  const pkg = getTicketPackage(ticket);
  if (!pkg) return [];

  return [
    { key: 'bottles', label: 'Bottles', items: Array.isArray(pkg.bottles) ? pkg.bottles : [] },
    { key: 'chasers', label: 'Chasers', items: Array.isArray(pkg.chasers) ? pkg.chasers : [] },
    { key: 'waters', label: 'Waters', items: Array.isArray(pkg.waters) ? pkg.waters : [] },
  ].filter((group) => group.items.length > 0);
};

export const getTicketSections = (ticket) => {
  return Array.isArray(ticket?.sections) ? ticket.sections : [];
};
