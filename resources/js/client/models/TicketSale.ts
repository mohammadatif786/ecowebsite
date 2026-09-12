export type DrinkAddon = {
    name: string;
    quantity: number;
    unit_price: number;
    total_price: number;
};

export type TableAddon = {
    section: string;
    capacity: number;
    quantity: number;
    unit_price: number;
    total_price: number;
};

export type status = {
    status: string;
};
export type TicketSale = {
    id: number;
    firebase_id: string;
    ticket_id: number;
    ticket_type: string;
    ticket_name: string;
    ticket_status: string;
    ticket_qrcode_id: string;
    ticket_qrcode: number;
    no_of_tickets: number;
    user_id: number;
    link_up_event_id: number;
    payment_method: string;
    pay_type: string;
    fee: string;
    discount: string;
    tax: string;
    coupan_amount: string;
    drinks_total: string;
    tables_total: string;
    drink_addons: DrinkAddon[] | null;
    table_addons: TableAddon[] | null;
    sub_total: string;
    total: string;
    stripe_id: string;
    stripe_price: string;
    stripe_status: string;
    created_at: string;
    updated_at: string;
    cancellation_request: status[];
    [key: string]: any; // ✅ allows useForm to accept it
};
