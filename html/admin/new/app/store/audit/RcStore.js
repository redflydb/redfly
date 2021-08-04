Ext.define(
    'REDfly.store.audit.Rc',
    {
        alias: 'store.audit-rc',
        autoload: {
            limit: 0,
            start: 0
        },
        extend: 'Ext.data.Store',
        model: 'REDfly.model.audit.Rc',
        pageSize: 1000000000,
        proxy: {
            reader: {
                messageProperty: 'error',
                rootProperty: 'results',
                type: 'json'
            },
            type: 'ajax',
            url: REDfly.Config.getApiV2Url() + '/audit/rc',
            writer: {
                allowSingle: false,
                type: 'json'
            }
        },
        sorters: [{
            direction: 'ASC',
            property: 'name'
        }]
    }
);