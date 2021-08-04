// --------------------------------------------------------------------------------
// The rcApprovalPanel is a stripped-down version of the rcPanel used by
// the approveRc dialog and placed at its right side.
// --------------------------------------------------------------------------------
REDfly.component.rcApprovalPanel = Ext.extend(Ext.FormPanel, {
    // Ext.FormPanel configs
    autoHeight: true,
    border: true,
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
            autoLoad: false,            
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                url: REDfly.config.apiUrl + '/jsonstore/reporterconstruct/load'
            }),
            // Embedded Ext.data.JsonReader configs
            fields: REDfly.dialog.rcRecord,
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
            name: this.newFormField(
                'textfield',
                'name',
                'Name'
            ),
            fbtp: this.newFormField(
                'textfield',
                'fbtp',
                'Transgenic Construct'
            ),
            evidence: this.newFormField(
                'textfield',
                'evidence_term',
                'Evidence'
            ),
            sequenceSource: this.newFormField(
                'textfield',                
                'sequence_source_term',
                'Sequence Source'
            ),
            sequence: this.newFormField(
                'textarea',
                'sequence',
                'Sequence'
            ),
            assayedInSpecies: this.newFormField(
                'textfield',
                'assayed_in_species_scientific_name',
                '"Assayed In" Species'
            ),
            anatomicalExpressionTerms: this.newFormField(
                'textarea',
                'anatomical_expression_terms',
                'Anatomical Expression Terms'
            ),
            stagingData: this.newFormField(
                'textarea',
                'staging_data',
                'Staging Data'
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
            ),
            isOverride: this.newFormField(
                'checkbox',
                'is_override',
                'Manual CRM Override'
            ),
            isNegative: this.newFormField(
                'checkbox',                
                'is_negative',
                'Negative'
            ),
            isCrm: this.newFormField(
                'checkbox',                
                'is_crm',
                'Is CRM'
            ),
            isMinimalized: this.newFormField(
                'checkbox',
                'is_minimalized',
                'Is Minimalized'
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
        REDfly.component.rcApprovalPanel.superclass.initComponent.apply(this, arguments);
        var storeLoad = function(store, recordList, opt) {
            if ( recordList.length === 0 ) { return; }
            // Set the internal data record so we can access it later
            var rcRecord = recordList[0];
            this.data = rcRecord;
            if ( ! rcRecord.get('last_update') ) {
                rcRecord.set('last_update', 'N/A');
            }            
            if ( rcRecord.get('date_added') === rcRecord.get('last_update') ) {
                rcRecord.set(
                    'curator',
                    rcRecord.get('curator_full_name') +
                        ' (Added On ' + rcRecord.get('date_added_formatted') + ')'
                );
            } else {
                rcRecord.set(
                    'curator',
                    rcRecord.get('curator_full_name') +
                        ' (Last Updated On ' + rcRecord.get('last_update_formatted') + ')'
                );
            }
            if ( rcRecord.get('last_audit') === null ) {
                rcRecord.set(
                    'auditor',
                    'N/A'
                );
            } else {
                rcRecord.set(
                    'auditor',
                    rcRecord.get('auditor_full_name') +
                        ' (Last Audited On ' + rcRecord.get('last_audit_formatted') + ')'
                );
            }
            // Load any form elements with names matching a field in the record
            this.getForm().loadRecord(rcRecord);
            // Create a text list of the anatomical expression terms
            var anatomicalExpressionTerms = [];
            var anatomicalExpressionTermList = Ext.util.JSON.decode(this.data.get('anatomical_expression_terms'));
            Ext.each(
                anatomicalExpressionTermList,
                function (item) {
                    anatomicalExpressionTerms.push( item.term + ' (' + item.identifier + ')' );
                }
            );
            this.formElements.anatomicalExpressionTerms.setValue(anatomicalExpressionTerms.join('\n'));
            // Create a text list of the staging data
            var stagingData = [];
            var stagingDataList = Ext.util.JSON.decode(this.data.get('staging_data'));
            Ext.each(
                stagingDataList,
                function (item) {
                    stagingData.push(item.anatomical_expression_term + ' (' + item.anatomical_expression_identifier + '), ' +
                        item.stage_on_term + ' (' + item.stage_on_identifier + '), ' +
                        item.stage_off_term + ' (' + item.stage_off_identifier + '), ' +
                        item.biological_process_term + ' (' + item.biological_process_go_id + ')');
                }
            );
            this.formElements.stagingData.setValue(stagingData.join('\n'));
            // Reloads the form to display new data
            this.doLayout();
        };
        this.store.on(
            'load',
            storeLoad,
            this
        );
        this.on(
            'added',
            function() {
                this.load(this.redflyId);
            },
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
    // other panels.
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
    // a "selectValue" event which is caught by the containing window.
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
                                Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
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
// --------------------------------------------------------------------------------
// Reporter construct approval window.
// --------------------------------------------------------------------------------
REDfly.dialog.approveRc = Ext.extend(Ext.Window, {
    // Ext.Window configs
    autoScroll: true,
    closeAction: 'hide',
    modal: true,
    width: 1200,
    // REDfly configs
    btnApprove: null,
    btnReject: null,
    entityPanels: null,
    hboxPanel: null,
    mergePanel: null,
    redflyIdList: null,
    initComponent: function() {
        // Reset internal data structures. Handle them here and not in the
        // static object initializers above.
        this.entityPanels = [];
        this.redflyIdList = [];
        var mergePanel = new REDfly.component.rcPanel({
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
            text: 'Approve'
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
                new Ext.form.Checkbox({
                    boxLabel: 'Notify paper author',
                    checked: REDfly.component.notifyAuthor,
                    handler: function (checkbox, checked) {
                        REDfly.component.notifyAuthor = checked;
                    }
                }),
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
        REDfly.dialog.approveRc.superclass.initComponent.apply(this, arguments);
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
            case 'assayed_in_species_scientific_name':
                this.mergePanel.setDataValue('assayed_in_species_id', panel.getDataValue('assayed_in_species_id'));
                this.mergePanel.setDataValue('assayed_in_species_short_name', panel.getDataValue('assayed_in_species_short_name'));
                break;
            case 'evidence_term':
                this.mergePanel.setDataValue('evidence_id', panel.getDataValue('evidence_id'));
                break;
            case 'gene_name':
                this.mergePanel.setDataValue('gene_id', panel.getDataValue('gene_id'));
                break;
            case 'sequence':
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
            case 'sequence_source_term':
                this.mergePanel.setDataValue('sequence_source_id', panel.getDataValue('sequence_source_id'));
                break;
            default:
                this.mergePanel.setDataValue(dataField, panel.getDataValue(dataField));
                break;
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
    // Initialize the rcApprovalPanels based on the REDfly identifiers selected.
    // For each REDflyId one rcApprovalPanel will be added to the approval window.
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
        // Clear out any bits left in the rcPanel
        this.mergePanel.reset();
        // Since we are using an hbox layout that does not include a scroll bar,
        // tailor the width of the window to the sum of the width of the components.
        var width = this.mergePanel.width;
        for ( var counter = 0;
            counter < redflyIdList.length;
            counter++ ) {
            var redflyId = redflyIdList[counter];
            if ( redflyId.substr(0, 4) !== 'RFRC' ) {
                continue;
            }
            // Be sure to pass the formId in on creation or it will be null
            // in initComponent()
            var entityPanel = new REDfly.component.rcApprovalPanel({
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
            fieldLabel: 'Mark for deletion pending RC(s)',
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
                                    failure: function(response, opts) {
                                        Ext.MessageBox.show({
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.ERROR,
                                            msg: Ext.util.JSON.decode(response.responseText),
                                            title: 'Error'
                                        });
                                        Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
                                        rejectionWindow.close();
                                        this.hide();
                                    },                                
                                    method: 'GET',
                                    params: {
                                        delete_items: deleteItems.getValue() ? 'true' : 'false',
                                        email_curators: emailCurators.getValue() ? 'true' : 'false',
                                        message: rejectReason.getValue(),
                                        names: Ext.util.JSON.encode(nameList),
                                        redfly_ids: Ext.util.JSON.encode(this.redflyIdList)
                                    },
                                    scope: this,
                                    success: function(response, opts) {
                                        var returnValue = Ext.util.JSON.decode(response.responseText);
                                        Ext.MessageBox.show({
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.INFO,
                                            msg: returnValue.message.replace(/[\n]/g, '<br>'),
                                            title: returnValue.success ? 'Success' : 'Error'
                                        });
                                        Ext.MessageBox.getDialog().getEl().setStyle('z-index', '1000000');
                                        rejectionWindow.close();
                                        this.hide();
                                    },
                                    url: REDfly.config.apiUrl + '/jsonstore/reporterconstruct/reject'
                                });
                            },
                            scope: this
                        },
                        text: 'Reject'
                    },
                    {
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
            title: 'Reject RC'
        });
        rejectionWindow.show();
        rejectionWindow.getEl().setStyle('z-index','90000');
    },
    // --------------------------------------------------------------------------------
    // Approve the entities that has been created in the mergePanel.
    // --------------------------------------------------------------------------------
    approve: function() {
        this.mergePanel.setAction('approve');
        if ( this.entityPanels.length === 1 ) {
            this.mergePanel.setRedflyId(this.entityPanels[0].getDataValue('redfly_id'));
        }
        this.mergePanel.save(this.redflyIdList);
    }
});