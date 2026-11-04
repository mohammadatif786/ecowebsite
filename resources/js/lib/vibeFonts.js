// Text Vibe looks — one list for the composer and the feed card (the app uses the same names).
export const VIBE_FONTS = [
  { name: 'Aa Classic', class: 'font-serif italic' },
  { name: 'Aa Modern', class: 'font-sans font-bold' },
  { name: 'Aa Mono', class: 'font-mono' },
  { name: 'Aa Script', class: 'font-serif tracking-wide' },
  { name: 'Aa Heavy', class: 'font-black tracking-tighter' },
  { name: 'Aa Italic', class: 'font-sans italic font-semibold' },
  { name: 'Aa Impact', class: 'font-sans uppercase font-extrabold tracking-widest' },
  { name: 'Aa Typewriter', class: 'font-mono tracking-widest' },
  { name: 'Aa Elegant', class: 'font-serif tracking-widest font-light' },
  { name: 'Aa Neon', class: 'font-black', style: 'text-shadow:0 0 10px rgba(255,255,255,.85),0 0 22px rgba(255,255,255,.55)' },
];

export const vibeFont = (name) => VIBE_FONTS.find((f) => f.name === name) || null;
