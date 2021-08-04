Ext.define(
    'REDfly.store.audit.RcTs',
    {
        alias: 'store.audit-rc-ts',
        autoload: {
            limit: 0,
            start: 0
        },
        extend: 'Ext.data.Store',
        model: 'REDfly.model.audit.RcTs',
        pageSize: 1000000000,
        proxy: {
            reader: {
                messageProperty: 'error',
                rootProperty: 'results',
                type: 'json'
            },
            type: 'ajax',
            url: REDfly.Config.getApiV2Url() + '/audit/rc_ts',
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