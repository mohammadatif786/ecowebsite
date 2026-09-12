export const DRINK_ADDON_GROUPS = [
  { key: 'mixDrinks', label: 'Mix Drinks', prefix: 'mixdrink' },
  { key: 'wines', label: 'Wines', prefix: 'wine' },
  { key: 'waters', label: 'Waters', prefix: 'water' },
  { key: 'beers', label: 'Beers', prefix: 'beer' },
  { key: 'softDrinks', label: 'Soft Drinks', prefix: 'softdrink' },
  { key: 'bottles', label: 'Bottles', prefix: 'bottles' },
];

const toNumber = (value, fallback = 0) => {
  const n = Number(value);
  return Number.isFinite(n) ? n : fallback;
};

const normalizeName = (name) => String(name ?? '').trim();

export const getDrinkAvailable = (drink) => {
  return Math.max(0, toNumber(drink?.qty ?? drink?.quantity ?? drink?.available_qty ?? 0));
};

export const getDrinkPrice = (drink) => {
  return Math.max(0, toNumber(drink?.cost ?? drink?.price ?? 0));
};

export const getDrinkAddonGroups = (ticket) => {
  if (!ticket?.drink_addons?.enabled || !ticket.drink_addons?.items) return [];

  return DRINK_ADDON_GROUPS.map((group) => ({
    ...group,
    drinks: Array.isArray(ticket.drink_addons.items[group.key])
      ? ticket.drink_addons.items[group.key]
      : [],
  })).filter((group) => group.drinks.length > 0);
};

export const hasDrinkAddons = (ticket) => getDrinkAddonGroups(ticket).length > 0;

export const buildDrinkSku = (prefix, drinkName, ticketId) => {
  return `${prefix}_${normalizeName(drinkName)}_${ticketId}`;
};

export const parseDrinkSku = (sku) => {
  const value = String(sku || '');
  const firstUnderscore = value.indexOf('_');
  const lastUnderscore = value.lastIndexOf('_');

  if (firstUnderscore <= 0 || lastUnderscore <= firstUnderscore) return null;

  const prefix = value.slice(0, firstUnderscore);
  const name = value.slice(firstUnderscore + 1, lastUnderscore);
  const ticketId = Number(value.slice(lastUnderscore + 1));
  const group = DRINK_ADDON_GROUPS.find((item) => item.prefix === prefix);

  if (!group || !Number.isFinite(ticketId)) return null;

  return { prefix, name, ticketId, groupKey: group.key, groupLabel: group.label };
};

export const findDrinkForSku = (tickets, sku) => {
  const parsed = parseDrinkSku(sku);
  if (!parsed) return null;

  const ticket = (tickets || []).find((item) => Number(item.id) === parsed.ticketId);
  const group = DRINK_ADDON_GROUPS.find((item) => item.key === parsed.groupKey);
  const drinks = ticket?.drink_addons?.items?.[parsed.groupKey];

  if (!ticket || !group || !Array.isArray(drinks)) return null;

  const drink = drinks.find((item) => normalizeName(item?.name) === normalizeName(parsed.name));
  if (!drink) return null;

  return { ticket, group, drink, parsed };
};

export const addonIdForDrink = (ticket, groupKey, drink) => {
  if (drink?.id) return drink.id;

  let idCounter = 1;
  const items = ticket?.drink_addons?.items || {};

  for (const group of DRINK_ADDON_GROUPS) {
    const drinks = Array.isArray(items[group.key]) ? items[group.key] : [];
    for (const item of drinks) {
      if (group.key === groupKey && normalizeName(item?.name) === normalizeName(drink?.name)) {
        return idCounter;
      }
      idCounter += 1;
    }
  }

  return null;
};
