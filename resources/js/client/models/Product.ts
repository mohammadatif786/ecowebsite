import { ProductCategory } from "./ProductCategory";

export type Product = {
    id: number;
    name?: string | undefined;
    description: string;
    cover_image: string|null;
    qty: string
    price: string
    status: string,
    images: Array<string>
    category?: ProductCategory
    [key: string]: any // ✅ allows useForm to accept it
};
