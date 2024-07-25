// ================================================================================
// Dialog for performing CRUD operations reporter constructs.
// NOTE: In ExtJs 3.3.1, you cannot call Ext.data.Record::set(field, value) with
// value = null or there will be no change to the value of that record field.
// From the Record.js source code:
// set : function(name, value) {
//   var encode = Ext.isPrimitive(value) ? String : Ext.encode;
//   if (encode(this.data[name]) == encode(value))
//     return; // if null it always returns here
// ================================================================================
// Create a record that will be used to configure the store data fields and keep
// track of data in the dialog. This lets us store everything in one place.
// --------------------------------------------------------------------------------
REDfly.dialog.rcRecord = Ext.data.Record.create([
    { name: 'action', defaultValue: 'cancel' },
    { name: 'archive_date', allowBlank: true, defaultValue: '' },
    { name: 'archive_date_formatted', allowBlank: true, defaultValue: '' },
    { name: 'assayed_in_species_id' },
    { name: 'assayed_in_species_scientific_name' },
    { name: 'assayed_in_species_short_name' },
    { name: 'auditor_full_name', allowBlank: true, defaultValue: '' },
    { name: 'auditor_id', allowBlank: true, defaultValue: '' },
    { name: 'author_email', allowBlank: true, defaultValue: '' },
    { name: 'chromosome' },
    { name: 'chromosome_id' },
    { name: 'citation', allowBlank: true, defaultValue: '' },
    { name: 'curator_full_name' },
    { name: 'curator_id' },
    { name: 'current_genome_assembly_release_version' },
    { name: 'date_added' },
    { name: 'date_added_formatted' },
    { name: 'end' },
    { name: 'evidence_id' },
    { name: 'evidence_term' },
    // Anatomical expression terms returned from the REST service
    { name: 'anatomical_expression_terms', allowBlank: true, defaultValue: '' },
    // Anatomical expression term identfiers set in the user interface
    { name: 'anatomical_expression_term_ids', allowBlank: true, defaultValue: '' },
    { name: 'fbal', allowBlank: true, defaultValue: '' },
    { name: 'fbtp', allowBlank: true, defaultValue: '' },
    { name: 'figure_labels', allowBlank: true, defaultValue: '' },
    { name: 'gene_id' },
    { name: 'gene_identifier' },
    { name: 'gene_name' },
    { name: 'has_flyexpress_images' },
    { name: 'has_tfbs' },
    { name: 'is_crm', type: 'boolean' },
    { name: 'is_minimalized', type: 'boolean' },
    { name: 'is_negative', type: 'boolean' },
    { name: 'is_override', type: 'boolean' },
    { name: 'last_audit' },
    { name: 'last_audit_formatted' },
    { name: 'last_update' },
    { name: 'last_update_formatted' },
    { name: 'name' },
    { name: 'notes', allowBlank: true, defaultValue: '' },
    { name: 'pubmed_id' },
    { name: 'redfly_id', allowBlank: true, defaultValue: '' },
    { name: 'sequence', defaultValue: '' },
    { name: 'sequence_from_species_id' },
    { name: 'sequence_from_species_scientific_name' },
    { name: 'sequence_from_species_short_name' },
    { name: 'sequence_source_id' },
    { name: 'sequence_source_term' },
    { name: 'size' },
    // Staging data returned from the REST service
    { name: 'staging_data', allowBlank: true, defaultValue: '' },
    // Staging data set in the user interface
    { name: 'staging_data_ui', allowBlank: true, defaultValue: '' },
    { name: 'start' },
    { name: 'state', defaultValue: '' }
]);
// --------------------------------------------------------------------------------
// The rcStore JsonStore is used to load and save reporter constructs through the
// REST service.
// --------------------------------------------------------------------------------
REDfly.store.rcStore = new Ext.data.JsonStore({
    // Ext.data.JsonStore configs
    autoLoad: false,
    // Do not save dirty records until told to do so via button handler
    autoSave: false,
    proxy: new Ext.data.HttpProxy({
        api: {
            create: {
                method: 'POST',
                url: REDfly.config.apiUrl + '/jsonstore/reporterconstruct/save'
            },
            // No destroy for this store but the spec can not be undefined!
            destroy: {
                url: REDfly.config.apiUrl + '/jsonstore/reporterconstruct/delete'
            },
            read: {
                method: 'GET',
                url: REDfly.config.apiUrl + '/jsonstore/reporterconstruct/load'
            },
            update: {
                method: 'POST',
                url: REDfly.config.apiUrl + '/jsonstore/reporterconstruct/save'
            }
        }
    }),
    writer: new Ext.data.JsonWriter({
        encode: true,
        // Write all the fields, not just those that changed
        writeAllFields: true
    }),
    // Embedded Ext.data.JsonReader configs
    fields: REDfly.dialog.rcRecord,
    idProperty: 'redfly_id',
    messageProperty: 'message',
    root: 'results',
    successProperty: 'success',
    totalProperty: 'num'
});
// --------------------------------------------------------------------------------
// Create the new/edit RC dialog by extending Ext.FormPanel. This is used for
// CRUD operations on a reporter construct and contains functionality to
// automatically query citations and coordinates.
// --------------------------------------------------------------------------------
REDfly.component.rcPanel = Ext.extend(Ext.FormPanel, {
    // Ext.FormPanel configs
    bodyStyle: { backgroundColor: '#F1F1F1' },
    border: false,
    buttonAlign: 'center',
    defaults: { width: 550 },
    defaultType: 'textfield',
    labelWidth: 145,
    // If true, the form monitors its valid state client-side and regularly fires
    // the clientvalidation event passing that state. When monitoring valid state,
    // the FormPanel enables/disables any of its configured buttons which have been
    // configured with formBind: true depending on whether the form is valid or not.
    monitorValid: true,
    padding: 5,
    // REDfly configs
    // Note that multiple instances of this panel will access the same store so
    // be careful about onLoad handlers.
    citationStore: REDfly.store.citation,
    // Record to hold dialog information needed in multiple places and to save
    // data when editing is complete.
    data: null,
    // This object will hold all of the form elements for easy access
    formElements: {},
    store: REDfly.store.rcStore,
    userId: null,
    userFullName: null,
    initComponent: function() {
        // Turn on validation errors beside the field globally
        Ext.form.Field.prototype.msgTarget = 'side';
        // Enables the closewindow event generated by this class to be detected
        // by its parent class when the window being closed
        this.enableBubble('closewindow');
        this.data = new REDfly.dialog.rcRecord();
        //var stateItem = new Ext.form.DisplayField({
        //    fieldLabel: 'State',
        //    hidden: true,
        //    name: 'state',
        //    value: ''
        //});
        var redflyIdItem = new Ext.form.DisplayField({
            fieldLabel: 'REDfly ID',
            value: 'N/A'
        });
        var curatorDisplayItem = new REDfly.component.displayUser({
            // Ext.form.CompositeField configs
            fieldLabel: 'Curator',
            // REDfly configs
            fullName: this.userFullName,
            timeOrderWords: 'Added On'
        });
        var auditorDisplayItem = new REDfly.component.displayUser({
            // Ext.form.CompositeField configs
            fieldLabel: 'Auditor',
            // REDfly configs
            fullName: 'N/A',
            timeOrderWords: 'Last Audited On'
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
                            var id = field.getValue(),
                                url = 'http://www.ncbi.nlm.nih.gov/pubmed/' + id;
                            if ( Ext.isEmpty(id) ) {
                                Ext.MessageBox.show({
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                    msg: 'No Pubmed ID entered'.replace(' ', '&nbsp;'),
                                    title: 'Error'
                                });
                                Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
                            } else {
                                window.open(url);
                            }
                        },
                        this
                    );
                },
                invalid: function(field) { this.data.set('pubmed_id', ''); },
                scope: this,
                valid: function(field) {
                    // Only load data if the pmid has changed
                    if ( this.data.get('pubmed_id') !== field.getValue() ) {
                        this.formElements.transgenicConstruct.reset();
                        this.formElements.anatomicalExpressionTerms.setPubmedId(field.getValue());
                        this.data.set('pubmed_id', field.getValue());
                        this.data.set('fbtp', '');
                        this.data.set('fbal', '');
                        this.loadCitation(field.getValue());
                    }
                }
            },
            name: 'pubmed_id',
            regex: /^[0-9]+$/,
            regexText: 'Pubmed ID must be numeric'.replace(' ', '&nbsp;'),
            validationDelay: 500,
            validationEvent: 'blur',
            width: 300
        });
        var citationItem = new Ext.form.TextArea({
            allowBlank: false,
            disabled: true,
            fieldLabel: 'Citation',
            height: 100,
            name: 'citation',
            width: 400
        });
        var authorEmailItem = new Ext.form.TextField({
            allowBlank: true,
            fieldLabel: 'Author Email',
            name: 'author_email',
            validationEvent: 'blur',
            vtype: 'email',
            width: 300
        });
        var geneItem = new REDfly.component.selectGene();
        geneItem.allowBlank = false;
        geneItem.disable();
        var nameItem = new Ext.form.TextField({
            allowBlank: false,
            fieldLabel: 'Element Name',
            listeners: {
                blur: function(field) {
                    // Save what the user types
                    this.data.set('name', field.getValue());
                },
                scope: this
            },
            name: 'name',
            width: 300
        });
        nameItem.disable();
        var transgenicConstructItem = new REDfly.component.selectTransgenicConstruct({ pmidField: pubmedIdItem });
        transgenicConstructItem.disabled = true;
        var evidenceItem = new REDfly.component.selectEvidence();
        evidenceItem.allowBlank = false;
        var sequenceSourceItem = new REDfly.component.selectSequenceSource();
        sequenceSourceItem.allowBlank = false;
        // Record whether or not the entered sequence is valid.
        this.validSequence = true;
        // The sequence validator function does not have a scope option,
        // so this variable should be used in place of "this".
        var me = this;
        var sequenceItem = new Ext.form.TextArea({
            autoScroll: true,
            fieldLabel: 'Sequence',
            height: 150,
            invalidText: 'Invalid Sequence',
            listeners: {
                blur: function(field) {
                    if ( this.data.get('sequence') !== field.getValue() ) {
                        this.data.set('current_genome_assembly_release_version', this.formElements.sequenceFromSpecies.current_genome_assembly_release_version);
                        this.data.set('sequence', field.getValue());
                        this.searchCoordinates(
                            this.formElements.sequenceFromSpecies.short_name,
                            field.getValue()
                        );
                    }
                },
                scope: this
            },
            minLength: 20,
            name: 'sequence',
            // See http://www.genomatix.de/online_help/help/sequence_formats.html#IUPAC
            // for the source of this regex.
            regex: /^[ACGTURYKMSWBDHVN\s]{20,}$/i,
            regexText: 'Invalid sequence'.replace(' ', '&nbsp;'),
            validator: function() {
                if ( me.validSequence ) {
                    return true;
                } else {
                    return 'Invalid Sequence';
                }
            },
            validationEvent: 'blur',
            width: 400,
            // REDfly configs
            // This must be set in order to access the species field in the dialog
            speciesField: null
        });
        sequenceItem.allowBlank = false;
        sequenceItem.disable();
        var coordinatesItem = new REDfly.component.selectCoordinates();
        // The coordinates will be set automatically
        coordinatesItem.disable();
        var sequenceFromSpeciesItem = new REDfly.component.selectSequenceFromSpecies({
            geneField: geneItem,
            nameField: nameItem,
            transgenicConstructField: transgenicConstructItem,
            sequenceField: sequenceItem
        });
        sequenceFromSpeciesItem.allowBlank = false;
        geneItem.sequenceFromSpeciesField = sequenceFromSpeciesItem;
        transgenicConstructItem.sequenceFromSpeciesField = sequenceFromSpeciesItem;
        sequenceItem.sequenceFromSpeciesField = sequenceFromSpeciesItem;
        var curateAnatomicalExpressionTermsItem = new REDfly.component.curateRcAnatomicalExpressionTerms();
        curateAnatomicalExpressionTermsItem.disable();
        var assayedInSpeciesItem = new REDfly.component.selectAssayedInSpecies({
            curateAnatomicalExpressionTermsField: curateAnatomicalExpressionTermsItem
        });
        assayedInSpeciesItem.allowBlank = false;
        curateAnatomicalExpressionTermsItem.anatomicalExpressionTermSelect.assayedInSpeciesField = assayedInSpeciesItem;
        var curateStagingDataItem = new REDfly.component.curateRcStagingData();
        var notesItem = new Ext.form.TextArea({
            fieldLabel: 'Notes',
            name: 'notes',
            width: 400
        });
        var figureLabelsItem = new Ext.form.TextArea({
            fieldLabel: 'Figure Labels (separate labels with ^)',
            name: 'figure_labels',
            regex: /^[a-z0-9_\^\s]+$/i,
            regexText: 'Labels may contain letters, numbers, and underscores.<br>Separate labels with "^"'.replace(' ', '&nbsp;'),
            width: 400
        });
        var manualOverrideCheckboxItem = new Ext.form.Checkbox({
            boxLabel: 'Manual CRM Override',
            name: 'is_override'
        });
        var isCrmCheckboxItem = new Ext.form.Checkbox({
            boxLabel: 'Is CRM',
            disabled: true,
            name: 'is_crm'
        });
        var isNegativeCheckboxItem = new Ext.form.Checkbox({
            boxLabel: 'Negative',
            name: 'is_negative'
        });
        var isMinimalizedCheckboxItem = new Ext.form.Checkbox({
            boxLabel: 'Is Minimalized',
            disabled: true,
            name: 'is_minimized'
        });
        var twoCuratorCheckboxItem = new Ext.form.Checkbox({
            boxLabel: 'Checked by 2 Curators',
            hidden: ( this.mode === 'approve' ),
            name: 'two_curator_verification'
        });
        var buttonList = null;
        if ( this.mode === 'edit' ) {
            buttonList = [
                {
                    formBind: true,
                    handler: function(button, event) {
                        // Update the data in the form and
                        // save it as long as the form is valid.
                        this.setAction('save');
                        this.save();
                    },
                    scope: this,
                    text: 'Save'
                },
                {
                    formBind: true,
                    handler: function(button, event) {
                        this.setAction('save_new');
                        this.save();
                    },
                    scope: this,
                    text: 'Save, New'
                },
                {
                    formBind: true,
                    handler: function(button, event) {
                        this.setAction('save_new_based_on');
                        this.save();
                    },
                    scope: this,
                    text: 'Save, New Based On'
                },
                {
                    formBind: true,
                    handler: function(button, event) {
                        this.setAction('submit_for_approval');
                        this.save();
                    },
                    scope: this,
                    text: 'Submit for Approval'
                },
                {
                    formBind: true,
                    handler: function(button, event) {
                        this.setAction('mark_for_deletion');
                        this.save();
                    },
                    scope: this,
                    text: 'Mark for Deletion'
                },
                {
                    handler: function(button, event) {
                        this.setAction('cancel');
                        this.ownerCt.hide();
                    },
                    scope: this,
                    text: 'Cancel'
                }
            ];
        }
        // Apply the changes to the window including storing the form elements
        // under this.formElements for easy access later.
        Ext.apply(
            this,
            {
                items: [
                    redflyIdItem,
                    //stateItem,
                    curatorDisplayItem,
                    auditorDisplayItem,
                    sequenceFromSpeciesItem,
                    pubmedIdItem,
                    citationItem,
                    authorEmailItem,
                    geneItem,
                    nameItem,
                    transgenicConstructItem,
                    evidenceItem,
                    sequenceSourceItem,
                    sequenceItem,
                    coordinatesItem,
                    assayedInSpeciesItem,
                    curateAnatomicalExpressionTermsItem,
                    curateStagingDataItem,
                    notesItem,
                    figureLabelsItem,
                    {
                        columns: 2,
                        items: [
                            manualOverrideCheckboxItem,
                            isCrmCheckboxItem,
                            isNegativeCheckboxItem,
                            isMinimalizedCheckboxItem,
                            twoCuratorCheckboxItem
                        ],
                        width: 'auto',
                        xtype: 'checkboxgroup'
                    }
                ],
                buttons: buttonList,
                formElements: {
                    redflyId: redflyIdItem,
                    //state: stateItem,
                    curatorDisplay: curatorDisplayItem,
                    auditorDisplay: auditorDisplayItem,
                    sequenceFromSpecies: sequenceFromSpeciesItem,
                    pubmedId: pubmedIdItem,
                    citation: citationItem,
                    authorEmail: authorEmailItem,
                    gene: geneItem,
                    name: nameItem,
                    transgenicConstruct: transgenicConstructItem,
                    evidence: evidenceItem,
                    sequenceSource: sequenceSourceItem,
                    sequence: sequenceItem,
                    coordinates: coordinatesItem,
                    assayedInSpecies: assayedInSpeciesItem,
                    anatomicalExpressionTerms: curateAnatomicalExpressionTermsItem,
                    stagingData: curateStagingDataItem,
                    notes: notesItem,
                    figureLabels: figureLabelsItem,
                    isOverride: manualOverrideCheckboxItem,
                    isCrm: isCrmCheckboxItem,
                    isNegative: isNegativeCheckboxItem,
                    isMinimalized: isMinimalizedCheckboxItem,
                    twoCuratorVerification: twoCuratorCheckboxItem
                }
            }
        );
        REDfly.component.rcPanel.superclass.initComponent.apply(this, arguments);
        var selectSequenceFromSpeciesCallBack = function (newValue, oldValue)
        {
            if ( oldValue !== newValue ) {
                this.data.set('sequence', '');
                this.formElements.gene.reset();
                this.formElements.name.reset();
                this.formElements.transgenicConstruct.reset();
                this.formElements.sequence.reset();
                this.formElements.coordinates.reset();
            }
        };
        this.formElements.sequenceFromSpecies.on(
            'change',
            selectSequenceFromSpeciesCallBack,
            this
        );
        // Display a warning if no transgenic construct results were found for this PMID
        var transgenicConstructExceptionCallBack = function(proxy, type, action, options, response, arg)
        {
            Ext.MessageBox.show({
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING,
                msg: response.message.replace(' ', '&nbsp;'),
                title: 'Warning'
            });
            Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
        };
        this.formElements.transgenicConstruct.getStore().on(
            'exception',
            transgenicConstructExceptionCallBack
        );
        // Use a delegate to pass a message for the user into the callback
        this.citationStore.on(
            'beforeload',
            this.showWaitMessage.createDelegate(
                this.citationStore,
                ['Loading citation data, please wait...']
            ),
            this
        );
        // Add a listener to the citation store so that the citation is
        // displayed in the text area when a pubmed id is provided.
        var displayCitationCallBack = function(
            store,
            records,
            options
        ) {
            var contents = '';
            var email = '';
            if ( records.length !== 0 ) {
                contents = records[0].data.contents;
                email = records[0].data.author_email;
            }
            this.formElements.citation.setValue(contents);
            var emailField = this.formElements.authorEmail;
            emailField.setValue(email);
            email === null
                ? emailField.enable()
                : emailField.disable();
            this.data.set('citation', contents);
            this.data.set('author_email', email);
        };
        this.citationStore.on(
            'load',
            displayCitationCallBack,
            this
        );
        this.citationStore.on(
            'load',
            this.hideWaitMessage
        );
        // Handle errors during the citation loading
        var citationExceptionCallBack = function(
            proxy,
            type,
            action,
            options,
            response,
            arg
        ) {
            this.formElements.citation.setValue('');
            Ext.MessageBox.show({
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR,
                msg: ('No citation found for PMID ' + options.params.external_id +
                    ': ' + response.message).replace(' ', '&nbsp;'),
                title: 'Warning'
            });
            Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
        };
        this.citationStore.on(
            'exception',
            citationExceptionCallBack,
            this
        );
        REDfly.store.blatsearch.on(
            'beforeload',
            function() {
                // Clear coordinate fields and data.
                this.formElements.coordinates.reset();
                this.data.set('chromosome', '');
                this.data.set('chromosome_id', '');
                this.data.set('start', '');
                this.data.set('end', '');
                // Mark sequence as invalid until coordinates are updated.
                this.validSequence = false;
                this.formElements.sequence.markInvalid();
                this.formElements.sequence.validate();
                this.showWaitMessage('Loading coordinates, please wait...');
            },
            this
        );
        // Add a listener to the coordinates store so that the coordinates
        // are updated when a call to the Dockerized server BLAT is made.
        REDfly.store.blatsearch.on(
            'load',
            this.searchCoordinatesCallBack,
            this
        );
        REDfly.store.blatsearch.on(
            'exception',
            this.blatExceptionCallBack,
            this
        );
        geneItem.on(
            'select',
            this.selectGeneCallBack,
            this
        );
        var selectAssayedInSpeciesCallBack = function (newValue, oldValue)
        {
            if ( oldValue !== newValue ) {
                this.formElements.anatomicalExpressionTerms.store.removeAll();
                this.formElements.stagingData.store.removeAll();
            }
        };
        this.formElements.assayedInSpecies.on(
            'change',
            selectAssayedInSpeciesCallBack,
            this
        );
        // Virtually deletes the staging data records associated to the anatomical expression
        // being deleted.
        // Such staging data records virtually deleted are finally deleted in the database after
        // anyone from the buttons (but the Cancel button) at the bottom of the RC editing panel
        // is pushed
        curateAnatomicalExpressionTermsItem.on(
            'deleteanatomicalexpression',
            function(anatomicalExpressionIdentifier) {
                var stagingDataRecordsToBeRemoved = [], index = 0;
                this.formElements.stagingData.store.each(function(record) {
                    if ( record.get('anatomical_expression_identifier') === anatomicalExpressionIdentifier ) {
                        stagingDataRecordsToBeRemoved[index++] = record;
                    }
                });
                for (index = 0;
                    index < stagingDataRecordsToBeRemoved.length;
                    index++) {
                    this.formElements.stagingData.store.remove(stagingDataRecordsToBeRemoved[index]);
                }
            },
            this
        );
        curateAnatomicalExpressionTermsItem.on(
            'newstagingdata',
            function(stagingData) {
                var stagingDataRecord = Ext.data.Record.create([
                    {   name: 'ts_id',
                        allowBlank: true,
                        defaultValue: ''    },
                    {   name: 'rc_id',
                        allowBlank: true,
                        defaultValue: ''   },
                    {   name: 'anatomical_expression_identifier'   },
                    {   name: 'anatomical_expression_term'   },
                    {   name: 'anatomical_expression_display'  },
                    {   name: 'pubmed_id'   },
                    {   name: 'stage_on_id' },
                    {   name: 'stage_on_identifier' },
                    {   name: 'stage_on_term' },
                    {   name: 'stage_on_display' },
                    {   name: 'stage_off_id'    },
                    {   name: 'stage_off_identifier'    },
                    {   name: 'stage_off_term' },
                    {   name: 'stage_off_display' },
                    {   name: 'biological_process_id',
                        allowBlank: true,
                        defaultValue: ''    },
                    {   name: 'biological_process_identifier',
                        allowBlank: true,
                        defaultValue: ''    },
                    {   name: 'biological_process_term',
                        allowBlank: true,
                        defaultValue: ''    },
                    {   name: 'biological_process_display',
                        allowBlank: true,
                        defaultValue: ''    },
                    {   name: 'sex_id'  },
                    {   name: 'sex_term'  },
                    {   name: 'ectopic_id'  },
                    {   name: 'ectopic_term'  },
                    {   name: 'enhancer_or_silencer_attribute_id',
                        defaultValue: 'enhancer'   },
                    {   name: 'enhancer_or_silencer_attribute_term',
                        defaultValue: 'Enhancer'   }
                ]);
                var newStagingDataRecord = new stagingDataRecord({
                    ts_id: stagingData.get('ts_id'),
                    rc_id: stagingData.get('rc_id'),
                    anatomical_expression_identifier: stagingData.get('anatomical_expression_identifier'),
                    anatomical_expression_term: stagingData.get('anatomical_expression_term'),
                    anatomical_expression_display: stagingData.get('anatomical_expression_display'),
                    pubmed_id: stagingData.get('pubmed_id'),
                    stage_on_id: stagingData.get('stage_on_id'),
                    stage_on_identifier: stagingData.get('stage_on_identifier'),
                    stage_on_term: stagingData.get('stage_on_term'),
                    stage_on_display: stagingData.get('stage_on_display'),
                    stage_off_id: stagingData.get('stage_off_id'),
                    stage_off_identifier: stagingData.get('stage_off_identifier'),
                    stage_off_term: stagingData.get('stage_off_term'),
                    stage_off_display: stagingData.get('stage_off_display'),
                    biological_process_id: stagingData.get('biological_process_id'),
                    biological_process_identifier: stagingData.get('biological_process_identifier'),
                    biological_process_term: stagingData.get('biological_process_term'),
                    biological_process_display: stagingData.get('biological_process_display'),
                    sex_id: stagingData.get('sex_id'),
                    sex_term: stagingData.get('sex_term'),
                    ectopic_id: stagingData.get('ectopic_id'),
                    ectopic_term: stagingData.get('ectopic_term'),
                    enhancer_or_silencer_attribute_id: stagingData.get('enhancer_or_silencer_attribute_id'),
                    enhancer_or_silencer_attribute_term: stagingData.get('enhancer_or_silencer_attribute_term')
                });
                // A record should only be in one store, so make a copy.
                this.formElements.stagingData.store.add(newStagingDataRecord.copy());
            },
            this
        );
        curateStagingDataItem.on(
            'existingstagingdata',
            function(recordIndex, stagingData) {
                // It is going to be modified by reference, baby!
                var existingStagingDataRecord = this.formElements.stagingData.store.getAt(recordIndex);
                existingStagingDataRecord.beginEdit();
                existingStagingDataRecord.set('ts_id', stagingData.get('ts_id'));
                existingStagingDataRecord.set('rc_id', stagingData.get('rc_id'));
                existingStagingDataRecord.set('pubmed_id', stagingData.get('pubmed_id'));
                existingStagingDataRecord.set('assayed_in_species_id', stagingData.get('assayed_in_species_id'));
                existingStagingDataRecord.set('stage_on_id', stagingData.get('stage_on_id'));
                existingStagingDataRecord.set('stage_on_identifier',  stagingData.get('stage_on_identifier'));
                existingStagingDataRecord.set('stage_on_term', stagingData.get('stage_on_term'));
                existingStagingDataRecord.set('stage_on_display', stagingData.get('stage_on_display'));
                existingStagingDataRecord.set('stage_off_id', stagingData.get('stage_off_id'));
                existingStagingDataRecord.set('stage_off_identifier', stagingData.get('stage_off_identifier'));
                existingStagingDataRecord.set('stage_off_term', stagingData.get('stage_off_term'));
                existingStagingDataRecord.set('stage_off_display', stagingData.get('stage_off_display'));
                existingStagingDataRecord.set('biological_process_id', stagingData.get('biological_process_id'));
                existingStagingDataRecord.set('biological_process_identifier', stagingData.get('biological_process_identifier'));
                existingStagingDataRecord.set('biological_process_term', stagingData.get('biological_process_term'));
                existingStagingDataRecord.set('biological_process_display', stagingData.get('biological_process_display'));
                existingStagingDataRecord.set('sex_id', stagingData.get('sex_id'));
                existingStagingDataRecord.set('sex_term', stagingData.get('sex_term'));
                existingStagingDataRecord.set('ectopic_id', stagingData.get('ectopic_id'));
                existingStagingDataRecord.set('ectopic_term', stagingData.get('ectopic_term'));
                existingStagingDataRecord.set('enhancer_or_silencer_attribute_id', stagingData.get('enhancer_or_silencer_attribute_id'));
                existingStagingDataRecord.set('enhancer_or_silencer_attribute_term', stagingData.get('enhancer_or_silencer_attribute_term'));
                existingStagingDataRecord.endEdit();
                existingStagingDataRecord.commit(false);
            },
            this
        );
        // Since AJAX calls are async, add a listener to the store so the data
        // in the window will be populated on load.
        this.store.on(
            'load',
            this.loadFormFromRecord,
            this
        );
        this.store.on(
            'save',
            this.storeSaveCallBack,
            this
        );
        this.store.on(
            'exception',
            this.storeExceptionCallBack,
            this
        );
    },
    // --------------------------------------------------------------------------------
    // Display a wait message to the user while contacting external services/sites
    // @param message The message to be displayed on the dialog
    // --------------------------------------------------------------------------------
    showWaitMessage: function(message) {
        Ext.MessageBox.show({
            msg: message,
            progressText: 'Loading...',
            wait: true,
            waitConfig: { interval: 200 },
            width: 300
        });
        Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
    },
    // --------------------------------------------------------------------------------
    // Show a generic message to the user
    // @param title The dialog title
    // @param message The message to be displayed in the dialog
    // --------------------------------------------------------------------------------
    showMessage: function(title, message) {
        Ext.MessageBox.show({
            msg: message,
            title: title
        });
        Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
    },
    // --------------------------------------------------------------------------------
    // Hide any visible messages
    // --------------------------------------------------------------------------------
    hideWaitMessage: function() {
        Ext.MessageBox.hide();
    },
    // --------------------------------------------------------------------------------
    // Load data from the store into the form.
    // --------------------------------------------------------------------------------
    loadFormFromRecord: function(store, recordList, opt) {
        if ( recordList.length !== 1 ) { return true; }
        var formRecord = recordList[0];
        this.data = formRecord;
        this.getForm().loadRecord(formRecord);
        this.formElements.redflyId.setValue(formRecord.get('redfly_id'));
        if ( (formRecord.get('date_added') === formRecord.get('last_update')) ||
            (formRecord.get('last_update') === null) ) {
            this.formElements.curatorDisplay.update({
                fullName: formRecord.get('curator_full_name'),
                timeOrderWords: 'Added On',
                formattedDate: formRecord.get('date_added_formatted')
            });
        } else {
            this.formElements.curatorDisplay.update({
                fullName: formRecord.get('curator_full_name'),
                timeOrderWords: 'Last Updated On',
                formattedDate: formRecord.get('last_update_formatted')
            });
        }
        if ( formRecord.get('last_audit') === null ) {
            this.formElements.auditorDisplay.update({
                fullName: formRecord.get('auditor_full_name'),
                timeOrderWords: '',
                formattedDate: ''
            });
        } else {
            this.formElements.auditorDisplay.update({
                fullName: formRecord.get('auditor_full_name'),
                timeOrderWords: 'Last Audited On',
                formattedDate: formRecord.get('last_audit_formatted')
            });
        }
        this.loadSequenceFromSpecies(formRecord.get('sequence_from_species_id'));
        this.loadGene(formRecord.get('gene_id'));
        this.formElements.gene.enable();
        this.formElements.name.enable();
        this.loadTransgenicConstruct(formRecord.get('fbtp'));
        this.formElements.transgenicConstruct.enable();
        this.loadEvidence(formRecord.get('evidence_id'));
        this.loadSequenceSource(formRecord.get('sequence_source_id'));
        this.formElements.sequence.enable();
        this.formElements.coordinates.setChromosome(formRecord.get('chromosome'));
        this.formElements.coordinates.setStart(formRecord.get('start'));
        this.formElements.coordinates.setEnd(formRecord.get('end'));
        this.loadAssayedInSpecies(formRecord.get('assayed_in_species_id'));
        this.setAnatomicalExpressionTerms(formRecord.get('anatomical_expression_terms'));
        this.formElements.anatomicalExpressionTerms.setRcId(recordList[0].json.id);
        this.formElements.anatomicalExpressionTerms.setPubmedId(formRecord.get('pubmed_id'));
        this.formElements.anatomicalExpressionTerms.enable();
        this.setStagingData(formRecord.get('staging_data'));
        this.doLayout();
    },
    // --------------------------------------------------------------------------------
    // Load the citation from the database or NCBI through the citation store
    // --------------------------------------------------------------------------------
    loadCitation: function(pubmedId) {
        if ( pubmedId ) {
            this.citationStore.load({ params: { external_id: pubmedId } });
        }
    },
    // --------------------------------------------------------------------------------
    // Search any coordinates from the Dockerized BLAT server through the
    // REDfly.store.blatsearch store.
    // @param {String} speciesShortName The species short name to choose the genome
    //   database for the query.
    // @param {String} sequence The sequence to use in the query.
    // --------------------------------------------------------------------------------
    searchCoordinates: function(
        speciesShortName,
        sequence
    ) {
        if ( sequence.length < 20 ) {
            Ext.MessageBox.show({
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING,
                msg: 'The sequence must contain at least 20bp'.replace(' ', '&nbsp;'),
                title: 'Warning'
            });
            Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
        } else {
            REDfly.store.blatsearch.load({ params: {
                speciesShortName: speciesShortName,
                sequence: sequence
            } });
        }
    },
    // --------------------------------------------------------------------------------
    // Update the coordinates after REDfly.store.blatsearch loads.
    // @see Ext.data.Store (@event load)
    // --------------------------------------------------------------------------------
    searchCoordinatesCallBack: function(store, records, options) {
        this.hideWaitMessage();
        if ( records.length === 0 ) {
            Ext.MessageBox.show({
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING,
                msg: 'No sequence match from the BLAT server'.replace(' ', '&nbsp;'),
                title: 'Warning'
            });
            Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
        } else {
            if ( records.length === 1 ) {
                this.displayCoordinates(records[0]);
            } else {
                var multiplesBlatMatchesWindow = new REDfly.component.blatInfo({
                    listeners: {
                        recordselected: this.displayCoordinates,
                        scope: this
                    },
                    store: store
                });
                multiplesBlatMatchesWindow.getEl().setStyle('z-index', '1000000');
            }
        }
    },
    // --------------------------------------------------------------------------------
    // Display the provided coordinates.
    // As a side effect this method also clears any validation errors
    // from the sequence input field.
    // @param {Object} record A data record from REDfly.store.blatsearch.
    // --------------------------------------------------------------------------------
    displayCoordinates: function(record) {
        this.formElements.coordinates.setChromosome(record.data.chromosome);
        this.formElements.coordinates.setStart(record.data.start);
        this.formElements.coordinates.setEnd(record.data.end);
        this.data.set('chromosome', record.data.chromosome);
        this.data.set('chromosome_id', record.data.chromosome_id);
        this.data.set('end', record.data.end);
        this.data.set('start', record.data.start);
        this.validSequence = true;
        this.formElements.sequence.clearInvalid();
        this.formElements.sequence.validate();
    },
    // --------------------------------------------------------------------------------
    // Handle exceptions from REDfly.store.blatsearch.
    // Marks the sequence as invalid and displays an error message.
    // @see Ext.data.Store (@event exception)
    // --------------------------------------------------------------------------------
    blatExceptionCallBack: function(proxy, type, action, options, response, arg) {
        this.validSequence = false;
        this.formElements.sequence.markInvalid();
        this.formElements.sequence.validate();
        Ext.MessageBox.show({
            buttons: Ext.MessageBox.OK,
            icon: Ext.MessageBox.ERROR,
            msg: response.message.replace(' ', '&nbsp;'),
            title: 'Error loading coordinates'
        });
        Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
    },
    // --------------------------------------------------------------------------------
    // Load the "Sequence From" species store and set the selected species.
    // Also updates the data record species_id and species_term.
    // @param {Number} speciesId The id number of the species to select.
    // --------------------------------------------------------------------------------
    loadSequenceFromSpecies: function(speciesId) {
        this.formElements.sequenceFromSpecies.store.load({
            callback: function() {
                this.formElements.sequenceFromSpecies.setValue(speciesId);
                var record = this.formElements.sequenceFromSpecies.getStore().getById(speciesId);
                this.data.set('current_genome_assembly_release_version', record.data.current_genome_assembly_release_version);
                this.formElements.sequenceFromSpecies.current_genome_assembly_release_version = record.data.current_genome_assembly_release_version;
                this.data.set('sequence_from_species_id', record.data.id);
                this.formElements.sequenceFromSpecies.id = record.data.id;
                this.data.set('sequence_from_species_scientific_name', record.data.scientific_name);
                this.formElements.sequenceFromSpecies.scientific_name = record.data.scientific_name;
                this.data.set('sequence_from_species_short_name', record.data.short_name);
                this.formElements.sequenceFromSpecies.short_name = record.data.short_name;
            },
            scope: this
        });
    },
    // --------------------------------------------------------------------------------
    // Load the "Assayed In" species store and set the selected species.
    // Also updates the data record species_id and species_term.
    // @param {Number} speciesId The id number of the species to select.
    // --------------------------------------------------------------------------------
    loadAssayedInSpecies: function(speciesId) {
        this.formElements.assayedInSpecies.store.load({
            callback: function() {
                this.formElements.assayedInSpecies.setValue(speciesId);
                var record = this.formElements.assayedInSpecies.getStore().getById(speciesId);
                this.data.set('assayed_in_species_id', record.data.id);
                this.formElements.assayedInSpecies.id = record.data.id;
                this.data.set('assayed_in_species_scientific_name', record.data.scientific_name);
                this.formElements.assayedInSpecies.scientific_name = record.data.scientific_name;
                this.data.set('assayed_in_species_short_name', record.data.short_name);
                this.formElements.assayedInSpecies.short_name = record.data.short_name;
            },
            scope: this
        });
    },
    // --------------------------------------------------------------------------------
    // Load the gene store and set the selected gene.
    // Also updates the data record: gene_id, gene_identifier, and gene_name.
    // @param {Number} geneId The id number of the gene to select.
    // --------------------------------------------------------------------------------
    loadGene: function(geneId) {
        this.formElements.gene.store.load({
            params: { id: geneId },
            callback: function() {
                this.formElements.gene.setValue(geneId);
                var record = this.formElements.gene.getStore().getById(geneId);
                this.data.set('gene_id', record.data.id);
                this.data.set('gene_identifier', record.data.identifier);
                var oldGeneName = this.data.get('gene_name');
                this.data.set('gene_name', record.data.name);
                this.generateElementName(oldGeneName);
            },
            scope: this
        });
    },
    // --------------------------------------------------------------------------------
    // Load the transgenicConstruct store and set the selected transgenic construct.
    // Also updates the data record fbtp.
    // @param {String} transgenicConstruct The transgenic construct to be selected.
    // --------------------------------------------------------------------------------
    loadTransgenicConstruct: function(transgenicConstruct) {
        this.formElements.transgenicConstruct.store.load({
            params: { pmid: this.formElements.pubmedId.getValue() },
            callback: function() {
                this.formElements.transgenicConstruct.setValue(transgenicConstruct);
                this.data.set('fbtp', transgenicConstruct);
            },
            scope: this
        });
    },
    // --------------------------------------------------------------------------------
    // Load a single evidence term and set the selected evidence.
    // Also updates the data record evidence_id and evidence_term.
    // @param {String} evidenceId The evidence id.
    // --------------------------------------------------------------------------------
    loadEvidence: function(evidenceId) {
        this.formElements.evidence.store.load({
            callback: function() {
                this.formElements.evidence.setValue(evidenceId);
                var record = this.formElements.evidence.getStore().getById(evidenceId);
                this.data.set('evidence_id', record.data.id);
                this.data.set('evidence_term', record.data.term);
            },
            scope: this
        });
    },
    // --------------------------------------------------------------------------------
    // Load the sequence source store and set the selected sequence source.
    // Also updates the data record sequence_source_id and sequence_source_term.
    // @param {String} seqSourceId The sequence source id.
    // --------------------------------------------------------------------------------
    loadSequenceSource: function(sequenceSourceId) {
        this.formElements.sequenceSource.store.load({
            callback: function() {
                this.formElements.sequenceSource.setValue(sequenceSourceId);
                var record = this.formElements.sequenceSource.getStore().getById(sequenceSourceId);
                this.data.set('sequence_source_id', record.data.id);
                this.data.set('sequence_source_term', record.data.term);
            },
            scope: this
        });
    },
    // --------------------------------------------------------------------------------
    // Store the gene name and id when the user makes a selection in the dropdown.
    // This lets us store both items in the internal data record.
    // @param {Ext.form.ComboBox} combo The gene search combobox.
    // @param {Ext.data.Record} record The gene data record.
    // @param {Number} index The index of the selected item.
    // --------------------------------------------------------------------------------
    selectGeneCallBack: function(combo, record, index) {
        var oldGeneName = this.data.get('gene_name');
        this.data.set('gene_name', record.data.name);
        this.generateElementName(oldGeneName);
    },
    // --------------------------------------------------------------------------------
    // Generate the element name in the format: elementName = geneName_arbitraryName
    // when a new gene name is chosen by the curator even if there is
    // no arbitrary name, yet.
    // --------------------------------------------------------------------------------
    generateElementName: function(oldGeneName) {
        var geneName = this.data.get('gene_name');
        var elementName = this.data.get('name');
        if ( elementName === '' ) {
            elementName = geneName + '_';
        } else {
            if ( (oldGeneName !== '') &&
                (oldGeneName !== geneName) ) {
                elementName = geneName + '_' + elementName.substring(oldGeneName.length + 1);
            } else {
                elementName = geneName + '_' + elementName.substring(geneName.length + 1);
            }
        }
        this.formElements.name.setValue(elementName);
        this.data.set('name', elementName);
    },
    // --------------------------------------------------------------------------------
    // Reset the current form and load any data specified by the redfly id.
    // --------------------------------------------------------------------------------
    load: function(redflyId) {
        // Since we are currently loading on the "show" event make sure to clear
        // the form if no redfly id was provided or we will be showing whatever
        // was loaded the list time the window was displayed.
        this.reset();
        if ( ! Ext.isEmpty(redflyId) ) {
            this.store.load({
                params: { redfly_id: redflyId }
            });
        }
    },
    // --------------------------------------------------------------------------------
    // Apply general verification of the data in the dialog. This may include
    // local validation as well as asynchronous AJAX calls to the server to
    // verify the uniqueness of the element name and discover any coordinate
    // duplicate which can be override by the curator.
    // @param config An object containing configuration information. At the
    //   very least it must contain failure: and success: properties that are
    //   functions to be called on failure and success.
    //   Additional fields may be added to the object by verification routines along
    //   the way
    // @return True on successful verification, false otherwise.
    // --------------------------------------------------------------------------------
    verify: function(config) {
        var errorList = [];
        // First verify the local elements before making any asynchronous AJAX call
        if ( ! this.verifyLocalFormElements(errorList) ) {
            config.failure.call(this, errorList);
            return false;
        }
        config.errorList = errorList;
        this.verifyRemoteFormElements(config);
    },
    // --------------------------------------------------------------------------------
    // Verify local form elements.
    // @param errorList An array of error messages generated by this verification
    // @returns True on success and false if there was a verification error
    // --------------------------------------------------------------------------------
    verifyLocalFormElements: function(errorList) {
        // Verify the data beyond the existence of local form elements
        var geneName = this.data.get('gene_name');
        // Error: the gene name is empty
        if ( geneName === '' ) {
            errorList.push('The gene name is empty.');
        }
        // Error: the name does not start with both gene name and underscore
        if ( this.formElements.name.getValue().substr(0, geneName.length + 1) !== (geneName + '_') ) {
            errorList.push('The name does not start with both gene name \"' + geneName + '\" and underscore.');
        }
        // Error: the name does not end with any arbitrary name
        if ( this.formElements.name.getValue().substr(geneName.length + 1) === '' ) {
            errorList.push('The name does not end with any arbitrary name.');
        }
        // Error: is_negative is checked and anatomical expression terms are selected
        if ( this.formElements.isNegative.getValue() &&
            (this.formElements.anatomicalExpressionTerms.store.getCount() !== 0) ) {
            errorList.push('Negative RCs may not have anatomical expression terms.');
        }
        // Error: submit for approval and two_curator_verification is not checked
        if ( (this.data.get('action') === 'submit_for_approval') &&
            (!this.formElements.twoCuratorVerification.getValue()) ) {
            errorList.push('Two curators must verify the data before submitting for approval.');
        }
        // Error: mark for deletion an entity having the "current" state from a non-administrative user
        if ( (this.data.get('action') === 'mark_for_deletion') &&
            (this.data.get('state') === 'current') &&
            (REDfly.config.isAdmin !== 1)) {
            errorList.push('Entities with the \"current\" state can be marked for deletion only by an administrator.');
        }
        // Error: edit an archived entity
        if ( this.data.get('state') === 'archived' ) {
            errorList.push('Entities with the \"archived\" state can not be edited at any moment.');
        }
        return ( errorList.length === 0 );
    },
    // --------------------------------------------------------------------------------
    // Hit the server to verify that
    //      1) the element name is unique.
    //      2) there is no coordinate falling in the interval minus/plus the error
    //         margin of other RC(s), although it can be overrode by the curator.
    // @param errorList An array of error messages generated by this verification
    // @returns True on success and false if there was a verification error
    // --------------------------------------------------------------------------------
    verifyRemoteFormElements: function(config) {
        Ext.Ajax.on(
            'beforerequest',
            this.showWaitMessage.createDelegate(
                Ext.Ajax,
                ['Checking for any duplicate kind...']
            ),
            this,
            { single: true }
        );
        Ext.Ajax.on(
            'requestcomplete',
            this.hideWaitMessage,
            this
        );
        Ext.Ajax.on(
            'requestexception',
            this.hideWaitMessage,
            this
        );
        // Save the "this" reference so we do not refer to the Ajax object.
        var verifyRemoteFormElementsFunction = this;
        Ext.Ajax.request({
            failure:  function(response, opts) {
                config.failure(Ext.util.JSON.decode(response.responseText));
            },
            method: 'GET',
            params: {
                chromosome_id: this.data.get('chromosome_id'),
                end: this.data.get('end'),
                name: this.formElements.name.getValue(),
                redfly_id: Ext.isEmpty(this.data.get('redfly_id')) ? null : this.data.get('redfly_id'),
                start: this.data.get('start')
            },
            scope: this,
            success: function(response, opts) {
                var returnValue = Ext.util.JSON.decode(response.responseText);
                if ( returnValue.success ) {
                    duplicateKinds = returnValue.results[0];
                    if ( duplicateKinds.elementNameDuplicateDetected === true ) {
                        Ext.MessageBox.show({
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.INFO,
                            msg: duplicateKinds.elementNameDuplicateMessage,
                            title: 'Found Element Name Duplicate'
                        });
                        Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
                    } else {
                        if ( duplicateKinds.coordinateDuplicateDetected === true ) {
                            Ext.MessageBox.show({
                                buttons: Ext.MessageBox.YESNO,
                                fn: function(btn) {
                                    if ( btn === 'yes' ) {
                                        // We need to use call() so that the callback gets the correct scope
                                        config.success.call(verifyRemoteFormElementsFunction);
                                    }
                                },
                                icon: Ext.MessageBox.QUESTION,
                                msg: duplicateKinds.coordinateDuplicateMessage +
                                    '.<br><br>Do you want to Override (Yes) or Continue editing (No)?',
                                title: 'Found Coordinates Duplicate(s)'
                            });
                            Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
                        } else {
                            // We need to use call() so that the callback gets the correct scope
                            config.success.call(verifyRemoteFormElementsFunction);
                        }
                    }
                } else {
                    config.failure(Ext.util.JSON.decode(response.responseText));
                }
            },
            url: REDfly.config.apiUrl + '/jsonstore/reporterconstruct/checkAnyDuplicateKind'
        });
    },
    // --------------------------------------------------------------------------------
    // Display verification errors, if there are any.
    // @param errorList An array of error messages generated by this verification
    // @returns True on success and false if there was a verification error
    // --------------------------------------------------------------------------------
    displayVerificationErrors: function(errorList) {
        if ( errorList.length !== 0 ) {
            errorMessage = '<ul>';
            errorList.forEach(function(error) {
                errorMessage += '<li>' + error + '</li>';
            });
            errorMessage += '</ul>';
            Ext.MessageBox.show({
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR,
                msg: errorMessage,
                title: 'Error',
                width: 500
            });
            Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
            this.setAction('');
        }
    },
    // --------------------------------------------------------------------------------
    // Reset the dialog but do not clear any data stored in the data record.
    // --------------------------------------------------------------------------------
    reset: function() {
        this.getForm().reset();
        this.store.removeAll(true);
        this.formElements.anatomicalExpressionTerms.store.removeAll();
        this.formElements.stagingData.store.removeAll();
        // Do not fire the "clear" action or it will trigger the "destroy" REST call
        this.citationStore.removeAll(true);
        this.data = new REDfly.dialog.rcRecord({
            action: 'cancel',
            curator: this.userFullName,
            curator_id: this.userId
        });
        // Set all the undefined values to an empty string so the form clears them out
        // when we update it.
        this.normalizeEmptyRecordFields(this.data);
        this.getForm().loadRecord(this.data);
        this.formElements.redflyId.setValue( Ext.isEmpty(this.data.get('redfly_id'))
            ? 'N/A'
            : this.data.get('redfly_id') );
    },
    // --------------------------------------------------------------------------------
    // Save data and send it back to the server after verification
    // --------------------------------------------------------------------------------
    save: function(redflyIdList) {
        // When saving a RC for approval we want to be able to send the list of
        // REDfly identifiers that were considered to the save method so they can be
        // removed after the merge. To do this we add a parameter to the store that
        // contains a serialized list.
        // If this is not an approval be sure to set the list to empty/null!
        if ( (this.data.get('action') === 'approve') &&
             (arguments.length === 1) &&
             (redflyIdList.length !== 0 )) {
            this.store.setBaseParam('redfly_id_list', Ext.util.JSON.encode(redflyIdList));
        } else {
            this.store.setBaseParam('redfly_id_list', null);
        }
        this.updateDataRecordFromForm();
        this.verify({
            failure: this.displayVerificationErrors,
            success: this._save
        });
    },
    // --------------------------------------------------------------------------------
    // Perform the actual saving of the data. This should be called after all
    // (possibly asynchronous) verification is successfully completed.
    // --------------------------------------------------------------------------------
    _save: function() {
        this.showWaitMessage('Saving...');
        // Since we are only saving a single record at a time,
        //  1) remove the current record from the store and,
        //  2) add the new one
        // without firing the clear event.
        this.store.removeAll(true);
        this.store.add(this.data);
        this.store.addListener(
            'save',
            function () {
                if ( REDfly.component.approvalState &&
                    REDfly.component.notifyAuthor ) {
                    Ext.Ajax.request({
                        failure: function(response, opts) {
                            var errorMessage = 'Notification Error: ' + response.statusText;
                            Ext.MessageBox.show({
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR,
                                msg: errorMessage.replace(' ', '&nbsp;'),
                                title: 'Error'
                            });
                            Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
                        },
                        jsonData: {
                            pubmed_ids: [
                                Number(this.data.get('pubmed_id'))
                            ]
                        },
                        method: 'POST',
                        success: function(response, opts) {
                            var returnValue = Ext.util.JSON.decode(response.responseText);
                            if ( returnValue.success ) {
                                var message = '';
                                for (var index = 0;
                                    index < returnValue.results.length;
                                    index++) {
                                    var authorNotificationState = returnValue.results[index];
                                    if ( authorNotificationState.emailAddress === '' ) {
                                        message += 'The author(s) of the publication (PMID:' +
                                        authorNotificationState.externalId +
                                        ') do not have any email address to be notified.<br>';
                                    } else {
                                        if ( authorNotificationState.contacted === true ) {
                                            message += 'The author(s) of the publication (PMID:' +
                                                authorNotificationState.externalId +
                                                ') have already been notified on ' +
                                                authorNotificationState.contactDate +
                                                '.<br>';
                                        } else {
                                            if ( authorNotificationState.approvalEntityNames !== '' ) {
                                                message += 'There are still one or more entries: ' +
                                                    authorNotificationState.approvalEntityNames +
                                                    ' with the approval state belonging to the publication (PMID:' +
                                                    authorNotificationState.externalId +
                                                    ').<br>';
                                            } else {
                                                if ( authorNotificationState.emailed === true ) {
                                                    message += 'The author(s) of the publication (PMID:' +
                                                        authorNotificationState.externalId +
                                                        ') have just been notified by email: ' +
                                                        authorNotificationState.emailAddress +
                                                        '.<br>';
                                                } else {
                                                    message += 'The notification status of the author(s) of the publication (PMID:' +
                                                        authorNotificationState.externalId +
                                                        ') is unknown.<br>';
                                                }
                                            }
                                        }
                                    }
                                }
                                Ext.MessageBox.show({
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.INFO,
                                    msg: message,
                                    title: 'Success'
                                });
                                Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
                            } else {
                                var errorMessage = 'Notification Error: ' + response.statusText;
                                Ext.MessageBox.show({
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.ERROR,
                                    msg: errorMessage.replace(' ', '&nbsp;'),
                                    title: 'Error'
                                });
                                Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
                            }
                        },
                        url: REDfly.config.baseUrl + 'api/v2/audit/notify/authors'
                    });
                }
                REDfly.component.approvalState = false;
            },
            this
        );
        this.store.save();
    },
    // --------------------------------------------------------------------------------
    // Handle exceptions generated by the RC store.
    // @see Ext.data.DataProxy
    // --------------------------------------------------------------------------------
    storeExceptionCallBack: function(proxy, type, action, options, response, arg) {
        // Prevent the window from being closed.
        this.setAction('');
        this.hideWaitMessage();
        Ext.MessageBox.show({
            buttons: Ext.MessageBox.OK,
            icon: Ext.MessageBox.ERROR,
            msg: response.message,
            title: 'Error'
        });
        Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
    },
    // --------------------------------------------------------------------------------
    // RC store "save" callback.
    // --------------------------------------------------------------------------------
    storeSaveCallBack: function(store) {
        switch (this.data.get('action')) {
            case 'save':
            case 'submit_for_approval':
            case 'mark_for_deletion':
            case 'approve':
                this.fireEvent('closewindow');
                break;
            case 'save_new_based_on':
                this.newBasedOn();
                break;
            case 'save_new':
                this.reset();
                break;
        }
    },
    // --------------------------------------------------------------------------------
    // Normalize the record by setting all the undefined values to an empty string
    // so the form clears them out when we update it.
    // If we do not do this it seems as if those fields do not even exist.
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
    // Prepare the form for creating a new RC based on the current one.
    // --------------------------------------------------------------------------------
    newBasedOn: function() {
        this.formElements.curatorDisplay.reset();
        this.formElements.auditorDisplay.reset();
        this.formElements.coordinates.reset();
        this.formElements.anatomicalExpressionTerms.store.removeAll();
        this.formElements.stagingData.store.removeAll();
        this.data = new REDfly.dialog.rcRecord({
            assayed_in_species_id: this.data.get('assayed_in_species_id'),
            author_email: this.data.get('author_email'),
            citation: this.data.get('citation'),
            curator_full_name: this.data.get('curator_full_name'),
            curator_id: this.data.get('curator_id'),
            current_genome_assembly_release_version: this.data.get('current_genome_assembly_release_version'),
            evidence_id: this.data.get('evidence_id'),
            gene_id: this.data.get('gene_id'),
            gene_identifier: this.data.get('gene_identifier'),
            gene_name: this.data.get('gene_name'),
            name: this.data.get('gene_name') + '_',
            pubmed_id: this.data.get('pubmed_id'),
            sequence_from_species_id: this.data.get('sequence_from_species_id'),
            sequence_source_id: this.data.get('sequence_source_id')
        });
        // Set all the undefined values to an empty string so the form clears them out
        // when we update it.
        this.normalizeEmptyRecordFields(this.data);
        this.getForm().loadRecord(this.data);
        this.formElements.redflyId.setValue(
            Ext.isEmpty(this.data.get('redfly_id'))
                ? 'N/A'
                : this.data.get('redfly_id')
        );
        // Redo the layout now that we have added some values
        this.doLayout();
    },
    // --------------------------------------------------------------------------------
    // Update the internal data record based on the data currently entered into
    // the form elements. This is done in preparation for saving the data.
    // --------------------------------------------------------------------------------
    updateDataRecordFromForm: function() {
        // We do not grab the chromosome id, the start, and the end because these are
        // set after the BLAT call being returned back.
        // NOTE: For performance reasons, the citation store is only loaded when
        // a new pmid is entered and not when a record is loaded. This presents
        // an issue if an author email is entered because this is stored along
        // with the citation.
        this.data.beginEdit();
        if ( this.data.get('action') === 'approve' ) {
            this.data.set('auditor_id', this.userId);
        } else {
            this.data.set('curator_id', this.userId);
        }
        this.data.set('assayed_in_species_id', this.formElements.assayedInSpecies.getValue());
        this.data.set('author_email', this.formElements.authorEmail.getValue());
        this.data.set('current_genome_assembly_release_version', this.formElements.sequenceFromSpecies.current_genome_assembly_release_version);
        this.data.set('evidence_id', this.formElements.evidence.getValue());
        var anatomicalExpressionTermList = [];
        var anatomicalExpressionTermIdList = [];
        var rList = this.formElements.anatomicalExpressionTerms.store.getRange();
        Ext.each(rList,
            function (item, index) {
                anatomicalExpressionTermList.push(item.data);
                anatomicalExpressionTermIdList.push(item.data.id);
            },
            this
        );
        // Anatomical expression terms returned from the REST service
        this.data.set('anatomical_expression_terms', anatomicalExpressionTermList);
        // Anatomical expression term identfiers set in the user interface
        this.data.set('anatomical_expression_term_ids', anatomicalExpressionTermIdList);
        this.data.set('fbtp', this.formElements.transgenicConstruct.getValue());
        this.data.set('figure_labels', this.formElements.figureLabels.getValue());
        this.data.set('gene_id', this.formElements.gene.getValue());
        this.data.set('is_negative', this.formElements.isNegative.getValue());
        this.data.set('is_override', this.formElements.isOverride.getValue());
        this.data.set('name', this.formElements.name.getValue());
        this.data.set('notes', this.formElements.notes.getValue());
        this.data.set('pubmed_id', this.formElements.pubmedId.getValue());
        this.data.set('sequence', this.formElements.sequence.getValue());
        this.data.set('sequence_from_species_id', this.formElements.sequenceFromSpecies.getValue());
        this.data.set('sequence_source_id', this.formElements.sequenceSource.getValue());
        var stagingDataList = [];
        var stagingDataUIList = [];
        rList = this.formElements.stagingData.store.getRange();
        Ext.each(
            rList,
            function (item, index) {
                stagingDataList.push(item.data);
                var stagingDataIdentifiersObject = {
                    ts_id: item.data.ts_id,
                    rc_id: item.data.rc_id,
                    anatomical_expression_identifier: item.data.anatomical_expression_identifier,
                    pubmed_id: item.data.pubmed_id,
                    stage_on_identifier: item.data.stage_on_identifier,
                    stage_off_identifier: item.data.stage_off_identifier,
                    biological_process_identifier: item.data.biological_process_identifier,
                    sex_id: item.data.sex_id,
                    ectopic_id: item.data.ectopic_id,
                    enhancer_or_silencer_attribute_id: item.data.enhancer_or_silencer_attribute_id
                };
                stagingDataUIList.push(stagingDataIdentifiersObject);
            },
            this
        );
        // Staging data returned from the REST service
        this.data.set('staging_data', stagingDataList);
        // Staging data set in the user interface
        this.data.set('staging_data_ui', stagingDataUIList);
        this.data.endEdit();
    },
    // --------------------------------------------------------------------------------
    // Set the REDfly id
    // @param id The redfly id
    // --------------------------------------------------------------------------------
    setRedflyId: function(id) {
        this.data.set('redfly_id', id);
    },
    // --------------------------------------------------------------------------------
    // Set the action that the user is taking on this panel.
    // This typically happens on a button press.
    // --------------------------------------------------------------------------------
    setAction: function(action) {
        this.data.set('action', action);
    },
    // --------------------------------------------------------------------------------
    // Provide a mechanism for setting the data values in this panel from the outside.
    // This is used in the approval window where individual data fields can be selected
    // and then used to populate this panel.
    // @param {String} field The name of the field to set
    // @param {Mixed} fieldData The value of the field
    // --------------------------------------------------------------------------------
    setDataValue: function(
        field,
        fieldData
    ) {
        // Some elements may require that we suspend events while storing the
        // data. E.g., if events are enabled storing the pubmed id will trigger
        // loadCitation() will be called because the data changed.
        this.data.set('auditor_id', REDfly.config.userId);
        this.formElements.auditorDisplay.update({
            fullName: REDfly.config.userFullName,
            timeOrderWords: '',
            formattedData: ''
        });
        switch (field)
        {
            case 'anatomical_expression_terms':
                this.setAnatomicalExpressionTerms(fieldData);
                break;
            case 'assayed_in_species_id':
                this.loadAssayedInSpecies(fieldData);
                this.data.set('assayed_in_species_id', fieldData);
                break;
            case 'author_email':
                this.formElements.authorEmail.setValue(fieldData);
                this.data.set('author_email', fieldData);
                break;
            case 'chromosome':
                this.formElements.coordinates.setChromosome(fieldData);
                this.data.set('chromosome', fieldData);
                break;
            case 'chromosome_id':
                this.data.set('chromosome_id', fieldData);
                break;
            case 'citation':
                this.formElements.citation.setValue(fieldData);
                this.data.set('citation', fieldData);
                break;
            case 'curator_full_name':
                this.formElements.curatorDisplay.update({
                    fullName: fieldData
                });
                this.data.set('curator_full_name', fieldData);
                break;
            case 'curator_id':
                this.data.set('curator_id', fieldData);
                break;
            case 'current_genome_assembly_release_version':
                this.data.set('current_genome_assembly_release_version', fieldData);
                break;
            case 'end':
                this.formElements.coordinates.setEnd(fieldData);
                this.data.set('end', fieldData);
                break;
            case 'evidence_id':
                this.loadEvidence(fieldData);
                this.data.set('evidence_id', fieldData);
                break;
            case 'fbtp':
                this.loadTransgenicConstruct(fieldData);
                break;
            case 'figure_labels':
                this.formElements.figureLabels.setValue(fieldData);
                this.data.set('figure_labels', fieldData);
                break;
            case 'gene_id':
                this.loadGene(fieldData);
                this.data.set('gene_id', fieldData);
                break;
            case 'is_crm':
                this.formElements.isCrm.setValue(fieldData);
                this.data.set('is_crm', fieldData);
                break;
            case 'is_minimalized':
                this.formElements.isMinimalized.setValue(fieldData);
                this.data.set('is_minimalized', fieldData);
                break;
            case 'is_negative':
                this.formElements.isNegative.setValue(fieldData);
                this.data.set('is_negative', fieldData);
                break;
            case 'is_override':
                this.formElements.isOverride.setValue(fieldData);
                this.data.set('is_override', fieldData);
                break;
            case 'name':
                this.formElements.name.setValue(fieldData);
                this.data.set('name', fieldData);
                break;
            case 'notes':
                this.formElements.notes.setValue(fieldData);
                this.data.set('notes', fieldData);
                break;
            case 'pubmed_id':
                this.formElements.pubmedId.suspendEvents();
                this.formElements.pubmedId.setValue(fieldData);
                this.data.set('pubmed_id', fieldData);
                this.formElements.pubmedId.resumeEvents();
                break;
            case 'sequence':
                this.formElements.sequence.setValue(fieldData);
                this.data.set('sequence', fieldData);
                break;
            case 'sequence_from_species_id':
                this.loadSequenceFromSpecies(fieldData);
                this.data.set('sequence_from_species_id', fieldData);
                break;
            case 'sequence_source_id':
                this.loadSequenceSource(fieldData);
                break;
            case 'staging_data':
                this.setStagingData(fieldData);
                break;
            case 'start':
                this.formElements.coordinates.setStart(fieldData);
                this.data.set('start', fieldData);
                break;
            default:
                break;
        }
    },
    // --------------------------------------------------------------------------------
    // Set the anatomical expression terms.
    // @param {String} terms JSON encoded string of anatomical expression terms.
    // --------------------------------------------------------------------------------
    setAnatomicalExpressionTerms: function(anatomicalExpressionTerms) {
        this.formElements.anatomicalExpressionTerms.store.removeAll();
        // The anatomical expression terms are a nested array so it will need to be
        // decoded and then loaded into the store of selected anatomical expression
        // terms.
        var anatomicalExpressionTermList = Ext.util.JSON.decode(anatomicalExpressionTerms);
        Ext.each(
            anatomicalExpressionTermList,
            function (item) {
                var record = new Ext.data.Record(item);
                record.set('display', record.data.term + ' (' + record.data.identifier + ')');
                this.formElements.anatomicalExpressionTerms.add(record);
            },
            this
        );
    },
    // --------------------------------------------------------------------------------
    // Set the staging data.
    // @param {String} data JSON encoded string of staging data.
    // --------------------------------------------------------------------------------
    setStagingData: function(stagingData) {
        this.formElements.stagingData.store.removeAll();
        // The staging data are a nested array so it will need to be
        // decoded and then loaded into the store of selected staging
        // data.
        var stagingDataList = Ext.util.JSON.decode(stagingData);
        Ext.each(
            stagingDataList,
            function (item) {
                var record = new Ext.data.Record(item);
                record.set('anatomical_expression_display', record.data.anatomical_expression_term + ' (' + record.data.anatomical_expression_identifier + ')');
                record.set('stage_on_display', record.data.stage_on_term + ' (' + record.data.stage_on_identifier + ')');
                record.set('stage_off_display', record.data.stage_off_term + ' (' + record.data.stage_off_identifier + ')');
                record.set('biological_process_display', record.data.biological_process_term + ' (' + record.data.biological_process_identifier + ')');
                switch (record.data.sex_id) {
                    case 'm':
                        record.set('sex_term', 'Male');
                        break;
                    case 'f':
                        record.set('sex_term', 'Female');
                        break;
                    case 'both':
                        record.set('sex_term', 'Both');
                        break;
                    default:
                        record.set('sex_term', 'Unknown: ' + record.data.sex_id);
                }
                switch (record.data.ectopic_id) {
                    case '0':
                        record.set('ectopic_term', 'False');
                        break;
                    case '1':
                        record.set('ectopic_term', 'True');
                        break;
                    default:
                        record.set('ectopic_term', 'Unknown: ' + record.data.ectopic_id);
                }
                switch (record.data.enhancer_or_silencer_attribute_id) {
                    case 'enhancer':
                        record.set('enhancer_or_silencer_attribute_term', 'Enhancer');
                        break;
                    case 'negative':
                        record.set('enhancer_or_silencer_attribute_term', 'Negative');
                        break;
                    case 'silencer':
                        record.set('enhancer_or_silencer_attribute_term', 'Silencer');
                        break;
                    default:
                        record.set('enhancer_or_silencer_attribute_term', 'Unknown: ' + record.data.enhancer_or_silencer_attribute_id);
                }
                this.formElements.stagingData.add(record);
            },
            this
        );
    }
});