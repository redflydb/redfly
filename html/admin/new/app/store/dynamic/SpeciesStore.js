Ext.define(
    'REDfly.store.dynamic.Species',
    {
        alias: 'store.dynamic-species',
        extend: 'Ext.data.Store',
        model: 'REDfly.model.dynamic.Species',
        proxy: {
            reader: {
                messageProperty: 'error',
                rootProperty: 'results',
                type: 'json'
            },
            type: 'ajax',
            url: REDfly.Config.getApiV2Url() + '/dynamic/species'
        },
        sorters: [{
            direction: 'ASC',
            property: 'scientific_name'
        }]
    }
);