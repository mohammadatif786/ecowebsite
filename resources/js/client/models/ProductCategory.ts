export type ProductCategory = {
    id: number;
    name?: string | undefined;
    status: string,
    [key: string]: any // ✅ allows useForm to accept it
};
