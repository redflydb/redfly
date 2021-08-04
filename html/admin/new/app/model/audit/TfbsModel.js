Ext.define(
    'REDfly.model.audit.Tfbs',
    {
        extend: 'Ext.data.Model',
        fields: [{
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
        },  {
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
        }, {
            name: 'transcription_factor_display',
            type: 'string'
        } 
    ],
    idProperty: 'id'
});