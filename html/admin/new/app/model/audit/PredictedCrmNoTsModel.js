Ext.define(
    'REDfly.model.audit.PredictedCrmNoTs',
    {
        extend: 'Ext.data.Model',
        fields: [{
            name: 'anatomical_expression_display',
            type: 'string'
        }, {
            name: 'chromosome_display',
            type: 'string'
        }, {
            name: 'curator_full_name',
            type: 'string'
        }, {
            name: 'id',
            type: 'int'
        }, {
            name: 'name',
            type: 'string'
        }, {
            name: 'pubmed_id',
            type: 'string'
        }, {
            name: 'sequence_from_species_scientific_name',
            type: 'string'
        }, {
            name: 'state', 
            type: 'string'
        }],
        idProperty: 'id'
    }
);