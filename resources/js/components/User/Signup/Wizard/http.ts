// Small JSON POST helper for the wizard's background calls (phone code, location).
// Laravel's XSRF cookie is sent back as a header, like axios does.

export interface JsonResult<T> {
    ok: boolean;
    status: number;
    data: T & { message?: string; errors?: Record<string, string[]> };
}

function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

export async function postJson<T = Record<string, unknown>>(url: string, body: Record<string, unknown>): Promise<JsonResult<T>> {
    try {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': xsrfToken(),
            },
            body: JSON.stringify(body),
        });
        const data = await response.json().catch(() => ({}));
        return { ok: response.ok, status: response.status, data };
    } catch {
        return { ok: false, status: 0, data: { message: 'No connection. Check your internet and try again.' } as T & { message: string } };
    }
}

/** First error message from a failed JSON response. */
export function firstError(result: JsonResult<unknown>, fallback: string): string {
    if (result.status === 429) return 'Too many tries. Wait a few minutes and try again.';
    const errors = result.data?.errors;
    if (errors) {
        const first = Object.values(errors)[0];
        if (first?.[0]) return first[0];
    }
    return result.data?.message || fallback;
}
