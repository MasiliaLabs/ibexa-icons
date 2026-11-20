import { useCallback, useEffect, useState } from 'react';

import type { Api } from './api';
import type { IconsData } from './types';

export interface IconsState {
    data: IconsData | null;
    loading: boolean;
    failed: boolean;
    setData: (data: IconsData) => void;
    reload: () => Promise<void>;
}

/** The content types and groups with their icons, and the icon set, loaded once; a change updates the list without loading it again. */
export function useIcons(api: Api): IconsState {
    const [data, setData] = useState<IconsData | null>(null);
    const [loading, setLoading] = useState(true);
    const [failed, setFailed] = useState(false);

    const reload = useCallback(async (): Promise<void> => {
        const result = await api.data();
        if (result.ok) {
            setData(result.data);
            setFailed(false);
        } else {
            setFailed(true);
        }
        setLoading(false);
    }, [api]);

    useEffect(() => {
        void reload();
    }, [reload]);

    return { data, loading, failed, setData, reload };
}
