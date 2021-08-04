Ext.define(
    'REDfly.model.audit.RcTs',
    {
        extend: 'Ext.data.Model',
        fields: [{
            name: 'anatomical_expression_display',
            type: 'string'
        }, {
            name: 'assayed_in_species_scientific_name',
            type: 'string'
        }, {
            name: 'biological_process_display',
            type: 'string'
        }, {
            name: 'chromosome_display',
            type: 'string'
        }, {
            name: 'curator_full_name',
            type: 'string'
        }, {
            name: 'ectopic',
            type: 'int'
        }, {
            name: 'enhancer_or_silencer',
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
            name: 'sex',
            type: 'string'
        }, {
            name: 'off_developmental_stage_display',
            type: 'string'
        }, {
            name: 'on_developmental_stage_display',
            type: 'string'
        }, {
            name: 'state', 
            type: 'string'
        }],
        idProperty: 'id'
    }
);