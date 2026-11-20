/**
 * The icons that match words: "calendar" finds "calendar", "calendar-check"... best first. The same rules as the search of the
 * server (Sprite::search), so what the page shows is what the assistant is given.
 */
export function searchIcons(ids: readonly string[], query: string): string[] {
    // One-letter words are in every name: they say nothing.
    const words = query.toLowerCase().split(/[^a-z0-9]+/).filter((word) => word.length >= 2);
    if (words.length === 0) {
        return [...ids];
    }
    const scored: Array<[string, number]> = [];
    for (const id of ids) {
        const parts = id.split('-');
        let score = 0;
        for (const word of words) {
            if (id === word) {
                score += 10;
            } else if (parts.includes(word)) {
                score += 5;
            } else if (id.includes(word)) {
                score += 2;
            }
        }
        if (score > 0) {
            scored.push([id, score]);
        }
    }
    scored.sort((a, b) => (b[1] - a[1]) || (a[0] < b[0] ? -1 : a[0] > b[0] ? 1 : 0));

    return scored.map(([id]) => id);
}

/** The first icons of a list, and how many more there are to show. */
export function page<T>(items: readonly T[], limit: number): { shown: T[]; more: number } {
    return { shown: items.slice(0, limit), more: Math.max(0, items.length - limit) };
}
