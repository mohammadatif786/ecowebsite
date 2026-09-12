export type ClubFete = {
    id: number;
    firebase_id: string;
    is_paid: boolean;
    start_date: string; // or Date if used as Date object
    end_date: string;   // or Date if used as Date object
    status: string;
    cost: number;
    email: string;
    image_object: string;
    location: string;
    name: string;
    country: string;
    state: string;
    city: string;
    paid: number;
    phone: string;
    video_object: string;
    www: string;
    invoice_id: number;
    created_at?: Date;
    updated_at?: Date;
    [key: string]: any // ✅ allows useForm to accept it
};
