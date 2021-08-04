Ext.define(
    'REDfly.view.admin.audit.PredictedCrmList',
    {
        bind: {
            store: '{predictedCrmStore}'
        },
        bufferedRenderer: true,    
        columns: [{
            dataIndex: 'id',
            hidden: true
        }, {
            align: 'left',
            dataIndex: 'pubmed_id',
            renderer: 'doShowTooltip',
            text: 'PMID',
            width: 65
        }, {
            align: 'left',
            dataIndex: 'curator_full_name',
            renderer: 'doShowTooltip',
            text: 'Curator',
            width: 120
        }, {
            align: 'left',
            dataIndex: 'state',
            renderer: 'doCapitalize',
            text: 'State',
            width: 60
        }, {
            align: 'left',
            dataIndex: 'sequence_from_species_scientific_name',
            renderer: 'doShowTooltip',
            text: '"Sequence From" Species',
            width: 150
        }, {
            align: 'left',
            dataIndex: 'name',
            renderer: 'doShowTooltip',
            text: 'Element Name',
            width: 120
        }, {
            align: 'left',
            dataIndex: 'coordinates',
            renderer: 'doShowTooltip',
            text: 'Coordinates',
            width: 140
        }, {
            align: 'left',
            dataIndex: 'evidence',
            renderer: 'doShowTooltip',
            text: 'Evidence',
            width: 180
        }, {
            align: 'left',
            dataIndex: 'evidence_subtype',
            renderer: 'doShowTooltip',
            text: 'Evidence Subtype',
            width: 180
        }, {
            align: 'left',
            dataIndex: 'anatomical_expression_displays',
            renderer: 'doShowTooltip',
            text: 'Anatomical Expression(s)',
            width: 180
        }, {
            align: 'left',
            dataIndex: 'notes',
            renderer: 'doShowTooltip',
            text: 'Notes',
            width: 120
        }, {
            align: 'left',
            dataIndex: 'date_added',
            renderer: 'doShowTooltip',
            text: 'Added',
            width: 120
        }, {
            align: 'left',
            dataIndex: 'last_update',
            renderer: 'doShowTooltip',
            text: 'Last Update',
            width: 120
        }],        
        extend: 'Ext.grid.Panel',
        selModel: {
            selType: 'checkboxmodel'
        },        
        xtype: 'audit-predicted-crm-list'
    }
);