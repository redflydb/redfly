Ext.define(
    'REDfly.store.audit.CrmSegment',
    {
        alias: 'store.audit-crm-segment',
        autoload: {
            limit: 0,
            start: 0
        },
        extend: 'Ext.data.Store',
        model: 'REDfly.model.audit.CrmSegment',
        pageSize: 1000000000,
        proxy: {
            reader: {
                messageProperty: 'error',
                rootProperty: 'results',
                type: 'json'
            },
            type: 'ajax',
            url: REDfly.Config.getApiV2Url() + '/audit/crm_segment',
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