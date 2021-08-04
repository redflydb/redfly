Ext.define(
    'REDfly.store.dynamic.Curator',
    {
        alias: 'store.dynamic-curator',
        extend: 'Ext.data.Store',
        model: 'REDfly.model.dynamic.Curator',
        proxy: {
            reader: {
                messageProperty: 'error',
                rootProperty: 'results',
                type: 'json'
            },
            type: 'ajax',
            url: REDfly.Config.getApiV2Url() + '/dynamic/curator'
        },
        sorters: [{
            direction: 'ASC',
            property: 'first_name'
        }]
    }
);