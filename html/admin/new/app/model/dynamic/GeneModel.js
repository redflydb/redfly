Ext.define(
    'REDfly.model.dynamic.Gene',
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
            name: 'name',
            type: 'string'
        }, {
            name: 'species_scientific_name',
            type: 'string'
        }, {
            name: 'species_short_name',
            type: 'string'
        }],
        idProperty: 'id'
    }
);