// The four password rules and the strength meter from the onboarding design.
// The server is the real gate (it also rejects passwords found in known leaks); this only guides the member.

export interface PasswordRule {
    label: string;
    met: boolean;
}

export const strengthLabels = ['Too weak', 'Weak', 'Fair', 'Good', 'Strong'] as const;
export const strengthColors = ['#DC2626', '#DC2626', '#D97706', '#2563EB', '#16A34A'] as const;

export function passwordRules(password: string): PasswordRule[] {
    return [
        { label: '8+ characters', met: password.length >= 8 },
        { label: 'Upper and lowercase', met: /[a-z]/.test(password) && /[A-Z]/.test(password) },
        { label: 'A number', met: /\d/.test(password) },
        { label: 'A symbol', met: /[^A-Za-z0-9]/.test(password) },
    ];
}

/** 0 to 4: how many of the four rules are met. */
export function passwordScore(password: string): number {
    return passwordRules(password).filter((rule) => rule.met).length;
}
