export const useAdStatus = () => {
    const normalizeStatus = (ad: any): string => {
        const status = ad.publication_status;

        if (status === 'Active (publish immediately)') return 'active';
        if (status === 'Scheduled (go live on start date)') return 'scheduled';
        if (status === 'Draft (save without publishing)') return 'expired';

        return ad.status ? 'active' : 'draft';
    };

    const statusLabels: Record<string, { label: string; class: string }> = {
        active: { label: 'Active', class: 'status-active' },
        scheduled: { label: 'Scheduled', class: 'status-scheduled' },
        expired: { label: 'Expired', class: 'status-expired' },
        draft: { label: 'Draft', class: 'status-default' },
    };

    const getStatus = (ad: any) => {
        const key = normalizeStatus(ad);

        return statusLabels[key] ?? {
            label: ad.publication_status ?? 'Unknown',
            class: 'status-default',
        };
    };

    return { getStatus };
};
