Ext.define(
    'REDfly.store.dynamic.OnDevelopmentalStage',
    {
        alias: 'store.dynamic-on-developmental-stage',
        extend: 'Ext.data.Store',
        model: 'REDfly.model.dynamic.OnDevelopmentalStage',
        proxy: {
            reader: {
                messageProperty: 'error',
                rootProperty: 'results',
                type: 'json'
            },
            type: 'ajax',
            url: REDfly.Config.getApiV2Url() + '/dynamic/developmental_stage'
        },
        sorters: [{
            direction: 'ASC',
            property: 'term'
        }]
    }
);