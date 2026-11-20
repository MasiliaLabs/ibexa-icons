export type IconKind = 'type' | 'group';

/** Where an icon comes from: the registry (admin, ai, import, default), the project's YAML (config), or nowhere (none: Ibexa's default icon). */
export type IconSource = 'admin' | 'ai' | 'import' | 'default' | 'config' | 'none';

/** A content type or a group, with the icon it has. */
export interface ItemDto {
    kind: IconKind;
    identifier: string;
    name: string;
    group: string;
    icon: string | null;
    source: IconSource;
}

export interface IconsData {
    canManage: boolean;
    /** The assistant of masilia/ibexa-ai is installed: it can choose icons */
    assistant: boolean;
    items: ItemDto[];
    /** Every icon of the Ibexa icon set, by name */
    icons: string[];
}

export const SOURCES: readonly IconSource[] = ['admin', 'ai', 'import', 'default', 'config', 'none'];
