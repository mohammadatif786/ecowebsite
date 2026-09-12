export type LinkupEvent = {
    id: number,
    firebase_id: string,
    category_id: number | null,
    city: string,
    country: string,
    coupon_visibility: boolean,
    description: string,
    disclaimer: string,
    email: string,
    featured_image: string,
    image_object: string | null, // Could be more specific
    latitude: number | null,
    longitude: number | null,
    organizer_image_object: string | null,
    organizer_name: string,
    phone: string,
    state: string,
    title: string,
    type: string,
    venue: string,
    website: string,
    likes_count: number,
    start_time: string, // could also be `Date` if parsed
    end_time: string,   // could also be `Date`
    created_at?: string, // or Date
    updated_at?: string, // or Date
    is_free: boolean,
    organizer_id: number | null,
    avatar: number | null,
    [key: string]: any // ✅ allows useForm to accept it
    event_details: Record<string, any>;
    slug: string
};
export type Coupon = {
    id: number;
    code: string;
    description: string;
    discount: string;
    discount_type: "percentage" | "amount";
    expiry_date: string;
    image_object: string;
    image_url: string;
    firebase_event_id: string | null;
    firebase_id: string | null;
};
