// ================================================================================
// Dialog for performing CRUD operations on binding sites.
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
REDfly.dialog.tfbsRecord = Ext.data.Record.create([
    { name: 'action', defaultValue: 'cancel' },
    { name: 'archive_date', allowBlank: true, defaultValue: '' },
    { name: 'archive_date_formatted', allowBlank: true, defaultValue: '' },
    { name: 'archived_ends' },
    { name: 'archived_genome_assembly_release_versions' },
    { name: 'archived_starts' },
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
    { name: 'figure_labels', allowBlank: true, defaultValue: '' },
    { name: 'gene_id' },
    { name: 'gene_identifier' },
    { name: 'gene_name' },
    { name: 'last_audit' },
    { name: 'last_audit_formatted' },
    { name: 'last_update' },
    { name: 'last_update_formatted' },
    { name: 'name' },
    { name: 'notes', allowBlank: true, defaultValue: '' },
    { name: 'pubmed_id' },
    { name: 'redfly_id', allowBlank: true, defaultValue: '' },
    { name: 'sequence', defaultValue: '' },
    { name: 'sequence_with_flank', defaultValue: '' },
    { name: 'species' },
    { name: 'sequence_from_species_id' },
    { name: 'sequence_from_species_scientific_name' },
    { name: 'sequence_from_species_short_name' },
    { name: 'start' },
    { name: 'state' },
    { name: 'tf_id' },
    { name: 'tf_identifier' },
    { name: 'tf_name' }
]);
// --------------------------------------------------------------------------------
// The tfbsStore JsonStore is used to load and save transcription factor binding
// sites through the REST service.
// --------------------------------------------------------------------------------
REDfly.store.tfbsStore = new Ext.data.JsonStore({
    // Ext.data.JsonStore configs
    autoLoad: false,
    // Don't save dirty records until told to do so via button handler
    autoSave: false,
    proxy: new Ext.data.HttpProxy({
        api: {
            create: {
                method: 'POST',
                url: REDfly.config.apiUrl + '/jsonstore/transcriptionfactorbindingsite/save'
            },
            // No destroy for this store but the spec can't be undefined!
            destroy: {
                url: REDfly.config.apiUrl + '/jsonstore/transcriptionfactorbindingsite/delete'
            },
            read: {
                method: 'GET',
                url: REDfly.config.apiUrl + '/jsonstore/transcriptionfactorbindingsite/load'
            },
            update: {
                method: 'POST',
                url: REDfly.config.apiUrl + '/jsonstore/transcriptionfactorbindingsite/save'
            }
        },
        method: 'GET'
    }),
    writer: new Ext.data.JsonWriter({
        encode: true,
        // Write all the fields, not just those that changed
        writeAllFields: true
    }),
    // Embedded Ext.data.JsonReader configs    
    fields: REDfly.dialog.tfbsRecord,
    idProperty: 'redfly_id',
    messageProperty: 'message',
    root: 'results',
    successProperty: 'success',
    totalProperty: 'num'
});
// --------------------------------------------------------------------------------
// TFBS panel used for CRUD operations and to hold selected data during the
// approval process.
// All data is sent to the server based on this.data which is a record creating
// using REDfly.dialog.tfbsRecord. Any time that data is selected using a
// dropdown or set via an event from a store load, the record is updated. Plain
// text fields that are meant to accept user data with no events associated with
// them will be queried at save time to have their corresponding data records
// element updated.
// this.formElements is an object containing a data member for each form element
// named corresponsing to the field in the TFBS record. This makes it easy to
// reference the form elements using this.formElements[field_name] when updating
// data from an external source such as in the approval panel.
// Comboboxes (e.g., selectGene, tfSearch, evidenceSearch) can be set using an
// identifier passed in during the approval process or when loading a record for
// editing, or by the user selecting (possibly filtered) data from the dropdown.
// Either way, both the element name and database id should be set.
// Events Fired:
// closewindow: Fired to let the container know that this panel should be closed
// clientvalidation: Fired explicitly by validateSequenceWithFlank() to let the
//   container know that the form validation has taken place.
// --------------------------------------------------------------------------------
REDfly.component.tfbsPanel = new Ext.extend(Ext.FormPanel, {
    // Ext.FormPanel configs    
    bodyStyle: { backgroundColor: '#F1F1F1' },
    border: false,
    buttonAlign: 'center',
    defaultType: 'textfield',
    defaults: { width: 400 },
    labelWidth: 150,
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
    // Current mode: "approval", "edit"
    mode: null,
    store: REDfly.store.tfbsStore,
    userId: null,
    userFullName: null,
    initComponent: function() {
        // Turn on validation errors beside the field globally
        Ext.form.Field.prototype.msgTarget = 'side';
        // Enables the closewindow event generated by this class to be detected 
        // by its parent class when the window being closed
        this.enableBubble('closewindow');
        this.data = new REDfly.dialog.tfbsRecord();
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
                        this.data.set('pubmed_id', field.getValue());
                        this.loadCitation(field.getValue());
                    }
                },
            },
            name: 'pubmed_id',
            regex: /^[0-9]+$/,
            regexText: 'Pubmed ID must be numeric',
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
        var transcriptionFactorItem   = new REDfly.component.selectGene(
            { fieldLabel: 'Transcription Factor' }
        );
        transcriptionFactorItem.allowBlank = false;
        transcriptionFactorItem.disable();
        var nameItem = new Ext.form.TextField({
            allowBlank: false,
            fieldLabel: 'Element Name',
            listeners: {
                blur: function(field) {
                    this.data.set('name', field.getValue());
                },
                scope: this
            },
            name: 'name',
            width: 300
        }); 
        nameItem.disable();               
        var evidenceItem = new REDfly.component.selectEvidence();
        evidenceItem.allowBlank = false;
        this.containedSequence = true;
        // validSequence is used by sequence and sequence_with_flank to
        // determine if blatstore got valid data back from blatserver
        // without doing repetitive queries
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
                blur: function(field) { this.validateSequenceWithFlank(); },
                scope: this
            },
            minLength: 1,
            name: 'sequence',
            // See http://www.genomatix.de/online_help/help/sequence_formats.html#IUPAC
            // for the source of this regex.
            regex: /^[ACGTURYKMSWBDHVN\s]{1,}$/i,
            regexText: 'Invalid sequence'.replace(' ', '&nbsp;'),
            validationEvent: 'blur',
            validator: function() {
                if ( me.validSequence ) {
                    return true;
                } else {
                    return 'Invalid Sequence';
                }
            },
            width: 400,
            // REDfly configs
            // This must be set in order to access the species field in the dialog
            speciesField: null            
        });
        sequenceItem.allowBlank = false;
        sequenceItem.disable();        
        var sequenceWithFlankItem = new Ext.form.TextArea({
            autoScroll: true,
            fieldLabel: 'Sequence with Flank',
            height: 150,
            invalidText: 'Invalid Sequence with Flank',
            listeners: {
                blur: function(field) {
                    var sequenceWithFlank = field.getValue();
                    // Validate the sequence with flank and if the value
                    // is valid and has changed go out to blat for the
                    // coordinates.
                    // We should only load the coordinates store if the
                    // sequence changed.
                    if ( this.validateSequenceWithFlank() &&
                        this.data.get('sequence_with_flank') !== sequenceWithFlank ) {
                        this.data.set('current_genome_assembly_release_version', this.formElements.sequenceFromSpecies.current_genome_assembly_release_version);
                        this.data.set('sequence_with_flank', sequenceWithFlank);
                        this.searchCoordinates(
                            this.formElements.sequenceFromSpecies.short_name,
                            sequenceWithFlank
                        );
                    }
                },
                scope: this
            },
            minLength: 40,
            name: 'sequence_with_flank',
            // See http://www.genomatix.de/online_help/help/sequence_formats.html#IUPAC
            // for the source of this regex.
            regex: /^[ACGTURYKMSWBDHVN\s]{40,}$/i,
            regexText: 'Invalid Sequence with Flank',
            validationEvent: 'blur',
            validator: function() {
                if ( me.validSequence ) {
                    return true;
                } else {
                    return 'Invalid Sequence with Flank';
                }
            },
            width: 400,
            // REDfly configs
            // This must be set in order to access the species field in the dialog
            speciesField: null
        });
        sequenceWithFlankItem.allowBlank = false;
        sequenceWithFlankItem.disable();
        var coordinatesItem = new REDfly.component.selectCoordinates();
        // The coordinates will be set automatically
        coordinatesItem.disable();
        var sequenceFromSpeciesItem = new REDfly.component.selectSequenceFromSpecies({
            geneField: geneItem,
            transcriptionFactorField: transcriptionFactorItem,
            nameField: nameItem,
            sequenceField: sequenceItem,
            sequenceWithFlankField: sequenceWithFlankItem
        });
        sequenceFromSpeciesItem.allowBlank = false;
        geneItem.sequenceFromSpeciesField = sequenceFromSpeciesItem;
        transcriptionFactorItem.sequenceFromSpeciesField = sequenceFromSpeciesItem;
        sequenceItem.sequenceFromSpeciesField = sequenceFromSpeciesItem;
        sequenceWithFlankItem.sequenceFromSpeciesField = sequenceFromSpeciesItem;
        var notesItem = new Ext.form.TextArea({
            fieldLabel: 'Notes',
            height: 50,
            name: 'notes',
            width: 400
        });
        var figureLabelsItem = new Ext.form.TextArea({
            fieldLabel: 'Figure Labels (separate labels with ^)',
            height: 50,
            name: 'figure_labels',
            regex: /^[a-z0-9_\^\s]+$/i,
            regexText: 'Labels may contain letters, numbers, and underscores.<br>Separate labels with "^"'.replace(' ', '&nbsp;'),
            width: 400
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
        Ext.apply(this, {
            items: [
                redflyIdItem,
                curatorDisplayItem,
                auditorDisplayItem,
                sequenceFromSpeciesItem,
                pubmedIdItem,
                citationItem,
                authorEmailItem,
                geneItem,
                transcriptionFactorItem,
                nameItem,
                evidenceItem,
                sequenceItem,
                sequenceWithFlankItem,
                coordinatesItem,
                notesItem,
                figureLabelsItem,
                twoCuratorCheckboxItem
            ],
            buttons: buttonList,
            formElements: {
                redflyId: redflyIdItem,
                curatorDisplay: curatorDisplayItem,
                auditorDisplay: auditorDisplayItem,
                sequenceFromSpecies: sequenceFromSpeciesItem,
                pubmedId: pubmedIdItem,
                citation: citationItem,
                authorEmail: authorEmailItem,
                gene: geneItem,
                transcriptionFactor: transcriptionFactorItem,
                name: nameItem,
                evidence: evidenceItem,
                sequence: sequenceItem,
                sequenceWithFlank: sequenceWithFlankItem,
                coordinates: coordinatesItem,
                notes: notesItem,
                figureLabels: figureLabelsItem,
                twoCuratorVerification: twoCuratorCheckboxItem
            }
        });
        REDfly.component.tfbsPanel.superclass.initComponent.apply(this, arguments);
        var selectSequenceFromSpeciesCallBack = function (newValue, oldValue) {
            if ( oldValue !== newValue ) {
                this.data.set('sequence', '');
                this.formElements.gene.reset();
                this.formElements.transcriptionFactor.reset();
                this.formElements.name.reset();
                this.formElements.sequence.reset();
                this.formElements.sequenceWithFlank.reset();
                this.formElements.coordinates.reset();
            }
        };
        this.formElements.sequenceFromSpecies.on(
            'change',
            selectSequenceFromSpeciesCallBack,
            this
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
        var displayCitationCallBack = function(store, records, options) {
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
        var citationExceptionCallBack = function(proxy, type, action, options, response, arg) {
            this.formElements.citation.setValue('');
            Ext.MessageBox.show({
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR,
                msg: ('No citation found for PMID ' + options.params.external_id +
                    ': ' + response.message).replace(' ', '&nbsp;'),
                title: 'Warning'
            });
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
                this.formElements.sequenceWithFlank.markInvalid();
                this.formElements.sequenceWithFlank.validate();
                this.showWaitMessage('Loading coordinates, please wait...');
            },
            this
        );
        // Add a listener to the coordinates store so that the coordinates
        // are updated when a call to the Dockerized server BLAT is made.
        var searchCoordinatesCallBack = function(store, records, options) {
            this.hideWaitMessage();
            if ( records.length === 0 ) {
                Ext.MessageBox.show({
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.WARNING,
                    msg: 'No sequence match from the BLAT server'.replace(' ', '&nbsp;'),
                    title: 'Warning'
                });
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
                }
            }
        };
        REDfly.store.blatsearch.on(
            'load',
            searchCoordinatesCallBack,
            this
        );
        var blatExceptionCallBack = function(proxy, type, action, options, response, arg) {
            this.validSequence = false;
            this.formElements.sequence.markInvalid();
            this.formElements.sequence.validate();
            this.formElements.sequenceWithFlank.markInvalid();
            this.formElements.sequenceWithFlank.validate();
            this.showMessage('Error loading coordinates', response.message);
        };
        REDfly.store.blatsearch.on(
            'exception',
            blatExceptionCallBack,
            this
        );
        // --------------------------------------------------------------------------------
        // Store both id and name of the gene when the user makes a selection in
        // the dropdown. This lets us store both items in the internal data record.
        // --------------------------------------------------------------------------------
        var selectGeneCallBack = function(combo, record, index) {
            this.data.set('gene_id', record.data.id);
            this.data.set('gene_name', record.data.name);
            this.generateName();
        };
        this.formElements.gene.on(
            'select',
            selectGeneCallBack,
            this
        );
        // --------------------------------------------------------------------------------
        // Store both id and name of the transcription factor when the user makes a 
        // selection in the dropdown. This lets us store both items in the internal data
        // record.
        // --------------------------------------------------------------------------------
        var selectTranscriptionFactorCallBack = function(combo, record, index) {
            this.data.set('tf_id', record.data.id);
            this.data.set('tf_name', record.data.name);
            this.generateName();
        };
        this.formElements.transcriptionFactor.on(
            'select',
            selectTranscriptionFactorCallBack,
            this
        );
        // --------------------------------------------------------------------------------
        // Store the evidence name and id when the user makes a selection in the dropdown.
        // This lets us store both items in the internal data record.
        // --------------------------------------------------------------------------------
        var selectEvidenceCallBack = function(combo, record, index) {
            this.data.set('evidence_term', record.data.term);
            this.data.set('evidence_id', record.data.id);
        };
        this.formElements.evidence.on(
            'select',
            selectEvidenceCallBack,
            this
        );
        this.store.on(
            'beforeload',
            this.showWaitMessage.createDelegate(
                this.citationStore,
                ['Loading data, please wait...']
            ),
            this
        );
        this.store.on(
            'load',
            this.hideWaitMessage
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
    },
    // --------------------------------------------------------------------------------
    // Hide any visible messages
    // --------------------------------------------------------------------------------
    hideWaitMessage: function() {
        Ext.MessageBox.hide();
    },
    // --------------------------------------------------------------------------------
    // Set the REDfly id
    // @param id The redfly id
    // --------------------------------------------------------------------------------
    setRedflyId: function(id) {
         this.data.set('redfly_id', id);
    },
    // --------------------------------------------------------------------------------
    // Set the window mode to control button behavior
    // @param mode The window mode
    // --------------------------------------------------------------------------------
    setMode: function(mode) {
        this.mode = mode;
    },
    // --------------------------------------------------------------------------------
    // Set the action that the user is taking on this panel. This typically
    // happens on a button press.
    // --------------------------------------------------------------------------------
    setAction: function(action) {
        this.data.set('action', action);
    },
    // --------------------------------------------------------------------------------
    // Reset the current form and load any data specified by the redfly id.
    // @param redflyId The new redfly id to load
    // --------------------------------------------------------------------------------
    load: function(redflyId) {
        // Since we are currently loading on the "show" event make sure to clear
        // the form if no REDfly identifier was provided or we will be showing
        // whatever was loaded the list time the window was displayed.
        this.reset();
        if ( ! Ext.isEmpty(redflyId) ) {
            this.store.load({
                params: { redfly_id: redflyId }
            });
        }
    },
    // --------------------------------------------------------------------------------
    // Display verification errors, if there are any.
    // @param errorList An array of error messages generated by this verification
    // --------------------------------------------------------------------------------
    displayVerificationErrors: function(errorList) {
        if ( errorList.length !== 0 ) {
            errorsMessage = '';
            for (errorIndex = 0; errorIndex < errorList.length; errorIndex++ ) {
                errorsMessage = errorsMessage
                    .concat(errorList[errorIndex].replace(/\s/g, '&nbsp;'))
                    .concat('<br>');
            }
            Ext.MessageBox.show({
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.INFO,
                msg: errorsMessage,
                title: 'Error(s)'
            });
            this.setAction('');
        }
    },
    // --------------------------------------------------------------------------------
    // Load the citation from the store whenever the pubmed id changes
    // @param pubmedId the new pubmed id
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
    // @param {String} sequenceWithFlank The sequence with flank to use in the query.
    // --------------------------------------------------------------------------------
    searchCoordinates: function(
        speciesShortName,
        sequenceWithFlank
    ) {
        if ( sequenceWithFlank.length < 40 ) {
            Ext.MessageBox.show({
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING,
                msg: 'The sequence with flank must contain at least 40bp'.replace(' ', '&nbsp;'),
                title: 'Warning'
            });
        } else {
            REDfly.store.blatsearch.load({ params: {
                speciesShortName: speciesShortName,
                sequence: sequenceWithFlank
            } });
        }
    },
    // --------------------------------------------------------------------------------
    // Display the provided coordinates.
    // As a side effect this method also clears any validation errors
    // from the sequence input field.
    // @param {Object} record A data record from REDfly.store.blatsearch.
    // --------------------------------------------------------------------------------    
    displayCoordinates: function(record) {
        var strippedSequenceWithFlank = this.formElements.sequenceWithFlank.getValue().replace(
            /[\s]+/g,
            ''
        );
        var strippedSequence = this.formElements.sequence.getValue().replace(
            /[\s]+/g,
            ''
        );
        var flankLength = (strippedSequenceWithFlank.length - strippedSequence.length) / 2;
        var start = parseInt(record.data.start, 10) + flankLength;
        var end = parseInt(record.data.end, 10) - flankLength;
        this.formElements.coordinates.setChromosome(record.data.chromosome);
        this.formElements.coordinates.setStart(start);
        this.formElements.coordinates.setEnd(end);
        this.data.set('chromosome', record.data.chromosome);
        this.data.set('chromosome_id', record.data.chromosome_id);
        this.data.set('start', start);
        this.data.set('end', end);
        this.validSequence = true;
        this.formElements.sequence.clearInvalid();
        this.formElements.sequence.validate();
        this.formElements.sequenceWithFlank.clearInvalid();
        this.formElements.sequenceWithFlank.validate();
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
    // ----------------------------------------------------------------------------------
    // Load the gene store and set the selected gene.
    // Also updates the data record: gene_id, gene_identifier and gene_name.
    // @param {Number} geneId The id of the gene to be selected.
    // ----------------------------------------------------------------------------------
    loadGene: function(geneId) {
        this.formElements.gene.getStore().load({ 
            params: { id: geneId },
            callback: function() {
                this.formElements.gene.setValue(geneId);
                var record = this.formElements.gene.getStore().getById(geneId);
                this.data.set('gene_id', record.data.id);
                this.data.set('gene_identifier', record.data.identifier);
                this.data.set('gene_name', record.data.name);
                this.generateName();
            },
            scope: this            
         });
    },
    // --------------------------------------------------------------------------------
    // Load the transcription factor store and set the selected transcription factor.
    // Also updates the data record: tf_id, tf_identifier and tf_name.
    // @param {Number} transcriptionFactorId The id of the transcription factor to 
    //   be selected.
    // --------------------------------------------------------------------------------
    loadTranscriptionFactor: function(transcriptionFactorId) {
        this.formElements.transcriptionFactor.getStore().load({
            params: { id: transcriptionFactorId },
            callback: function() {
                this.formElements.transcriptionFactor.setValue(transcriptionFactorId);
                var record = this.formElements.transcriptionFactor.getStore().getById(transcriptionFactorId);
                this.data.set('tf_id', record.data.id);
                this.data.set('tf_identifier', record.data.identifier);
                this.data.set('tf_name', record.data.name);
                this.generateName();
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
    // Load data from the store into the form.
    // Copy the loaded record into the object so that we can track any changes.
    // --------------------------------------------------------------------------------
    loadFormFromRecord: function(store, recordList, opt) {
        if ( recordList.length !== 1 ) { return true; }
        // Only refresh the record if the owning container is visible. Since
        // this panel is used in both the approval dialog and the CRUD dialog it
        // is possible that when the store is loaded multiple onLoad events will
        // be processed causing both the hidden and visible dialogs to load
        // their records.
        if ( ! this.ownerCt.isVisible() ) { return true; }
        var formRecord = recordList[0];
        this.data = formRecord;
        this.form.loadRecord(formRecord);
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
        this.loadTranscriptionFactor(formRecord.get('tf_id'));
        this.formElements.gene.enable();
        this.formElements.transcriptionFactor.enable();
        this.formElements.name.enable();
        this.loadEvidence(formRecord.get('evidence_id'));
        this.formElements.sequence.enable();
        this.formElements.sequenceWithFlank.enable();
        this.formElements.coordinates.setChromosome(formRecord.get('chromosome'));
        this.formElements.coordinates.setStart(formRecord.get('start'));
        this.formElements.coordinates.setEnd(formRecord.get('end'));
        this.doLayout();
    },
    // --------------------------------------------------------------------------------
    // Apply general verification of the data in the dialog. This may include
    // local validation as well as asynchronous AJAX calls to the server to
    // verify the uniqueness of the element name.
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
        var name = this.data.get('name');
        var position = name.lastIndexOf(':');
        var id = name.substr(position + 1);
        if ( id.length < 1 ) {
            errorList.push('Invalid TFBS name');
        }
        var transcriptionFactorRecord = this.formElements.transcriptionFactor.getStore().getById(this.data.get('tf_id'));
        if ( ! transcriptionFactorRecord ) {
            errorList.push('Transcription factor binding site not provided');
        }
        var geneRecord = this.formElements.gene.getStore().getById(this.data.get('gene_id'));
        if ( ! geneRecord ) {
            errorList.push('Gene not provided');
        }
        if ( (! this.formElements.evidence.getValue()) || 
            (! this.data.get('evidence_id')) ) {
            errorList.push('Evidence term not selected');
        }
        if ( (this.data.get('action') === 'submit_for_approval') &&
            (! this.formElements.twoCuratorVerification.getValue()) ) {
            errorList.push('Two curators must verify the data before submitting for approval.');
        }

        return ( errorList.length === 0 );
    },
    //--------------------------------------------------------------------------------
    // Hit the server to verify that the element is unique.
    // This means that the gene, transcription factor, and coordinates must not 
    // already exist in the database.
    // @param errorList An array of error messages generated by this verification
    // @returns True on success and false if there was a verification error
    // --------------------------------------------------------------------------------
    verifyRemoteFormElements: function(config) {
        Ext.Ajax.on(
            'beforerequest',
            this.showWaitMessage.createDelegate(
                Ext.Ajax,
                ['Checking for duplicates...']
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
        // Save the "this" reference so that we do not refer to the AJAX object.
        var verifyRemoteFormElementsFunction = this;
        Ext.Ajax.request({
            failure:  function(response, opts) {
                config.failure(Ext.decode(response.responseText));
            },
            method: 'GET',
            params: {
                chromosome_id: this.data.get('chromosome_id'),
                end: this.data.get('end'),
                gene_id: this.data.get('gene_id'),
                redfly_id: this.data.get('redfly_id'),
                start: this.data.get('start'),
                tf_id: this.data.get('tf_id'),
            },
            scope: this,
            success: function(response, opts) {
                var returnValue = Ext.util.JSON.decode(response.responseText);
                if ( returnValue.success ) {
                    // We need to use call() so that the callback gets the correct scope
                    config.success.call(verifyRemoteFormElementsFunction);
                } else {
                    config.errorList.push(returnValue.message);
                    // We need to use call() so that the callback gets the correct scope
                    config.failure.call(
                        verifyRemoteFormElementsFunction,
                        config.errorList
                    );
                }
            },
            url: REDfly.config.apiUrl + '/jsonstore/transcriptionfactorbindingsite/checkForDuplicates'
        });
    },
    // --------------------------------------------------------------------------------
    // Reset the dialog but do not clear any data stored in the data record.
    // --------------------------------------------------------------------------------
    reset: function() {
        this.getForm().reset();
        this.store.removeAll(true);
        // Do not fire the "clear" action or it will trigger the "destroy" REST call
        this.citationStore.removeAll(true);
        this.data = new REDfly.dialog.tfbsRecord({
            action: 'cancel',
            curator: this.userFullName,
            curator_id: this.userId
        });
        // Set all the undefined values to an empty string so that the form clears 
        // them out when we update it.
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
        // When saving a TFBS for approval we want to be able to send the list of
        // REDfly identifiers that were considered to the save method so they can be
        // removed after the merge. To do this we add a parameter to the store that
        // contains a serialized list.
        // If this is not an approval be sure to set the list to empty/null!
        if ( (this.data.get('action') === 'approve' ) &&
             (arguments.length === 1) &&
             (redflyIdList.length !== 0) ) {
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
        this.store.save();
    },
    // --------------------------------------------------------------------------------
    // Handle exceptions generated by the TFBS store.
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
    },
    // --------------------------------------------------------------------------------
    // TFBS store "save" callback.
    // --------------------------------------------------------------------------------
    storeSaveCallBack: function() {
        switch (this.data.get('action')) {
            case 'save':
            case 'submit_for_approval':
            case 'approve':
                if ( this.closeAction === 'hide' ) {
                    this.hide();
                } else {
                    this.fireEvent('closewindow');
                }
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
        // Set undefined values to an empty string so the form clears them out
        // when we update it.
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
    // Prepare the form for creating a new TFBS based on the current one.
    // --------------------------------------------------------------------------------
    newBasedOn: function() {
        this.formElements.curatorDisplay.reset();
        this.formElements.auditorDisplay.reset();
        this.formElements.coordinates.reset();
        this.data = new REDfly.dialog.tfbsRecord({
            author_email: this.data.get('author_email'),
            citation: this.data.get('citation'),
            curator_full_name: this.data.get('curator_full_name'),            
            curator_id: this.data.get('curator_id'),
            current_genome_assembly_release_version: this.data.get('current_genome_assembly_release_version'),
            evidence_id: this.data.get('evidence_id'),
            gene_id: this.data.get('gene_id'),
            gene_identifier: this.data.get('gene_identifier'),
            gene_name: this.data.get('gene_name'),
            name: this.data.get('name'),
            notes: this.data.get('notes'),
            pubmed_id: this.data.get('pubmed_id'),
            sequence_from_species_id: this.data.get('sequence_from_species_id'),            
            tf_name: this.data.get('tf_name'),
            tf_id: this.data.get('tf_id')
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
        //this.formElements.auditorDisplay.reset();
        //this.formElements.curatorDisplay.setDate(null);
        // redo the layout now that we've added some values
        this.update();
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
        this.data.set('author_email', this.formElements.authorEmail.getValue());
        this.data.set('figure_labels', this.formElements.figureLabels.getValue());
        this.data.set('name', this.formElements.name.getValue());
        this.data.set('notes', this.formElements.notes.getValue());
        this.data.set('sequence', this.formElements.sequence.getValue());
        this.data.set('sequence_from_species_id', this.formElements.sequenceFromSpecies.getValue());
        this.data.set('sequence_with_flank', this.formElements.sequenceWithFlank.getValue());
        this.data.endEdit();
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
        // data.  E.g., if events are enabled storing the pubmed id will trigger
        // loadCitation() will be called because the data changed.
        this.data.set('auditor_id', REDfly.config.userId);
        this.formElements.auditorDisplay.update({ 
            fullName: REDfly.config.userFullName,
            timeOrderWords: '',
            formattedData: ''
        });        
        switch (field)
        {
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
            case 'figure_labels':
                this.formElements.figureLabels.setValue(fieldData);
                this.data.set('figure_labels', fieldData);
                break;
            case 'gene_id':
                this.loadGene(fieldData);
                this.data.set('gene_id', fieldData);
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
            case 'sequence_with_flank':
                this.formElements.sequenceWithFlank.setValue(fieldData);
                this.data.set('sequence_with_flank', fieldData);
                break;                
            case 'sequence_from_species_id':
                this.loadSequenceFromSpecies(fieldData);
                this.data.set('sequence_from_species_id', fieldData);
                break;                
            case 'start':
                this.formElements.coordinates.setStart(fieldData);
                this.data.set('start', fieldData);
                break;
            case 'tf_id':
                this.loadTranscriptionFactor(fieldData);
                this.data.set('tf_id', fieldData);
                break;
            default:
                break;
        }
    },
    // --------------------------------------------------------------------------------
    // Validate the sequence with flank.
    // Sequence validation is done using the textfield regex.
    // NOTE: Since the approval window listens to the clientvalidation event we must
    //   fire it if we change the validity of the field.
    // 1. The sequence with flank must pass the the textarea regex check using
    //    /^[ACGTURYKMSWBDHVN\s]{1,}$/i  (generated using
    //    http://en.wikipedia.org/wiki/Nucleotide#Abbreviation_codes_for_degenerate_bases)
    // 2. The sequence must be valid
    // 3. The sequence must be present in the sequence with flank
    // 4. The length of the left and right flanks must be the same
    // --------------------------------------------------------------------------------
    validateSequenceWithFlank: function() {
        // Sequence must be valid
        // Sequence must be contained within sequence with flank
        // Flank length must be equal
        var errorMessage = null;
        var sequenceWithFlank = this.formElements.sequenceWithFlank;
        // The sequence must be valid
        if ( ! this.formElements.sequence.isValid() ) {
            errorMessage = 'Invalid sequence';
        } else {
            var strippedSequenceWithFlank = sequenceWithFlank.getValue().replace(
                /[\s]+/g,
                ''
            ).toLowerCase();
            var strippedSequence = this.formElements.sequence.getValue().replace(
                /[\s]+/g,
                ''
            ).toLowerCase();
            // The sum of the left and right flank is always even if both flanks are 
            // the same size.
            if ( ((strippedSequenceWithFlank.length - strippedSequence.length) % 2) === 1 ) {
                errorMessage = 'The lengths of both left and right flanks do not match';
            } else {
                var flankLength = (strippedSequenceWithFlank.length - strippedSequence.length) / 2;
                // The sequence must be contained at the center of the sequence with flank
                var sequence = strippedSequenceWithFlank.substr(flankLength, strippedSequence.length);
                if ( strippedSequence !== sequence ) {
                    errorMessage = 'The sequence is not found at the center of the sequence with flank';
                }
            }
        }
        if ( errorMessage !== null ) {
            sequenceWithFlank.markInvalid(errorMessage);
        }
        this.fireEvent(
            'clientvalidation',
            (errorMessage === null)
        );

        return (errorMessage === null);
    },
    // --------------------------------------------------------------------------------
    // Generate the TFBS name in the format "<tf>_<gene>:REDFLY:XXX" The XXX is
    // a placeholder and will be replaced by the entity id once the TFBS is
    // approved.
    // --------------------------------------------------------------------------------
    generateName: function() {
        var name = this.data.get('name');
        var id = 'REDFLY:XXX';
        if ( ! Ext.isEmpty(name) ) {
            var position = name.lastIndexOf(':');
            id = 'REDFLY:' + name.substr(position + 1);
        }
        var display = this.data.get('tf_name') + '_' +
            this.data.get('gene_name') + ':' + id;
        this.formElements.name.setValue(display);
        this.data.set('name', display);
    }
});