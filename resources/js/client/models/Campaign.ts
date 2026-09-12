export type Campaign = {
    id: number;
    name: string;
    user_id: number;
    advertiser_name: string;
    advertiser_postback_url: string;
    offer_url: string,
    landing_url: string;
    portal_url: string;
    created_at?: Date,
    updated_at?: Date,
};
