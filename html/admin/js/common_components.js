// --------------------------------------------------------------------------------
// A few global state variables.
// Those are shared among the dialogs, so needs to be placed here.
// --------------------------------------------------------------------------------
REDfly.component.approvalState = false;
REDfly.component.notifyAuthor = false;
// --------------------------------------------------------------------------------
// The displayUser component displays the full name of a curator/auditor along 
// with the time order words and a formatted date as an user-immutable display
// field. The date information is only displayed if an user has been provided.
// --------------------------------------------------------------------------------
REDfly.component.displayUser = Ext.extend(Ext.form.CompositeField, {
    fullName: '',
    timeOrderWords: '',
    formattedDate: '',
    // --------------------------------------------------------------------------------
    // Update the full name
    // @param fullName The new full name to be displayed
    // --------------------------------------------------------------------------------
    setFullName: function(fullName) {
        this.fullName = fullName;
    },
    // --------------------------------------------------------------------------------
    // Update the time order words
    // @param timeOrderWords The new time order words to be displayed
    // --------------------------------------------------------------------------------
    setTimeOrderWords: function(timeOrderWords) {
        this.timeOrderWords = timeOrderWords;
    },
    // --------------------------------------------------------------------------------
    // Update the formatted date
    // @param formattedDate The new formatted date to be displayed
    // --------------------------------------------------------------------------------
    setFormattedDate: function(formattedDate) {
        this.formattedDate = formattedDate;
    },
    // --------------------------------------------------------------------------------
    // Update all the values at once
    // @param options An object containing the new full name, time order words, and
    // formatted date
    // --------------------------------------------------------------------------------
    update: function(options) {
        if ( options.fullName ) {
            this.setFullName(options.fullName);
        }
        if ( options.timeOrderWords ) {
            this.setTimeOrderWords(options.timeOrderWords);
        }
        if ( options.formattedDate ) {
            this.setFormattedDate(options.formattedDate);
        }
        this.items.get(0).setValue(Ext.isEmpty(this.fullName)
            ? 'N/A'
            : this.fullName );
        this.items.get(1).setValue(Ext.isEmpty(this.formattedDate) 
            ? ''
            : '(' + this.timeOrderWords + ' ' + this.formattedDate + ')'
        );
        this.doLayout();
    },
    // --------------------------------------------------------------------------------
    // Reset the values to default
    // --------------------------------------------------------------------------------
    reset: function() {
        this.fullName = '';
        this.timeOrderWords = '';
        this.formattedDate = '';
        this.items.get(0).reset();
        this.items.get(1).reset();
    },
    initComponent: function() {
        var items = [
            // The full name of the user
            {
                value: this.fullName,
                xtype: 'displayfield'
            },
            // The date information which the user made an action on
            {
                xtype: 'displayfield'
            }
        ];
        Ext.apply(this, {
            items: items
        });
        REDfly.component.displayUser.superclass.initComponent.apply(this, arguments);
    }
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select an anatomical expression.
// --------------------------------------------------------------------------------
REDfly.component.selectAnatomicalExpressionTerm = Ext.extend(Ext.form.ComboBox, {
    displayField: 'display',
    fieldLabel: 'Anatomical Expression Terms',
    listeners: {
        beforequery: function(queryEvent) {
            // The queryEvent object has 4 properties:
            // 'combo', 'query', 'forceAll', and 'cancel'.
            // Set the appropriate parameter for the store based on what the
            // user typed into the combo box.
            var paramObj = {};
            // No longer necessary since the Drosophila ontology has to be accessible
            // for all species annotation according to the PI
            //if ( this.assayedInSpeciesField !== null ) {
            //    paramObj.species_id = this.assayedInSpeciesField.id;
            //}
            if (// Both Aedes aegypti and Anopheles gambiae species
                // to be targeted for the anatomical expressions list 
                (queryEvent.query.substr(0, 4) === 'TGMA') ||
                // The Drosophila melanogaster species 
                // to be targeted for the anatomical expressions list
                (queryEvent.query.substr(0, 4) === 'FBbt') ||
                // The Tribolium castaneum species
                // to be targeted for the anatomical expressions list
                (queryEvent.query.substr(0, 4) === 'TrOn')) {
                paramObj.identifier = queryEvent.query + '*';
            } else {
                paramObj.term = '*' + queryEvent.query + '*';
            }
            //console.log(paramObj);
            queryEvent.combo.getStore().load({ params: paramObj });
            // Cancel the load, otherwise the combobox will try and
            // load the data again.
            queryEvent.cancel = true;
        }
    },
    name: 'anatomical_expression_id',
    store: new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoLoad: false,
        baseParams: {
            sort: 'name'
        },
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in anatomicalExpression Store');
                }
            },        
            url: REDfly.config.apiUrl + '/jsonstore/anatomicalexpression/list'
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
        messageProperty: 'message',
        root: 'results',
        totalProperty: 'num'
    }),
    tpl: '<tpl for="."><div ext:qtip="{display}" class="x-combo-list-item">{display}</div></tpl>',
    triggerAction: 'query',
    valueField: 'id',
    width: 200,
    // REDfly configs
    // This must be set in order to access the species field in the dialog
    assayedInSpeciesField: null
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select a species for the data related to the 
// anatomical expression(s).
// --------------------------------------------------------------------------------
REDfly.component.selectAssayedInSpecies = Ext.extend(Ext.form.ComboBox, {
    displayField: 'scientific_name',
    editable: false,
    fieldLabel: '"Assayed In" Species',
    listeners: {
        change: function(combo, newValue, oldValue) {
            if ( (oldValue === '') &&
                (newValue !== '') )  {
                this.curateAnatomicalExpressionTermsField.enable();
            }
        },
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
        baseParams: {
            sort: 'scientific_name'
        },        
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in selectSpecies Store');
                }
            },
            url: REDfly.config.apiUrl + '/jsonstore/species/list'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
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
    // REDfly configs
    id: null,
    scientific_name: null,
    short_name: null,
    // Form fields dependent on the species chosen by the user
    curateAnatomicalExpressionTermsField: null  
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select a biological process.
// --------------------------------------------------------------------------------
REDfly.component.selectBiologicalProcess = Ext.extend(Ext.form.ComboBox, {
    displayField: 'display',
    fieldLabel: 'Biological Process',
    listeners: {
        beforequery: function(queryEvent) {
            // The queryEvent object has 4 properties:
            // 'combo', 'query', 'forceAll', and 'cancel'.            
            // Set the appropriate parameter for the store based on what the
            // user typed into the combo box.
            var paramObj = {};
            if ( queryEvent.query.substr(0, 2) === 'GO' ) {
                paramObj.go_id = queryEvent.query + '*';
            } else {
                paramObj.term = '*' + queryEvent.query + '*';
            }
            //console.log(paramObj);
            queryEvent.combo.getStore().load({ params: paramObj });
            // Cancel the load, otherwise the combobox will try and
            // load the data again.
            queryEvent.cancel = true;
        }
    },
    name: 'biological_process_id',
    store: new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoLoad: false,
        baseParams: {
            sort: 'term'
        },
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in selectBiologicalProcess Store');
                }
            },
            url: REDfly.config.apiUrl + '/jsonstore/biologicalprocess/list'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'display',
            'id',
            'identifier',
            'term'
        ],
        idProperty: 'id',
        messageProperty: 'message',
        root: 'results',
        totalProperty: 'num'
    }),
    tpl: '<tpl for="."><div ext:qtip="{display}" class="x-combo-list-item">{display}</div></tpl>',
    triggerAction: 'query',
    valueField: 'id',
    width: 'auto'
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select a chromosome.
// --------------------------------------------------------------------------------
REDfly.component.selectChromosome = Ext.extend(Ext.form.ComboBox, {
    displayField: 'display',
    fieldLabel: 'Chromosome',
    // User must select an item or clears the box
    forceSelection: true,    
    listeners: {
        beforequery: function(queryEvent) {
            // The queryEvent object has 4 properties:
            // 'combo', 'query', 'forceAll', and 'cancel'.            
            // Set the appropriate parameter for the store based on what the
            // user typed into the combo box.
            var paramObj = {};
            if ( this.sequenceFromSpeciesField.id !== null ) {
                if ( -1 < this.sequenceFromSpeciesField.id.search(/^\d+$/g) ) {
                    paramObj.species_id = this.sequenceFromSpeciesField.id;
                }
            }
            if ( queryEvent.query !== '' ) {
                paramObj.name = '*' + queryEvent.query + '*';
            }
            //console.log(paramObj);
            queryEvent.combo.getStore().load({ params: paramObj });
            // Cancel the load, otherwise the combobox will try and
            // load the data again.
            queryEvent.cancel = true;
        }
    },
    name: 'chromosome_id',
    tpl: '<tpl for="."><div ext:qtip="{display}" class="x-combo-list-item">{display}</div></tpl>',
    triggerAction: 'query',
    valueField: 'id',
    width: 'auto',
        // REDfly configs
    // This must be set in order to access the species field in the dialog
    sequenceFromSpeciesField: null,
    initComponent: function() {
        var chromosomeStore = new Ext.data.JsonStore({
            // Ext.data.JsonStore configs
            autoLoad: true,
            baseParams: {
                sort: 'species_short_name,name'
            },
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                listeners: {
                    exception: function() {
                        //console.log('Error in coordinates Store');
                    }
                },
                url: REDfly.config.apiUrl + '/jsonstore/chromosome/list'
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
            root: 'results',
            totalProperty: 'num'
        });
        Ext.apply(this, {
            store: chromosomeStore
        });
        REDfly.component.selectChromosome.superclass.initComponent.apply(this, arguments);
    }
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select a chromosome name as well as two text fields
// as both start and end coordinates.
// It is only used to receive the coordinates match from the BLAT server.
// So it can not be editable by the curator when creating a new entity record or
// editing an existing entity record
// --------------------------------------------------------------------------------
REDfly.component.selectCoordinates = Ext.extend(Ext.form.CompositeField, {
    fieldLabel: 'Coordinates',
    msgTarget: 'under',
    store: new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoLoad: true,
        baseParams: {
            sort: 'species_short_name,name'
        },        
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in coordinates Store');
                }
            },
            url: REDfly.config.apiUrl + '/jsonstore/chromosome/list'
        }),        
        // Embedded Ext.data.JsonReader configs
        fields: [
            'display',
            'id',
            'length',
            'name',
            'species_short_name'
        ],
        idProperty: 'id',
        messageProperty: 'message',
        root: 'results',
        totalProperty: 'num'
    }),
    // REDfly configs
    chromosome: null,
    start: null,
    end: null,
    getChromosome: function() {
        return this.chromosome.getValue();
    },
    setChromosome: function(val) {
        this.chromosome.setRawValue(val);
    },
    getStart: function() {
        return this.start.getValue();
    },
    setStart: function(val) {
        this.start.setValue(val);
    },
    getEnd: function() {
        return this.end.getValue();
    },
    setEnd: function(val) {
        this.end.setValue(val);
    },
    reset: function() {
        this.chromosome.reset();
        this.start.reset();
        this.end.reset();
    },
    initComponent: function() {
        var chromosome = new Ext.form.ComboBox({
            autoCreate: {
                maxLength: 3,
                size: 3,
                tag: 'input'
            },
            displayField: 'display',
            store: this.store,
            triggerAction: 'all',
            valueField: 'id',
            width: 140
        });
        var start = new Ext.form.TextField({
            allowBlank: true,
            name: 'Start',
            regex: /^[0-9]+$/,
            regexText: 'Non-numeric start coordinate'.replace(' ', '&nbsp;'),
            width: 75
        });
        var end = new Ext.form.TextField({
            allowBlank: true,
            name: 'End',
            regex: /^[0-9]+$/,
            regexText: 'Non-numeric end coordinate'.replace(' ', '&nbsp;'),
            width: 75
        });
        var items = [
            chromosome,
            {
                value: ':',
                xtype: 'displayfield'
            },
            start,
            {
                value: '..',
                xtype: 'displayfield'                
            },
            end
        ];
        // Apply any configuration to the current window
        Ext.apply(this, {
            items: items,
            chromosome: chromosome,
            start: start,
            end: end
        });
        REDfly.component.selectCoordinates.superclass.initComponent.apply(this, arguments);
    }
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select a curator.
// --------------------------------------------------------------------------------
REDfly.component.selectCurator = new Ext.form.ComboBox({
    displayField: 'full_name',
    editable: false,
    fieldLabel: 'Curator',
    id: 'curator_id',
    name: 'curator',
    store: new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoLoad: true,
        baseParams: {
            sort: 'full_name'
        },
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in curator Store');
                }
            },
            url: REDfly.config.apiUrl + '/jsonstore/curator/list'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'id',
            'full_name'
        ],
        idProperty: 'id',
        messageProperty: 'message',
        root: 'results',
        totalProperty: 'num'
    }),
    triggerAction: 'all',
    valueField: 'id'
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select an off developmental stage.
// --------------------------------------------------------------------------------
REDfly.component.selectDevelopmentalStageOff = Ext.extend(Ext.form.ComboBox, {
    allowBlank: false,
    displayField: 'display',
    fieldLabel: 'Stage Off',
    listeners: {
        beforequery: function(queryEvent) {
            // The queryEvent object has 4 properties:
            // 'combo', 'query', 'forceAll', and 'cancel'.            
            // Set the appropriate parameter for the store based on what the
            // user typed into the combo box.
            var paramObj = {};
            // No longer necessary since the Drosophila ontology has to be accessible
            // for all species annotation according to the PI
            //paramObj.species_id = this.assayed_in_species_id;
            if ( queryEvent.query.substr(0, 4) === 'FBdv' ) {
                paramObj.identifier = queryEvent.query + '*';
            } else {
                paramObj.term = '*' + queryEvent.query + '*';
            }
            //console.log(paramObj);
            queryEvent.combo.getStore().load({ params: paramObj });
            // Cancel the load, otherwise the combobox will try and
            // load the data again.
            queryEvent.cancel = true;
        }
    },
    name: 'developmental_stage_off_id',
    store: new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoLoad: false,
        baseParams: {
            sort: 'term'
        },
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in selectDevelopmentalStageOff Store');
                }
            },
            url: REDfly.config.apiUrl + '/jsonstore/developmentalstage/list'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'display',
            'id',
            'identifier',
            'species_id',
            'term'
        ],
        idProperty: 'id',
        messageProperty: 'message',
        root: 'results',
        totalProperty: 'num'        
    }),
    tpl: '<tpl for="."><div ext:qtip="{display}" class="x-combo-list-item">{display}</div></tpl>',
    triggerAction: 'query',
    valueField: 'id',
    width: 'auto',
    // REDfly configs
    // This must be set in order to access the species id in the dialog
    assayed_in_species_id: null
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select an on developmental stage.
// --------------------------------------------------------------------------------
REDfly.component.selectDevelopmentalStageOn = Ext.extend(Ext.form.ComboBox, {
    allowBlank: false,
    displayField: 'display',
    fieldLabel: 'Stage On',
    listeners: {
        beforequery: function(queryEvent) {
            // The queryEvent object has 4 properties:
            // 'combo', 'query', 'forceAll', and 'cancel'.
            // Set the appropriate parameter for the store based on what the
            // user typed into the combo box.
            var paramObj = {};
            // No longer necessary since the Drosophila ontology has to be accessible
            // for all species annotation according to the PI
            //paramObj.species_id = this.assayed_in_species_id;
            if ( queryEvent.query.substr(0, 4) === 'FBdv' ) {
                paramObj.identifier = queryEvent.query + '*';
            } else {
                paramObj.term = '*' + queryEvent.query + '*';
            }
            //console.log(paramObj);
            queryEvent.combo.getStore().load({ params: paramObj });
            // Cancel the load, otherwise the combobox will try and
            // load the data again.
            queryEvent.cancel = true;
        }
    },
    name: 'developmental_stage_on_id',
    store: new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoLoad: false,
        baseParams: {
            sort: 'term'
        },
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in selectDevelopmentalStageOn Store');
                }
            },            
            url: REDfly.config.apiUrl + '/jsonstore/developmentalstage/list'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'display',
            'id',
            'identifier',
            'species_id',
            'term'
        ],
        idProperty: 'id',
        messageProperty: 'message',
        root: 'results',
        totalProperty: 'num'
    }),
    tpl: '<tpl for="."><div ext:qtip="{display}" class="x-combo-list-item">{display}</div></tpl>',
    triggerAction: 'query',
    valueField: 'id',
    width: 'auto',
    // REDfly configs
    // This must be set in order to access the species id in the dialog
    assayed_in_species_id: null
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select an ectopic value.
// --------------------------------------------------------------------------------
REDfly.component.selectEctopic = Ext.extend(Ext.form.ComboBox, {
    allowBlank: false,
    displayField: 'term',
    editable: false,
    fieldLabel: 'Ectopic',
    mode: 'local',
    store: new Ext.data.ArrayStore({
        // Configs
        data: [['0', 'False'],
               ['1', 'True']],
        // Properties
        fields: ['id', 'term' ],
        // Embedded Ext.data.ArrayReader configs
        id: 0
    }),
    triggerAction: 'all',
    valueField: 'id'
});
// --------------------------------------------------------------------------------
// Create a textfield widget to enter an end coordinate.
// --------------------------------------------------------------------------------
REDfly.component.selectEndCoordinate = Ext.extend(Ext.form.TextField, {
    allowBlank: true,
    name: 'End',
    regex: /^[0-9]+$/,
    regexText: 'Non-numeric end coordinate'.replace(' ', '&nbsp;'),
    width: 75    
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select the enhancer or silencer attribute.
// --------------------------------------------------------------------------------
REDfly.component.selectEnhancerOrSilencerAttribute = Ext.extend(Ext.form.ComboBox, {
    allowBlank: true,
    displayField: 'term',
    editable: false,
    fieldLabel: 'Enhancer/Silencer',
    mode: 'local',
    store: new Ext.data.ArrayStore({
        // Configs
        data: [['enhancer', 'Enhancer'],
               ['silencer', 'Silencer']],
        // Properties
        fields: ['id', 'term' ],
        // Embedded Ext.data.ArrayReader configs
        id: 0
    }),
    triggerAction: 'all',
    valueField: 'id'
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select an evidence term.
// --------------------------------------------------------------------------------
REDfly.component.selectEvidence = Ext.extend(Ext.form.ComboBox, {
    allowBlank: false,
    displayField: 'term',
    fieldLabel: 'Evidence',
    listeners: {
        beforequery: function(queryEvent) {
            // The queryEvent object has 4 properties:
            // 'combo', 'query', 'forceAll', and 'cancel'.
            // Set the appropriate parameter for the store based on what the
            // user typed into the combo box.
            var paramObj = {};
            paramObj.name = '*' + queryEvent.query + '*';
            //console.log(paramObj);
            queryEvent.combo.getStore().load({ params: paramObj });
            // Cancel the load, otherwise the combobox will try and
            // load the data again.
            queryEvent.cancel = true;
        }
    },    
    name: 'evidence_id',
    store: new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoLoad: true,
        baseParams: {
            sort: 'name'
        },
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in selectEvidence Store');
                }
            },
            url: REDfly.config.apiUrl + '/jsonstore/evidence/list'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'id',
            'term'
        ],
        idProperty: 'id',
        messageProperty: 'message',
        root: 'results',
        totalProperty: 'num'
    }),
    triggerAction: 'all',
    valueField: 'id'
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select an evidence subtype term.
// --------------------------------------------------------------------------------
REDfly.component.selectEvidenceSubtype = Ext.extend(Ext.form.ComboBox, {
    allowBlank: false,
    displayField: 'term',
    fieldLabel: 'Evidence Subtype',
    listeners: {
        beforequery: function(queryEvent) {
            // The queryEvent object has 4 properties:
            // 'combo', 'query', 'forceAll', and 'cancel'.
            // Set the appropriate parameter for the store based on what the
            // user typed into the combo box.
            var paramObj = {};
            paramObj.name = '*' + queryEvent.query + '*';
            //console.log(paramObj);
            queryEvent.combo.getStore().load({ params: paramObj });
            // Cancel the load, otherwise the combobox will try and
            // load the data again.
            queryEvent.cancel = true;
        }
    },
    name: 'evidence_subtype_id',
    store: new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoLoad: true,
        baseParams: {
            sort: 'name'
        },
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in selectEvidenceSubtype Store');
                }
            },
            url: REDfly.config.apiUrl + '/jsonstore/evidencesubtype/list'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'id',
            'term'
        ],
        idProperty: 'id',
        messageProperty: 'message',
        root: 'results',
        totalProperty: 'num'
    }),
    triggerAction: 'all',
    valueField: 'id'
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select a gene.
// Normally, the combo box will pass a query parameter called 'query' to the store
// with whatever value that the user typed to filter the results.
// Since the user can type the gene name (free-form text) or an identifier (string
// starting with 'AAEL' or 'AGAP' or 'FBgn' or 'TC' depending on the species chosen
// by the curator) we need to handle that in the beforequery listener and pass the
// correct paramters to the load method of the store.
// Note that if we need to access portions of the data record of the store
// associated with a combobox entry we can get to it by:
// var cmp;
// var data = cmp.getStore().getById(cmp.getValue()).data.term;
// --------------------------------------------------------------------------------
REDfly.component.selectGene = Ext.extend(Ext.form.ComboBox, {
    displayField: 'display',
    fieldLabel: 'Gene (not applied to predicted CRMs)',
    // User must select an item or clears the box
    forceSelection: true,
    listeners: {
        beforequery: function(queryEvent) {
            // The queryEvent object has 4 properties:
            // 'combo', 'query', 'forceAll', and 'cancel'.
            // Set the appropriate parameter for the store based on what the
            // user typed into the combo box.
            var paramObj = {};
            if ( this.sequenceFromSpeciesField.id !== null ) {
                if ( -1 < this.sequenceFromSpeciesField.id.search(/^\d+$/g) ) {
                    paramObj.species_id = this.sequenceFromSpeciesField.id;
                }
            }            
            if (// The Aedes aegypti species 
                // to be targeted for the genes list
                (queryEvent.query.substr(0, 4) === 'AAEL') ||
                // The Anopheles gambiae species
                // to be targeted for the genes list
                (queryEvent.query.substr(0, 4) === 'AGAP') ||
                // The Drosophila melanogaster species
                // to be targeted for the genes list
                (queryEvent.query.substr(0, 4) === 'FBgn') ||
                // The Tribolium castaneum species
                // to be targeted for the genes list
                (queryEvent.query.substr(0, 2) === 'TC')) {
                paramObj.identifier = queryEvent.query + '*';
            } else {
                paramObj.name = '*' + queryEvent.query + '*';
            }
            //console.log(paramObj);
            queryEvent.combo.getStore().load({ params: paramObj });
            // Cancel the load, otherwise the combobox will try and
            // load the data again.
            queryEvent.cancel = true;
        }
    },
    name: 'gene_id',
    // The Gene JsonStore is used to load genes from the REST service. The 'load'
    // listener is used to transform the data return from the store to display the
    // name and identifier together after the data is loaded.
    queryDelay: 750,
    // Set the trigger action to 'query' so that we can set the raw value when
    // the CRUD window is loaded and only that value will be shown in the
    // dropdown.
    triggerAction: 'query',
    valueField: 'id',
    width: 250,
    // REDfly configs
    // This must be set in order to access the species field in the dialog
    sequenceFromSpeciesField: null,
    initComponent: function() {
        var geneStore = new Ext.data.JsonStore({
            // Ext.data.JsonStore configs
            autoLoad: false,
            baseParams: {
                sort: 'name'
            },
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                listeners: {
                    exception: function() {
                        //console.log('Error in geneSearch Store');
                    }
                },
                url: REDfly.config.apiUrl + '/jsonstore/gene/list'
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
            messageProperty: 'message',
            root: 'results',
            totalProperty: 'num'
        });
        Ext.apply(this, {
            store: geneStore
        });
        REDfly.component.selectGene.superclass.initComponent.apply(this, arguments);
    }
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select a species for the data related to the 
// sequence.
// --------------------------------------------------------------------------------
REDfly.component.selectSequenceFromSpecies = Ext.extend(Ext.form.ComboBox, {
    displayField: 'scientific_name',
    editable: false,
    fieldLabel: '"Sequence From" Species',
    listeners: {
        change: function(combo, newValue, oldValue) {
            if ( (oldValue === '') &&
                (newValue !== '') )  {
                this.geneField.enable();
                // Only for TFBSs
                if ( this.transcriptionFactorField !== null ) {
                    this.transcriptionFactorField.enable();
                }
                this.nameField.enable();
                // Only for RCs
                if ( this.transgenicConstructField !== null ) {
                    this.transgenicConstructField.enable();
                }
                this.sequenceField.enable();
                // Only for TFBSs
                if ( this.sequenceWithFlankField !== null ) {
                    this.sequenceWithFlankField.enable();
                }                
            }
        },
        select: function(combo, record, index) {
            this.current_genome_assembly_release_version = record.data.current_genome_assembly_release_version;
            this.id = record.data.id;
            this.scientific_name = record.data.scientific_name;
            this.short_name = record.data.short_name;
        }
    },    
    name: 'species_id',
    store: new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoLoad: true,
        baseParams: {
            sort: 'scientific_name'
        },
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in selectSpecies Store');
                }
            },
            url: REDfly.config.apiUrl + '/jsonstore/species/list'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'current_genome_assembly_release_version',
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
    // REDfly configs
    current_genome_assembly_release_version: null,
    id: null,
    scientific_name: null,
    short_name: null,
    // Form fields dependent on the species chosen by the user
    geneField: null,
    transcriptionFactorField: null,
    nameField: null,
    transgenicConstructField: null,
    sequenceField: null
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select a sequence source term.
// --------------------------------------------------------------------------------
REDfly.component.selectSequenceSource = Ext.extend(Ext.form.ComboBox, {
    allowBlank: false,    
    displayField: 'term',
    fieldLabel: 'Sequence Source',
    listeners: {
        beforequery: function(queryEvent) {
            // The queryEvent object has 4 properties:
            // 'combo', 'query', 'forceAll', and 'cancel'.
            // Set the appropriate parameter for the store based on what the
            // user typed into the combo box.
            var paramObj = {};
            paramObj.name = '*' + queryEvent.query + '*';
            //console.log(paramObj);
            queryEvent.combo.getStore().load({ params: paramObj });
            // Cancel the load, otherwise the combobox will try and
            // load the data again.
            queryEvent.cancel = true;
        }
    },    
    name: 'sequence_source_id',
    store: new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoLoad: true,
        baseParams: {
            sort: 'name'
        },
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in selectSeqSource Store');
                }
            },
            url: REDfly.config.apiUrl + '/jsonstore/sequencesourceterm/list'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'id',
            'term'
        ],
        idProperty: 'id',
        messageProperty: 'message',
        root: 'results',
        totalProperty: 'num'
    }),
    triggerAction: 'all',    
    valueField: 'id'
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select a sex.
// --------------------------------------------------------------------------------
REDfly.component.selectSex = Ext.extend(Ext.form.ComboBox, {
    allowBlank: false,
    displayField: 'term',
    editable: false,
    fieldLabel: 'Sex',
    mode: 'local',
    store: new Ext.data.ArrayStore({
        // Configs
        data: [['m', 'Male'],
               ['f', 'Female'],
               ['both', 'Both']],
        // Properties           
        fields: ['id', 'term' ],
        // Embedded Ext.data.ArrayReader configs
        id: 0
    }),
    triggerAction: 'all',
    valueField: 'id'
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select a status.
// --------------------------------------------------------------------------------
REDfly.component.selectStatus = new Ext.form.ComboBox({
    displayField: 'status',
    editable: false,
    fieldLabel: 'Status',
    id: 'status',
    mode: 'local',
    name: 'status',
    store: new Ext.data.ArrayStore({
        data: [['approval', 'Approval']
               ,['approved', 'Approved']
               ,['archived', 'Archived']
               ,['current', 'Current']
               ,['deleted', 'Deleted']
               ,['editing', 'Editing']
               ],
        fields: ['id', 'status' ],
        id: 0
    }),
    triggerAction: 'all',
    valueField: 'id'
});
// --------------------------------------------------------------------------------
// Create a textfield widget to enter a start coordinate.
// --------------------------------------------------------------------------------
REDfly.component.selectStartCoordinate = Ext.extend(Ext.form.TextField, {
    allowBlank: true,
    name: 'Start',
    regex: /^[0-9]+$/,
    regexText: 'Non-numeric start coordinate'.replace(' ', '&nbsp;'),
    width: 75
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select a transcription factor. 
// Normally, the combo box will pass a query parameter called 'query' to the store
// with whatever value that the user typed to filter the results.
// Since the user can type the transcription factor name (free-form text) or an 
// identifier (string starting with 'AAEL' or 'AGAP' or 'FBgn' or 'TC' depending
// on the species chosen by the curator) we need to handle that in the beforequery
// listener and pass the correct paramters to the load method of the store.
// Note that if we need to access portions of the data record of the store
// associated with a combobox entry we can get to it by:
// var cmp;
// var data = cmp.getStore().getById(cmp.getValue()).data.term;
// --------------------------------------------------------------------------------
REDfly.component.selectTranscriptionFactor = Ext.extend(Ext.form.ComboBox, {
    displayField: 'display',
    fieldLabel: 'Transcription Factor (only applied to TFBSs)',
    // The user must select an item or clears the box
    forceSelection: true,
    listeners: {
        beforequery: function(queryEvent) {
            // The queryEvent object has 4 properties:
            // 'combo', 'query', 'forceAll', and 'cancel'.
            // Set the appropriate parameter for the store based on what the
            // user typed into the combo box.
            var paramObj = {};
            if ( this.sequenceFromSpeciesField.id !== null ) {
                if ( -1 < this.sequenceFromSpeciesField.id.search(/^\d+$/g) ) {
                    paramObj.species_id = this.sequenceFromSpeciesField.id;
                }
            }            
            if (// The Aedes aegypti species 
                // to be targeted for the genes list
                (queryEvent.query.substr(0, 4) === 'AAEL') ||
                // The Anopheles gambiae species
                // to be targeted for the genes list
                (queryEvent.query.substr(0, 4) === 'AGAP') ||
                // The Drosophila melanogaster species
                // to be targeted for the genes list
                (queryEvent.query.substr(0, 4) === 'FBgn') ||
                // The Tribolium castaneum species
                // to be targeted for the genes list
                (queryEvent.query.substr(0, 2) === 'TC')) {
                paramObj.identifier = queryEvent.query + '*';
            } else {
                paramObj.name = '*' + queryEvent.query + '*';
            }
            //console.log(paramObj);
            queryEvent.combo.getStore().load({ params: paramObj });
            // Cancel the load, otherwise the combobox will try and
            // load the data again.
            queryEvent.cancel = true;
        }
    },
    name: 'transcription_factor_id',
    // The transcription factor JsonStore is used to load genes from the REST
    // service. The 'load' listener is used to transform the data return from
    // the store to display the name and identifier together after the data
    // is loaded.
    queryDelay: 750,
    // Set the trigger action to 'query' so that we can set the raw value when
    // the CRUD window is loaded and only that value will be shown in the
    // dropdown.
    triggerAction: 'query',
    valueField: 'id',
    width: 250,    
    // REDfly configs
    // This must be set in order to access the species field in the dialog
    sequenceFromSpeciesField: null,
    initComponent: function() {
        var transcriptionFactorStore = new Ext.data.JsonStore({
            // Ext.data.JsonStore configs
            autoLoad: false,
            baseParams: {
                sort: 'name'
            },
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                listeners: {
                    exception: function() {
                        //console.log('Error in geneSearch Store');
                    }
                },
                url: REDfly.config.apiUrl + '/jsonstore/gene/list'
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
            messageProperty: 'message',
            root: 'results',
            totalProperty: 'num'
        });
        Ext.apply(this, {
            store: transcriptionFactorStore
        });
        REDfly.component.selectTranscriptionFactor.superclass.initComponent.apply(this, arguments);
    }
});
// --------------------------------------------------------------------------------
// Create a combobox widget to select a transgenic construct.
// --------------------------------------------------------------------------------
REDfly.component.selectTransgenicConstruct = Ext.extend(Ext.form.ComboBox, {
    displayField: 'tname',
    editable: false,
    //emptyText: '---',
    fieldLabel: 'Transgenic Construct',
    listeners: {
        beforequery: function(queryEvent) {
            // Targets the drosophila melanogaster species at the moment
            if ( this.sequenceFromSpeciesField.id === '1' ) {            
                var pmid = this.pmidField.getValue();
                if ( (pmid !== '' ) &&
                    this.pmidField.isValid() ) {
                    queryEvent.combo.getStore().load({ params: { pmid: pmid } });
                } else {
                    Ext.MessageBox.show({
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.WARNING,
                        msg: 'You must enter a Pubmed ID to query possible transgenic constructs' .
                            replace(' ', '&nbsp;'),
                        title: 'Pubmed ID Required'
                    });
                    Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
                }
            }
            // Cancel the load, otherwise the combobox will try and load the data again.
            queryEvent.cancel = true;
        }
    },
    name: 'fbtp',
    store: new Ext.data.JsonStore({
        // Ext.data.JsonStore configs
        autoLoad: false,
        baseParams: {
            sort: 'name'
        },
        listeners: {
            load: function(store, recordList, opt) {
                // Change the name field to 'name (identifier)' for display
                for ( i = 0; i < recordList.length; i++ ) {
                    newName = recordList[i].data.tname + ' (' + recordList[i].data.fbtp + ')';
                    recordList[i].set('tname', newName);
                }
            }
        },
        proxy: new Ext.data.HttpProxy({
            method: 'GET',
            listeners: {
                exception: function() {
                    //console.log('Error in fbtp Store');
                }
            },        
            url: REDfly.config.apiUrl + '/jsonstore/remoteflybase/pmid_to_fbal'
        }),
        // Embedded Ext.data.JsonReader configs
        fields: [
            'fbrf',
            'fbtp',
            'tname'
        ],
        idProperty: 'fbtp',
        messageProperty: 'message',
        root: 'results',
        totalProperty: 'num'
    }),
    triggerAction: 'all',
    valueField: 'fbtp',
    // REDfly configs
    // These configs must be set in order to access the PMID and species fields in the dialog
    pmidField: null,
    sequenceFromSpeciesField: null
});
// --------------------------------------------------------------------------------
// The citation JsonStore is used to load the citation from the REST service.
// --------------------------------------------------------------------------------
REDfly.store.citation = new Ext.data.JsonStore({
    // Ext.data.JsonStore configs
    autoLoad: false,
    // Do not save dirty records until told to do so via button handler
    autoSave: false,
    baseParams: {
        limit: 1,
        load_if_not_avail: 1,
        sort: 'external_id'
    },
    proxy: new Ext.data.HttpProxy({
        api: {
            create: {
                method: 'POST',
                url: REDfly.config.apiUrl + '/jsonstore/citation/save'
            },
            // This is not implemented, but required in the API
            destroy: {
                url: REDfly.config.apiUrl + '/jsonstore/citation/delete'
            },
            read: {
                method: 'GET',
                url: REDfly.config.apiUrl + '/jsonstore/citation/list'
            },
            update: {
                method: 'POST',
                url: REDfly.config.apiUrl + '/jsonstore/citation/save'
            }
        }
    }),    
    // Embedded Ext.data.JsonReader configs
    fields: [
        'author_contacted',
        'author_contacted_date',
        'author_email',
        'author_list',
        'citation_id',
        'citation_type',
        'contents',
        'external_id',
        'journal_name',
        'title',
        'year'
    ],
    idProperty: 'citation_id',
    messageProperty: 'message',
    root: 'results',
    successProperty: 'success',
    totalProperty: 'num',
    // Embedded Ext.data.JsonWriter configs
    writer: new Ext.data.JsonWriter({
        encode: true,
        // write all fields, not just those that changed        
        writeAllFields: true
    })
});
// The Ajax timeout is set as 60000 (60 seconds) so that the front end will wait 
// until getting the results from the Dockerized BLAT server due to the length of 
// the longest genome from the Tribolium castaneum species. It takes about 32 seconds.
// The other genomes of the other insect species: Aedes aegypti, Anopheles gambiae,
// and Drosophila melanogaster are shorter so they take wait times shorter than the
// 32 seconds.
Ext.Ajax.timeout = 60000;
// --------------------------------------------------------------------------------
// The blatsearch JsonStore is used to load BLAT coordinates from the REST service.
// --------------------------------------------------------------------------------
REDfly.store.blatsearch = new Ext.data.JsonStore({
    // Ext.data.JsonStore configs
    autoLoad: false,
    listeners: {
        load: function() {
            //console.log('blatsearch load');
        }
    },
    proxy: new Ext.data.HttpProxy({
        method: 'POST',
        // The new link pointing into the local Dockerized BLAT server
        url: REDfly.config.baseUrl + 'api/v2/datasource/blat/search',
    }),
    // Embedded Ext.data.JsonReader configs
    fields: [
        'chromosome',
        'chromosome_id',
        'end',
        'size',
        'start'
    ],
    messageProperty: 'message',
    root: 'results',
    totalProperty: 'num'
});
// --------------------------------------------------------------------------------
// Blat info window
// --------------------------------------------------------------------------------
REDfly.component.blatInfo = Ext.extend(Ext.Window, {
    buttonAlign: 'center',
    layout: 'fit',
    modal: true,
    store: null,
    title: 'Multiple possible matches returned from BLAT',
    constructor: function(config)
    {
        config = config || {};
        this.store = config.store;
        this.addEvents('recordselected');
        this.listeners = config.listeners;
        REDfly.component.blatInfo.superclass.constructor.call(this, config);
    },
    initComponent: function() {
        var selModel = new Ext.grid.RowSelectionModel({ singleSelect: true });
        var grid = new Ext.grid.GridPanel({
            colModel: new Ext.grid.ColumnModel({
                columns: [
                    {
                        dataIndex: 'chromosome',
                        header: 'Chromosome'
                    }, {
                        dataIndex: 'start',
                        header: 'Start'
                    }, {
                        dataIndex: 'end',
                        header: 'End'
                    }, {
                        dataIndex: 'size',
                        header: 'Size'
                    }
                ]
            }),
            listeners: {
                rowdblclick: function(grid, rowIndex) {
                    var selected = grid.store.getAt(rowIndex);
                    this.fireEvent('recordselected', selected);
                    this.close();
                },
                scope: this
            },
            selModel: selModel,
            store: this.store,
            title: 'BLAT Records',
            viewConfig: {
                autoFill: true
            }
        });
        this.width = Ext.min([
            Ext.getBody().getViewSize().width,
            640
        ]);
        this.height = Ext.min([
            Ext.getBody().getViewSize().height,
            480
        ]);
        Ext.apply(this, {
            buttons: [
                {
                    handler: function() {
                        if ( selModel.hasSelection() ) {
                            var selected = selModel.getSelected();
                            this.fireEvent('recordselected', selected);
                            this.close();
                        } else {
                            Ext.MessageBox.show({
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING,
                                msg: 'No record selected'.replace(' ', '&nbsp;'),
                                title: 'Error'
                            });
                            Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
                        }
                    },
                    scope: this,
                    text: 'Select'
                }, {
                    handler: this.close,
                    scope: this,
                    text: 'Cancel'
                }
            ],
            items: [grid]
        });
        REDfly.component.blatInfo.superclass.initComponent.apply(this, arguments);
        this.show();
    }
});