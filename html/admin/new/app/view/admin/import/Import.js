Ext.define(
    'REDfly.view.admin.import.Import',
    {
        controller: 'import',
        extend: 'Ext.panel.Panel',
        items: [{
            border: false,
            padding: '10, 100, 10, 100',
            width: 800,
            xtype: 'import-form'
        }, {
            height: 400,
            reference: 'error-list',
            xtype: 'import-error-list'
        }],
        requires: [
            'REDfly.view.admin.import.ErrorList',
            'REDfly.view.admin.import.Form',
            'REDfly.view.admin.import.ImportController',
            'REDfly.view.admin.import.ImportViewModel'
        ],
        viewModel: {
            type: 'import'
        },
        xtype: 'import'
    }
);