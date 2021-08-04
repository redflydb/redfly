Ext.define(
    'REDfly.store.dynamic.AnatomicalExpression',
    {
        alias: 'store.dynamic-anatomical-expression',
        extend: 'Ext.data.Store',
        model: 'REDfly.model.dynamic.AnatomicalExpression',
        proxy: {
            reader: {
                messageProperty: 'error',
                rootProperty: 'results',
                type: 'json'
            },
            type: 'ajax',
            url: REDfly.Config.getApiV2Url() + '/dynamic/anatomical_expression'
        },
        sorters: [{
            direction: 'ASC',
            property: 'term'
        }]
    }
);