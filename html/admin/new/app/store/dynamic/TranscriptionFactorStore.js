Ext.define(
    'REDfly.store.dynamic.TranscriptionFactor',
    {
        alias: 'store.dynamic-transcription-factor',
        extend: 'Ext.data.Store',
        model: 'REDfly.model.dynamic.TranscriptionFactor',
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