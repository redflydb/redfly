Ext.define(
    'REDfly.store.import.Errors',
    {
        alias: 'store.import-errors',
        extend: 'Ext.data.Store',
        model: 'REDfly.model.import.Error',
        proxy: {
            type: 'memory',
            reader: {
                messageProperty: 'error',
                rootProperty: 'results',
                type: 'json'
            }
        }
    }
);