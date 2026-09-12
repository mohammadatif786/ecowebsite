export type SubscriptionPlan = {
    id: number;
    title: string;
    stripe_price_id: string | null;
    description: string;
    price: number;
    duration: string;
    status: boolean;
};
