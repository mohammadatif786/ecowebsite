export const useAdMetrics = () => {
  const getDurationDays = (start: string, end?: string) => {
    if (!start) return 0;

    const s = new Date(start).getTime();
    const e = end ? new Date(end).getTime() : Date.now();

    return Math.max(0, Math.ceil((e - s) / 86400000));
  };

  const getCTR = (clicks: number, impressions: number) => {
    if (!impressions) return 0;
    return ((clicks / impressions) * 100).toFixed(1);
  };

  return { getDurationDays, getCTR };
};
