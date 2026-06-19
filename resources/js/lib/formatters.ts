export function formatCurrency(
    value: string | number,
    locale = 'pt-BR',
    currency = 'BRL',
): string {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
    }).format(Number(value));
}

export function formatPhone(phone: string | null | undefined): string {
    if (!phone) {
        return '-';
    }
    const cleaned = phone.replace(/\D/g, '');

    if (cleaned.length === 11) {
        return `(${cleaned.substring(0, 2)}) ${cleaned.substring(2, 7)}-${cleaned.substring(7)}`;
    }

    if (cleaned.length === 10) {
        return `(${cleaned.substring(0, 2)}) ${cleaned.substring(2, 6)}-${cleaned.substring(6)}`;
    }

    return phone;
}
