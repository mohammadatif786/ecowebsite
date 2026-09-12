export type User = {
    id: number;
    name?: string;
    email: string;
    password?: string;
    is_wizard_completed?: boolean;
    uid?: string;
    country?: string;
    front_side?: string;
    link_me_with?: string;
    distance_filter?: number;
    language?: string;
    whyare?: string;
    about_me?: string;
    balance?: number;
    age_filter?: string;
    fcmToken?: string;
    address_proof?: string;
    image?: string;
    new_message_notification?: boolean;
    more_photos?: string[]; // Assuming it's an array of URLs/paths
    country_code?: string;
    is_restaurant?: boolean;
    is_top_shelf?: boolean;
    link_with_me_phone_code?: string;
    phone_number?: string;
    city?: string;
    new_city?: string;
    back_side?: string;
    job?: string;
    status?: string;
    is_club?: boolean;
    birthday?: string; // Or Date if parsed
    new_match_notification?: boolean;
    new_country?: string;
    gender?: string;
    university?: string;
    created_at?: string; // Or Date
    subscription?: string;
    video?: string;
    link_me_with_country_name?: string;
    stripe_customer_id?: string;
    updated_at?: string; // Or Date
    distanceinMK?: number;
    state?: string;
    new_state?: string;
    first_name?: string;
    last_name?: string;
    kyc_status?: string;
    promotion_notification?: boolean;
    avatar?: string;
    show_age?: boolean;
    phone_number_dial_code?: string;
    which_latin_country_you_linked_with?: string;
    is_ghost?: boolean;
    link_me_with_country_code?: string;
    last_active?: string; // Or Date
    interests?: string[]; // Assuming it's an array
    age?: number;
    is_live_streaming?: boolean;
    latitude?: number;
    longitude?: number;
    caribbean_interest?: string;
    username?: string;
    email_verified_at?: string; // Or Date
    deleted_at?: string; // Or Date
};
