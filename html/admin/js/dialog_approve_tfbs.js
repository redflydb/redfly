// ================================================================================
// The tfbsApprovalPanel is essentially a stripped-down version of the tfbsPanel
// that is used in the approveTfbs dialog. One tfbsApprovalPanel is displayed
// for each tfbs (e.g., redflyId) selected to be approved.
// Each panel consists of a set of composite fields which contain a text field
// to display entity data and a radio button to indicate selection of that data.
// On the selection of a radio button (or in the case of the "select all"
// button, all radio buttons) the 'selectdata' event is fired and caught by the
// approveTfbs dialog where it grabs the data out of this panel using
// getDataValue() and populates a tfbsPanel with that data.
// When displaying multiple tfbsApprovalPanels only the first will display the
// field labels to save space.
// Events Fired:
// selectvalue: Fired when a radio button has been selected to let the container
//   know to update its other children accordingly.
// ================================================================================
REDfly.component.tfbsApprovalPanel = new Ext.extend(Ext.FormPanel, {
    // Ext.FormPanel configs
    autoHeight: true,
    border : true,
    data: null,
    frame: true,
    labelWidth: 145,
    width: 400,
    // REDfly configs
    formElements: null,
    panelId: null,
    redflyId: null,
    store: null,
    initComponent: function() {
        this.enableBubble('selectvalue');
        this.store = new Ext.data.JsonStore({
            // Ext.data.JsonStore configs
            autoload: false,
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                url: REDfly.config.apiUrl + '/jsonstore/transcriptionfactorbindingsite/load'
            }),
            // Embedded Ext.data.JsonReader configs            
            fields: REDfly.dialog.tfbsRecord,
            idProperty: 'redfly_id',
            messageProperty: 'message',
            root: 'results',
            successProperty: 'success',
            totalProperty: 'num'
        });
        // The following fields are going to be placed in the second form at 
        // the right side of this super-form
        var itemList = [
            {
                fieldLabel: 'Curator',
                name: 'curator',
                width: 200,
                xtype: 'displayfield'
            },
            {
                fieldLabel: 'Auditor',
                name: 'auditor',
                width: 200,
                xtype: 'displayfield'
            },
            {
                fieldLabel: 'State',
                name: 'state',
                width: 200,
                xtype: 'displayfield'
            }
        ];        
        var formElements = {
            sequenceFromSpecies: this.newFormField(
                'textfield',
                'sequence_from_species_scientific_name',
                '"Sequence From" Species'
            ),
            pubmedId: this.newFormField(
                'textfield',
                'pubmed_id',
                'Pubmed ID'
            ),
            citation: this.newFormField(
                'textarea',
                'citation',
                'Citation'
            ),
            authorEmail: this.newFormField(
                'textfield',
                'author_email',
                'Author Email'
            ),
            gene: this.newFormField(
                'textfield',
                'gene_name',
                'Gene Name'
            ),            
            transcriptionFactor: this.newFormField(
                'textfield',                
                'tf_name',
                'Transcription Factor'
            ),
            name: this.newFormField(
                'textfield',
                'name',
                'Name'
            ),
            evidence: this.newFormField(
                'textfield',
                'evidence_term',
                'Evidence'
            ),
            sequence: this.newFormField(
                'textarea',
                'sequence',
                'Sequence'
            ),
            sequenceWithFlank: this.newFormField(
                'textarea',
                'sequence_with_flank',
                'Sequence With Flank'
            ),                        
            notes: this.newFormField(
                'textarea',
                'notes',
                'Notes'
            ),
            figureLabels: this.newFormField(
                'textarea',
                'figure_labels',
                'Figure Labels'
            )
        };
        for ( var prop in formElements ) {
            itemList.push(formElements[prop]);
        }
        Ext.apply(this, {
            buttons: [{
                listeners: {
                    click: function() {
                        this.selectAllFields();
                    },
                    scope: this
                },
                text: 'Select All'
            }],
            formElements: formElements,
            hideLabels: ( this.panelId !== 0 ),
            items: itemList,
            width: ( this.panelId === 0
                ? this.width
                : 250 )
        });
        REDfly.component.tfbsApprovalPanel.superclass.initComponent.apply(this, arguments);
        var storeLoad = function(store, recordList, opt) {
            if ( recordList.length === 0 ) { return; }
            // Set the internal data record so we can access it later
            var tfbsRecord = recordList[0];
            this.data = tfbsRecord;
            if ( ! tfbsRecord.get('last_update') ) {
                tfbsRecord.set('last_update', 'N/A');
            }
            if ( tfbsRecord.get('date_added') === tfbsRecord.get('last_update') ) {
                tfbsRecord.set(
                    'curator',
                    tfbsRecord.get('curator_full_name') +
                        ' (Added On ' + tfbsRecord.get('date_added_formatted') + ')'
                );
            } else {
                tfbsRecord.set(
                    'curator',
                    tfbsRecord.get('curator_full_name') +
                        ' (Last Updated On ' + tfbsRecord.get('last_update_formatted') + ')'
                );
            }
            if ( tfbsRecord.get('last_audit') === null ) {
                tfbsRecord.set(
                    'auditor',
                    'N/A'
                );
            } else {
                tfbsRecord.set(
                    'auditor',
                    tfbsRecord.get('auditor_full_name') +
                        ' (Last Audited On ' + tfbsRecord.get('last_audit_formatted') + ')'
                );
            }
            // Load any form elements with names matching a field in the record
            this.getForm().loadRecord(tfbsRecord);
            // Reloads the form to display new data
            this.doLayout();
        };
        this.store.on(
            'load',
            storeLoad,
            this
        );
        var onBeforeShowCb = function(window) {
            this.load(this.redflyId);
        };
        this.on(
            'added',
            onBeforeShowCb,
            this
        );
    },
    // --------------------------------------------------------------------------------
    // Reset the current form and load any data specified by the redfly id.
    // @param redflyId The new redfly id to load
    // --------------------------------------------------------------------------------
    load: function(redflyId) {
        this.store.load({ params: { redfly_id: redflyId } });
    },
    // --------------------------------------------------------------------------------
    // Set the REDfly id
    // @param id The redfly id
    // --------------------------------------------------------------------------------
    setRedflyId: function(redflyId) {
        this.redflyId = redflyId;
    },
    // --------------------------------------------------------------------------------
    // Set the identifier for this panel
    // @param id The panel id
    // --------------------------------------------------------------------------------
    setPanelId: function(id) {
        this.panelId = id;
    },
    // --------------------------------------------------------------------------------
    // Uncheck a radio button that has been selected on another panel.
    // This is typically called by the panel container.
    // @param label The form label of the field to be unchecked
    // --------------------------------------------------------------------------------
    uncheckRadio: function(label) {
        this.formElements[label].deselect();
    },
    // --------------------------------------------------------------------------------
    // Access the data stored in this panel.
    // In most cases the data visible to the user is returned but others return an id
    // that can be used to populate dropdown/comboboxes.
    // @param label A form label
    // @return the value associated with a form label
    // --------------------------------------------------------------------------------
    getDataValue: function(label) {
        return this.data.get(label);
    },
    // --------------------------------------------------------------------------------
    // Select all radio buttons on this panel at once.
    // This will fire an event to the approval panel to uncheck radio buttons on the
    //  other panels.
    // --------------------------------------------------------------------------------
    selectAllFields: function() {
        for ( var prop in this.formElements ) {
            this.formElements[prop].select();
        }
    },
    // --------------------------------------------------------------------------------
    // Create a new form field. This is a composite field with a text field or
    // text area and a radio button that will be selected to indicate that the
    // data in the text field is selected. On selection the radio button throws
    // a "selectValue" event which is acaught by the containing window.
    // @param fieldType Field type
    // @param fieldName The name of the form element
    // @param fieldLabel The label to be displayed next to the field
    // @returns The new composite field.
    // --------------------------------------------------------------------------------
    newFormField: function(
        fieldType,
        fieldName,
        fieldLabel
    ) {
        // Each field consistes of a composite field containing a text field
        // (with label) and a radio button.
        var composite = new Ext.form.CompositeField({
            deselect: function() {
                this.items.get(1).setValue(false);
            },
            items: [{
                disabled: true,
                fieldLabel: fieldLabel,
                name: fieldName,
                width: 200,
                xtype: fieldType
            },{
                listeners: {
                    check: function(radiobutton, isSelected) {
                        if ( isSelected ) {
                            this.fireEvent(
                                'selectvalue',
                                fieldName,
                                this.panelId
                            );
                        }
                    },
                    scope: this
                },
                name: 'r_' + fieldName,
                xtype: 'radio'
            }],
            select: function() {
                this.items.get(1).setValue(true);
            },
            setValue: function(value) {
                this.items.get(0).setValue(value);
            }
        });
        if ( fieldName === 'pubmed_id' ) {
            composite.on(
                'afterrender',
                function(field) {
                    var textfield = field.items.first();
                    field.label.setStyle({
                        color: 'blue',
                        cursor: 'pointer',
                        textDecoration: 'underline'
                    });
                    field.label.on(
                        'click',
                        function() {
                            var id = textfield.getValue(),
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
                this
            );
        }
        return composite;
    }
});
// ================================================================================
// Generate the approval panel. This consists of an (initially) empty
// tfbsPanel followed by one or more tfbsApprovalPanels. On creation the
// tfbsApprovalPanels are loaded with data and the user can select all or
// individual items on a panel to populate the tfPanel. On selection, each
// radio button fires a 'selectvalue' event to indicate that it has been
// selected and updateApprovalPanel() is called to populate the tfbsPanel with
// the selected data.
// NOTE: To allow scrolling and align each panel horizontally the approval and
// entity panels are placed into a panel with an hbox layout. Since hbox
// does not support auto scroll this panel is then placed into a window with a
// fixed size and autoscroll = true to allow scrolling.
// ================================================================================
REDfly.dialog.approveTfbs = Ext.extend(Ext.Window, {
    // Ext.Window configs
    autoScroll: true,
    closeAction: 'hide',
    modal: true,
    width: 1050,
    // REDfly configs
    btnApprove: null,
    btnReject: null,
    entityPanels: null,
    hboxPanel: null,
    mergePanel: null,
    redflyIdList: null,
    initComponent: function() {
        // Reset internal data structures.
        // Handle them here and not in the static object initializers above.
        this.entityPanels = [];
        this.redflyIdList = [];
        var mergePanel = new REDfly.component.tfbsPanel({
            mode: 'approve'
        });
        // The approve button will be enabled/disabled based on whether or not
        // the mergePanel validates.
        this.btnApprove = new Ext.Button({
            handler: function(button, event) {
                REDfly.component.approvalState = true;
                this.approve();
            },
            scope: this,
            text: 'Approve',
        });
        this.btnReject = new Ext.Button({
            handler: function(button, event) {
                this.reject();
            },
            scope: this,
            text: 'Reject'
        });
        this.hboxPanel =  new Ext.Panel({
            bodyStyle: { backgroundColor: '#F1F1F1' },
            items: [ mergePanel ],
            layout: 'hbox'
        });
        Ext.apply(this, {
            buttonAlign: 'center',
            fbar: [
                this.btnApprove,
                this.btnReject,
                new Ext.Button({
                    handler: function(button, event) {
                        this.mergePanel.data.set('action', 'cancel');
                        this.hide();
                    },
                    scope: this,
                    text: 'Cancel'
                })
            ],
            // Since the height is depends on the size of the viewport
            // it should be calculated when the window is created, not
            // when the component is defined
            height: Ext.min([
                Ext.getBody().getViewSize().height,
                700
            ]),
            items: [ this.hboxPanel ],
            mergePanel: mergePanel
        });
        // Set up the handler to watch the status of the approval panel and
        // enable/disable the Approval button based on the validation status of
        // that panel.
        REDfly.dialog.approveTfbs.superclass.initComponent.apply(this, arguments);
        // Whenever a radio button is selected it will fire a dataChange event.
        // Catch that here and update the approval panel with that data.
        this.on(
            'selectvalue',
            this.updateApprovalPanel
        );
        // The mergePanel will fire a closewindow event when it is ready to be
        // closed.
        this.on(
            'closewindow',
            this.hide
        );
        // Set up the handler to watch the status of the approval panel and
        // enable/disable the Approval button based on the validation status of
        // that panel.        
        // Add or remove monitoring of the clientvalidation event when this
        // window is shown or hidden, respectively.
        this.on(
            'beforehide',
            this.disableApprovalFormMonitor
        );
        this.on(
            'beforeshow',
            this.enableApprovalFormMonitor
        );
    },
    // --------------------------------------------------------------------------------
    // Update the targeted field of the merge panel with data from a selected field
    // of the approval panel.
    // @param {String} dataField The identifier for the form field that was selected.
    // @param {Number} panelId The identifier for the panel that was selected.
    // --------------------------------------------------------------------------------
    updateApprovalPanel: function(
        dataField,
        panelId
    ) {
        var panel = this.entityPanels[panelId];
        if ( (panel.getDataValue('date_added') === panel.getDataValue('last_update')) || 
            (panel.getDataValue('last_update') === null) ) {
            this.mergePanel.formElements.curatorDisplay.update({
                fullName: panel.getDataValue('curator_full_name'),
                timeOrderWords: 'Added On',
                formattedDate: panel.getDataValue('date_added_formatted')
            });
        } else {
            this.mergePanel.formElements.curatorDisplay.update({
                fullName: panel.getDataValue('curator_full_name'),
                timeOrderWords: 'Added On',
                formattedDate: panel.getDataValue('last_update_formatted')
            });            
        }
        if ( panel.getDataValue('last_audit') === null ) {
            this.mergePanel.formElements.auditorDisplay.update({
                fullName: panel.getDataValue('auditor_full_name'),
                timeOrderWords: '',
                formattedDate: ''
            });
        } else {
            this.mergePanel.formElements.auditorDisplay.update({
                fullName: panel.getDataValue('auditor_full_name'),
                timeOrderWords: 'Last Audited On',
                formattedDate: panel.getDataValue('last_audit_formatted')
            });            
        }        
        switch (dataField) {
            case 'evidence_term':
                this.mergePanel.setDataValue('evidence_id', panel.getDataValue('evidence_id'));
                break;
            case 'gene_name':
                this.mergePanel.setDataValue('gene_id', panel.getDataValue('gene_id'));
                break;
            case 'sequence_with_flank':
                this.mergePanel.setDataValue(dataField, panel.getDataValue(dataField));
                this.mergePanel.setDataValue('chromosome_id', panel.getDataValue('chromosome_id'));
                this.mergePanel.setDataValue('chromosome', panel.getDataValue('chromosome'));
                this.mergePanel.setDataValue('start', panel.getDataValue('start'));
                this.mergePanel.setDataValue('end', panel.getDataValue('end'));
                break;
            case 'sequence_from_species_scientific_name':
                this.mergePanel.setDataValue('sequence_from_species_id', panel.getDataValue('sequence_from_species_id'));
                this.mergePanel.setDataValue('sequence_from_species_short_name', panel.getDataValue('sequence_from_species_short_name'));
                break;                
            case 'tf_name':
                this.mergePanel.setDataValue('tf_id', panel.getDataValue('tf_id'));
                break;
            default:
                this.mergePanel.setDataValue(dataField, panel.getDataValue(dataField));
                break;
        }
        // Be sure to validate the sequence and sequence with flank since the
        // admin can select parts from different entities.
        if ( (dataField === 'sequence') ||
            (dataField ===  'sequence_with_flank') ) {
            this.mergePanel.validateSequenceWithFlank();
        }
        // Uncheck all other panels other than the one that was selected
        for ( var counter = 0;
            counter < this.entityPanels.length;
            counter++ ) {
            if ( counter !== panelId ) {
                this.entityPanels[counter].uncheckRadio(dataField);
            }
        }
    },
    // --------------------------------------------------------------------------------
    // Initialize the tfbsApprovalPanels based on the REDfly identifiers selected.
    // For each REDflyId one tfbsApprovalPanel will be added to the approval window.
    // @param redflyIdList An array of selected REDfly identifiers
    // --------------------------------------------------------------------------------
    setRedflyIds: function(redflyIdList) {
        // Since the window is hidden on close, remove any existing panels
        // from previous incarnations. Simply resetting the array will not do this!
        for ( var index = 0;
            index < this.entityPanels.length;
            index++ ) {
            this.hboxPanel.remove(this.entityPanels[index]);
        }
        this.entityPanels = [];
        this.redflyIdList = [];
        // Clear out any bits left in the tfbsPanel
        this.mergePanel.reset();
        // Since we are using an hbox layout that does not include a scroll bar,
        // tailor the width of the window to the sum of the width of the components.
        var width = this.mergePanel.width;
        for ( var counter = 0;
            counter < redflyIdList.length;
            counter++ ) {
            var redflyId = redflyIdList[counter];
            if ( redflyId.substr(0, 4) !== 'RFTF' ) {
                continue;
            }
            // Be sure to pass the formId in on creation or it will be null
            // in initComponent()
            var entityPanel = new REDfly.component.tfbsApprovalPanel({
                panelId: counter,
                redflyId: redflyId,
                title: redflyId
            });
            this.entityPanels.push(entityPanel);
            this.redflyIdList.push(redflyId);
            this.hboxPanel.add(entityPanel);
            width += entityPanel.width;
        }
        this.hboxPanel.setWidth(width);
    },
    // --------------------------------------------------------------------------------
    // Enable or disable the approve button.
    // @param {Object} form The form object.
    // @param {Boolean} valid TRUE if the form is valid.
    // --------------------------------------------------------------------------------
    updateApproveButtonState: function(form, valid) {
        if ( valid ) {
            this.btnApprove.enable();
        } else {
            this.btnApprove.disable();
        }
    },
    // --------------------------------------------------------------------------------
    // Add a listener to the approval panel form so we get clientvalidation events
    // and can update the status of our own approval button.
    // This should be called when the window is shown.
    // --------------------------------------------------------------------------------
    enableApprovalFormMonitor: function() {
        this.mergePanel.on(
            'clientvalidation',
            this.updateApproveButtonState,
            this
        );
    },
    // --------------------------------------------------------------------------------
    // Remove the listener on the approval panel form so we stop monitoring
    // clientvalidation events or we will continue to get them when the form is hidden.
    // This should be called when the window is hidden.
    // --------------------------------------------------------------------------------
    disableApprovalFormMonitor: function() {
        this.mergePanel.removeListener(
            'clientvalidation',
            this.updateApproveButtonState,
            this
        );
    },
    // --------------------------------------------------------------------------------
    // Reject the entities that are currently being viewed.
    // The auditor will have the option of providing a reason and also for deleting
    // the entries.
    // --------------------------------------------------------------------------------
    reject: function() {
        var rejectReason = new Ext.form.TextArea({
            fieldLabel: 'Reason for rejecting',
            height: 50,
            name: 'reject_reason',
            width: 500
        });
        var deleteItems = new Ext.form.Checkbox({
            fieldLabel: 'Delete pending TFBS(s)',
            name: 'delete_items'
        });
        var emailCurators = new Ext.form.Checkbox({
            checked: true,
            fieldLabel: 'Notify Curators',
            name: 'email_curators'
        });
        // Construct the list of element names and curators for the rejection process.
        var nameList = [];
        for ( var index = 0;
            index < this.entityPanels.length;
            index++ ) {
            nameList.push({
                name: this.entityPanels[index].getDataValue('name'),
                curator: this.entityPanels[index].getDataValue('curator')
            });
        }
        var rejectionWindow = new Ext.Window({
            border: false,
            items: [{
                buttons: [
                    {
                        listeners: {
                            click: function() {
                                Ext.Ajax.request({
                                    failure:  function(response, opts) {
                                        Ext.MessageBox.show({
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.ERROR,
                                            msg: Ext.util.JSON.decode(response.responseText),
                                            title: 'Error'
                                        });
                                        rejectionWindow.close();
                                        this.hide();
                                    },
                                    method: 'GET',
                                    params: {
                                        delete_items: (deleteItems.getValue() ? 'true' : 'false'),
                                        email_curators: (emailCurators.getValue() ? 'true' : 'false'),
                                        message: rejectReason.getValue(),
                                        names: Ext.util.JSON.encode(nameList),
                                        redfly_ids: Ext.util.JSON.encode(this.redflyIdList)
                                    },
                                    scope: this,
                                    success: function(response, opts) {
                                        var returnValue = Ext.decode(response.responseText);
                                        Ext.MessageBox.show({
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.INFO,
                                            msg: returnValue.message.replace(/[\n]/g, '<br>'),
                                            title: returnValue.success ? 'Success' : 'Error'
                                        });                                        
                                        rejectionWindow.close();
                                        this.hide();
                                    },
                                    url: REDfly.config.apiUrl + '/jsonstore/transcriptionfactorbindingsite/reject'
                                });
                            },
                            scope: this
                        },
                        text: 'Reject'
                    },{
                        listeners: {
                            click: function() { rejectionWindow.close(); }
                        },
                        text: 'Cancel'
                    }
                ],
                frame: true,
                items: [
                    rejectReason,
                    deleteItems,
                    emailCurators
                ],
                labelWidth: 150,
                width: 700,
                xtype: 'form'
            }],
            modal: true,
            title: 'Reject TFBS'
        });
        rejectionWindow.show();
    },
    // --------------------------------------------------------------------------------
    // Approve the entities that has been created in the mergePanel.
    // --------------------------------------------------------------------------------
    approve: function() {
        this.mergePanel.setAction('approve');
        if ( this.entityPanels.length  === 1 ) {
            this.mergePanel.setRedflyId(this.entityPanels[0].getDataValue('redfly_id'));
        }
        this.mergePanel.save(this.redflyIdList);
    }
});