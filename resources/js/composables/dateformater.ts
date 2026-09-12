export function formatUpdatedAt(dateString: string | null | undefined, type: boolean): string {
    if (!dateString) return '—';

    const date = new Date(dateString);
    if (type) {
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'short', // e.g., Dec
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true, // 2:32 PM (use false for 14:32)
        }).format(date);
    } else {
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'short', // e.g., Dec
            day: 'numeric',
        }).format(date);
    }
}
