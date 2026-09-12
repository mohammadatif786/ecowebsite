import { User } from "@/types";
import { Product } from "./Product";


export type OrderItem = {
    id: string;
    order_id: number;
    product_id: number;
    qty: number;
    unit_price: number;
    sub_total: number
    product?: Product
    [key: string]: any // ✅ allows useForm to accept it
};
