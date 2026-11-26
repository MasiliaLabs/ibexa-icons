import React, { useMemo } from 'react';

import { AdminProviders } from '@masilia-admin-kit/AdminProviders';
import { Alert } from '@masilia-admin-kit/Alert';
import { Notice } from '@masilia-admin-kit/Notice';
import { Spinner } from '@masilia-admin-kit/Spinner';
import { Muted } from '@masilia-admin-kit/Layout';
import { apiBase, createApi } from './api';
import { IconsList } from './IconsList';
import { t } from './messages';
import { useIcons } from './useIcons';

interface IconsAppProps {
    /** The address of the `data` route of the API, as the page's siteaccess builds it */
    dataUrl: string;
}

/** The icons page: the content types and groups with their icon, and the panel that chooses one among the icons of Ibexa. */
export function IconsApp({ dataUrl }: IconsAppProps): React.ReactElement {
    const api = useMemo(() => createApi((input, init) => window.fetch(input, init), t('ui.failed'), apiBase(dataUrl)), [dataUrl]);
    const { data, loading, failed, setData } = useIcons(api);

    let body: React.ReactElement;
    if (loading) {
        body = <p role="status"><Muted><Spinner label={t('ui.loading')} /> {t('ui.loading')}</Muted></p>;
    } else if (failed || data === null) {
        body = <Alert type="error" title={t('ui.load_failed')} />;
    } else {
        body = (
            <>
                <IconsList api={api} data={data} onChange={setData} />
                {data.assistant && data.canManage ? <Notice icon="about-info">{t('ui.assistant')}</Notice> : null}
            </>
        );
    }

    return (
        <AdminProviders t={t} className="masilia-icons-react">
            <Notice>{t('ui.intro')}</Notice>
            {body}
        </AdminProviders>
    );
}
