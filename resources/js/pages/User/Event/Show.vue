<script setup lang="ts">
import { LinkupEvent } from '@/client/models/LinkupEvent';
import EventCard from '@/components/front/EventCard.vue';
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/front/ui/carousel';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import moment from 'moment';
import { ref, onMounted, watch, computed } from 'vue';
import { toast } from "vue-sonner";
import './components/style.css';

type DrinkAddons = {
    enabled: boolean;
    items: Record<string, any>;
    total: string;
    freeOne: Record<string, any>;
};

type CookoutProteinConfig = {
    name: string;
    qty?: number;
    mode?: 'included' | 'addon';
    price?: number;
};
type CookoutDrinkConfig = {
    name: string;
    mode?: 'included' | 'addon';
    price?: number;
    qty?: number;
};
type CookoutManualAddonConfig = {
    name: string;
    price?: number;
    qty?: number;
};

type WellnessServiceConfig = {
    name: string;
    mode?: 'included' | 'addon';
    price?: number;
    duration?: number;
};

type WellnessManualAddonConfig = {
    name: string;
    price?: number;
    qty?: number;
};

type WellnessBookingConfig = {
    buffer?: number;
    duration?: number;
    maxPerSlot?: number;
    mobileFee?: number;
    mode?: string;
    policy?: string;
    slotDate?: string;
    slotStart?: string;
    slotEnd?: string;
};

type WellnessConfig = {
    includeService?: 'yes' | 'no';
    preset?: string | null;
    services?: WellnessServiceConfig[];
    manualAddons?: WellnessManualAddonConfig[];
    booking?: WellnessBookingConfig;
};
type CookoutConfig = {
    includeFood?: 'yes' | 'no';
    cuisinePreset?: string;
    proteins?: CookoutProteinConfig[];
    customProteins?: CookoutProteinConfig[];
    sides?: string[];
    drinks?: (string | CookoutDrinkConfig)[];
    manualAddons?: CookoutManualAddonConfig[];
};
type CookoutSelection = {
    includedProtein: string;
    proteins: Record<string, number>;
    manualAddons: Record<string, number>;
    drinks: Record<string, number>;
};

const extractTicketIdFromSku = (sku: string): number | null => {
    const parts = sku.split('_');
    if (sku.startsWith('tbl_') && parts.length >= 2) {
        const id = parseInt(parts[1]);
        return isNaN(id) ? null : id;
    }
    for (let i = parts.length - 1; i >= 0; i--) {
        if (/^\d+$/.test(parts[i])) {
            return parseInt(parts[i]);
        }
    }
    const id = parseInt(sku);
    return isNaN(id) ? null : id;
};

const getDrinksForTable = (ticketId: number, section: string) => {
    const lines: Array<{ nameType: string; qty: number; total: number }> = [];
    Object.entries(cartItems.value).forEach(([sku, item]) => {
        const type = getItemType(sku);
        if (type !== 'drink' && type !== 'bottles') return;
        const id = extractTicketIdFromSku(sku);
        if (id !== ticketId) return;
        const sec = (item as any).section || '-';
        if (sec !== section) return;
        const qty = Number((item as any).quantity || 0);
        if (qty <= 0) return;
        const nameType = String((item as any).type || 'Addon');
        const total = Number((item as any).price || 0) * qty;
        lines.push({ nameType, qty, total });
    });
    return lines;
};

const getTicketRemaining = (ticket: TicketData): number => {
    const rem = (ticket as any)?.quantity ?? ticket.quantity ?? 0;
    return Number(rem) || 0;
};

const getTicketTotal = (ticket: TicketData): number => {
    return Number(ticket.quantity || 0);
};

const isSaleEnded = (ticket: TicketData): boolean => {
    if (!ticket?.sale_end) return false;
    return moment(ticket.sale_end).isBefore(moment());
};

const isSoldOut = (ticket: TicketData): boolean => {
    return getAvailableQuantity(ticket.id.toString()) === 0;
};

const isSoonGone = (ticket: TicketData): boolean => {
    const total = getTicketTotal(ticket);
    const remaining = getTicketRemaining(ticket);
    if (isSaleEnded(ticket)) return false;
    if (!total || total <= 0) return false;
    if (remaining <= 0) return false;
    return remaining <= Math.ceil(total * 0.25);
};

const hasBaseTicketInCart = (ticketId: number): boolean => {
    const key = ticketId.toString();
    return (cartItems.value?.[key]?.quantity || 0) > 0;
};

const getOrderSummaryHeaderAmount = (ticketGroup: any): number | null => {
    try {
        const qty = Number(ticketGroup?.baseTicket?.quantity || 0);
        const ticketPrice = Number(ticketGroup?.baseTicket?.price || 0);
        if (ticketPrice > 0 && qty > 0) return ticketPrice * qty;
        const ticket = tickets.find((t: any) => t.id === ticketGroup.ticketId);
        const tablePrice = Number(ticket?.table_price || 0);
        if (tablePrice > 0 && qty > 0) return tablePrice * qty;
        return null;
    } catch { return null; }
};

type TicketData = {
    id: number;
    event_id: number | null;
    name: string;
    is_free: 'yes' | 'no';
    price: number | null;
    promo_price: number;
    quantity: number;
    qty_available: number;
    tickets_per_attendee: number;
    sale_start: string | null;
    sale_end: string | null;
    description: string | null;
    has_table: 'yes' | 'no';
    table_price: number;
    table_capacity: number;
    sections: string | null;
    type: string;
    ticket_type: string;
    status: 'active' | 'inactive';
    drink_addons: DrinkAddons | null;
    package_id?: number | null;
    drink_package?: {
        name: string;
        bottles?: Array<any>;
        chasers?: Array<any>;
        waters?: Array<any>;
        notes?: string;
    } | null;
    created_at: string | null;
    updated_at: string | null;
    link_up_event_id: number | null;
    sales_start: string | null;
    sales_end: string | null;
    cookout?: CookoutConfig | null;
    wellness?: WellnessConfig | null;
};

const isSponsorVideo = (path: string | null | undefined): boolean => {
    if (!path) return false;
    return /\.(mp4|webm|ogg)$/i.test(path.split('?')[0]);
};

type EventFeeSettingData = {
    id: number;
    service_fee_pct: number;
    service_fee_fixed: number;
    processing_fee_pct: number;
    processing_fee_fixed: number;
    tax_rate: string;
    drink_fee_pct: number;
    bottle_fee_pct: string;
    vip_fee_pct: number;
    mobile_fee_pct: number;
    spa_platform_fee_pct: number;
    spa_platform_fee_fixed: number;
    cookout_platform_fee_percent?: number;
    cookout_platform_fee_fixed?: number;
    cookout_default_gratuity?: number;
    cookout_enable_gratuity?: boolean;
};

const { event, otherEvents, appurl, organizerStats, tickets, attendees, eventFeeSettings, show_map, currency, isWellnessEvent: backendIsWellnessEvent, isCookoutEvent: backendIsCookoutEvent } = usePage<{
    event: LinkupEvent;
    otherEvents: LinkupEvent[];
    appurl: string;
    organizerStats: {
        organizer_id: number;
        organizer_name: string;
        organizer_avatar: string | null;
        followers_count: number;
        events_count: number;
        hosting_since: string;
        is_following: boolean;
        bio: string;
    };
    tickets: TicketData[];
    show_map: boolean;
    attendees: Array<{
        id: number;
        name: string;
        email: string;
        avatar: string | null;
        total_tickets: number;
        purchase_date: string;
        tickets: Array<{
            name: string;
            quantity: number;
        }>;
    }>;
    eventFeeSettings: EventFeeSettingData;
    currency: any;
    isWellnessEvent: boolean;
    isCookoutEvent: boolean;
}>().props;

const normalizedEventType = computed(() => String((event as any)?.type || '').trim().toLowerCase());
const backendWellnessFlag = Boolean(backendIsWellnessEvent);
const backendCookoutFlag = Boolean(backendIsCookoutEvent);
const isCookoutEventType = computed(() => backendCookoutFlag || normalizedEventType.value === 'cookout' || normalizedEventType.value.includes('cookout'));
const isWellnessEventType = computed(() => (
    backendWellnessFlag
    || normalizedEventType.value === 'wellness'
    || normalizedEventType.value.includes('wellness')
    || normalizedEventType.value.includes('spa')
));
const hideArtistsAndCoupons = computed(() => isCookoutEventType.value || isWellnessEventType.value);

const isFollowingOrganizer = ref(organizerStats?.is_following || false);
const followersCount = ref(organizerStats?.followers_count || 0);
const form = useForm({
    items: [],
    appliedCoupons: []
});
const followForm = useForm({
    organizer_id: organizerStats.organizer_id,
});

type ReviewData = {
    id: number;
    rating: number;
    review_text: string;
    reviewer_name: string;
    created_at: string;
    user_id?: number;
    can_edit: boolean;
    can_delete: boolean;
};

const reviews = ref<ReviewData[]>([]);
const averageRating = ref(0);
const reviewsCount = ref(0);
const discount = ref(0);
const appliedCouponCode = ref('');
const selectedRating = ref(0);
const hasUserReviewed = ref(false);

const reviewForm = useForm({
    rating: 0,
    review_text: '',
    reviewer_name: ''
});

const editingReviewId = ref<number | null>(null);
const editSelectedRating = ref(0);
const editReviewForm = useForm({
    rating: 0,
    review_text: '',
    reviewer_name: ''
});


const toggleFavorite = (e: any) => {
    e.preventDefault();

    form.post(route('frontend.event.toggle-favorite', event.id), {
        preserveScroll: true,
        preserveState: false,
    });
};

const toggleFollow = () => {
    if (!followForm.organizer_id) {
        alert('Organizer ID is missing');
        return;
    }

    followForm.post(route('frontend.event.toggle-follow-organizer'), {
        preserveScroll: true,
        onSuccess: () => {
            const wasFollowing = isFollowingOrganizer.value;
            isFollowingOrganizer.value = !wasFollowing;
            if (wasFollowing) {
                followersCount.value = Math.max(0, followersCount.value - 1);
            } else {
                followersCount.value = followersCount.value + 1;
            }
            toast.success(wasFollowing ? 'Unfollowed organizer' : 'Following organizer');
        },
        onError: () => {
            toast.error('Follow toggle error');
        }
    });
};

const copyLink = (e: any) => {
    e.preventDefault();
    navigator.clipboard.writeText(window.location.href);
    alert('Link Copied Successfully');
};

const loadReviews = async () => {
    try {
        const response = await fetch(route('frontend.event.reviews.index', event.id));
        const data = await response.json();

        if (data.success) {
            reviews.value = data.reviews;
            averageRating.value = data.average_rating;
            reviewsCount.value = data.reviews_count;
            hasUserReviewed.value = data.user_has_reviewed;
        }
    } catch (error) {
        console.error('Error loading reviews:', error);
    }
};

const submitReview = async () => {
    if (selectedRating.value === 0) {
        toast.error('Please select a rating');
        return;
    }

    reviewForm.rating = selectedRating.value;

    reviewForm.post(route('frontend.event.reviews.store', event.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Review submitted successfully!');
            selectedRating.value = 0;
            reviewForm.reset();
            loadReviews();
        },
        onError: (errors) => {
            console.error('Submit error:', errors);
            toast.error('Error submitting review');
        }
    });
};

const startEditReview = (review: ReviewData) => {
    editingReviewId.value = review.id;
    editSelectedRating.value = review.rating;
    editReviewForm.rating = review.rating;
    editReviewForm.review_text = review.review_text;
    editReviewForm.reviewer_name = review.reviewer_name;
};

const cancelEditReview = () => {
    editingReviewId.value = null;
    editSelectedRating.value = 0;
    editReviewForm.reset();
};

const updateReview = async (reviewId: number) => {
    if (editSelectedRating.value === 0) {
        toast.error('Please select a rating');
        return;
    }

    editReviewForm.rating = editSelectedRating.value;

    editReviewForm.put(route('frontend.event.reviews.update', [event.id, reviewId]), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Review updated successfully!');
            editingReviewId.value = null;
            editSelectedRating.value = 0;
            editReviewForm.reset();
            loadReviews();
        },
        onError: (errors) => {
            console.error('Update error:', errors);
            toast.error('Error updating review');
        }
    });
};

const deleteReview = (reviewId: number, reviewerName: string) => {
    const confirmed = confirm(`Are you sure you want to delete the review by "${reviewerName}"? This action cannot be undone.`);

    if (!confirmed) {
        return;
    }

    const deleteForm = useForm({});

    deleteForm.delete(route('frontend.event.reviews.destroy', [event.id, reviewId]), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Review deleted successfully!');
            loadReviews();
        },
        onError: (errors) => {
            console.error('Delete error:', errors);
            toast.error('Error deleting review');
        }
    });
};
const showTicketModal = ref(false);

const showPreview = ref(false);
const previewSrc = ref('');
const previewType = ref('image');
const previewAlt = ref('');

const openPreview = (src: string, type: string, alt = '') => {
    previewSrc.value = `${appurl}${src}`;
    previewType.value = type;
    previewAlt.value = alt;
    showPreview.value = true;
};

const closePreview = () => {
    showPreview.value = false;
    previewSrc.value = '';
    previewType.value = 'image';
    previewAlt.value = '';
};

const applyCoupon = () => {
    const code = (document.getElementById('coupon') as HTMLInputElement)?.value?.trim() || '';
    if (!code) {
        alert('Please enter a coupon code');
        return;
    }

    const coupon = event.coupons?.find((c: any) => c.code === code);
    if (!coupon) {
        alert('Invalid coupon code');
        discount.value = 0;
        return;
    }

    const now = new Date();
    const expiryDate = new Date(coupon.expiry_date);
    if (now > expiryDate) {
        alert('Coupon has expired');
        discount.value = 0;
        return;
    }

    if (coupon.status !== 'live') {
        alert('Coupon is not active');
        discount.value = 0;
        return;
    }

    const currentSubtotal = Object.values(cartItems.value).reduce((sum, item) => sum + (item.price * item.quantity), 0);
    if (coupon.discount_type === 'percentage') {
        discount.value = currentSubtotal * (parseFloat(coupon.discount) / 100);
    } else if (coupon.discount_type === 'amount') {
        discount.value = parseFloat(coupon.discount);
    }

    if (discount.value > currentSubtotal) {
        discount.value = currentSubtotal;
    }

    appliedCouponCode.value = code;
};

const openTicketModal = () => {
    showTicketModal.value = true;
};

const closeTicketModal = () => {
    showTicketModal.value = false;
    clearCart();
    discount.value = 0;
};

const canIncreaseQuantity = (sku: string, currentQty: number): { allowed: boolean; message?: string } | { allowed: false; message: string } => {
    const ticketData = tickets.find(ticket => {
        if (sku === ticket.id.toString()) return true;
        if (sku === `tbl_${ticket.id}`) return true;
        if (sku.startsWith(`tbl_${ticket.id}_`)) return true;
        if (sku.startsWith('mixdrink_') && sku.endsWith(`_${ticket.id}`)) return true;
        if (sku.startsWith('wine_') && sku.endsWith(`_${ticket.id}`)) return true;
        if (sku.startsWith('beer_') && sku.endsWith(`_${ticket.id}`)) return true;
        if (sku.startsWith('water_') && sku.endsWith(`_${ticket.id}`)) return true;
        if (sku.startsWith('softdrink_') && sku.endsWith(`_${ticket.id}`)) return true;
        if (sku.startsWith('bottles_') && sku.endsWith(`_${ticket.id}`)) return true;
        return false;
    });

    if (sku === ticketData.id.toString()) {
        const availableQty = ticketData?.quantity;
        if (availableQty > 0 && currentQty >= availableQty) {
            return {
                allowed: false,
                message: `You can only purchase up to ${availableQty} tickets for this ticket.`
            };
        }
        const perAttendeeLimit = Number((ticketData as any)?.tickets_per_attendee ?? 0);
        if (perAttendeeLimit > 0 && currentQty >= perAttendeeLimit) {
            return {
                allowed: false,
                message: `You can only purchase up to ${perAttendeeLimit} tickets for this ticket.`
            };
        }
    } else if (sku === `tbl_${ticketData.id}` || sku.startsWith(`tbl_${ticketData.id}_`)) {
        const maxTables = ticketData.table_capacity;
        if (maxTables > 0 && currentQty >= maxTables) {
            return {
                allowed: false,
                message: `You can only purchase up to ${maxTables} tables for this ticket.`
            };
        }
    } else if (sku.startsWith('mixdrink_') || sku.startsWith('wine_') || sku.startsWith('beer_') ||
        sku.startsWith('water_') || sku.startsWith('softdrink_') || sku.startsWith('bottles_')) {
        if (!ticketData.drink_addons?.enabled) {
            return { allowed: false, message: 'Drink addons not available' };
        }

        const drinkName = sku.split('_')[1];
        const drinkType = sku.split('_')[0].replace('drink', 'Drinks').replace('wine', 'wines')
            .replace('beer', 'beers').replace('water', 'waters')
            .replace('softdrink', 'softDrinks').replace('bottles', 'bottles');;

        const drinks = ticketData.drink_addons.items[drinkType as keyof typeof ticketData.drink_addons.items];
        const drink = Array.isArray(drinks) ? drinks.find((d: any) => d.name === drinkName) : null;

        const availableQty = Math.max(0, Number(drink.qty) || 0);
        if (currentQty >= availableQty) {
            return {
                allowed: false,
                message: `Only ${availableQty} ${drinkName} available for this ticket.`,
            };
        }
    }

    return { allowed: true };
};
const getAvailableQuantity = (sku: string): number => {
    const ticketData = tickets.find(ticket => {
        if (sku === ticket.id.toString()) return true;
        if (sku === `tbl_${ticket.id}`) return true;
        if (sku.startsWith(`tbl_${ticket.id}_`)) return true;
        if (sku.startsWith('mixdrink_') && sku.endsWith(`_${ticket.id}`)) return true;
        if (sku.startsWith('wine_') && sku.endsWith(`_${ticket.id}`)) return true;
        if (sku.startsWith('beer_') && sku.endsWith(`_${ticket.id}`)) return true;
        if (sku.startsWith('water_') && sku.endsWith(`_${ticket.id}`)) return true;
        if (sku.startsWith('softdrink_') && sku.endsWith(`_${ticket.id}`)) return true;
        if (sku.startsWith('bottles_') && sku.endsWith(`_${ticket.id}`)) return true;
        return false;
    });

    if (!ticketData) return 0;

    if (sku === ticketData.id.toString()) {
        return (ticketData as any)?.quantity ?? ticketData.quantity ?? 0;
    } else if (sku === `tbl_${ticketData.id}` || sku.startsWith(`tbl_${ticketData.id}_`)) {
        return ticketData.table_capacity || 0;
    } else if (sku.startsWith('mixdrink_') || sku.startsWith('wine_') || sku.startsWith('beer_') ||
        sku.startsWith('water_') || sku.startsWith('softdrink_') || sku.startsWith('bottles_')) {
        if (!ticketData.drink_addons?.enabled) return 0;

        const drinkName = sku.split('_')[1];
        const drinkType = sku.split('_')[0].replace('drink', 'Drinks').replace('wine', 'wines')
            .replace('beer', 'beers').replace('water', 'waters')
            .replace('softdrink', 'softDrinks').replace('bottles', 'bottles');

        const drinks = ticketData.drink_addons.items[drinkType as keyof typeof ticketData.drink_addons.items];
        const drink = Array.isArray(drinks) ? drinks.find((d: any) => d.name === drinkName) : null;

        return drink ? drink.qty : 0;
    }

    return 0;
};

const getDrinkPrice = (sku: string, ticketData: TicketData): number => {
    if (!ticketData.drink_addons?.enabled) return 0;

    const drinkName = sku.split('_')[1];
    const drinkType = sku.split('_')[0].replace('drink', 'Drinks').replace('wine', 'wines')
        .replace('beer', 'beers').replace('water', 'waters')
        .replace('softdrink', 'softDrinks').replace('bottles', 'bottles');

    const drinks = ticketData.drink_addons.items[drinkType as keyof typeof ticketData.drink_addons.items];
    const drink = Array.isArray(drinks) ? drinks.find((d: any) => d.name === drinkName) : null;

    return drink ? parseFloat(drink.cost) || 0 : 0;
};

const getItemType = (sku: string): 'ticket' | 'table' | 'drink' | 'bottles' => {
    if (sku.startsWith('tbl_')) {
        return 'table';
    } else if (sku.startsWith('mixdrink_') || sku.startsWith('wine_') || sku.startsWith('beer_') ||
        sku.startsWith('water_') || sku.startsWith('softdrink_')) {
        return 'drink';
    } else if (sku.startsWith('bottles_')) {
        return 'bottles';
    } else {
        return 'ticket';
    }
};

const buildIncludedSku = (prefix: string, name: string, ticketId: number, section?: string) => {
    const sec = (section || '-').toString().replace(/\s+/g, '-');
    return `${prefix}_${name}_${ticketId}_${sec}`;
};
const includedPrefixFor = (group: 'main_bottles' | 'chasers_or_mixers' | 'water_options') => {
    if (group === 'main_bottles') return 'bottles';
    if (group === 'water_options') return 'water';
    return 'mixdrink';
};
const getIncludedQtyInCart = (ticketId: number, group: 'main_bottles' | 'chasers_or_mixers' | 'water_options', name: string, section?: string) => {
    const sku = buildIncludedSku(includedPrefixFor(group), name, ticketId, section);
    return getCartQuantity(sku);
};
const incIncluded = (ticketId: number, group: 'main_bottles' | 'chasers_or_mixers' | 'water_options', item: any, section?: string) => {
    const prefix = includedPrefixFor(group);
    const sku = buildIncludedSku(prefix, String(item.name), ticketId, section);
    const current = getCartQuantity(sku);
    const maxQty = Number(item.qty ?? item.quantity ?? item.count ?? Infinity);
    if (current >= maxQty) return;
    const unitPrice = Number(item.price ?? item.cost ?? 0);
    const typeLabelMap: Record<string, string> = { mixdrink: 'Mix Drink', water: 'Water', bottles: 'Bottles' };
    const type = `${item.name} (${typeLabelMap[prefix] || 'Drink'})`;
    cartItems.value[sku] = {
        sku,
        type,
        addon_id: item.id,
        quantity: current + 1,
        price: unitPrice,
        section: section || '-'
    };
};
const decIncluded = (ticketId: number, group: 'main_bottles' | 'chasers_or_mixers' | 'water_options', item: any, section?: string) => {
    const prefix = includedPrefixFor(group);
    const sku = buildIncludedSku(prefix, String(item.name), ticketId, section);
    const current = getCartQuantity(sku);
    if (current <= 0) return;
    if (current === 1) {
        delete cartItems.value[sku];
    } else {
        cartItems.value[sku] = { ...cartItems.value[sku], quantity: current - 1, section: section || cartItems.value[sku]?.section || '-' } as any;
    }
};

