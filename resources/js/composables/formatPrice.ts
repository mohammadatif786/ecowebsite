export function formatPrice(price: number | string) {
  const value = Number(price);

  if (!Number.isFinite(value)) return '0';

  return value % 1 === 0
    ? value.toLocaleString()
    : value.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
}
