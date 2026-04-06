import { format, formatDistanceToNow, isToday, isYesterday } from 'date-fns';
import { id } from 'date-fns/locale';

/**
 * Format a number as Indonesian Rupiah.
 */
export function formatRupiah(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
}

/**
 * Format an ISO timestamp for display.
 */
export function formatTimestamp(isoString) {
    if (!isoString) return '';
    const date = new Date(isoString);

    if (isToday(date)) {
        return format(date, 'HH:mm');
    }
    if (isYesterday(date)) {
        return 'Kemarin';
    }
    return format(date, 'dd/MM/yy');
}

/**
 * Format a timestamp as relative time (e.g., "5 menit lalu").
 */
export function formatRelativeTime(isoString) {
    if (!isoString) return '';
    return formatDistanceToNow(new Date(isoString), { addSuffix: true, locale: id });
}

/**
 * Truncate a string to a maximum length.
 */
export function truncate(str, maxLength = 50) {
    if (!str) return '';
    if (str.length <= maxLength) return str;
    return str.slice(0, maxLength) + '…';
}
