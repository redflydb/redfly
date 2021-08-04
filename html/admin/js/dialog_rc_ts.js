// ================================================================================
// Dialog for performing CRUD operations of staging data only for RCs.
// ================================================================================
// Creates a record that will be used to configure the store data fields and keep
// track of data in the dialog. This lets us store everything in one place.
// --------------------------------------------------------------------------------
REDfly.dialog.rctsRecord = Ext.data.Record.create([
    {   name: 'action',
        defaultValue: 'cancel'  },
    {   name: 'anatomical_expression_display'  },
    {   name: 'anatomical_expression_identifier'   },
    {   name: 'anatomical_expression_term'   },    
    {   name: 'assayed_in_species_id'   },
    {   name: 'biological_process_display',
        allowBlank: true,
        defaultValue: ''    },
    {   name: 'biological_process_id',
        allowBlank: true,
        defaultValue: ''    },
    {   name: 'biological_process_identifier',
        allowBlank: true,
        defaultValue: ''    },
    {   name: 'biological_process_term',
        allowBlank: true,
        defaultValue: ''    },
    {   name: 'ectopic_id',
        defaultValue: '0'   },
    {   name: 'ectopic_term',
        defaultValue: 'False'   },
    {   name: 'enhancer_or_silencer_attribute_id',
        defaultValue: 'enhancer'   },
    {   name: 'enhancer_or_silencer_attribute_term',
        defaultValue: 'Enhancer'   },
    {   name: 'pubmed_id'   },
    {   name: 'rc_id',
        allowBlank: true,
        defaultValue: ''    },
    {   name: 'sex_id',
        defaultValue: 'both'    },
    {   name: 'sex_term',
        defaultValue: 'Both'    },
    {   name: 'stage_off_display'   },
    {   name: 'stage_off_id'    },
    {   name: 'stage_off_identifier'    },
    {   name: 'stage_off_term'   },
    {   name: 'stage_on_display'   },
    {   name: 'stage_on_id' },
    {   name: 'stage_on_identifier' },
    {   name: 'stage_on_term'   },
    {   name: 'ts_id',
        allowBlank: true,
        defaultValue: ''    }
]);
// --------------------------------------------------------------------------------
// Creates the new/edit staging data dialog by extending Ext.FormPanel.  
// This is used for CRUD operations on staging data and contains functionality to
// automatically query citations.
// --------------------------------------------------------------------------------
REDfly.component.rctsPanel = Ext.extend(Ext.FormPanel, {
    // Ext.FormPanel configs
    bodyStyle: { backgroundColor: '#F1F1F1' },
    border: false,
    buttonAlign: 'center',
    defaultType: 'textfield',
    defaults: { width: '95%' },
    labelWidth: 130,
    // If true, the form monitors its valid state client-side and regularly fires
    // the clientvalidation event passing that state. When monitoring valid state,
    // the FormPanel enables/disables any of its configured buttons which have been 
    // configured with formBind: true depending on whether the form is valid or not.    
    monitorValid: true,
    padding: 5,
    title: null,
    // REDfly configs
    rctsStore: null,
    citationRctsStore: null,
    mode: null,
    rowIndex: null,
    tsId: null,
    rcId: null,
    anatomicalExpressionDisplay: null,
    anatomicalExpressionIdentifier: null,
    anatomicalExpressionTerm: null,
    pubmedId: null,
    assayedInSpeciesId: null,
    sexId: null,
    sexTerm: null,
    ectopicId: null,
    ectopicTerm: null,
    enhancerOrSilencerAttributeId: null,
    enhancerOrSilencerAttributeTerm: null,
    // Record to hold the dialog information during the window existence and
    // to save the data when its editing is complete.
    data: null,
    // This object will hold all of the form elements for easy access.
    formElements: null,
    // Note: non-primitive data structures must be initialized here in the
    // initComponent function for efficiently creating and destroying 
    // objects of this class.
    initComponent: function() {
        this.rctsStore = new Ext.data.JsonStore({
            // Ext.data.JsonStore configs
            autoLoad: false,
            // Do not save dirty records until told to do so via button handler.
            autoSave: false,
            proxy: new Ext.data.HttpProxy({
                api: {
                    create: {
                        method: 'POST',                        
                        url: REDfly.config.apiUrl + '/jsonstore/reporterconstructtriplestore/save'
                    },
                    // No destroy for this store but the specification can not be undefined!
                    destroy: {
                        url: REDfly.config.apiUrl + '/jsonstore/reporterconstructtriplestore/destroy'
                    },
                    read: {
                        method: 'GET',                        
                        url: REDfly.config.apiUrl + '/jsonstore/reporterconstructtriplestore/load'
                    },
                    update: {
                        method: 'POST',                        
                        url: REDfly.config.apiUrl + '/jsonstore/reporterconstructtriplestore/update'
                    }
                }
            }),
            writer: new Ext.data.JsonWriter({
                encode: true,
                // Write all the fields, not just those that changed.
                writeAllFields: true
            }),
            // Embedded Ext.data.JsonReader configs
            fields: REDfly.dialog.rctsRecord,
            idProperty: 'ts_id',
            messageProperty: 'message',
            root: 'results',
            successProperty: 'success',
            totalProperty: 'total'
        }),
        this.citationRctsStore = new Ext.data.JsonStore({
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
                // Write all the fields, not just those that changed.
                writeAllFields: true
            })
        });
        // Turns on validation errors beside the field globally.
        Ext.form.Field.prototype.msgTarget = 'side';
        // Enables the 'stagingdata' event generated by this class, REDfly.component.rctsPanel,
        // to be detected by its parent class, Ext.Window.
        this.enableBubble('stagingdata');
        this.data = new REDfly.dialog.rctsRecord();
        this.data.set('anatomical_expression_display', this.anatomicalExpressionDisplay);
        this.data.set('anatomical_expression_identifier', this.anatomicalExpressionIdentifier);
        this.data.set('anatomical_expression_term', this.anatomicalExpressionTerm);
        this.data.set('assayed_in_species_in', this.assayedInSpeciesId);
        this.data.set('ectopic_id', this.ectopicId);
        this.data.set('ectopic_term', this.ectopicTerm);
        this.data.set('enhancer_or_silencer_attribute_id', this.enhancerOrSilencerAttributeId);
        this.data.set('enhancer_or_silencer_attribute_term', this.enhancerOrSilencerAttributeTerm);
        this.data.set('pubmed_id', this.pubmedId);
        this.data.set('rc_id', this.rcId);
        this.data.set('sex_id', this.sexId);
        this.data.set('sex_term', this.sexTerm);
        this.data.set('ts_id', this.tsId);
        var anatomicalExpressionItem = new Ext.form.TextField({
            fieldLabel: 'Anatomical Expression',
            readOnly: true,
            value: this.data.get('anatomical_expression_display')
        });
        var pubmedIdItem = new Ext.form.TextField({
            allowBlank: false,
            fieldLabel: 'Pubmed ID',
            labelStyle: 'color:blue; text-decoration:underline; cursor:pointer;',
            listeners: {
                afterrender: function(field) {
                    field.label.on(
                        'click',
                        function() {
                            var id = field.getValue();
                            if ( Ext.isEmpty(id) ) {
                                Ext.MessageBox.show({
                                    buttons: Ext.MessageBox.OK,
                                    fn: function() {
                                        Ext.WindowMgr.get('rctsCrudWindowId').getEl().setStyle('z-index', '90000');
                                    },
                                    icon: Ext.MessageBox.WARNING,
                                    msg: 'No Pubmed ID entered'.replace(' ', '&nbsp;'),
                                    title: 'Warning'
                                });
                                Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
                            } else {
                                window.open('http://www.ncbi.nlm.nih.gov/pubmed/' + id);
                            }
                        },
                        this
                    );
                },
                beforerender: function(field) {
                    var id = field.getValue();
                    if ( ! Ext.isEmpty(id) ) {
                        this.loadCitation(id);
                    }                    
                }, 
                // It is activated when the regular expression rule is not fulfilled.
                invalid: function(field) {
                    this.data.set('pubmed_id', ''); 
                },
                scope: this,
                // It is activated when the regular expression rule is fulfilled.
                valid: function(field) {
                    // Only loads data if the Pubmed ID has changed.
                    if ( this.data.get('pubmed_id') !== field.getValue() ) {
                        this.data.set('pubmed_id', field.getValue());
                        this.loadCitation(field.getValue());
                    }
                }
            },
            // It is set here to be automatically included with form submit().
            name: 'pubmed_id',
            regex: /^[0-9]{1,8}$/,
            regexText: 'Pubmed ID must have between 1 and 8 digits (0 to 9)',
            validationDelay: 500,
            validationEvent: 'blur',
            value: this.data.get('pubmed_id'),
            width: 300,
        });
        var citationItem = new Ext.form.TextArea({
            allowBlank: false,
            disabled: true,
            fieldLabel: 'Citation',
            height: 100,
            width: 400
        });
        var stageOnItem = new REDfly.component.selectDevelopmentalStageOn();
        // No longer necessary since the Drosophila ontology has to be accessible
        // for all species annotation according to the PI
        //stageOnItem.assayed_in_species_id = this.assayedInSpeciesId;
        stageOnItem.on(
            'select',
            this.selectStageOnComboBox,
            this
        );
        var stageOffItem = new REDfly.component.selectDevelopmentalStageOff();
        // No longer necessary since the Drosophila ontology has to be accessible
        // for all species annotation according to the PI
        //stageOffItem.assayed_in_species_id = this.assayedInSpeciesId;
        stageOffItem.on(
            'select',
            this.selectStageOffComboBox,
            this
        );
        var biologicalProcessItem = new REDfly.component.selectBiologicalProcess();
        biologicalProcessItem.on(
            'select',
            this.selectBiologicalProcessComboBox,
            this
        );
        var sexItem = new REDfly.component.selectSex();
        sexItem.on(
            'select',
            this.selectSexComboBox,
            this
        );
        var ectopicItem = new REDfly.component.selectEctopic();
        ectopicItem.on(
            'select',
            this.selectEctopicComboBox,
            this
        );
        var enhancerOrSilencerAttributeItem = new REDfly.component.selectEnhancerOrSilencerAttribute();
        enhancerOrSilencerAttributeItem.on(
            'select',
            this.selectEnhancerOrSilencerAttributeComboBox,
            this
        );
        var buttonList = new Array();
        var submitButtonLabel;
        switch(this.mode) {
            case 'create':
                submitButtonLabel = 'Save';
                break;
            case 'edit':
                submitButtonLabel = 'Update';
                break;
            default:
                submitButtonLabel = 'Unknown Mode';
        };
        buttonList.push({
            formBind: true,
            handler: function(button, event) {
                this.setAction(this.mode);
                this.save();
                this.ownerCt.destroy();
            },
            scope: this,
            text: submitButtonLabel
        });
        buttonList.push({
            handler: function(button, event) {
                this.setAction('cancel');
                this.ownerCt.destroy();
            },
            scope: this,
            text: 'Cancel'
        });
        // Applies the changes to the window including storing the form elements
        // under this.formElements for easy access later.
        Ext.apply(
            this,
            {
                // Ext.FormPanel configs
                items: [
                    anatomicalExpressionItem,
                    pubmedIdItem,
                    citationItem,
                    stageOnItem,
                    stageOffItem,
                    biologicalProcessItem,
                    sexItem,
                    ectopicItem,
                    enhancerOrSilencerAttributeItem
                ],
                buttons: buttonList,
                // UI variables
                formElements: {
                    anatomicalExpressionTextField: anatomicalExpressionItem,
                    pubmedIdTextField: pubmedIdItem,
                    citationTextArea: citationItem,
                    stageOnComboBox: stageOnItem,
                    stageOffComboBox: stageOffItem,
                    biologicalProcessComboBox: biologicalProcessItem,
                    sexComboBox: sexItem,
                    ectopicComboBox: ectopicItem,
                    enhancerOrSilencerAttributeComboBox: enhancerOrSilencerAttributeItem
                }
            }
        );
        REDfly.component.rctsPanel.superclass.initComponent.apply(this, arguments);
        if ( this.data.get('sex_id') !== null ) {
            this.formElements.sexComboBox.setValue(this.data.get('sex_id'));
        }
        if ( this.data.get('ectopic_id') !== null ) {
            this.formElements.ectopicComboBox.setValue(this.data.get('ectopic_id'));
        }
        if ( this.data.get('enhancer_or_silencer_attribute_id') !== null ) {
            this.formElements.enhancerOrSilencerAttributeComboBox.setValue(this.data.get('enhancer_or_silencer_attribute_id'));
        }
        // Since AJAX calls are async, adds a listener to the store so the data
        // in the window will be populated on load.
        this.rctsStore.on(
            'load',
            this.loadFormFromRecord,
            this
        );
        this.rctsStore.on(
            'exception',
            this.rctsStoreException,
            this
        );
        // Uses a delegate to pass a message for the user into the callback.
        this.citationRctsStore.on(
            'beforeload',
            this.showWaitMessage.createDelegate(
                this.citationRctsStore,
                ['Loading citation data, please wait...']
            ),
            this
        );
        // Adds a listener to the citation store so that the citation is
        // displayed in the text area when a pubmed id is provided.
        var displayCitationTextArea = function(store, records, options) {
            var contents = '';
            if ( records.length !== 0 ) {
                contents = records[0].data.contents;
            } 
            this.formElements.citationTextArea.setValue(contents);
        };
        this.citationRctsStore.on(
            'load',
            displayCitationTextArea,
            this
        );
        this.citationRctsStore.on(
            'load',
            this.hideWaitMessage,
            this
        );
        // Handles errors during the loading of the citation.
        var citationException = function(proxy, type, action, options, response, arg) {
            this.formElements.pubmedIdTextField.setValue('');
            this.formElements.citationTextArea.setValue('');
            Ext.MessageBox.show({
                buttons: Ext.MessageBox.OK,
                fn: function() {
                    Ext.WindowMgr.get('rctsCrudWindowId').getEl().setStyle('z-index', '90000');
                },
                icon: Ext.MessageBox.WARNING,
                msg: ('No citation found for PMID ' + options.params.external_id + ': '
                    + response.message).replace(' ', '&nbsp;'),
                title: 'Warning'
            });
            Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
        };
        this.citationRctsStore.on(
            'exception',
            citationException,
            this
        );
    },
    // --------------------------------------------------------------------------------
    // Displays a wait message to the user while contacting external services/sites.
    // @param message The message to be displayed on the dialog
    // --------------------------------------------------------------------------------
    showWaitMessage: function(message) {
        Ext.MessageBox.show({
            msg: message,
            progressText: 'Loading...',
            width: 300,
            wait: true,
            waitConfig: { interval: 200 }
        });
        Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
    },
    // --------------------------------------------------------------------------------
    // Hides any visible wait message.
    // --------------------------------------------------------------------------------
    hideWaitMessage: function() {
        Ext.MessageBox.hide();
        Ext.WindowMgr.get('rctsCrudWindowId').getEl().setStyle('z-index', '90000');
    },
    // --------------------------------------------------------------------------------
    // Resets the current form and load any data specified by the ts identifier.
    // --------------------------------------------------------------------------------
    load: function(tsId) {
        // Since we are currently loading on the "show" event, make sure to clear
        // the form if no ts_id was provided or we will be showing whatever
        // was loaded the last time the window was displayed.
        this.reset();
        if ( ! Ext.isEmpty(tsId) ) {
            this.rctsStore.load({
                params: { ts_id: tsId }
            });
        }
    },
    // --------------------------------------------------------------------------------
    // Resets the dialog.
    // --------------------------------------------------------------------------------
    reset: function() {
        this.getForm().reset();
        this.rctsStore.removeAll(true);
        // Do not fire the 'clear' event or it will trigger the 'destroy' REST call.
        this.citationRctsStore.removeAll(true);
        this.data = new REDfly.dialog.rctsRecord({
            action: 'cancel',
            assayed_in_species_id: this.assayedInSpeciesId,
            biological_process_display: null,
            biological_process_id: null,
            biological_process_identifier: null,
            biological_process_term: null,
            ectopic_id: '0',
            ectopic_term: 'False',
            enhancer_or_silencer_attribute_id: 'enhancer',
            enhancer_or_silencer_attribute_term: 'Enhancer',
            anatomical_expression_display: this.anatomicalExpressionDisplay,
            anatomical_expression_identifier: this.anatomicalExpressionIdentifier,
            anatomical_expression_term: this.anatomicalExpressionTerm,
            pubmed_id: this.pubmedId,
            rc_id: this.rcId,
            sex_id: 'both',
            sex_term: 'Both',
            stage_off_display: null,
            stage_off_id: null,
            stage_off_identifier: null,
            stage_off_term: null,
            stage_on_display: null,
            stage_on_id: null,
            stage_on_identifier: null,
            stage_on_term: null,
            ts_id: null
        });
        // Sets undefined values to an empty string so the form clears them out
        // when we update it.
        this.normalizeEmptyRecordFields(this.data);
        this.getForm().loadRecord(this.data);
    },
    // --------------------------------------------------------------------------------
    // Normalizes the record by setting undefined values to an empty string so the 
    // form clears them out when we update it. If we do not do this it seems as if 
    // those fields do not even exist.
    // @param record The record to normalize
    // --------------------------------------------------------------------------------
    normalizeEmptyRecordFields: function(record) {
        // The Ext.data.Record::set() method will not set the value if it is null!
        // set : function(name, value) {
        //   var encode = Ext.isPrimitive(value) ? String : Ext.encode;
        //   if (encode(this.data[name]) == encode(value))
        //     return; // if null it always returns here
        // Set undefined values to an empty string so the form clears
        // them out when we update it.
        record.fields.each(function (field) {
            var fieldName = field.name;
            if ( record.get(fieldName) === undefined ) {
                // We can not set the record value to null or
                // it will not make the change!
                record.set(fieldName, '');
            }
        });
    },
    // --------------------------------------------------------------------------------
    // Loads data from the store into the form. It is automatically executed after 
    // the load function.
    // --------------------------------------------------------------------------------
    loadFormFromRecord: function(store, recordList, opt) {
        var rctsRecord = recordList[0];
        this.data = rctsRecord;
        // As long as any form item/element has the 'name' attribute defined
        // when using form submit().
        this.getForm().loadRecord(rctsRecord);
        this.loadCitation(rctsRecord.get('pubmed_id'));
        // Making the following comboboxes targeting the species identifier given
        // No longer necessary since the Drosophila ontology has to be accessible
        // for all species annotation according to the PI
        //this.formElements.stageOnComboBox.assayed_in_species_id = rctsRecord.get('assayed_in_species_id');
        //this.formElements.stageOffComboBox.assayed_in_species_id = rctsRecord.get('assayed_in_species_id');
        // Loading the following comboboxes with their identifiers assigned from the record.
        this.loadStageOnComboBox(rctsRecord.get('stage_on_id'));
        this.loadStageOffComboBox(rctsRecord.get('stage_off_id'));
        this.loadBiologicalProcessComboBox(rctsRecord.get('biological_process_id'));
        this.loadSexComboBox(rctsRecord.get('sex_id'));
        this.loadEctopicComboBox(rctsRecord.get('ectopic_id'));
        this.loadEnhancerOrSilencerAttributeComboBox(rctsRecord.get('enhancer_or_silencer_attribute_id'));
        this.doLayout();
    },
    // --------------------------------------------------------------------------------
    // Loads the citation from the database or NCBI through the citation store.
    // --------------------------------------------------------------------------------
    loadCitation: function(pubmedId) {
        if ( ! Ext.isEmpty(pubmedId) ) {
            this.citationRctsStore.load({ 
                params: { external_id: pubmedId } 
            });
        }
    },
    // --------------------------------------------------------------------------------
    // Loads the stage on store and sets the selected stage on.
    // Also updates the record data: stage_on_id, stage_on_identifier, stage_on_term,
    // and stage_on_display.
    // @param {Number} stageOnId The id number of the stage on to select.
    // --------------------------------------------------------------------------------
    loadStageOnComboBox: function(stageOnId) {
        this.formElements.stageOnComboBox.store.load({
            callback: function() {
                this.formElements.stageOnComboBox.setValue(stageOnId);
                var record = this.formElements.stageOnComboBox.getStore().getById(stageOnId);
                this.data.set('stage_on_display', record.data.display);
                this.data.set('stage_on_id', record.data.id);
                this.data.set('stage_on_identifier', record.data.identifier);
                this.data.set('stage_on_term', record.data.term);
            },
            params: { id: stageOnId },
            scope: this
        });
    },
    // --------------------------------------------------------------------------------
    // Loads the stage off store and sets the selected stage off.
    // Also updates the record data: stage_off_id, stage_off_identifier, 
    // stage_off_term, and stage_off_display.
    // @param {Number} stageOffId The id number of the stage off to select.
    // --------------------------------------------------------------------------------
    loadStageOffComboBox: function(stageOffId) {
        this.formElements.stageOffComboBox.store.load({
            callback: function() {
                this.formElements.stageOffComboBox.setValue(stageOffId);
                var record = this.formElements.stageOffComboBox.getStore().getById(stageOffId);
                this.data.set('stage_off_display', record.data.display);
                this.data.set('stage_off_id', record.data.id);
                this.data.set('stage_off_identifier', record.data.identifier);
                this.data.set('stage_off_term', record.data.term);
            },
            params: { id: stageOffId },
            scope: this
        });
    },
    // --------------------------------------------------------------------------------
    // Loads the biological process store and sets the selected biological process.  
    // Also updates the record data: biological_process_id, biological_process_go_id, 
    // biological_process_term, and biological_process_display.
    // @param {Number} biologicalProcessId The id number of the biological process 
    // to select.
    // --------------------------------------------------------------------------------
    loadBiologicalProcessComboBox: function(biologicalProcessId) {
        // Biological Process Not Mandatory
        if ( biologicalProcessId === '0' ) {
            this.formElements.biologicalProcessComboBox.setValue('');
            this.data.set('biological_process_display', '');
            this.data.set('biological_process_id', '');
            this.data.set('biological_process_identifier', '');
            this.data.set('biological_process_term', '');
        } else {
            this.formElements.biologicalProcessComboBox.store.load({
                callback: function() {
                    this.formElements.biologicalProcessComboBox.setValue(biologicalProcessId);
                    var record = this.formElements.biologicalProcessComboBox.getStore().getById(biologicalProcessId);
                    this.data.set('biological_process_display', record.data.display);
                    this.data.set('biological_process_id', record.data.id);
                    this.data.set('biological_process_identifier', record.data.identifier);
                    this.data.set('biological_process_term', record.data.term);
                },
                params: { id: biologicalProcessId },
                scope: this
            });
        }
    },
    // --------------------------------------------------------------------------------
    // Sets the selected sex.  
    // Also updates the record data: sex_id and sex_term.
    // @param {Number} sexId The id number of the sex to select.
    // --------------------------------------------------------------------------------
    loadSexComboBox: function(sexId) {
        this.formElements.sexComboBox.setValue(sexId);
        var record = this.formElements.sexComboBox.getStore().getById(sexId);
        this.data.set('sex_id', record.data.id);
        this.data.set('sex_term', record.data.term);
    },
    // --------------------------------------------------------------------------------
    // Sets the selected ectopic.  
    // Also updates the record data: ectopic_id and ectopic_term.
    // @param {Number} ectopicId The id number of the ectopic to select.
    // --------------------------------------------------------------------------------
    loadEctopicComboBox: function(ectopicId) {
        this.formElements.ectopicComboBox.setValue(ectopicId);
        var record = this.formElements.ectopicComboBox.getStore().getById(ectopicId);
        this.data.set('ectopic_id', record.data.id);
        this.data.set('ectopic_term', record.data.term);
    },
    // --------------------------------------------------------------------------------
    // Sets the selected enhancer/silencer attribute.  
    // Also updates the record data: enhancer_or_silencer_attribute_id and
    // enhancer_or_silencer_attribute_term.
    // @param {Number} enhancerOrSilencerAttributeId The id number of the 
    // enhancer/silencer attribute to select.
    // --------------------------------------------------------------------------------
    loadEnhancerOrSilencerAttributeComboBox: function(enhancerOrSilencerAttributeId) {
        this.formElements.enhancerOrSilencerAttributeComboBox.setValue(enhancerOrSilencerAttributeId);
        var record = this.formElements.enhancerOrSilencerAttributeComboBox.getStore().getById(enhancerOrSilencerAttributeId);
        this.data.set('enhancer_or_silencer_attribute_id', record.data.id);
        this.data.set('enhancer_or_silencer_attribute_term', record.data.term);
    },    
    // --------------------------------------------------------------------------------
    // Stores all the stage on data when the user makes a selection in the dropdown.
    // @param {Ext.form.ComboBox} combo The stage on combobox.
    // @param {Ext.data.Record} record The stage on data record.
    // @param {Number} index The index of the selected item.
    // --------------------------------------------------------------------------------
    selectStageOnComboBox: function(combo, record, index) {
        this.data.set('stage_on_display', record.data.display);
        this.data.set('stage_on_id', record.data.id);
        this.data.set('stage_on_identifier', record.data.identifier);
        this.data.set('stage_on_term', record.data.term);
    },
    // --------------------------------------------------------------------------------
    // Stores all the stage off data when the user makes a selection in the dropdown.
    // @param {Ext.form.ComboBox} combo The stage off combobox.
    // @param {Ext.data.Record} record The stage off data record.
    // @param {Number} index The index of the selected item.
    // --------------------------------------------------------------------------------
    selectStageOffComboBox: function(combo, record, index) {
        this.data.set('stage_off_display', record.data.display);
        this.data.set('stage_off_id', record.data.id);
        this.data.set('stage_off_identifier', record.data.identifier);
        this.data.set('stage_off_term', record.data.term);
    },
    // --------------------------------------------------------------------------------
    // Stores all the biological process data when the user makes a selection in the
    // dropdown.
    // @param {Ext.form.ComboBox} combo The biological process combobox.
    // @param {Ext.data.Record} record The biological process data record.
    // @param {Number} index The index of the selected item.
    // --------------------------------------------------------------------------------
    selectBiologicalProcessComboBox: function(combo, record, index) {
        this.data.set('biological_process_display', record.data.display);
        this.data.set('biological_process_id', record.data.id);
        this.data.set('biological_process_identifier', record.data.identifier);
        this.data.set('biological_process_term', record.data.term);
    },
    // --------------------------------------------------------------------------------
    // Stores all the sex data when the user makes a selection in the dropdown.
    // @param {Ext.form.ComboBox} combo The sex search combobox.
    // @param {Ext.data.Record} record The sex data record.
    // @param {Number} index The index of the selected item.
    // --------------------------------------------------------------------------------
    selectSexComboBox: function(combo, record, index) {
        this.data.set('sex_id', record.data.id);
        this.data.set('sex_term', record.data.term);
    },
    // --------------------------------------------------------------------------------
    // Stores all the ectopic data when the user makes a selection in the dropdown.
    // @param {Ext.form.ComboBox} combo The ectopic search combobox.
    // @param {Ext.data.Record} record The ectopic data record.
    // @param {Number} index The index of the selected item.
    // --------------------------------------------------------------------------------
    selectEctopicComboBox: function(combo, record, index) {
        this.data.set('ectopic_id', record.data.id);
        this.data.set('ectopic_term', record.data.term);
    },
    // --------------------------------------------------------------------------------
    // Stores all the enhancer/silencer attribute data when the user makes a selection
    // in the dropdown.
    // @param {Ext.form.ComboBox} combo The enhancer/silencer attribute search combobox.
    // @param {Ext.data.Record} record The enhancer/silencer attribute data record.
    // @param {Number} index The index of the selected item.
    // --------------------------------------------------------------------------------
    selectEnhancerOrSilencerAttributeComboBox: function(combo, record, index) {
        this.data.set('enhancer_or_silencer_attribute_id', record.data.id);
        this.data.set('enhancer_or_silencer_attribute_term', record.data.term);
    },
    // --------------------------------------------------------------------------------
    // Saves new data and sends it back to the server after the verification being 
    // completed.
    // ---------------------------------------------------------------------------------
    save: function() {
        this.updateDataRecordFromForm();
        this.verify({
            success: this._save('Saving...'),
            failure: this.displayVerificationErrors
        });
    },
    // --------------------------------------------------------------------------------
    // Updates the internal data record based on the data currently entered into
    // the form elements. This is done in preparation for saving the data.
    // --------------------------------------------------------------------------------
    updateDataRecordFromForm: function() {
        this.data.beginEdit();
        this.data.set('pubmed_id', this.formElements.pubmedIdTextField.getValue());
        if ( this.formElements.stageOnComboBox.getValue() === '' ) {
            this.data.set('stage_on_display', '');
            this.data.set('stage_on_id', '');
            this.data.set('stage_on_identifier', '');
            this.data.set('stage_on_term', '');
        } else {
            var stageOnRecord = this.formElements.stageOnComboBox.getStore().getById(this.formElements.stageOnComboBox.getValue());
            this.data.set('stage_on_display', stageOnRecord.data.display);
            this.data.set('stage_on_id', stageOnRecord.data.id);
            this.data.set('stage_on_identifier', stageOnRecord.data.identifier);
            this.data.set('stage_on_term', stageOnRecord.data.term);
        }
        if ( this.formElements.stageOffComboBox.getValue() === '' ) {
            this.data.set('stage_off_display', '');
            this.data.set('stage_off_id', '');
            this.data.set('stage_off_identifier', '');
            this.data.set('stage_off_term', '');
        } else {
            var stageOffRecord = this.formElements.stageOffComboBox.getStore().getById(this.formElements.stageOffComboBox.getValue());
            this.data.set('stage_off_display', stageOffRecord.data.display);
            this.data.set('stage_off_id', stageOffRecord.data.id);
            this.data.set('stage_off_identifier', stageOffRecord.data.identifier);
            this.data.set('stage_off_term', stageOffRecord.data.term);
        }
        if ( (this.formElements.biologicalProcessComboBox.getValue() === null) || 
            (this.formElements.biologicalProcessComboBox.getValue() === '') ) {
            this.data.set('biological_process_display', '');
            this.data.set('biological_process_id', '');
            this.data.set('biological_process_identifier', '');
            this.data.set('biological_process_term', '');
        } else {
            var biologicalProcessRecord = this.formElements.biologicalProcessComboBox.getStore().getById(this.formElements.biologicalProcessComboBox.getValue());
            this.data.set('biological_process_display', biologicalProcessRecord.data.display);
            this.data.set('biological_process_id', biologicalProcessRecord.data.id);
            this.data.set('biological_process_identifier', biologicalProcessRecord.data.identifier);
            this.data.set('biological_process_term', biologicalProcessRecord.data.term);
        }
        var sexRecord = this.formElements.sexComboBox.getStore().getById(this.formElements.sexComboBox.getValue());
        this.data.set('sex_id', sexRecord.data.id);
        this.data.set('sex_term', sexRecord.data.term);
        var ectopicRecord = this.formElements.ectopicComboBox.getStore().getById(this.formElements.ectopicComboBox.getValue());
        this.data.set('ectopic_id', ectopicRecord.data.id);
        this.data.set('ectopic_term', ectopicRecord.data.term);
        var enhancerOrSilencerAttributeRecord = this.formElements.enhancerOrSilencerAttributeComboBox.getStore().getById(this.formElements.enhancerOrSilencerAttributeComboBox.getValue());
        this.data.set('enhancer_or_silencer_attribute_id', enhancerOrSilencerAttributeRecord.data.id);
        this.data.set('enhancer_or_silencer_attribute_term', enhancerOrSilencerAttributeRecord.data.term);
        this.data.endEdit();
    },
    // --------------------------------------------------------------------------------
    // Applies the general verification of the data in the dialog.
    // This may include local validation as well as asynchronous AJAX calls to the 
    // server.
    // @return True on successful verification, false otherwise.
    // --------------------------------------------------------------------------------
    verify: function(config) {
        var errorList = [];
        if ( ! this.verifyLocalFormElements(errorList) ) {
            if ( errorList.length !== 0 ) {
                config.failure.call(this, errorList);
            }
            return false;
        }
        return true;
    },
    // --------------------------------------------------------------------------------
    // Verifies the local form elements.
    // @param errorList An array of error messages generated by this verification
    // @returns True on success and false if there was a verification error
    // --------------------------------------------------------------------------------
    verifyLocalFormElements: function(errorList) {
        // Mandatory
        //if ( this.formElements.citationTextArea.getValue() === '' )
        if ( this.data.set('pubmed_id') === '' ) {
            errorList.push('Pubmed identifier non-existing.');
        }
        // Mandatory
        if ( this.data.get('stage_on_identifier') === '' ) {
            errorList.push('No stage_on identifier selected.');
        }
        // Mandatory
        if ( this.data.get('stage_off_identifier') === '' ) {
            errorList.push('No stage_off identifier selected.');
        }
        // Mandatory
        if ( this.data.get('enhancer_or_silencer_attribute_id') === '' ) {
            errorList.push('No enhancer/silencer selected.');
        }
        return ( errorList.length === 0 );
    },
    // --------------------------------------------------------------------------------
    // Performs the actual saving of the data. This should be called after all the
    // (possibly asynchronous) verifications are successfully completed.
    // --------------------------------------------------------------------------------
    _save: function(message) {
        this.fireEvent(
            'stagingdata',
            this.rowIndex,
            this.data
        );
    },
    // --------------------------------------------------------------------------------
    // Displays the verification errors, if there are any.
    // @param errorList An array of error messages generated by this verification
    // --------------------------------------------------------------------------------
    displayVerificationErrors: function(errorList) {
        if ( errorList.length !== 0 ) {
            Ext.MessageBox.show({
                title: 'Error',
                msg: '<ul>' + errorList.join('\n<li>') + '</ul>',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR,
                fn: function() {
                    Ext.WindowMgr.get('rctsCrudWindowId').getEl().setStyle('z-index', '90000');
                } 
            });
            Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
            this.setAction('');
        }
    },
    // --------------------------------------------------------------------------------
    // Handles exceptions generated by the RC store.
    // @see Ext.data.DataProxy
    // --------------------------------------------------------------------------------
    storeException: function(proxy, type, action, options, response, arg) {
        // Prevents the window from being closed.
        this.setAction('');
        Ext.MessageBox.show({
            buttons: Ext.MessageBox.OK,
            fn: function() {
                Ext.WindowMgr.get('rctsCrudWindowId').getEl().setStyle('z-index', '90000');
            }, 
            icon: Ext.MessageBox.ERROR,
            msg: response.message,
            title: 'Error'
        });
        Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
    },
    // --------------------------------------------------------------------------------
    // Sets the action that the user is taking on this panel. This typically happens 
    // on a button press.
    // --------------------------------------------------------------------------------
    setAction: function(action) {
        this.data.set('action', action);
    }
});