const calculateDrinkFees = (basePrice: number, drinkType: string): { drinkFee: number; bottleFee: number; totalFees: number } => {
    if (!eventFeeSettings) {
        return { drinkFee: 0, bottleFee: 0, totalFees: 0 };
    }

    const drinkFeePct = parseFloat(eventFeeSettings.drink_fee_pct?.toString() || '0');
    const bottleFeePct = parseFloat(eventFeeSettings.bottle_fee_pct || '0');

    const isDrinkType = drinkType === 'wine' || drinkType === 'beer' || drinkType === 'water' || drinkType === 'mixdrink' || drinkType === 'softdrink';
    const isBottleType = drinkType === 'bottles';

    const drinkFee = isDrinkType ? basePrice * (drinkFeePct / 100) : 0;
    const bottleFee = isBottleType ? basePrice * (bottleFeePct / 100) : 0;

    return {
        drinkFee,
        bottleFee,
        totalFees: drinkFee + bottleFee
    };
};

const getDiscountedTicketPrice = (ticketData: TicketData): number => {
    const base = parseFloat(ticketData.price?.toString() || '0');
    const promo = parseFloat((ticketData as any)?.promo_price?.toString?.() || '0');
    const discounted = base - (isNaN(promo) ? 0 : promo);
    return discounted > 0 ? discounted : 0;
};

const getBasePrice = (sku: string, ticketData: TicketData): number => {
    if (sku.startsWith('tbl_') && sku.includes('_')) {
        return parseFloat(ticketData.table_price?.toString() || '0');
    } else if (sku.startsWith('tbl_')) {
        return parseFloat(ticketData.table_price?.toString() || '0');
    } else if (sku.startsWith('mixdrink_') || sku.startsWith('wine_') || sku.startsWith('beer_') ||
        sku.startsWith('water_') || sku.startsWith('softdrink_') || sku.startsWith('bottles_')) {
        return getDrinkPrice(sku, ticketData);
    } else {
        return getDiscountedTicketPrice(ticketData);
    }
};

