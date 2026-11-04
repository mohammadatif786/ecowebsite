// Inline styles shared by the wizard steps, copied from the "LinkUp Onboarding" design so every
// chip, card, checkbox and switch matches it exactly.

/** Rounded pill button (popular cities, presets, work fields, ...). */
export const chip = (on: boolean) =>
    'min-height: 44px; padding: 0 16px; border-radius: 999px; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer; ' +
    (on ? 'border: 1.5px solid #1D4ED8; background: #1D4ED8; color: #FFFFFF;' : 'border: 1.5px solid #DCE3F0; background: #FFFFFF; color: #1E293B;');

/** Small pill used for the university region filter. */
export const smallChip = (on: boolean) =>
    'min-height: 36px; padding: 0 12px; border-radius: 999px; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; ' +
    (on ? 'border: 1.5px solid #1D4ED8; background: #1D4ED8; color: #FFFFFF;' : 'border: 1.5px solid #DCE3F0; background: #FFFFFF; color: #1E293B;');

/** Selectable card border and background. */
export const card = (on: boolean) => (on ? 'border: 2px solid #1D4ED8; background: #EEF4FF;' : 'border: 1.5px solid #DCE3F0; background: #FFFFFF;');

/** Round or rounded-square icon holder inside a card. */
export const iconBox = (on: boolean, size: number, radius: string) =>
    `flex: none; width: ${size}px; height: ${size}px; border-radius: ${radius}; display: flex; align-items: center; justify-content: center; ` +
    (on ? 'background: #1D4ED8; color: #FFFFFF;' : 'background: #EEF2F8; color: #1D4ED8;');

/** Checkbox square. */
export const checkBox = (on: boolean) =>
    'flex: none; width: 20px; height: 20px; box-sizing: border-box; border-radius: 6px; display: flex; align-items: center; justify-content: center; ' +
    (on ? 'background: #1D4ED8; border: 2px solid #1D4ED8;' : 'background: #FFFFFF; border: 2px solid #CBD5E1;');

/** On/off switch track and knob. */
export const switchTrack = (on: boolean) =>
    'flex: none; position: relative; width: 52px; height: 32px; border-radius: 999px; border: none; padding: 0; cursor: pointer; ' +
    (on ? 'background: #1D4ED8;' : 'background: #94A3B8;');
export const switchKnob = (on: boolean) =>
    'position: absolute; top: 4px; width: 24px; height: 24px; border-radius: 50%; background: #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.2); ' +
    (on ? 'left: 24px;' : 'left: 4px;');

/** Segmented control button (km / mi). */
export const segment = (on: boolean) =>
    'min-width: 52px; min-height: 40px; padding: 0 14px; border-radius: 9px; border: none; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; ' +
    (on ? 'background: #FFFFFF; color: #0B1530; box-shadow: 0 1px 3px rgba(11,21,48,0.12);' : 'background: transparent; color: #475569;');

const selectBase =
    'appearance: none; -webkit-appearance: none; width: 100%; height: 54px; padding: 0 16px; border-radius: 14px; border: 1.5px solid #CBD5E1; font-family: inherit; font-size: 16px; font-weight: 500; ';
/** Select box, enabled or waiting for the field before it. */
export const select = (enabled = true) =>
    selectBase + (enabled ? 'background: #FFFFFF; color: #0B1530; cursor: pointer;' : 'background: #EEF2F8; color: #64748B; cursor: not-allowed;');

/** Text box. */
export const textField =
    'width: 100%; height: 54px; box-sizing: border-box; padding: 0 16px; border-radius: 14px; border: 1.5px solid #CBD5E1; background: #FFFFFF; font-family: inherit; font-size: 16px; color: #0B1530;';

/** Search box above a long list. */
export const searchField =
    'height: 46px; box-sizing: border-box; padding: 0 16px; border-radius: 14px; border: 1.5px solid #CBD5E1; background: #FFFFFF; font-family: inherit; font-size: 15px; color: #0B1530;';

/** Big blue button, or grey when it can't be pressed yet. */
export const primaryButton = (ready: boolean) =>
    'display: flex; align-items: center; justify-content: center; gap: 10px; height: 58px; border-radius: 14px; border: none; font-family: inherit; font-size: 17px; font-weight: 700; ' +
    (ready ? 'background: #1D4ED8; color: #FFFFFF; cursor: pointer; box-shadow: 0 10px 24px rgba(29,78,216,0.28);' : 'background: #DCE3F0; color: #64748B; cursor: not-allowed;');

export const label = 'font-size: 14px; font-weight: 700; color: #1E293B;';
export const groupTitle = 'font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.06em;';
export const fieldError = 'margin: 0; font-size: 13px; font-weight: 600; color: #B91C1C;';
