Ext.define(
    'REDfly.store.dynamic.State',
    {
        alias: 'store.dynamic-state',
        extend: 'Ext.data.Store',
        model: 'REDfly.model.dynamic.State',
        data: [{
            id: 0,
            state: 'approval',
            display: 'Approval'
        }, {
            id: 1,
            state: 'approved',
            display: 'Approved'
        }, {
            id: 2,
            state: 'deleted',
            display: 'Deleted'
        }, {
            id: 3,
            state: 'editing',
            display: 'Editing'
        }]
    }
);