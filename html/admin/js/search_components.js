REDfly.component.sequenceFromSpeciesComboBox = Ext.extend(Ext.form.ComboBox, {
    displayField: 'display',
    editable: false,
    fieldLabel: '"Sequence From" Species',
    listeners: {
        select: function(combo, record, index) {
            this.id = record.data.id;
            this.scientific_name = record.data.scientific_name;
            this.short_name = record.data.short_name;
        }
    },    
    name: 'species_id',
    store: new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoLoad: true,
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in selectSpecies Store');
                }
            },
            url: REDfly.config.apiUrl + '/jsonstore/species/list?sort=scientific_name'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'display',
            'id',
            'scientific_name',
            'short_name'
        ],
        idProperty: 'id',
        messageProperty: 'message',
        root: 'results',
        totalProperty: 'num'
    }),
    triggerAction: 'all',
    valueField: 'id',
    width: 200,
    // REDfly configs
    id: null,
    scientific_name: null,
    short_name: null
});
REDfly.component.assayedInSpeciesComboBox = Ext.extend(Ext.form.ComboBox, {
    displayField: 'display',
    editable: false,
    fieldLabel: '"Assayed In" Species',
    listeners: {
        select: function(combo, record, index) {
            this.id = record.data.id;
            this.scientific_name = record.data.scientific_name;
            this.short_name = record.data.short_name;
        }
    },    
    name: 'species_id',
    store: new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoLoad: true,
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in selectSpecies Store');
                }
            },
            url: REDfly.config.apiUrl + '/jsonstore/species/list?sort=scientific_name'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'display',
            'id',
            'scientific_name',
            'short_name'
        ],
        idProperty: 'id',
        messageProperty: 'message',
        root: 'results',
        totalProperty: 'num'
    }),
    triggerAction: 'all',
    valueField: 'id',
    width: 200,
    // REDfly configs
    id: null,
    scientific_name: null,
    short_name: null
});
REDfly.store.rcSearchResults = new Ext.data.JsonStore({
    // Ext.data.JsonStore configs
    autoLoad: false,
    baseParams: {
        sort: 'name',
        view: 'curator'
    },
    proxy: new Ext.data.HttpProxy({
        method: 'GET',
        listeners: {
            exception: function() {
                //console.log('Error in rcSearchResults Store');
            }
        },
        url: REDfly.config.apiUrl + '/jsonstore/reporterconstruct/search'
    }),
    // Embedded Ext.data.JsonReader configs
    fields: [ 
        'assayed_in_species_id',
        'assayed_in_species_scientific_name',
        'auditor_full_name',
        'auditor_id',
        'coordinates',
        'curator_full_name',
        'curator_id',
        'date_added',
        'entity_id', 
        'gene',
        'gene_id',
        'is_crm',
        'last_audit',
        'last_update',
        'name',
        'pmid',
        'redfly_id',
        'sequence_from_species_id',
        'sequence_from_species_scientific_name',
        'state'
    ],
    idProperty: 'id',
    root: 'results',
    totalProperty: 'num'
});
REDfly.store.tfbsSearchResults = new Ext.data.JsonStore({
    // Ext.data.JsonStore configs
    autoLoad: false,
    baseParams: {
        sort: 'name',
        view: 'curator'
    },
    proxy: new Ext.data.HttpProxy({
        method: 'GET',
        listeners: {
            exception: function() {
                //console.log('Error in tfbsSearchResults Store');
            }
        },        
        url: REDfly.config.apiUrl + '/jsonstore/transcriptionfactorbindingsite/search'
    }),
    // Embedded Ext.data.JsonReader configs    
    fields: [
        'assayed_in_species_id',
        'assayed_in_species_scientific_name',
        'auditor_full_name',
        'auditor_id',
        'coordinates',
        'curator_full_name',
        'curator_id',
        'date_added',
        'entity_id',
        'gene',
        'gene_id',
        'last_audit',        
        'last_update',
        'name',
        'pmid',  
        'redfly_id',
        'sequence_from_species_id',
        'sequence_from_species_scientific_name',
        'state',
        'tf_id',
        'transcription_factor'
    ],
    idProperty: 'id',
    root: 'results',
    totalProperty: 'num'
});
REDfly.store.crmSegmentSearchResults = new Ext.data.JsonStore({
    // Ext.data.JsonStore configs
    autoLoad: false,
    baseParams: {
        sort: 'name',
        view: 'curator'
    },
    proxy: new Ext.data.HttpProxy({
        method: 'GET',
        listeners: {
            exception: function() {
                //console.log('Error in crmSegmentSearchResults Store');
            }
        },
        url: REDfly.config.apiUrl + '/jsonstore/crmsegment/search'
    }),
    // Embedded Ext.data.JsonReader configs
    fields: [
        'assayed_in_species_id',
        'assayed_in_species_scientific_name',
        'auditor_full_name',
        'auditor_id',
        'coordinates',
        'curator_full_name',
        'curator_id',
        'date_added',
        'entity_id', 
        'gene',
        'gene_id',
        'is_crm',
        'last_audit',
        'last_update',
        'name',
        'pmid',
        'redfly_id',
        'sequence_from_species_id',
        'sequence_from_species_scientific_name',
        'state'
    ],
    idProperty: 'id',
    root: 'results',
    totalProperty: 'num'
});
REDfly.store.predictedCrmSearchResults = new Ext.data.JsonStore({
    // Ext.data.JsonStore configs
    autoLoad: false,
    baseParams: {
        sort: 'name',
        view: 'curator'
    },
    proxy: new Ext.data.HttpProxy({
        method: 'GET',
        listeners: {
            exception: function() {
                //console.log('Error in predictedCrmSearchResults Store');
            }
        },
        url: REDfly.config.apiUrl + '/jsonstore/predictedcrm/search'
    }),
    // Embedded Ext.data.JsonReader configs
    fields: [
        'auditor_full_name',
        'auditor_id',
        'coordinates',
        'curator_full_name',
        'curator_id',
        'date_added',
        'entity_id', 
        'last_audit',
        'last_update',
        'name',
        'pmid',
        'redfly_id',
        'sequence_from_species_id',
        'sequence_from_species_scientific_name',
        'state'
    ],
    idProperty: 'id',
    root: 'results',
    totalProperty: 'num'
});
// --------------------------------------------------------------------------------
// Create a tab panel containing a grid panel to display search results. The
// number of results returned is displayed in the tab next to the title.
// --------------------------------------------------------------------------------
REDfly.component.searchResultTab = Ext.extend(Ext.Panel, {
    grid: null,
    entity: null,
    height: 400,
    store: null,
    // Current title
    title: 'Results',
    // Original title used when displaying number of records found
    origTitle: 'Results',
    // Optional handler for row clicks
    rowClickHandler: null,
    rowDblClickHandler: null,
    load: function(params) {
        this.setTitle('Loading...');
        this.store.load( { params: params } );
    },
    getGrid: function() {
        return this.grid;
    },
    reset: function() {
        this.store.removeAll();
        this.setTitle(this.origTitle);
    },
    initComponent: function() {
        var selectionModel = new Ext.grid.CheckboxSelectionModel();
        var gridColumns = [selectionModel];
        gridColumns.push({
                dataIndex: 'pmid',
                header: 'PMID',
                menuDisabled: true,
                sortable: true,
                width: 65
            }, {
                dataIndex: 'curator_full_name',
                header: 'Curator',
                menuDisabled: true,
                sortable: true,
                width: 120
            }, {
                dataIndex: 'state',
                header: 'State',
                menuDisabled: true,
                renderer: function(value) {
                    return value.charAt(0).toUpperCase() + value.slice(1);
                },
                sortable: true,
                width: 60
            }, {
                dataIndex: 'sequence_from_species_scientific_name',
                header: '"Sequence From" Species',
                menuDisabled: true,
                sortable: true,
                width: 150
            }
        );
        if ( this.entity !== 'predictedCrm' ) {
            gridColumns.push({
                dataIndex: 'gene',
                header: 'Gene',
                menuDisabled: true,
                sortable: true,
                width: 80
            })
        };
        if ( this.entity === 'tfbs' ) {
            gridColumns.push({
                dataIndex: 'transcription_factor',
                header: 'Transcription Factor',
                menuDisabled: true,
                sortable: true,
                width: 120
            })
        };        
        gridColumns.push({
                dataIndex: 'name',
                header: 'Name',
                id: 'entity_name',
                menuDisabled: true,
                sortable: true,
                width: 120
            }, {
                dataIndex: 'coordinates',
                header: 'Coordinates',
                menuDisabled: true,
                sortable: true,
                width: 140
            },  
        );
        if ( (this.entity !== 'tfbs') &&
            (this.entity !== 'predictedCrm') ) {
            gridColumns.push({
                dataIndex: 'assayed_in_species_scientific_name',
                header: '"Assayed In" Species',
                menuDisabled: true,
                sortable: true,
                width: 140
            })
        };
        gridColumns.push({
                dataIndex: 'date_added',
                header: 'Added',
                menuDisabled: true,
                sortable: true,
                width: 130
            }, {
                dataIndex: 'last_update',
                header: 'Last Updated',
                menuDisabled: true,
                sortable: true,
                width: 130
            }, {
                dataIndex: 'auditor_full_name',
                header: 'Auditor',
                menuDisabled: true,
                sortable: true,
                width: 120
            }, {
                dataIndex: 'last_audit',
                header: 'Last Audited',
                menuDisabled: true,
                sortable: true,
                width: 120
            }
        );
        var searchResults = new Ext.grid.GridPanel({
            autoExpandColumn: 'entity_name',
            columns: gridColumns,
            frame: true,
            height: this.height,
            id: 'searchResults',
            listeners: {
                beforerender: function() {
                    if ( ! REDfly.config.isAdmin ) {
                        this.getColumnModel().setHidden(0, true);
                    }
                }
            },
            loadMask: { msg: 'Loading',
                        store: this.store },
            sm: selectionModel,
            store: this.store,
            stripeRows: true,
            view: new Ext.ux.grid.BufferView({ scrollDelay: false })
        });
        // Add optional event handlers to the grid
        if ( Ext.isFunction(this.rowClickHandler) ) {
            searchResults.on(
                'rowclick',
                this.rowClickHandler
            );
        }
        if ( Ext.isFunction(this.rowDblClickHandler) ) {
            searchResults.on(
                'rowdblclick',
                this.rowDblClickHandler
            );
        }
        // Apply any configuration to the current window
        Ext.apply(this, {
            grid: searchResults,
            items: searchResults,
            origTitle: this.title
        });
        REDfly.component.searchResultTab.superclass.initComponent.apply(this, arguments);
        // Since AJAX calls are asynchronous, add a listener to update the tab title
        // with the number of records returned.
        var titleChangeCb = function(store, recordList, opt) {
            this.setTitle(this.origTitle + ' (' + recordList.length + ')');
        };
        this.store.on(
            'load',
            titleChangeCb,
            this
        );
    }
});