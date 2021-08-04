// ================================================================================
// REDfly search user interface
// ================================================================================
Ext.onReady(function () {
    // Array of gene displays
    var geneDisplayArray = [];
    // Definition of the download windows.
    // These ones will be generated on demand
    var downloadAllCRMs = null;
    var downloadAllRCs = null;
    var downloadAllCRMSegments = null;
    var downloadAllPredictedCRMs = null;
    var downloadAllTFBSs = null;
    // Used to reset the timeout for the pause function
    var timeout = 0;
    // These three arrays are used to construct and decode the bookmark
    // URL. The order of the values is important, it determines the
    // order of values in the URL.
    var rcOptionIds = [
        'rc-data-type-all',
        'rc-data-type-crm',
        'rc-data-type-crm-with-tfbs',
        'rc-restrictions-anatomical-expression-positive',
        'rc-restrictions-anatomical-expression-negative',
        'rc-restrictions-include-enhancer',
        'rc-restrictions-include-silencer',
        'rc-restrictions-exclude-enhancer',
        'rc-restrictions-exclude-silencer',
        'rc-restrictions-minimized',
        'rc-miscellaneous-options-has-images'
    ];
    var tfbsOptionIds = [
        'tfbs-data-type-all',
        'tfbs-data-type-with-crm',
        'tfbs-miscellaneous-options-has-images'
    ];
    var tfbsRestrictionsIds = [
        'tfbs-restrictions-all-genes',
        'tfbs-restrictions-target-gene-only',
        'tfbs-restrictions-tf-gene-only'
    ];
    // Holds the URL value for the REDfly ID if any.
    var redflyIdUrl = '';
    //----------------------------------------------------------------------------------
    // All The JSON Stores Alphabetically Sorted
    //----------------------------------------------------------------------------------    
    // JSON store of anatomical expressions
    //----------------------------------------------------------------------------------    
    var anatomicalExpressionStore = new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoload: false,
        baseParams: {
            sort: 'term'
        },
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            url: redflyApiUrl + '/jsonstore/anatomicalexpression/list'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: Ext.data.Record.create([
            'display',
            'id',
            'identifier',
            'species_id',
            'term'
        ]),
        idProperty: 'id',
        root: 'results',
        totalProperty: 'num'
    });
    anatomicalExpressionStore.load();
    //----------------------------------------------------------------------------------    
    // JSON store of biological processes
    //----------------------------------------------------------------------------------    
    var biologicalProcessStore = new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoload: false,
        baseParams: {
            sort: 'term'
        },
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            url: redflyApiUrl + '/jsonstore/biologicalprocess/list'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: Ext.data.Record.create([
            'display',
            'id',
            'identifier',
            'term'
        ]),
        idProperty: 'id',
        root: 'results',
        totalProperty: 'num'
    });
    biologicalProcessStore.load();        
    //----------------------------------------------------------------------------------
    // JSON store of chromosomes
    //----------------------------------------------------------------------------------    
    var chromosomeStore = new Ext.data.JsonStore({
        // Ext.data.JsonStore configs        
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            url: redflyApiUrl + '/jsonstore/chromosome/list?sort=species_short_name,name'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'display',
            'id',
            'length',
            'name',
            'species_id',
            'species_short_name'
        ],
        idProperty: 'id',
        messageProperty: 'message',        
        listeners: {
            load: selectChromosome
        },
        root: 'results',
        totalProperty: 'num'
    });
    chromosomeStore.load();
    //----------------------------------------------------------------------------------    
    // JSON store of cis-regulatory module segments
    //----------------------------------------------------------------------------------    
    var crmSegmentStore = new Ext.data.JsonStore({
        // Ext.data.JsonStore configs        
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            url: redflyApiUrl + '/jsonstore/crmsegment/search?sort=name'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'assayed_in_species_scientific_name',
            'coordinates',
            'coordinates_display',
            'gene',
            'gene_id',
            'has_images',            
            'id',
            'name',
            'redfly_id',
            'sequence_from_species_scientific_name'
        ],
        idProperty: 'id',
        listeners: {
            beforeload: function(source, operation) {
                operation.params = Ext.apply(
                    operation.params || {},
                    REDfly.config.crmsegmentSearchParameters
                );
            },
            load: search
        },
        root: 'results',
        totalProperty: 'num'
    });    
    //----------------------------------------------------------------------------------
    // JSON store of cis-regulatory modules without any page size
    //----------------------------------------------------------------------------------    
    var crmStore = new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            url: redflyApiUrl + '/jsonstore/reporterconstruct/search'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'redfly_id'
        ],
        idProperty: 'id',
        listeners: {
            beforeload: function(source, operation) {
                operation.params = Ext.apply(
                    operation.params || {},
                    REDfly.config.crmSearchParameters
                );
            },
            load: search
        },
        root: 'results',
        totalProperty: 'num'
    });
    //----------------------------------------------------------------------------------
    // JSON store of database information
    //----------------------------------------------------------------------------------    
    var databaseInformationStore = new Ext.data.JsonStore({
        fields: [
            'last_crm_update',
            'last_crmsegment_update',
            'last_predictedcrm_update',
            'last_rc_update',
            'last_tfbs_update',
            'number_crms',
            'number_rcs'
        ],
        idProperty: 'id',
        root: 'results',
        totalProperty: 'num',
        url: redflyApiUrl + '/jsonstore/information/db'
    });
    databaseInformationStore.load();
    //----------------------------------------------------------------------------------
    // JSON store of evidence terms
    //----------------------------------------------------------------------------------    
    var evidenceStore = new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            url: redflyApiUrl + '/jsonstore/evidence/list?sort=name'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'id',
            'term'
        ],
        idProperty: 'id',
        listeners: {
            load: selectEvidence
        },
        root: 'results',
        totalProperty: 'num'
    });
    evidenceStore.load();    
    //----------------------------------------------------------------------------------    
    // JSON store of developmental stages
    //----------------------------------------------------------------------------------    
    var developmentalStageStore = new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoload: false,
        baseParams: {
            sort: 'term'
        },
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            url: redflyApiUrl + '/jsonstore/developmentalstage/list'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: Ext.data.Record.create([
            'display',
            'id',
            'identifier',
            'species_id',
            'term'
        ]),
        idProperty: 'id',
        root: 'results',
        totalProperty: 'num'
    });
    developmentalStageStore.load();
    //----------------------------------------------------------------------------------
    // JSON store of genes.
    // It does not load like the rest of the stores because it does not have any data 
    // until the search field is filled in
    //----------------------------------------------------------------------------------    
    var geneStore = new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        baseParams: {
            sort: 'name'
        },        
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            url: redflyApiUrl + '/jsonstore/gene/list'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'display',
            'id',
            'identifier',
            'name',
            'species_id'
        ],
        idProperty: 'id',
        listeners: {
            load: searchDatabase
        },
        root: 'results',
        totalProperty: 'num'
    });
    //----------------------------------------------------------------------------------    
    // JSON store of inferred cis-regulatory modules
    //----------------------------------------------------------------------------------    
    var inferredCrmStore = new Ext.data.JsonStore({
        // Ext.data.JsonStore configs        
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            url: redflyApiUrl + '/jsonstore/inferredcrm/search?sort=gene,coordinates'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'assayed_in_species_scientific_name',
            'coordinates',
            'anatomical_expressions',
            'anatomical_expression_identifiers',
            'gene',
            'id',
            'sequence_from_species_scientific_name'
        ],
        idProperty: 'id',
        listeners: {
            beforeload: function(source, operation) {
                operation.params = Ext.apply(
                    operation.params || {},
                    REDfly.config.inferredCrmSearchParameters
                );
            },
            load: search
        },
        root: 'results',
        totalProperty: 'total'
    });
    //----------------------------------------------------------------------------------    
    // JSON store of predicted cis-regulatory modules
    //----------------------------------------------------------------------------------    
    var predictedCrmStore = new Ext.data.JsonStore({
        // Embedded Ext.data.JsonReader configs
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            url: redflyApiUrl + '/jsonstore/predictedcrm/search?sort=name'
        }),
        // Embedded Ext.data.JsonReader configs        
        fields: [
            'assayed_in_species_scientific_name',
            'coordinates',
            'coordinates_display',
            'id',
            'name',
            'pubmed_id',
            'redfly_id',
            'sequence_from_species_scientific_name'
        ],
        idProperty: 'id',
        listeners: {
            beforeload: function(source, operation) {
                operation.params = Ext.apply(
                    operation.params || {},
                    REDfly.config.predictedCrmSearchParameters
                );
            },
            load: search
        },
        root: 'results',
        totalProperty: 'num'
    });
    //----------------------------------------------------------------------------------
    // JSON store of reporter constructs
    //----------------------------------------------------------------------------------    
    var rcStore = new Ext.data.JsonStore({
        // Embedded Ext.data.JsonReader configs        
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            url: redflyApiUrl + '/jsonstore/reporterconstruct/search?sort=name'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'assayed_in_species_scientific_name',
            'coordinates',
            'coordinates_display',
            'gene',
            'gene_id',
            'has_images',
            'id',
            'is_crm',
            'name',
            'redfly_id',
            'sequence_from_species_scientific_name'
        ],
        idProperty: 'id',
        listeners: {
            beforeload: function(source, operation) {
                operation.params = Ext.apply(
                    operation.params || {},
                    REDfly.config.rcSearchParameters
                );
            },
            load: search
        },
        root: 'results',
        totalProperty: 'num'
    });
    //----------------------------------------------------------------------------------    
    // JSON store of species
    //----------------------------------------------------------------------------------    
    var speciesStore = new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            url: redflyApiUrl + '/jsonstore/species/list?sort=scientific_name'
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
    });
    speciesStore.load();
    //----------------------------------------------------------------------------------    
    // JSON store of transcription factor binding sites
    //----------------------------------------------------------------------------------    
    var tfbsStore = new Ext.data.JsonStore({
        // Ext.data.JsonStore configs        
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            url: redflyApiUrl + '/jsonstore/transcriptionfactorbindingsite/search?sort=name'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'assayed_in_species_scientific_name',
            'coordinates',
            'coordinates_display',
            'gene',
            'gene_id',
            'has_images',            
            'id',
            'name',
            'redfly_id',
            'sequence_from_species_scientific_name',
            'tf',
            'tf_id'
        ],
        idProperty: 'id',
        listeners: {
            beforeload: function(source, operation) {
                operation.params = Ext.apply(
                    operation.params || {},
                    REDfly.config.tfbsSearchParameters
                );
            },
            load: search
        },
        root: 'results',
        totalProperty: 'num'
    });
    //----------------------------------------------------------------------------------
    // All The Array Stores Alphabetically Sorted
    //----------------------------------------------------------------------------------
    // Array store of chromosome positions
    //----------------------------------------------------------------------------------    
    var chromosomePositionStore = new Ext.data.ArrayStore({
        fields: [
            'id',
            'position'
        ]
    });
    chromosomePositionStore.loadData([
        [0, 'All'],
        [1, '5\''],
        [2, '3\'']
    ]);
    //----------------------------------------------------------------------------------    
    // Array store of gene displays
    //----------------------------------------------------------------------------------        
    var geneDisplayStore = new Ext.data.ArrayStore({
        // The field order here must not be altered 
        // because it is followed by the function
        // "geneKeyPause" below. 
        fields: [
            'record',
            'display'
        ]
    });
    //----------------------------------------------------------------------------------
    // All The Input Fields Sorted By The Disposition In The Search Form
    //----------------------------------------------------------------------------------
    // Combobox of genes
    //----------------------------------------------------------------------------------
    var geneComboBox = new Ext.form.ComboBox({
        displayField: 'display',
        enableKeyEvents: true,
        hideTrigger: true,
        listeners: {
            blur: autoFill,
            keyup: geneKeyPause,
            select: changeGeneValue,
            specialkey: checkEnter
        },
        mode: 'local',
        store: geneDisplayStore,
        triggerAction: 'all',
        typeAhead: false
    });
    //----------------------------------------------------------------------------------
    // Radiogroup of "by locus" and "by name"
    //----------------------------------------------------------------------------------
    var includeRangeRadioGroup = new Ext.form.RadioGroup({
        items: [
            {
                boxLabel: 'by locus',
                checked: true,
                inputValue: 'by_locus',
                name: 'rb-auto'
            },
            {
                boxLabel: 'by name',
                inputValue: 'by_name',
                name: 'rb-auto'
            },
        ]
    });
    //----------------------------------------------------------------------------------
    // Text field of element name or FBtp identifier
    //----------------------------------------------------------------------------------
    var elementNameOrFBtpIdentifierTextField = new Ext.form.TextField({
        autoCreate: {
            maxlength: 50,
            size: 27,
            tag: 'input'
        },
        fieldLabel: '<b>Element Name/FBtp</b> <a href="javascript:elementNameorFBtpIdentifierHelp()">?</a>',
        listeners: { specialkey: checkEnter }
    });
    //----------------------------------------------------------------------------------
    // Text field of pubmed ID
    //----------------------------------------------------------------------------------    
    var pubmedIdTextField = new Ext.form.TextField({
        autoCreate: {
            maxLength: 20,
            size: 27,
            tag: 'input'
        },
        fieldLabel: '<b>Pubmed ID</b> <a href="javascript:pubmedIdHelp()">?</a>',
        listeners: { specialkey: checkEnter },
        validator: function(value) {
            if ( value !== '' ) {
                if ( new RegExp('^\\d+$').test(value) ) {
                    return true;
                } else {
                    return 'This value, ' + value + ', must be numeric and integer';
                }
            }
        }
    });
    //----------------------------------------------------------------------------------
    // Combobox of "Sequence From" species
    //----------------------------------------------------------------------------------
    var sequenceFromSpeciesComboBox = new Ext.form.ComboBox({
        displayField: 'display',
        editable: false,
        emptyText: 'Select Species...',
        enableKeyEvents: true,
        fieldLabel: '<b>"Sequence From" Species</b> <a href="javascript:sequenceFromSpeciesHelp()">?</a>',
        forceSelection: true,
        listeners: {
            'select': clearGeneNameField,
            specialkey: checkEnter
        },
        mode: 'local',
        store: speciesStore,
        tpl: '<tpl for="."><div ext:qtip="{display}" class="x-combo-list-item">{display}</div></tpl>',
        triggerAction: 'all',
        typeAhead: true,
        valueField: 'id',
        width: 195,
        initComponent: function() {
            var me = this;
            this.store.on (
                'load',
                function(store) {
                    me.setValue(store.getAt(store.find('scientific_name', 'Drosophila melanogaster')).get('id'));
                }
            )
        }
    });
    function clearGeneNameField() {
        geneComboBox.clearValue();
        geneStore.removeAll();
        geneStore.load({ params: {
            species_id: sequenceFromSpeciesComboBox.getValue()
        }});
    }
    //----------------------------------------------------------------------------------
    // Combobox of "Assayed In" species
    //----------------------------------------------------------------------------------
    var assayedInSpeciesComboBox = new Ext.form.ComboBox({
        displayField: 'display',
        editable: false,
        emptyText: 'Select Species...',                
        enableKeyEvents: true,
        fieldLabel: '<b>"Assayed In" Species</b> <a href="javascript:assayedInSpeciesHelp()">?</a>',
        forceSelection: true,
        listeners: {
            specialkey: checkEnter
        },
        mode: 'local',
        store: speciesStore,
        tpl: '<tpl for="."><div ext:qtip="{display}" class="x-combo-list-item">{display}</div></tpl>',
        triggerAction: 'all',
        typeAhead: true,
        valueField: 'id',
        width: 195,
        initComponent: function() {
            var me = this;
            this.store.on (
                'load',
                function(store) {
                    me.setValue(store.getAt(store.find('scientific_name', 'Drosophila melanogaster')).get('id'));
                }
            )
        }
    });
    //----------------------------------------------------------------------------------
    // Combobox of chromosomes
    //----------------------------------------------------------------------------------
    var chromosomeComboBox = new Ext.form.ComboBox({
        autoCreate: {
            maxLength: 3,
            size: 3,
            tag: 'input'
        },
        displayField: 'display',
        fieldLabel: '<b>Chromosome</b>',
        forceSelection: true,
        listeners: {
            beforequery: function(q) {
                var paramObj = {};
                paramObj.species_id = sequenceFromSpeciesComboBox.getValue();
                paramObj.display = '*' + q.query + '*';
                q.combo.getStore().load({ params: paramObj });
                q.cancel = true;
            },             
            specialkey: checkEnter
        },
        mode: 'local',
        selectOnFocus: true,
        store: chromosomeStore,
        tpl: '<tpl for="."><div ext:qtip="{display}" class="x-combo-list-item">{display}</div></tpl>',
        triggerAction: 'all',
        value: 'Any',
        width: 140
    });
    //----------------------------------------------------------------------------------
    // Text field of the start coordinate on the release coordinates
    //----------------------------------------------------------------------------------
    var startCoordinateTextField = new Ext.form.TextField({
        autoCreate: {
            maxLength: 8,
            size: 8,
            tag: 'input'
        },
        fieldLabel: '<b>Start Coord.</b>',
        id: 'startCoordinateTextField',
        listeners: { specialkey: checkEnter },
        name: 'startCoordinateTextField',
        validator: function(value) {
            if ( value !== '' ) {
                if ( new RegExp('^\\d+$').test(value) ) {
                    endCoordinateValue = Ext.getCmp('endCoordinateTextField').getValue();
                    if ( (endCoordinateValue !== '') && 
                        (new RegExp('^\\d+$').test(endCoordinateValue)) ) {
                        if ( parseInt(endCoordinateValue, 10) <= parseInt(value, 10) ) {
                            return 'The start coordinate must be less than the end coordinate';
                        } else {
                            return true;
                        }
                    } else {
                        return true;
                    }
                } else {
                    return 'This value, ' + value + ', must be numeric and integer';
                }
            }
        },
        width: 90
    });
    //----------------------------------------------------------------------------------
    // Text field of the end coordinate on the release coordinates
    //----------------------------------------------------------------------------------
    var endCoordinateTextField = new Ext.form.TextField({
        autoCreate: {
            maxLength: 8,
            size: 8,
            tag: 'input'
        },        
        fieldLabel: '<b>End Coord.</b> <a href="javascript:coordinatesHelp()">?</a>',
        id: 'endCoordinateTextField',
        listeners: { specialkey: checkEnter },
        name: 'endCoordinateTextField',
        validator: function(value) {
            if ( value !== '' ) {
                if ( new RegExp('^\\d+$').test(value) ) {
                    startCoordinateValue = Ext.getCmp('startCoordinateTextField').getValue();
                    if ( (startCoordinateValue !== '') && 
                        (new RegExp('^\\d+$').test(startCoordinateValue)) ) {
                        if ( parseInt(value, 10) <= parseInt(startCoordinateValue, 10) ) {
                            return 'The end coordinate must be great than the start coordinate';
                        } else {
                            return true;
                        }
                    } else {
                        return true;
                    }
                } else {
                    return 'This value, ' + value + ', must be numeric and integer';
                }
            }        },
        width: 90
    });
    //----------------------------------------------------------------------------------
    // Text field of the maximum sequence size
    //----------------------------------------------------------------------------------
    var maximumSequenceSizeTextField = new Ext.form.TextField({
        autoCreate: {
            maxLength: 12,
            size: 12,
            tag: 'input'
        },
        emptyText: 'Base Pairs...',
        fieldLabel: '<b>Maximum Size</b> <a href="javascript:maximumSequenceSizeHelp()">?</a>',
        listeners: { specialkey: checkEnter },
        validator: function(value) {
            if ( value !== '' ) {
                if ( new RegExp('^\\d+$').test(value) ) {
                    return true;
                } else {
                    return 'This value, ' + value + ', must be numeric and integer';
                }
            }
        }        
    });
    //----------------------------------------------------------------------------------
    // Text field of the search range interval
    //----------------------------------------------------------------------------------
    var searchRangeIntervalTextField = new Ext.form.TextField({
        autoCreate: {
            maxLength: 12,
            size: 12,
            tag: 'input'
        },
        emptyText: '10000',
        fieldLabel: '<b>Search Range Interval (-/+) bp</b> <a href="javascript:searchRangeIntervalHelp()">?</a>',
        listeners: { specialkey: checkEnter },
        validator: function(value) {
            if ( value !== '' ) {
                if ( new RegExp('^\\d+$').test(value) ) {
                    return true;
                } else {
                    return 'This value, ' + value + ', must be numeric and integer';
                }
            }
        },        
        value: '10000'
    });
    //----------------------------------------------------------------------------------
    // Combobox of evidence terms
    //----------------------------------------------------------------------------------    
    var evidenceComboBox = new Ext.form.ComboBox({
        displayField: 'term',
        emptyText: 'Select Evidence...',
        fieldLabel: '<b>Restrict Evidence To</b> <a href="javascript:evidenceHelp()">?</a>',
        forceSelection: true,
        listeners: {
            specialkey: checkEnter
        },
        mode: 'local',
        selectOnFocus: true,
        store: evidenceStore,
        tpl: '<tpl for="."><div ext:qtip="{term}" class="x-combo-list-item">{term}</div></tpl>',
        triggerAction: 'all',
        typeAhead: true,
        width: 250
    });
    //----------------------------------------------------------------------------------
    // Combobox of anatomical expressions
    //----------------------------------------------------------------------------------
    var anatomicalExpressionComboBox = new Ext.form.ComboBox({
        displayField: 'display',
        emptyText: 'Select Anatomical Expression Term...',
        listeners: {
            beforequery: function(q) {
                var paramObj = {};
                paramObj.species_id = assayedInSpeciesComboBox.getValue();
                if ( (q.query.substr(0, 4) === 'FBbt') ||
                    (q.query.substr(0, 4) === 'TGMA') ||
                    (q.query.substr(0, 4) === 'TrOn') ) {
                    paramObj.identifier = q.query + '*';
                } else {
                    paramObj.term = '*' + q.query + '*';
                }
                q.combo.getStore().load({ params: paramObj });
                q.cancel = true;
            },
            specialkey: checkEnter
        },
        store: anatomicalExpressionStore,
        tpl: '<tpl for="."><div ext:qtip="{display}" class="x-combo-list-item">{display}</div></tpl>',
        triggerAction: 'query',
        valueField: 'identifier',
        width: 330
    });
    //----------------------------------------------------------------------------------
    // Combobox of developmental stages
    //----------------------------------------------------------------------------------
    var developmentalStageComboBox = new Ext.form.ComboBox({
        displayField: 'display',
        emptyText: 'Select Developmental Stage Term...',
        listeners: {
            beforequery: function(q) {
                var paramObj = {};
                paramObj.species_id = assayedInSpeciesComboBox.getValue();
                if ( q.query.substr(0, 4) === 'FBdv' ) {
                    paramObj.identifier = q.query + '*';
                } else {
                    paramObj.term = '*' + q.query + '*';
                }
                q.combo.getStore().load({ params: paramObj });
                q.cancel = true;
            },
            specialkey: checkEnter
        },
        store: developmentalStageStore,
        tpl: '<tpl for="."><div ext:qtip="{display}" class="x-combo-list-item">{display}</div></tpl>',
        triggerAction: 'query',
        valueField: 'identifier',
        width: 330
    });
    //----------------------------------------------------------------------------------
    // Combobox of biological processes
    //----------------------------------------------------------------------------------
    var biologicalProcessComboBox = new Ext.form.ComboBox({
        displayField: 'display',
        emptyText: 'Select Biological Process Term...',
        listeners: {
            beforequery: function(q) {
                var paramObj = {};
                if ( q.query.substr(0, 2) === 'GO' ) {
                    paramObj.identifier = q.query + '*';
                } else {
                    paramObj.term = '*' + q.query + '*';
                }
                q.combo.getStore().load({ params: paramObj });
                q.cancel = true;
            },
            specialkey: checkEnter
        },
        store: biologicalProcessStore,
        tpl: '<tpl for="."><div ext:qtip="{display}" class="x-combo-list-item">{display}</div></tpl>',
        triggerAction: 'query',
        valueField: 'identifier',
        width: 330
    });
    //----------------------------------------------------------------------------------
    // Date field for the entries updated after the date entered
    //----------------------------------------------------------------------------------
    var lastUpdateDateField = new Ext.form.DateField({
        autoCreate: {
            maxLength: 29,
            size: 15,
            tag: 'input'
        },
        fieldLabel: '<b>Last Updated After...</b> <a href="javascript:lastUpdateHelp()">?</a>',
        format: 'M d Y',
        listeners: { specialkey: checkEnter }
    });
    //----------------------------------------------------------------------------------    
    // Date field for the entries added on or after the date entered
    //----------------------------------------------------------------------------------
    var dateAddedDateField = new Ext.form.DateField({
        autoCreate: {
            maxLength: 29,
            size: 15,
            tag: 'input'
        },        
        fieldLabel: '<b>Entry Added After...</b> <a href="javascript:dateAddedHelp()">?</a>',
        format: 'M d Y',
        listeners: { specialkey: checkEnter }
    });
    //----------------------------------------------------------------------------------    
    // Text field that contains the last search URL
    //----------------------------------------------------------------------------------
    var urlLinkTextField = new Ext.form.TextField({
        fieldLabel: '<b>URL for Last Search</b>',
        listeners: {
            focus: highlightUrl
        },
        readOnly: true,
        value: redflyBaseUrl + 'search.php',
        width: 475
    });
    //----------------------------------------------------------------------------------
    // Button to search the most recent updates
    //----------------------------------------------------------------------------------
    var recentUpdatesButton = new Ext.Button({
        handler: function () {
            sequenceFromSpeciesComboBox.reset();
            assayedInSpeciesComboBox.reset();
            setResultTabTitle({
                id: 'tab-crm-rc',
                loading: true}
            );
            setResultTabTitle({
                id: 'tab-crmsegment',
                loading: true}
            );
            setResultTabTitle({
                id: 'tab-pcrm',
                loading: true}
            );
            setResultTabTitle({
                id: 'tab-tfbs',
                loading: true}
            );
            setResultTabTitle({
                id: 'tab-icrm',
                loading: true}
            );
            // date of the last updated CRM
            var dbLastCrm = databaseInformationStore.getAt(0).get('last_crm_update').toString();
            REDfly.config.crmSearchParameters = {
                is_crm: true,
                last_update: '>' + dbLastCrm
            };
            crmStore.load();
            // date of the last updated RC
            var dbLastRc = databaseInformationStore.getAt(0).get('last_rc_update').toString();
            REDfly.config.rcSearchParameters = {
                last_update: '>' + dbLastRc
            };
            rcStore.load();
            // date of the last updated CRM segment
            var dbLastCrmSegment = databaseInformationStore.getAt(0).get('last_crmsegment_update').toString();
            REDfly.config.crmsegmentSearchParameters = {
                last_update: '>' + dbLastCrmSegment
            };
            crmSegmentStore.load();
            // date of the last updated predicted CRM
            var dbLastPredictedCrm = databaseInformationStore.getAt(0).get('last_predictedcrm_update').toString();
            REDfly.config.predictedCrmSearchParameters = {
                last_update: '>' + dbLastPredictedCrm
            };  
            predictedCrmStore.load();
            // date of the last updated TFBS
            var dbLastTfbs = databaseInformationStore.getAt(0).get('last_tfbs_update').toString();
            REDfly.config.tfbsSearchParameters = {
                last_update: '>' + dbLastTfbs
            };
            tfbsStore.load();
            // Inferred CRMs are never updated, just re-created
            REDfly.config.inferredCrmSearchParameters = null;
            inferredCrmStore.load();
        },
        text: 'Recent Updates'
    });
    //----------------------------------------------------------------------------------
    // Button to search the entire database
    //----------------------------------------------------------------------------------    
    var browseAllButton = new Ext.Button({
        handler: function () {
            sequenceFromSpeciesComboBox.reset();
            assayedInSpeciesComboBox.reset();
            setResultTabTitle({
                id: 'tab-crm-rc',
                loading: true}
            );
            setResultTabTitle({
                id: 'tab-crmsegment',
                loading: true
            });
            setResultTabTitle({
                id: 'tab-pcrm',
                loading: true
            });
            setResultTabTitle({
                id: 'tab-tfbs',
                loading: true
            });
            setResultTabTitle({
                id: 'tab-icrm',
                loading: true
            });
            REDfly.config.crmSearchParameters = {
                is_crm: true
            };
            crmStore.load();
            REDfly.config.rcSearchParameters = null;
            rcStore.load();
            REDfly.config.crmsegmentSearchParameters = null;
            crmSegmentStore.load();
            REDfly.config.predictedCrmSearchParameters = null;
            predictedCrmStore.load();
            REDfly.config.tfbsSearchParameters = null;
            tfbsStore.load();
            REDfly.config.inferredCrmSearchParameters = null;
            inferredCrmStore.load();
        },
        text: 'Browse All'
    });
    //----------------------------------------------------------------------------------    
    // Button to make a customized database search
    //----------------------------------------------------------------------------------    
    var searchButton = new Ext.Button({
        handler: function () {
            searchDatabase();
        },
        text: '<b>Search</b>',
        width: 80
    });
    //----------------------------------------------------------------------------------
    // Advanced search panel for the search panel
    //----------------------------------------------------------------------------------
    var advancedSearchFieldSet = new Ext.form.FieldSet({
        animCollapse: true,
        autoHeight: true,
        autoShow: true,
        collapsed: true,
        collapsible: true,
        hideMode: 'offsets',
        id: 'advancedSearch',
        items: [
            {
                activeTab: 0,
                autoHeight: true,
                defaults: {
                    autoHeight: true,
                    cls: 'custom-search',
                    layout: 'form',
                    padding: 4
                },
                id: 'tabPanel',
                items: [
                    {
                        id: 'crmtab',
                        items: [
                            {
                                defaults: {
                                    columnWidth: 0.33,
                                    columns: 1
                                },
                                items: [
                                    {
                                        defaults: {
                                            name: 'rc-data-type'
                                        },
                                        id: 'rc-data-type',
                                        items: [
                                            {
                                                anchor: '-15',
                                                html: '<b>Data Type:</b>',
                                                xtype: 'label'
                                            }, {
                                                boxLabel: 'All Reporter Constructs',
                                                checked: true,
                                                id: 'rc-data-type-all',
                                                inputValue: 'all'
                                            }, {
                                                boxLabel: 'CRM <a href="javascript:crmHelp()">?</a>',
                                                id: 'rc-data-type-crm',
                                                inputValue: 'crm'
                                            }, {
                                                boxLabel: 'CRM with TFBS <a href="javascript:crmWithTfbsHelp()">?</a>',
                                                id: 'rc-data-type-crm-with-tfbs',
                                                inputValue: 'crm-with-tfbs'
                                            }
                                        ],
                                        xtype: 'radiogroup'
                                    }, {
                                        id: 'rc-restrictions',
                                        items: [
                                            {
                                                anchor: '-15',
                                                html: '<b>Restrictions:</b>',
                                                xtype: 'label'
                                            }, {
                                                boxLabel: 'Positive Expression Only <a href="javascript:expressionPositiveHelp()">?</a>',
                                                id: 'rc-restrictions-anatomical-expression-positive',
                                                listeners: {
                                                    check: uncheck
                                                },
                                                name: 'anatomical-expression-positive'
                                            }, {
                                                boxLabel: 'Negative Expression Only <a href="javascript:expressionNegativeHelp()">?</a>',
                                                id: 'rc-restrictions-anatomical-expression-negative',
                                                listeners: {
                                                    check: uncheck
                                                },
                                                name: 'anatomical-expression-negative'
                                            }, {
                                                boxLabel: 'Including Enhancer <a href="javascript:includeEnhancerHelp()">?</a>',
                                                id: 'rc-restrictions-include-enhancer',
                                                listeners: {
                                                    check: uncheck
                                                },
                                                name: 'include-enhancer'
                                            }, {
                                                boxLabel: 'Including Silencer <a href="javascript:includeSilencerHelp()">?</a>',
                                                id: 'rc-restrictions-include-silencer',
                                                listeners: {
                                                    check: uncheck
                                                },
                                                name: 'include-silencer'
                                            }, {
                                                boxLabel: 'Excluding Enhancer <a href="javascript:excludeEnhancerHelp()">?</a>',
                                                id: 'rc-restrictions-exclude-enhancer',
                                                listeners: {
                                                    check: uncheck
                                                },
                                                name: 'exclude-enhancer'
                                            }, {
                                                boxLabel: 'Excluding Silencer <a href="javascript:excludeSilencerHelp()">?</a>',
                                                id: 'rc-restrictions-exclude-silencer',
                                                listeners: {
                                                    check: uncheck
                                                },
                                                name: 'exclude-silencer'
                                            }, {
                                                boxLabel: 'Minimized Only <a href="javascript:minimizedHelp()">?</a>',
                                                id: 'rc-restrictions-minimized',
                                                name: 'minimized'
                                            }
                                        ],
                                        xtype: 'checkboxgroup'
                                    }, {
                                        id: 'rc-miscellaneous-options',
                                        items: [
                                            {
                                                anchor: '-15',
                                                html: '<b>Miscellaneous Options:</b>',
                                                xtype: 'label'
                                            }, {
                                                boxLabel: 'Images <a href="javascript:rcHasImagesHelp()">?</a>',
                                                id: 'rc-miscellaneous-options-has-images',
                                                name: 'has-images'
                                            }
                                        ],
                                        xtype: 'checkboxgroup'
                                    }
                                ],
                                layout: 'column'
                            }
                        ],
                        tabTip: 'These options are modifiers for CRM/RC search only',
                        title: 'CRM/RC Options'
                    }, {
                        id: 'tfbstab',
                        items: [
                            {
                                defaults: {
                                    columns: 1,
                                    columnWidth: 0.33
                                },
                                items: [
                                    {
                                        defaults: {
                                            name: 'tfbs-data-type'
                                        },
                                        id: 'tfbs-data-type',
                                        items: [
                                            {
                                                anchor: '-15',
                                                html: '<b>Data Type:</b>',
                                                xtype: 'label'
                                            }, {
                                                boxLabel: 'All TFBS',
                                                checked: true,
                                                id: 'tfbs-data-type-all',
                                                inputValue: 'tfbs'
                                            }, {
                                                boxLabel: 'TFBS with CRM <a href="javascript:tfbsWithCrmHelp()">?</a>',
                                                id: 'tfbs-data-type-with-crm',
                                                inputValue: 'with-crm'
                                            }
                                        ],
                                        xtype: 'radiogroup'
                                    }, {
                                        defaults: {
                                            name: 'tfbs-restrictions'
                                        },
                                        id: 'tfbs-restrictions',
                                        items: [
                                            {
                                                anchor: '-15',
                                                html: '<b>Restrictions:</b>',
                                                xtype: 'label'
                                            }, {
                                                boxLabel: 'All Genes <a href="javascript:allGenesHelp()">?</a>',
                                                checked: true,
                                                id: 'tfbs-restrictions-all-genes',
                                                inputValue: 'all-genes'
                                            }, {
                                                boxLabel: 'Target Gene Only <a href="javascript:targetGeneHelp()">?</a>',
                                                id: 'tfbs-restrictions-target-gene-only',
                                                inputValue: 'target-gene-only'
                                            }, {
                                                boxLabel: 'TF Gene Only <a href="javascript:tfGeneHelp()">?</a>',
                                                id: 'tfbs-restrictions-tf-gene-only',
                                                inputValue: 'tf-gene-only'
                                            }
                                        ],
                                        xtype: 'radiogroup'                                        
                                    }, {
                                        id: 'tfbs-miscellaneous-options',
                                        items: [
                                            {
                                                anchor: '-15',
                                                html: '<b>Miscellaneous Options:</b>',
                                                xtype: 'label'
                                            }, {
                                                boxLabel: 'Images <a href="javascript:tfbsHasImagesHelp()">?</a>',
                                                id: 'tfbs-miscellaneous-options-has-images',
                                                name: 'has-images'
                                            }
                                        ],
                                        xtype: 'checkboxgroup'
                                    }
                                ],
                                layout: 'column'
                            }
                        ],
                        tabTip: 'These options are modifiers for only the TFBS search',
                        title: 'TFBS Options'
                    }
                ],
                layoutOnTabChange: true,
                plain: true,
                xtype: 'tabpanel'
            }, {
                defaults: {
                    columnWidth: '0.25'
                },
                id: 'position',
                items: [
                    {
                        items: [
                            {
                                anchor: '-15',
                                html: '<b>Position</b> <a href="javascript:positionHelp()">?</a>:',
                                xtype: 'label'
                            }, {
                                boxLabel: '5\' to gene',
                                id: 'five_prime',
                                name: 'five_prime',
                                xtype: 'checkbox'
                            }
                        ]
                    }, {
                        items: [
                            {
                                anchor: '-15',
                                cls: 'x-form-check-group-label',
                                text: '',
                                xtype: 'label'
                            }, {
                                boxLabel: '3\' to gene',
                                id: 'three_prime',
                                name: 'three_prime',
                                xtype: 'checkbox'
                            }
                        ]
                    }, {
                        items: [
                            {
                                anchor: '-15',
                                cls: 'x-form-check-group-label',
                                text: '',
                                xtype: 'label'
                            }, {
                                boxLabel: 'In Intron',
                                id: 'in_intron',
                                name: 'in_intron',
                                xtype: 'checkbox'
                            }
                        ]
                    }, {
                        items: [
                            {
                                anchor: '-15',
                                cls: 'x-form-check-group-label',
                                text: '',
                                xtype: 'label'
                            }, {
                                boxLabel: 'In Exon',
                                id: 'in_exon',
                                name: 'in_exon',
                                xtype: 'checkbox'
                            }
                        ]
                    }
                ],
                xtype: 'checkboxgroup'
            }, {
                defaults: {
                    columnWidth: 0.25,
                    layout: 'form'
                },
                items: [
                    {
                        items: [chromosomeComboBox],
                        labelAlign: 'top',
                        layout: 'form'
                    }, {
                        columnWidth: 0.5,
                        items: [
                            {
                                items: [startCoordinateTextField],
                                labelAlign: 'top',
                                layout: 'form',
                                width: 92
                            }, {
                                html: '<br /><br />..',
                                width: 9
                            }, {
                                items: [endCoordinateTextField],
                                labelAlign: 'top',
                                layout: 'form',
                                width: 92
                            }
                        ],
                        layout: 'hbox'
                    }, {
                        items: [maximumSequenceSizeTextField],
                        layout: 'form',
                        labelAlign: 'top'
                    }, {
                        items: [searchRangeIntervalTextField],
                        labelAlign: 'top',
                        layout: 'form'
                    }
                ],
                layout: 'column'
            }, {
                bodyStyle: 'padding-top: 10px',
                items: [evidenceComboBox],
                layout: 'form',
                xtype: 'panel'
            }, {
                bodyStyle: 'padding-top: 14px',
                items: [
                    {
                        html: '<b>Anatomical Expression Term (Anatomy Ontology Updated ' +
                            redflyAnatomyOntologyUpdateDate +
                            ')</b> <a href="javascript:anatomicalExpressionTermHelp()">?</a>:',
                        xtype: 'label'
                    }
                ],
                layout: 'form',
                xtype: 'panel'
            }, {
                bodyStyle: 'padding-top: 10px',
                items: [
                    {
                        columnWidth: 0.5,
                        hideLabels: true,
                        items: [anatomicalExpressionComboBox],
                        layout: 'form'
                    }, {
                        columnWidth: 0.5,
                        bodyStyle: 'padding-top: 1px',
                        items: [
                            {
                                id: 'exactAnatomicalExpressionTerm',
                                items: [
                                    {
                                        items: [
                                            {
                                                boxLabel: 'Exact Anatomical Expression Term ' +
                                                    '<a href="javascript:exactAnatomicalExpressionTermHelp()">?</a>',
                                                name: 'exactAnatomicalExpressionTermCb'
                                            }
                                        ]
                                    }
                                ],
                                xtype: 'checkboxgroup'
                            }
                        ]
                    }
                ],
                layout: 'column'
            }, {
                bodyStyle: 'padding-top: 14px',
                items: [
                    {
                        html: '<b>Developmental Stage Term (Development Ontology Updated ' +
                            redflyDevelopmentOntologyUpdateDate +
                            ')</b> <a href="javascript:developmentalStageTermHelp()">?</a>:',
                        xtype: 'label'
                    }
                ],
                layout: 'form',
                xtype: 'panel'
            }, {
                bodyStyle: 'padding-top: 10px',
                items: [
                    {
                        columnWidth: 0.5,
                        hideLabels: true,
                        items: [developmentalStageComboBox],
                        layout: 'form'
                    }, {
                        columnWidth: 0.5,
                        bodyStyle: 'padding-top: 1px',
                        items: [
                            {
                                id: 'exactDevelopmentalStageTerm',
                                items: [
                                    {
                                        items: [
                                            {
                                                boxLabel: 'Exact Developmental Stage Term ' +
                                                    '<a href="javascript:exactDevelopmentalStageTermHelp()">?</a>',
                                                name: 'exactDevelopmentalStageTermCb'
                                            }
                                        ]
                                    }
                                ],
                                xtype: 'checkboxgroup'
                            }
                        ]
                    }
                ],
                layout: 'column'
            }, {
                bodyStyle: 'padding-top: 14px',
                items: [
                    {
                        html: '<b>Biological Process Term (GO Ontology Updated ' +
                            goOntologyUpdateDate +
                            ')</b> <a href="javascript:biologicalProcessTermHelp()">?</a>:',
                        xtype: 'label'
                    }
                ],
                layout: 'form',
                xtype: 'panel'
            }, {
                bodyStyle: 'padding-top: 10px',
                items: [
                    {
                        columnWidth: 0.5,
                        hideLabels: true,
                        items: [biologicalProcessComboBox],
                        layout: 'form'
                    }, {
                        columnWidth: 0.5,
                        bodyStyle: 'padding-top: 1px',
                        items: [
                            {
                                id: 'exactBiologicalProcessTerm',
                                items: [
                                    {
                                        items: [
                                            {
                                                boxLabel: 'Exact Biological Process Term' +
                                                    '<a href="javascript:exactBiologicalProcessTermHelp()">?</a>',
                                                name: 'exactBiologicalProcessTermCb'
                                            }
                                        ]
                                    }
                                ],
                                xtype: 'checkboxgroup'
                            }
                        ]
                    }
                ],
                layout: 'column'
            }, {
                bodyStyle: 'padding-top: 10px',
                defaults: {
                    columnWidth: 0.5,
                    layout: 'form'
                },
                items: [
                    {
                        items: [lastUpdateDateField]
                    }, {
                        items: [dateAddedDateField]
                    }
                ],
                layout: 'column'
            }
        ],
        listeners: {
            show: function () {
                this.render();
            }
        },
        title: '<b>Advanced Search</b>',
        xtype: 'fieldset'
    });
    //----------------------------------------------------------------------------------
    // Search panel that holds all of the search functions
    //----------------------------------------------------------------------------------
    var searchFormPanel = new Ext.form.FormPanel({
        border: false,
        buttonAlign: 'center',
        buttons: [
            {
                handler: searchDatabase,
                id: 'searchButton',
                text: '<b>Search</b>'
            }, {
                handler: function () {
                    geneComboBox.setValue('');
                    includeRangeRadioGroup.reset();
                    elementNameOrFBtpIdentifierTextField.setValue('');
                    pubmedIdTextField.setValue('');
                    sequenceFromSpeciesComboBox.setValue('');
                    assayedInSpeciesComboBox.setValue('');
                    Ext.getCmp('exclude_cell_culture_only').setValue(true);
                    Ext.getCmp('rc-data-type').reset();
                    Ext.getCmp('rc-restrictions').reset();
                    Ext.getCmp('rc-miscellaneous-options').reset();
                    Ext.getCmp('tfbs-data-type').reset();
                    Ext.getCmp('tfbs-restrictions').reset();
                    Ext.getCmp('tfbs-miscellaneous-options').reset();
                    Ext.getCmp('position').reset();
                    chromosomeComboBox.setValue('Any');
                    startCoordinateTextField.setValue('');
                    endCoordinateTextField.setValue('');
                    maximumSequenceSizeTextField.setValue('');
                    searchRangeIntervalTextField.setValue('');
                    evidenceComboBox.setValue('');
                    anatomicalExpressionComboBox.setValue('');
                    Ext.getCmp('exactAnatomicalExpressionTerm').reset();
                    developmentalStageComboBox.setValue('');
                    Ext.getCmp('exactDevelopmentalStageTerm').reset();
                    biologicalProcessComboBox.setValue('');
                    Ext.getCmp('exactBiologicalProcessTerm').reset();
                    lastUpdateDateField.setValue('');
                    dateAddedDateField.setValue('');
                },
                text: 'Clear Search Fields'
            }, {
                handler: function () {
                    crmStore.removeAll();
                    rcStore.removeAll();
                    crmSegmentStore.removeAll();
                    predictedCrmStore.removeAll();
                    tfbsStore.removeAll();
                    inferredCrmStore.removeAll();
                    setResultTabTitle({
                        id: 'tab-crm-rc',
                        reset: true}
                    );
                    setResultTabTitle({
                        id: 'tab-crmsegment',
                        reset: true
                    });
                    setResultTabTitle({
                        id: 'tab-pcrm',
                        reset: true
                    });
                    setResultTabTitle({
                        id: 'tab-tfbs',
                        reset: true}
                    );
                    setResultTabTitle({
                        id: 'tab-icrm',
                        reset: true}
                    );
                },
                text: 'Clear Search Data'
            }
        ],
        fileUpload: true,
        frame: true,
        id: 'searchFormPanel',
        items: [
            {
                items: [
                    {
                        items: [
                            {
                                items: [
                                    {
                                        bodyStyle:'padding:0 15px 0',
                                        border: true,
                                        items:[
                                            geneComboBox,
                                            includeRangeRadioGroup
                                        ],
                                        layout: 'column',
                                        margins: '15px 0 0',
                                        title: '<b style="color:black;">Gene Name</b> <a href="javascript:geneHelp()">?</a>',
                                        width: 230,
                                        xtype: 'fieldset'   
                                    }, {
                                        bodyStyle:'padding:0 25px 0',
                                        border: false,                                        
                                        items: [
                                            elementNameOrFBtpIdentifierTextField,
                                            sequenceFromSpeciesComboBox
                                        ],
                                        layout: 'form',
                                        width: 250,
                                        xtype: 'fieldset'
                                    },{
                                        bodyStyle:'padding:0 15px 0',
                                        border: false,                                        
                                        items: [
                                            pubmedIdTextField,
                                            assayedInSpeciesComboBox
                                        ],
                                        layout: 'form',
                                        width: 250,
                                        xtype: 'fieldset'
                                    }
                                ],
                                layout: 'column'
                            }, {
                                items: [{
                                    items: [
                                        {
                                            columnWidth: 0.2,
                                            items: [recentUpdatesButton]
                                        }, {
                                            columnWidth: 0.24,
                                            items: [{
                                                handler: function () {
                                                    this.showMenu(true);
                                                },
                                                menu: [
                                                    {
                                                        handler: function () {
                                                            // Construct the window on demand
                                                            if ( ! downloadAllCRMs ) {
                                                                var url = '/file/{file_type}/crm?';
                                                                downloadAllCRMs = new REDfly.window.download({
                                                                    downloadAllItems: true,
                                                                    downloadUrlTemplate: new Ext.Template(url),
                                                                    title: 'Download All CRMs',
                                                                });
                                                            }
                                                            // The Drosophila melanogaster species by default
                                                            downloadAllCRMs.speciesChooser.setValue('Drosophila melanogaster');
                                                            // The default file type is the BED one by alphabetical sort
                                                            downloadAllCRMs.fileTypeChooser.setValue('BED');
                                                            downloadAllCRMs.setFileTypeOptionPanel('BED');
                                                            downloadAllCRMs.show();
                                                        },
                                                        text: 'Download All CRMs'
                                                    }, {
                                                        handler: function () {
                                                            // Construct the window on demand
                                                            if ( ! downloadAllRCs ) {
                                                                var url = '/file/{file_type}/rc?';
                                                                downloadAllRCs = new REDfly.window.download({
                                                                    downloadAllItems: true,
                                                                    downloadUrlTemplate: new Ext.Template(url),
                                                                    title: 'Download All RCs'
                                                                });
                                                            }
                                                            // The Drosophila melanogaster species by default
                                                            downloadAllRCs.speciesChooser.setValue('Drosophila melanogaster');
                                                            // The default file type is the BED one by alphabetical sort
                                                            downloadAllRCs.fileTypeChooser.setValue('BED');
                                                            downloadAllRCs.setFileTypeOptionPanel('BED');
                                                            downloadAllRCs.show();
                                                        },
                                                        text: 'Download All RCs'
                                                    }, {
                                                        handler: function () {
                                                            // Construct the window on demand
                                                            if ( ! downloadAllCRMSegments ) {
                                                                var url = '/file/{file_type}/crm_segment?';
                                                                downloadAllCRMSegments = new REDfly.window.download({
                                                                    downloadAllItems: true,
                                                                    downloadUrlTemplate: new Ext.Template(url),
                                                                    title: 'Download All CRM Segments'
                                                                });
                                                            }
                                                            // The Drosophila melanogaster species by default
                                                            downloadAllCRMSegments.speciesChooser.setValue('Drosophila melanogaster');
                                                            // The default file type is the BED one by alphabetical sort
                                                            downloadAllCRMSegments.fileTypeChooser.setValue('BED');
                                                            downloadAllCRMSegments.setFileTypeOptionPanel('BED');
                                                            downloadAllCRMSegments.show();
                                                        },
                                                        text: 'Download All CRM Segments'
                                                    }, {
                                                        handler: function () {
                                                            // Construct the window on demand
                                                            if ( ! downloadAllPredictedCRMs ) {
                                                                var url = '/file/{file_type}/predicted_crm?';
                                                                downloadAllPredictedCRMs = new REDfly.window.download({
                                                                    downloadAllItems: true,
                                                                    downloadUrlTemplate: new Ext.Template(url),
                                                                    title: 'Download All Predicted CRMs'
                                                                });
                                                            }
                                                            // The Drosophila melanogaster species by default
                                                            downloadAllPredictedCRMs.speciesChooser.setValue('Drosophila melanogaster');
                                                            // The default file type is the BED one by alphabetical sort
                                                            downloadAllPredictedCRMs.fileTypeChooser.setValue('BED');
                                                            downloadAllPredictedCRMs.setFileTypeOptionPanel('BED');
                                                            downloadAllPredictedCRMs.show();
                                                        },
                                                        text: 'Download All Predicted CRMs'
                                                    }, {
                                                        handler: function () {
                                                            // Construct the window on demand
                                                            if ( ! downloadAllTFBSs ) {
                                                                var url = '/file/{file_type}/tfbs?';
                                                                downloadAllTFBSs = new REDfly.window.download({
                                                                    downloadAllItems: true,
                                                                    downloadUrlTemplate: new Ext.Template(url),
                                                                    title: 'Download All TFBSs'
                                                                });
                                                            }
                                                            // The Drosophila melanogaster species by default
                                                            downloadAllTFBSs.speciesChooser.setValue('Drosophila melanogaster');
                                                            // The default file type is the BED one by alphabetical sort
                                                            downloadAllTFBSs.fileTypeChooser.setValue('BED');
                                                            downloadAllTFBSs.setFileTypeOptionPanel('BED');
                                                            downloadAllTFBSs.show();
                                                        },
                                                        text: 'Download All TFBSs'
                                                    }
                                                ],
                                                text: 'Batch Download',
                                                xtype: 'splitbutton'
                                            }]
                                        }, {
                                            columnWidth: 0.35,
                                            items: [browseAllButton]
                                        }, {
                                            columnWidth: 0.20,
                                            items: [searchButton]
                                        }, {
                                            boxLabel: 'Exclude Cell Culture Only <a href="javascript:excludeCellCultureHelp()">?</a>',
                                            checked: true,
                                            id: 'exclude_cell_culture_only',
                                            name: 'exclude_cell_culture_only',
                                            xtype: 'checkbox'
                                        }
                                    ],
                                    layout: 'column'
                                }]
                            }
                        ],
                        title: '<b>Search Options</b>',
                        xtype: 'fieldset'
                    },
                    advancedSearchFieldSet
                ],
                layout: 'form',
                xtype: 'panel'
            }, {
                border: false,
                html: '<hr style="border:0; height:1px; color:#ccc; background-color:#ccc;" />',
                xtype: 'panel'
            }, {
                items: [urlLinkTextField],
                layout: 'form',
                xtype: 'panel'
            }
        ],
        labelAlign: 'top',
        renderTo: 'search',
        title: '<b>REDFly Database Search</b>',
        width: 730
    });
    // Required so that the tab panel gets initiated so that all of the components on it
    // are not undefined
    Ext.getCmp('tabPanel').setActiveTab(1);
    Ext.getCmp('tabPanel').setActiveTab(0);
    advancedSearchFieldSet.collapse(true);
    REDfly.config.windowAreaStartCoordinates = searchFormPanel.getPosition();
    // The start of the window area should be to the right of the search panel
    REDfly.config.windowAreaStartCoordinates[0] += REDfly.config.searchPanelWidth;
    REDfly.config.nextWindowCoordinates = REDfly.config.windowAreaStartCoordinates;
    // Checkbox selection models
    var crmCheckboxSelectionModel = new Ext.grid.CheckboxSelectionModel();
    var crmSegmentCheckboxSelectionModel = new Ext.grid.CheckboxSelectionModel();
    var predictedCrmCheckboxSelectionModel = new Ext.grid.CheckboxSelectionModel();
    var tfbsCheckboxSelectionModel = new Ext.grid.CheckboxSelectionModel();
    var inferredCrmCheckboxSelectionModel = new Ext.grid.CheckboxSelectionModel();
    //----------------------------------------------------------------------------------
    // Handle a click event on the RC/CRM result grid by opening an RC/CRM window.
    // Parameters:
    //      thisGrid: the grid that was clicked
    //      rowIndex: the row index that was clicked
    //      columnIndex: the column index that was clicked
    //      e: the event
    //----------------------------------------------------------------------------------
    function cellClickRc(thisGrid, rowIndex, columnIndex, e)
    {
        // Column 0 is the checkbox. Do not open a window if the checkbox was clicked.
        if ( columnIndex === 0 ) return;
        var record = thisGrid.getStore().getAt(rowIndex);
        var redflyId = record.get('redfly_id');
        var entityName = record.get('name');
        // If an user double-clicks on a grid cell then do not open two of the same window.
        if ( redflyId === REDfly.state.lastSelectedReporterConstruct ) return;
        // If the window already exists, show it
        if ( REDfly.state.windowGroup.get(redflyId) ) {
            REDfly.state.windowGroup.get(redflyId).show();
        } else {
            REDfly.state.lastSelectedReporterConstruct = redflyId;
            var rcWindow = REDfly.fn.showOrCreateEntityWindow(
                redflyId,
                entityName
            );
        }
    }
    var rcGrid = new Ext.grid.GridPanel({
        autoExpandColumn: 'redfly_id',
        columnLines: true,
        columns: [
            crmCheckboxSelectionModel,
            {
                dataIndex: 'is_crm',
                header: 'Type',
                id: 'Type',
                menuDisabled: true,
                renderer: function (value) {
                    if (value === '1') {
                        return 'CRM';
                    }
                    return 'RC';
                },
                sortable: true,
                width: 45
            }, {
                dataIndex: 'name',
                header: 'Element Name',
                id: 'Name',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('name') + '"';
                    return value;
                },
                sortable: true,
                width: 180
            }, {
                dataIndex: 'gene',
                header: 'Gene Name',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('gene') + '"';
                    return value;
                },
                sortable: true,
                width: 120
            }, {
                dataIndex: 'redfly_id',
                header: 'Redfly ID',
                id: 'redfly_id',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('redfly_id') + '"';
                    return value;
                },
                sortable: true,
                width: 120
            }, {
                dataIndex: 'has_images',
                header: 'Has Image?',
                id: 'has_images',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('has_images') + '"';
                    return value;
                },
                sortable: true,
                width: 80
            }
        ],
        frame: true,
        height: screen.height / 2 - 78,
        id: 'rcgrid',
        listeners: {
            cellclick: cellClickRc
        },
        sm: crmCheckboxSelectionModel,
        store: rcStore,
        stripeRows: true,
        tbar: new Ext.PagingToolbar({
            displayInfo: true,
            pageSize: REDfly.config.pageSize,
            prependButtons: true,
            store: rcStore
        }),
        title: '<b>Search Results</b>',
        view: new Ext.ux.grid.BufferView({ scrollDelay: false }),
        width: searchFormPanel.getWidth() - 2
    });
    //----------------------------------------------------------------------------------    
    // Handle a click event on the CRM segment result grid by opening an CRM segment window.
    // Parameters:
    //      thisGrid: the grid that was clicked
    //      rowIndex: the row index that was clicked
    //      columnIndex: the column index that was clicked
    //      e: the event
    //----------------------------------------------------------------------------------    
    function cellClickCrmSegment(thisGrid, rowIndex, columnIndex, e)
    {
        // Column 0 is the checkbox. Do not open a window if the checkbox was clicked.
        if ( columnIndex === 0 ) return;
        var record = thisGrid.getStore().getAt(rowIndex);
        var redflyId = record.get('redfly_id');
        var entityName = record.get('name');
        // If an user double-clicks on a grid cell then do not open two of the same window.
        if ( redflyId === REDfly.state.lastSelectedCrmSegment ) return;
        // If the window already exists, show it
        if ( REDfly.state.windowGroup.get(redflyId) ) {
            REDfly.state.windowGroup.get(redflyId).show();
        } else {
            REDfly.state.lastSelectedCrmSegment = redflyId;
            var crmSegmentWindow = REDfly.fn.showOrCreateEntityWindow(
                redflyId,
                entityName
            );
        }
    }
    var crmSegmentGrid = new Ext.grid.GridPanel({
        autoExpandColumn: 'redfly_id',
        columnLines: true,
        columns: [
            crmSegmentCheckboxSelectionModel,
            {
                dataIndex: 'name',
                header: 'Element Name',
                id: 'Name',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('name') + '"';
                    return value;
                },
                sortable: true,
                width: 180
            }, {
                dataIndex: 'gene',
                header: 'Gene Name',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('gene') + '"';
                    return value;
                },
                sortable: true,
                width: 120
            }, {
                dataIndex: 'redfly_id',
                header: 'Redfly ID',
                id: 'redfly_id',
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('redfly_id') + '"';
                    return value;
                },
                menuDisabled: true,
                sortable: true,
                width: 120
            //}, {
            //    dataIndex: 'has_images',
            //    header: 'Has Image?',
            //    id: 'has_images',
            //    menuDisabled: true,
            //    renderer: function(value, metadata, record) {
            //        metadata.attr = 'ext:qtip="' + record.get('has_images') + '"';
            //        return value;
            //    },
            //    sortable: true,
            //    width: 80
            }
        ],
        frame: true,
        height: screen.height / 2 - 78,
        id: 'crmsegmentgrid',
        listeners: {
            cellclick: cellClickCrmSegment
        },
        sm: crmSegmentCheckboxSelectionModel,
        store: crmSegmentStore,
        stripeRows: true,
        tbar: new Ext.PagingToolbar({
            displayInfo: true,
            pageSize: REDfly.config.pageSize,
            prependButtons: true,
            store: crmSegmentStore
        }),
        title: '<b>Search Results</b>',
        view: new Ext.ux.grid.BufferView({ scrollDelay: false }),
        width: searchFormPanel.getWidth() - 2
    });
    //----------------------------------------------------------------------------------    
    // Handle a click event on the predicted CRM result grid by opening a predicted CRM window.
    // Parameters:
    //      thisGrid: the grid that was clicked
    //      rowIndex: the row index that was clicked
    //      columnIndex: the column index that was clicked
    //      e: the event
    //----------------------------------------------------------------------------------    
    function cellClickPredictedCrm(thisGrid, rowIndex, columnIndex, e)
    {
        // Column 0 is the checkbox. Do not open a window if the checkbox was clicked.
        if ( columnIndex === 0 ) return;
        var record = thisGrid.getStore().getAt(rowIndex);
        var redflyId = record.get('redfly_id');
        var entityName = record.get('name');
        // If an user double-clicks on a grid cell then do not open two of the same window.
        if ( redflyId === REDfly.state.lastSelectedPredictedSegment ) return;
        // If the window already exists, show it
        if ( REDfly.state.windowGroup.get(redflyId) ) {
            REDfly.state.windowGroup.get(redflyId).show();
        } else {
            REDfly.state.lastSelectedPredictedSegment = redflyId;
            var predictedCrmWindow = REDfly.fn.showOrCreateEntityWindow(
                redflyId,
                entityName
            );
        }
    }    
    var predictedCrmGrid = new Ext.grid.GridPanel({
        autoExpandColumn: 3,
        columnLines: true,
        columns: [
            predictedCrmCheckboxSelectionModel,
            {
                dataIndex: 'name',
                header: 'Element Name',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('name') + '"';
                    return value;
                },
                sortable: true,
                width: 125
            }, {
                dataIndex: 'coordinates',
                header: 'Coordinates',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('coordinates') + '"';
                    return value;
                },
                sortable: true,
                width: 150
            }, {
                dataIndex: 'redfly_id',
                header: 'REDfly ID',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('redfly_id') + '"';
                    return value;
                },
                sortable: true
            }, {
                dataIndex: 'pubmed_id',
                header: 'Reference',
                menuDisabled: true,
                renderer: function (value) {
                    return '<a href="https://www.ncbi.nlm.nih.gov/pubmed/' + value +
                        '" target="_blank">' + value + '</a>';
                },
                sortable: true
            }
        ],
        frame: true,
        height: screen.height / 2 - 78,
        id: 'predictedCrmGrid',
        listeners: {
            cellclick: cellClickPredictedCrm
        },
        sm: predictedCrmCheckboxSelectionModel,
        store: predictedCrmStore,
        stripeRows: true,
        tbar: new Ext.PagingToolbar({
            displayInfo: true,
            pageSize: REDfly.config.pageSize,
            prependButtons: true,
            store: predictedCrmStore
        }),
        title: '<b>Search Results</b>',
        view: new Ext.ux.grid.BufferView({ scrollDelay: false }),
        width: searchFormPanel.getWidth() - 2
    });
    //----------------------------------------------------------------------------------            
    // Handle a click event on the TFBS result grid by opening an TFBS window.
    // Parameters:
    //      thisGrid: the grid that was clicked
    //      rowIndex: the row index that was clicked
    //      columnIndex: the column index that was clicked
    //      e: the event
    //----------------------------------------------------------------------------------    
    function cellClickTfbs(thisGrid, rowIndex, columnIndex, e)
    {
        // Column 0 is the checkbox. Do not open a window if the checkbox was clicked.
        if ( columnIndex === 0 ) return;
        var record = thisGrid.getStore().getAt(rowIndex);
        var redflyId = record.get('redfly_id');
        var entityName = record.get('name');
        // If an user double-clicks on a grid cell then do not open two of the same window.
        if ( redflyId === REDfly.state.lastSelectedTranscriptionFactorBindingSite ) return;
        // If the window already exists, show it
        if ( REDfly.state.windowGroup.get(redflyId) ) {
            REDfly.state.windowGroup.get(redflyId).show();
        } else {
            REDfly.state.lastSelectedTranscriptionFactorBindingSite = redflyId;
            var tfbsWindow = REDfly.fn.showOrCreateEntityWindow(
                redflyId,
                entityName
            );
        }
    }
    var tfbsGrid = new Ext.grid.GridPanel({
        autoExpandColumn: 'Name',
        columnLines: true,
        columns: [
            tfbsCheckboxSelectionModel,
            {
                dataIndex: 'name',
                header: 'Element Name',
                id: 'Name',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('name') + '"';
                    return value;
                },
                sortable: true,
                width: 170
            }, {
                dataIndex: 'gene',
                header: 'Gene Name',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('gene') + '"';
                    return value;
                },
                sortable: true,
                width: 80
            }, {
                dataIndex: 'tf',
                header: 'TF Name',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('tf') + '"';
                    return value;
                },
                sortable: true,
                width: 80
            }, {
                dataIndex: 'redfly_id',
                header: 'Redfly ID',
                id: 'redfly_id',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('redfly_id') + '"';
                    return value;
                },
                sortable: true,
                width: 150
            }, {
                dataIndex: 'has_images',
                header: 'Has Image?',
                id: 'has_images',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('has_images') + '"';
                    return value;
                },
                sortable: true,
                width: 80
            }
        ],
        frame: true,
        height: screen.height / 2 - 78,
        id: 'tfbsGrid',
        listeners: {
            cellclick: cellClickTfbs
        },
        sm: tfbsCheckboxSelectionModel,
        store: tfbsStore,
        stripeRows: true,
        tbar: new Ext.PagingToolbar({
            displayInfo: true,
            pageSize: REDfly.config.pageSize,
            prependButtons: true,
            store: tfbsStore
        }),
        title: '<b>Search Results</b>',
        view: new Ext.ux.grid.BufferView({ scrollDelay: false }),
        width: searchFormPanel.getWidth() - 2
    });
    //----------------------------------------------------------------------------------
    // Handle a click event on the iCRM result grid by opening an iCRM window.
    // Parameters:
    //      thisGrid: the grid that was clicked
    //      rowIndex: the row index that was clicked
    //      columnIndex: the column index that was clicked
    //      e: the event
    //----------------------------------------------------------------------------------
    function cellClickIcrm(thisGrid, rowIndex, columnIndex, e)
    {
        // Column 0 is the checkbox. Do not open a window if the checkbox was clicked.
        if ( columnIndex === 0 ) return;
        var record = thisGrid.getStore().getAt(rowIndex);
        // Since iCRMs do not have a REDfly ID we will simulate one using 'RFIC:' and the id to
        // uniquely identify the entity window.
        var redflyId = 'RFIC:' + record.get('id');
        var name = record.get('gene_name') + ' ' + record.get('coordinates');
        // If an user double-clicks on a grid cell then do not open two of the same window.
        if ( redflyId === REDfly.state.lastSelectedInferredCrm ) return;
        // If the window already exists, show it
        if ( REDfly.state.windowGroup.get(redflyId) ) {
            REDfly.state.windowGroup.get(redflyId).show();
        } else {
            REDfly.state.lastSelectedInferredCrm = redflyId;
            var icrmWindow = REDfly.fn.showOrCreateEntityWindow(redflyId, name);
        }
    }
    var inferredCrmGrid = new Ext.grid.GridPanel({
        autoExpandColumn: 3,
        columnLines: true,
        columns: [
            inferredCrmCheckboxSelectionModel,
            {
                dataIndex: 'gene',
                header: 'Gene Name',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('gene') + '"';
                    return value;
                },
                sortable: true,
                width: 125
            }, {
                dataIndex: 'coordinates',
                header: 'Coordinates',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('coordinates') + '"';
                    return value;
                },
                sortable: true,
                width: 150
            }, {
                dataIndex: 'anatomical_expression_identifiers',
                header: 'Putative Anatomical Expression Pattern',
                menuDisabled: true,
                renderer: function(value, metadata, record) {
                    metadata.attr = 'ext:qtip="' + record.get('anatomical_expression_identifiers') + '"';
                    return value;
                },
                sortable: true
            }
        ],
        frame: true,
        height: screen.height / 2 - 78,
        id: 'inferredCrmGrid',
        listeners: {
            cellclick: cellClickIcrm
        },
        sm: inferredCrmCheckboxSelectionModel,
        store: inferredCrmStore,
        stripeRows: true,
        tbar: new Ext.PagingToolbar({
            displayInfo: true,
            pageSize: REDfly.config.pageSize,
            prependButtons: true,
            store: inferredCrmStore
        }),
        title: '<b>Search Results</b>',
        view: new Ext.ux.grid.BufferView({ scrollDelay: false }),
        width: searchFormPanel.getWidth() - 2
    });
    // Create buttons for the bottom toolbar of the tab panel.
    // These buttons act on entries in all displayed panels
    var windowTabSelectorBtn = new REDfly.search.windowTabSelectorBtn();
    var tileWindowsBtn = new REDfly.search.tileWindowsBtn();
    var downloadSelectedBtn = new REDfly.search.downloadSelectedBtn({
        selectionModels: {
            crm: crmCheckboxSelectionModel,
            crmsegment: crmSegmentCheckboxSelectionModel,
            predictedcrm: predictedCrmCheckboxSelectionModel,
            tfbs: tfbsCheckboxSelectionModel
        }
    });
    var viewSelectedBtn = new REDfly.search.viewSelectedBtn({
        selectionModels: {
            crm: crmCheckboxSelectionModel,
            crmsegment: crmSegmentCheckboxSelectionModel,
            predictedcrm: predictedCrmCheckboxSelectionModel,
            tfbs: tfbsCheckboxSelectionModel
        }
    });
    var closeAllBtn = new REDfly.search.closeAllBtn();
    var resultsTabPanel = new Ext.TabPanel({
        // Defaults to the CRM/RC tab
        activeTab: 0,
        buttons: [
            windowTabSelectorBtn,
            tileWindowsBtn,
            downloadSelectedBtn,
            viewSelectedBtn,
            closeAllBtn
        ],
        height: screen.height / 2 - 50,
        items: [
            {
                id: 'tab-crm-rc',
                items: rcGrid,
                title: REDfly.config.resultTabDefaults['tab-crm-rc'].title
            }, {
                id: 'tab-crmsegment',
                items: crmSegmentGrid,
                title: REDfly.config.resultTabDefaults['tab-crmsegment'].title
            }, {
                id: 'tab-pcrm',
                items: predictedCrmGrid,
                title: REDfly.config.resultTabDefaults['tab-pcrm'].title
            },  {
                id: 'tab-tfbs',
                items: tfbsGrid,
                title: REDfly.config.resultTabDefaults['tab-tfbs'].title
            }, {
                id: 'tab-icrm',
                items: inferredCrmGrid,
                title: REDfly.config.resultTabDefaults['tab-icrm'].title
            } 
        ],
        listeners: {
            beforetabchange: tabChange
        },
        renderTo: 'grid-example',
        width: searchFormPanel.getWidth()
    });
    //----------------------------------------------------------------------------------
    // This set of "if" statements are used to parse values from the URL
    //----------------------------------------------------------------------------------
    var parsedValue, i;
    if ( (parsedValue = searchQueryString('gene_id')) !== null ) {
        geneComboBox.setValue(parsedValue);
        geneStore.load({
            params: {
                id: parsedValue
            },
            callback: function () {
                geneComboBox.setValue(geneStore.getAt(0).get('name'));
            }
        });
    }
    if ( (parsedValue = searchQueryString('tf_id')) !== null ) {
        geneComboBox.setValue(parsedValue);
        geneStore.load({
            params: {
                id: parsedValue
            },
            callback: function () {
                geneComboBox.setValue(geneStore.getAt(0).get('name'));
            }
        });
        Ext.getCmp('tfbs-restrictions').setValue('tf-gene-only', true);
    }
    if ( (parsedValue = searchQueryString('gene_identifier')) !== null ) {
        geneComboBox.setValue(parsedValue);
        geneStore.load({
            params: {
                identifier: parsedValue
            }
        });
    }
    if ( (parsedValue = searchQueryString('tf_identifier')) !== null ) {
        geneComboBox.setValue(parsedValue);
        geneStore.load({
            params: {
                identifier: parsedValue
            }
        });
        Ext.getCmp('tfbs-restrictions').setValue('tf-gene-only', true);
    }
    if ( (parsedValue = searchQueryString('gene_restrictions')) !== null ) {
        if ( parsedValue === 'both' ) {
            Ext.getCmp('tfbs-restrictions').setValue('all', true);
        } else if ( parsedValue === 'target_gene_only' ) {
            Ext.getCmp('tfbs-restrictions').setValue('target-gene-only', true);
        } else if ( parsedValue === 'tf_gene_only' ) {
            Ext.getCmp('tfbs-restrictions').setValue('tf-gene-only', true);
        }
    }
    if ( (parsedValue = searchQueryString('include_range')) !== null ) {
        includeRangeRadioGroup.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('name')) !== null ) {
        elementNameOrFBtpIdentifierTextField.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('fbtp_identifier')) !== null ) {
        elementNameOrFBtpIdentifierTextField.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('pubmed_id')) !== null ) {
        pubmedIdTextField.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('sequence_from_species_id')) !== null ) {
        sequenceFromSpeciesComboBox.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('assayed_in_species_id')) !== null ) {
        assayedInSpeciesComboBox.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('cell_culture_only')) !== null ) {
        Ext.getCmp('exclude_cell_culture_only').setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('crmOptions')) !== null ) {
        parsedValue = parsedValue.split(',');
        for ( i = 0; i < parsedValue.length; i++ ) {
            if ( parsedValue[i] === 'true' && rcOptionIds[i] !== undefined ) {
                Ext.getCmp(rcOptionIds[i]).setValue(true);
            }
        }
    }
    if ( (parsedValue = searchQueryString('is_crm')) !== null ) {
        if ( parsedValue === 'yes' || parsedValue === 'true' || parsedValue === '1' ) {
            Ext.getCmp('rc-data-type').setValue('crm', true);
        }
    }
    if ( (parsedValue = searchQueryString('has_tfbs')) !== null ) {
        if ( parsedValue === 'yes' || parsedValue === 'true' || parsedValue === '1' ) {
            Ext.getCmp('rc-data-type').setValue('crm-with-tfbs', true);
        }
    }
    if ( (parsedValue = searchQueryString('is_negative')) !== null ) {
        if ( parsedValue === 'yes' || parsedValue === 'true' || parsedValue === '1' ) {
            Ext.getCmp('rc-restrictions').setValue('anatomical-expression-negative', true);
        } else {
            Ext.getCmp('rc-restrictions').setValue('anatomical-expression-negative', false);
        }
    }
    if ( (parsedValue = searchQueryString('is_minimalized')) !== null ) {
        if ( parsedValue === 'yes' || parsedValue === 'true' || parsedValue === '1' ) {
            Ext.getCmp('rc-restrictions').setValue('minimized', true);
        }
    }
    if ( (parsedValue = searchQueryString('tfbsOptions')) !== null ) {
        parsedValue = parsedValue.split(',');
        for ( i = 0; i < parsedValue.length; i++ ) {
            if ( parsedValue[i] === 'true' && tfbsOptionIds[i] !== undefined ) {
                Ext.getCmp(tfbsOptionIds[i]).setValue(true);
            }
        }
    }
    if ( (parsedValue = searchQueryString('has_rc')) !== null ) {
        if ( parsedValue === 'yes' || parsedValue === 'true' || parsedValue === '1' ) {
            Ext.getCmp('tfbs-data-type').setValue('with-crm', true);
        }
    }
    if ( (parsedValue = searchQueryString('tfbsRestrictions')) !== null ) {
        parsedValue = parsedValue.split(',');
        for ( i = 0; i < parsedValue.length; i++ ) {
            if ( parsedValue[i] === 'true' && tfbsRestrictionsIds[i] !== undefined ) {
                Ext.getCmp(tfbsRestrictionsIds[i]).setValue(true);
            }
        }
    }
    if ( (parsedValue = searchQueryString('five_prime')) !== null ) {
        if ( parsedValue === 'yes' || parsedValue === 'true' || parsedValue === '1' ) {
            Ext.getCmp('five_prime').setValue(true);
        }
    }
    if ( (parsedValue = searchQueryString('three_prime')) !== null ) {
        if ( parsedValue === 'yes' || parsedValue === 'true' || parsedValue === '1' ) {
            Ext.getCmp('three_prime').setValue(true);
        }
    }
    if ( (parsedValue = searchQueryString('in_intron')) !== null ) {
        if ( parsedValue === 'yes' || parsedValue === 'true' || parsedValue === '1' ) {
            Ext.getCmp('in_intron').setValue(true);
        }
    }
    if ( (parsedValue = searchQueryString('in_exon')) !== null ) {
        if ( parsedValue === 'yes' || parsedValue === 'true' || parsedValue === '1' ) {
            Ext.getCmp('in_exon').setValue(true);
        }
    }
    if ( (parsedValue = searchQueryString('chr_id')) !== null ) {
        chromosomeComboBox.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('start')) !== null ) {
        startCoordinateTextField.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('end')) !== null ) {
        endCoordinateTextField.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('maximum_sequence_size')) !== null ) {
        maximumSequenceSizeTextField.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('search_range')) !== null ) {
        searchRangeIntervalTextField.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('evidence_id')) !== null ) {
        evidenceComboBox.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('anatomical_expression_identifier')) !== null ) {
        anatomicalExpressionComboBox.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('exact_anatomical_expression_identifier')) !== null ) {
        if ( parsedValue === '1' || parsedValue === 'true' || parsedValue === 'True' ) {
            parsedValue = true;
        } else {
            parsedValue = false;
        }
        Ext.getCmp('exactAnatomicalExpressionTerm').items.items[0].setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('developmental_stage_identifier')) !== null ) {
        developmentalStageComboBox.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('exact_developmental_stage_identifier')) !== null ) {
        if ( parsedValue === '1' || parsedValue === 'true' || parsedValue === 'True' ) {
            parsedValue = true;
        } else {
            parsedValue = false;
        }
        Ext.getCmp('exactDevelopmentalStageTerm').items.items[0].setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('biological_process_identifier')) !== null ) {
        biologicalProcessComboBox.setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('exact_biological_process_identifier')) !== null ) {
        if ( parsedValue === '1' || parsedValue === 'true' || parsedValue === 'True' ) {
            parsedValue = true;
        } else {
            parsedValue = false;
        }
        Ext.getCmp('exactBiologicalProcessTerm').items.items[0].setValue(parsedValue);
    }
    if ( (parsedValue = searchQueryString('last_update')) !== null ) {
        lastUpdateDateField.setValue(parsedValue.substring(4, 15));
    }
    if ( (parsedValue = searchQueryString('date_added')) !== null ) {
        dateAddedDateField.setValue(parsedValue.substring(4, 15));
    }
    if ( (parsedValue = searchQueryString('redfly_id')) !== null ) {
        redflyIdUrl = parsedValue;
    }
    // Either remove a listener from the gene search field or do an
    // initial gene search according to the specifications provided by
    // the URL
    if ( Ext.isEmpty(window.location.search.substring(1)) ) {
        geneStore.removeListener('load', searchDatabase);
    } else {
        if ( geneComboBox.getValue() === '' ) {
            geneStore.load({ params: {
                species_id: sequenceFromSpeciesComboBox.getValue()
            } });
        }
    }
    // Secondary variables that save the value of the last search for
    // the bookmarked URL
    var bookmarkGene,
        bookmarkIncludeRange,
        bookmarkElementNameOrFBtpIdentifier,
        bookmarkPubmedId,
        bookmarkSequenceFromSpeciesId,
        bookmarkAssayedInSpeciesId,
        bookmarkCellCultureOnly,
        bookmarkFivePrime,
        bookmarkThreePrime,
        bookmarkInIntron,
        bookmarkInExon,
        bookmarkChromosomeId,
        bookmarkStartCoordinate,
        bookmarkEndCoordinate,
        bookmarkMaxiumSequenceSize,
        bookmarkSearchRangeInterval,
        bookmarkEvidence,
        bookmarkAnatomicalExpressionIdentifier,
        bookmarkExactAnatomicalExpressionIdentifier,
        bookmarkDevelopmentalStageIdentifier,
        bookmarkExactDevelopmentalStageIdentifier,
        bookmarkBiologicalProcessIdentifier,
        bookmarkExactBiologicalProcessIdentifier,                
        bookmarkLastUpdate,
        bookmarkDateAdded;
    // Does a search on the database and stores the values of the
    // search in secondary variables.
    function search(searchStore)
    {
        if ( geneComboBox.getValue() === '' ) {
            bookmarkGene = '';
        } else {
            // The Aedes aegypti species
            var aaelGeneValue = geneComboBox.getValue().split('AAEL')[1];
            // The Anopheles gambiae species
            var agapGeneValue = geneComboBox.getValue().split('AGAP')[1];
            // The Drosophila melanogaster species            
            var fbgnGeneValue = geneComboBox.getValue().split('FBgn')[1];
            // The Tribolium castaneum species
            var tcGeneValue = geneComboBox.getValue().split('TC')[1];
            if ( (aaelGeneValue !== undefined) ||
                (agapGeneValue !== undefined) ||
                (fbgnGeneValue !== undefined) ||
                (tcGeneValue !== undefined) ) {
                bookmarkGene = geneStore.getAt(
                    geneStore.find(
                        'identifier',
                        geneComboBox.getValue()
                    )
                ).get('id');
            } else {
                bookmarkGene = geneStore.getAt(
                    geneStore.find(
                        'name',
                        geneComboBox.getValue()
                    )
                ).get('id');
            }
        }
        bookmarkElementNameOrFBtpIdentifier = elementNameOrFBtpIdentifierTextField.getValue();
        bookmarkPubmedId = pubmedIdTextField.getValue();
        bookmarkSequenceFromSpeciesId = sequenceFromSpeciesComboBox.getValue();
        bookmarkAssayedInSpeciesId = assayedInSpeciesComboBox.getValue();
        //bookmarkCellCultureOnly = 
        //bookmarkFivePrime =
        //bookmarkThreePrime =
        //bookmarkInIntron =
        //bookmarkInExon =
        if ( parseInt(chromosomeComboBox.getValue(), 10) === chromosomeComboBox.getValue() ) {
            bookmarkChromosomeId = chromosomeComboBox.getValue();
        } else if ( (chromosomeComboBox.getValue() !== '') &&
            (chromosomeComboBox.getValue() !== 'Any') ) {
            bookmarkChromosomeId = chromosomeComboBox.getStore().getAt(chromosomeComboBox.getStore().find(
                'display',
                chromosomeComboBox.getValue()
            )).get('id');
        }
        bookmarkStartCoordinate = startCoordinateTextField.getValue();
        bookmarkEndCoordinate = endCoordinateTextField.getValue();
        bookmarkMaxiumSequenceSize = maximumSequenceSizeTextField.getValue();
        if ( (! Ext.isEmpty(bookmarkGene)) &&
            (includeRangeRadioGroup.getValue().inputValue === 'by_locus') ) {
            bookmarkIncludeRange = 'true';
            if ( ! Ext.isEmpty(searchRangeIntervalTextField.getValue())) {
                bookmarkSearchRangeInterval = searchRangeIntervalTextField.getValue();
            } else {
                bookmarkSearchRangeInterval = 10000;
            }
        } else {
            bookmarkIncludeRange = 'false';
            bookmarkSearchRangeInterval = '';
        }
        if ( parseInt(evidenceComboBox.getValue(), 10) === evidenceComboBox.getValue() ) {
            bookmarkEvidence = evidenceComboBox.getValue();
        } else if ( (evidenceComboBox.getValue() !== '') &&
            (evidenceComboBox.getValue() !== 'Select Evidence...') ) {
            bookmarkEvidence = evidenceComboBox.getStore().getAt(evidenceComboBox.getStore().find(
                'term',
                evidenceComboBox.getValue()
            )).get('id');
        }
        bookmarkAnatomicalExpressionIdentifier = anatomicalExpressionComboBox.getValue();
        bookmarkExactAnatomicalExpressionIdentifier = Ext.getCmp('exactAnatomicalExpressionTerm').items.items[0].getValue();
        bookmarkDevelopmentalStageIdentifier = developmentalStageComboBox.getValue();
        bookmarkExactDevelopmentalStageIdentifier = Ext.getCmp('exactDevelopmentalStageTerm').items.items[0].getValue();
        bookmarkBiologicalProcessIdentifier = biologicalProcessComboBox.getValue();
        bookmarkExactBiologicalProcessIdentifier = Ext.getCmp('exactBiologicalProcessTerm').items.items[0].getValue();        
        bookmarkLastUpdate = lastUpdateDateField.getValue();
        bookmarkDateAdded = dateAddedDateField.getValue();
        urlLinkTextField.setValue(urlCreate());
        switch(searchStore) {
            case crmSegmentStore:
                setResultTabTitle({
                    id: 'tab-crmsegment',
                    results: {
                        number_of_crmsegments_search_result: crmSegmentStore.getTotalCount()
                    }
                });
                break;                
            case inferredCrmStore:
                setResultTabTitle({
                    id: 'tab-icrm',
                    results: {
                        number_of_icrms_search_result: inferredCrmStore.getTotalCount()
                    }
                });
                break;
            case predictedCrmStore:
                setResultTabTitle({
                    id: 'tab-pcrm',
                    results: {
                        number_of_pcrms_search_result: predictedCrmStore.getTotalCount()
                    }
                });
                break;
            case crmStore:
            case rcStore:
                setResultTabTitle({
                    id: 'tab-crm-rc',
                    results: {
                        number_of_crms_search_result: crmStore.getTotalCount(),
                        number_of_rcs_search_result: rcStore.getTotalCount(),
                        number_of_database_crms: databaseInformationStore.getAt(0).get('number_crms', 0),
                        number_of_database_rcs: databaseInformationStore.getAt(0).get('number_rcs', 0)
                    }
                });
                break;
            case tfbsStore:
                setResultTabTitle({
                    id: 'tab-tfbs',
                    results: { 
                        number_of_tfbss_search_result: tfbsStore.getTotalCount() 
                    }
                });
                break;
            default:
        }
    }
    function searchDatabase()
    {
        setResultTabTitle({
            id: 'tab-crm-rc',
            loading: true
        });
        setResultTabTitle({
            id: 'tab-crmsegment',
            loading: true
        });        
        setResultTabTitle({
            id: 'tab-pcrm',
            loading: true
        });
        setResultTabTitle({
            id: 'tab-tfbs',
            loading: true
        });
        setResultTabTitle({
            id: 'tab-icrm',
            loading: true
        });
        // Clears all of the previous data
        crmStore.removeAll();
        rcStore.removeAll();
        crmSegmentStore.removeAll();
        predictedCrmStore.removeAll();
        tfbsStore.removeAll();
        inferredCrmStore.removeAll();
        // Anatomical expression identifier
        var anatomicalExpressionIdentifier = '';
        // "Assayed In" species id
        var assayedInSpeciesId = '';
        // Cell culture only
        var cellCultureOnly = '';
        // Chromosome id
        var chromosomeId = '';
        // Developmental stage identifier
        var developmentalStageIdentifier = '';
        // Element name
        var elementName = '*';
        // End coordinate
        var endCoordinate = '';
        // Enhancer attribute excluded
        var enhancerAttributeExcluded = 'false';
        // Enhancer attribute included
        var enhancerAttributeIncluded = 'false';
        // UNIX time for lastUpdate (defaults to the starting UNIX time)
        var epochLastUpdated = '';
        // UNIX time for dateAdded (defaults to the starting UNIX time)
        var epochAdded = '';
        // Evidence id
        var evidenceId = '';
        // FBtp identifier
        var fbtpIdentifier = '';
        // If an entry is 5'
        var fivePrime = '';
        // Gene id
        var geneId = '';
        // Gene locus to be searched
        var geneLocus = '';
        // Gene search restrictions
        var geneRestrictions = '';
        // Gene search  (defaults to 'false')    
        var geneSearch = 'false';
        // Whether or not an entry has an associate RC
        var hasRc = '';
        // Whether or not an entry has an associated TFBS
        var hasTfbs = '';
        // Includes a search range interval (defaults to 'true')
        var includeSearchRangeInterval = 'true';
        // If an entry is an exon
        var inExon = '';
        // If an entry is an intron
        var inIntron = '';
        // Whether or not an entry is a CRM (defaults to 'false')
        var isCrm = 'false';
        // Whether or not an entry is minimalized
        var isMinimalized = '';
        // Whether or not an entry is negative
        var isNegative = '';
        // Maximum sequence size
        var maximumSequenceSize = '';
        // PubMed Id
        var pubmedId = '';
        // If a RC has images
        var rcHasImages = '';
        // Search range interval
        var searchRangeInterval = '';
        // "Sequence From" species id
        var sequenceFromSpeciesId = '';
        // Silencer attribute excluded
        var silencerAttributeExcluded = 'false';
        // Silencer attribute included
        var silencerAttributeIncluded = 'false';
        // Start coordinate
        var startCoordinate = '';
        // If a TFBS has images
        var tfbsHasImages = '';
        // TF gene id
        var tfId = '';
        // If an entry is 3'
        var threePrime = '';
        //---------------------------------------------------------------------------------
        // The next lines of data capture follow the field disposition in the search form
        //---------------------------------------------------------------------------------
        // Gets both gene (all the entities) and transcription factor (only TFBS) values
        if ( geneComboBox.getValue() !== '' ) {
            // The Aedes aegypti species
            var aaelGeneValue = geneComboBox.getValue().split('AAEL')[1];
            // The Anopheles gambiae species
            var agapGeneValue = geneComboBox.getValue().split('AGAP')[1];
            // The Drosophila melanogaster species 
            var fbgnGeneValue = geneComboBox.getValue().split('FBgn')[1];
            // The Tribolium castaneum species 
            var tcGeneValue = geneComboBox.getValue().split('TC')[1];
            var index;
            if ( (aaelGeneValue !== undefined) ||
                (agapGeneValue !== undefined) ||
                (fbgnGeneValue !== undefined) ||
                (tcGeneValue !== undefined) ) {
                index = geneStore.find(
                    'identifier',
                    geneComboBox.getValue()
                );
            } else if ( new RegExp('^\\d+$').test(geneComboBox.getValue()) ) {
                index = geneStore.findExact(
                    'id',
                    geneComboBox.getValue()
                );
            } else {
                index = geneStore.findExact(
                    'name',
                    geneComboBox.getValue()
                );
            }
            geneId = geneStore.getAt(index).get('id');
            tfId = geneStore.getAt(index).get('id');
            // For predicted and inferred CRMs
            if ( includeRangeRadioGroup.getValue().inputValue === 'by_locus' ) {
                geneSearch = 'true';
                geneLocus = geneComboBox.getValue();                
            }
            if ( includeRangeRadioGroup.getValue().inputValue === 'by_name' ) {
                geneSearch = 'true';
                geneLocus = '';
            }
        }
        // 1) Sets the gene name to be searched by locus or name and
        // 2) gets the search range interval
        if ( includeRangeRadioGroup.getValue() !== null ) { 
            if ( includeRangeRadioGroup.getValue().inputValue === 'by_locus') {
                includeSearchRangeInterval = 'true';
                if ( searchRangeIntervalTextField.getValue() !== '' ) {
                    searchRangeInterval = searchRangeIntervalTextField.getValue();
                } else {
                    searchRangeInterval = 10000;
                }
            } else {
                includeSearchRangeInterval = 'false';
                searchRangeInterval = 0;    
            }
        } else {
            includeSearchRangeInterval = 'false';
            searchRangeInterval = 0;
        }
        // Gets the element name or the FBtp value
        if ( elementNameOrFBtpIdentifierTextField.getValue() !== '' ) {
            if ( elementNameOrFBtpIdentifierTextField.getValue().toLowerCase().search(/^(fbtp\d+)$|^(fbmc\d+)$/g) === -1 ) {
                elementName = '*' + elementNameOrFBtpIdentifierTextField.getValue() + '*';
                fbtpIdentifier = '';
            } else {
                // Any FBtp/FBmc identifier must belong to the Drosophila 
                // melanogaster species at the moment.
                elementName = '';
                fbtpIdentifier = elementNameOrFBtpIdentifierTextField.getValue() + '*';
            }
        }
        // Gets the Pubmed ID
        if ( pubmedIdTextField.getValue() != '' ) {
            pubmedId = pubmedIdTextField.getValue();
        }
        // Gets the "Sequence From" species ID
        if ( sequenceFromSpeciesComboBox.getValue() !== '' ) {
            sequenceFromSpeciesId = sequenceFromSpeciesComboBox.getValue();
        }
        // Gets the "Assayed In" species ID
        if ( assayedInSpeciesComboBox.getValue() !== '' ) {
            assayedInSpeciesId = assayedInSpeciesComboBox.getValue();
        }
        // Checks if cell culture is excluded
        if ( Ext.getCmp('exclude_cell_culture_only').getValue() === true) {
            cellCultureOnly = 'false';
        }
        // Checks if the RC and CRM segment entries must be positive
        if ( Ext.getCmp('rc-restrictions-anatomical-expression-positive').getValue() ) {
            isNegative = 'false';
        }
        // Checks if the RC and CRM segment entries must be negative
        if ( Ext.getCmp('rc-restrictions-anatomical-expression-negative').getValue() ) {
            isNegative = 'true';
        }
        // Checks if the RC, CRM segment, and predicted CRM entries must include the enhancer attribute
        if ( Ext.getCmp('rc-restrictions-include-enhancer').getValue() ) {
            enhancerAttributeIncluded = 'true';
        }        
        // Checks if the RC, CRM segment, and predicted CRM entries must include the silencer attribute
        if ( Ext.getCmp('rc-restrictions-include-silencer').getValue() ) {
            silencerAttributeIncluded = 'true';
        }
        // Checks if the RC, CRM segment, and predicted CRM entries must exclude the enhancer attribute
        if ( Ext.getCmp('rc-restrictions-exclude-enhancer').getValue() ) {
            enhancerAttributeExcluded = 'true';
        }
        // Checks if the RC, CRM segment, and predicted CRM entries must exclude the silencer attribute
        if ( Ext.getCmp('rc-restrictions-exclude-silencer').getValue() ) {
            silencerAttributeExcluded = 'true';
        }
        // Checks if the RC and CRM segment entries must be minimalized
        if ( Ext.getCmp('rc-restrictions-minimized').getValue() ) {
            isMinimalized = 'true';
        } else {
            isMinimalized = 'false';
        }                
        // Checks if the RC entries have images
        if ( Ext.getCmp('rc-miscellaneous-options-has-images').getValue() ) {
            rcHasImages = 'true';
        } else {
            rcHasImages = 'false';
        }
        // Checks if the value of the tfbs gene restriction is 'both'
        if ( Ext.getCmp('tfbs-restrictions-all-genes').getValue() ) {
            geneRestrictions = 'both';
        } else {
            // Checks if the value of the tfbs gene restriction is
            // 'target gene only'
            if ( Ext.getCmp('tfbs-restrictions-target-gene-only').getValue() ) {
                geneRestrictions = '';
                tfId = '';
            } else {
                // Checks if the value of the tfbs gene restriction is
                // 'tf gene only'
                if ( Ext.getCmp('tfbs-restrictions-tf-gene-only').getValue() ) {
                    geneRestrictions = '';
                    geneId = '';
                }
            }
        }
        // Checks if the TFBS entries have images
        if ( Ext.getCmp('tfbs-miscellaneous-options-has-images').getValue() ) {
            tfbsHasImages = 'true';
        } else {
            tfbsHasImages = 'false';
        }
        // Checks if the entries are 5'
        if ( Ext.getCmp('position').items.items[0].getValue() ) {
            fivePrime = 'true';
        } else {
            fivePrime = 'false';
        }
        // Checks if the entries are 3'
        if ( Ext.getCmp('position').items.items[1].getValue() ) {
            threePrime = 'true';
        } else {
            threePrime = 'false';
        }
        // Checks if the entries are introns
        if ( Ext.getCmp('position').items.items[2].getValue() ) {
            inIntron = 'true';
        } else {
            inIntron = 'false';
        }
        // Checks if the entries are exons
        if ( Ext.getCmp('position').items.items[3].getValue() ) {
            inExon = 'true';
        } else {
            inExon = 'false';
        }
        // Gets the chromosome ID
        if ( (chromosomeComboBox.getValue() !== '') &&
            (chromosomeComboBox.getValue() !== 'Any') ) {
            chromosomeId = chromosomeStore.getAt(chromosomeStore.find(
                'display',
                chromosomeComboBox.getValue()
            )).get('id');
        }
        // Gets the start coordinate
        if ( startCoordinateTextField.getValue() !== '' ) {
            startCoordinate = String(parseInt(startCoordinateTextField.getValue(), 10));
        }
        // Gets the end coordinate
        if ( endCoordinateTextField.getValue() !== '' ) {
            endCoordinate = String(parseInt(endCoordinateTextField.getValue(), 10));
        }
        // Gets the maximum sequence size
        if ( maximumSequenceSizeTextField.getValue() !== '' ) {
            maximumSequenceSize = maximumSequenceSizeTextField.getValue();
        }
        // Gets the evidence term
        if ( evidenceComboBox.getValue() !== '' ) {
            if ( new RegExp('^\\d+$').test(evidenceComboBox.getValue()) ) {
                evidenceId = evidenceStore.getAt(evidenceStore.find(
                    'id',
                    evidenceComboBox.getValue()
                )).get('id');
            } else {
                evidenceId = evidenceStore.getAt(evidenceStore.find(
                    'term',
                    evidenceComboBox.getValue()
                )).get('id');
            }
        }
        anatomicalExpressionIdentifier = anatomicalExpressionComboBox.getValue();
        // Whether or not the anatomical expression identifier matches exactly
        // (defaults to 'false' to match all subclasses)
        var exactAnatomicalExpressionIdentifier;
        if ( Ext.getCmp('exactAnatomicalExpressionTerm').items.items[0].getValue() ) {
            exactAnatomicalExpressionIdentifier = 'true';
        } else {
            exactAnatomicalExpressionIdentifier = 'false';
        }
        developmentalStageIdentifier = developmentalStageComboBox.getValue();
        // Whether or not the developmental stage identifier matches exactly
        // (defaults to 'false' to match all subclasses)
        var exactDevelopmentalStageIdentifier;
        if ( Ext.getCmp('exactDevelopmentalStageTerm').items.items[0].getValue() ) {
            exactDevelopmentalStageIdentifier = 'true';
        } else {
            exactDevelopmentalStageIdentifier = 'false';
        }
        var biologicalProcessIdentifier = biologicalProcessComboBox.getValue();
        // Whether or not the biological process identifier matches exactly
        // (defaults to 'false' to match all subclasses)
        var exactBiologicalProcessIdentifier;
        if ( Ext.getCmp('exactBiologicalProcessTerm').items.items[0].getValue() ) {
            exactBiologicalProcessIdentifier = 'true';
        } else {
            exactBiologicalProcessIdentifier = 'false';
        }                
        var time;
        // Gets the date of the last update
        if ( lastUpdateDateField.getValue() !== '' ) {

            // The date of the last update to search for
            time = new Date(lastUpdateDateField.getValue());
            epochLastUpdated = (time.getTime() / 1000);
            epochLastUpdated = epochLastUpdated.toString();
            epochLastUpdated = '>' + epochLastUpdated;
        }
        // Gets the date added
        if ( dateAddedDateField.getValue() !== '' ) {
            // The date the entries were added
            time = new Date(dateAddedDateField.getValue());
            epochAdded = (time.getTime() / 1000);
            epochAdded = epochAdded.toString();
            epochAdded = '>' + epochAdded;
        }
        // The API does not distinguish between RFRC or RFTF or RFSEG for the
        // redfly parameter so different variables are needed
        var redflyIdRcUrl = '';
        var redflyIdTfUrl = '';
        var redflyIdCrmsUrl = '';        
        switch (redflyIdUrl.split(':')[0]) {
            case 'RFRC':
                redflyIdRcUrl = redflyIdUrl;
                break;
            case 'RFTF':
                redflyIdTfUrl = redflyIdUrl;
                break;
            case 'RFSEG':
                redflyIdCrmsUrl = redflyIdUrl;
                break;
            default:
        }
        if ( redflyIdRcUrl !== '' ) {
            REDfly.config.crmSearchParameters = {
                is_crm: true,
                redfly_id: redflyIdRcUrl
            };
            crmStore.load();            
            REDfly.config.rcSearchParameters = {
                redfly_id: redflyIdRcUrl
            };
            rcStore.load();
            setResultTabTitle({
                id: 'tab-tfbs',
                results: { number_of_tfbss_search_result: 0 }
            });
            setResultTabTitle({
                id: 'tab-icrm',
                results: { number_of_icrms_search_result: 0 }
            });
            setResultTabTitle({
                id: 'tab-pcrm',
                results: { number_of_pcrms_search_result: 0 }
            });
            redflyIdUrl = '';
            return true;
        } else if ( redflyIdTfUrl !== '' ) {
            REDfly.config.tfbsSearchParameters = {
                redfly_id: redflyIdTfUrl
            };
            tfbsStore.load();
            setResultTabTitle({
                id: 'tab-crm-rc',
                results: {
                    number_of_crms_search_result: 0,
                    number_of_rcs_search_result: 0
                }
            });
            setResultTabTitle({
                id: 'tab-icrm',
                results: { number_of_icrms_search_result: 0 }
            });
            setResultTabTitle({
                id: 'tab-pcrm',
                results: { number_of_pcrms_search_result: 0 }
            });
            redflyIdUrl = '';
            return true;
        }
        // If the search is from all of the RC's then search with the
        // current values
        if ( Ext.getCmp('rc-data-type-all').getValue() ) {
            REDfly.config.rcSearchParameters = {
                anatomical_expression_identifier: anatomicalExpressionIdentifier,
                assayed_in_species_id: assayedInSpeciesId,
                biological_process_identifier: biologicalProcessIdentifier,
                cell_culture_only: cellCultureOnly,
                chr_end: endCoordinate,
                chr_id: chromosomeId,
                chr_start: startCoordinate,
                date_added: epochAdded,
                developmental_stage_identifier: developmentalStageIdentifier,
                enhancer_attribute_excluded: enhancerAttributeExcluded,
                enhancer_attribute_included: enhancerAttributeIncluded,
                evidence_id: evidenceId,
                exact_anatomical_expression_identifier: exactAnatomicalExpressionIdentifier,
                exact_biological_process_identifier: exactBiologicalProcessIdentifier,
                exact_developmental_stage_identifier: exactDevelopmentalStageIdentifier,
                fbtp_identifier: fbtpIdentifier,
                five_prime: fivePrime,
                gene_id: geneId,
                has_images: rcHasImages,
                in_exon: inExon,
                in_intron: inIntron,
                is_minimalized: isMinimalized,
                is_negative: isNegative,
                include_range: includeSearchRangeInterval,
                last_update: epochLastUpdated,
                maximum_sequence_size: maximumSequenceSize,
                name: elementName,
                pubmed_id: pubmedId,
                redfly_id: redflyIdRcUrl,
                search_range: searchRangeInterval,
                sequence_from_species_id: sequenceFromSpeciesId,
                silencer_attribute_excluded: silencerAttributeExcluded,
                silencer_attribute_included: silencerAttributeIncluded,
                three_prime: threePrime
            };
            rcStore.load();
            REDfly.config.crmSearchParameters = {
                anatomical_expression_identifier: anatomicalExpressionIdentifier,
                assayed_in_species_id: assayedInSpeciesId,
                biological_process_identifier: biologicalProcessIdentifier,
                cell_culture_only: cellCultureOnly,
                chr_end: endCoordinate,
                chr_id: chromosomeId,
                chr_start: startCoordinate,
                date_added: epochAdded,
                developmental_stage_identifier: developmentalStageIdentifier,
                enhancer_attribute_excluded: enhancerAttributeExcluded,
                enhancer_attribute_included: enhancerAttributeIncluded,
                evidence_id: evidenceId,
                exact_anatomical_expression_identifier: exactAnatomicalExpressionIdentifier,
                exact_biological_process_identifier: exactBiologicalProcessIdentifier,
                exact_developmental_stage_identifier: exactDevelopmentalStageIdentifier,
                fbtp_identifier: fbtpIdentifier,
                five_prime: fivePrime,
                gene_id: geneId,
                has_images: rcHasImages,
                in_exon: inExon,
                in_intron: inIntron,
                is_crm: true,
                is_minimalized: isMinimalized,
                is_negative: isNegative,
                include_range: includeSearchRangeInterval,
                last_update: epochLastUpdated,
                maximum_sequence_size: maximumSequenceSize,
                name: elementName,
                pubmed_id: pubmedId,
                redfly_id: redflyIdRcUrl,
                search_range: searchRangeInterval,
                sequence_from_species_id: sequenceFromSpeciesId,
                silencer_attribute_excluded: silencerAttributeExcluded,
                silencer_attribute_included: silencerAttributeIncluded,
                three_prime: threePrime
            };
            crmStore.load();            
        } else {
            // Check if the RC entries are only CRM's
            if ( Ext.getCmp('rc-data-type-crm').getValue() ) {
                isCrm = 'true';
            } else {
                // Check if the RC entries must have an associated TFBS
                if ( Ext.getCmp('rc-data-type-crm-with-tfbs').getValue() ) {
                    isCrm = 'true';
                    hasTfbs = 'true';
                }
            }
            REDfly.config.rcSearchParameters = {
                anatomical_expression_identifier: anatomicalExpressionIdentifier,
                assayed_in_species_id: assayedInSpeciesId,
                biological_process_identifier: biologicalProcessIdentifier,
                cell_culture_only: cellCultureOnly,
                chr_end: endCoordinate,
                chr_id: chromosomeId,
                chr_start: startCoordinate,
                date_added: epochAdded,
                developmental_stage_identifier: developmentalStageIdentifier,
                enhancer_attribute_excluded: enhancerAttributeExcluded,
                enhancer_attribute_included: enhancerAttributeIncluded,
                evidence_id: evidenceId,
                exact_anatomical_expression_identifier: exactAnatomicalExpressionIdentifier,
                exact_biological_process_identifier: exactBiologicalProcessIdentifier,
                exact_developmental_stage_identifier: exactDevelopmentalStageIdentifier,
                fbtp_identifier: fbtpIdentifier,
                five_prime: fivePrime,
                gene_id: geneId,
                has_images: rcHasImages,
                has_tfbs: hasTfbs,
                in_exon: inExon,
                in_intron: inIntron,
                is_crm: isCrm,
                is_minimalized: isMinimalized,
                is_negative: isNegative,
                include_range: includeSearchRangeInterval,
                last_update: epochLastUpdated,
                maximum_sequence_size: maximumSequenceSize,
                name: elementName,
                pubmed_id: pubmedId,
                redfly_id: redflyIdRcUrl,
                search_range: searchRangeInterval,
                sequence_from_species_id: sequenceFromSpeciesId,
                silencer_attribute_excluded: silencerAttributeExcluded,
                silencer_attribute_included: silencerAttributeIncluded,
                three_prime: threePrime
            };
            rcStore.load();
            REDfly.config.crmSearchParameters = {
                anatomical_expression_identifier: anatomicalExpressionIdentifier,
                assayed_in_species_id: assayedInSpeciesId,
                biological_process_identifier: biologicalProcessIdentifier,
                cell_culture_only: cellCultureOnly,
                chr_end: endCoordinate,
                chr_id: chromosomeId,
                chr_start: startCoordinate,
                date_added: epochAdded,
                developmental_stage_identifier: developmentalStageIdentifier,
                enhancer_attribute_excluded: enhancerAttributeExcluded,
                enhancer_attribute_included: enhancerAttributeIncluded,
                evidence_id: evidenceId,
                exact_anatomical_expression_identifier: exactAnatomicalExpressionIdentifier,
                exact_biological_process_identifier: exactBiologicalProcessIdentifier,
                exact_developmental_stage_identifier: exactDevelopmentalStageIdentifier,
                fbtp_identifier: fbtpIdentifier,
                five_prime: fivePrime,
                gene_id: geneId,
                has_images: rcHasImages,
                has_tfbs: hasTfbs,
                in_exon: inExon,
                in_intron: inIntron,
                is_crm: true,
                is_minimalized: isMinimalized,
                is_negative: isNegative,
                include_range: includeSearchRangeInterval,
                last_update: epochLastUpdated,
                maximum_sequence_size: maximumSequenceSize,
                name: elementName,
                pubmed_id: pubmedId,
                redfly_id: redflyIdRcUrl,
                search_range: searchRangeInterval,
                sequence_from_species_id: sequenceFromSpeciesId,
                silencer_attribute_excluded: silencerAttributeExcluded,
                silencer_attribute_included: silencerAttributeIncluded,
                three_prime: threePrime
            };
            crmStore.load();            
        }
        REDfly.config.crmsegmentSearchParameters = {
            anatomical_expression_identifier: anatomicalExpressionIdentifier,
            assayed_in_species_id: assayedInSpeciesId,
            biological_process_identifier: biologicalProcessIdentifier,
            cell_culture_only: cellCultureOnly,
            chr_end: endCoordinate,
            chr_id: chromosomeId,
            chr_start: startCoordinate,
            date_added: epochAdded,
            developmental_stage_identifier: developmentalStageIdentifier,
            enhancer_attribute_excluded: enhancerAttributeExcluded,
            enhancer_attribute_included: enhancerAttributeIncluded,
            evidence_id: evidenceId,
            exact_anatomical_expression_identifier: exactAnatomicalExpressionIdentifier,
            exact_biological_process_identifier: exactBiologicalProcessIdentifier,
            exact_developmental_stage_identifier: exactDevelopmentalStageIdentifier,
            fbtp_identifier: fbtpIdentifier,
            five_prime: fivePrime,
            gene_id: geneId,
            is_minimalized: isMinimalized,
            is_negative: isNegative,            
            in_exon: inExon,
            in_intron: inIntron,
            include_range: includeSearchRangeInterval,
            last_update: epochLastUpdated,
            maximum_sequence_size: maximumSequenceSize,
            name: elementName,
            pubmed_id: pubmedId,
            redfly_id: redflyIdCrmsUrl,
            search_range: searchRangeInterval,
            sequence_from_species_id: sequenceFromSpeciesId,
            silencer_attribute_excluded: silencerAttributeExcluded,
            silencer_attribute_included: silencerAttributeIncluded,
            three_prime: threePrime
        };
        crmSegmentStore.load();
        REDfly.config.predictedCrmSearchParameters = {
            anatomical_expression_identifier: anatomicalExpressionIdentifier,
            assayed_in_species_id: assayedInSpeciesId,
            biological_process_identifier: biologicalProcessIdentifier,
            chr_end: endCoordinate,
            chr_id: chromosomeId,
            chr_start: startCoordinate,
            date_added: epochAdded,
            developmental_stage_identifier: developmentalStageIdentifier,
            enhancer_attribute_excluded: enhancerAttributeExcluded,
            enhancer_attribute_included: enhancerAttributeIncluded,
            evidence_id: evidenceId,
            exact_anatomical_expression_identifier: exactAnatomicalExpressionIdentifier,
            exact_biological_process_identifier: exactBiologicalProcessIdentifier,
            exact_developmental_stage_identifier: exactDevelopmentalStageIdentifier,
            fbtp_identifier: fbtpIdentifier,
            five_prime: fivePrime,
            gene_locus: geneLocus,
            gene_search: geneSearch,
            in_exon: inExon,
            in_intron: inIntron,
            include_range: includeSearchRangeInterval,
            last_update: epochLastUpdated,
            maximum_sequence_size: maximumSequenceSize,
            name: elementName,
            pubmed_id: pubmedId,
            search_range: searchRangeInterval,
            sequence_from_species_id: sequenceFromSpeciesId,
            silencer_attribute_excluded: silencerAttributeExcluded,
            silencer_attribute_included: silencerAttributeIncluded,
            three_prime: threePrime
        };
        predictedCrmStore.load();
        // If the search restrictions check all TFBS's then search
        if ( Ext.getCmp('tfbs-data-type-all').getValue() ) {
            REDfly.config.tfbsSearchParameters = {
                anatomical_expression_identifier: anatomicalExpressionIdentifier,
                assayed_in_species_id: assayedInSpeciesId,
                biological_process_identifier: biologicalProcessIdentifier,
                chr_end: endCoordinate,
                chr_id: chromosomeId,
                chr_start: startCoordinate,
                date_added: epochAdded,
                developmental_stage_identifier: developmentalStageIdentifier,
                evidence_id: evidenceId,
                exact_anatomical_expression_identifier: exactAnatomicalExpressionIdentifier,
                fbtp_identifier: fbtpIdentifier,
                five_prime: fivePrime,
                gene_id: geneId,
                gene_restrictions: geneRestrictions,
                has_images: tfbsHasImages,
                in_exon: inExon,
                in_intron: inIntron,
                include_range: includeSearchRangeInterval,
                last_update: epochLastUpdated,
                maximum_sequence_size: maximumSequenceSize,
                name: elementName,
                pubmed_id: pubmedId,
                redfly_id: redflyIdTfUrl,
                search_range: searchRangeInterval,
                sequence_from_species_id: sequenceFromSpeciesId,
                tf_id: tfId,
                three_prime: threePrime
            };
            tfbsStore.load();
        } else {
            // Checks if the TFBS entries must have an associated RC
            if ( Ext.getCmp('tfbs-data-type-with-crm').getValue() ) {
                hasRc = 'true';
            }
            // Otherwise run the search with the new parameters
            REDfly.config.tfbsSearchParameters = {
                anatomical_expression_identifier: anatomicalExpressionIdentifier,
                assayed_in_species_id: assayedInSpeciesId,
                biological_process_identifier: biologicalProcessIdentifier,
                chr_end: endCoordinate,
                chr_id: chromosomeId,
                chr_start: startCoordinate,
                date_added: epochAdded,
                developmental_stage_identifier: developmentalStageIdentifier,
                evidence_id: evidenceId,
                exact_anatomical_expression_identifier: exactAnatomicalExpressionIdentifier,
                fbtp_identifier: fbtpIdentifier,
                five_prime: fivePrime,
                gene_id: geneId,
                gene_restrictions: geneRestrictions,
                has_images: tfbsHasImages,
                has_rc: hasRc,
                in_exon: inExon,
                in_intron: inIntron,
                include_range: includeSearchRangeInterval,
                last_update: epochLastUpdated,
                maximum_sequence_size: maximumSequenceSize,
                name: elementName,
                pubmed_id: pubmedId,
                redfly_id: redflyIdTfUrl,
                search_range: searchRangeInterval,
                sequence_from_species_id: sequenceFromSpeciesId,
                tf_id: tfId,
                three_prime: threePrime
            }
            tfbsStore.load();
        }
        REDfly.config.inferredCrmSearchParameters = {
            anatomical_expression_identifier: anatomicalExpressionIdentifier,
            assayed_in_species_id: assayedInSpeciesId,
            biological_process_identifier: biologicalProcessIdentifier,
            chr_end: endCoordinate,
            chr_id: chromosomeId,
            chr_start: startCoordinate,
            components: elementName,
            date_added: epochAdded,
            developmental_stage_identifier: developmentalStageIdentifier,            
            evidence_id: evidenceId,
            exact_anatomical_expression_identifier: exactAnatomicalExpressionIdentifier,
            fbtp_identifier: fbtpIdentifier,
            five_prime: fivePrime,
            gene_locus: geneLocus,
            gene_search: geneSearch,
            in_exon: inExon,
            in_intron: inIntron,
            include_range: includeSearchRangeInterval,
            maximum_sequence_size: maximumSequenceSize,
            last_update: epochLastUpdated,
            pubmed_id: pubmedId,
            search_range: searchRangeInterval,
            sequence_from_species_id: sequenceFromSpeciesId,
            three_prime: threePrime
        };
        inferredCrmStore.load();
    }
    // Highlights the urlLink textfield. Is called when the textfield gains focus
    function highlightUrl()
    {
        urlLinkTextField.selectText(
            0,
            urlLinkTextField.getValue().length
        );
    }
    // Creates the url needed to bookmark a search after an user does a search.
    // Called from the search function
    function urlCreate()
    {
        var url = redflyBaseUrl + 'search.php?';
        if ( ! Ext.isEmpty(bookmarkGene) ) {
            url = url + 'gene_id=' + bookmarkGene + '&';
        }
        if ( ! Ext.isEmpty(bookmarkIncludeRange) ) {
            url = url + 'include_range=' + bookmarkIncludeRange + '&';
        }
        if ( ! Ext.isEmpty(bookmarkElementNameOrFBtpIdentifier) ) {
            url = url + 'name=' + bookmarkElementNameOrFBtpIdentifier + '&';
        }
        if ( ! Ext.isEmpty(bookmarkPubmedId) ) {
            url = url + 'pubmed_id=' + bookmarkPubmedId + '&';
        }
        if ( ! Ext.isEmpty(bookmarkSequenceFromSpeciesId) ) {
            url = url + 'sequence_from_species_id=' + bookmarkSequenceFromSpeciesId + '&';
        }
        if ( ! Ext.isEmpty(bookmarkAssayedInSpeciesId) ) {
            url = url + 'assayed_in_species_id=' + bookmarkAssayedInSpeciesId + '&';
        }
        if ( ! Ext.isEmpty(bookmarkCellCultureOnly) ) {
            url = url + 'cell_culture_only=' + bookmarkCellCultureOnly + '&';
        }
        if ( ! Ext.isEmpty(bookmarkFivePrime) ) {
            url = url + 'five_prime=' + bookmarkFivePrime + '&';
        }
        if ( ! Ext.isEmpty(bookmarkThreePrime) ) {
            url = url + 'three_prime=' + bookmarkThreePrime + '&';
        }
        if ( ! Ext.isEmpty(bookmarkInIntron) ) {
            url = url + 'in_intron=' + bookmarkInIntron + '&';
        }
        if ( ! Ext.isEmpty(bookmarkInExon) ) {
            url = url + 'in_intron=' + bookmarkInExon + '&';
        }
        if ( ! Ext.isEmpty(bookmarkChromosomeId) ) {
            url = url + 'chr_id=' + bookmarkChromosomeId + '&';
        }
        if ( ! Ext.isEmpty(bookmarkStartCoordinate) ) {
            url = url + 'start=' + bookmarkStartCoordinate + '&';
        }
        if ( ! Ext.isEmpty(bookmarkEndCoordinate) ) {
            url = url + 'end=' + bookmarkEndCoordinate + '&';
        }
        if ( ! Ext.isEmpty(bookmarkMaxiumSequenceSize) ) {
            url = url + 'maximum_sequence_size=' + bookmarkMaxiumSequenceSize + '&';
        }
        if ( ! Ext.isEmpty(bookmarkSearchRangeInterval) ) {
            url = url + 'search_range=' + bookmarkSearchRangeInterval + '&';
        }
        if ( ! Ext.isEmpty(bookmarkEvidence) ) {
            url = url + 'evidence_id=' + bookmarkEvidence + '&';
        }
        if ( ! Ext.isEmpty(bookmarkAnatomicalExpressionIdentifier) ) {
            url = url + 'anatomical_expression_identifier=' + bookmarkAnatomicalExpressionIdentifier + '&';
        }
        if ( ! Ext.isEmpty(bookmarkExactAnatomicalExpressionIdentifier) ) {
            url = url + 'exact_anatomical_expression_identifier=' + bookmarkExactAnatomicalExpressionIdentifier + '&';
        }
        if ( ! Ext.isEmpty(bookmarkDevelopmentalStageIdentifier) ) {
            url = url + 'developmental_stage_identifier=' + bookmarkDevelopmentalStageIdentifier + '&';
        }
        if ( ! Ext.isEmpty(bookmarkExactDevelopmentalStageIdentifier) ) {
            url = url + 'exact_developmental_stage_identifier=' + bookmarkExactDevelopmentalStageIdentifier + '&';
        }
        if ( ! Ext.isEmpty(bookmarkBiologicalProcessIdentifier) ) {
            url = url + 'biological_process_identifier=' + bookmarkBiologicalProcessIdentifier + '&';
        }
        if ( ! Ext.isEmpty(bookmarkExactBiologicalProcessIdentifier) ) {
            url = url + 'exact_biological_process_identifier=' + bookmarkExactBiologicalProcessIdentifier + '&';
        }        
        if ( ! Ext.isEmpty(bookmarkLastUpdate) ) {
            url = url + 'last_update=' + bookmarkLastUpdate + '&';
        }
        if ( ! Ext.isEmpty(bookmarkDateAdded) ) {
            url = url + 'date_added=' + bookmarkDateAdded + '&';
        }
        var bookmarkCrmOptions = [];
        var bookmarkTfbsOptions = [];
        var bookmarkTfbsRestrictions = [];
        Ext.each(rcOptionIds, function (id, i) {
            bookmarkCrmOptions[i] = Ext.getCmp(id).getValue();
        });
        Ext.each(tfbsOptionIds, function (id, i) {
            bookmarkTfbsOptions[i] = Ext.getCmp(id).getValue();
        });
        Ext.each(tfbsRestrictionsIds, function (id, i) {
            bookmarkTfbsRestrictions[i] = Ext.getCmp(id).getValue();
        });
        url = url + 'crmOptions=' + bookmarkCrmOptions +
            '&tfbsOptions=' + bookmarkTfbsOptions +
            '&tfbs-restrictions=' + bookmarkTfbsRestrictions;
        return url;
    }
    // Searches the url query string for the input value and returns
    // the data that it stores
    function searchQueryString(input)
    {
        var value = searchQueryStringForKey(input);
        if ( value === null ) { return null; }
        var segments = value.split('%20');
        var string = '';
        for ( i = 0; i < segments.length; i++ ) {
            if ( string === '' ) {
                string = segments[i];
            } else {
                string = string + ' ' + segments[i];
            }
        }
        return string;
    }
    function searchQueryStringForKey(input)
    {
        var query = window.location.search.substring(1);
        var segment = query.split('&');
        var words;
        for ( i = 0; i < segment.length; i++ ) {
            words = segment[i].split('=');
            if ( words[0] === input ) {
                return words[1];
            }
        }
        return null;
    }
    // Clears the number of results next to each tab title in the
    // search panel. Called on the listener 'beforeTabChange' in
    // resultsTabPanel
    function tabChange(panel, newTab, oldTab)
    {
        // When the panel initially sets the active tab there will be no previous tab.
        if ( oldTab === undefined  ) return;
        newTabId = newTab.getId();
        // We do not currently support viewing and downloading of iCRMs
        if ( newTabId === 'tab-icrm' ) {
            viewSelectedBtn.hide();
            downloadSelectedBtn.hide();
        } else {
            viewSelectedBtn.show();
            downloadSelectedBtn.show();
        }
        // Update BED track name and description based on tab change
        if ( (newTabId === 'tab-crm-rc') &&
            Ext.getCmp('bedTrackNameValue')) {
            Ext.getCmp('bedTrackNameValue').setValue('CRM');
            Ext.getCmp('bedTrackDescriptionValue').setValue('CRMs selected from REDfly');
        }
        if ( (newTabId === 'tab-tfbs') &&
            Ext.getCmp('bedTrackNameValue')) {
            Ext.getCmp('bedTrackNameValue').setValue('TBFS');
            Ext.getCmp('bedTrackDescriptionValue').setValue('TBFS selected from REDfly');
        }
        setResultTabTitle({
            id: 'tab-crm-rc',
            selected: (newTabId === 'tab-crm-rc'),
            results: {
                number_of_crms_search_result: crmStore.getTotalCount(),
                number_of_rcs_search_result: rcStore.getTotalCount(),
                number_of_database_crms: databaseInformationStore.getAt(0).get('number_crms', 0),
                number_of_database_rcs: databaseInformationStore.getAt(0).get('number_rcs', 0)
            }
        });
        setResultTabTitle({
            id: 'tab-pcrm',
            selected: ( newTabId === 'tab-pcrm' ),
            results: { number_of_pcrms_search_result: predictedCrmStore.getTotalCount() }
        });
        setResultTabTitle({
            id: 'tab-tfbs',
            selected: ( newTabId === 'tab-tfbs' ),
            results: { number_of_tfbss_search_result: tfbsStore.getTotalCount() }
        });
        setResultTabTitle({
            id: 'tab-icrm',
            selected: ( newTabId === 'tab-icrm' ),
            results: { number_of_icrms_search_result: inferredCrmStore.getTotalCount() }
        });
    }
    function geneKeyPause(textField, notUsed)
    {
        clearTimeout(timeout);
        timeout = setTimeout(
            function () {
                if ( geneComboBox.getValue() === '' ) {
                    geneStore.load({
                        params: { 
                            limit: 0,
                            species_id: sequenceFromSpeciesComboBox.getValue()
                        }
                    });
                } else {
                    // The Aedes aegypti species
                    var aaelGeneValue = geneComboBox.getValue().split('AAEL')[1];
                    // The Anopheles gambiae species
                    var agapGeneValue = geneComboBox.getValue().split('AGAP')[1];
                    // The Drosophila melanogaster species
                    var fbgnGeneValue = geneComboBox.getValue().split('FBgn')[1];
                    // The Tribolium castaneum species
                    var tcGeneValue = geneComboBox.getValue().split('TC')[1];
                    if ( (aaelGeneValue !== undefined) ||
                        (agapGeneValue !== undefined) ||
                        (fbgnGeneValue !== undefined) ||
                        (tcGeneValue !== undefined) ) {
                        geneStore.load({
                            callback: function () {
                                if ( geneStore.getTotalCount() !== 0 ) {
                                    geneDisplayArray = [];
                                    for ( var i = 0; i < geneStore.getTotalCount(); i++ ) {
                                        geneDisplayArray[i] = [
                                            // record
                                            geneStore.getAt(i),
                                            // display
                                            geneStore.getAt(i).get('display')
                                        ];
                                    }
                                    geneDisplayStore.loadData(geneDisplayArray);
                                }
                            },
                            params: { 
                                identifier: geneComboBox.getValue() + '*',
                                species_id: sequenceFromSpeciesComboBox.getValue()
                            }
                        });
                    } else {
                        geneStore.load({
                            callback: function () {
                                if ( geneStore.getTotalCount() !== 0 ) {
                                    geneDisplayArray = [];
                                    for ( var i = 0; i < geneStore.getTotalCount(); i++ ) {
                                        geneDisplayArray[i] = [
                                            // record
                                            geneStore.getAt(i),
                                            // display
                                            geneStore.getAt(i).get('display')
                                        ];
                                    }
                                    geneDisplayStore.loadData(geneDisplayArray);
                                }
                            },
                            params: {
                                name: geneComboBox.getValue() + '*',
                                species_id: sequenceFromSpeciesComboBox.getValue()
                            }
                        });
                    }
                }
            },
            500
        );
    }
    function changeGeneValue(combobox, record, number)
    {
        geneComboBox.setValue(record.get('record').get('name'));
    }
    function selectChromosome()
    {
        if ( (chromosomeComboBox.getValue() !== 'Any') &&
            (chromosomeComboBox.getValue() !== '') ) {
            chromosomeComboBox.setValue(chromosomeComboBox.getStore().getAt(chromosomeComboBox.getStore().find('id', chromosomeComboBox.getValue())).get('name'));
        }
        chromosomeStore.removeListener('load', selectChromosome);
    }
    function selectEvidence()
    {
        if ( (evidenceComboBox.getValue() !== 'Select Evidence...') &&
            (evidenceComboBox.getValue() !== '') ) {
            evidenceComboBox.setValue(evidenceComboBox.getStore().getAt(evidenceComboBox.getStore().find('id', evidenceComboBox.getValue())).get('term'));
        }
        evidenceStore.removeListener('load', selectEvidence);
    }
    function uncheck(
        checkbox,
        checked
    ) {
        if ( ! checked ) { return; }
        if ( checkbox.getName() === 'anatomical-expression-positive' ) {
            Ext.getCmp('rc-restrictions').setValue('anatomical-expression-negative', false);
        } else if ( checkbox.getName() === 'anatomical-expression-negative' ) {
            Ext.getCmp('rc-restrictions').setValue('anatomical-expression-positive', false);
        }
        if ( checkbox.getName() === 'include-enhancer' ) {
            Ext.getCmp('rc-restrictions').setValue('exclude-enhancer', false);
        } else if ( checkbox.getName() === 'exclude-enhancer' ) {
            Ext.getCmp('rc-restrictions').setValue('include-enhancer', false);
        }
        if ( checkbox.getName() === 'include-silencer' ) {
            Ext.getCmp('rc-restrictions').setValue('exclude-silencer', false);
        } else if ( checkbox.getName() === 'exclude-silencer' ) {
            Ext.getCmp('rc-restrictions').setValue('include-silencer', false);
        }        
    }
    // Automatically fills in the gene field with the most valid entry
    // if the user does not enter an entire value
    function autoFill()
    {
        if ( geneComboBox.getValue() !== '' ) {
            // The Anopheles gambiae species            
            var aaelGeneValue = geneComboBox.getValue().split('AAEL')[1];
            // The Anopheles gambiae species
            var agapGeneValue = geneComboBox.getValue().split('AGAP')[1];
            // The Drosophila melanogaster species
            var fbgnGeneValue = geneComboBox.getValue().split('FBgn')[1];
            // The Tribolium castaneum species
            var tcGeneValue = geneComboBox.getValue().split('TC')[1];
            if ( (aaelGeneValue !== undefined) ||
                (agapGeneValue !== undefined) ||
                (fbgnGeneValue !== undefined) ||
                (tcGeneValue !== undefined) ) {
                if ( (geneStore.getTotalCount() !== 0) &&
                    (geneComboBox.getValue() !== geneStore.getAt(
                        geneStore.find('identifier',
                        geneComboBox.getValue()
                    )).get('name')) ) {
                    geneComboBox.setValue(geneStore.getAt(0).get('name'));
                }
            } else {
                if ( (geneStore.getTotalCount() !== 0) &&
                    (geneStore.findExact(
                        'name',
                        geneComboBox.getValue()
                    ) < 0) ) {
                    geneComboBox.setValue(geneStore.getAt(0).get('name'));
                }
            }
        }
    }
    // Checks if the user presses the ENTER key while the input focus is on a search field and
    // executes a search
    function checkEnter(field, e)
    {
        if ( e.getKey() === e.ENTER ) {
            if ( field != geneComboBox ) {
                searchDatabase();
            } else {
                autoFill();
                searchDatabase();
            }
        }
    }
    // Set the title of a result tab based on the defined configuration.
    //Parameters:
    //  options: Object containing options for setting the tab title.
    //  loading: true if the tab is loading the results
    //  reset: true if the title should be reset to the default
    //  results: an object containing the number of results
    //  selected: true if the tab has been selected
    function setResultTabTitle(options) {
        // Start with the current title and override if necessary.
        var title = resultsTabPanel.findById(options.id).title;
        if ( options.loading ) title = REDfly.config.resultTabDefaults[options.id].loadingTitle;
        else if ( options.reset ) title = REDfly.config.resultTabDefaults[options.id].title;
        else if ( options.results ) title = REDfly.config.resultTabDefaults[options.id].results.apply(options.results);
        // If a tab is currently selected, bold the title.
        if ( options.selected ) title = '<b>' + title + '</b>';
        resultsTabPanel.findById(options.id).setTitle(title);
    }

     //Required for tooltips to work
    Ext.QuickTips.init();
});
