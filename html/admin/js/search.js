Ext.onReady(function() {
    Ext.QuickTips.init();
    var userProfileWin = null;
    var rcCrudWindow = null;
    var tfbsCrudWindow = null;
    var crmSegmentCrudWindow = null;
    var rcApprovalWindow = null;
    var tfbsApprovalWindow = null;
    var crmSegmentApprovalWindow = null;    
    // Stores the REDfly identifier of the latest item selected in a grid
    var selectedItemId = null;
    function displayRcWindow(
        title,
        id
    ) {
        if ( ! rcCrudWindow ) {
            var rcPanel = new REDfly.component.rcPanel({
                // Panel configs
                columnWidth: 1,
                title: title,
                // REDfly configs                
                userId: REDfly.config.userId,
                userFullName: REDfly.config.userFullName,
                redflyId: id,
                mode: 'edit'
            });
            rcCrudWindow = new Ext.Window({
                // Window configs
                autoScroll: true,
                closeAction: 'hide',
                height: Ext.min([Ext.getBody().getViewSize().height, 700]),
                id: 'rcCrudWindowId',
                items: [rcPanel],
                layout: 'column',
                listeners: {
                    closewindow: function() {
                        this.hide();
                    }
                },
                modal: true,
                title: 'Reporter Construct',
                width: 750,
                // REDfly configs
                rcPanel: rcPanel
            });
            // The rc panel needs to monitor the beforeshow event of the
            // window and load itself before being displayed.
            rcPanel.mon(
                rcCrudWindow,
                'beforeshow',
                function() {
                    this.load(this.data.get('redfly_id'));
                },
                rcPanel
            );
            // When the window is closed, if new data has been saved update the
            // search panels. If the action was 'cancel' clear any selections
            // to the grid.
            searchform.mon(
                rcCrudWindow,
                'hide',
                function() {
                    if ( rcCrudWindow.rcPanel.data.get('action') !== 'cancel' ) {
                        submitSearchCb();
                    } else {
                        rcSearchResults.getGrid().getSelectionModel().clearSelections();
                    }
                }
            );
        }
        rcCrudWindow.rcPanel.setRedflyId(id);
        rcCrudWindow.rcPanel.setTitle(title);
        rcCrudWindow.show();
    }
    function displayTfbsWindow(
        title,
        id
    ) {
        if ( ! tfbsCrudWindow ) {
            var tfbsPanel = new REDfly.component.tfbsPanel({
                // Panel configs
                columnWidth: 1,
                title: title,
                // REDfly configs                
                userId: REDfly.config.userId,
                userFullName: REDfly.config.userFullName,
                redflyId: id,
                mode: 'edit'
            });
            tfbsCrudWindow = new Ext.Window({
                // Window configs
                autoScroll: true,
                closeAction: 'hide',
                height: Ext.min([Ext.getBody().getViewSize().height, 700]),
                items: [tfbsPanel],
                layout: 'column',
                listeners: {
                    closewindow: function() {
                        this.hide();
                    }
                },
                modal: true,
                title: 'Binding Site',
                width: 650,
                // REDfly configs
                tfbsPanel: tfbsPanel
            });
            // The tfbs panel needs to monitor the beforeshow event of the
            // window and load itself before being displayed.
            tfbsPanel.mon(
                tfbsCrudWindow,
                'beforeshow',
                function() {
                    this.load(this.data.get('redfly_id'));
                },
                tfbsPanel
            );
            // When the window is closed, if new data has been saved update the
            // search panels. If the action was 'cancel' clear any selections
            // to the grid.
            searchform.mon(
                tfbsCrudWindow,
                'hide',
                function() {
                    if ( tfbsCrudWindow.tfbsPanel.data.get('action') !== 'cancel' ) {
                        submitSearchCb();
                    } else {
                        tfbsSearchResults.getGrid().getSelectionModel().clearSelections();
                    }
                }
            );
        }
        tfbsCrudWindow.tfbsPanel.setRedflyId(id);
        tfbsCrudWindow.tfbsPanel.setTitle(title);
        tfbsCrudWindow.show();
    }
    function displayCrmSegmentWindow(
        title,
        id
    ) {
        if ( ! crmSegmentCrudWindow ) {
            var crmSegmentPanel = new REDfly.component.crmSegmentPanel({
                // Panel configs
                columnWidth: 1,
                title: title,
                // REDfly configs                
                userId: REDfly.config.userId,
                userFullName: REDfly.config.userFullName,
                redflyId: id,
                mode: 'edit'
            });
            crmSegmentCrudWindow = new Ext.Window({
                // Window configs
                autoScroll: true,
                closeAction: 'hide',
                height: Ext.min([Ext.getBody().getViewSize().height, 700]),
                id: 'crmSegmentCrudWindowId',
                items: [crmSegmentPanel],
                layout: 'column',
                listeners: {
                    closewindow: function() {
                        this.hide();
                    }
                },
                modal: true,
                title: 'CRM Segment',
                width: 750,
                // REDfly configs
                crmSegmentPanel: crmSegmentPanel
            });
            // The CRM segment panel needs to monitor the beforeshow event of
            // the window and load itself before being displayed.
            crmSegmentPanel.mon(
                crmSegmentCrudWindow,
                'beforeshow',
                function() {
                    this.load(this.data.get('redfly_id'));
                },
                crmSegmentPanel
            );
            // When the window is closed, if new data has been saved update the
            // search panels. If the action was 'cancel' clear any selections
            // to the grid.
            searchform.mon(
                crmSegmentCrudWindow,
                'hide',
                function() {
                    if ( crmSegmentCrudWindow.crmSegmentPanel.data.get('action') !== 'cancel' ) {
                        submitSearchCb();
                    } else {
                        crmSegmentSearchResults.getGrid().getSelectionModel().clearSelections();
                    }
                }
            );
        }
        crmSegmentCrudWindow.crmSegmentPanel.setRedflyId(id);
        crmSegmentCrudWindow.crmSegmentPanel.setTitle(title);
        crmSegmentCrudWindow.show();
    }
    function displayRcApprovalWindow(
        title,
        entityIds
    ) {
        if ( entityIds.length === 0 ) { return; }
        // The approval window is deleted and re-created each time it is used.
        if ( ! rcApprovalWindow ) {
            rcApprovalWindow = new REDfly.dialog.approveRc({ title: title });
            // When the window is closed, if new data has been saved update the
            // search panels. If the action was 'cancel' clear any selections
            // to the grid.
            searchform.mon(
                rcApprovalWindow,
                'hide',
                function() {
                    if ( rcApprovalWindow.mergePanel.data.get('action') !== 'cancel' ) {
                        submitSearchCb();
                    } else {
                        rcSearchResults.getGrid().getSelectionModel().clearSelections();
                    }
                }
            );
        }
        rcApprovalWindow.setRedflyIds(entityIds);
        rcApprovalWindow.show();
    }
    function displayTfbsApprovalWindow(
        title,
        entityIds
    ) {
        if ( entityIds.length === 0 ) { return; }
        // The approval window is deleted and re-created each time it is used.
        if ( ! tfbsApprovalWindow ) {
            tfbsApprovalWindow = new REDfly.dialog.approveTfbs({ title: title });
            // When the window is closed, if new data has been saved update the
            // search panels. If the action was 'cancel' clear any selections
            // to the grid.
            searchform.mon(
                tfbsApprovalWindow,
                'hide',
                function() {
                    if ( tfbsApprovalWindow.mergePanel.data.get('action') !== 'cancel' ) {
                        submitSearchCb();
                    } else {
                        tfbsSearchResults.getGrid().getSelectionModel().clearSelections();
                    }
                }
            );
        }
        tfbsApprovalWindow.setRedflyIds(entityIds);
        tfbsApprovalWindow.show();
    }
    function displayCrmSegmentApprovalWindow(
        title,
        entityIds
    ) {
        if ( entityIds.length === 0 ) { return; }
        // The approval window is deleted and re-created each time it is used.
        if ( ! crmSegmentApprovalWindow ) {
            crmSegmentApprovalWindow = new REDfly.dialog.approveCrmSegment({ title: title });
            // When the window is closed, if new data has been saved update the
            // search panels. If the action was 'cancel' clear any selections
            // to the grid.
            searchform.mon(
                crmSegmentApprovalWindow,
                'hide',
                function() {
                    if ( crmSegmentApprovalWindow.mergePanel.data.get('action') !== 'cancel' ) {
                        submitSearchCb();
                    } else {
                        crmSegmentSearchResults.getGrid().getSelectionModel().clearSelections();
                    }
                }
            );
        }
        crmSegmentApprovalWindow.setRedflyIds(entityIds);
        crmSegmentApprovalWindow.show();
    }
    // --------------------------------------------------------------------------------
    // Create the administrative menu for the top of the form.
    // This will include a user profile editor button and an "administrative menu"
    // dropdown if the user has the admin role.
    // --------------------------------------------------------------------------------    
    var toolbar = new Ext.Toolbar({
        style: 'border: none; background: none',
        items: [ '->' ]
    });
    if ( REDfly.config.isAdmin ) {
        toolbar.add({
            menu: [{
                handler: function() { 
                    new REDfly.component.ReportList();
                },
                text: 'Download Reports'
            },{
                handler: function() {
                    window.open(REDfly.config.baseUrl + 'admin/google_analytics.php', '_blank') ||
                    window.location.replace(REDfly.config.baseUrl + 'admin/google_analytics.php');
                },
                text: 'Check Audiences'
            },
            ],
            text: 'Administrative Menu'
        });
    }
    toolbar.add(
        {
            handler: function() {
                if ( ! userProfileWin ) {
                    userProfileWin = new REDfly.dialog.userProfile({
                        title: 'User Profile',
                        userId: REDfly.config.userId
                    });
                }
                userProfileWin.show();
            },
            text: 'Profile'
        },{
            handler: function() {
                // Since there is no real way to log out of http basic auth,
                // simulate a logout by specifying a fake user and forcing the
                // browser to request for the username and password. Once that dialog
                // is cancelled the failure handler is called to redirect the
                // browser to a non-protected location.
                Ext.Ajax.request({
                    failure: function(response, opts) {
                        document.location.href = REDfly.config.baseUrl + 'logout.php';
                    },
                    success: function(response, opts) {
                        document.location.href = REDfly.config.baseUrl + 'logout.php';
                    },
                    url: REDfly.config.baseUrl.replace('://', '://logout@') + 'admin/'
                });
            },
            text: 'Logout'
        }
    );
    // --------------------------------------------------------------------------------
    // Create the search form
    // --------------------------------------------------------------------------------
    var sequenceFromSpeciesSearch = new REDfly.component.sequenceFromSpeciesComboBox();
    var assayedInSpeciesSearch = new REDfly.component.assayedInSpeciesComboBox();
    var chromosomeSearch = new REDfly.component.selectChromosome();
    chromosomeSearch.sequenceFromSpeciesField = sequenceFromSpeciesSearch;
    var startCoordinateSearch = new REDfly.component.selectStartCoordinate();
    var endCoordinateSearch = new REDfly.component.selectEndCoordinate();
    var geneSearch  = new REDfly.component.selectGene();
    geneSearch.sequenceFromSpeciesField = sequenceFromSpeciesSearch;
    var transcriptionFactorSearch  = new REDfly.component.selectTranscriptionFactor();
    transcriptionFactorSearch.sequenceFromSpeciesField = sequenceFromSpeciesSearch;
    var enhancerOrSilencerAttributeSearch = new REDfly.component.selectEnhancerOrSilencerAttribute();
    // --------------------------------------------------------------------------------
    // Handle search submissions. This may be called from multiple places including
    // the search button handler, or after to update the search results after closing
    // the editing/approval window.
    // --------------------------------------------------------------------------------
    var submitSearchCb = function() {
        var name = Ext.getCmp('element_name').getValue();
        var curatorId = Ext.getCmp('curator_id').getValue();
        var status = Ext.getCmp('status').getValue();
        var pubmedId = Ext.getCmp('pubmedId').getValue();
        var chrId = chromosomeSearch.getValue();
        var start = startCoordinateSearch.getValue();
        var end = endCoordinateSearch.getValue();
        var geneId = geneSearch.getValue();
        var transcriptionFactorId = transcriptionFactorSearch.getValue();
        var enhancerOrSilencerAttribute = enhancerOrSilencerAttributeSearch.getValue(); 
        var rcParameters = {};
        var tfbsParameters = {};
        var crmSegmentParameters = {};
        var predictedCrmParameters = {};
        if ( sequenceFromSpeciesSearch.getValue() !== '' ) {
            rcParameters.sequence_from_species_id = sequenceFromSpeciesSearch.getValue();
            tfbsParameters.sequence_from_species_id = sequenceFromSpeciesSearch.getValue();
            crmSegmentParameters.sequence_from_species_id = sequenceFromSpeciesSearch.getValue();
            predictedCrmParameters.sequence_from_species_id = sequenceFromSpeciesSearch.getValue();
        }
        if ( assayedInSpeciesSearch.getValue() !== '' ) {
            rcParameters.assayed_in_species_id = assayedInSpeciesSearch.getValue();
            tfbsParameters.assayed_in_species_id = assayedInSpeciesSearch.getValue();
            crmSegmentParameters.assayed_in_species_id = assayedInSpeciesSearch.getValue();
            predictedCrmParameters.assayed_in_species_id = assayedInSpeciesSearch.getValue();
        }        
        if ( name !== '' ) {
            rcParameters.name = name + '*';
            tfbsParameters.name = name + '*';
            crmSegmentParameters.name = name + '*';
            predictedCrmParameters.name = name + '*';
        }
        if ( curatorId !== '' ) {
            rcParameters.curator_id = curatorId;
            tfbsParameters.curator_id = curatorId;
            crmSegmentParameters.curator_id = curatorId;
            predictedCrmParameters.curator_id = curatorId;

        }
        if ( status !== '' ) {
            rcParameters.state = status;
            tfbsParameters.state = status;
            crmSegmentParameters.state = status;
            predictedCrmParameters.state = status;
        }
        if ( pubmedId !== '' ) {
            rcParameters.pubmed_id = pubmedId;
            tfbsParameters.pubmed_id = pubmedId;
            crmSegmentParameters.pubmed_id = pubmedId;
            predictedCrmParameters.pubmed_id = pubmedId;
        }
        if ( chrId !== '' ) {
            rcParameters.chr_id = chrId;
            tfbsParameters.chr_id = chrId;
            crmSegmentParameters.chr_id = chrId;
            predictedCrmParameters.chr_id = chrId;
        }
        if ( start !== '' ) {
            rcParameters.chr_start = start;
            tfbsParameters.chr_start = start;
            crmSegmentParameters.chr_start = start;
            predictedCrmParameters.chr_start = start;
        }
        if ( end !== '' ) {
            rcParameters.chr_end = end;
            tfbsParameters.chr_end = end;
            crmSegmentParameters.chr_end = end;
            predictedCrmParameters.chr_end = end;
        }
        if ( geneId !== '' ) {
            rcParameters.gene_id = geneId;
            tfbsParameters.gene_id = geneId;
            crmSegmentParameters.gene_id = geneId;
            // Gene identifier not applied for predicted CRMs
            predictedCrmParameters.gene_id = geneId;
        }
        if ( transcriptionFactorId !== '' ) {
            tfbsParameters.transcription_factor_id = transcriptionFactorId;
            // Transcription factor identifier not applied for reporter constructs,
            // CRM segments, and predicted CRMs
            rcParameters.transcription_factor_id = transcriptionFactorId;
            crmSegmentParameters.transcription_factor_id = transcriptionFactorId;
            predictedCrmParameters.transcription_factor_id = transcriptionFactorId;
        }
        //if ( enhancerOrSilencerAttribute !== '' ) {
        //    if ( enhancerOrSilencerAttribute === 'enhancer' ) {
        //        rcParameters.enhancer_attribute_included = enhancerOrSilencerAttribute;
        //        crmSegmentParameters.enhancer_attribute_included = enhancerOrSilencerAttribute;
        //        predictedCrmParameters.enhancer_attribute_included = enhancerOrSilencerAttribute;
        //        // Enhancer attribute not applied for TFBSs
        ////        tfbsParameters.enhancer_attribute_included = enhancerOrSilencerAttribute;
        //    } else {
        //        rcParameters.silencer_attribute_included = enhancerOrSilencerAttribute;
        //        crmSegmentParameters.silencer_attribute_included = enhancerOrSilencerAttribute;
        //        predictedCrmParameters.silencer_attribute_included = enhancerOrSilencerAttribute;
        //        // Silencer attribute not applied for TFBSs
        //        tfbsParameters.silencer_attribute_included = enhancerOrSilencerAttribute;
        //    }
        //}
        rcSearchResults.load(rcParameters);
        tfbsSearchResults.load(tfbsParameters);
        crmSegmentSearchResults.load(crmSegmentParameters);
        predictedCrmSearchResults.load(predictedCrmParameters);
    };
    // --------------------------------------------------------------------------------
    // Create the search form
    // --------------------------------------------------------------------------------
    var searchform = new Ext.form.FormPanel({
        autoHeight: true,
        buttonAlign: 'center',
        buttons: [
            {
                // Used with 'monitorValid: true' to disable submit if invalid fields exist
                formBind: true,
                id: 'button_search',
                // When using the 'handler:' I could not trap a click event fired
                // on an enter keypress.
                listeners: {
                    click: submitSearchCb
                },
                text: 'Search'
            },{
                text: 'Create',
                id: 'button_create',
                menu: new Ext.menu.Menu({
                    items: [
                        {
                            handler: function() {
                                displayRcWindow(
                                    'New Reporter Construct',
                                    null
                                );
                            },
                            text: 'RC/CRM'
                        },
                        {
                            handler: function() {
                                displayTfbsWindow(
                                    'New TFBS',
                                    null
                                );
                            },
                            text: 'TFBS'
                        },
                        {
                            handler: function() {
                                displayCrmSegmentWindow(
                                    'New CRM Segment',
                                    null
                                );
                            },
                            text: 'CRM Segment'
                        }
                    ]
                }),
                handler: function() {
                    switch(resultTabs.getActiveTab().getItemId()) {
                        case 'rcTab':
                            displayRcWindow(
                                'New Reporter Construct',
                                null
                            );
                            break;
                        case 'tfbsTab':
                            displayTfbsWindow(
                                'New TFBS',
                                null
                            );
                            break;
                        case 'crmSegmentTab':
                            displayCrmSegmentWindow(
                                'New CRM Segment',
                                null
                            );
                            break;
                        case 'predictedCrmTab':
                            break;
                        default:
                    }
                },
                xtype: 'splitbutton'
            },{
                handler: function() {
                    var results;
                    switch(resultTabs.getActiveTab().getItemId()) {
                        case 'rcTab':
                            results = rcSearchResults;
                            break;
                        case 'tfbsTab':
                            results = tfbsSearchResults;
                            break;
                        case 'crmSegmentTab':
                            results = crmSegmentSearchResults;
                            break;
                        case 'predictedCrmTab':
                            results = predictedCrmSearchResults;
                            break;
                        default:
                            results = null;
                    }
                    if ( results === null ) { return; }
                    var selections = results.getGrid().getSelectionModel().getSelections();
                    var archivedItemList = [];
                    Ext.each(selections, function(record) {
                        if ( record.get('state') === 'archived' ) {
                            archivedItemList.push(record);
                        }
                    });
                    if ( archivedItemList.length !== 0 ) {
                        var errorMessage = 'The archived version of the entity: ' +
                            archivedItemList[0].get('name') +  
                            ' can not be edited';
                        Ext.Msg.alert('Error', errorMessage.replace(' ', '&nbsp;'));
                        return;
                    }
                    var nonEditableItemList = [];
                    Ext.each(selections, function(record) {
                        if ( record.get('editable') === '0' ) {
                            nonEditableItemList.push(record);
                        }
                    });
                    if ( nonEditableItemList.length !== 0 ) {
                        var errorMessage = 'There is already an edited version of the entity: ' +
                            nonEditableItemList[0].get('name');
                        Ext.Msg.alert('Error', errorMessage.replace(' ', '&nbsp;'));
                        return;
                    }
                    if ( selectedItemId ) {
                        switch(resultTabs.getActiveTab().getItemId()) {
                            case 'rcTab':
                                displayRcWindow(
                                    'Edit Reporter Construct',
                                    selectedItemId
                                );
                                break;
                            case 'tfbsTab':
                                displayTfbsWindow(
                                    'Edit TFBS',
                                    selectedItemId
                                );
                                break;
                            case 'crmSegmentTab':
                                displayCrmSegmentWindow(
                                    'Edit CRM Segment',
                                    selectedItemId
                                );
                                break;
                            case 'predictedCrmTab':
                                Ext.MessageBox.show({
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.WARNING,
                                    msg: 'Predicted CRMs can not be edited by any curator at the moment'.replace(' ', '&nbsp;'),
                                    title: 'Warning'
                                });
                                break;
                            default:
                        }
                    }
                },
                text: 'Edit'
            },{
                handler: function() {
                    var results;
                    switch(resultTabs.getActiveTab().getItemId()) {
                        case 'rcTab':
                            results = rcSearchResults;
                            break;
                        case 'tfbsTab':
                            results = tfbsSearchResults;
                            break;
                        case 'crmSegmentTab':
                            results = crmSegmentSearchResults;
                            break;
                        case 'predictedCrmTab':
                            results = predictedCrmSearchResults;
                            break;
                        default:
                            results = null;
                    }
                    if ( results === null ) { 
                        return;
                    }
                    var selections = results.getGrid().getSelectionModel().getSelections();
                    var selectedIdList = [];
                    var nonApprovalItemList = [];
                    Ext.each(selections, function(record) {
                        if ( record.get('state') !== 'approval' ) {
                            nonApprovalItemList.push(record);
                        }
                        selectedIdList.push(record.get('redfly_id'));
                    });
                    if ( nonApprovalItemList.length !== 0 ) {
                        var errorMessage = 'The non-approval version of the entity: ' +
                            nonApprovalItemList[0].get('name') +  
                            ' can not be approved';
                        Ext.Msg.alert('Error', errorMessage.replace(' ', '&nbsp;'));
                        return;
                    }
                    switch(resultTabs.getActiveTab().getItemId()) {
                        case 'rcTab':
                            displayRcApprovalWindow(
                                'Approve RC/CRM',
                                selectedIdList
                            );
                            break;
                        case 'tfbsTab':
                            displayTfbsApprovalWindow(
                                'Approve TFBS',
                                selectedIdList
                            );
                            break;
                        case 'crmSegmentTab':
                            displayCrmSegmentApprovalWindow(
                                'Approve CRM Segment',
                                selectedIdList
                            );
                            break;
                        case 'predictedCrmTab':
                            Ext.MessageBox.show({
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.WARNING,
                                msg: 'Predicted CRMs can not be approved by any auditor at the moment'.replace(' ', '&nbsp;'),
                                title: 'Warning'
                            });
                            break;
                        default:
                    }
                },
                listeners: {
                    // If the current user is not an administrator then do not show the 'Approve' button
                    beforerender: function() {
                        if ( ! REDfly.config.isAdmin ) { this.setVisible(false); }
                    }
                },
                scope: this,
                text: 'Approve'
            },{
                handler: function () {
                    window.open(
                        '/admin/new#import',
                        null,
                        'width=800,height=600'
                    );
                },
                text: 'Batch Import'
            },{
                handler: function () {
                    window.open(
                        '/admin/new#audit',
                        null,
                        'width=1800,height=730'
                    );
                },
                text: 'Batch Audit'
            },{
                handler: function() {
                    Ext.getCmp('curator_search').getForm().reset();
                    sequenceFromSpeciesSearch.current_genome_assembly_release_version = null;
                    sequenceFromSpeciesSearch.id = null;
                    sequenceFromSpeciesSearch.scientific_name = null;
                    sequenceFromSpeciesSearch.short_name = null;
                    rcSearchResults.reset();
                    tfbsSearchResults.reset();
                    crmSegmentSearchResults.reset();
                    predictedCrmSearchResults.reset();
                },
                text: 'Reset'
            }
        ],
        frame: true,
        id: 'curator_search',
        items: [{
            items: [
                {
                    columnWidth: 0.2,
                    items: [
                        sequenceFromSpeciesSearch,
                        assayedInSpeciesSearch,
                        {
                            fieldLabel: 'Element Name',
                            id: 'element_name',
                            name: 'element_name',
                            xtype: 'textfield'
                        },
                        REDfly.component.selectCurator
                    ],
                    layout: 'form'
                }, {
                    columnWidth: 0.2,
                    items: [
                        REDfly.component.selectStatus,
                        {
                            fieldLabel: 'PMID',
                            id: 'pubmedId',
                            name: 'pubmedId',
                            regex: /^[0-9]+$/,
                            xtype: 'textfield'
                        },
                        geneSearch,
                        transcriptionFactorSearch
                    ],
                    layout: 'form'
                }, {
                    columnWidth: 0.3,
                    items: [
                        {   
                            fieldLabel: 'Chromosome',
                            items: [
                                chromosomeSearch,
                                {
                                    value: ':',
                                    xtype: 'displayfield'
                                },
                                startCoordinateSearch,
                                {
                                    value: '..',
                                    xtype: 'displayfield'                
                                },
                                endCoordinateSearch,
                            ],
                            layout: 'column'
                        },
                        //enhancerOrSilencerAttributeSearch
                    ],
                    layout: 'form'
                }
            ],
            layout: 'column'
        }],
        keys: [
            {
                key: [ Ext.EventObject.ENTER ],
                handler: function() {
                    // If the submit button has not been disabled by the 'monitorValid' property 
                    // then simulate a click event on the button when the return key is pressed 
                    // in a form element.
                    var searchButton = Ext.getCmp('button_search');
                    if ( ! searchButton.disabled ) {
                        searchButton.fireEvent('click');
                    }
                }
            }
        ],
        labelAlign: 'top',
        monitorValid: true,
        standardSubmit: true,
        tbar: toolbar,
        title: 'REDfly Administration Tools',
        width: 1400
    });
    // --------------------------------------------------------------------------------
    // Create the grid panels and click handlers for the search results and put
    // them into the tab panel.
    // --------------------------------------------------------------------------------    
    // A single-click on a row selects an item
    var rowClickCb = function(grid, rowIndex, event) {
        var record = grid.getStore().getAt(rowIndex);
        selectedItemId = record.get('redfly_id');
    };
    // A double-click on a row opens an item for editing
    var rcRowDblClickCb = function(grid, rowIndex, event) {
        var record = grid.getStore().getAt(rowIndex);
        if ( record.get('state') !== 'archived' )  {
            displayRcWindow(
                'Edit Reporter Construct',
                record.get('redfly_id')
            );
        }
    };
    var tfbsRowDblClickCb = function(grid, rowIndex, event) {
        var record = grid.getStore().getAt(rowIndex);
        if ( record.get('state') !== 'archived' )  {
            displayTfbsWindow(
                'Edit TFBS',
                record.get('redfly_id')
            );
        }
    };
    var crmSegmentRowDblClickCb = function(grid, rowIndex, event) {
        var record = grid.getStore().getAt(rowIndex);
        if ( record.get('state') !== 'archived' )  {
            displayCrmSegmentWindow(
                'Edit CRM Segment',
                record.get('redfly_id')
            );
        }
    };
    var predictedCrmRowDblClickCb = function(grid, rowIndex, event) {
        var record = grid.getStore().getAt(rowIndex);
        if ( record.get('state') !== 'archived' )  {
            Ext.MessageBox.show({
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING,
                msg: 'Predicted CRMs can not be edited by any curator at the moment'.replace(' ', '&nbsp;'),
                title: 'Warning'
            });            
        }
    };    
    var rcSearchResults = new REDfly.component.searchResultTab({
        autoHeight: true,
        entity: 'rc',
        id: 'rcTab',
        store: REDfly.store.rcSearchResults,
        title: 'RC/CRM',
        rowClickHandler: rowClickCb,
        rowDblClickHandler: rcRowDblClickCb
    });
    var tfbsSearchResults = new REDfly.component.searchResultTab({
        autoHeight: true,
        entity: 'tfbs',
        id: 'tfbsTab',
        store: REDfly.store.tfbsSearchResults,
        title: 'TFBS',
        rowClickHandler: rowClickCb,
        rowDblClickHandler: tfbsRowDblClickCb
    });
    var crmSegmentSearchResults = new REDfly.component.searchResultTab({
        autoHeight: true,
        entity: 'crmSegment',
        id: 'crmSegmentTab',
        store: REDfly.store.crmSegmentSearchResults,
        title: 'CRM Segment',
        rowClickHandler: rowClickCb,
        rowDblClickHandler: crmSegmentRowDblClickCb
    });
    var predictedCrmSearchResults = new REDfly.component.searchResultTab({
        autoHeight: true,
        entity: 'predictedCrm',
        id: 'predictedCrmTab',
        store: REDfly.store.predictedCrmSearchResults,
        title: 'Predicted CRM',
        rowClickHandler: rowClickCb,
        // No predicted CRM record is editable at the moment
        rowDblClickHandler: predictedCrmRowDblClickCb
    });    
    var resultTabs = new Ext.TabPanel({
        activeTab: 0,
        autoHeight: true,
        items: [ 
            rcSearchResults,
            tfbsSearchResults,
            crmSegmentSearchResults,
            predictedCrmSearchResults
        ],
        width: 1400
    });
    // Render the search and results tabs into a panel for display
    var admintools = new Ext.Panel({
        autoHeight: true,
        items: [
            searchform,
            resultTabs
        ],
        //layout: 'anchor',
        renderTo: 'admintools'
    });
});