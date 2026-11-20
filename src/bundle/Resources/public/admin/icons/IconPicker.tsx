import React, { useMemo, useState } from 'react';

import { Dialog } from '@masilia-admin-kit/Dialog';
import { IconSvg } from '@masilia-admin-kit/IconSvg';
import { Button } from '@masilia-admin-kit/ids';
import { SearchInput } from '@masilia-admin-kit/SearchInput';
import { Spinner } from '@masilia-admin-kit/Spinner';
import { t } from './messages';
import { page, searchIcons } from './pickerSearch';
import type { ItemDto } from './types';

const PAGE = 120;

interface IconPickerProps {
    item: ItemDto;
    icons: readonly string[];
    saving: boolean;
    error?: string;
    onChoose: (icon: string) => void;
    onClose: () => void;
}

/** The icons of Ibexa, to choose one: a search over their names, and the grid of the best matches. */
export function IconPicker({ item, icons, saving, error, onChoose, onClose }: IconPickerProps): React.ReactElement {
    const [query, setQuery] = useState('');
    const [selected, setSelected] = useState<string | null>(null);
    const [limit, setLimit] = useState(PAGE);

    const found = useMemo(() => searchIcons(icons, query), [icons, query]);
    const { shown, more } = page(found, limit);

    return (
        <Dialog
            title={t('picker.title', { name: item.name })}
            variant="panel"
            onClose={onClose}
            closeLabel={t('action.close')}
            footer={(
                <>
                    <span className="masilia-icons-picker__status" role="status" aria-live="polite">
                        {saving ? <><Spinner label={t('picker.saving')} /> {t('picker.saving')}</> : null}
                    </span>
                    <Button type="tertiary" onClick={onClose}>{t('action.cancel')}</Button>
                    <Button type="primary" disabled={saving || selected === null} onClick={() => { if (selected !== null) { onChoose(selected); } }}>{t('action.save')}</Button>
                </>
            )}
        >
            <div className="masilia-icons-picker">
                <p className="masilia-icons-picker__current">
                    <span>{t('picker.current')}:</span>{' '}
                    {item.icon !== null ? <><IconSvg name={item.icon} size="medium" /> <code>{item.icon}</code></> : <span className="masilia-icons-muted">{t('picker.nothing')}</span>}
                    {selected !== null ? <> <span aria-hidden="true">→</span> <span>{t('picker.selected')}:</span> <IconSvg name={selected} size="medium" /> <code>{selected}</code></> : null}
                </p>
                {error !== undefined ? <p className="ibexa-form-error__row" role="alert">{error}</p> : null}
                <SearchInput value={query} onChange={(value) => { setQuery(value); setLimit(PAGE); }} placeholder={t('picker.search')} clearLabel={t('list.search.clear')} />
                <p className="masilia-icons-muted" role="status">{t('picker.count', { shown: shown.length, total: found.length })}</p>
                {found.length === 0 ? <p className="masilia-icons-muted">{t('picker.empty')}</p> : (
                    <ul className="masilia-icons-grid" aria-label={t('picker.title', { name: item.name })}>
                        {shown.map((name) => (
                            <li key={name}>
                                <button
                                    type="button"
                                    className={`masilia-icons-grid__item${name === selected ? ' masilia-icons-grid__item--selected' : ''}${name === item.icon ? ' masilia-icons-grid__item--current' : ''}`}
                                    aria-pressed={name === selected}
                                    title={name}
                                    onClick={() => setSelected(name)}
                                    onDoubleClick={() => onChoose(name)}
                                >
                                    <IconSvg name={name} size="medium" />
                                    <span className="masilia-icons-grid__name">{name}</span>
                                </button>
                            </li>
                        ))}
                    </ul>
                )}
                {more > 0 ? <div><Button type="secondary" size="small" onClick={() => setLimit(limit + PAGE)}>{t('picker.more')}</Button></div> : null}
            </div>
        </Dialog>
    );
}
