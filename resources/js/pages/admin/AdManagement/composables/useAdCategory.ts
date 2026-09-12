export const useAdCategory = () => {
  const categoryConfig = {
    restaurant: { label: 'Restaurant', class: 'category-restaurant', icon: '🍽️' },
    club: { label: 'Club / Fête', class: 'category-club', icon: '🎭' },
    general: { label: 'General', class: 'category-general', icon: '📢' },
  };

  const getCategory = (category: string) =>
    categoryConfig[category] ?? {
      label: category.toUpperCase(),
      class: 'category-default',
      icon: '📌',
    };

  return { getCategory };
};
