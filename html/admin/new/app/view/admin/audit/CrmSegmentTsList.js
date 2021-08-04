Ext.define(
    'REDfly.view.admin.audit.CrmSegmentTsList',
    {
        bind: {
            store: '{crmSegmentTsStore}'
        },
        bufferedRenderer: true,
        columns: [{
            dataIndex: 'id',
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
            dataIndex: 'assayed_in_species_scientific_name',
            renderer: 'doShowTooltip',
            text: '"Assayed In" Species',
            width: 140
        }, {
            align: 'left',
            dataIndex: 'anatomical_expression_display',
            renderer: 'doShowTooltip',
            text: 'Anatomical Expression',
            width: 300
        }, {
            align: 'left',
            dataIndex: 'pubmed_id',
            renderer: 'doShowTooltip',
            text: 'PMID',
            width: 65
        }, {
            align: 'left',
            dataIndex: 'on_developmental_stage_display',
            renderer: 'doShowTooltip',
            text: '"On" Developmental Stage',
            width: 200
        }, {
            align: 'left',
            dataIndex: 'off_developmental_stage_display',
            renderer: 'doShowTooltip',
            text: '"Off" Developmental Stage',
            width: 200
        }, {
            align: 'left',
            dataIndex: 'biological_process_display',
            renderer: 'doShowTooltip',
            text: 'Biological Process',
            width: 200
        }, {
            align: 'left',
            dataIndex: 'sex',
            renderer: 'doShowTooltip',
            text: 'Sex',
            width: 40
        }, {
            align: 'left',
            dataIndex: 'ectopic',
            renderer: 'doShowTooltip',
            text: 'Ectopic',
            width: 60
        }, {
            align: 'left',
            dataIndex: 'enhancer_or_silencer',
            renderer: 'doShowTooltip',
            text: 'Enh./Sil.',
            width: 60
        }],
        extend: 'Ext.grid.Panel',
        xtype: 'audit-crm-segment-ts-list'
    }
);