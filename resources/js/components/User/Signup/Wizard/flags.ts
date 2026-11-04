// Flags for the Caribbean and Latin American countries, drawn with CSS gradients (no images),
// straight from the "LinkUp Onboarding" design. Names match WizardOptions on the server.

const h3 = (a: string, b: string, c: string) => 'linear-gradient(' + a + ' 33.33%, ' + b + ' 33.33% 66.66%, ' + c + ' 66.66%)';
const v3 = (a: string, b: string, c: string) => 'linear-gradient(to right, ' + a + ' 33.33%, ' + b + ' 33.33% 66.66%, ' + c + ' 66.66%)';
const tri = (c: string, w: number) => 'linear-gradient(to top right, ' + c + ' 50%, transparent 50%) 0 0/' + w + 'px 12px no-repeat, linear-gradient(to bottom right, ' + c + ' 50%, transparent 50%) 0 12px/' + w + 'px 12px no-repeat';
const dot = (x: number, y: number, r: number, c: string) => 'radial-gradient(circle at ' + x + 'px ' + y + 'px, ' + c + ' 0 ' + r + 'px, transparent ' + (r + 0.5) + 'px)';
const ensign = (badge: string) => dot(26, 12, 4.5, badge) + ', linear-gradient(#C8102E, #C8102E) 7px 0/2px 12px no-repeat, linear-gradient(#C8102E, #C8102E) 0 5px/16px 2px no-repeat, linear-gradient(#fff, #fff) 6px 0/4px 12px no-repeat, linear-gradient(#fff, #fff) 0 4px/16px 4px no-repeat, linear-gradient(to bottom right, transparent 42%, #fff 42% 58%, transparent 58%) 0 0/16px 12px no-repeat, linear-gradient(to top right, transparent 42%, #fff 42% 58%, transparent 58%) 0 0/16px 12px no-repeat, #012169';
const jm = 'transparent calc(50% - 2.5px), #FED100 calc(50% - 2.5px) calc(50% + 2.5px), transparent calc(50% + 2.5px)';
const fr = v3('#002395', '#fff', '#ED2939');
export const caribbeanFlags: Array<[string, string]> = [
  ['Anguilla', ensign('#fff')],
  ['Antigua and Barbuda', 'linear-gradient(to top right, #CE1126 50%, transparent 50%) 0 0/18px 24px no-repeat, linear-gradient(to top left, #CE1126 50%, transparent 50%) 18px 0/18px 24px no-repeat, ' + dot(18, 7, 3, '#FCD116') + ', linear-gradient(#000 40%, #0072C6 40% 60%, #fff 60%)'],
  ['Aruba', dot(7, 7, 2.5, '#EF3340') + ', linear-gradient(transparent 66%, #FBE122 66% 72%, transparent 72% 78%, #FBE122 78% 84%, transparent 84%), #418FDE'],
  ['Bahamas', tri('#000', 18) + ', ' + h3('#00ABC9', '#FAE042', '#00ABC9')],
  ['Barbados', v3('#00267F', '#FFC726', '#00267F')],
  ['Belize', dot(18, 12, 6, '#fff') + ', linear-gradient(#CE1126 10%, #003F87 10% 90%, #CE1126 90%)'],
  ['British Virgin Islands', ensign('#2E8B57')],
  ['Cayman Islands', ensign('#fff')],
  ['Cuba', tri('#CF142B', 16) + ', linear-gradient(#002A8F 20%, #fff 20% 40%, #002A8F 40% 60%, #fff 60% 80%, #002A8F 80%)'],
  ['Curaçao', dot(6, 5, 1.8, '#fff') + ', ' + dot(10, 9, 2.2, '#fff') + ', linear-gradient(transparent 62.5%, #F9E814 62.5% 75%, transparent 75%), #002B7F'],
  ['Dominica', dot(18, 12, 5, '#D41C30') + ', linear-gradient(to right, transparent 14px, #FCD116 14px 16px, #000 16px 18px, #fff 18px 20px, transparent 20px), linear-gradient(transparent 8px, #FCD116 8px 10px, #000 10px 12px, #fff 12px 14px, transparent 14px), #006B3F'],
  ['Dominican Republic', 'linear-gradient(#fff, #fff) center/100% 4px no-repeat, linear-gradient(#fff, #fff) center/5px 100% no-repeat, linear-gradient(to right, #002D62 50%, #CE1126 50%) top/100% 50% no-repeat, linear-gradient(to right, #CE1126 50%, #002D62 50%) bottom/100% 50% no-repeat'],
  ['Grenada', dot(18, 12, 3, '#CE1126') + ', conic-gradient(from -59deg, #FCD116 0 118deg, #007A5E 0 180deg, #FCD116 0 298deg, #007A5E 0) 3px 3px/30px 18px no-repeat, #CE1126'],
  ['Guadeloupe', fr],
  ['Guyana', tri('#CE1126', 15) + ', ' + tri('#000', 18) + ', ' + tri('#FCD116', 34) + ', ' + tri('#fff', 36) + ', #009E49'],
  ['Haiti', 'linear-gradient(#00209F 50%, #D21034 50%)'],
  ['Jamaica', 'linear-gradient(to top right, ' + jm + '), linear-gradient(to bottom right, ' + jm + '), conic-gradient(from -56.3deg, #009B3A 0 112.6deg, #000 0 180deg, #009B3A 0 292.6deg, #000 0)'],
  ['Martinique', fr],
  ['Montserrat', ensign('#5DA9E9')],
  ['Puerto Rico', tri('#0050F0', 16) + ', linear-gradient(#ED0000 20%, #fff 20% 40%, #ED0000 40% 60%, #fff 60% 80%, #ED0000 80%)'],
  ['Saint Kitts and Nevis', 'linear-gradient(to bottom right, #009E49 38%, #FCD116 38% 42%, #000 42% 58%, #FCD116 58% 62%, #CE1126 62%)'],
  ['Saint Lucia', 'linear-gradient(to top left, #FCD116 50%, transparent 50%) 10px 12px/8px 10px no-repeat, linear-gradient(to top right, #FCD116 50%, transparent 50%) 18px 12px/8px 10px no-repeat, linear-gradient(to top left, #000 50%, transparent 50%) 10px 3px/8px 19px no-repeat, linear-gradient(to top right, #000 50%, transparent 50%) 18px 3px/8px 19px no-repeat, linear-gradient(to top left, #fff 50%, transparent 50%) 8px 1px/10px 21px no-repeat, linear-gradient(to top right, #fff 50%, transparent 50%) 18px 1px/10px 21px no-repeat, #66CCFF'],
  ['Saint Vincent and the Grenadines', 'linear-gradient(to right, #002674 25%, #FCD116 25% 75%, #009E60 75%)'],
  ['Sint Maarten', tri('#fff', 18) + ', linear-gradient(#DC171D 50%, #012A87 50%)'],
  ['Suriname', dot(18, 12, 3, '#ECC81D') + ', linear-gradient(#377E3F 20%, #fff 20% 30%, #B40A2D 30% 70%, #fff 70% 80%, #377E3F 80%)'],
  ['Trinidad and Tobago', 'linear-gradient(to top right, #CE1126 36%, #fff 36% 40%, #000 40% 60%, #fff 60% 64%, #CE1126 64%)'],
  ['Turks and Caicos Islands', ensign('#FCD116')],
  ['U.S. Virgin Islands', dot(18, 12, 5, '#F4C430') + ', #fff']
];
export const latinAmericaFlags: Array<[string, string]> = [
  ['Argentina', dot(18, 12, 2.5, '#F6B40E') + ', ' + h3('#74ACDF', '#fff', '#74ACDF')],
  ['Bolivia', h3('#D52B1E', '#F9E300', '#007934')],
  ['Brazil', dot(18, 12, 4.5, '#002776') + ', linear-gradient(to top left, #FEDF00 50%, transparent 50%) 3px 3px/15px 9px no-repeat, linear-gradient(to top right, #FEDF00 50%, transparent 50%) 18px 3px/15px 9px no-repeat, linear-gradient(to bottom left, #FEDF00 50%, transparent 50%) 3px 12px/15px 9px no-repeat, linear-gradient(to bottom right, #FEDF00 50%, transparent 50%) 18px 12px/15px 9px no-repeat, #009C3B'],
  ['Chile', dot(6, 6, 2, '#fff') + ', linear-gradient(#0039A6, #0039A6) 0 0/12px 12px no-repeat, linear-gradient(#fff 50%, #D52B1E 50%)'],
  ['Colombia', 'linear-gradient(#FCD116 50%, #003893 50% 75%, #CE1126 75%)'],
  ['Costa Rica', 'linear-gradient(#002B7F 16.66%, #fff 16.66% 33.33%, #CE1126 33.33% 66.66%, #fff 66.66% 83.33%, #002B7F 83.33%)'],
  ['Ecuador', dot(18, 12, 3, '#6B4E16') + ', linear-gradient(#FFDD00 50%, #034EA2 50% 75%, #ED1C24 75%)'],
  ['El Salvador', h3('#0047AB', '#fff', '#0047AB')],
  ['Guatemala', v3('#4997D0', '#fff', '#4997D0')],
  ['Honduras', h3('#00BCE4', '#fff', '#00BCE4')],
  ['Mexico', dot(18, 12, 2.5, '#8C5A2B') + ', ' + v3('#006847', '#fff', '#CE1126')],
  ['Nicaragua', h3('#0067C6', '#fff', '#0067C6')],
  ['Panama', dot(9, 6, 2, '#005293') + ', ' + dot(27, 18, 2, '#D21034') + ', linear-gradient(to right, #fff 50%, #D21034 50%) top/100% 50% no-repeat, linear-gradient(to right, #005293 50%, #fff 50%) bottom/100% 50% no-repeat'],
  ['Paraguay', h3('#D52B1E', '#fff', '#0038A8')],
  ['Peru', v3('#D91023', '#fff', '#D91023')],
  ['Uruguay', dot(6, 6, 3, '#FCD116') + ', linear-gradient(#fff, #fff) 0 0/13px 13px no-repeat, repeating-linear-gradient(#fff 0 2.667px, #0038A8 2.667px 5.333px)'],
  ['Venezuela', h3('#FFCC00', '#00247D', '#CF142B')]
];

const all = new Map<string, string>([...caribbeanFlags, ...latinAmericaFlags]);

/** Inline style for a flag swatch; `extra` overrides size or position. */
export function flagStyle(country: string, extra = ''): string {
    const bg = all.get(country);
    if (!bg) return '';
    return `flex: none; display: inline-block; width: 36px; height: 24px; border-radius: 4px; box-shadow: inset 0 0 0 1px rgba(11,21,48,0.15); background: ${bg};${extra}`;
}
