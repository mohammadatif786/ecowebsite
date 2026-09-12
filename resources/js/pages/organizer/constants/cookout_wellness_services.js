
export const COOKOUT_PROTEINS = [
    { name: "Fried Fish", defaultChecked: true, defaultQty: 1, defaultMode: "included", defaultPrice: 0 },
    { name: "BBQ Chicken", defaultChecked: false, defaultQty: 0, defaultMode: "addon", defaultPrice: 8 },
    { name: "Curry Chicken", defaultChecked: false, defaultQty: 0, defaultMode: "addon", defaultPrice: 8 },
    { name: "Jerk Chicken", defaultChecked: false, defaultQty: 0, defaultMode: "addon", defaultPrice: 8 },
    { name: "Oxtail", defaultChecked: false, defaultQty: 0, defaultMode: "addon", defaultPrice: 12 },
    { name: "Curry Goat", defaultChecked: false, defaultQty: 0, defaultMode: "addon", defaultPrice: 10 },
    { name: "Mutton", defaultChecked: false, defaultQty: 0, defaultMode: "addon", defaultPrice: 10 },
    { name: "Steak", defaultChecked: false, defaultQty: 0, defaultMode: "addon", defaultPrice: 12 },
    { name: "Brown Stew Fish", defaultChecked: false, defaultQty: 0, defaultMode: "addon", defaultPrice: 6 },
    { name: "Ribs", defaultChecked: false, defaultQty: 0, defaultMode: "addon", defaultPrice: 10 },
    { name: "Sausage", defaultChecked: false, defaultQty: 0, defaultMode: "addon", defaultPrice: 5 },
    { name: "Pulled Pork", defaultChecked: false, defaultQty: 0, defaultMode: "addon", defaultPrice: 8 },
    { name: "Brazilian Picanha", defaultChecked: false, defaultQty: 0, defaultMode: "addon", defaultPrice: 12 },
    { name: "Feijoada (Brazil)", defaultChecked: false, defaultQty: 0, defaultMode: "addon", defaultPrice: 10 },
    { name: "Veg Option (Callaloo / Veg Plate)", defaultChecked: false, defaultQty: 0, defaultMode: "included", defaultPrice: 0 },
];

export const COOKOUT_SIDES = [
    { name: "Rice", defaultChecked: true },
    { name: "Macaroni (Mac Pie)", defaultChecked: true },
    { name: "Potato Salad", defaultChecked: true },
    { name: "Coleslaw", defaultChecked: true },
    { name: "Rice & Peas", defaultChecked: false },
    { name: "Pelau (Trinidad)", defaultChecked: false },
    { name: "Fried Plantain", defaultChecked: false },
    { name: "Festival", defaultChecked: false },
    { name: "Callaloo", defaultChecked: false },
    { name: "Cou-Cou (Barbados)", defaultChecked: false },
    { name: "Johnny Cake", defaultChecked: false },
    { name: "Conch Salad (Bahamas)", defaultChecked: false },
];

export const COOKOUT_DRINKS = [
    { name: "Water", defaultChecked: true, defaultMode: "addon", defaultPrice: 2 },
    { name: "Soft Drinks", defaultChecked: true, defaultMode: "addon", defaultPrice: 3 },
    { name: "Juice", defaultChecked: false, defaultMode: "addon", defaultPrice: 4 },
    { name: "Sorrel", defaultChecked: false, defaultMode: "addon", defaultPrice: 5 },
    { name: "Mauby", defaultChecked: false, defaultMode: "addon", defaultPrice: 5 },
    { name: "Coconut Water", defaultChecked: false, defaultMode: "addon", defaultPrice: 6 },
    { name: "Alcohol (if allowed)", defaultChecked: false, defaultMode: "addon", defaultPrice: 10 },
];

export const WELLNESS_SERVICES = [
    { name: "Swedish Massage", type: "massage", defaultChecked: true, defaultMode: "included", defaultPrice: 0, duration: 60 },
    { name: "Deep Tissue Upgrade", type: "massage", defaultChecked: false, defaultMode: "addon", defaultPrice: 20, duration: 60 },
    { name: "Hot Stone Upgrade", type: "massage", defaultChecked: false, defaultMode: "addon", defaultPrice: 25, duration: 60 },
    { name: "Classic Facial", type: "facials", defaultChecked: false, defaultMode: "addon", defaultPrice: 30, duration: 45 },
    { name: "Manicure", type: "nails", defaultChecked: false, defaultMode: "addon", defaultPrice: 15, duration: 30 },
    { name: "Pedicure", type: "nails", defaultChecked: false, defaultMode: "addon", defaultPrice: 20, duration: 45 },
    { name: "Lymphatic Drainage Massage", type: "massage", defaultChecked: false, defaultMode: "addon", defaultPrice: 0, duration: 30 },
    { name: "Sports Massage", type: "massage", defaultChecked: false, defaultMode: "addon", defaultPrice: 0, duration: 30 },
    { name: "Prenatal Massage", type: "massage", defaultChecked: false, defaultMode: "addon", defaultPrice: 0, duration: 30 },
    { name: "Scalp Massage / Indian Head Massage", type: "massage", defaultChecked: false, defaultMode: "addon", defaultPrice: 0, duration: 30 },
    { name: "Chair Massage (seated, shorter sessions)", type: "massage", defaultChecked: false, defaultMode: "addon", defaultPrice: 0, duration: 30 },
    { name: "Foot Massage", type: "massage", defaultChecked: false, defaultMode: "addon", defaultPrice: 0, duration: 30 },
];

