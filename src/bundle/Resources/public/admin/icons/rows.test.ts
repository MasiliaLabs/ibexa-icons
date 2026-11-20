import assert from 'node:assert/strict';
import { test } from 'node:test';

import { countMissing, filterItems, itemKey, withIcon } from './rows';
import type { ItemDto } from './types';

const items: ItemDto[] = [
    { kind: 'group', identifier: 'Blocks', name: 'Blocks', group: '', icon: 'folder', source: 'import' },
    { kind: 'type', identifier: 'hero_banner', name: 'Hero banner', group: 'Blocks', icon: null, source: 'none' },
    { kind: 'type', identifier: 'article', name: 'Article', group: 'Content', icon: 'news', source: 'config' },
    { kind: 'type', identifier: 'event', name: 'Event', group: 'Content', icon: 'calendar', source: 'admin' },
];

test('a search matches the name, the identifier, the group and the icon, whatever the case', () => {
    assert.deepEqual(filterItems(items, { query: 'HERO', kind: '', from: '' }).map((i) => i.identifier), ['hero_banner']);
    assert.deepEqual(filterItems(items, { query: 'content', kind: '', from: '' }).map((i) => i.identifier), ['article', 'event']);
    assert.deepEqual(filterItems(items, { query: 'calen', kind: '', from: '' }).map((i) => i.identifier), ['event']);
    assert.equal(filterItems(items, { query: '  ', kind: '', from: '' }).length, 4);
});

test('the filters of the kind and of where the icon comes from narrow the list', () => {
    assert.deepEqual(filterItems(items, { query: '', kind: 'group', from: '' }).map((i) => i.identifier), ['Blocks']);
    assert.deepEqual(filterItems(items, { query: '', kind: '', from: 'none' }).map((i) => i.identifier), ['hero_banner']);
    assert.deepEqual(filterItems(items, { query: 'e', kind: 'type', from: 'admin' }).map((i) => i.identifier), ['event']);
});

test('a type and a group of the same name do not share a key', () => {
    assert.notEqual(itemKey({ kind: 'type', identifier: 'x' }), itemKey({ kind: 'group', identifier: 'x' }));
});

test('the items without an icon are counted', () => {
    assert.equal(countMissing(items), 1);
});

test('choosing an icon marks the item as chosen by a person, taking it back leaves it with none', () => {
    const chosen = withIcon(items, { kind: 'type', identifier: 'hero_banner' }, 'banner');
    assert.deepEqual([chosen[1].icon, chosen[1].source], ['banner', 'admin']);
    assert.equal(chosen[0], items[0], 'the others are the same objects');

    const back = withIcon(chosen, { kind: 'type', identifier: 'hero_banner' }, null);
    assert.deepEqual([back[1].icon, back[1].source], [null, 'none']);
});
