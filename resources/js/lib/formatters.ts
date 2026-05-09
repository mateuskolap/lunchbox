export function formatCurrency(value: string | number, locale = 'pt-BR', currency = 'BRL'): string {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
    }).format(Number(value));
}
