import React, { useMemo } from 'react';

import { iconPath } from '@masilia-admin-kit/icons';
import { AssetsProvider, TranslatorProvider } from '@masilia-admin-kit/ids';
import { Notice } from '@masilia-admin-kit/Notice';
import { Spinner } from '@masilia-admin-kit/Spinner';
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
        body = <p className="masilia-icons-muted" role="status"><Spinner label={t('ui.loading')} /> {t('ui.loading')}</p>;
    } else if (failed || data === null) {
        body = <p className="ibexa-alert ibexa-alert--error" role="alert">{t('ui.load_failed')}</p>;
    } else {
        body = (
            <>
                <IconsList api={api} data={data} onChange={setData} />
                {data.assistant && data.canManage ? <Notice icon="about-info">{t('ui.assistant')}</Notice> : null}
            </>
        );
    }

    return (
        <TranslatorProvider value={{ trans: (id) => t(id) }}>
            <AssetsProvider value={{ getIconPath: iconPath }}>
                <div className="masilia-icons-react">
                    <Notice>{t('ui.intro')}</Notice>
                    {body}
                </div>
            </AssetsProvider>
        </TranslatorProvider>
    );
}
