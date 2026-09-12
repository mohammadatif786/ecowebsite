export type Coupon = {
    id: number;
    link_up_event_id: number | null|string;
    code: string;
    image_object?: string|null;
    image_url?: string|null;
    expiry_date?: string|null;
    description?: string;
    discount: string;
    title?: string;
    discount_type: 'percentage' | 'amount' | string;
    status: number | string | null;
    [key: string]: any // ✅ allows useForm to accept it
};
