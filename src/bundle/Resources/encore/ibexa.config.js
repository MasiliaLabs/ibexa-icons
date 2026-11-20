const path = require('path');

module.exports = (Encore) => {
    Encore.addEntry('ibexa-admin-ui-masilia-icons-react-js', [
        path.resolve(__dirname, '../public/admin/icons/index.tsx'),
    ]);
    Encore.addEntry('ibexa-admin-ui-masilia-icons-react-css', [
        path.resolve(__dirname, '../public/admin/icons/icons.scss'),
    ]);
};
