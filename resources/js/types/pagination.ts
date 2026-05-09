export type PaginatedResponse<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    total: number;
    per_page: number;
    from: number | null;
    to: number | null;
};