const calculateOrderFees = (): { taxRatePercent: number; vipPackageFeePct: number; bottleSubtotal: number; bottleFee: number; serviceFeePercent: number; serviceFeeFixed: number; processingFeePercent: number; processingFeeFixed: number; drinkSubtotal: number; drinkFees: number; totalFees: number; mobileFee: number } => {
    if (!eventFeeSettings) {
        return { taxRatePercent: 0, vipPackageFeePct: 0, bottleSubtotal: 0, bottleFee: 0, serviceFeePercent: 0, serviceFeeFixed: 0, processingFeePercent: 0, processingFeeFixed: 0, drinkSubtotal: 0, drinkFees: 0, totalFees: 0, mobileFee: 0 };
    }

    const hasPaidTickets = Object.entries(cartItems.value).some(([sku]) => {
        const itemType = getItemType(sku);
        if (itemType === 'ticket') {
            const ticketData = tickets.find(t => t.id.toString() === sku);
            if (!ticketData) return false;
            const discounted = getDiscountedTicketPrice(ticketData);
            return ticketData.is_free !== 'yes' && discounted > 0;
        }
        return false;
    });

    if (!hasPaidTickets) {
        return { taxRatePercent: 0, vipPackageFeePct: 0, bottleSubtotal: 0, bottleFee: 0, serviceFeePercent: 0, serviceFeeFixed: 0, processingFeePercent: 0, processingFeeFixed: 0, drinkSubtotal: 0, drinkFees: 0, totalFees: 0, mobileFee: 0 };
    }

    let drinkSubtotal = 0;
    let bottleSubtotal = 0;
    let drinkFees = 0;
    let bottleFee = 0;
    let mobileFee = 0;
    let cookoutTicketSubtotal = 0;

    Object.entries(cartItems.value).forEach(([sku, item]) => {
        const itemType = getItemType(sku);
        const itemTotal = item.price * item.quantity;

        if (itemType === 'drink') {
            drinkSubtotal += itemTotal;
            const drinkType = sku.split('_')[0];
            const drinkFeeCalc = calculateDrinkFees(item.price, drinkType);
            drinkFees += drinkFeeCalc.totalFees * item.quantity;
        } else if (itemType === 'bottles') {
            bottleSubtotal += itemTotal;
            const bottleType = sku.split('_')[0];
            const bottleFeeCalc = calculateDrinkFees(item.price, bottleType);
            bottleFee += bottleFeeCalc.totalFees * item.quantity;
        } else if (itemType === 'ticket') {
            const ticketData = tickets.find(t => t.id.toString() === sku);
            if (ticketData?.wellness?.booking?.mobileFee) {
                const mode = wellnessSelections.value[ticketData.id]?.serviceMode || 'inhouse';
                if (mode === 'mobile') {
                    mobileFee += ticketData.wellness.booking.mobileFee * item.quantity;
                }
            }
            if (ticketData && isCookoutTicketEnabled(ticketData.id)) {
                cookoutTicketSubtotal += itemTotal;
            }
        }
    });

    let cookoutAddonSubtotal = 0;
    Object.keys(cookoutSelections.value).forEach(ticketIdStr => {
        const ticketId = Number(ticketIdStr);
        if (!Number.isFinite(ticketId)) return;
        cookoutAddonSubtotal += cookoutTotalForTicket(ticketId);
    });

    let serviceFeePercent = 0;
    let serviceFeeFixed = 0;
    let processingFeePercent = 0;
    let processingFeeFixed = 0;
    let taxRateValue = 0;
    let taxMultiplier = 0;
    let taxRate = 0;
    let vipPackageFeePct = 0;
    let taxBase = 0;
    const vipPct = (parseFloat(eventFeeSettings.vip_fee_pct?.toString() || '0') / 100);

    let vipTicketSubtotal = 0;
    let standardTicketSubtotal = 0;
    let taxRatePercent = 0;

    Object.entries(cartItems.value).forEach(([sku, item]) => {
        if (getItemType(sku) === 'ticket' && item.quantity > 0) {
            const ticket = tickets.find(t => t.id.toString() === sku);
            const hasTable = ticket?.has_table === 'yes';
            const isCookoutTicket = Boolean(ticket && isCookoutTicketEnabled(ticket.id));

            const isPackageTableTicket = hasTable && !!(ticket as any)?.package_id;

            const isTableSelected = hasTable && (
                isPackageTableTicket ||
                Object.entries(cartItems.value).some(([tableSku, tableItem]) => {
                    return tableSku.startsWith(`tbl_${ticket?.id}_`) && Number(tableItem?.quantity || 0) > 0;
                })
            );

            const itemTotal = item.price * item.quantity;

            if (isCookoutTicket) {
                // Cookout tickets should use standard service/processing fees and tax (no cookout platform fee)
            }

            if (isTableSelected && vipPct > 0) {
                const tablePrice = parseFloat(ticket?.table_price?.toString() || '0');
                if (tablePrice > 0 && tablePrice !== item.price) {
                    vipTicketSubtotal += tablePrice * item.quantity;
                } else {
                    vipTicketSubtotal += itemTotal;
                }
            } else {
                standardTicketSubtotal += itemTotal;
            }
        }
    });

    if (standardTicketSubtotal > 0) {
        serviceFeePercent = standardTicketSubtotal * (parseFloat(eventFeeSettings.service_fee_pct?.toString() || '0') / 100);
        serviceFeeFixed = parseFloat(eventFeeSettings.service_fee_fixed?.toString() || '0');

        processingFeePercent = standardTicketSubtotal * (parseFloat(eventFeeSettings.processing_fee_pct?.toString() || '0') / 100);
        processingFeeFixed = parseFloat(eventFeeSettings.processing_fee_fixed?.toString() || '0');
    }

    if (vipTicketSubtotal > 0) {
        vipPackageFeePct = vipTicketSubtotal * vipPct;
    }
    const getCountryTaxRules = () => {
        const eventCountry = (event.country || '').toUpperCase().trim();
        
        // Bahamas VAT Rules (10% on full amount)
        if (eventCountry === 'BAHAMAS' || eventCountry === 'BS') {
            return {
                country: 'BAHAMAS',
                taxRate: 0.10, // 10% VAT
                taxBase: 'FULL_AMOUNT', // Tax everything except tips
                taxTicket: true,
                taxFees: true,
                taxMobileFees: true,
                taxPlatformFees: true,
                taxDrinkFees: true,
                taxBottleFees: true
            };
        }
        
        // USA State-based Tax Rules
        if (eventCountry === 'USA' || eventCountry === 'US' || eventCountry === 'UNITED STATES') {
            const defaultRules = {
                country: 'USA',
                taxTicket: true,
                taxFees: false, // Default: only tax services, not fees
                taxMobileFees: true, // Mobile fees are required fees
                taxPlatformFees: true, // Platform/service fees are required fees
                taxDrinkFees: false,
                taxBottleFees: false,
                no_state_sales_tax: false,
                allow_local_sales_tax: false
            };

            const taxRules = (usePage().props as any).taxRules;

            if (!taxRules || !taxRules.US || !event.state) {
                return defaultRules;
            }

            const stateCode = event.state.toUpperCase();
            const stateRules = taxRules.US[stateCode];

            if (!stateRules) {
                return defaultRules;
            }

            if (stateRules.no_state_sales_tax && stateRules.allow_local_sales_tax) {
                const localTaxRate = event.tax_rate || 0;

                return {
                    ...defaultRules,
                    taxTicket: localTaxRate > 0,
                    taxFees: stateRules.tax_fees ?? false,
                    no_state_sales_tax: false,
                    allow_local_sales_tax: true,
                    local_tax_rate: localTaxRate
                };
            }

            return {
                ...defaultRules,
                taxTicket: stateRules.tax_ticket ?? true,
                taxFees: stateRules.tax_fees ?? false,
                no_state_sales_tax: stateRules.no_state_sales_tax ?? false,
                allow_local_sales_tax: stateRules.allow_local_sales_tax ?? false
            };
        }
        
        // Default/International rules - tax everything except tips (conservative approach)
        return {
            country: 'DEFAULT',
            taxTicket: true,
            taxFees: true,
            taxMobileFees: true,
            taxPlatformFees: true,
            taxDrinkFees: true,
            taxBottleFees: true
        };
    };

    {
        const countryTaxRules = getCountryTaxRules();
        
        // Debug logging
        console.log('Tax Calculation Debug:', {
            country: countryTaxRules.country,
            eventCountry: event.country,
            eventState: event.state,
            standardTicketSubtotal,
            vipTicketSubtotal,
            serviceFeePercent,
            serviceFeeFixed,
            processingFeePercent,
            processingFeeFixed,
            mobileFee,
            drinkFees,
            bottleFee,
            vipPackageFeePct
        });
        
        // Handle Bahamas VAT
        if (countryTaxRules.country === 'BAHAMAS') {
            taxRate = countryTaxRules.taxRate;
            taxBase = standardTicketSubtotal + vipTicketSubtotal;
            
            // Add all required fees to tax base for Bahamas
            taxBase += serviceFeePercent + serviceFeeFixed + processingFeePercent + processingFeeFixed;
            taxBase += mobileFee;
            taxBase += drinkFees;
            taxBase += bottleFee;
            taxBase += vipPackageFeePct;
            
            taxRateValue = taxRate;
            taxMultiplier = taxRateValue > 1 ? taxRateValue / 100 : taxRateValue;
            taxRatePercent = taxBase * taxMultiplier;
            
            console.log('Bahamas VAT Calculation:', {
                taxRate,
                taxBase,
                taxMultiplier,
                taxRatePercent
            });
        }
        // Handle USA state-based taxes
        else if (countryTaxRules.country === 'USA') {
            taxRate = parseFloat(event.event_details?.tax_rate) || 0;

            if (countryTaxRules.no_state_sales_tax && countryTaxRules.allow_local_sales_tax && countryTaxRules.local_tax_rate) {
                taxRate = countryTaxRules.local_tax_rate;
            }
            else if (countryTaxRules.no_state_sales_tax) {
                return { taxRatePercent: 0, vipPackageFeePct: 0, bottleSubtotal: 0, bottleFee: 0, serviceFeePercent: 0, serviceFeeFixed: 0, processingFeePercent: 0, processingFeeFixed: 0, drinkSubtotal: 0, drinkFees: 0, totalFees: 0, mobileFee: 0 };
            }

            if (taxRate === 0 && countryTaxRules.rate) {
                taxRate = countryTaxRules.rate;
            }

            const taxIncluded = event.event_details?.tax_included === 'yes';

            if (!taxIncluded) {
                taxRatePercent = 0;
            } else {
                taxBase = standardTicketSubtotal + vipTicketSubtotal;

                // Add required fees to tax base based on USA rules
                if (countryTaxRules.taxFees) {
                    taxBase += serviceFeePercent + serviceFeeFixed + processingFeePercent + processingFeeFixed;
                }
                
                // Always tax mobile fees (required fee)
                if (countryTaxRules.taxMobileFees) {
                    taxBase += mobileFee;
                }
                
                // Always tax platform/service fees (required fees)
                if (countryTaxRules.taxPlatformFees) {
                    taxBase += vipPackageFeePct;
                }
                
                // Tax drink and bottle fees if state requires it
                if (countryTaxRules.taxDrinkFees) {
                    taxBase += drinkFees;
                }
                
                if (countryTaxRules.taxBottleFees) {
                    taxBase += bottleFee;
                }

                taxRateValue = parseFloat(taxRate?.toString() || '0');
                taxMultiplier = taxRateValue > 1 ? taxRateValue / 100 : taxRateValue;
                taxRatePercent = taxBase * taxMultiplier;
                
                console.log('USA Tax Calculation:', {
                    state: event.state,
                    taxRate,
                    taxBase,
                    taxMultiplier,
                    taxRatePercent,
                    taxRules: countryTaxRules
                });
            }
        }
        // Handle default/international rules
        else {
            taxRate = parseFloat(event.event_details?.tax_rate) || 0.10; // Default 10% if not specified
            const taxIncluded = event.event_details?.tax_included !== 'no'; // Default to included

            if (!taxIncluded) {
                taxRatePercent = 0;
            } else {
                taxBase = standardTicketSubtotal + vipTicketSubtotal;
                
                // Tax everything except tips (conservative approach)
                taxBase += serviceFeePercent + serviceFeeFixed + processingFeePercent + processingFeeFixed;
                taxBase += mobileFee;
                taxBase += drinkFees;
                taxBase += bottleFee;
                taxBase += vipPackageFeePct;

                taxRateValue = parseFloat(taxRate?.toString() || '0');
                taxMultiplier = taxRateValue > 1 ? taxRateValue / 100 : taxRateValue;
                taxRatePercent = taxBase * taxMultiplier;
            }
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
        totalFees: standardFeesTotal + drinkFees + bottleFee + vipPackageFeePct + mobileFee
    };
};
const clearCart = () => {
    cartItems.value = {};
    cookoutSelections.value = {};
    wellnessSelections.value = {};

    activeTicketId.value = null;

    document.querySelectorAll('[data-role="qty"]').forEach(qtySpan => {
        qtySpan.textContent = '0';
    });

    updateOrderSummary();
};

const validateWellnessServiceSelection = (): { ok: boolean; message?: string } => {
    for (const [sku, item] of cartTicketEntries.value) {
        if (Number(item.quantity || 0) <= 0) continue;
        const ticket = tickets.find(t => t.id.toString() === sku);
        if (!ticket?.wellness || ticket.wellness.includeService !== 'yes') continue;

        const included = wellnessIncludedServices(ticket.id);
        if (included.length === 0) continue;

        const selected = String(wellnessSelections.value[ticket.id]?.includedService || '');
        if (!selected) {
            return { ok: false, message: 'Please highlight service' };
        }
    }
    return { ok: true };
};

const validateCookoutServiceSelection = (): { ok: boolean; message?: string } => {
    for (const [sku, item] of cartTicketEntries.value) {
        if (Number(item.quantity || 0) <= 0) continue;
        const ticket = tickets.find(t => t.id.toString() === sku);
        if (!ticket || !isCookoutTicketEnabled(ticket.id)) continue;

        const included = cookoutIncludedProteins(ticket.id);
        if (included.length === 0) continue;

        const selected = String(cookoutSelections.value[ticket.id]?.includedProtein || '');
        if (!selected) {
            return { ok: false, message: 'Please highlight service' };
        }
    }
    return { ok: true };
};

const canProceedToPayment = computed(() => {
    if (cart.value.items.length === 0) return false;
    return validateWellnessServiceSelection().ok && validateCookoutServiceSelection().ok;
});

const paymentBlockMessage = computed(() => {
    if (cart.value.items.length === 0) return '';
    const wellnessRes = validateWellnessServiceSelection();
    if (!wellnessRes.ok) return wellnessRes.message || '';
    const cookoutRes = validateCookoutServiceSelection();
    if (!cookoutRes.ok) return cookoutRes.message || '';
    return '';
});
const cartItems = ref<Record<string, { type: string, quantity: number, price: number, section?: string, addon_id?: number }>>({});
const cookoutSelections = ref<Record<number, CookoutSelection>>({});

type WellnessSelection = {
    includedService: string;
    services: Record<string, number>;
    manualAddons: Record<string, number>;
    selectedSlot: string | null;
    selectedSlotDate: string | null;
    holdExpiresAt: number | null;
    serviceMode: 'mobile' | 'inhouse';
    contactPhone?: string;
};
const wellnessSelections = ref<Record<number, WellnessSelection>>({});
const holdTimers = ref<Record<number, number>>({});

const cartTicketEntries = computed(() => {
    return Object.entries(cartItems.value).filter(([sku, item]) => getItemType(sku) === 'ticket' && Number(item.quantity || 0) > 0);
});

const cartHasCookoutTicket = computed(() => {
    return cartTicketEntries.value.some(([sku]) => {
        const ticket = tickets.find(t => t.id.toString() === sku);
        return Boolean(ticket && isCookoutTicketEnabled(ticket.id));
    });
});

const cartHasNonCookoutPaidTicket = computed(() => {
    return cartTicketEntries.value.some(([sku]) => {
        const ticket = tickets.find(t => t.id.toString() === sku);
        if (!ticket) return false;
        if (isCookoutTicketEnabled(ticket.id)) return false;
        const discounted = getDiscountedTicketPrice(ticket);
        return discounted > 0;
    });
});

const ensureWellnessSelection = (ticketId: number): WellnessSelection => {
    if (!wellnessSelections.value[ticketId]) {
        wellnessSelections.value[ticketId] = { 
            includedService: '', 
            services: {}, 
            manualAddons: {}, 
            selectedSlot: null,
            holdExpiresAt: null,
            serviceMode: 'inhouse',
            contactPhone: ''
        };
    }
    return wellnessSelections.value[ticketId];
};

const ensureCookoutSelection = (ticketId: number): CookoutSelection => {
    if (!cookoutSelections.value[ticketId]) {
        cookoutSelections.value[ticketId] = { includedProtein: '', proteins: {}, manualAddons: {}, drinks: {} };
    }
    return cookoutSelections.value[ticketId];
};
const getCookoutConfig = (ticketId: number): CookoutConfig | null => {
    const t = tickets.find(x => x.id === ticketId);
    return (t?.cookout as any) || null;
};

const getWellnessConfig = (ticketId: number): WellnessConfig | null => {
    const t = tickets.find(x => x.id === ticketId);
    return (t?.wellness as any) || null;
};

const wellnessIncludedServices = (ticketId: number): WellnessServiceConfig[] => {
    const c = getWellnessConfig(ticketId);
    const services = Array.isArray(c?.services) ? c?.services : [];
    return services.filter(s => s && s.name && (s.mode || 'included') !== 'addon');
};

const wellnessAddonServices = (ticketId: number): WellnessServiceConfig[] => {
    const c = getWellnessConfig(ticketId);
    const services = Array.isArray(c?.services) ? c?.services : [];
    return services.filter(s => s && s.name && (s.mode || 'included') === 'addon');
};

const wellnessManualAddons = (ticketId: number): WellnessManualAddonConfig[] => {
    const c = getWellnessConfig(ticketId);
    const addons = Array.isArray(c?.manualAddons) ? c?.manualAddons : [];
    return addons.filter(a => a && a.name);
};

const wellnessBooking = (ticketId: number): WellnessBookingConfig | null => {
    const c = getWellnessConfig(ticketId);
    return (c?.booking as any) || null;
};

const wellnessSlotBlocks = (ticketId: number) => {
    const t = tickets.find(x => x.id === ticketId);
    return (t as any)?.wellness_slot_blocks || [];
};

const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

const startHoldTimer = (ticketId: number) => {
    if (window[`timer_${ticketId}`]) clearInterval(window[`timer_${ticketId}`]);
    
    const updateTimer = () => {
        const selection = wellnessSelections.value[ticketId];
        if (!selection || !selection.holdExpiresAt) {
            holdTimers.value[ticketId] = 0;
            clearInterval(window[`timer_${ticketId}`]);
            return;
        }

        const now = Date.now();
        const diff = Math.max(0, Math.floor((selection.holdExpiresAt - now) / 1000));
        holdTimers.value[ticketId] = diff;

        if (diff <= 0) {
            releaseWellnessSlot(ticketId);
            clearInterval(window[`timer_${ticketId}`]);
        }
    };

    updateTimer();
    window[`timer_${ticketId}`] = setInterval(updateTimer, 1000);
};

const releaseWellnessSlot = (ticketId: number) => {
    const selection = wellnessSelections.value[ticketId];
    if (selection) {
        selection.selectedSlot = null;
        selection.holdExpiresAt = null;
    }
    holdTimers.value[ticketId] = 0;
    if (window[`timer_${ticketId}`]) {
        clearInterval(window[`timer_${ticketId}`]);
    }
};

const selectWellnessSlot = (ticketId: number, slot: string, date: string) => {
    if (!hasBaseTicketInCart(ticketId)) return;
    const selection = ensureWellnessSelection(ticketId);
    selection.selectedSlot = slot;
    selection.selectedSlotDate = date;
    selection.holdExpiresAt = Date.now() + 5 * 60 * 1000; // 5 minutes
    startHoldTimer(ticketId);
};

const getHoldTime = (ticketId: number) => {
    return formatTime(holdTimers.value[ticketId] || 0);
};

const getSelectedWellnessSlot = (ticketId: number): string | null => {
    return wellnessSelections.value[ticketId]?.selectedSlot || null;
};

const setWellnessIncludedService = (ticketId: number, name: string) => {
    if (!hasBaseTicketInCart(ticketId)) return;
    ensureWellnessSelection(ticketId).includedService = name;
};

const getWellnessIncludedServiceValue = (ticketId: number): string => {
    return String(wellnessSelections.value[ticketId]?.includedService || '');
};

const setWellnessServiceMode = (ticketId: number, mode: 'mobile' | 'inhouse') => {
    const sel = ensureWellnessSelection(ticketId);
    sel.serviceMode = mode;
    updateOrderSummary();
};

const getWellnessServiceModeValue = (ticketId: number): 'mobile' | 'inhouse' => {
    const val = wellnessSelections.value[ticketId]?.serviceMode || 'inhouse';
    return val as 'mobile' | 'inhouse';
};

const setWellnessContactPhone = (ticketId: number, phone: string) => {
    const sel = ensureWellnessSelection(ticketId);
    sel.contactPhone = phone;
};

const getWellnessContactPhoneValue = (ticketId: number): string => {
    return String(wellnessSelections.value[ticketId]?.contactPhone || '');
};

const getWellnessServiceQty = (ticketId: number, name: string): number => {
    return Number(wellnessSelections.value[ticketId]?.services?.[name] || 0);
};

const incWellnessService = (ticketId: number, name: string) => {
    if (!hasBaseTicketInCart(ticketId)) return;
    const sel = ensureWellnessSelection(ticketId);
    const current = Number(sel.services[name] || 0);
    const svc = wellnessAddonServices(ticketId).find(s => s.name === name);
    const maxQty = Number((svc as any)?.qty || 0);
    if (maxQty > 0 && current >= maxQty) return;
    sel.services[name] = current + 1;
};

const decWellnessService = (ticketId: number, name: string) => {
    const sel = ensureWellnessSelection(ticketId);
    const current = Number(sel.services[name] || 0);
    if (current <= 0) return;
    const next = current - 1;
    if (next <= 0) {
        delete sel.services[name];
    } else {
        sel.services[name] = next;
    }
};

const getWellnessManualAddonQty = (ticketId: number, name: string): number => {
    return Number(wellnessSelections.value[ticketId]?.manualAddons?.[name] || 0);
};

const incWellnessManualAddon = (ticketId: number, name: string) => {
    if (!hasBaseTicketInCart(ticketId)) return;
    const sel = ensureWellnessSelection(ticketId);
    const current = Number(sel.manualAddons[name] || 0);
    const addon = wellnessManualAddons(ticketId).find(a => a.name === name);
    const maxQty = Number(addon?.qty || 0);
    if (maxQty > 0 && current >= maxQty) return;
    sel.manualAddons[name] = current + 1;
};

const decWellnessManualAddon = (ticketId: number, name: string) => {
    const sel = ensureWellnessSelection(ticketId);
    const current = Number(sel.manualAddons[name] || 0);
    if (current <= 0) return;
    const next = current - 1;
    if (next <= 0) {
        delete sel.manualAddons[name];
    } else {
        sel.manualAddons[name] = next;
    }
};

const wellnessTotalForTicket = (ticketId: number): number => {
    if (!hasBaseTicketInCart(ticketId)) return 0;
    const ticket = tickets.find(t => t.id === ticketId);
    if (!ticket?.wellness || ticket.wellness.includeService !== 'yes') return 0;
    const sel = wellnessSelections.value[ticketId];
    if (!sel) return 0;
    let sum = 0;
    wellnessAddonServices(ticketId).forEach(s => {
        const qty = Number(sel.services[s.name] || 0);
        if (qty > 0) sum += Number(s.price || 0) * qty;
    });
    wellnessManualAddons(ticketId).forEach(a => {
        const qty = Number(sel.manualAddons[a.name] || 0);
        if (qty > 0) sum += Number(a.price || 0) * qty;
    });
    return sum;
};

const wellnessAddonsTotal = computed(() => {
    let sum = 0;
    Object.keys(wellnessSelections.value).forEach(ticketIdStr => {
        const ticketId = Number(ticketIdStr);
        if (!Number.isFinite(ticketId)) return;
        sum += wellnessTotalForTicket(ticketId);
    });
    return sum;
});

const getWellnessLinesForTicket = (ticketId: number) => {
    if (!hasBaseTicketInCart(ticketId)) return [];
    const ticket = tickets.find(t => t.id === ticketId);
    if (!ticket?.wellness || ticket.wellness.includeService !== 'yes') return [];
    const sel = wellnessSelections.value[ticketId];
    if (!sel) return [];
    const lines: Array<{ nameType: string; qty: number; total: number; value?: string; isSlot?: boolean }> = [];
    
    if (sel.selectedSlot) {
        lines.push({ nameType: 'Time slot', value: `${sel.selectedSlot} (HOLD)`, qty: 1, total: 0, isSlot: true });
    }

    if (sel.serviceMode) {
        const val = sel.serviceMode === 'mobile' ? 'Mobile' : 'In-house';
        lines.push({ nameType: 'Service mode', value: val, qty: 1, total: 0, isSlot: true });
    }

    if (sel.serviceMode === 'mobile' && sel.contactPhone) {
        lines.push({ nameType: 'Contact phone', value: sel.contactPhone, qty: 1, total: 0, isSlot: true });
    }

    if (sel.includedService) {
        const key = ticketId.toString();
        const ticketQty = Number(cartItems.value?.[key]?.quantity || 0);
        if (ticketQty > 0) {
            lines.push({ nameType: `${sel.includedService} (Included Service)`, qty: ticketQty, total: 0 });
        }
    }
    wellnessAddonServices(ticketId).forEach(s => {
        const qty = Number(sel.services[s.name] || 0);
        if (qty > 0) lines.push({ nameType: `${s.name} (Wellness Add-on)`, qty, total: Number(s.price || 0) * qty });
    });
    wellnessManualAddons(ticketId).forEach(a => {
        const qty = Number(sel.manualAddons[a.name] || 0);
        if (qty > 0) lines.push({ nameType: `${a.name} (Wellness Manual)`, qty, total: Number(a.price || 0) * qty });
    });
    return lines;
};
const isCookoutTicketEnabled = (ticketId: number): boolean => {
    const c = getCookoutConfig(ticketId);
    return Boolean(c && c.includeFood === 'yes');
};
const cookoutIncludedProteins = (ticketId: number): string[] => {
    const c = getCookoutConfig(ticketId);
    const proteins = (c?.proteins || []) as any[];
    const customProteins = (((c as any)?.customProteins || []) as any[]);

    const list = [...proteins, ...customProteins]
        .filter((p: any) => {
            if (!p || !p.name) return false;
            if (p.checked === false) return false;
            return (p.mode || 'included') !== 'addon';
        })
        .map((p: any) => String(p.name));

    return Array.from(new Set(list));
};
const cookoutIncludedDrinks = (ticketId: number): string[] => {
    const c = getCookoutConfig(ticketId);
    const list = (c?.drinks || [])
        .map((d: any) => typeof d === 'string' ? {name: d, mode: 'included'} : d)
        .filter((d: any) => d && d.name && (d.mode || 'included') !== 'addon')
        .map((d: any) => String(d.name));
    return Array.from(new Set(list));
};
const cookoutAddonDrinks = (ticketId: number): Array<{ name: string; price: number; maxQty: number | null }> => {
    const c = getCookoutConfig(ticketId);
    return (c?.drinks || [])
        .map((d: any) => typeof d === 'string' ? {name: d, mode: 'included', price: 0} : d)
        .filter((d: any) => d && d.name && d.mode === 'addon')
        .map((d: any) => ({
            name: String(d.name),
            price: Number(d.price || 0),
            maxQty: (d.qty !== undefined && d.qty !== null) ? Number(d.qty) : null
        }));
};
const cookoutAddonProteins = (ticketId: number): Array<{ name: string; price: number; maxQty: number | null }> => {
    const c = getCookoutConfig(ticketId);
    const proteins = (c?.proteins || []);
    const customProteins = ((c as any)?.customProteins || []) as CookoutProteinConfig[];
    return [...proteins, ...customProteins]
        .filter((p: any) => p && p.name && p.checked !== false && (p.mode || 'included') === 'addon')
        .map(p => ({
            name: String(p.name),
            price: Number((p as any).price || 0),
            maxQty: (p.qty !== undefined && p.qty !== null) ? Number(p.qty) : null
        }));
};
const cookoutManualAddons = (ticketId: number): Array<{ name: string; price: number; maxQty: number | null }> => {
    const c = getCookoutConfig(ticketId);
    return (c?.manualAddons || [])
        .filter(a => a && a.name)
        .map(a => ({
            name: String(a.name),
            price: Number(a.price || 0),
            maxQty: (a.qty !== undefined && a.qty !== null) ? Number(a.qty) : null
        }));
};
const setCookoutIncludedProtein = (ticketId: number, name: string) => {
    ensureCookoutSelection(ticketId).includedProtein = name;
};
const getCookoutIncludedProteinValue = (ticketId: number): string => {
    return String(cookoutSelections.value[ticketId]?.includedProtein || '');
};
const getCookoutProteinQty = (ticketId: number, name: string): number => {
    return Number(cookoutSelections.value[ticketId]?.proteins?.[name] || 0);
};
const incCookoutProtein = (ticketId: number, name: string) => {
    if (!hasBaseTicketInCart(ticketId)) {
        toast.error('Add a ticket before selecting cookout add-ons');
        return;
    }
    const selection = ensureCookoutSelection(ticketId);
    const cfg = cookoutAddonProteins(ticketId).find(p => p.name === name);
    if (!cfg) return;
    const current = Number(selection.proteins[name] || 0);
    if (cfg.maxQty !== null && cfg.maxQty > 0 && current >= cfg.maxQty) return;
    selection.proteins[name] = current + 1;
};
const decCookoutProtein = (ticketId: number, name: string) => {
    const selection = ensureCookoutSelection(ticketId);
    const current = Number(selection.proteins[name] || 0);
    if (current <= 0) return;
    const next = current - 1;
    if (next <= 0) {
        delete selection.proteins[name];
    } else {
        selection.proteins[name] = next;
    }
};
const getCookoutManualAddonQty = (ticketId: number, name: string): number => {
    return Number(cookoutSelections.value[ticketId]?.manualAddons?.[name] || 0);
};
const incCookoutManualAddon = (ticketId: number, name: string) => {
    if (!hasBaseTicketInCart(ticketId)) {
        toast.error('Add a ticket before selecting cookout add-ons');
        return;
    }
    const selection = ensureCookoutSelection(ticketId);
    const cfg = cookoutManualAddons(ticketId).find(a => a.name === name);
    if (!cfg) return;
    const current = Number(selection.manualAddons[name] || 0);
    if (cfg.maxQty !== null && cfg.maxQty > 0 && current >= cfg.maxQty) return;
    selection.manualAddons[name] = current + 1;
};
const decCookoutManualAddon = (ticketId: number, name: string) => {
    const selection = ensureCookoutSelection(ticketId);
    const current = Number(selection.manualAddons[name] || 0);
    if (current <= 0) return;
    const next = current - 1;
    if (next <= 0) {
        delete selection.manualAddons[name];
    } else {
        selection.manualAddons[name] = next;
    }
};
const getCookoutDrinkQty = (ticketId: number, name: string): number => {
    return Number(cookoutSelections.value[ticketId]?.drinks?.[name] || 0);
};
const incCookoutDrink = (ticketId: number, name: string) => {
    if (!hasBaseTicketInCart(ticketId)) {
        toast.error('Add a ticket before selecting cookout add-ons');
        return;
    }
    const selection = ensureCookoutSelection(ticketId);
    const cfg = cookoutAddonDrinks(ticketId).find(d => d.name === name);
    if (!cfg) return;
    const current = Number(selection.drinks[name] || 0);
    if (cfg.maxQty !== null && cfg.maxQty > 0 && current >= cfg.maxQty) return;
    selection.drinks[name] = current + 1;
};
const decCookoutDrink = (ticketId: number, name: string) => {
    const selection = ensureCookoutSelection(ticketId);
    const current = Number(selection.drinks[name] || 0);
    if (current <= 0) return;
    const next = current - 1;
    if (next <= 0) {
        delete selection.drinks[name];
    } else {
        selection.drinks[name] = next;
    }
};
const cookoutTotalForTicket = (ticketId: number): number => {
    if (!isCookoutTicketEnabled(ticketId)) return 0;
    if (!hasBaseTicketInCart(ticketId)) return 0;
    const sel = cookoutSelections.value[ticketId];
    if (!sel) return 0;
    let sum = 0;
    cookoutAddonProteins(ticketId).forEach(p => {
        const qty = Number(sel.proteins[p.name] || 0);
        if (qty > 0) sum += Number(p.price || 0) * qty;
    });
    cookoutAddonDrinks(ticketId).forEach(d => {
        const qty = Number(sel.drinks[d.name] || 0);
        if (qty > 0) sum += Number(d.price || 0) * qty;
    });
    cookoutManualAddons(ticketId).forEach(a => {
        const qty = Number(sel.manualAddons[a.name] || 0);
        if (qty > 0) sum += Number(a.price || 0) * qty;
    });
    return sum;
};
const cookoutAddonsTotal = computed(() => {
    let sum = 0;
    Object.keys(cookoutSelections.value).forEach((ticketIdStr) => {
        const ticketId = Number(ticketIdStr);
        if (!Number.isFinite(ticketId)) return;
        sum += cookoutTotalForTicket(ticketId);
    });
    return sum;
});
const getCookoutLinesForTicket = (ticketId: number) => {
    if (!isCookoutTicketEnabled(ticketId)) return [];
    if (!hasBaseTicketInCart(ticketId)) return [];
    const sel = cookoutSelections.value[ticketId];
    if (!sel) return [];
    const lines: Array<{ nameType: string; qty: number; total: number }> = [];
    if (sel.includedProtein) {
        const key = ticketId.toString();
        const ticketQty = Number(cartItems.value?.[key]?.quantity || 0);
        if (ticketQty > 0) {
            lines.push({ nameType: `${sel.includedProtein} (Included Plate)`, qty: ticketQty, total: 0 });
        }
    }
    cookoutAddonProteins(ticketId).forEach(p => {
        const qty = Number(sel.proteins[p.name] || 0);
        if (qty > 0) {
            lines.push({ nameType: `${p.name} (Protein)`, qty, total: Number(p.price || 0) * qty });
        }
    });
    cookoutAddonDrinks(ticketId).forEach(d => {
        const qty = Number(sel.drinks[d.name] || 0);
        if (qty > 0) {
            lines.push({ nameType: `${d.name} (Drink)`, qty, total: Number(d.price || 0) * qty });
        }
    });
    cookoutManualAddons(ticketId).forEach(a => {
        const qty = Number(sel.manualAddons[a.name] || 0);
        if (qty > 0) {
            lines.push({ nameType: `${a.name} (Extra)`, qty, total: Number(a.price || 0) * qty });
        }
    });
    return lines;
};

const cart = computed(() => ({
    items: Object.entries(cartItems.value).map(([sku, item]) => ({
        sku,
        type: item.type,
        quantity: item.quantity,
        price: item.price,
        section: item.section,
        addon_id: item.addon_id
    })),
    appliedCoupons: []
}));

const packageTablesSubtotal = computed(() => {
    let total = 0;
    Object.entries(cartItems.value).forEach(([sku, item]) => {
        const ticketId = parseInt(sku);
        if (!isNaN(ticketId)) {
            const ticket = tickets.find(t => t.id === ticketId);
            if (ticket && ticket.package_id && ticket.has_table === 'yes') {
                const tablePrice = parseFloat(ticket.table_price?.toString() || '0');
                const ticketPrice = getDiscountedTicketPrice(ticket);
                if (tablePrice > 0) {
                    if (tablePrice !== ticketPrice) {
                        total += tablePrice * (item.quantity || 0);
                    }
                }
            }
        }
    });
    return total;
});
const sumCartWithTableDedup = () => {
    const baseMap = new Map<number, { price: number }>();
    Object.entries(cartItems.value).forEach(([sku, item]) => {
        const id = parseInt(sku);
        if (!isNaN(id)) {
            baseMap.set(id, { price: Number(item.price || 0) });
        }
    });

    let sum = 0;
    Object.entries(cartItems.value).forEach(([sku, item]) => {
        let ticketId: number | null = null;
        if (sku.startsWith('tbl_')) {
            const parts = sku.split('_');
            ticketId = parseInt(parts[1]);
        } else if (!isNaN(parseInt(sku))) {
            ticketId = parseInt(sku);
        } else if (sku.includes('_')) {
            const parts = sku.split('_');
            ticketId = parseInt(parts[parts.length - 1]);
        }

        const baseInfo = ticketId ? baseMap.get(ticketId) : undefined;
        if (sku.startsWith('tbl_') && baseInfo && Number(item.price) === Number(baseInfo.price)) {
            return;
        }
        sum += Number(item.price) * Number(item.quantity || 0);
    });
    return sum;
};

const total = computed(() => {
    const baseTotal = sumCartWithTableDedup();
    const packageTables = Number(packageTablesSubtotal.value) || 0;
    const discountedSubtotal = (baseTotal) - discount.value;
    const fees = calculateOrderFees();
    const finalTotal = discountedSubtotal + fees.totalFees + packageTables + cookoutAddonsTotal.value + wellnessAddonsTotal.value;
    return finalTotal;
});

const subtotal = computed(() => {
    const base = sumCartWithTableDedup();
    const packageTables = Number(packageTablesSubtotal.value) || 0;
    return base + packageTables + cookoutAddonsTotal.value + wellnessAddonsTotal.value;
});

const servicesSubtotal = computed(() => {
    const fees = calculateOrderFees();
    return subtotal.value - fees.drinkSubtotal - fees.bottleSubtotal;
});

const isCartFreeOnly = computed(() => {
    return Object.entries(cartItems.value).every(([sku]) => {
        const itemType = getItemType(sku);
        if (itemType === 'ticket') {
            const ticketData = tickets.find(t => t.id.toString() === sku);
            return ticketData && (ticketData.is_free === 'yes' ||
                ticketData.price === 0 || ticketData.price === '0' ||
                ticketData.price === '0.00' || !ticketData.price ||
                parseFloat(ticketData.price?.toString() || '0') === 0);
        }
        return true;
    });
});

const groupedCartItems = computed(() => {
    const groups = new Map();

    Object.entries(cartItems.value).forEach(([sku, item]) => {
        let ticketId;
        let itemType = 'base';

        if (sku.startsWith('tbl_')) {
            const parts = sku.split('_');
            ticketId = parseInt(parts[1]);
            itemType = 'table';
        } else if (sku.includes('_') && !sku.startsWith('tbl_')) {
            const parts = sku.split('_');
            let idIdx = -1;
            for (let i = parts.length - 1; i >= 0; i--) {
                if (/^\d+$/.test(parts[i])) { idIdx = i; break; }
            }
            ticketId = idIdx !== -1 ? parseInt(parts[idIdx]) : NaN;
            itemType = 'drink';
        } else {
            ticketId = parseInt(sku);
            itemType = 'base';
        }

        if (!groups.has(ticketId)) {
            const ticket = tickets.find(t => t.id === ticketId);
            groups.set(ticketId, {
                ticketId,
                ticketName: ticket?.name || `Ticket ${ticketId}`,
                baseTicket: null,
                tables: [],
                drinks: [],
                package: ticket?.drink_package || null,
                ticketTotal: 0,
                total: 0,
                sections: ticket?.sections
            });
        }

        const group = groups.get(ticketId);
        const itemWithSku = { ...item, sku };

        if (itemType === 'base') {
            group.baseTicket = itemWithSku;
            group.ticketTotal = item.price * item.quantity;
        } else if (itemType === 'table') {
            group.tables.push(itemWithSku);
        } else if (itemType === 'drink') {
            group.drinks.push(itemWithSku);
        }

        group.total += item.price * item.quantity;
    });

    return Array.from(groups.values());
});

const errorDialog = ref({
    open: false,
    title: '',
    description: ''
});

const activeTicketId = ref<number | null>(null);

const getCartQuantity = (sku: string): number => {
    return Number(cartItems.value?.[sku]?.quantity || 0);
};

const getDrinkAddonGroups = (ticket: any): { key: string; label: string; prefix: string; items: any[] }[] => {
    const items = ticket?.drink_addons?.items || {};
    const groups = [
        { key: 'mixDrinks', label: 'Mix Drinks', prefix: 'mixdrink' },
        { key: 'wines', label: 'Wines', prefix: 'wine' },
        { key: 'beers', label: 'Beers', prefix: 'beer' },
        { key: 'waters', label: 'Waters', prefix: 'water' },
        { key: 'softDrinks', label: 'Soft Drinks', prefix: 'softdrink' },
        { key: 'bottles', label: 'Bottles', prefix: 'bottles' },
    ];

    return groups
        .map((g) => ({
            ...g,
            items: Array.isArray(items?.[g.key]) ? items[g.key] : [],
        }))
        .filter((g) => g.items.length > 0);
};

const showDrinksModal = ref(false);
const drinksModalTicketId = ref<number | null>(null);
const drinksTicket = computed(() => tickets.find(t => t.id === (drinksModalTicketId.value ?? -1)) || null);

const openDrinksModal = (ticketId: number) => {
    activeTicketId.value = ticketId;
    drinksModalTicketId.value = ticketId;
    showDrinksModal.value = true;
};

const closeDrinksModal = () => {
    showDrinksModal.value = false;
    drinksModalTicketId.value = null;
};

const showTablesModal = ref(false);
const tablesModalTicketId = ref<number | null>(null);
const tablesSelectedSection = ref('Main Floor');
const tablesTicket = computed(() => tickets.find(t => t.id === (tablesModalTicketId.value ?? -1)) || null);
const openTablesModal = (ticketId: number) => {
    activeTicketId.value = ticketId;
    tablesModalTicketId.value = ticketId;
    tablesSelectedSection.value = 'Main Floor';
    showTablesModal.value = true;
};
const closeTablesModal = () => {
    showTablesModal.value = false;
    tablesModalTicketId.value = null;
};

const showPackageModal = ref(false);
const packageModalTicketId = ref<number | null>(null);
const packageTicket = computed(() => tickets.find(t => t.id === (packageModalTicketId.value ?? -1)) || null);
const openPackageModal = (ticketId: number) => {
    activeTicketId.value = ticketId;
    packageModalTicketId.value = ticketId;
    showPackageModal.value = true;
};
const closePackageModal = () => {
    showPackageModal.value = false;
    packageModalTicketId.value = null;
};

const updateTicketQuantity = (sku: string, action: 'inc' | 'dec') => {
    const currentQty = cartItems.value[sku]?.quantity || 0;

    if (action === 'inc') {
        const ticketData = tickets.find(t => {
            if (sku === t.id.toString()) return true;
            if (sku === `tbl_${t.id}`) return true;
            if (sku.startsWith(`tbl_${t.id}_`)) return true;
            if (sku.startsWith('mixdrink_') && sku.endsWith(`_${t.id}`)) return true;
            if (sku.startsWith('wine_') && sku.endsWith(`_${t.id}`)) return true;
            if (sku.startsWith('beer_') && sku.endsWith(`_${t.id}`)) return true;
            if (sku.startsWith('water_') && sku.endsWith(`_${t.id}`)) return true;
            if (sku.startsWith('softdrink_') && sku.endsWith(`_${t.id}`)) return true;
            if (sku.startsWith('bottles_') && sku.endsWith(`_${t.id}`)) return true;
            return false;
        });

        if (ticketData) {
            const isDrinkSku = sku.startsWith('mixdrink_') || sku.startsWith('wine_') || sku.startsWith('beer_') || sku.startsWith('water_') || sku.startsWith('softdrink_') || sku.startsWith('bottles_');
            if (isDrinkSku && !hasBaseTicketInCart(ticketData.id)) {
                toast.error('Add a ticket before selecting drinks');
                return;
            }
            const basePrice = getBasePrice(sku, ticketData);

            const type = sku.startsWith('tbl_') && sku.includes('_') ?
                `Table for ${ticketData.table_capacity}` :
                sku.startsWith('tbl_') ?
                    `Table for ${ticketData.table_capacity}` :
                    (sku.startsWith('mixdrink_') || sku.startsWith('wine_') || sku.startsWith('beer_') ||
                        sku.startsWith('water_') || sku.startsWith('softdrink_') || sku.startsWith('bottles_')) ?
                        (() => {
                            const parts = sku.split('_');
                            const drinkName = parts.slice(1, parts.length - 1).join(' ');
                            const drinkType = parts[0];

                            const typeLabel = {
                                'mixdrink': 'Mix Drink',
                                'wine': 'Wine',
                                'beer': 'Beer',
                                'water': 'Water',
                                'softdrink': 'Soft Drink',
                                'bottles': 'Bottles'
                            }[drinkType] || 'Drink';

                            return `${drinkName} (${typeLabel})`;
                        })() :
                        ticketData.type;

            const section = sku.startsWith('tbl_') && sku.includes('_') ?
                sku.split('_')[2] : '-';

            cartItems.value[sku] = {
                sku,
                type,
                quantity: currentQty + 1,
                price: basePrice,
                section: section
            };

            if (sku === ticketData.id.toString()) {
                activeTicketId.value = ticketData.id;

                const isWellnessTicket = Boolean(ticketData?.wellness && ticketData.wellness.includeService === 'yes');
                if (isWellnessTicket) {
                    const onlyIncluded = wellnessIncludedServices(ticketData.id);
                    if (onlyIncluded.length === 1) {
                        const sel = ensureWellnessSelection(ticketData.id);
                        if (!sel.includedService) {
                            sel.includedService = onlyIncluded[0].name;
                        }
                    }
                }
            }

            updateDOMQuantity(sku);
        }
    } else if (action === 'dec' && currentQty > 0) {
        if (currentQty === 1) {
            delete cartItems.value[sku];

            const ticketId = parseInt(sku);
            if (!isNaN(ticketId) && activeTicketId.value === ticketId) {
                activeTicketId.value = null;
            }
            if (!isNaN(ticketId) && sku === ticketId.toString()) {
                delete cookoutSelections.value[ticketId];
                delete wellnessSelections.value[ticketId];
            }
        } else {
            cartItems.value[sku] = {
                ...cartItems.value[sku],
                sku,
                quantity: currentQty - 1
            };
        }

        updateDOMQuantity(sku);
    }

    updateOrderSummary();
};

const updateOrderSummary = () => {
};

const updateDOMQuantity = (sku: string) => {
    const qtySpans = document.querySelectorAll(`[data-sku="${sku}"] [data-role="qty"]`);
    const quantity = cartItems.value[sku]?.quantity || 0;

    qtySpans.forEach(span => {
        span.textContent = quantity.toString();
    });
};

watch(cartItems, (newCartItems) => {
    Object.keys(newCartItems).forEach(sku => {
        updateDOMQuantity(sku);
    });

    updateOrderSummary();
}, { deep: true });

const prepareCartForBackend = () => {
    type BackendItem = { ticketId: number; qty: number; addons: any[]; cookout?: any; wellness?: any };
    const byTicket = new Map<number, BackendItem>();

    const getDrinkAddonId = (ticketId: number, categoryKey: string, name: string): number | null => {
        const ticket = tickets.find(t => t.id === ticketId);
        if (!ticket || !ticket.drink_addons || !ticket.drink_addons.items) return null;
        const categories = ticket.drink_addons.items as Record<string, any[]>;
        let idCounter = 1;
        for (const [cat, items] of Object.entries(categories)) {
            for (const it of items) {
                if (cat === categoryKey && it.name === name) return idCounter;
                idCounter++;
            }
        }
        return null;
    };

    cart.value.items.forEach(item => {
        let ticketId: number;
        if (item.sku.startsWith('tbl_')) {
            const parts = item.sku.split('_');
            ticketId = parseInt(parts[1]);
        } else if (item.sku.includes('_')) {
            const parts = item.sku.split('_');
            let foundId: number | null = null;
            for (let i = parts.length - 1; i >= 0; i--) {
                const seg = parts[i];
                if (/^\d+$/.test(seg)) { foundId = parseInt(seg); break; }
            }
            ticketId = foundId ?? parseInt(parts[parts.length - 1]);
        } else {
            ticketId = parseInt(item.sku);
        }

        if (isNaN(ticketId)) {
            return;
        }

        if (!byTicket.has(ticketId)) {
            byTicket.set(ticketId, { ticketId, qty: 0, addons: [], cookout: undefined });
        }

        if (item.sku === ticketId.toString()) {
            byTicket.get(ticketId)!.qty += item.quantity;
        }
    });

    cart.value.items.forEach(item => {
        let ticketId: number;
        if (item.sku.startsWith('tbl_')) {
            const parts = item.sku.split('_');
            ticketId = parseInt(parts[1]);
        } else if (item.sku.includes('_')) {
            const parts = item.sku.split('_');
            let foundId: number | null = null;
            for (let i = parts.length - 1; i >= 0; i--) {
                const seg = parts[i];
                if (/^\d+$/.test(seg)) { foundId = parseInt(seg); break; }
            }
            ticketId = foundId ?? parseInt(parts[parts.length - 1]);
        } else {
            ticketId = parseInt(item.sku);
        }
        const target = byTicket.get(ticketId);
        if (!target) return;

        if (item.sku.startsWith('tbl_')) {
            const section = item.sku.split('_')[2] || '-';
            const existing = target.addons.find(a => a.category === 'table' && a.section === section);
            if (existing) {
                existing.quantity += item.quantity;
            } else {
                target.addons.push({ category: 'table', section, quantity: item.quantity });
            }
            return;
        }

        const categoryKeys = ['mixdrink', 'wine', 'beer', 'water', 'softdrink', 'bottles'];
        const parts = item.sku.split('_');
        let typeIdx = -1;
        let typeKey = '';
        for (let i = 0; i < parts.length; i++) {
            const p = parts[i].toLowerCase();
            const hit = categoryKeys.find(k => p === k);
            if (hit) { typeIdx = i; typeKey = hit; break; }
        }
        if (typeIdx !== -1) {
            let idIdx = -1;
            for (let i = parts.length - 1; i > typeIdx; i--) {
                if (/^\d+$/.test(parts[i])) { idIdx = i; break; }
            }
            const name = idIdx > typeIdx + 1 ? parts.slice(typeIdx + 1, idIdx).join('_') : parts.slice(typeIdx + 1, parts.length - 1).join('_');
            const normalizedName = name.replace(/_/g, ' ').trim();
            const categoryMap: Record<string, string> = {
                mixdrink: 'mixDrinks',
                wine: 'wines',
                beer: 'beers',
                water: 'waters',
                softdrink: 'softDrinks',
                bottles: 'bottles',
            };
            const categoryKey = categoryMap[typeKey];
            const addonIdFromCart = (item as any).addon_id as number | undefined;
            const addon_id = addonIdFromCart ?? getDrinkAddonId(ticketId, categoryKey, normalizedName);
            const section = (item as any).section || '-';
            const matcher = (a: any) => (
                (addon_id !== null ? a.addon_id === addon_id : (a.name?.toLowerCase?.() === normalizedName.toLowerCase() && a.category === categoryKey))
                && a.section === section
            );
            const existing = target.addons.find(matcher);
            if (existing) {
                existing.quantity += item.quantity;
            } else {
                const payload: any = { name: normalizedName, category: categoryKey, quantity: item.quantity, section };
                if (addon_id !== null) payload.addon_id = addon_id;
                target.addons.push(payload);
            }
            return;
        }
    });

    const resultMap = new Map<number, BackendItem>();

    Array.from(byTicket.values()).forEach(it => {
        const sel = cookoutSelections.value[it.ticketId];
        const hasCookout = Boolean(
            isCookoutTicketEnabled(it.ticketId)
            && sel
            && (
                Boolean(sel.includedProtein)
                || Object.values(sel.proteins || {}).some(v => Number(v) > 0)
                || Object.values(sel.drinks || {}).some(v => Number(v) > 0)
                || Object.values(sel.manualAddons || {}).some(v => Number(v) > 0)
            )
        );
        if (hasCookout) {
            const proteins: Array<{ name: string; qty: number }> = [];
            Object.entries(sel?.proteins || {}).forEach(([name, qty]) => {
                const q = Number(qty || 0);
                if (q > 0) proteins.push({ name, qty: q });
            });
            const drinks: Array<{ name: string; qty: number }> = [];
            Object.entries(sel?.drinks || {}).forEach(([name, qty]) => {
                const q = Number(qty || 0);
                if (q > 0) drinks.push({ name, qty: q });
            });
            const manualAddons: Array<{ name: string; qty: number }> = [];
            Object.entries(sel?.manualAddons || {}).forEach(([name, qty]) => {
                const q = Number(qty || 0);
                if (q > 0) manualAddons.push({ name, qty: q });
            });
            it.cookout = {
                includedProtein: sel?.includedProtein || '',
                proteins,
                drinks,
                manualAddons
            };
        }

        const wsel = wellnessSelections.value[it.ticketId];
        const hasWellness = Boolean(
            wsel
            && (
                Boolean(wsel.includedService)
                ||
                Boolean(wsel.selectedSlot)
                ||
                (wsel.serviceMode === 'mobile')
                ||
                Boolean((wsel.contactPhone || '').trim())
                ||
                Object.values(wsel.services || {}).some(v => Number(v) > 0)
                || Object.values(wsel.manualAddons || {}).some(v => Number(v) > 0)
            )
        );
        if (hasWellness) {
            const services: Array<{ name: string; qty: number }> = [];
            Object.entries(wsel?.services || {}).forEach(([name, qty]) => {
                const q = Number(qty || 0);
                if (q > 0) services.push({ name, qty: q });
            });
            const manualAddons: Array<{ name: string; qty: number }> = [];
            Object.entries(wsel?.manualAddons || {}).forEach(([name, qty]) => {
                const q = Number(qty || 0);
                if (q > 0) manualAddons.push({ name, qty: q });
            });
            it.wellness = { 
                includedService: wsel?.includedService || '', 
                selectedSlot: wsel?.selectedSlot || null,
                selectedSlotDate: wsel?.selectedSlotDate || null,
                holdExpiresAt: wsel?.holdExpiresAt || null,
                serviceMode: wsel?.serviceMode || 'inhouse',
                contactPhone: wsel?.contactPhone || '',
                services, 
                manualAddons 
            };
        }

        if (it.qty > 0 || (it.addons && it.addons.length > 0) || hasCookout || hasWellness) {
            if (resultMap.has(it.ticketId)) {
                const existing = resultMap.get(it.ticketId)!;
                existing.qty = Math.max(existing.qty, it.qty);
                if (!existing.cookout && it.cookout) existing.cookout = it.cookout;
                if (!existing.wellness && it.wellness) existing.wellness = it.wellness;
                it.addons.forEach(newAddon => {
                    const existingAddon = existing.addons.find(a =>
                        a.category === newAddon.category &&
                        a.section === newAddon.section &&
                        (newAddon.addon_id ? a.addon_id === newAddon.addon_id : a.name === newAddon.name)
                    );
                    if (existingAddon) {
                        existingAddon.quantity += newAddon.quantity;
                    } else {
                        existing.addons.push(newAddon);
                    }
                });
            } else {
                resultMap.set(it.ticketId, { ...it });
            }
        }
    });

    const result = Array.from(resultMap.values()).map(it => {
        if (it.qty === 0 && ((it.addons && it.addons.length > 0) || it.cookout)) {
            it.qty = 1;
        }
        return it;
    });

    return result;
};

const payFromWallet = () => {
    if (cart.value.items.length === 0) {
        errorDialog.value = {
            open: true,
            title: "No ticket selected",
            description: "Please select at least one ticket",
        };
        return;
    }

    const wellnessCheck = validateWellnessServiceSelection();
    if (!wellnessCheck.ok) {
        errorDialog.value = {
            open: true,
            title: "Service Required",
            description: wellnessCheck.message || "Please highlight service",
        };
        return;
    }

    const cookoutCheck = validateCookoutServiceSelection();
    if (!cookoutCheck.ok) {
        errorDialog.value = {
            open: true,
            title: "Service Required",
            description: cookoutCheck.message || "Please highlight service",
        };
        return;
    }

    Object.assign(form, {
        items: prepareCartForBackend(),
        appliedCoupons: appliedCouponCode.value ? [appliedCouponCode.value] : []
    });

    form.post(route("frontend.user.wallet.buy-ticket"), {
        onError: (errors) => {
            errorDialog.value = {
                open: true,
                title: "Payment Error",
                description: errors.cart || "Failed to process wallet payment",
            };
        },
    });
};

const payFromStripe = () => {
    if (cart.value.items.length === 0) {
        errorDialog.value = {
            open: true,
            title: "No ticket selected",
            description: "Please select at least one ticket",
        };
        return;
    }

    const wellnessCheck = validateWellnessServiceSelection();
    if (!wellnessCheck.ok) {
        errorDialog.value = {
            open: true,
            title: "Service Required",
            description: wellnessCheck.message || "Please highlight service",
        };
        return;
    }

    const cookoutCheck = validateCookoutServiceSelection();
    if (!cookoutCheck.ok) {
        errorDialog.value = {
            open: true,
            title: "Service Required",
            description: cookoutCheck.message || "Please highlight service",
        };
        return;
    }

    const backendData = prepareCartForBackend();

    Object.assign(form, {
        items: backendData,
        appliedCoupons: appliedCouponCode.value ? [appliedCouponCode.value] : []
    });

    form.post(route("frontend.stripe.buy-ticket"), {
        onError: (errors) => {
            console.error("Stripe payment errors:", errors);
            errorDialog.value = {
                open: true,
                title: "Payment Error",
                description: errors.payment || errors.items || "Failed to initiate payment",
            };
        },
        onSuccess: () => {
        },
    });
};
onMounted(() => {
    document.addEventListener('click', (e) => {
        const target = e.target as HTMLElement;

        if (target.matches('[data-act="inc"]')) {
            const ticketElement = target.closest('.ticket') as HTMLElement;
            if (ticketElement) {
                const sku = ticketElement.dataset.sku;
                if (sku) {
                    const currentQty = cartItems.value[sku]?.quantity || 0;
                    const validation = canIncreaseQuantity(sku, currentQty);
                    if (!validation.allowed) {
                        toast.error(validation.message || 'Cannot increase quantity');
                        return;
                    }
                    updateTicketQuantity(sku, 'inc');
                }
            }
        }

        if (target.matches('[data-act="dec"]')) {
            e.preventDefault();
            const button = target;
            const qtySpan = button.parentElement?.querySelector('[data-role="qty"]') as HTMLElement;
            const ticket = button.closest('.ticket') as HTMLElement;
            const sku = ticket?.getAttribute('data-sku');

            if (sku && qtySpan) {
                const currentQty = parseInt(qtySpan.textContent || '0');
                if (currentQty > 0) {
                    updateTicketQuantity(sku, 'dec');
                }
            }
        }
    });

    document.addEventListener('change', (e) => {
        const target = e.target as HTMLElement;
        if (target.matches('[data-role="section"]')) {
            const select = target as HTMLSelectElement;
            const ticket = select.closest('.ticket') as HTMLElement;
            const section = select.value;

            const currentSku = ticket.getAttribute('data-sku');
            if (currentSku && currentSku.startsWith('tbl_')) {
                const ticketId = currentSku.split('_')[1];
                const newSku = `tbl_${ticketId}_${section}`;
                ticket.setAttribute('data-sku', newSku);
            }
        }
    });
});

onMounted(() => {
    loadMap();
    loadReviews();
});

const loadMap = () => {
    const lat = event.latitude;
    const lon = event.longtitude;
    
    // Parse coordinates if they're in DMS format
    let parsedLat = lat;
    let parsedLon = lon;
    
    if (typeof lat === 'string' && lat.includes('°')) {
        // Simple DMS to decimal conversion for latitude
        const latMatch = lat.match(/([+-]?\d+\.?\d*)°?\s*([NS])/);
        if (latMatch) {
            const degrees = parseFloat(latMatch[1]);
            const direction = latMatch[2];
            parsedLat = direction === 'S' ? -degrees : degrees;
        }
    }
    
    if (typeof lon === 'string' && lon.includes('°')) {
        // Simple DMS to decimal conversion for longitude
        const lonMatch = lon.match(/([+-]?\d+\.?\d*)°?\s*([EW])/);
        if (lonMatch) {
            const degrees = parseFloat(lonMatch[1]);
            const direction = lonMatch[2];
            parsedLon = direction === 'W' ? -degrees : degrees;
        }
    }
    
    const numLat = parseFloat(parsedLat);
    const numLon = parseFloat(parsedLon);
    
    if (!isNaN(numLat) && !isNaN(numLon)) {
        const map = L.map('map').setView([numLat, numLon], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        L.marker([numLat, numLon]).addTo(map);
    }
}

const isVideo = (filename: string): boolean => {
    if (!filename) return false;
    const videoExtensions = ['.mp4', '.webm', '.ogg', '.avi', '.mov', '.wmv', '.flv', '.mkv'];
    const extension = filename.toLowerCase().substring(filename.lastIndexOf('.'));
    return videoExtensions.includes(extension);
};

</script>
<template>
    <AuthenticatedLayout>
        <div
            style="background: #f7fafc; font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; color: #0f172a; border-radius: 10px;">
            <div class="max-w-7xl mx-auto px-7 py-7">
                <!-- Event Poster Section -->
                <section class="poster mb-6">
                    <div class="media">
                        <!-- Fallback to image_url or placeholder -->
                        <img :src="event.image_url || '/assets/images/placeholder-event.jpg'" class="w-full h-full"
                            style="object-fit: cover; aspect-ratio: 16/9; border-radius: 0;">
                    </div>
                </section>

                <!-- Event Header -->
                <section class="event-header mb-6">
                    <div class="header-left">
                        <h1 class="title">{{ event.title }}</h1>

                        <!-- Event Date and Time based on event_type -->
                        <div class="sub when">
                            <!-- Single Event -->
                            <div v-if="event?.event_details?.event_type === 'single'">
                                <div v-if="event.event_details.single_event_date">
                                    {{ moment(event.event_details.single_event_date).format("DD MMM YYYY") }}
                                </div>
                                <div v-if="event.event_details.single_start_time || event.event_details.single_end_time"
                                    class="time-info">
                                    {{ event.event_details.single_start_time ?
                                        moment(event.event_details.single_start_time, 'HH:mm').format('h:mm A') : '' }}
                                    {{ event.event_details.single_end_time ? ' - ' +
                                        moment(event.event_details.single_end_time, 'HH:mm').format('h:mm A') : '' }}
                                </div>
                            </div>

                            <!-- Tables Modal (design only) -->
                            <div v-if="showTablesModal && tablesTicket" class="drinks-modal" role="dialog"
                                aria-modal="true">
                                <div class="drinks-modal-backdrop" @click="closeTablesModal"></div>
                                <div class="drinks-modal-panel">
                                    <header class="drinks-modal-header">
                                        <h4>🪑 Add Table for {{ tablesTicket?.name }}</h4>
                                        <button class="close" @click="closeTablesModal">Close</button>
                                    </header>
                                    <main class="drinks-modal-body">
                                        <div class="group-title">Table</div>
                                        <article class="ticket ticket--table"
                                            :data-sku="`tbl_${tablesTicket.id}_${tablesSelectedSection}`"
                                            :data-section="tablesSelectedSection">
                                            <div class="row">
                                                <div class="name">Table for {{ tablesTicket.table_capacity }} <span
                                                        class="badge badge-table">Table</span></div>
                                                <div v-if="getAvailableQuantity(`tbl_${tablesTicket.id}_${tablesSelectedSection}`) === 0"
                                                    class="out-of-stock badge-red">
                                                    <span>Out of Stock</span>
                                                </div>
                                                <div v-else>
                                                    <div v-if="Number(tablesTicket.table_price ?? 0) > 0" class="price"
                                                        :data-price="tablesTicket.table_price">{{
                                                            tablesTicket.table_price }}</div>
                                                    <div v-else class="free-ticket badge-green"><span>Free</span></div>
                                                </div>
                                            </div>
                                            <div class="inc muted">Includes seating for {{ tablesTicket.table_capacity
                                                }} people</div>
                                            <div class="row" style="margin-top:8px;">
                                                <label class="muted">Section
                                                    <select class="sel" v-model="tablesSelectedSection"
                                                        data-role="section">
                                                        <option value="Main Floor">Main Floor</option>
                                                        <option value="Balcony">Balcony</option>
                                                        <option value="Poolside">Poolside</option>
                                                    </select>
                                                </label>
                                                <span class="pill">Capacity: {{ tablesTicket.table_capacity }} •
                                                    Available: {{
                                                        getAvailableQuantity(`tbl_${tablesTicket.id}_${tablesSelectedSection}`)
                                                    }}</span>
                                            </div>

                                            <div style="margin-top:8px;" v-if="tablesTicket?.sections.length > 0">
                                                <label>Table Seating:</label>
                                                <span v-for="seat in tablesTicket.sections" :key="seat?.id">
                                                    Seat: {{ seat?.name }} ,
                                                </span>
                                            </div>
                                            <div class="perforation"></div>
                                            <div class="row">
                                                <div class="qty">
                                                    <button data-act="dec" aria-label="Decrease quantity">–</button>
                                                    <span data-role="qty">{{
                                                        getCartQuantity(`tbl_${tablesTicket.id}_${tablesSelectedSection}`)
                                                        }}</span>
                                                    <button data-act="inc" aria-label="Increase quantity"
                                                        :disabled="getAvailableQuantity(`tbl_${tablesTicket.id}_${tablesSelectedSection}`) === 0">+</button>
                                                </div>
                                            </div>
                                        </article>
                                        <!-- Show included drinks from ticket when a table is selected -->
                                        <div v-if="getCartQuantity(`tbl_${tablesTicket.id}_${tablesSelectedSection}`) > 0"
                                            class="package-contents" style="margin-top:12px;">
                                            <div v-if="Array.isArray(tablesTicket.main_bottles) && tablesTicket.main_bottles.length > 0"
                                                class="package-section">
                                                <h5 class="package-category">🍹 Main Bottles</h5>
                                                <ul class="package-items">
                                                    <li v-for="(b, idx) in tablesTicket.main_bottles" :key="`mb-${idx}`"
                                                        class="">
                                                        <span>{{ (b.qty ?? b.quantity ?? b.count ?? 1) }} × {{ b.name ??
                                                            b.title ?? b.label ?? b }}</span>
                                                        <span class="qty inline-flex items-center gap-2">
                                                            <button class="btn btn-xs"
                                                                @click="(e) => { e.preventDefault(); decIncluded(tablesTicket.id, 'main_bottles', b, tablesSelectedSection); }">–</button>
                                                            <span>{{ getIncludedQtyInCart(tablesTicket.id,
                                                                'main_bottles', String(b.name ?? b.title ?? b.label ??
                                                                    b), tablesSelectedSection) }}</span>
                                                            <button class="btn btn-xs"
                                                                @click="(e) => { e.preventDefault(); incIncluded(tablesTicket.id, 'main_bottles', b, tablesSelectedSection); }">+</button>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div v-if="Array.isArray(tablesTicket.chasers_or_mixers) && tablesTicket.chasers_or_mixers.length > 0"
                                                class="package-section">
                                                <h5 class="package-category">🥤 Chasers / Mixers</h5>
                                                <ul class="package-items">
                                                    <li v-for="(c, idx) in tablesTicket.chasers_or_mixers"
                                                        :key="`cm-${idx}`" class="">
                                                        <span>{{ (c.qty ?? c.quantity ?? c.count ?? 1) }} × {{ c.name ??
                                                            c.title ?? c.label ?? c }}</span>
                                                        <span class="qty inline-flex items-center gap-2">
                                                            <button class="btn btn-xs"
                                                                @click="(e) => { e.preventDefault(); decIncluded(tablesTicket.id, 'chasers_or_mixers', c, tablesSelectedSection); }">–</button>
                                                            <span>{{ getIncludedQtyInCart(tablesTicket.id,
                                                                'chasers_or_mixers', String(c.name ?? c.title ?? c.label
                                                                    ?? c), tablesSelectedSection) }}</span>
                                                            <button class="btn btn-xs"
                                                                @click="(e) => { e.preventDefault(); incIncluded(tablesTicket.id, 'chasers_or_mixers', c, tablesSelectedSection); }">+</button>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div v-if="Array.isArray(tablesTicket.water_options) && tablesTicket.water_options.length > 0"
                                                class="package-section">
                                                <h5 class="package-category">💧 Water Options</h5>
                                                <ul class="package-items">
                                                    <li v-for="(w, idx) in tablesTicket.water_options"
                                                        :key="`wo-${idx}`" class="">
                                                        <span>{{ (w.qty ?? w.quantity ?? w.count ?? 1) }} × {{ w.name ??
                                                            w.title ?? w.label ?? w }}</span>
                                                        <span class="qty inline-flex items-center gap-2">
                                                            <button class="btn btn-xs"
                                                                @click="(e) => { e.preventDefault(); decIncluded(tablesTicket.id, 'water_options', w, tablesSelectedSection); }">–</button>
                                                            <span>{{ getIncludedQtyInCart(tablesTicket.id,
                                                                'water_options', String(w.name ?? w.title ?? w.label ??
                                                                    w), tablesSelectedSection) }}</span>
                                                            <button class="btn btn-xs"
                                                                @click="(e) => { e.preventDefault(); incIncluded(tablesTicket.id, 'water_options', w, tablesSelectedSection); }">+</button>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </main>
                                    <footer class="drinks-modal-footer">
                                        <button class="btn btn-outline" @click="closeTablesModal">Done</button>
                                    </footer>
                                </div>
                            </div>

                            <!-- Package Modal (design only) -->
                            <div v-if="showPackageModal && packageTicket && packageTicket.drink_package"
                                class="drinks-modal" role="dialog" aria-modal="true">
                                <div class="drinks-modal-backdrop" @click="closePackageModal"></div>
                                <div class="drinks-modal-panel">
                                    <header class="drinks-modal-header">
                                        <h4>📦 Package: {{ packageTicket.drink_package.name }}</h4>
                                        <button class="close" @click="closePackageModal">Close</button>
                                    </header>
                                    <main class="drinks-modal-body">
                                        <article class="ticket package-display"
                                            :class="packageTicket.has_table === 'yes' ? 'ticket--table' : 'ticket--base'">
                                            <div class="row" v-if="packageTicket.has_table === 'yes'">
                                                <div class="name">Table for {{ packageTicket.table_capacity }} <span
                                                        class="badge badge-table">Table</span></div>
                                                <div v-if="Number(packageTicket.table_price ?? 0) > 0" class="price"
                                                    :data-price="packageTicket.table_price">{{ packageTicket.table_price
                                                    }}</div>
                                                <div v-else-if="Number(packageTicket.price ?? 0) > 0"
                                                    class="free-ticket badge-green"><span>Free</span></div>
                                                <div v-else class="free-ticket badge-green"><span>Free</span></div>
                                            </div>
                                            <div class="package-contents">
                                                <div v-if="packageTicket.drink_package.bottles && packageTicket.drink_package.bottles.length > 0"
                                                    class="package-section">
                                                    <h5 class="package-category">🍹 Main Bottles</h5>
                                                    <ul class="package-items">
                                                        <li v-for="bottle in packageTicket.drink_package.bottles"
                                                            :key="bottle.id">{{ bottle.qty }} × {{ bottle.name }}</li>
                                                    </ul>
                                                </div>
                                                <div v-if="packageTicket.drink_package.chasers && packageTicket.drink_package.chasers.length > 0"
                                                    class="package-section">
                                                    <h5 class="package-category">🥤 Chasers / Mixers</h5>
                                                    <ul class="package-items">
                                                        <li v-for="chaser in packageTicket.drink_package.chasers"
                                                            :key="chaser.id">{{ chaser.qty }} × {{ chaser.name }}</li>
                                                    </ul>
                                                </div>
                                                <div v-if="packageTicket.drink_package.waters && packageTicket.drink_package.waters.length > 0"
                                                    class="package-section">
                                                    <h5 class="package-category">💧 Water Options</h5>
                                                    <ul class="package-items">
                                                        <li v-for="water in packageTicket.drink_package.waters"
                                                            :key="water.id">{{ water.qty }} × {{ water.name }}</li>
                                                    </ul>
                                                </div>

                                                <div v-if="packageTicket?.sections && packageTicket.sections.length > 0"
                                                    class="package-section" style="margin-top:8px;">
                                                    <h5 class="package-category">Table Seating Section</h5>
                                                    <ul class="package-items">
                                                        <li v-for="section in packageTicket?.sections"
                                                            :key="section?.id">Seat: {{
                                                                section?.name }}</li>
                                                    </ul>
                                                </div>

                                                <div v-if="packageTicket.drink_package.notes" class="package-notes"
                                                    style="margin-top:8px;">
                                                    <p class="muted"><strong>📝 Notes:</strong> {{
                                                        packageTicket.drink_package.notes }}</p>
                                                </div>
                                            </div>
                                        </article>
                                    </main>
                                    <footer class="drinks-modal-footer">
                                        <button class="btn btn-outline" @click="closePackageModal">Done</button>
                                    </footer>
                                </div>
                            </div>
                            <!-- Recurring Event -->
                            <div v-else-if="event?.event_details?.event_type === 'recurring'">
                                <div class="recurring-info">
                                    <div v-if="event.event_details.recurr_start_date && event.event_details.recurr_end_date"
                                        class="date-range">
                                        {{ moment(event.event_details.recurr_start_date).format("DD MMM YYYY") }}
                                        - {{ moment(event.event_details.recurr_end_date).format("DD MMM YYYY") }}
                                    </div>
                                </div>
                            </div>

                            <!-- Fallback to original format if event_type is not set -->
                            <div v-else>
                                {{ event.start_time ? moment(event.start_time).format("DD MMM YYYY") : '' }}
                                {{ event.end_time ? ' - ' + moment(event.end_time).format('h:mm A') : '' }}
                            </div>
                        </div>
                        <div class="chips" aria-label="Audiences">
                            <span class="chip" v-for="audience in event?.event_details?.audiences" :key="audience.id">{{
                                audience }}</span>
                        </div>
                        <div class="socials" style="margin-top:12px">
                            <a :href="`${event?.event_details?.twitter}`" class="btn btn-ghost"
                                v-if="event?.event_details?.twitter">X / Twitter</a>
                            <a :href="`${event?.event_details?.instagram}`" class="btn btn-ghost"
                                v-if="event?.event_details?.instagram">Instagram</a>
                            <a :href="`${event?.event_details?.facebook}`" class="btn btn-ghost"
                                v-if="event?.event_details?.facebook">Facebook</a>
                            <a :href="`${event?.event_details?.tiktok}`" class="btn btn-ghost"
                                v-if="event?.event_details?.tiktok">TikTok</a>
                            <a :href="`${event?.event_details?.linkedin}`" class="btn btn-ghost"
                                v-if="event?.event_details?.linkedin">LinkedIn</a>
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="kpis" aria-label="Event KPIs">
                            <div class="kpi">
                                <div class="k">Event Type</div>
                                <div class="v">{{ event.type }}</div>
                            </div>
                            <div class="kpi">
                                <div class="k">Going</div>
                                <div class="v">{{ attendees?.length }}</div>
                            </div>
                            <div class="kpi">
                                <div class="k">Attendees List</div>
                                <div class="v">{{ event?.event_details?.attendees ? 'Shown' : 'Hidden' }}</div>
                            </div>
                            <div class="kpi">
                                <div class="k">Reviews</div>
                                <div class="v">{{ event?.event_details?.enable_views ? 'Enabled' : 'Disabled' }}</div>
                            </div>
                        </div>
                        <div class="cta">
                            <button @click="openTicketModal"
                                class="btn btn-primary btn-buy-big mx-3">
                                <span class="pulse"></span> Buy Tickets
                            </button>
                            <button @click="toggleFavorite" class="btn btn-ghost mx-3">Save</button>
                            <button @click="copyLink" class="btn btn-ghost mx-3">Share</button>
                        </div>
                    </div>
                </section>

                <!-- Main Content Grid -->
                <section class="grid-2">
                    <main class="stack">
                        <!-- About Event -->
                        <div class="card">
                            <h3 style="margin:0 0 8px">About Event</h3>
                            <div v-html="event.description || 'No description available for this event.'"
                                class="card-content"></div>
                        </div>
                        <!-- Disclaimer Event -->
                        <div class="card">
                            <h3 style="margin:0 0 8px">Disclaimer</h3>
                            <div v-html="event.disclaimer || 'No disclaimer available for this event.'"
                                class="card-content"></div>
                        </div>

                        <!-- Media Gallery - Show images and videos -->
                        <div class="card">
                            <h3 style="margin:0 0 10px">Media Gallery</h3>
                            <div v-if="!event?.event_details?.image_gallery?.length">
                                <p>No media available for this event.</p>
                            </div>
                            <div v-else class="gallery grid grid-cols-2 gap-2">
                                <div v-for="(mediaItem, index) in event?.event_details?.image_gallery" :key="index">
                                    <!-- Video -->
                                    <video v-if="isVideo(mediaItem)" :src="`${appurl}${mediaItem}`" controls
                                        class="w-full h-32 object-cover rounded-lg shadow-sm cursor-pointer"
                                        style="background: #000;"
                                        @click="openPreview(mediaItem, 'video', 'Event media')" loading="lazy">
                                        Your browser does not support the video tag.
                                    </video>
                                    <!-- Image -->
                                    <img v-else :src="`${appurl}${mediaItem}`" alt="Event media" loading="lazy"
                                        class="w-full h-32 object-cover rounded-lg shadow-sm cursor-pointer"
                                        @click="openPreview(mediaItem, 'image', 'Event media')" />
                                </div>
                            </div>
                        </div>


                        <!-- Artists / Speakers - Only show if artists exist -->
                        <div v-if="!hideArtistsAndCoupons && event?.event_details?.artists?.length" class="card">
                            <h3 style="margin:0 0 8px">Artists / Speakers</h3>
                            <div class="list">
                                <div v-for="(artist, index) in event?.event_details?.artists" :key="artist.id || index"
                                    class="artist">
                                    <div class="avatar bg-gray-300">
                                        <img v-if="event?.event_details?.artist_image?.[index]"
                                            :src="`${appurl}${event.event_details.artist_image[index]}`" alt="Artist"
                                            loading="lazy" style="border-radius: 50%;height: 102%;">
                                        <img v-else :src="`${appurl}images/defaultuser.jpg`" alt="Artist" loading="lazy"
                                            style="border-radius: 50%;height: 102%;">
                                    </div>
                                    <div>
                                        <b>{{ artist }}</b>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sponsors - Only show if sponsors exist -->
                        <div class="card">
                            <h3 style="margin:0 0 8px">Sponsors</h3>
                            <div v-if="!event?.sponsors || !event.sponsors.length">
                                <p>No sponsors available for this event.</p>
                            </div>
                            <div v-else class="sponsors">
                                <div v-for="(sponsor, index) in event?.sponsors" :key="index" class="sponsor"
                                    :data-description="sponsor.description || 'No description available'">
                                    <video v-if="isSponsorVideo(sponsor.image_object)"
                                        :src="`${appurl}${sponsor.image_object}`"
                                        class="w-full h-full object-cover rounded" controls loading="lazy"></video>
                                    <img v-else :src="`${appurl}${sponsor.image_object}`" :alt="sponsor.name"
                                        class="w-full h-full object-cover rounded" loading="lazy" />
                                    <small>{{ sponsor.name }} • Sponsor</small>
                                </div>
                            </div>
                        </div>

                        <!-- Coupons - Only show if coupons exist -->
                        <div v-if="!hideArtistsAndCoupons && event?.coupons?.length" class="card">
                            <h3 style="margin:0 0 8px">Coupons</h3>
                            <div class="coupon-list flex flex-wrap gap-3">
                                <div v-for="(coupon, index) in event?.coupons" :key="index"
                                    class="coupon flex items-center gap-2 p-2 border rounded-lg">
                                    <img :src="`${appurl}${coupon.image_object}`" alt="Coupon" width="48" height="48"
                                        class="rounded-lg object-cover" loading="lazy">
                                    <div>
                                        <b>{{ coupon.discount }}</b>
                                        <div class="sub text-sm text-gray-500">{{ coupon.discount_type }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reviews Section -->
                        <div v-if="event?.event_details?.enable_views" class="card">
                            <h3 style="margin:0 0 12px">Reviews & Ratings</h3>

                            <!-- Rating Summary -->
                            <div class="rating-summary">
                                <div class="rating-display">
                                    <div class="rating-big">{{ averageRating ? averageRating.toFixed(1) : '0.0' }}</div>
                                    <div class="rating-stars">
                                        <span v-for="star in 5" :key="star" class="star"
                                            :class="{ 'filled': star <= Math.round(averageRating) }">★</span>
                                    </div>
                                    <div class="sub">{{ reviewsCount }} review{{ reviewsCount !== 1 ? 's' : '' }}</div>
                                </div>
                            </div>

                            <!-- Review Form -->
                            <form @submit.prevent="submitReview" class="review-form" v-if="!hasUserReviewed">
                                <h4>Leave a Review</h4>
                                <div class="form-group">
                                    <label>Rating</label>
                                    <div class="star-picker">
                                        <button type="button" v-for="star in 5" :key="star" class="star-btn"
                                            :class="{ 'active': star <= selectedRating }"
                                            @click="selectedRating = star">
                                            ★
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Your Name</label>
                                    <input v-model="reviewForm.reviewer_name" type="text" placeholder="Enter your name"
                                        required class="form-input" />
                                </div>
                                <div class="form-group">
                                    <label>Review</label>
                                    <textarea v-model="reviewForm.review_text" rows="4"
                                        placeholder="Share your experience..." required
                                        class="form-textarea"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary"
                                    :disabled="reviewForm.processing || selectedRating === 0">
                                    {{ reviewForm.processing ? 'Submitting...' : 'Submit Review' }}
                                </button>
                            </form>

                            <!-- Already Reviewed Message -->
                            <div v-if="hasUserReviewed" class="already-reviewed">
                                <p>✓ Thank you for your review!</p>
                            </div>

                            <!-- Reviews List -->
                            <div class="reviews-list" v-if="reviews.length > 0">
                                <h4>Latest Reviews ({{ Math.min(reviews.length, 5) }} of {{ reviewsCount }})</h4>
                                <div v-for="review in reviews" :key="review.id" class="review-item">
                                    <!-- Normal Review Display -->
                                    <div v-if="editingReviewId !== review.id">
                                        <div class="review-header">
                                            <div class="reviewer-info">
                                                <strong>{{ review.reviewer_name }}</strong>
                                                <div class="review-stars">
                                                    <span v-for="star in 5" :key="star" class="star"
                                                        :class="{ 'filled': star <= review.rating }">★</span>
                                                </div>
                                            </div>
                                            <div class="review-actions">
                                                <div class="review-date">{{ review.created_at }}</div>
                                                <div class="action-buttons">
                                                    <button v-if="review.can_edit" @click="startEditReview(review)"
                                                        class="edit-btn" title="Edit Review">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2">
                                                            <path
                                                                d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                            <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                        </svg>
                                                    </button>
                                                    <button v-if="review.can_delete"
                                                        @click="deleteReview(review.id, review.reviewer_name)"
                                                        class="delete-btn" title="Delete Review">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2">
                                                            <polyline points="3,6 5,6 21,6" />
                                                            <path
                                                                d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2" />
                                                            <line x1="10" y1="11" x2="10" y2="17" />
                                                            <line x1="14" y1="11" x2="14" y2="17" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="review-text" v-html="review.review_text"></div>
                                    </div>

                                    <!-- Edit Review Form -->
                                    <div v-else class="edit-review-form">
                                        <h5>Edit Your Review</h5>
                                        <div class="form-group">
                                            <label>Rating</label>
                                            <div class="star-picker">
                                                <button type="button" v-for="star in 5" :key="star" class="star-btn"
                                                    :class="{ 'active': star <= editSelectedRating }"
                                                    @click="editSelectedRating = star">
                                                    ★
                                                </button>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Your Name</label>
                                            <input v-model="editReviewForm.reviewer_name" type="text"
                                                placeholder="Enter your name" required class="form-input" />
                                        </div>
                                        <div class="form-group">
                                            <label>Review</label>
                                            <textarea v-model="editReviewForm.review_text" rows="4"
                                                placeholder="Share your experience..." required
                                                class="form-textarea"></textarea>
                                        </div>
                                        <div class="edit-actions">
                                            <button @click="updateReview(review.id)" class="btn btn-primary"
                                                :disabled="editReviewForm.processing || editSelectedRating === 0">
                                                {{ editReviewForm.processing ? 'Updating...' : 'Update Review' }}
                                            </button>
                                            <button @click="cancelEditReview" class="btn btn-secondary">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Show More Reviews Link -->
                                <div v-if="reviewsCount > 5" class="show-more-reviews">
                                    <p class="text-sm text-gray-600">
                                        Showing latest 5 reviews. Total: {{ reviewsCount }} reviews.
                                    </p>
                                </div>
                            </div>

                            <!-- No Reviews State -->
                            <div v-else class="no-reviews">
                                <p>No reviews yet. Be the first to review this event!</p>
                            </div>
                        </div>
                    </main>

                    <aside class="stack">
                        <!-- Organizer Card -->
                        <div class="card" id="organizerCard">
                            <div class="organizer">
                                <img class="avatar bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center text-2xl font-bold"
                                    :src="organizerStats?.organizer_avatar || '/assets/images/placeholder-avatar.jpg'"
                                    alt="Organizer" loading="lazy">

                                <div style="flex:1">
                                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                        <h3 style="margin:0">{{ event.organizer?.organizer_name || event.organizer_name
                                            || 'Organizer' }}</h3>
                                        <button id="followBtn" class="follow" aria-pressed="false"
                                            @click="toggleFollow">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                                aria-hidden="true">
                                                <path
                                                    d="M12 21s-6.716-4.297-9.014-7.25C1.052 11.16 2.099 8 5.167 8c1.808 0 2.93 1.01 3.54 2.003C9.317 8.91 10.44 8 12.25 8c3.067 0 4.115 3.16 2.18 5.75C18.716 16.703 12 21 12 21Z"
                                                    stroke="#0b72d2" stroke-width="1.6" fill="#e0f2fe" />
                                            </svg>
                                            <span>{{ isFollowingOrganizer ? 'Following' : 'Follow' }}</span>
                                            <span class="dot" aria-hidden="true"></span>
                                        </button>

                                    </div>
                                    <div class="sub">Organizer • Typically responds within 24h</div>
                                    <div class="followers" id="followerCount">{{ followersCount }}
                                        followers</div>
                                </div>
                            </div>
                            <div class="cta" style="margin-top:12px">
                                <button class="btn btn-ghost">Share</button>
                            </div>
                        </div>

                        <!-- Event Facts -->
                        <div class="card">
                            <h3 style="margin:0 0 8px">Event Facts</h3>
                            <div class="facts">
                                <div class="fact"><b>Event Type</b>
                                    <div>{{ event?.type || 'Public' }}</div>
                                </div>
                                <div class="fact"><b>Seating Plan</b>
                                    <div>{{ event?.event_details?.seating_plan == true ? 'Yes' : 'No' }}</div>
                                </div>
                                <div class="fact" v-if="event?.website"><b>Website</b>
                                    <div><a :href="event?.website" target="_blank">{{ event?.website }}</a></div>
                                </div>
                                <div class="fact" v-if="event?.email"><b>Email</b>
                                    <div>{{ event?.email }}</div>
                                </div>
                                <div class="fact" v-if="event?.phone"><b>Phone</b>
                                    <div>{{ event?.phone }}</div>
                                </div>
                                <div class="fact" v-if="event?.country || event?.city"><b>Location</b>
                                    <div>{{ event?.country }},{{ event?.state }},{{ event?.city }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="card">
                            <h3 style="margin:0 0 8px">Location</h3>
                            <div class="fact" style="margin:0"><b>Venue</b>
                                <div>{{ event?.venue || 'To be announced' }}</div>
                            </div>
                            <div style="margin-top:10px">
                                <div v-if="show_map" id="map"
                                    style="width: 100%;height: 180px;border-radius:12px;z-index: 1;"></div>
                                <div v-else
                                    style="background:#e9f2fb;border:2px solid var(--outline);border-radius:12px;height:180px;display:flex;align-items:center;justify-content:center;color:#084c86">
                                    Map / Venue image placeholder</div>
                            </div>
                            <div class="chips" style="margin-top:10px">
                                <span class="chip">{{ event?.venue ? 'Venue' : 'Online event' }}</span>
                                <span class="chip">{{ event?.venue ? 'In-person' : 'Virtual' }}</span>
                            </div>
                        </div>

                        <!-- Attendees Section -->
                        <div class="card">
                            <h3 style="margin:0 0 8px">Attendees ({{ attendees?.length || 0 }})</h3>
                            <div class="sub">
                                {{ event?.event_details?.attendees ? 'Showing attendees who purchased tickets' :
                                    'Attendee list is hidden for this event' }}
                            </div>

                            <!-- Attendees List -->
                            <div v-if="event?.event_details?.attendees && attendees && attendees.length > 0"
                                class="attendees-section">
                                <!-- Avatar Preview -->
                                <div class="attendees-preview" style="display:flex;gap:8px;margin:12px 0;">
                                    <div v-for="attendee in attendees.slice(0, 6)" :key="attendee.id"
                                        class="attendee-avatar"
                                        :title="`${attendee.name} (${attendee.total_tickets} ticket${attendee.total_tickets > 1 ? 's' : ''})`"
                                        :style="{
                                            backgroundImage: attendee.avatar ? `url(${attendee.avatar})` : 'none',
                                            backgroundSize: 'cover',
                                            backgroundPosition: 'center'
                                        }">
                                        <span v-if="!attendee.avatar" class="avatar-initial">
                                            {{ attendee.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div v-if="attendees.length > 6" class="attendee-avatar more-count">
                                        +{{ attendees.length - 6 }}
                                    </div>
                                </div>
                            </div>

                            <!-- No Attendees State -->
                            <div v-else-if="event?.event_details?.attendees && (!attendees || attendees.length === 0)"
                                class="no-attendees">
                                <div style="display:flex;gap:8px;margin-top:8px">
                                    <div class="attendee-avatar placeholder"></div>
                                    <div class="attendee-avatar placeholder"></div>
                                    <div class="attendee-avatar placeholder"></div>
                                    <div class="attendee-avatar placeholder"></div>
                                </div>
                                <p style="color: #64748b; margin-top: 12px; font-size: 0.9em;">No tickets purchased yet
                                </p>
                            </div>

                            <!-- Hidden Attendees State -->
                            <div v-else class="hidden-attendees">
                                <div style="display:flex;gap:8px;margin-top:8px">
                                    <div class="attendee-avatar placeholder"></div>
                                    <div class="attendee-avatar placeholder"></div>
                                    <div class="attendee-avatar placeholder"></div>
                                    <div class="attendee-avatar placeholder"></div>
                                </div>
                            </div>
                        </div>

                        <!-- More events from organizer -->
                        <div class="card">
                            <h3 style="margin:0 0 8px">More events from this organizer</h3>
                            <Carousel>
                                <CarouselPrevious />
                                <CarouselNext />
                                <CarouselContent>
                                    <CarouselItem v-for="event in otherEvents" :key="event.id"
                                        class="basis-full sm:basis-1/2 md:basis-1/2">
                                        <EventCard :event="event" />
                                    </CarouselItem>
                                </CarouselContent>
                            </Carousel>
                        </div>
                    </aside>
                </section>
            </div>

            <!-- Sticky Buy CTA -->
            <div class="sticky-buy">
                <div class="sticky-inner">
                    <button id="openTicketsSticky" @click="openTicketModal" class="btn btn-primary btn-buy-big">
                        <span class="pulse" aria-hidden="true"></span> Buy Tickets
                    </button>
                </div>
            </div>


        </div>
    </AuthenticatedLayout>
    <!-- Ticket Sidebar/Sheet -->
    <div v-if="showTicketModal" class="ticket-sidebar" role="dialog" aria-modal="true" aria-labelledby="ticketTitle">
        <div class="ticket-sidebar-backdrop" @click="closeTicketModal"></div>
        <div class="ticket-sidebar-panel">
            <header class="ticket-sidebar-header text-center">
                <h3 id="ticketTitle">{{ event.title }}</h3>
                <div>
                    <button @click="closeTicketModal" class="close">Close</button>
                </div>
            </header>
            <main class="ticket-sidebar-main mb-5">
                <!-- LEFT: Ticket selections -->
                <div class="tickets-container mb-5">
                    <div v-if="tickets.length === 0" class="no-tickets-message">
                        <p>No tickets are currently available for this event.</p>
                        <p>Contact the organizer for more information.</p>
                    </div>

                    <div v-else class="tickets-grid mt-5">
                        <div class="section-header tickets">🎟️ Tickets</div>
                        <div v-if="!hideArtistsAndCoupons && event?.coupons?.length" class="w-full">
                            <label for="coupon" class="text-sm text-gray-500 mb-1 block">Coupon Code</label>
                            <div
                                class="flex items-center border border-gray-300 rounded-md px-3 py-2 focus-within:ring-2 focus-within:ring-indigo-500">
                                <input id="coupon" type="text" placeholder="Enter code"
                                    class="flex-1 outline-none bg-transparent text-gray-700 placeholder-gray-400" />
                                <button @click="applyCoupon"
                                    class="text-indigo-500 font-medium hover:text-indigo-600 transition">
                                    Apply
                                </button>
                            </div>
                        </div>
                        <div v-for="ticket in tickets" :key="ticket.id" class="ticket-section">
                            <!-- Admission -->
                            <article class="ticket ticket--base" :data-sku="ticket.id">
                                <div class="row">
                                    <div class="name">
                                        {{ ticket?.name }} <span class="badge badge-ticket">Ticket</span>
                                        <span v-if="isSoldOut(ticket)" class="badge badge-red">Sold Out</span>
                                        <span v-else-if="isSoonGone(ticket)" class="badge">Soon gone</span>
                                    </div>

                                    <!-- If ticket price is 0: show table_price; if both are 0: show Free -->
                                    <template v-if="isSaleEnded(ticket)">
                                        <div class="out-of-stock badge-red"><span>No longer available</span></div>
                                    </template>
                                    <template v-else-if="!ticket.price || Number(ticket.price) === 0">
                                        <div v-if="Number(ticket.table_price ?? 0) > 0" class="price"
                                            :data-price="ticket.table_price">{{ ticket.table_price }}</div>
                                        <div v-else class="free-ticket badge-green"><span>Free</span></div>
                                    </template>
                                    <template v-else>
                                        <div v-if="getAvailableQuantity(ticket.id.toString()) === 0"
                                            class="out-of-stock badge-red">
                                            <span>Sold Out</span>
                                        </div>
                                        <div v-else class="price" :data-price="(Number(ticket.price || 0) - Number(ticket.promo_price || 0) > 0
                                            ? (Number(ticket.price || 0) - Number(ticket.promo_price || 0)).toFixed(2)
                                            : Number(ticket.price || 0).toFixed(2))">
                                            {{ currency }}{{ (Number(ticket.price || 0) - Number(ticket.promo_price || 0) > 0
                                                ? (Number(ticket.price || 0) - Number(ticket.promo_price || 0))
                                                : Number(ticket.price || 0)).toFixed(2) }}
                                        </div>
                                    </template>
                                </div>
                                <div class="inc muted" v-html="ticket.description"></div>
                                <div class="perforation"></div>
                                <div class="row" aria-label="Quantity">
                                    <span class="pill">Type: {{ ticket.type }} • Available: {{
                                        getTicketRemaining(ticket) }}</span>
                                    <div class="qty">
                                        <button data-act="dec" aria-label="Decrease quantity">–</button>
                                        <span data-role="qty">0</span>
                                        <button data-act="inc" aria-label="Increase quantity"
                                            :disabled="getAvailableQuantity(ticket.id.toString()) === 0 || isSaleEnded(ticket)">+</button>
                                    </div>
                                </div>
                                <!-- Open Drinks Modal button (design only) -->
                                <div v-if="getAvailableQuantity(ticket.id.toString()) != 0 && !isSaleEnded(ticket)">
                                    <div class="row"
                                        v-if="!ticket.package_id && ticket.has_table !== 'yes' && ticket.drink_addons && ticket.drink_addons.enabled && hasBaseTicketInCart(ticket.id) && !(ticket.cookout && ticket.cookout.includeFood === 'yes')">
                                        <button class="btn btn-outline btn-sm" @click="openDrinksModal(ticket.id)">🥤
                                            Add Drinks</button>
                                    </div>
                                    <!-- Open Tables Modal button (design only) -->
                                    <div class="row"
                                        v-if="!ticket.package_id && ticket.has_table === 'yes' && hasBaseTicketInCart(ticket.id)">
                                        <button class="btn btn-outline btn-sm" @click="openTablesModal(ticket.id)">🪑
                                            Add Table</button>
                                    </div>
                                    <!-- Open Package Modal button (design only) -->
                                    <div class="row"
                                        v-if="ticket.package_id && ticket.has_table === 'yes' && ticket.drink_package">
                                        <button class="btn btn-outline btn-sm" @click="openPackageModal(ticket.id)">📦
                                            View Package</button>
                                    </div>
                                </div>
                            </article>

                            <div v-if="ticket.cookout && ticket.cookout.includeFood === 'yes' && hasBaseTicketInCart(ticket.id)"
                                class="mt-3 rounded-2xl border border-blue-200/70 bg-gradient-to-b from-white/95 to-blue-50/40 shadow-sm overflow-hidden">
                                <div class="p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                                            <span class="text-orange-600">🔥</span>
                                            Cookout Plate Order
                                        </div>
                                        <span class="pill">Ticket: {{ ticket.name }}</span>
                                    </div>
                                    <div class="text-sm text-slate-500 mt-1">
                                        Base includes one plate. Add-ons update totals automatically.
                                    </div>
                                </div>

                                <div class="h-px bg-slate-200/50"></div>

                                <div class="p-4 space-y-4">
                                    <div v-if="cookoutIncludedProteins(ticket.id).length > 0"
                                        class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="text-lg font-extrabold text-slate-900">Choose Included Plate (1)</div>
                                        </div>
                                        <div class="text-sm text-slate-500 mt-1">Choose exactly one protein for your included plate.</div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                                            <label v-for="p in cookoutIncludedProteins(ticket.id)" :key="`ip-${ticket.id}-${p}`"
                                                class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4 flex items-center gap-3 cursor-pointer">
                                                <input type="radio" :name="`included-protein-${ticket.id}`" class="accent-indigo-600"
                                                    :value="p" :checked="getCookoutIncludedProteinValue(ticket.id) === p"
                                                    @change="() => setCookoutIncludedProtein(ticket.id, p)" />
                                                <div>
                                                    <div class="font-extrabold text-slate-900">{{ p }}</div>
                                                    <div class="text-xs text-slate-500">Included plate</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <div v-if="(getCookoutConfig(ticket.id)?.sides || []).length > 0"
                                        class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                        <div class="font-extrabold text-slate-900">Included Sides (Grab &amp; Go)</div>
                                        <div class="text-sm text-slate-500 mt-1">
                                            {{ (getCookoutConfig(ticket.id)?.sides || []).join(', ') }}
                                        </div>
                                    </div>

                                    <div v-if="cookoutIncludedDrinks(ticket.id).length > 0"
                                        class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                        <div class="font-extrabold text-slate-900">Included Drinks</div>
                                        <div class="text-sm text-slate-500 mt-1">
                                            {{ cookoutIncludedDrinks(ticket.id).join(', ') }}
                                        </div>
                                    </div>

                                    <div v-if="cookoutAddonDrinks(ticket.id).length > 0"
                                        class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                        <div class="flex items-center justify-between gap-3 mb-2">
                                            <div class="text-lg font-extrabold text-slate-900">Add-on Drinks</div>
                                            <span class="pill">Paid</span>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div v-for="d in cookoutAddonDrinks(ticket.id)" :key="`cd-${ticket.id}-${d.name}`"
                                                class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="font-extrabold text-slate-900">
                                                        {{ d.name }}
                                                        <span class="badge badge-drink">Cookout Add-on</span>
                                                    </div>
                                                    <div class="font-extrabold text-slate-900">{{ currency }}{{ Number(d.price || 0).toFixed(2) }}</div>
                                                </div>
                                                <div class="flex items-center justify-between gap-3 mt-3">
                                                    <div class="qty">
                                                        <button type="button" @click="decCookoutDrink(ticket.id, d.name)">–</button>
                                                        <span data-role="qty">{{ getCookoutDrinkQty(ticket.id, d.name) }}</span>
                                                        <button type="button"
                                                            :disabled="d.maxQty !== null && d.maxQty > 0 && getCookoutDrinkQty(ticket.id, d.name) >= d.maxQty"
                                                            @click="incCookoutDrink(ticket.id, d.name)">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="cookoutAddonProteins(ticket.id).length > 0"
                                        class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                        <div class="flex items-center justify-between gap-3 mb-2">
                                            <div class="text-lg font-extrabold text-slate-900">Add-on Proteins</div>
                                            <span class="pill">Paid</span>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div v-for="p in cookoutAddonProteins(ticket.id)" :key="`cp-${ticket.id}-${p.name}`"
                                                class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="font-extrabold text-slate-900">
                                                        {{ p.name }}
                                                        <span class="badge badge-drink">Cookout Add-on</span>
                                                    </div>
                                                    <div class="font-extrabold text-slate-900">{{ currency }}{{ Number(p.price || 0).toFixed(2) }}</div>
                                                </div>
                                                <div class="flex items-center justify-between gap-3 mt-3">
                                                    <span class="pill">Available: {{ p.maxQty === null ? '—' : p.maxQty }}</span>
                                                    <div class="qty">
                                                        <button type="button" @click="decCookoutProtein(ticket.id, p.name)">–</button>
                                                        <span data-role="qty">{{ getCookoutProteinQty(ticket.id, p.name) }}</span>
                                                        <button type="button"
                                                            :disabled="p.maxQty !== null && p.maxQty > 0 && getCookoutProteinQty(ticket.id, p.name) >= p.maxQty"
                                                            @click="incCookoutProtein(ticket.id, p.name)">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="cookoutManualAddons(ticket.id).length > 0"
                                        class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                        <div class="flex items-center justify-between gap-3 mb-2">
                                            <div class="text-lg font-extrabold text-slate-900">Extras (Add-ons)</div>
                                            <span class="pill">Optional</span>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div v-for="a in cookoutManualAddons(ticket.id)" :key="`ca-${ticket.id}-${a.name}`"
                                                class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="font-extrabold text-slate-900">
                                                        {{ a.name }}
                                                        <span class="badge badge-drink">Cookout Extra</span>
                                                    </div>
                                                    <div class="font-extrabold text-slate-900">{{ currency }}{{ Number(a.price || 0).toFixed(2) }}</div>
                                                </div>
                                                <div class="flex items-center justify-between gap-3 mt-3">
                                                    <span class="pill">Available: {{ a.maxQty === null ? '—' : a.maxQty }}</span>
                                                    <div class="qty">
                                                        <button type="button" @click="decCookoutManualAddon(ticket.id, a.name)">–</button>
                                                        <span data-role="qty">{{ getCookoutManualAddonQty(ticket.id, a.name) }}</span>
                                                        <button type="button"
                                                            :disabled="a.maxQty !== null && a.maxQty > 0 && getCookoutManualAddonQty(ticket.id, a.name) >= a.maxQty"
                                                            @click="incCookoutManualAddon(ticket.id, a.name)">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="ticket.drink_addons && ticket.drink_addons.enabled && getDrinkAddonGroups(ticket).length > 0"
                                        class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                        <div class="flex items-center justify-between gap-3 mb-2">
                                            <div class="text-lg font-extrabold text-slate-900">Drinks (Add-ons)</div>
                                            <span class="pill">Optional</span>
                                        </div>

                                        <div v-for="group in getDrinkAddonGroups(ticket)" :key="`co-dg-${ticket.id}-${group.key}`" class="mt-3">
                                            <div class="font-extrabold text-slate-900 text-sm">{{ group.label }}</div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                                                <div v-for="drink in group.items" :key="`co-dr-${ticket.id}-${group.key}-${drink.name}`"
                                                    class="ticket rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4"
                                                    :data-sku="`${group.prefix}_${drink.name}_${ticket.id}`">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="font-extrabold text-slate-900">
                                                            {{ drink.name }}
                                                            <span class="badge badge-drink">Drink Add-on</span>
                                                        </div>
                                                        <div class="font-extrabold text-slate-900">
                                                            {{ Number(drink.cost ?? drink.price ?? 0) > 0 ? `${currency}${Number(drink.cost ?? drink.price ?? 0).toFixed(2)}` : 'Complimentary' }}
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-3 mt-3">
                                                        <span class="pill">Available: {{ drink.available_qty ?? '—' }}</span>
                                                        <div class="qty">
                                                            <button data-act="dec" aria-label="Decrease quantity">–</button>
                                                            <span data-role="qty">{{ getCartQuantity(`${group.prefix}_${drink.name}_${ticket.id}`) }}</span>
                                                            <button data-act="inc" aria-label="Increase quantity">+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="cookoutTotalForTicket(ticket.id) > 0"
                                        class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                        <div class="flex items-center justify-between gap-3 text-sm text-slate-700">
                                            <span class="font-extrabold">Cookout add-ons total</span>
                                            <span class="font-extrabold">{{ currency }}{{ cookoutTotalForTicket(ticket.id).toFixed(2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="ticket.wellness && ticket.wellness.includeService === 'yes' && hasBaseTicketInCart(ticket.id)"
                                class="mt-3 rounded-2xl border border-emerald-200/70 bg-gradient-to-b from-white/95 to-emerald-50/40 shadow-sm overflow-hidden">
                                <div class="p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                                            <span class="text-emerald-600">🧖</span>
                                            Wellness Services
                                        </div>
                                        <span class="pill">Ticket: {{ ticket.name }}</span>
                                    </div>
                                    <div class="text-sm text-slate-500 mt-1" v-if="getWellnessConfig(ticket.id)?.preset">
                                        Preset: {{ getWellnessConfig(ticket.id)?.preset }}
                                    </div>
                                </div>

                                <div class="h-px bg-slate-200/50"></div>

                                <div class="p-4 space-y-4">
                                    <div v-if="wellnessBooking(ticket.id)"
                                        class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                        <div class="font-extrabold text-slate-900">Booking</div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-3 text-sm text-slate-700">
                                            <div v-if="wellnessBooking(ticket.id)?.mode"><span class="font-extrabold">Mode:</span> {{ getWellnessServiceModeValue(ticket.id) }}</div>
                                            <div v-if="Number(wellnessBooking(ticket.id)?.duration || 0) > 0"><span class="font-extrabold">Duration:</span> {{ Number(wellnessBooking(ticket.id)?.duration || 0) }} min</div>
                                            <div v-if="Number(wellnessBooking(ticket.id)?.buffer || 0) > 0"><span class="font-extrabold">Buffer:</span> {{ Number(wellnessBooking(ticket.id)?.buffer || 0) }} min</div>
                                            <div v-if="Number(wellnessBooking(ticket.id)?.maxPerSlot || 0) > 0"><span class="font-extrabold">Max/Slot:</span> {{ Number(wellnessBooking(ticket.id)?.maxPerSlot || 0) }}</div>
                                            <div v-if="(wellnessBooking(ticket.id)?.slotDate || wellnessBooking(ticket.id)?.slotStart || wellnessBooking(ticket.id)?.slotEnd)">
                                                <span class="font-extrabold">Slots:</span>
                                                {{ wellnessBooking(ticket.id)?.slotDate || '' }}
                                                {{ wellnessBooking(ticket.id)?.slotStart ? ` ${wellnessBooking(ticket.id)?.slotStart}` : '' }}
                                                {{ wellnessBooking(ticket.id)?.slotEnd ? ` - ${wellnessBooking(ticket.id)?.slotEnd}` : '' }}
                                            </div>
                                            <div v-if="getWellnessServiceModeValue(ticket.id) === 'mobile' && Number(wellnessBooking(ticket.id)?.mobileFee || 0) > 0">
                                                <span class="font-extrabold">Mobile Fee:</span>
                                                {{ currency }}{{ Number(wellnessBooking(ticket.id)?.mobileFee || 0).toFixed(2) }}
                                            </div>
                                        </div>

                                        <div class="mt-4" v-if="(wellnessBooking(ticket.id)?.mode === 'mobile') || Number(wellnessBooking(ticket.id)?.mobileFee || 0) > 0">
                                            <div class="text-sm font-extrabold text-slate-900">Choose service mode</div>
                                            <div class="mt-2">
                                                <label class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-3 flex items-center gap-3">
                                                    <div class="flex-1">
                                                        <select
                                                            class="sel"
                                                            :value="getWellnessServiceModeValue(ticket.id)"
                                                            @change="(e) => setWellnessServiceMode(ticket.id, (e.target as HTMLSelectElement).value as 'mobile' | 'inhouse')"
                                                        >
                                                            <option value="inhouse">In-house</option>
                                                            <option value="mobile">
                                                                Mobile {{ Number(wellnessBooking(ticket.id)?.mobileFee || 0) > 0 ? `(+${currency}${Number(wellnessBooking(ticket.id)?.mobileFee || 0).toFixed(2)})` : '' }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="mt-3" v-if="getWellnessServiceModeValue(ticket.id) === 'mobile'">
                                                <label class="text-sm font-extrabold text-slate-900 block mb-1">Mobile contact phone</label>
                                                <input
                                                    type="tel"
                                                    class="w-full border border-slate-200 rounded-2xl p-3 outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500/50 transition-all bg-white font-medium"
                                                    :value="getWellnessContactPhoneValue(ticket.id)"
                                                    @input="(e) => setWellnessContactPhone(ticket.id, (e.target as HTMLInputElement).value)"
                                                    placeholder="Enter a phone number for the organiser"
                                                />
                                                <div class="text-xs text-slate-500 mt-1">Used by the organiser for mobile service coordination.</div>
                                            </div>
                                        </div>

                                        <div v-if="wellnessBooking(ticket.id)?.policy" class="text-sm text-slate-600 mt-3">
                                            <div class="font-extrabold text-slate-900">Policy</div>
                                            <div class="mt-1">{{ wellnessBooking(ticket.id)?.policy }}</div>
                                        </div>

                                        <!-- Select a time slot -->
                                        <div v-if="wellnessSlotBlocks(ticket.id).length > 0" class="mt-5">
                                            <div class="flex items-center justify-between">
                                                <div class="font-extrabold text-slate-900">
                                                    Select a time slot
                                                    <span v-if="wellnessSlotBlocks(ticket.id)[0]?.slot_date" class="ml-2 text-xs font-normal text-slate-500">
                                                        ({{ wellnessSlotBlocks(ticket.id)[0].slot_date }})
                                                    </span>
                                                </div>
                                                <div class="text-xs text-slate-500">
                                                    {{ getSelectedWellnessSlot(ticket.id) ? `Held: ${getSelectedWellnessSlot(ticket.id)} (waiting on payment)` : 'No slot selected.' }}
                                                </div>
                                            </div>
                                            <div class="text-xs text-slate-500 mt-1 mb-3">
                                                Flow: slot is held during checkout → booked after successful payment → released only if canceled.
                                            </div>

                                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                                                <button v-for="block in wellnessSlotBlocks(ticket.id)"
                                                    :key="`${block.start_time}-${block.end_time}`"
                                                    type="button"
                                                    :disabled="block.count <= 0"
                                                    @click="selectWellnessSlot(ticket.id, `${block.start_time.substring(0, 5)}-${block.end_time.substring(0, 5)}`, block.slot_date)"
                                                    class="relative px-2 py-2 text-xs font-extrabold rounded-xl border transition-all duration-200 flex flex-col items-center justify-center min-h-[50px]"
                                                    :class="[
                                                        block.count <= 0 
                                                            ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed'
                                                            : getSelectedWellnessSlot(ticket.id) === `${block.start_time.substring(0, 5)}-${block.end_time.substring(0, 5)}`
                                                                ? 'bg-[#379ce8] text-white border-[#379ce8] shadow-md transform scale-[1.02]'
                                                                : 'bg-white text-slate-700 border-slate-200 hover:border-[#379ce8] hover:bg-slate-50'
                                                    ]">
                                                    <span>{{ block.start_time.substring(0, 5) }}-{{ block.end_time.substring(0, 5) }}</span>
                                                    
                                                    <!-- Selected Checkmark (white when selected) -->
                                                    <div v-if="getSelectedWellnessSlot(ticket.id) === `${block.start_time.substring(0, 5)}-${block.end_time.substring(0, 5)}`" 
                                                        class="absolute -top-1 -right-1 h-4 w-4 bg-white text-[#379ce8] rounded-full flex items-center justify-center shadow-sm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                </button>
                                            </div>

                                            <!-- Hold Banner -->
                                            <div v-if="getSelectedWellnessSlot(ticket.id)" 
                                                class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-bold text-amber-900">Hold active: {{ getSelectedWellnessSlot(ticket.id) }}</span>
                                                    <span class="text-xs text-amber-700">Complete payment before the hold expires, or the slot will auto-release.</span>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span class="text-lg font-mono font-bold text-amber-900">{{ getHoldTime(ticket.id) }}</span>
                                                    <button type="button" 
                                                        @click="releaseWellnessSlot(ticket.id)"
                                                        class="px-3 py-1 bg-white border border-rose-200 text-rose-600 text-xs font-bold rounded-lg hover:bg-rose-50 transition-colors">
                                                        Release
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="wellnessIncludedServices(ticket.id).length > 0"
                                        class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                        <div class="font-extrabold text-slate-900">Included Services</div>
                                        <div class="text-sm text-slate-500 mt-1">
                                            Included with your ticket.
                                        </div>

                                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                                            <label v-for="s in wellnessIncludedServices(ticket.id)" :key="`ws-inc-${ticket.id}-${s.name}`"
                                                class="rounded-2xl border border-slate-200/60 bg-white shadow-sm px-4 py-3 flex items-start gap-3 cursor-pointer hover:bg-slate-50/70 transition-colors">
                                                <input
                                                    type="radio"
                                                    class="accent-emerald-600 mt-1"
                                                    :name="`wellness-included-${ticket.id}`"
                                                    :value="s.name"
                                                    :checked="getWellnessIncludedServiceValue(ticket.id) === s.name"
                                                    @change="() => setWellnessIncludedService(ticket.id, s.name)"
                                                />
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div class="font-extrabold text-slate-900 leading-snug truncate">
                                                            {{ s.name }}
                                                        </div>
                                                        <span class="badge badge-ticket shrink-0">Included</span>
                                                    </div>
                                                    <div class="text-xs text-slate-500 mt-0.5" v-if="Number(s.duration || 0) > 0">{{ Number(s.duration || 0) }} min</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <div v-if="wellnessAddonServices(ticket.id).length > 0"
                                        class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                        <div class="flex items-center justify-between gap-3 mb-2">
                                            <div class="text-lg font-extrabold text-slate-900">Add-on Services</div>
                                            <span class="pill">Paid</span>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div v-for="s in wellnessAddonServices(ticket.id)" :key="`ws-add-${ticket.id}-${s.name}`"
                                                class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="font-extrabold text-slate-900">
                                                        {{ s.name }}
                                                        <span class="badge badge-drink">Add-on</span>
                                                    </div>
                                                    <div class="font-extrabold text-slate-900" v-if="Number(s.price || 0) > 0">{{ currency }}{{ Number(s.price || 0).toFixed(2) }}</div>
                                                    <div class="text-sm text-slate-500" v-else>—</div>
                                                </div>
                                                <div class="text-xs text-slate-500 mt-2" v-if="Number(s.duration || 0) > 0">Duration: {{ Number(s.duration || 0) }} min</div>
                                                <div class="flex items-center justify-between gap-3 mt-3">
                                                    <span class="pill">Selected: {{ getWellnessServiceQty(ticket.id, s.name) }}</span>
                                                    <div class="qty">
                                                        <button type="button" @click="decWellnessService(ticket.id, s.name)">–</button>
                                                        <span data-role="qty">{{ getWellnessServiceQty(ticket.id, s.name) }}</span>
                                                        <button type="button"
                                                            :disabled="(s as any).qty !== undefined && (s as any).qty !== null && Number((s as any).qty) > 0 && getWellnessServiceQty(ticket.id, s.name) >= Number((s as any).qty)"
                                                            @click="incWellnessService(ticket.id, s.name)">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="wellnessManualAddons(ticket.id).length > 0"
                                        class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                        <div class="flex items-center justify-between gap-3 mb-2">
                                            <div class="text-lg font-extrabold text-slate-900">Manual Add-ons</div>
                                            <span class="pill">Optional</span>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div v-for="a in wellnessManualAddons(ticket.id)" :key="`wm-${ticket.id}-${a.name}`"
                                                class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="font-extrabold text-slate-900">{{ a.name }}</div>
                                                    <div class="font-extrabold text-slate-900" v-if="Number(a.price || 0) > 0">{{ currency }}{{ Number(a.price || 0).toFixed(2) }}</div>
                                                    <div class="text-sm text-slate-500" v-else>—</div>
                                                </div>
                                                <div class="text-xs text-slate-500 mt-2" v-if="Number(a.qty || 0) > 0">Qty available: {{ Number(a.qty || 0) }}</div>
                                                <div class="flex items-center justify-between gap-3 mt-3">
                                                    <span class="pill">Selected: {{ getWellnessManualAddonQty(ticket.id, a.name) }}</span>
                                                    <div class="qty">
                                                        <button type="button" @click="decWellnessManualAddon(ticket.id, a.name)">–</button>
                                                        <span data-role="qty">{{ getWellnessManualAddonQty(ticket.id, a.name) }}</span>
                                                        <button type="button"
                                                            :disabled="Number(a.qty || 0) > 0 && getWellnessManualAddonQty(ticket.id, a.name) >= Number(a.qty || 0)"
                                                            @click="incWellnessManualAddon(ticket.id, a.name)">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Package Display moved to modal only -->
                            <template v-if="false">
                                <div v-if="ticket.package_id && ticket.drink_package && ticket.has_table === 'yes' && activeTicketId === ticket.id"
                                    class="group-title">
                                    📦 {{ ticket.drink_package.name }}</div>
                                <article
                                    v-if="ticket.package_id && ticket.drink_package && ticket.has_table === 'yes' && activeTicketId === ticket.id"
                                    class="ticket ticket--table package-display">
                                    <div class="row">
                                        <div class="name">Table for {{ ticket.table_capacity }} <span
                                                class="badge badge-table">Table</span></div>
                                        <div v-if="Number(ticket.table_price ?? 0) > 0" class="price"
                                            :data-price="ticket.table_price">{{ ticket.table_price }}</div>
                                        <div v-else-if="Number(ticket.price ?? 0) > 0" class="free-ticket badge-green">
                                            <span>Free</span>
                                        </div>
                                        <div v-else class="free-ticket badge-green"><span>Free</span></div>
                                    </div>
                                    <div class="package-contents">
                                        <!-- Bottles -->
                                        <div v-if="ticket.drink_package.bottles && ticket.drink_package.bottles.length > 0"
                                            class="package-section">
                                            <h5 class="package-category">🍹 Main Bottles</h5>
                                            <ul class="package-items">
                                                <li v-for="bottle in ticket.drink_package.bottles" :key="bottle.id">
                                                    {{ bottle.qty }} × {{ bottle.name }}
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Chasers -->
                                        <div v-if="ticket.drink_package.chasers && ticket.drink_package.chasers.length > 0"
                                            class="package-section">
                                            <h5 class="package-category">🥤 Chasers / Mixers</h5>
                                            <ul class="package-items">
                                                <li v-for="chaser in ticket.drink_package.chasers" :key="chaser.id">
                                                    {{ chaser.qty }} × {{ chaser.name }}
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Waters -->
                                        <div v-if="ticket.drink_package.waters && ticket.drink_package.waters.length > 0"
                                            class="package-section">
                                            <h5 class="package-category">💧 Water Options</h5>
                                            <ul class="package-items">
                                                <li v-for="water in ticket.drink_package.waters" :key="water.id">
                                                    {{ water.qty }} × {{ water.name }}
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Notes -->
                                        <div v-if="ticket.drink_package.notes" class="package-notes">
                                            <p class="muted"><strong>📝 Notes:</strong> {{ ticket.drink_package.notes }}
                                            </p>
                                        </div>
                                    </div>
                                </article>
                            </template>

                            <!-- Table section moved to modal only -->
                            <template v-if="false">
                                <div v-if="ticket.has_table === 'yes' && !ticket.package_id && activeTicketId === ticket.id"
                                    class="group-title">
                                    Table</div>
                                <article
                                    v-if="ticket.has_table === 'yes' && !ticket.package_id && activeTicketId === ticket.id"
                                    class="ticket ticket--table" :data-sku="`tbl_${ticket.id}_Main Floor`"
                                    data-section="Main Floor">
                                    <div class="row">
                                        <div class="name">Table for {{ ticket.table_capacity }} <span
                                                class="badge badge-table">Table</span></div>
                                        <div v-if="getAvailableQuantity(`tbl_${ticket.id}_Main Floor`) === 0"
                                            class="out-of-stock badge-red">
                                            <span>Out of Stock</span>
                                        </div>
                                        <div v-else>
                                            <div v-if="Number(ticket.table_price ?? 0) > 0" class="price"
                                                :data-price="ticket.table_price">{{ ticket.table_price }}</div>
                                            <div v-else class="free-ticket badge-green"><span>Free</span></div>
                                        </div>
                                    </div>
                                    <div class="inc muted">Includes seating for {{ ticket.table_capacity }} people</div>
                                    <div class="row">
                                        <label class="muted">Section
                                            <select class="sel" data-role="section">
                                                <option value="Main Floor" selected>Main Floor</option>
                                                <option value="Balcony">Balcony</option>
                                                <option value="Poolside">Poolside</option>
                                            </select>
                                        </label>
                                        <span class="pill">Capacity: {{ ticket.table_capacity }} • Available: {{
                                            getAvailableQuantity(`tbl_${ticket.id}_Main Floor`) }}</span>
                                    </div>
                                    <div class="perforation"></div>
                                    <div class="row">
                                        <div class="qty">
                                            <button data-act="dec" aria-label="Decrease quantity">–</button>
                                            <span data-role="qty">0</span>
                                            <button data-act="inc" aria-label="Increase quantity"
                                                :disabled="getAvailableQuantity(`tbl_${ticket.id}_Main Floor`) === 0">+</button>
                                        </div>
                                    </div>
                                </article>
                            </template>

                            <!-- Drink Add-ons moved to modal only -->
                            <template v-if="false">
                                <!-- Mix Drinks -->
                                <template
                                    v-if="!showDrinksModal && !ticket.package_id && ticket.drink_addons && ticket.drink_addons.enabled && ticket.drink_addons.items.mixDrinks && ticket.drink_addons.items.mixDrinks.length > 0 && activeTicketId === ticket.id">
                                    <div class="group-title">Mix Drinks</div>
                                    <article v-for="drink in ticket.drink_addons.items.mixDrinks"
                                        :key="`${ticket.id}-mixdrink-${drink.name}`" class="ticket ticket--drink"
                                        :data-sku="`mixdrink_${drink.name}_${ticket.id}`">
                                        <div class="row">
                                            <div class="name">{{ drink.name }} <span class="badge badge-drink">Drink
                                                    Addon</span></div>
                                            <div v-if="getAvailableQuantity(`mixdrink_${drink.name}_${ticket.id}`) === 0"
                                                class="out-of-stock badge-red">
                                                <span>Out of Stock</span>
                                            </div>
                                            <div v-else class="price" :data-price="drink.cost">{{ drink.cost }}</div>
                                        </div>
                                        <div class="row">
                                            <span class="pill">Mix Drink • Available: {{
                                                getAvailableQuantity(`mixdrink_${drink.name}_${ticket.id}`) }}</span>
                                            <div class="qty">
                                                <button data-act="dec">–</button>
                                                <span data-role="qty">{{
                                                    getCartQuantity(`mixdrink_${drink.name}_${ticket.id}`) }}</span>
                                                <button data-act="inc"
                                                    :disabled="getAvailableQuantity(`mixdrink_${drink.name}_${ticket.id}`) === 0">+</button>
                                            </div>
                                        </div>
                                    </article>
                                </template>

                                <!-- Wines -->
                                <template
                                    v-if="!showDrinksModal && !ticket.package_id && ticket.drink_addons && ticket.drink_addons.enabled && ticket.drink_addons.items.wines && ticket.drink_addons.items.wines.length > 0 && activeTicketId === ticket.id">
                                    <div class="group-title">Wines</div>
                                    <article v-for="wine in ticket.drink_addons.items.wines"
                                        :key="`${ticket.id}-wine-${wine.name}`" class="ticket ticket--drink"
                                        :data-sku="`wine_${wine.name}_${ticket.id}`">
                                        <div class="row">
                                            <div class="name">{{ wine.name }} <span class="badge badge-drink">Drink
                                                    Addon</span></div>
                                            <div v-if="getAvailableQuantity(`wine_${wine.name}_${ticket.id}`) === 0"
                                                class="out-of-stock badge-red">
                                                <span>Out of Stock</span>
                                            </div>
                                            <div v-else class="price" :data-price="wine.cost">{{ wine.cost }}</div>
                                        </div>
                                        <div class="row">
                                            <span class="pill">Wine • Available: {{
                                                getAvailableQuantity(`wine_${wine.name}_${ticket.id}`) }}</span>
                                            <div class="qty">
                                                <button data-act="dec">–</button>
                                                <span data-role="qty">{{ getCartQuantity(`wine_${wine.name}_${ticket.id}`)
                                                    }}</span>
                                                <button data-act="inc"
                                                    :disabled="getAvailableQuantity(`wine_${wine.name}_${ticket.id}`) === 0">+</button>
                                            </div>
                                        </div>
                                    </article>
                                </template>

                                <!-- Waters -->
                                <template
                                    v-if="!showDrinksModal && !ticket.package_id && ticket.drink_addons && ticket.drink_addons.enabled && ticket.drink_addons.items.waters && ticket.drink_addons.items.waters.length > 0 && activeTicketId === ticket.id">
                                    <div class="group-title">Waters</div>
                                    <article v-for="water in ticket.drink_addons.items.waters"
                                        :key="`${ticket.id}-water-${water.name}`" class="ticket ticket--drink"
                                        :data-sku="`water_${water.name}_${ticket.id}`">
                                        <div class="row">
                                            <div class="name">{{ water.name }} <span class="badge badge-drink">Drink
                                                    Addon</span></div>
                                            <div v-if="getAvailableQuantity(`water_${water.name}_${ticket.id}`) === 0"
                                                class="out-of-stock badge-red">
                                                <span>Out of Stock</span>
                                            </div>
                                            <div v-else class="price" :data-price="water.cost">{{ water.cost }}</div>
                                        </div>
                                        <div class="row">
                                            <span class="pill">Water • Available: {{
                                                getAvailableQuantity(`water_${water.name}_${ticket.id}`) }}</span>
                                            <div class="qty">
                                                <button data-act="dec">–</button>
                                                <span data-role="qty">{{ getCartQuantity(`water_${water.name}_${ticket.id}`)
                                                    }}</span>
                                                <button data-act="inc"
                                                    :disabled="getAvailableQuantity(`water_${water.name}_${ticket.id}`) === 0">+</button>
                                            </div>
                                        </div>
                                    </article>
                                </template>

                                <!-- Beers -->
                                <template
                                    v-if="!showDrinksModal && !ticket.package_id && ticket.drink_addons && ticket.drink_addons.enabled && ticket.drink_addons.items.beers && ticket.drink_addons.items.beers.length > 0 && activeTicketId === ticket.id">
                                    <div class="group-title">Beers</div>
                                    <article v-for="beer in ticket.drink_addons.items.beers"
                                        :key="`${ticket.id}-beer-${beer.name}`" class="ticket ticket--drink"
                                        :data-sku="`beer_${beer.name}_${ticket.id}`">
                                        <div class="row">
                                            <div class="name">{{ beer.name }} <span class="badge badge-drink">Drink
                                                    Addon</span></div>
                                            <div v-if="getAvailableQuantity(`beer_${beer.name}_${ticket.id}`) === 0"
                                                class="out-of-stock badge-red">
                                                <span>Out of Stock</span>
                                            </div>
                                            <div v-else class="price" :data-price="beer.cost">{{ beer.cost }}</div>
                                        </div>
                                        <div class="row">
                                            <span class="pill">Beer • Available: {{
                                                getAvailableQuantity(`beer_${beer.name}_${ticket.id}`) }}</span>
                                            <div class="qty">
                                                <button data-act="dec">–</button>
                                                <span data-role="qty">{{ getCartQuantity(`beer_${beer.name}_${ticket.id}`)
                                                    }}</span>
                                                <button data-act="inc"
                                                    :disabled="getAvailableQuantity(`beer_${beer.name}_${ticket.id}`) === 0">+</button>
                                            </div>
                                        </div>
                                    </article>
                                </template>

                                <!-- Soft Drinks -->
                                <template
                                    v-if="!showDrinksModal && !ticket.package_id && ticket.drink_addons && ticket.drink_addons.enabled && ticket.drink_addons.items.softDrinks && ticket.drink_addons.items.softDrinks.length > 0 && activeTicketId === ticket.id">
                                    <div class="group-title">Soft Drinks</div>
                                    <article v-for="softDrink in ticket.drink_addons.items.softDrinks"
                                        :key="`${ticket.id}-softdrink-${softDrink.name}`" class="ticket ticket--drink"
                                        :data-sku="`softdrink_${softDrink.name}_${ticket.id}`">
                                        <div class="row">
                                            <div class="name">{{ softDrink.name }} <span class="badge badge-drink">Drink
                                                    Addon</span></div>
                                            <div v-if="getAvailableQuantity(`softdrink_${softDrink.name}_${ticket.id}`) === 0"
                                                class="out-of-stock badge-red">
                                                <span>Out of Stock</span>
                                            </div>
                                            <div v-else class="price" :data-price="softDrink.cost">{{ softDrink.cost }}
                                            </div>
                                        </div>
                                        <div class="row">
                                            <span class="pill">Soft Drink • Available: {{
                                                getAvailableQuantity(`softdrink_${softDrink.name}_${ticket.id}`) }}</span>
                                            <div class="qty">
                                                <button data-act="dec">–</button>
                                                <span data-role="qty">{{
                                                    getCartQuantity(`softdrink_${softDrink.name}_${ticket.id}`) }}</span>
                                                <button data-act="inc"
                                                    :disabled="getAvailableQuantity(`softdrink_${softDrink.name}_${ticket.id}`) === 0">+</button>
                                            </div>
                                        </div>
                                    </article>
                                </template>

                                <!-- bottles Drinks -->
                                <template
                                    v-if="!showDrinksModal && !ticket.package_id && ticket.drink_addons && ticket?.drink_addons?.enabled && ticket.drink_addons?.items?.bottles && ticket.drink_addons?.items?.bottles.length > 0 && activeTicketId === ticket.id">
                                    <div class="group-title">Soft Drinks</div>
                                    <article v-for="bottles in ticket.drink_addons?.items?.bottles"
                                        :key="`${ticket.id}-bottles-${bottles.name}`" class="ticket ticket--drink"
                                        :data-sku="`bottles_${bottles.name}_${ticket.id}`">
                                        <div class="row">
                                            <div class="name">{{ bottles.name }} <span class="badge badge-drink">Drink
                                                    Addon</span></div>
                                            <div v-if="getAvailableQuantity(`bottles_${bottles.name}_${ticket.id}`) === 0"
                                                class="out-of-stock badge-red">
                                                <span>Out of Stock</span>
                                            </div>
                                            <div v-else class="price" :data-price="bottles.cost">{{ bottles.cost }}
                                            </div>
                                        </div>
                                        <div class="row">
                                            <span class="pill">Soft Drinks • Available: {{
                                                getAvailableQuantity(`bottles_${bottles.name}_${ticket.id}`) }}</span>
                                            <div class="qty">
                                                <button data-act="dec">–</button>
                                                <span data-role="qty">{{
                                                    getCartQuantity(`bottles_${bottles.name}_${ticket.id}`) }}</span>
                                                <button data-act="inc"
                                                    :disabled="getAvailableQuantity(`bottles_${bottles.name}_${ticket.id}`) === 0">+</button>
                                            </div>
                                        </div>
                                    </article>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Order summary -->
                <aside class="ticket-sidebar-summary">
                    <div
                        class="rounded-2xl overflow-hidden border border-blue-200/70 bg-gradient-to-b from-white/95 to-blue-50/40 shadow-sm">
                        <div class="">
                            <img class="w-full" :src="event.image_url || '/assets/images/placeholder-event.jpg'"
                                style="object-fit: cover; aspect-ratio: 16/9; border-radius: 0;">
                        </div>
                        <div class="p-4">
                            <h4 class="text-lg font-extrabold text-slate-900">Order Summary</h4>

                    <!-- Card format for each ticket group -->
                    <div v-if="Object.keys(cartItems).length === 0" class="empty-cart">
                        <p class="text-gray-500 text-center py-8">No items in cart</p>
                    </div>

                    <div v-else class="cart-cards">
                        <div v-for="ticketGroup in groupedCartItems" :key="ticketGroup.ticketId" class="ticket-card">
                            <!-- Ticket Header -->
                            <div class="ticket-card-header">
                                <h5 class="ticket-name">
                                    <span class="item-qty" v-if="Number(ticketGroup?.baseTicket?.quantity || 0) > 0">x{{
                                        Number(ticketGroup?.baseTicket?.quantity || 0) }}</span>
                                    {{ ticketGroup.ticketName }}
                                    <!-- Show Free badge for free tickets when a base ticket exists -->
                                    <span
                                        v-if="Number(ticketGroup?.baseTicket?.quantity || 0) > 0 && ticketGroup.baseTicket && Number(ticketGroup.baseTicket.price || 0) === 0 && Number((tickets.find(t => t.id === ticketGroup.ticketId)?.table_price) || 0) === 0"
                                        class="free-badge">Free</span>
                                </h5>
                                <span class="ticket-total" v-if="getOrderSummaryHeaderAmount(ticketGroup) !== null">{{ currency }}{{
                                    Number(getOrderSummaryHeaderAmount(ticketGroup)).toFixed(2) }}</span>
                                <!-- If only addons are selected (no base ticket), hide the ticket price entirely -->
                                <template v-else></template>
                            </div>
                            <!-- Tables -->
                            <div v-for="table in ticketGroup.tables" :key="table.sku">
                                <div class="cart-item addon-item">
                                    <div class="item-info">
                                        <span class="item-name">{{ table.type }} — {{ table.section }} <span
                                                class="type-badge type-badge-table">Table</span></span>
                                        <span class="item-qty">x{{ table.quantity }}</span>
                                    </div>
                                    <span class="item-price" v-if="(table.price * table.quantity) > 0">{{ currency }}{{ (table.price
                                        *
                                        table.quantity).toFixed(2) }}</span>
                                    <span class="item-price" v-else>Free</span>
                                </div>
                                <!-- Drinks associated with this table/section (full-width beneath the table row) -->
                                <div v-if="getDrinksForTable(ticketGroup.ticketId, table.section).length > 0"
                                    class="table-drinks" style="margin:4px 0 10px 0.75rem;">
                                    <div v-for="(line, di) in getDrinksForTable(ticketGroup.ticketId, table.section)"
                                        :key="'td-' + di" class="text-xs text-gray-700 flex justify-between">
                                        <span>{{ line.nameType }} x{{ line.qty }}</span>
                                        <span>— {{ currency }}{{ Number(line.total).toFixed(2) }}</span>
                                    </div>
                                </div>
                                <!-- Table Seating -->
                                <div v-if="ticketGroup.sections.length > 0" class="package-category-summary">
                                    <span class="category-label">Table Seating:</span>
                                    <span class="category-items">
                                        <span v-for="seat in ticketGroup.sections" :key="seat.id || seat.name">
                                            <ul>
                                                <li>Seat: {{ seat.name }}</li>
                                            </ul>
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <!-- Package Display -->
                            <div v-if="ticketGroup.package" class="cart-package">
                                <div class="package-header-summary">
                                    <span class="package-icon">📦</span>
                                    <span class="package-name">{{ ticketGroup.package.name }}</span>
                                </div>
                                <!-- Package table price (uses ticket.table_price * qty) -->
                                <div class="package-table-summary"
                                    style="display:flex;justify-content:space-between;gap:8px;margin:6px 0;">
                                    <span class="category-label">Table</span>
                                    <span class="category-items">
                                        <template
                                            v-if="Number((tickets.find(t => t.id === ticketGroup.ticketId)?.table_price) || 0) * Number(ticketGroup.baseTicket.quantity || 0) > 0">
                                            {{ currency }}{{(Number((tickets.find(t => t.id === ticketGroup.ticketId)?.table_price)
                                                || 0) * Number(ticketGroup.baseTicket.quantity || 0)).toFixed(2)}}
                                        </template>
                                        <template v-else>Free</template>
                                    </span>
                                </div>
                                <div class="package-items-summary">
                                    <!-- Bottles -->
                                    <div v-if="ticketGroup.package.bottles && ticketGroup.package.bottles.length > 0"
                                        class="package-category-summary">
                                        <span class="category-label">🍹 Bottles:</span>
                                        <span class="category-items">
                                            <span v-for="(bottle, index) in ticketGroup.package.bottles"
                                                :key="bottle.id">
                                                {{ bottle.qty }}× {{ bottle.name }}<span
                                                    v-if="Number(index) < ticketGroup.package.bottles.length - 1">, </span>
                                            </span>
                                        </span>
                                    </div>
                                    <!-- Chasers -->
                                    <div v-if="ticketGroup.package.chasers && ticketGroup.package.chasers.length > 0"
                                        class="package-category-summary">
                                        <span class="category-label">🥤 Chasers:</span>
                                        <span class="category-items">
                                            <span v-for="(chaser, index) in ticketGroup.package.chasers"
                                                :key="chaser.id">
                                                {{ chaser.qty }}× {{ chaser.name }}<span
                                                    v-if="Number(index) < ticketGroup.package.chasers.length - 1">, </span>
                                            </span>
                                        </span>
                                    </div>
                                    <!-- Waters -->
                                    <div v-if="ticketGroup.package.waters && ticketGroup.package.waters.length > 0"
                                        class="package-category-summary">
                                        <span class="category-label">💧 Waters:</span>
                                        <span class="category-items">
                                            <span v-for="(water, index) in ticketGroup.package.waters" :key="water.id">
                                                {{ water.qty }}× {{ water.name }}<span
                                                    v-if="Number(index) < ticketGroup.package.waters.length - 1">, </span>
                                            </span>
                                        </span>
                                    </div>

                                    <!-- Table Seating -->
                                    <div v-if="ticketGroup.sections.length > 0" class="package-category-summary">
                                        <span class="category-label">Table Seating:</span>
                                        <span class="category-items">
                                            <span v-for="seat in ticketGroup.sections" :key="seat.id || seat.name">
                                                <ul>
                                                    <li>Seat: {{ seat.name }}</li>
                                                </ul>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Drinks and bottles (unassigned to any table section) -->
                            <div v-for="drink in ticketGroup.drinks.filter(d => !d.section || d.section === '-')"
                                :key="drink.sku" class="cart-item addon-item">
                                <div class="text-xs text-gray-600 flex justify-between w-full">
                                    <span>{{ drink.type }} x{{ drink.quantity }}</span>
                                    <span>— {{ currency }}{{ Number(drink.price * drink.quantity).toFixed(2) }}</span>
                                </div>
                            </div>

                            <div v-if="getCookoutLinesForTicket(ticketGroup.ticketId).length > 0"
                                class="table-drinks" style="margin:6px 0 0 0.75rem;">
                                <div v-for="(line, ci) in getCookoutLinesForTicket(ticketGroup.ticketId)"
                                    :key="'co-' + ci" class="text-xs text-gray-700 flex justify-between">
                                    <span>{{ line.nameType }} x{{ line.qty }}</span>
                                    <span>— {{ currency }}{{ Number(line.total).toFixed(2) }}</span>
                                </div>
                            </div>

                            <div v-if="getWellnessLinesForTicket(ticketGroup.ticketId).length > 0"
                                class="table-drinks" style="margin:6px 0 0 0.75rem;">
                                <div v-for="(line, wi) in getWellnessLinesForTicket(ticketGroup.ticketId)"
                                    :key="'wl-' + wi" class="text-xs text-gray-700 flex justify-between items-center py-0.5">
                                    <template v-if="line.isSlot">
                                        <span class="text-slate-400 font-medium">{{ line.nameType }}</span>
                                        <span class="text-slate-900 font-extrabold">{{ line.value }}</span>
                                    </template>
                                    <template v-else>
                                        <span>{{ line.nameType }} x{{ line.qty }}</span>
                                        <span>— {{ currency }}{{ Number(line.total).toFixed(2) }}</span>
                                    </template>
                                </div>
                            </div>

                            <div v-if="(getCookoutConfig(ticketGroup.ticketId)?.sides || []).length > 0"
                                class="rounded-2xl border border-slate-200/60 bg-white shadow-sm p-4 mt-3">
                                <div class="font-extrabold text-slate-900">Included Sides (Grab &amp; Go)</div>
                                <div class="text-sm text-slate-500 mt-1">
                                    {{ (getCookoutConfig(ticketGroup.ticketId)?.sides || []).join(', ') }}
                                </div>
                            </div>
                        </div>

                        <!-- Fee Breakdown -->
                        <div v-if="Object.keys(cartItems).length > 0 && !isCartFreeOnly" class="fee-breakdown">
                            <div class="fee-section">
                                <div class="fee-row" v-if="servicesSubtotal > 0">
                                    <span class="fee-label">Services Subtotal:</span>
                                    <span class="fee-amount">{{ currency }}{{ servicesSubtotal.toFixed(2) }}</span>
                                </div>

                                <div class="fee-row" v-if="calculateOrderFees().drinkSubtotal > 0">
                                    <span class="fee-label">Drinks Subtotal:</span>
                                    <span class="fee-amount">{{ currency }}{{ calculateOrderFees().drinkSubtotal.toFixed(2) }}</span>
                                </div>

                                <div class="fee-row" v-if="calculateOrderFees().bottleSubtotal > 0">
                                    <span class="fee-label">Bottles Subtotal:</span>
                                    <span class="fee-amount">{{ currency }}{{ calculateOrderFees().bottleSubtotal.toFixed(2) }}</span>
                                </div>

                                <div class="fee-row">
                                    <span class="fee-label">Ticket & Processing Fees:</span>
                                    <span class="fee-amount">{{ currency }}{{ subtotal.toFixed(2) }}</span>
                                </div>

                                <!-- Discount -->
                                <div v-if="discount > 0" class="fee-row discount-item">
                                    <span class="fee-label">Discount:</span>
                                    <span class="fee-amount">-{{ currency }}{{ discount.toFixed(2) }}</span>
                                </div>

                                <div class="fee-row"
                                    style="border-top: 1px solid #e2e8f0; padding-top: 6px; margin-top: 6px;"></div>

                                <!-- Fees (Service and Processing only) -->
                                <div v-if="(calculateOrderFees().serviceFeePercent + calculateOrderFees().serviceFeeFixed + calculateOrderFees().processingFeePercent + calculateOrderFees().processingFeeFixed) > 0"
                                    class="fee-row fee-item">
                                    <span class="fee-label">Service &amp; Processing Fee:</span>
                                    <span class="fee-amount">{{ currency }}{{ (calculateOrderFees().serviceFeePercent +
                                        calculateOrderFees().serviceFeeFixed +
                                        calculateOrderFees().processingFeePercent +
                                        calculateOrderFees().processingFeeFixed).toFixed(2) }}</span>
                                </div>

                                <!-- VIP Package Fee (separate) -->
                                <div v-if="calculateOrderFees().vipPackageFeePct > 0" class="fee-row fee-item">
                                    <span class="fee-label">Table Processing Fees:</span>
                                    <span class="fee-amount">{{ currency }}{{ calculateOrderFees().vipPackageFeePct.toFixed(2)
                                        }}</span>
                                </div>

                                <!-- Drink Fees (separate) -->
                                <div v-if="calculateOrderFees().drinkFees > 0" class="fee-row fee-item">
                                    <span class="fee-label">Drink Processing Fees:</span>
                                    <span class="fee-amount">{{ currency }}{{ calculateOrderFees().drinkFees.toFixed(2) }}</span>
                                </div>

                                <!-- Bottle Fees (separate) -->
                                <div v-if="calculateOrderFees().bottleFee > 0" class="fee-row fee-item">
                                    <span class="fee-label">Bottle Processing Fees:</span>
                                    <span class="fee-amount">{{ currency }}{{ calculateOrderFees().bottleFee.toFixed(2) }}</span>
                                </div>

                                <!-- Mobile Fee -->
                                <div v-if="calculateOrderFees().mobileFee > 0" class="fee-row fee-item">
                                    <span class="fee-label">Mobile Processing Fees:</span>
                                    <span class="fee-amount">{{ currency }}{{ calculateOrderFees().mobileFee.toFixed(2)
                                        }}</span>
                                </div>

                                <!-- Tax Rate Of Event -->
                                <div v-if="calculateOrderFees().taxRatePercent > 0" class="fee-row fee-item">
                                    <span class="fee-label">Tax:</span>
                                    <span class="fee-amount">{{ currency }}{{ calculateOrderFees().taxRatePercent.toFixed(2)
                                        }}</span>
                                </div>

                            </div>
                        </div>


                        <!-- Total -->
                        <div class="cart-total">
                            <hr class="mt-3 mb-3">
                            <div class="total-row">
                                <span class="total-label">Total:</span>
                                <span class="total-amount" v-if="total > 0">{{ currency }}{{ total.toFixed(2) }}</span>
                                <span class="total-amount" v-else>Free</span>
                            </div>
                        </div>
                    </div>

                    <button id="clearCart" class="w-full my-1 px-4 py-2 border rounded-md text-sm"
                        :disabled="Object.keys(cartItems).length === 0"
                        :class="{ 'opacity-50 cursor-not-allowed': Object.keys(cartItems).length === 0 }"
                        @click="clearCart">
                        Clear Cart
                    </button>

                    <div v-if="paymentBlockMessage" class="w-full my-2 text-sm text-red-600 font-semibold">
                        {{ paymentBlockMessage }}
                    </div>

                    <button class="w-full my-1 px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700"
                        :disabled="!canProceedToPayment"
                        :class="{ 'opacity-50 cursor-not-allowed': !canProceedToPayment }"
                        @click="payFromWallet">
                        Pay with Wallet
                    </button>
                    <button class="w-full my-1 px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700"
                        :disabled="!canProceedToPayment"
                        :class="{ 'opacity-50 cursor-not-allowed': !canProceedToPayment }"
                        @click="payFromStripe">
                        Pay with Card
                    </button>
                        </div>
                    </div>
                </aside>
            </main>
        </div>
    </div>

    <!-- Drinks Modal (design only) -->
    <div v-if="showDrinksModal && drinksTicket" class="drinks-modal" role="dialog" aria-modal="true">
        <div class="drinks-modal-backdrop" @click="closeDrinksModal"></div>
        <div class="drinks-modal-panel">
            <header class="drinks-modal-header">
                <h4>🥤 Add Drinks for {{ drinksTicket?.name }}</h4>
                <button class="close" @click="closeDrinksModal">Close</button>
            </header>
            <main class="drinks-modal-body">
                <!-- Mix Drinks -->
                <template
                    v-if="drinksTicket && drinksTicket.drink_addons && drinksTicket.drink_addons.enabled && drinksTicket.drink_addons.items.mixDrinks && drinksTicket.drink_addons.items.mixDrinks.length > 0">
                    <div class="group-title">Mix Drinks</div>
                    <article v-for="drink in drinksTicket?.drink_addons?.items?.mixDrinks || []"
                        :key="`${drinksTicket.id}-mixdrink-${drink.name}`" class="ticket ticket--drink"
                        :data-sku="`mixdrink_${drink.name}_${drinksTicket.id}`">
                        <div class="row">
                            <div class="name">{{ drink.name }} <span class="badge badge-drink">Drink Addon</span></div>
                            <div v-if="getAvailableQuantity(`mixdrink_${drink.name}_${drinksTicket.id}`) === 0"
                                class="out-of-stock badge-red">
                                <span>Out of Stock</span>
                            </div>
                            <div v-else-if="drink.cost > 0" class="price" :data-price="drink.cost">{{ currency }}{{ drink.cost }}</div>
                            <div v-else-if="drink?.cost == 0" class="free-ticket badge-green"><span>Complimentary</span>
                            </div>
                        </div>
                        <div class="row">
                            <span class="pill">Mix Drink • Available: {{
                                getAvailableQuantity(`mixdrink_${drink.name}_${drinksTicket.id}`) }}</span>
                            <div class="qty">
                                <button data-act="dec">–</button>
                                <span data-role="qty">{{ getCartQuantity(`mixdrink_${drink.name}_${drinksTicket.id}`)
                                    }}</span>
                                <button data-act="inc"
                                    :disabled="getAvailableQuantity(`mixdrink_${drink.name}_${drinksTicket.id}`) === 0">+</button>
                            </div>
                        </div>
                    </article>
                </template>

                <!-- Wines -->
                <template
                    v-if="drinksTicket && drinksTicket.drink_addons && drinksTicket.drink_addons.enabled && drinksTicket.drink_addons.items.wines && drinksTicket.drink_addons.items.wines.length > 0">
                    <div class="group-title">Wines</div>
                    <article v-for="wine in drinksTicket?.drink_addons?.items?.wines || []"
                        :key="`${drinksTicket.id}-wine-${wine.name}`" class="ticket ticket--drink"
                        :data-sku="`wine_${wine.name}_${drinksTicket.id}`">
                        <div class="row">
                            <div class="name">{{ wine.name }} <span class="badge badge-drink">Drink Addon</span></div>
                            <div v-if="getAvailableQuantity(`wine_${wine.name}_${drinksTicket.id}`) === 0"
                                class="out-of-stock badge-red">
                                <span>Out of Stock</span>
                            </div>
                            <div v-else-if="wine.cost > 0" class="price" :data-price="wine.cost">{{ currency }}{{ wine.cost }}</div>
                            <div v-else-if="wine?.cost == 0" class="free-ticket badge-green"><span>Complimentary</span>
                            </div>
                        </div>
                        <div class="row">
                            <span class="pill">Wine • Available: {{
                                getAvailableQuantity(`wine_${wine.name}_${drinksTicket.id}`)
                                }}</span>
                            <div class="qty">
                                <button data-act="dec">–</button>
                                <span data-role="qty">{{ getCartQuantity(`wine_${wine.name}_${drinksTicket.id}`) }}</span>
                                <button data-act="inc"
                                    :disabled="getAvailableQuantity(`wine_${wine.name}_${drinksTicket.id}`) === 0">+</button>
                            </div>
                        </div>
                    </article>
                </template>

                <!-- Waters -->
                <template
                    v-if="drinksTicket && drinksTicket.drink_addons && drinksTicket.drink_addons.enabled && drinksTicket.drink_addons.items.waters && drinksTicket.drink_addons.items.waters.length > 0">
                    <div class="group-title">Waters</div>
                    <article v-for="water in drinksTicket?.drink_addons?.items?.waters || []"
                        :key="`${drinksTicket.id}-water-${water.name}`" class="ticket ticket--drink"
                        :data-sku="`water_${water.name}_${drinksTicket.id}`">
                        <div class="row">
                            <div class="name">{{ water.name }} <span class="badge badge-drink">Drink Addon</span></div>
                            <div v-if="getAvailableQuantity(`water_${water.name}_${drinksTicket.id}`) === 0"
                                class="out-of-stock badge-red">
                                <span>Out of Stock</span>
                            </div>
                            <div v-else-if="water.cost > 0" class="price" :data-price="water.cost">{{ currency }}{{ water.cost }}</div>
                            <div v-else-if="water?.cost == 0" class="price">Complimentary</div>
                        </div>
                        <div class="row">
                            <span class="pill">Water • Available: {{
                                getAvailableQuantity(`water_${water.name}_${drinksTicket.id}`) }}</span>
                            <div class="qty">
                                <button data-act="dec">–</button>
                                <span data-role="qty">{{ getCartQuantity(`water_${water.name}_${drinksTicket.id}`) }}</span>
                                <button data-act="inc"
                                    :disabled="getAvailableQuantity(`water_${water.name}_${drinksTicket.id}`) === 0">+</button>
                            </div>
                        </div>
                    </article>
                </template>

                <!-- Beers -->
                <template
                    v-if="drinksTicket && drinksTicket.drink_addons && drinksTicket.drink_addons.enabled && drinksTicket.drink_addons.items.beers && drinksTicket.drink_addons.items.beers.length > 0">
                    <div class="group-title">Beers</div>
                    <article v-for="beer in drinksTicket?.drink_addons?.items?.beers || []"
                        :key="`${drinksTicket.id}-beer-${beer.name}`" class="ticket ticket--drink"
                        :data-sku="`beer_${beer.name}_${drinksTicket.id}`">
                        <div class="row">
                            <div class="name">{{ beer.name }} <span class="badge badge-drink">Drink Addon</span></div>
                            <div v-if="getAvailableQuantity(`beer_${beer.name}_${drinksTicket.id}`) === 0"
                                class="out-of-stock badge-red">
                                <span>Out of Stock</span>
                            </div>
                            <div v-else-if="beer.cost > 0" class="price" :data-price="beer.cost">{{ currency }}{{ beer.cost }}</div>
                            <div v-else-if="beer?.cost == 0" class="price">Complimentary</div>
                        </div>
                        <div class="row">
                            <span class="pill">Beer • Available: {{
                                getAvailableQuantity(`beer_${beer.name}_${drinksTicket.id}`)
                                }}</span>
                            <div class="qty">
                                <button data-act="dec">–</button>
                                <span data-role="qty">{{ getCartQuantity(`beer_${beer.name}_${drinksTicket.id}`) }}</span>
                                <button data-act="inc"
                                    :disabled="getAvailableQuantity(`beer_${beer.name}_${drinksTicket.id}`) === 0">+</button>
                            </div>
                        </div>
                    </article>
                </template>

                <!-- Soft Drinks -->
                <template
                    v-if="drinksTicket && drinksTicket.drink_addons && drinksTicket.drink_addons.enabled && drinksTicket.drink_addons.items.softDrinks && drinksTicket.drink_addons.items.softDrinks.length > 0">
                    <div class="group-title">Soft Drinks</div>
                    <article v-for="softDrink in drinksTicket?.drink_addons?.items?.softDrinks || []"
                        :key="`${drinksTicket.id}-softdrink-${softDrink.name}`" class="ticket ticket--drink"
                        :data-sku="`softdrink_${softDrink.name}_${drinksTicket.id}`">
                        <div class="row">
                            <div class="name">{{ softDrink.name }} <span class="badge badge-drink">Drink Addon</span></div>
                            <div v-if="getAvailableQuantity(`softdrink_${softDrink.name}_${drinksTicket.id}`) === 0"
                                class="out-of-stock badge-red">
                                <span>Out of Stock</span>
                            </div>
                            <div v-else-if="softDrink.cost > 0" class="price" :data-price="softDrink.cost">{{ currency }}{{
                                softDrink.cost }}
                            </div>
                            <div v-else-if="softDrink?.cost == 0" class="price">Complimentary</div>
                        </div>
                        <div class="row">
                            <span class="pill">Soft Drink • Available: {{
                                getAvailableQuantity(`softdrink_${softDrink.name}_${drinksTicket.id}`) }}</span>
                            <div class="qty">
                                <button data-act="dec">–</button>
                                <span data-role="qty">{{ getCartQuantity(`softdrink_${softDrink.name}_${drinksTicket.id}`)
                                    }}</span>
                                <button data-act="inc"
                                    :disabled="getAvailableQuantity(`softdrink_${softDrink.name}_${drinksTicket.id}`) === 0">+</button>
                            </div>
                        </div>
                    </article>
                </template>

                <!-- Bottles -->
                <template
                    v-if="drinksTicket && drinksTicket.drink_addons && drinksTicket.drink_addons.enabled && drinksTicket.drink_addons.items.bottles && drinksTicket.drink_addons.items.bottles.length > 0">
                    <div class="group-title">Bottles</div>
                    <article v-for="bottle in drinksTicket?.drink_addons?.items?.bottles || []"
                        :key="`${drinksTicket.id}-bottles-${bottle.name}`" class="ticket ticket--drink"
                        :data-sku="`bottles_${bottle.name}_${drinksTicket.id}`">
                        <div class="row">
                            <div class="name">{{ bottle.name }} <span class="badge badge-drink">Drink Addon</span></div>
                            <div v-if="getAvailableQuantity(`bottles_${bottle.name}_${drinksTicket.id}`) === 0"
                                class="out-of-stock badge-red">
                                <span>Out of Stock</span>
                            </div>
                            <div v-else-if="bottle.cost > 0" class="price" :data-price="bottle.cost">{{ currency }}{{ bottle.cost }}
                            </div>
                            <div v-else-if="bottle?.cost == 0" class="price">Complimentary</div>
                        </div>
                        <div class="row">
                            <span class="pill">Bottles • Available: {{
                                getAvailableQuantity(`bottles_${bottle.name}_${drinksTicket.id}`) }}</span>
                            <div class="qty">
                                <button data-act="dec">–</button>
                                <span data-role="qty">{{ getCartQuantity(`bottles_${bottle.name}_${drinksTicket.id}`)
                                    }}</span>
                                <button data-act="inc"
                                    :disabled="getAvailableQuantity(`bottles_${bottle.name}_${drinksTicket.id}`) === 0">+</button>
                            </div>
                        </div>
                    </article>
                </template>


            </main>
            <footer class="drinks-modal-footer">
                <button class="btn btn-outline" @click="closeDrinksModal">Done</button>
            </footer>
        </div>
    </div>

    <!-- Media Preview Modal -->
    <div v-if="showPreview" class="preview-modal" role="dialog" aria-modal="true" @click="closePreview">
        <div class="preview-backdrop"></div>
        <div class="preview-content" @click.stop>
            <button class="preview-close" @click="closePreview" aria-label="Close preview">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <div class="preview-media">
                <img v-if="previewType === 'image'" :src="previewSrc" :alt="previewAlt" class="preview-image" />
                <video v-else :src="previewSrc" controls class="preview-video" autoplay></video>
            </div>
        </div>
    </div>
</template>
