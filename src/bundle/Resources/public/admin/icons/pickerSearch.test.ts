import assert from 'node:assert/strict';
import { test } from 'node:test';

import { page, searchIcons } from './pickerSearch';

const ids = ['calendar', 'calendar-check', 'folder', 'home', 'text-paragraph', 'user-add'];

test('a whole name comes before the names that contain it', () => {
    assert.deepEqual(searchIcons(ids, 'calendar'), ['calendar', 'calendar-check']);
});

test('words match the parts of a name, in any order, and one-letter words are ignored', () => {
    assert.deepEqual(searchIcons(ids, 'add a user'), ['user-add']);
    assert.deepEqual(searchIcons(ids, 'paragraph text'), ['text-paragraph']);
});

test('nothing typed shows every icon, and a word that matches nothing shows none', () => {
    assert.deepEqual(searchIcons(ids, '   '), ids);
    assert.deepEqual(searchIcons(ids, 'zzz'), []);
});

test('a page of icons says how many more there are', () => {
    assert.deepEqual(page(ids, 4), { shown: ids.slice(0, 4), more: 2 });
    assert.deepEqual(page(ids, 10), { shown: ids, more: 0 });
});
