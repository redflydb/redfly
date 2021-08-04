Ext.define(
    'REDfly.view.admin.import.ErrorList',
    {
        autoLoad: false,
        columns: [{
            align: 'left',
            dataIndex: 'filename',
            text: 'File Name',
            width: 200
        }, {
            align: 'left',        
            dataIndex: 'line',
            text: 'Line',
            width: 50
        }, {
            align: 'left',
            cellWrap: true,
            dataIndex: 'error',
            flex: 1,
            text: 'Error'
        }],
        extend: 'Ext.grid.Panel',
        requires: [
            'REDfly.store.import.Errors'
        ],
        store: {
            type: 'import-errors'
        },
        xtype: 'import-error-list'
    }
);