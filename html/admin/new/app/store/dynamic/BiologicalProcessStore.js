Ext.define(
    'REDfly.store.dynamic.BiologicalProcess',
    {
        alias: 'store.dynamic-biological-process',
        extend: 'Ext.data.Store',
        model: 'REDfly.model.dynamic.BiologicalProcess',
        proxy: {
            reader: {
                messageProperty: 'error',
                rootProperty: 'results',
                type: 'json'
            },
            type: 'ajax',
            url: REDfly.Config.getApiV2Url() + '/dynamic/biological_process'
        },
        sorters: [{
            direction: 'ASC',
            property: 'term'
        }]
    }
);