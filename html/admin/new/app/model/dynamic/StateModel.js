Ext.define(
    'REDfly.model.dynamic.State',
    {
        extend: 'Ext.data.Model',
        fields: [{
            name: 'display',
            type: 'string'
        }, {
            name: 'id',
            type: 'int'
        }, {
            name: 'state',
            type: 'string'
        }],
        idProperty: 'id'
    }
);