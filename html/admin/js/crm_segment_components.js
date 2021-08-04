// --------------------------------------------------------------------------------
// Combined combo box and grid of selected anatomical expression terms for CRM segments.
// --------------------------------------------------------------------------------
REDfly.component.curateCrmSegmentAnatomicalExpressionTerms = Ext.extend(Ext.form.CompositeField, {
    anchor: '100%',
    fieldLabel: 'Anatomical Expression Terms',
    layout: {
        type: 'vbox'
    },    
    msgTarget: 'under',
    // REDfly configs
    // Very useful for creating staging data
    crmsId: null,
    pubmedId: null,
    // --------------------------------------------------------------------------------
    // Need a constructor to create the store before initComponent is called.
    // --------------------------------------------------------------------------------
    constructor: function(config) {
        this.anatomicalExpressionTermSelect = new REDfly.component.selectAnatomicalExpressionTerm();
        this.store = new Ext.data.ArrayStore({
            fields: [
                'assayed_in_species_id',
                'crm_segment_id',
                'display',
                'id',
                'identifier',
                'pubmed_id',
                'term'
            ],
            idIndex: 0
        });
        this.addEvents(
            'deleteanatomicalexpression',
            'newstagingdata'
        );
        REDfly.component.curateCrmSegmentAnatomicalExpressionTerms.superclass.constructor.call(this, config);
    },
    initComponent: function() {
        this.anatomicalExpressionTermSelect.on(
            'beforerender',
            function(component) {
                // Add a listener to the anatomical expression term combo box before this
                // component is registered so we can add selected terms to the grid.
                this.anatomicalExpressionTermSelect.on(
                    'select',
                    function(combo, record, index) {
                        this.add(record);
                        combo.clearValue();
                    },
                    this
                );
            },
            this
        );
        Ext.apply(
            this,
            {
                items: [
                    this.anatomicalExpressionTermSelect,
                    {
                        autoExpandColumn: 'display_column',
                        colModel: new Ext.grid.ColumnModel({
                            columns: [
                                {
                                    // It follows the remote sorting rule from the database
                                    dataIndex: 'display',
                                    header: 'List',
                                    id: 'display_column'
                                },{
                                    id: 'delete_action_column',
                                    items: [{
                                        handler: function(grid, rowIndex) {
                                            var anatomicalExpressionIdentifier = grid.getStore().getAt(rowIndex).get('identifier');
                                            grid.getStore().removeAt(rowIndex);
                                            this.fireEvent('deleteanatomicalexpression', anatomicalExpressionIdentifier);
                                        },
                                        icon: REDfly.config.baseUrl + '/images/delete.ico',
                                        scope: this,
                                        tooltip: 'Delete&nbsp;Anatomical&nbsp;Expression'
                                    }],
                                    width: 26,
                                    xtype: 'actioncolumn'
                                },{
                                    id: 'create_action_column',
                                    items: [{
                                        handler: function(grid, rowIndex) {
                                            var crmstsPanelItem = new REDfly.component.crmstsPanel({
                                                // Ext.FormPanel configs
                                                title: 'Create New Staging Data',
                                                // REDfly configs
                                                mode: 'create',
                                                crmsId: this.crmsId,
                                                tsId: '',
                                                anatomicalExpressionIdentifier: grid.getStore().getAt(rowIndex).get('identifier'),
                                                anatomicalExpressionTerm: grid.getStore().getAt(rowIndex).get('term'),
                                                anatomicalExpressionDisplay: grid.getStore().getAt(rowIndex).get('display'),
                                                pubmedId: this.pubmedId,
                                                assayedInSpeciesId: this.anatomicalExpressionTermSelect.assayedInSpeciesField.id,
                                                sexId: 'both',
                                                sexTerm: 'Both',
                                                ectopicId: '0',
                                                ectopicTerm: 'False',
                                                enhancerOrSilencerAttributeId: 'enhancer',
                                                enhancerOrSilencerAttributeTerm: 'Enhancer'
                                            });
                                            var curateAnatomicalExpressionTermsObject = this;
                                            var crmstsCrudWindow = new Ext.Window({
                                                // Ext.Window configs
                                                autoScroll: true,
                                                id: 'crmstsCrudWindowId',
                                                layout: 'auto',
                                                height: 430,
                                                items: [crmstsPanelItem],
                                                listeners: {
                                                    stagingdata: function(rowIndex, data) {
                                                        curateAnatomicalExpressionTermsObject.fireEvent('newstagingdata', data);
                                                    }
                                                },
                                                modal: true,
                                                title: 'CRM Segment Staging Data',
                                                width: 550,
                                                // REDfly configs
                                                crmstsPanel: crmstsPanelItem
                                            });
                                            crmstsCrudWindow.show();
                                            crmstsCrudWindow.getEl().setStyle('z-index','90000');
                                        },
                                        icon: REDfly.config.baseUrl + '/images/save.ico',
                                        scope: this,
                                        tooltip: 'Create&nbsp;Staging&nbsp;Data'
                                    }],
                                    width: 26,
                                    xtype: 'actioncolumn'
                                }
                            ],
                            defaults: {
                                menuDisabled: true,
                                sortable: true 
                            }
                        }),
                        height: 150,
                        name: 'anatomical_expression_term_grid',
                        store: this.store,
                        stripeRows: true,
                        width: 350,
                        xtype: 'grid'
                    }
                ]
            }
        );
        REDfly.component.curateCrmSegmentAnatomicalExpressionTerms.superclass.initComponent.apply(this, arguments);
    },
    // --------------------------------------------------------------------------------
    // Add a record to the store of the component.
    // @param {Ext.data.Record} record The record to add.
    // --------------------------------------------------------------------------------
    add: function(record) {
        // Do not add the same record twice. Note that store.find() returns a match on
        // any portion of the string by default, hence the regex.
        var re = new RegExp('^' + record.data.id + '$');
        if ( this.store.find('id', re) !== -1 ) { return; }
        // A record should only be in one store, so make a copy.
        this.store.add(record.copy());
    },
    setCrmsId: function(crmsId) {
        this.crmsId = crmsId;
    },
    setPubmedId: function(pubmedId) {
        this.pubmedId = pubmedId;
    }
});
// --------------------------------------------------------------------------------
// Grid of selected staging data for CRM segments.
// --------------------------------------------------------------------------------
REDfly.component.curateCrmSegmentStagingData = Ext.extend(Ext.form.CompositeField, {
    anchor: '100%',
    fieldLabel: 'Staging Data',
    msgTarget: 'under',
    // --------------------------------------------------------------------------------
    // Need a constructor to create the store before initComponent is called.
    // --------------------------------------------------------------------------------
    constructor: function(config) {
        this.store = new Ext.data.ArrayStore({
            fields: [
                'anatomical_expression_display',
                'anatomical_expression_identifier',
                'anatomical_expression_term',
                'assayed_in_species_id',
                'biological_process_display',
                'biological_process_id',
                'biological_process_identifier',
                'biological_process_term',
                'crm_segment_id',
                'ectopic_id',
                'ectopic_term',
                'enhancer_or_silencer_attribute_id',
                'enhancer_or_silencer_attribute_term',
                'pubmed_id',
                'sex_id',
                'sex_term',
                'stage_off_display',
                'stage_off_id',
                'stage_off_identifier',
                'stage_off_term',
                'stage_on_display',
                'stage_on_id',
                'stage_on_identifier',
                'stage_on_term',
                'ts_id'
            ],
            idIndex: 0
        });
        this.addEvents('existingstagingdata');
        REDfly.component.curateCrmSegmentStagingData.superclass.constructor.call(this, config);
    },
    initComponent: function() {
        Ext.apply(this, {
            items: [
                {
                    colModel: new Ext.grid.ColumnModel({
                        columns: [
                            {
                                // It follows the first remote sorting rule from the database
                                dataIndex: 'anatomical_expression_display', 
                                header: 'Anatomical Expression',
                                width: 250
                            }, {
                                dataIndex: 'pubmed_id', 
                                header: 'PMID',
                                width: 60
                            }, {
                                // It follows the second remote sorting rule from the database
                                dataIndex: 'stage_on_display', 
                                header: 'Stage On',
                                width: 220
                            }, {
                                // It follows the third remote sorting rule from the database
                                dataIndex: 'stage_off_display', 
                                header: 'Stage Off',
                                width: 220
                            }, {
                                dataIndex: 'biological_process_display', 
                                header: 'Biological Process',
                                width: 300
                            }, {
                                dataIndex: 'sex_term', 
                                header: 'Sex',
                                width: 50
                            }, {
                                dataIndex: 'ectopic_term', 
                                header: 'Ectopic',
                                width: 50
                            }, {
                                dataIndex: 'enhancer_or_silencer_attribute_term',
                                header: 'Enh./Sil.',
                                width: 70
                            }, {                            
                                id: 'edit_action_column',
                                items: [{
                                    handler: function(grid, rowIndex) {
                                        var crmstsPanelItem = new REDfly.component.crmstsPanel({
                                            // Ext.FormPanel configs
                                            title: 'Edit Existing Staging Data',
                                            // REDfly configs
                                            mode: 'edit',
                                            rowIndex: rowIndex,
                                            tsId: grid.getStore().getAt(rowIndex).get('ts_id'),
                                            anatomicalExpressionIdentifier: grid.getStore().getAt(rowIndex).get('anatomical_expression_identifier'),
                                            anatomicalExpressionTerm: grid.getStore().getAt(rowIndex).get('anatomical_expression_term'),
                                            anatomicalExpressionDisplay: grid.getStore().getAt(rowIndex).get('anatomical_expression_display'),
                                            assayedInSpeciesId: grid.getStore().getAt(rowIndex).get('assayed_in_species_id')
                                        });
                                        var curateStagingDataObject = this;
                                        var crmstsCrudWindow = new Ext.Window({
                                            // Ext.Window configs
                                            autoScroll: true,
                                            id: 'crmstsCrudWindowId',
                                            layout: 'auto',
                                            height: 430,
                                            items: [crmstsPanelItem],
                                            listeners: {
                                                stagingdata: function(rowIndex, data) {
                                                    curateStagingDataObject.fireEvent(
                                                        'existingstagingdata',
                                                        rowIndex,
                                                        data
                                                    );
                                                }
                                            },                                        
                                            modal: true,
                                            title: 'CRM Segment Staging Data',
                                            width: 550,
                                            // REDfly configs
                                            crmstsPanel: crmstsPanelItem
                                        });                                        
                                        // The crmsts panel needs to monitor the beforeshow event of the
                                        // window and load itself before being displayed.
                                        crmstsCrudWindow.crmstsPanel.mon(
                                            crmstsCrudWindow,
                                            'beforeshow',
                                            function() {
                                                this.load(grid.getStore().getAt(rowIndex).get('ts_id'));
                                            },
                                            crmstsCrudWindow.crmstsPanel
                                        );
                                        crmstsCrudWindow.show();
                                        crmstsCrudWindow.getEl().setStyle('z-index', '90000');
                                    },
                                    icon: REDfly.config.baseUrl + '/images/edit.ico',
                                    scope: this,
                                    tooltip: 'Edit&nbsp;Staging&nbsp;Data'
                                }],
                                width: 26,
                                xtype: 'actioncolumn'
                            },{                            
                                id: 'delete_action_column',
                                items: [{
                                    handler: function(grid, rowIndex) {
                                        grid.getStore().removeAt(rowIndex);
                                    },
                                    icon: REDfly.config.baseUrl + '/images/delete.ico',
                                    tooltip: 'Delete&nbsp;Staging&nbsp;Data'
                                }],
                                width: 26,
                                xtype: 'actioncolumn'
                            }
                        ],
                        defaults: { 
                            menuDisabled: true,
                            sortable: true
                        }
                    }),
                    height: 200,
                    name: 'staging_data_grid',
                    store: this.store,
                    stripeRows: true,
                    width: 550,
                    xtype: 'grid'
                }
            ]
        });
        REDfly.component.curateCrmSegmentStagingData.superclass.initComponent.apply(this, arguments);
    },
    // --------------------------------------------------------------------------------
    // Add a record to the store of the component.
    // @param {Ext.data.Record} record The record to add.
    // --------------------------------------------------------------------------------
    add: function(record) {
        // A record should only be in one store, so make a copy.
        this.store.add(record.copy());
    }
});