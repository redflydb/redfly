Ext.define(
    'REDfly.model.import.Error',
    {
        extend: 'Ext.data.Model',
        fields: [{
            name: 'error',
            type: 'string'
        }, {
            name: 'filename',
            type: 'string'
        }, {
            name: 'line',
            type: 'int'
        }]
    }
)