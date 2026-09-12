import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href?: string;
    icon?: LucideIcon;
    isActive?: boolean;
    children?: Array<NavItem>;
    permission?: string;
    isSection?: boolean;
    badge?: string;
    badgeVariant?: 'green' | 'pink' | 'blue' | 'orange' | 'peach' | 'purple';
}

export type SelectOption = {
    label: string;
    value?: string;
    iso2?: string;
    name?: string;
    state_code?: string;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

export interface User {
    id: number;
    name?: string;
    first_name: string;
    last_name: string;
    email: string;
    linkup_id: string;
    avatar?: string;
    email_verified_at?: string | null;
    about_me?: string,
    address_proof?: string,
    age?: string,
    back_side?: string,
    balance?: string,
    birthday?: string,
    country?: string,
    country_code?: string,
    distanceinMK?: string,
    fcmToken?: string,
    front_side?: string,
    gender?: string | null,
    is_club?: boolean,
    is_ghost?: boolean,
    is_live_streaming?: boolean,
    is_restaurant?: boolean,
    is_top_shelf?: boolean,
    job?: string,
    kyc_status?: string,
    language?: string,
    coins?: string,
    last_active?: string,
    link_me_with?: string,
    link_me_with_country_code?: string,
    link_me_with_country_name?: string,
    link_with_me_phone_code?: string,
    longitude?: string,
    city?: string,
    new_city?: string,
    new_country?: string,
    new_match_notification?: boolean,
    new_message_notification?: boolean,
    state?: string,
    new_state?: string,
    phone_number?: string,
    phone_number_dial_code?: string,
    promotion_notification?: string,
    show_age?: boolean,
    status?: boolean,
    university?: string,
    which_latin_country_you_linked_with?: string,
    whyare?: string,
    caribbean_interest?: string,
    age_filter?: string,
    more_photos?: string,
    distance_filter?: string,
    subscription?: string,
    created_at?: string;
    updated_at?: string;
    interests: any;
    [key: string]: any // ✅ allows useForm to accept it
}


export type BreadcrumbItemType = BreadcrumbItem;
