export type News = {
    id: number;
    firebase_id: string | null;
    image_object: string | null;
    title: string;
    content: string;
    status: number;
    created_at: string;
    updated_at: string;
    [key: string]: any // ✅ allows useForm to accept it

}