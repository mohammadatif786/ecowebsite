import { User } from "@/types";
import { OrderItem } from "./OrderItem";


export type Order = {
    id: string;
    user_id?: number;
    number: string;
    total: number;
    country: string;
    city: string;
    state: string;
    zipcode: string;
    street_address: string;
    status: 'new'|'processing'|'delivered'|'cancelled';
    customer?: User;
    order_items?: Array<OrderItem>
    [key: string]: any // allows useForm to accept it
};
