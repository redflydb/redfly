Ext.define(
    'REDfly.view.admin.audit.TfbsList',
    {
        bind: {
            store: '{tfbsStore}'
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
            dataIndex: 'gene_display',
            renderer: 'doShowTooltip',
            text: 'Gene',
            width: 120
        }, {
            align: 'left',
            dataIndex: 'transcription_factor_display',
            renderer: 'doShowTooltip',
            text: 'Transcription Factor',
            width: 120
        }, {
            align: 'left',
            dataIndex: 'name',
            renderer: 'doShowTooltip',
            text: 'Element Name',
            width: 260
        }, {
            align: 'left',
            dataIndex: 'coordinates',
            renderer: 'doShowTooltip',
            text: 'Coordinates',
            width: 140
        }, {
            align: 'left',
            dataIndex: 'assayed_in_species_scientific_name',
            renderer: 'doShowTooltip',
            text: '"Assayed In" Species',
            width: 140
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
        xtype: 'audit-tfbs-list'
    }
);