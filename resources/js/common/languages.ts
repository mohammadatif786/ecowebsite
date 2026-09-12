export type LangKey = 'en';

type LangMeta = {
  label: string;
  emoji: string;
  /** Google Translate language code; null if not supported */
  google: string | null;
  supported: boolean;
};

const languages: Record<LangKey, LangMeta> = {
 en: { label: 'English',        emoji: '🇬🇧', google: 'en',  supported: true }
};

export default languages;

/** CSV of supported Google language codes for TranslateElement `includedLanguages` */
export const includedGoogleLanguages = Object.values(languages)
  .filter(l => l.supported && l.google)
  .map(l => l.google)
  .join(',');
