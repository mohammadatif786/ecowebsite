export type Plans = {
    id?: number;
    title?: string | undefined;
    stripe_price_id?: string | undefined;
    duration?: number;
    price: number;
    status?: number;
    _method?: string;
};
