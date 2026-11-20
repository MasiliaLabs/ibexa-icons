import React, { StrictMode } from 'react';
import type { ReactNode } from 'react';

import { IconsApp } from './IconsApp';

const ROOT_ID = 'masilia-icons-root';

interface ReactDomClient {
    createRoot: (container: Element) => { render: (node: ReactNode) => void };
}

/**
 * Ibexa loads the React DOM client API as the global `ReactDOMClient` (react-dom-load); the `react-dom` default export has no
 * `createRoot` under React 19.
 */
function mount(): void {
    const client = (window as unknown as { ReactDOMClient?: ReactDomClient }).ReactDOMClient;
    const container = document.getElementById(ROOT_ID);
    if (client === undefined || container === null) {
        return;
    }

    client.createRoot(container).render(
        <StrictMode>
            <IconsApp dataUrl={container.dataset.api ?? '/api/masilia/icons/data'} />
        </StrictMode>,
    );
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mount);
} else {
    mount();
}
