import React, { useMemo, useState } from 'react';

import { FilterCard, FilterSelect } from '@masilia-admin-kit/FilterCard';
import { IconButton } from '@masilia-admin-kit/IconButton';
import { IconSvg } from '@masilia-admin-kit/IconSvg';
import { notify } from '@masilia-admin-kit/notify';
import { SearchInput } from '@masilia-admin-kit/SearchInput';
import { DataTable, TableHeader } from '@masilia-admin-kit/Table';
import type { Column } from '@masilia-admin-kit/Table';
import { Muted } from '@masilia-admin-kit/Layout';
import type { Api } from './api';
import { IconPicker } from './IconPicker';
import { t } from './messages';
import { countMissing, filterItems, itemKey, withIcon } from './rows';
import type { Filters } from './rows';
import { SOURCES } from './types';
import type { IconsData, ItemDto } from './types';

interface IconsListProps {
    api: Api;
    data: IconsData;
    onChange: (data: IconsData) => void;
}

export function IconsList({ api, data, onChange }: IconsListProps): React.ReactElement {
    const [filters, setFilters] = useState<Filters>({ query: '', kind: '', from: '' });
    const [editing, setEditing] = useState<ItemDto | null>(null);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | undefined>(undefined);

    const shown = useMemo(() => filterItems(data.items, filters), [data.items, filters]);
    const missing = countMissing(data.items);

    const choose = async (icon: string): Promise<void> => {
        if (editing === null) {
            return;
        }
        setSaving(true);
        const result = await api.set(editing.kind, editing.identifier, icon);
        setSaving(false);
        if (result.ok) {
            onChange({ ...data, items: withIcon(data.items, editing, icon) });
            notify('success', t('flash.saved', { name: editing.name }));
            setEditing(null);
            setError(undefined);
        } else {
            setError(result.message);
        }
    };

    const remove = async (item: ItemDto): Promise<void> => {
        const result = await api.remove(item.kind, item.identifier);
        if (result.ok) {
            // The YAML of the project or Ibexa's default icon answers again: the server knows which, so ask it.
            const fresh = await api.data();
            onChange(fresh.ok ? fresh.data : { ...data, items: withIcon(data.items, item, null) });
            notify('success', t('flash.removed', { name: item.name }));
        } else {
            notify('error', result.message);
        }
    };

    const columns: Column<ItemDto>[] = [
        {
            key: 'icon',
            header: <span className="masilia-kit-sr-only">{t('column.icon')}</span>,
            render: (item) => (item.icon !== null ? <IconSvg name={item.icon} size="medium" /> : <Muted><span aria-label={t('from.none')}>—</span></Muted>),
        },
        {
            key: 'name',
            header: t('column.name'),
            render: (item) => (
                <>
                    <strong className={item.kind === 'group' ? 'masilia-icons-group' : undefined}>{item.name}</strong>
                    <br />
                    <Muted>{item.kind === 'group' ? t('kind.group') : item.identifier}{item.icon !== null ? ` · ${item.icon}` : ''}</Muted>
                </>
            ),
        },
        { key: 'group', header: t('column.group'), render: (item) => (item.group === '' ? <Muted>—</Muted> : item.group) },
        { key: 'from', header: t('column.from'), render: (item) => <span className={`masilia-icons-from masilia-icons-from--${item.source}`}>{t(`from.${item.source}`)}</span> },
        ...(data.canManage ? [{
            key: 'actions',
            header: <span className="masilia-kit-sr-only">{t('column.actions')}</span>,
            actions: true,
            render: (item: ItemDto) => (
                <>
                    <IconButton icon="edit" label={t('action.choose', { name: item.name })} onClick={() => { setError(undefined); setEditing(item); }} />
                    {item.source === 'admin' || item.source === 'ai' || item.source === 'import' || item.source === 'default'
                        ? <IconButton icon="discard" label={t('action.remove', { name: item.name })} onClick={() => void remove(item)} />
                        : null}
                </>
            ),
        }] : []),
    ];

    return (
        <section className="masilia-icons-list">
            <FilterCard search={<SearchInput value={filters.query} onChange={(query) => setFilters({ ...filters, query })} placeholder={t('list.search')} clearLabel={t('list.search.clear')} />}>
                <FilterSelect
                    label={t('filter.kind')}
                    value={filters.kind}
                    onChange={(kind) => setFilters({ ...filters, kind: kind as Filters['kind'] })}
                    emptyLabel={t('filter.kind.all')}
                    options={[{ value: 'type', label: t('kind.type') }, { value: 'group', label: t('kind.group') }]}
                />
                <FilterSelect
                    label={t('filter.from')}
                    value={filters.from}
                    onChange={(from) => setFilters({ ...filters, from: from as Filters['from'] })}
                    emptyLabel={t('filter.from.all')}
                    options={SOURCES.map((source) => ({ value: source, label: t(`from.${source}`) }))}
                />
            </FilterCard>

            <TableHeader headline={t('list.title')} count={shown.length} notice={missing > 0 ? t('list.missing', { count: missing }) : undefined} />
            <DataTable
                columns={columns}
                rows={shown}
                label={t('list.title')}
                rowKey={itemKey}
                empty={{ info: data.items.length === 0 ? t('list.empty') : t('list.none_found') }}
            />

            {editing !== null ? (
                <IconPicker
                    item={editing}
                    icons={data.icons}
                    saving={saving}
                    error={error}
                    onChoose={(icon) => void choose(icon)}
                    onClose={() => setEditing(null)}
                />
            ) : null}
        </section>
    );
}
