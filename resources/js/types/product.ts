export type Product = {
    id: number;
    name: string;
    price: string;
    is_lunchbox?: boolean;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
};
