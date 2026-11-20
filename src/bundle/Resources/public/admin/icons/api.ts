import { request } from '@masilia-admin-kit/http';
import type { ApiResult, Fetcher } from '@masilia-admin-kit/http';
import type { IconKind, IconsData } from './types';

/** The base of the API of the page, from the address of its `data` route (the page knows its siteaccess prefix: the script does not guess it). */
export function apiBase(dataUrl: string): string {
    return dataUrl.replace(/\/data$/, '');
}

/** The calls of the icons page: one function for each thing it asks of the server. */
export function createApi(fetcher: Fetcher, failed: string, base: string) {
    const target = (kind: IconKind, identifier: string): string => `${base}/${kind}/${encodeURIComponent(identifier)}`;

    return {
        data: () => request<IconsData>(fetcher, 'GET', `${base}/data`, undefined, failed),
        set: (kind: IconKind, identifier: string, icon: string) => request<{ icon: string }>(fetcher, 'PUT', target(kind, identifier), { icon }, failed),
        remove: (kind: IconKind, identifier: string) => request<{ identifier: string }>(fetcher, 'DELETE', target(kind, identifier), undefined, failed),
    };
}

export type Api = ReturnType<typeof createApi>;
export type { ApiResult };
