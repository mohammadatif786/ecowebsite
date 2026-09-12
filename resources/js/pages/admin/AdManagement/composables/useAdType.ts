export const useAdType = () => {
  const adType = {
    image: { label: 'Image', class: 'ad-type-image', icon: '🖼️' },
    video: { label: 'Video', class: 'ad-type-video', icon: '🎬' },
    both: { label: 'Image + Video', class: 'ad-type-both', icon: '🖼️🎬' },
  };

  const getAdType = (type: string) =>
    adType[type] ?? {
      label: type.toUpperCase(),
      class: 'ad-type-default',
      icon: '📌',
    };

  return { getAdType };
};
