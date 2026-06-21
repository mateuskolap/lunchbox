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

export function formatDate(dateStr: string | null | undefined): string {
    if (!dateStr) {
        return '';
    }

    const datePart = dateStr.split('T')[0];
    const parts = datePart.split('-');

    if (parts.length !== 3) {
        return dateStr;
    }

    const [year, month, day] = parts;

    return `${day}/${month}/${year}`;
}

export function formatTime(dateStr: string | null | undefined): string {
    if (!dateStr) {
        return '';
    }

    const date = new Date(dateStr);

    if (isNaN(date.getTime())) {
        return dateStr;
    }

    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${hours}:${minutes}`;
}

export function getOrderStatusBadgeVariant(
    status: string,
): 'default' | 'destructive' | 'secondary' {
    switch (status) {
        case 'concluded':
            return 'default';
        case 'canceled':
            return 'destructive';
        default:
            return 'secondary';
    }
}

