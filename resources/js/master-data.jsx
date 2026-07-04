import React from 'react';
import { createRoot } from 'react-dom/client';
import MasterDataApp from './Components/MasterData/MasterDataApp';

const container = document.getElementById('react-master-data-root');
if (container) {
    const root = createRoot(container);
    root.render(<MasterDataApp />);
}
