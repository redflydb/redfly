Ext.define(
    'REDfly.store.dynamic.Gene',
    {
        alias: 'store.dynamic-gene',
        extend: 'Ext.data.Store',
        model: 'REDfly.model.dynamic.Gene',
        proxy: {
            reader: {
                messageProperty: 'error',
                rootProperty: 'results',
                type: 'json'
            },
            type: 'ajax',
            url: REDfly.Config.getApiV2Url() + '/dynamic/gene'
        },
        sorters: [{
            direction: 'ASC',
            property: 'name'
        }]
    }
);