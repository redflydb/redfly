Ext.define(
    'REDfly.store.dynamic.Chromosome',
    {
        alias: 'store.dynamic-chromosome',
        extend: 'Ext.data.Store',
        model: 'REDfly.model.dynamic.Chromosome',
        proxy: {
            reader: {
                messageProperty: 'error',
                rootProperty: 'results',
                type: 'json'
            },
            type: 'ajax',
            url: REDfly.Config.getApiV2Url() + '/dynamic/chromosome'
        },
        sorters: [{
            direction: 'ASC',
            property: 'display_sort'
        }]
    }
);