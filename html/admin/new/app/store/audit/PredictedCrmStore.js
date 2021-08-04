Ext.define(
    'REDfly.store.audit.PredictedCrm',
    {
        alias: 'store.audit-predicted-crm',
        autoload: {
            limit: 0,
            start: 0
        },
        extend: 'Ext.data.Store',
        model: 'REDfly.model.audit.PredictedCrm',
        pageSize: 1000000000,
        proxy: {
            url: REDfly.Config.getApiV2Url() + '/audit/predicted_crm',
            reader: {
                messageProperty: 'error',
                rootProperty: 'results',
                type: 'json'
            },
            type: 'ajax',
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