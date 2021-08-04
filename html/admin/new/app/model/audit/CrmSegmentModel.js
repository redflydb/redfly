Ext.define(
    'REDfly.model.audit.CrmSegment',
    {
        extend: 'Ext.data.Model',
        fields: [{
            name: 'anatomical_expression_displays', 
            type: 'string'
        }, {
            name: 'assayed_in_species_scientific_name',
            type: 'string'
        }, {
            name: 'auditor_full_name', 
            type: 'string'
        }, {
            name: 'chromosome_display',
            type: 'string'
        }, {
            name: 'coordinates', 
            type: 'string'
        }, {
            name: 'curator_full_name', 
            type: 'string'
        }, {
            name: 'evidence', 
            type: 'string'
        }, {
            name: 'evidence_subtype', 
            type: 'string'
        }, {
            name: 'date_added', 
            type: 'string'
        }, {
            name: 'gene_display',
            type: 'string'
        }, {
            name: 'id', 
            type: 'int'
        }, {
            name: 'last_update', 
            type: 'string'
        }, {
            name: 'name', 
            type: 'string'
        }, {
            name: 'notes', 
            type: 'string'
        }, {
            name: 'pubmed_id', 
            type: 'int'
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