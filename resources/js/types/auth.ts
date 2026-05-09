export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    two_factor_enabled?: boolean;
    permissions?: string[];
    roles?: string[];
};
export type Auth = {
    user: User;
    permissions: string[];
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
