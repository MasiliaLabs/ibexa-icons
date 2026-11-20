import type { IconKind, IconSource, ItemDto } from './types';

export interface Filters {
    query: string;
    kind: '' | IconKind;
    from: '' | IconSource;
}

/** The key of an item in a list: a type and a group can have the same name. */
export function itemKey(item: Pick<ItemDto, 'kind' | 'identifier'>): string {
    return `${item.kind}:${item.identifier}`;
}

/** Whether an item matches what was typed in the search box: its name, identifier, group or icon. */
export function matches(item: ItemDto, query: string): boolean {
    const needle = query.trim().toLowerCase();
    if (needle === '') {
        return true;
    }

    return [item.name, item.identifier, item.group, item.icon ?? ''].some((text) => text.toLowerCase().includes(needle));
}

/** The items to show: only what the filters let through, in the order of the repository (each group, then its types). */
export function filterItems(items: readonly ItemDto[], filters: Filters): ItemDto[] {
    return items.filter((item) => (filters.kind === '' || item.kind === filters.kind)
        && (filters.from === '' || item.source === filters.from)
        && matches(item, filters.query));
}

/** How many items have no icon at all (Ibexa shows its default one). */
export function countMissing(items: readonly ItemDto[]): number {
    return items.filter((item) => item.icon === null).length;
}

/** The item changed to have an icon chosen in the admin, or its choice taken back (no icon in the registry: the YAML or nothing answers again). */
export function withIcon(items: readonly ItemDto[], target: Pick<ItemDto, 'kind' | 'identifier'>, icon: string | null): ItemDto[] {
    return items.map((item) => (item.kind === target.kind && item.identifier === target.identifier
        ? { ...item, icon, source: icon === null ? 'none' : 'admin' }
        : item));
}
