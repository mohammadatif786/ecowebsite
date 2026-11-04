export const placeholderPhoto = (id = 1) => `https://i.pravatar.cc/700?img=${Number(id) % 70 || 1}`;

export const asArray = (value) => {
  if (Array.isArray(value)) return value.filter(Boolean);
  if (!value) return [];
  if (typeof value === 'string') {
    const trimmed = value.trim();
    if (!trimmed) return [];
    try {
      const parsed = JSON.parse(trimmed);
      if (Array.isArray(parsed)) return parsed.filter(Boolean);
      if (parsed && typeof parsed === 'object') return Object.values(parsed).filter(Boolean);
    } catch {
      return trimmed.split(',').map((item) => item.trim()).filter(Boolean);
    }
    return [trimmed];
  }
  return [];
};

export const assetUrl = (path, fallbackId) => {
  if (!path) return placeholderPhoto(fallbackId);
  if (/^(https?:|data:|blob:)/i.test(path)) return path;
  if (path.startsWith('/storage/') || path.startsWith('/images/') || path.startsWith('/assets/')) return path;
  if (path.startsWith('/')) return path;
  return `/storage/${path}`;
};

export const isFlagImage = (flag) => typeof flag === 'string' && /^https?:\/\//i.test(flag);

export const mapLinkupUser = (u) => {
  const img = assetUrl(u.avatar, u.id);
  const extraPhotos = asArray(u.more_photos).map((photo) => assetUrl(photo, u.id));
  const fallbackPhotos = extraPhotos.length
    ? []
    : [
        `https://picsum.photos/seed/${u.id || u.name}a/500/700`,
        `https://picsum.photos/seed/${u.id || u.name}b/500/700`,
      ];
  const photos = [img, ...extraPhotos, ...fallbackPhotos]
    .filter((photo, index, arr) => arr.indexOf(photo) === index);

  return {
    raw: u,
    id: u.id,
    uid: u.uid,
    name: u.name || 'LinkUp User',
    age: u.age || 25,
    gender: u.gender || '',
    country: u.resolved_country ?? u.new_country ?? u.country ?? '',
    city: u.resolved_city ?? u.new_city ?? u.city ?? '',
    flag: u.country_flag || 'Global',
    km: u.distance_km != null && !Number.isNaN(Number.parseFloat(u.distance_km))
      ? Math.floor(Number.parseFloat(u.distance_km))
      : u.distanceinMK != null && !Number.isNaN(Number.parseFloat(u.distanceinMK))
        ? Math.floor(Number.parseFloat(u.distanceinMK))
        : null,
    online: !!u.is_live_streaming,
    img,
    photos: photos.length ? photos : [placeholderPhoto(u.id)],
    activePhotoIdx: 0,
    swipeDelay: Math.floor(Math.random() * 5000),
    bio: u.about_me || u.whyare || '',
    interests: asArray(u.interests),
    languages: asArray(u.language),
    caribbeanCountry: u.caribbean_interest || '',
    goal: u.whyare || '',
    job: u.job || u.work || '',
    university: u.university || '',
    linkupId: u.linkup_id || u.uid || '',
    friendRequestSent: !!u.is_friend_request_sent,
  };
};
