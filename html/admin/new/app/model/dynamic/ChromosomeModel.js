Ext.define(
    'REDfly.model.dynamic.Chromosome',
    {
        extend: 'Ext.data.Model',
        fields: [{
            name: 'display',
            type: 'string'
        }, {
            name: 'display_sort',
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