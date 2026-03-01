/**
 * Format date to a human-readable string
 * Example: "Wednesday, Feb 16, 2026"
 */
export function formatDate(dateString: string | undefined | null): string {
    if (!dateString) return '—';
    
    const date = new Date(dateString);
    
    // Check if date is valid
    if (isNaN(date.getTime())) return '—';
    
    const options: Intl.DateTimeFormatOptions = {
        weekday: 'long',
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    };
    
    return date.toLocaleDateString('en-US', options);
}

/**
 * Format date to a shorter version
 * Example: "Feb 16, 2026"
 */
export function formatDateShort(dateString: string | undefined | null): string {
    if (!dateString) return '—';
    
    const date = new Date(dateString);
    
    if (isNaN(date.getTime())) return '—';
    
    const options: Intl.DateTimeFormatOptions = {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    };
    
    return date.toLocaleDateString('en-US', options);
}
