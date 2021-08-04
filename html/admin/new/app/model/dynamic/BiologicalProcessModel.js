Ext.define(
    'REDfly.model.dynamic.BiologicalProcess',
    {
        extend: 'Ext.data.Model',
        fields: [{
            name: 'display',
            type: 'string'
        }, {
            name: 'identifier',
            type: 'string'
        }, {
            name: 'id',
            type: 'int'
        }, {
            name: 'term',
            type: 'string'
        }],
        idProperty: 'id'
    }
);