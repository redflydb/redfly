Ext.define(
    'REDfly.model.dynamic.Species',
    {
        extend: 'Ext.data.Model',
        fields: [{
            name: 'display',
            type: 'string'
        }, {
            name: 'id',
            type: 'int'
        }, {
            name: 'scientific_name',
            type: 'string'
        }, {
            name: 'short_name',
            type: 'string'
        }],
        idProperty: 'id'
    }
);