export const DRINK_ADDON_CATS = [
    ['mixDrinks', 'Mix Drinks', '🍹', ['Rum Punch', 'Painkiller', 'Dark & Stormy', 'Mojito', 'Piña Colada', 'Daiquiri', 'Margarita', 'Sorrel Punch', 'Sex on The Beach', 'Martini']],
    ['wines', 'Wines', '🍷', ['Red Wine', 'White Wine', 'Rosé', 'Sparkling Wine', 'Sangria']],
    ['bottles', 'Bottles', '🍾', ["Tito's", 'Hennessy', 'Grey Goose', 'Bacardi', 'Johnnie Walker', 'Patrón', 'Moët', 'Absolut']],
    ['waters', 'Waters', '💧', ['Still Water', 'Sparkling Water', 'Coconut Water']],
    ['beers', 'Beers', '🍺', ['Kalik', 'Sands', 'Red Stripe', 'Carib', 'Blue Wave']],
    ['softDrinks', 'Soft Drinks', '🥤', ['Coke', 'Sprite', 'Ginger Beer', 'Lemonade', 'Juice', 'Sorrel']]
];

export const DRINK_ADDON_PRICES = {
    'Rum Punch': 8, 'Painkiller': 9, 'Dark & Stormy': 9, 'Mojito': 8, 'Piña Colada': 8, 'Daiquiri': 8, 'Margarita': 9, 'Sorrel Punch': 6, 'Sex on The Beach': 10, 'Martini': 12,
    'Red Wine': 10, 'White Wine': 10, 'Rosé': 10, 'Sparkling Wine': 12, 'Sangria': 9,
    "Tito's": 120, 'Hennessy': 180, 'Grey Goose': 150, 'Bacardi': 90, 'Johnnie Walker': 140, 'Patrón': 160, 'Moët': 130, 'Absolut': 95,
    'Still Water': 2, 'Sparkling Water': 3, 'Coconut Water': 4,
    'Kalik': 5, 'Sands': 5, 'Red Stripe': 5, 'Carib': 5, 'Blue Wave': 5,
    'Coke': 3, 'Sprite': 3, 'Ginger Beer': 3, 'Lemonade': 3, 'Juice': 4, 'Sorrel': 4
};

export const TABLE_DRINK_PACKAGES = {
    'Big Drinker Special': { bottles: [{ name: "Tito's", qty: 1 }, { name: 'Hennessy', qty: 1 }, { name: 'Grey Goose', qty: 1 }], chasers: [{ name: 'Red Bull', qty: 1 }, { name: 'Orange Juice', qty: 1 }], waters: [], notes: 'Chilled' },
    'Classic Bottle Package': { bottles: [{ name: 'Absolut', qty: 1 }, { name: 'Bacardi', qty: 1 }], chasers: [{ name: 'Sprite', qty: 2 }, { name: 'Cranberry Juice', qty: 1 }], waters: [{ name: 'Still Water', qty: 2 }], notes: '' },
    'Champagne Toast': { bottles: [{ name: 'Moët', qty: 2 }], chasers: [], waters: [{ name: 'Sparkling Water', qty: 2 }], notes: 'Served on ice' }
};

export const CUISINE_AUTOCHECK = {
    trinidad: { proteins: ['curry_chicken', 'oxtail'], sides: ['pelau', 'rice_peas'], drinks: ['sorrel'] },
    jamaica: { proteins: ['jerk_chicken', 'brown_stew_fish'], sides: ['rice_peas', 'callaloo'], drinks: ['sorrel'] },
    bahamas: { proteins: ['fried_fish'], sides: ['conch_salad', 'cou_cou'], drinks: ['coconut_water'] },
    barbados: { proteins: ['fried_fish', 'curry_chicken'], sides: ['cou_cou', 'macaroni'], drinks: ['mauby'] },
    brazil: { proteins: ['brazilian_picanha', 'feijoada'], sides: ['rice'], drinks: ['juice'] }
};

export const WELLNESS_PRESET_AUTOCHECK = {
    'Spa Day Essentials': ['swedish_massage', 'classic_facial', 'manicure'],
    'Bridal Prep': ['classic_facial', 'manicure', 'pedicure'],
    'Sports Recovery': ['sports_massage', 'deep_tissue', 'hot_stone'],
    'Prenatal Care': ['prenatal', 'foot']
};
