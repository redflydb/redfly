Ext.define(
    'REDfly.store.audit.CrmSegmentNoTs',
    {
        alias: 'store.audit-crm-segment-no-ts',
        autoload: {
            limit: 0,
            start: 0
        },
        extend: 'Ext.data.Store',
        model: 'REDfly.model.audit.CrmSegmentNoTs',
        pageSize: 1000000000,
        proxy: {
            reader: {
                messageProperty: 'error',
                rootProperty: 'results',
                type: 'json'
            },
            type: 'ajax',
            url: REDfly.Config.getApiV2Url() + '/audit/crm_segment_no_ts',
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