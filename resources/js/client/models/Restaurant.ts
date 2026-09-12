export type Restaurant = {
    id: number;
    firebase_id: string;
    name: string;
    email: string;
    phone: string;
    country: string;
    state: string;
    city: string;
    location: string;
    www: string;
    image_object: string;
    video_object: string;
    paid: number;
    is_paid: boolean;
    cost: number;
    start_date: string;
    end_date: string;
    invoice_id: number;
    status: string;
    created_at?: Date;
    updated_at?: Date;
    [key: string]: any // ✅ allows useForm to accept it
};
