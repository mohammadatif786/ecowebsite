// Shapes shared by the wizard page and its step components. Each step's answer has the same
// shape the server saves (SaveWizardStepRequest), so drafts can be posted as they are.

export type StepKey =
    | 'home'
    | 'languages'
    | 'phone'
    | 'birthday'
    | 'interests'
    | 'gender'
    | 'distance'
    | 'age_range'
    | 'show_me'
    | 'roots'
    | 'link_with'
    | 'bio'
    | 'work'
    | 'university'
    | 'intent'
    | 'photos';

export interface HomeAnswer {
    country: string; // ISO code, e.g. "BS"
    country_name: string;
    state: string;
    state_name: string;
    city: string;
    latitude?: number | null;
    longitude?: number | null;
}

export interface Answers {
    home: HomeAnswer;
    languages: { languages: string[] };
    phone: { phone_number?: string };
    birthday: { birthday: string; show_age: boolean };
    interests: { interests: string[] };
    gender: { gender: string; show_gender: boolean };
    distance: { km: number; unit: 'km' | 'mi' };
    age_range: { min: number; max: number };
    show_me: { show_me: string };
    roots: { roots: string };
    link_with: { countries: string[] };
    bio: { bio: string };
    work: { work: string };
    university: { no_university: boolean; university: string; location: string; country: string | null; studying: boolean };
    intent: { intents: string[] };
    photos: Record<string, never>;
}

/** What the server sends back for a saved step (a skipped step is just {skipped: true}). */
export type SavedAnswers = Partial<{ [K in StepKey]: Answers[K] | { skipped: true } }>;

export interface WizardOptions {
    languages: Array<{ name: string; native: string }>;
    interests: string[];
    genders: string[];
    showMe: string[];
    intents: Array<{ title: string; items: Array<{ id: string; title: string; desc: string }> }>;
    caribbean: string[];
    latinAmerica: string[];
    workFields: string[];
    maxPhotos: number;
}

export interface WizardMember {
    firstName: string;
    lastName: string;
    phone: string | null;
    phoneVerified: boolean;
}

export interface WizardUrls {
    wizard: string;
    saveStep: string;
    complete: string;
    locate: string;
    phoneSend: string;
    phoneVerify: string;
    logout: string;
}

/** A photo picked on the last step. */
export interface PickedPhoto {
    file: File;
    preview: string;
}
