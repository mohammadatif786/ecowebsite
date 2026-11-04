// 24x24 stroke icon paths used by the wizard, from the "LinkUp Onboarding" design.

export const wizardIcons = {
    pin: 'M12 22s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12zM12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z',
    bag: 'M3 7h18v13H3zM8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2',
    cap: 'M22 9L12 4 2 9l10 5 10-5zM6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5',
    chat: 'M21 12a8 8 0 0 1-11.5 7.2L4 21l1.8-5.5A8 8 0 1 1 21 12z',
    people: 'M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM2 21c0-4 3-6 7-6s7 2 7 6M17 3a4 4 0 0 1 0 8M22 21c0-3-1.5-5-4-5.7',
    person: 'M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM4 21c0-4.4 3.6-7 8-7s8 2.6 8 7',
    heart: 'M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21.2l8.8-8.8a5.5 5.5 0 0 0 0-7.8z',
    smile: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01',
    ticket: 'M3 9a2 2 0 0 0 0 6v3a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3a2 2 0 0 0 0-6V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2zM13 4v16',
    eats: 'M3 2v7a3 3 0 0 0 6 0V2M6 2v20M21 15V2a5 5 0 0 0-5 5v6a2 2 0 0 0 2 2h3zM18 15v7',
    spark: 'M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9zM19 16l.8 2.2L22 19l-2.2.8L19 22l-.8-2.2L16 19l2.2-.8z',
    live: 'M12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM16.2 7.8a6 6 0 0 1 0 8.4M7.8 16.2a6 6 0 0 1 0-8.4M19 5a10 10 0 0 1 0 14M5 19A10 10 0 0 1 5 5',
    coin: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM15 9.5c-.5-1-1.6-1.5-3-1.5-1.7 0-3 .9-3 2s1.3 1.7 3 2 3 .9 3 2-1.3 2-3 2c-1.4 0-2.5-.5-3-1.5M12 6v2M12 16v2',
    shop: 'M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18M16 10a4 4 0 0 1-8 0',
    tag: 'M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8zM7 7h.01',
    check: 'M5 12.5l4.5 4.5L19 7.5',
    back: 'M15 18l-6-6 6-6',
    arrow: 'M5 12h14M13 6l6 6-6 6',
} as const;

export type WizardIconKey = keyof typeof wizardIcons;

/** Icons for the "What are you into?" tiles, by interest name. */
export const interestIcons: Record<string, string> = {
    'Music Festivals': 'M9 18V5l12-2v13M9 18a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM21 16a3 3 0 1 1-6 0 3 3 0 0 1 6 0z',
    'Dance Events': 'M13 4a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM5 10l4-2 4 2 3 4M9 8l-1 6 4 3v4M8 14l-3 7',
    'Cultural Gatherings': wizardIcons.people,
    Travel: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18',
    Cooking: 'M4 10h16v6a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4zM2 10h20M9 6c0-1 1-2 1-3M14 6c0-1 1-2 1-3',
    Books: 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5zM4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5',
    Yoga: 'M12 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM12 8v6M5 11l7 1 7-1M6 20l6-4 6 4',
    Movies: 'M3 9h18v11H3zM3 9l2-5h16l-2 5M8 4l-1 5M14 4l-1 5',
    Liming: wizardIcons.chat,
    Wine: 'M8 3h8l-.5 6a3.5 3.5 0 0 1-7 0zM12 13v8M8 21h8',
};

/** Icons for "Who would you like to see?". */
export const showMeIcons: Record<string, string> = {
    Men: wizardIcons.person,
    Women: wizardIcons.person,
    Everyone: wizardIcons.people,
};

/** Icons for "What brings you to LinkUp?", by intent id. */
export const intentIcons: Record<string, string> = {
    linkup: wizardIcons.smile,
    serious: wizardIcons.heart,
    friends: wizardIcons.people,
    chat: wizardIcons.chat,
    party: wizardIcons.ticket,
    eats: wizardIcons.eats,
    vibes: wizardIcons.spark,
    uvibes: wizardIcons.cap,
    live: wizardIcons.live,
    coins: wizardIcons.coin,
    market: wizardIcons.shop,
    sell: wizardIcons.tag,
};
