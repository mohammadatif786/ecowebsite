export type EventSponsor = {
    id: number;
    image_object?: string | null,
    sponsor_image_object?: string | null,
    name?:string,
    description: string,
    link_up_event_id?:number|null,
    status:boolean|number,
    [key: string]: any // ✅ allows useForm to accept it
};
