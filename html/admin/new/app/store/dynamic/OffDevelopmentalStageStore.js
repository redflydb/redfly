Ext.define(
    'REDfly.store.dynamic.OffDevelopmentalStage',
    {
        alias: 'store.dynamic-off-developmental-stage',
        extend: 'Ext.data.Store',
        model: 'REDfly.model.dynamic.OffDevelopmentalStage',
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