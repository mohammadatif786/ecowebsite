export type EventCategory = {
    id: number;
    image_object?: string | null,
    name?:string,
    is_featured:boolean,
    status:boolean|number,
    [key: string]: any // ✅ allows useForm to accept it
};
