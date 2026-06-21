export type Product = {
    id: number;
    name: string;
    price: string;
    show_in_prep_summary?: boolean;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
};
