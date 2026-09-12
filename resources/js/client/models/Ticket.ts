export type TicketDetail = {
    type: string;
    event_id: number;
    ticket_type: string;
    name: string;
    description: string;
    has_table: string;
    is_free: string;
    price: number;
    promo_price: number;
    quantity: number;
    tickets_per_attendee: number;
    sale_start: string;
    sale_end: string;
    table_price?: number;
    table_capacity?: number;
    package_id?: number;
    sections?: [];
    main_bottles?: string[];
    chasers_or_mixers?: string[];
    water_options?: string[];
    drink_addons?: Record<string, any[]>;
    status?: string;
}

export const DEFAULT_TICKET: TicketDetail = {
    type: '',
    event_id: 0,
    ticket_type: '',
    name: '',
    description: '',
    has_table: 'no',
    is_free: 'no',
    price: 0,
    promo_price: 0,
    quantity: 0,
    tickets_per_attendee: 0,
    sale_start: '',
    sale_end: '',
    table_price: 0,
    table_capacity: 0,
    package_id: 0,
    sections: [],
    main_bottles: [],
    chasers_or_mixers: [],
    water_options: [],
    drink_addons: {},
    status: 'active'
}