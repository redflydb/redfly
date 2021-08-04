Ext.define(
    'REDfly.view.admin.audit.PredictedCrmNoTsList',
    {
        bind: {
            store: '{predictedCrmNoTsStore}'
        },
        bufferedRenderer: true,
        columns: [{
            dataIndex: 'id',
            hidden: true
        },  {
            dataIndex: 'pubmed_id',
            hidden: true
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
            width: 150
        }, {
            align: 'left',
            dataIndex: 'anatomical_expression_display',
            renderer: 'doShowTooltip',
            text: 'Anatomical Expression',
            width: 300
        }],
        extend: 'Ext.grid.Panel',
        xtype: 'audit-predicted-crm-no-ts-list'
    }
);