/**
 * The U Vibe post categories (Events, Parties, Campus Life, Clubs, Food, Gigs).
 *
 * The names come from the server's options (`composeOptions.categories`, the same list the app
 * gets), so a new category shows up here without touching the page; the icons are the design's.
 */
import { Briefcase, Building2, Calendar, Grid3x3, PartyPopper, Tag, Users, Utensils } from 'lucide-vue-next';

const ICONS = {
    all: Grid3x3,
    events: Calendar,
    parties: PartyPopper,
    'campus-life': Building2,
    clubs: Users,
    food: Utensils,
    opportunity: Briefcase,
};

// Used until the server's list arrives (and if it never does).
const DEFAULTS = [
    { id: 'events', label: 'Events' },
    { id: 'parties', label: 'Parties' },
    { id: 'campus-life', label: 'Campus Life' },
    { id: 'clubs', label: 'Clubs' },
    { id: 'food', label: 'Food' },
    { id: 'opportunity', label: 'Gigs' },
];

const withIcon = (c) => ({ id: c.id, label: c.label, icon: ICONS[c.id] || Tag });

/** The categories a post can have: [{id, label, icon}] (no "All"). */
export const postCategories = (options) =>
    (options?.categories?.length ? options.categories : DEFAULTS).map(withIcon);

/** The header tabs: "All" first, then every category. */
export const categoryTabs = (options) => [withIcon({ id: 'all', label: 'All' }), ...postCategories(options)];
