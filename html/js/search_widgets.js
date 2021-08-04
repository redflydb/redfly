// ==========================================================================================
// Various widgets that are reusable throughout the user interface.
// You can create a reusable object in ExtJs using the Ext.extend() method to create a subclass and
// optionally override members with the passed literal/static object.
// You can then create multiple new objects and override options is desired.
// myComboBox = Ext.extend(Ext.form.ComboBox, {
//     fieldLabel: '<b>File Type</b>',
//     editable: false,
//     forceSelection: true,
//     displayField: 'filetype',
//     mode: 'local',
//     triggerAction: 'all',
//     # We override initComponent instead of the constructor because the more basic initialization
//     # will already have taken place. Most notably, the config object passed to the constructor will
//     # have already been merged into the object.
//     initComponent: function() {
//         # Create child components, stores
//         var itemList = [ ... ];
//         # Apply local variables to object (i.e., copy values from one object into another)
//         Ext.apply(this, {
//             items: itemList
//         });
//         # Call the superclass initComponent (essentiallt the constructor)
//         myComboBox.superclass.initComponent.apply(this, arguments);
//     }
// });
// var combo = new myComboBox({fieldLabel: 'Override Field Label'});
// ==========================================================================================
// Namespaces that are available globally
Ext.namespace(
    // Main redfly namespace
    'REDfly',
    // Configuration options
    'REDfly.config',
    // Search widgets
    'REDfly.search',
    // Stores
    'REDfly.store',
    // Download components
    'REDfly.download',
    // Reusable window components
    'REDfly.window',
    // State information
    'REDfly.state',
    // Ext templates
    'REDfly.templates'
);
REDfly.store.species = new Ext.data.JsonStore({
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
REDfly.store.species.load();
// ------------------------------------------------------------------------------------------
// ComboBox for selecting the species.
// ------------------------------------------------------------------------------------------
REDfly.download.speciesComboBox = Ext.extend(Ext.form.ComboBox, {
    displayField: 'display',
    editable: false,
    fieldLabel: '<b>"Sequence From" Species</b>',
    forceSelection: true,
    labelStyle: 'width:160px',
    listeners: {
        render: function(c) {
            new Ext.ToolTip({
                target: c.getEl(),
                html: 'Select the species to which the regulatory element belongs for the batch download'
            });
        }
    },    
    mode: 'local',
    selectOnFocus: true,
    store: REDfly.store.species,
    triggerAction: 'all',
    // The species identifier of the Drosophila melanogaster one by default
    valueField: 'scientific_name',
    width: 195,
    initComponent: function() {
        REDfly.download.speciesComboBox.superclass.initComponent.apply(this, arguments);
    }
});
// ------------------------------------------------------------------------------------------
// Valid file download formats
// ------------------------------------------------------------------------------------------
REDfly.store.fileType = new Ext.data.ArrayStore({
    data: [
        [0, 'BED'],
        [1, 'CSV'],
        [2, 'FASTA'],
        [3, 'GBrowse'],
        [4, 'GFF3']
    ],
    fields: [
        'id',
        'filetype'
    ],
    id: 0
});
// ------------------------------------------------------------------------------------------
// Each file format may have an optional set of download options.
// Set up a list of option panels to be displayed when a user selects the corresponding
// download type. These are set up initially using the values of the file type store and
// populated later as the panels are defined.
// When populated
// obj: the constructor for the options panel
// urlExtras: An Ext.Template for generating additional url parameters based on the options panel
// ------------------------------------------------------------------------------------------
REDfly.download.optionsPanelList = {};
REDfly.store.fileType.each( function(record, index) {
    REDfly.download.optionsPanelList[record.get('filetype')] = null;
});
REDfly.store.bedDownloadOptions = new Ext.data.ArrayStore({
    data: [
        [0, 'Bed Simple', 'simple'],
        [1, 'Bed Browser', 'browser'],
    ],
    fields: [
        'id',
        'option',
        'param'
    ],
    id: 0
});
REDfly.store.fastaDownloadOptions = new Ext.data.ArrayStore({
    data: [
        [0, 'Sequence Only', 'seq'],
        [1, 'Sequence with Flank', 'flank'],
        [2, 'Both', 'both']
    ],
    fields: [
        'id',
        'option',
        'param'
    ],
    id: 0
});
// ------------------------------------------------------------------------------------------
// ComboBox for selecting download file format. Note that some file formats have axillary options
// available and these options should be displayed in the download window when the corresponding
// format is selected.
// ------------------------------------------------------------------------------------------
REDfly.download.fileTypeComboBox = Ext.extend(Ext.form.ComboBox, {
    displayField: 'filetype',
    editable: false,
    fieldLabel: '<b>File Type</b>',
    forceSelection: true,
    labelStyle: 'width:160px',
    mode: 'local',
    selectOnFocus: true,
    store: REDfly.store.fileType,
    triggerAction: 'all',
    // Default to the first item in the store
    value: REDfly.store.fileType.getAt(0).get('filetype'),
    width: 195,
    initComponent: function() {
        REDfly.download.fileTypeComboBox.superclass.initComponent.apply(this, arguments);
    }
});
// ------------------------------------------------------------------------------------------
// Panel for selecting BED file format download options
// ------------------------------------------------------------------------------------------
REDfly.download.bedOptions = Ext.extend(Ext.Panel, {
    hidden: false,
    layout: 'form',
    // Internal state settings. These will be applied to the download url template for file options.
    fileTypeOptions: null,
    initComponent: function() {
        // Must assign the options in initComponent or the static object will be shared amongst all
        // instances
        this.fileTypeOptions = {
            bedFileType: null,
            trackDescription: null,
            trackName: null
        };
        var bedFileTypeComboBox = new Ext.form.ComboBox({
            displayField: 'option',
            editable: false,
            fieldLabel: '<b>BED File Type</b>',
            forceSelection: true,
            labelStyle: 'width:160px',
            lazyInit: false,
            mode: 'local',
            selectOnFocus: true,
            store: REDfly.store.bedDownloadOptions,
            triggerAction: 'all',
            value: REDfly.store.bedDownloadOptions.getAt(0).get('param'),
            valueField: 'param',
            width: 195
        });
        var bedTrackNameTextField = new Ext.form.TextField({
            fieldLabel: '<b>Track Name</b>',
            id: 'bedTrackNameValue',
            labelStyle: 'width:160px',
            value: 'CRMs',
            width: 195
        });
        var bedTrackDescriptionTextField = new Ext.form.TextField({
            fieldLabel: '<b>Track Description</b>',
            id: 'bedTrackDescriptionValue',
            labelStyle: 'width:160px',
            value: 'CRMs selected from REDfly',
            width: 195
        });
        var isAddedBEDTrackNameDescription = false;
        // Set the default
        this.fileTypeOptions.bedFileType = bedFileTypeComboBox.getValue();
        this.fileTypeOptions.trackName = bedTrackNameTextField.getValue();
        this.fileTypeOptions.trackDescription = bedTrackDescriptionTextField.getValue();
        // Show BED track name and description field when 'browser' option is selected
        bedFileTypeComboBox.on(
            'select',
            function(field) {
                this.fileTypeOptions.bedFileType = field.getValue();
                if ( this.fileTypeOptions.bedFileType === 'browser' ) {
                    if ( ! isAddedBEDTrackNameDescription ) {
                        this.add(bedTrackNameTextField);
                        this.add(bedTrackDescriptionTextField);
                        isAddedBEDTrackNameDescription = true;
                    } else {
                        bedTrackNameTextField.show();
                        bedTrackDescriptionTextField.show();
                    }
                } else if ( this.fileTypeOptions.bedFileType === 'simple' ) {
                    bedTrackNameTextField.hide();
                    bedTrackDescriptionTextField.hide();
                }
                this.doLayout(true);
            },
            this
        );
        bedTrackNameTextField.on(
            'change',
            function(field) {
                this.fileTypeOptions.trackName = field.getValue();
            },
            this
        );
        bedTrackDescriptionTextField.on(
            'change',
            function(field) {
                this.fileTypeOptions.trackDescription = field.getValue();
            },
            this
        );
        Ext.apply(
            this,
            {
                items: [{
                    items: [ bedFileTypeComboBox ],
                    // Without the form layout the field label will not display
                    layout: 'form'
                }],
            }
        );
        REDfly.download.bedOptions.superclass.initComponent.apply(this, arguments);
    }
});
// Add an entry for this panel to the list option panels.
REDfly.download.optionsPanelList['BED'] = {
    obj: REDfly.download.bedOptions,
    // Extra parameters for the api call to support the options in the panel
    urlExtras: new Ext.Template('bed_file_type={bedFileType}&bed_track_name={trackName}&bed_track_description={trackDescription}')
}
// ------------------------------------------------------------------------------------------
// Panel for selecting FASTA file format download options
// ------------------------------------------------------------------------------------------
REDfly.download.fastaOptions = Ext.extend(Ext.Panel, {
    hidden: false,
    // Internal state settings. These will be applied to the download url template for file options.
    fileTypeOptions: null,
    initComponent: function() {
        // Must assign the options in initComponent or the static object will be shared amonst all
        // instances
        this.fileTypeOptions = {
            sequenceType: null
        };
        var fastaOptionsComboBox = new Ext.form.ComboBox({
            displayField: 'option',
            editable: false,
            fieldLabel: '<b>FASTA Options</b>',
            forceSelection: true,
            labelStyle: 'width:160px',
            lazyInit: false,
            mode: 'local',
            selectOnFocus: true,
            // We are using the same store for multiple widgets. May need to reset state.
            store: REDfly.store.fastaDownloadOptions,
            triggerAction: 'all',
            value: REDfly.store.fastaDownloadOptions.getAt(0).get('param'),
            valueField: 'param',
            width: 195
        });
        // Set the default
        this.fileTypeOptions.sequenceType = fastaOptionsComboBox.getValue();
        // Set internal state when selected
        fastaOptionsComboBox.on(
            'select',
            function(combo, record, index) {
                this.fileTypeOptions.sequenceType = combo.getValue();
            },
            this
        );
        Ext.apply(
            this,
            {
                items: [{
                    items: [ fastaOptionsComboBox ],
                    // Without the form layout the field label will not display
                    layout: 'form'
                }]
            }
        );
        REDfly.download.fastaOptions.superclass.initComponent.apply(this, arguments);
    }
});
// Add an entry for this panel to the list option panels.
REDfly.download.optionsPanelList['FASTA'] = {
    obj: REDfly.download.fastaOptions,
    // Extra parameters for the API call to support the options in the panel
    urlExtras: new Ext.Template('fasta_seq={sequenceType}')
}
// ------------------------------------------------------------------------------------------
// Download window for selecting options for downloaded entities.
// The downloadUrlTemplate parameter is required and is an Ext.template that may include
// properties specified in the redflyOptions object.
// ------------------------------------------------------------------------------------------
REDfly.window.download = Ext.extend(Ext.Window, {
    buttonAlign: 'center',
    // Do not display the 'X' button
    closable: false,
    // Fix the issue with scrollbars on the radio buttons
    cls: 'redfly-download-window',
    // The internal form panel so we can access it via for adding/removing option panels
    formPanel: null,
    title: 'Download',
    width: 400,
    // Internal state settings. These will be applied to the download url template
    redflyOptions: null,
    speciesChooser: null,
    defaultSpeciesScientificName: 'Drosophila melanogaster',
    fileTypeChooser: null,
    defaultFileType: 'BED',
    // Ext.Template for the download url
    downloadUrlTemplate: null,
    // Ext.Template for options from the file options panel. May be null.
    downloadUrlOptionsTemplate: null,
    // Flag for downloading all items rather than those specified in redfly_id[]
    downloadAllItems: false,
    // Current (active) file type option panel. Set to null if none is active.
    fileTypeOptionPanel: null,
    // List of file type option panels keyed by file type from the store.
    fileTypeOptionPanelList: { default: new Ext.Panel({hidden: true}) },
    // ----------------------------------------------------------------------------------------------
    // If an option panel exists for this file type and has not already been created, create it and
    // add it to the option panel list. Once added, show the panel and hide any existing panel.
    // ----------------------------------------------------------------------------------------------
    setFileTypeOptionPanel: function(fileType) {
        // If an options panel exists, add it to the list for this window.
        if ( (fileType in REDfly.download.optionsPanelList) &&
             (REDfly.download.optionsPanelList[fileType] !== null) &&
             (! (fileType in this.fileTypeOptionPanelList)) ) {
            // Use the constructor to create the panel and store it in the download window
            var optionsConstructor = REDfly.download.optionsPanelList[fileType].obj;
            this.fileTypeOptionPanelList[fileType] = new optionsConstructor();
        }
        // If an options panel exists, hide any existing panel and show the new panel.
        if ( this.fileTypeOptionPanel !== null ) {
            // Hide the panel and then remove it. Do not destroy the panel as it can be reused if
            // the user selects this option again.
            this.fileTypeOptionPanel.hide();
            this.formPanel.remove(this.fileTypeOptionPanel, false);
        }
        // If an options panel exists, add it to the panel and show it. May need to call
        // panel.doLayout()
        if ( fileType in this.fileTypeOptionPanelList ) {
            // Set and display the panel
            this.fileTypeOptionPanel = this.fileTypeOptionPanelList[fileType];
            this.formPanel.add(this.fileTypeOptionPanel);
            this.fileTypeOptionPanel.show();
            this.doLayout();
            // Grab the url extras to support the options in the panel
            this.downloadUrlOptionsTemplate = REDfly.download.optionsPanelList[fileType].urlExtras;
        } else {
            this.downloadUrlOptionsTemplate = null;
            this.fileTypeOptionPanel = null;
        }
    },
    initComponent: function() {
        // Must assign the options in initComponent or the static object will be shared amonst all
        // instances
        this.redflyOptions = {
            file_type: null,
            redflyId: null,
            speciesScientificName: null
        };
        // Check for required parameters
        if ( this.downloadUrlTemplate === null ) {
            throw new Ext.Error('Parameter downloadUrlTemplate not specified', { errorCode: 100 });
        }
        // Create new instances components needed to configure the file download and set default
        // values.
        this.speciesChooser = new REDfly.download.speciesComboBox(); 
        if ( this.downloadAllItems === false ) {
            this.redflyOptions.speciesScientificName = '';
            this.speciesChooser.hide();
        } else {
            // The Drosophila melanogaster species by default
            this.redflyOptions.speciesScientificName = 'Drosophila melanogaster';
        }
        this.speciesChooser.on(
            'select',
            function(combo, record, index) {
                this.redflyOptions.speciesScientificName = combo.getValue();
            },
            this
        );
        // Create new instances components needed to configure the file download and set default
        // values.
        this.fileTypeChooser = new REDfly.download.fileTypeComboBox();
        this.redflyOptions.file_type = this.fileTypeChooser.getValue();
        this.fileTypeChooser.on(
            'select',
            function(combo, record, index) {
                this.redflyOptions.file_type = combo.getValue();
                this.setFileTypeOptionPanel(this.redflyOptions.file_type);
            },
            this
        );
        var form = new Ext.FormPanel({
            border: false,
            frame: true,
            items: [
                this.speciesChooser,
                this.fileTypeChooser
            ]
        });
        this.formPanel = form;
        this.setFileTypeOptionPanel(this.redflyOptions.file_type);
        var downloadButton = new Ext.Button({
            handler: function () {
                // Generate the download url by applying any options to the template
                if ( this.downloadAllItems ) {
                    var downloadUrl = redflyApiV2Url + this.downloadUrlTemplate.apply(this.redflyOptions) +
                        'species_scientific_name=' + this.redflyOptions.speciesScientificName;
                } else {
                    var downloadUrl = redflyApiUrl + this.downloadUrlTemplate.apply(this.redflyOptions);
                }
                // Add any extra options to the API url from the file format options panel
                if ( this.fileTypeOptionPanel !== null ) {
                    if ( !(/.\?$/.test(downloadUrl)) ) {
                        downloadUrl += '&';
                    } 
                    downloadUrl += this.downloadUrlOptionsTemplate.apply(this.fileTypeOptionPanel.fileTypeOptions);
                }
                // To be able to send POST parameters to the rest api and process a file download we
                // need to use a temporary form. The Ext.Ajax method can not handle the file download.
                var temp = document.createElement('form');
                console.log(downloadUrl);
                temp.action = downloadUrl;
                temp.method = 'POST';
                temp.style.display = 'none';
                temp.target = '_blank';
                // If downloadAllItems === true then we only need to pass the download url to the
                // rest service. Otherwise, expect that redfly_id[] will be set with the list if
                // entity ids to download and add redfly ids to the post parameters. These are
                // currently the only POST parameters.
                if ( (! this.downloadAllItems) ||
                    ((this.redflyOptions.redflyId !== null) && (this.redflyOptions.redflyId.length > 0)) ) {
                    for ( var index = 0;
                        index < this.redflyOptions.redflyId.length;
                        index++ ) {
                        var opt = document.createElement('textarea');
                        // Note that we need the '[]' to be able to process the array on the server side.
                        opt.name = 'redfly_id[]';
                        opt.value = this.redflyOptions.redflyId[index];
                        temp.appendChild(opt);
                    }
                }
                document.body.appendChild(temp);
                temp.submit();
                document.body.removeChild(temp);
                this.speciesChooser.setValue(this.defaultSpeciesScientificName);
                this.redflyOptions.speciesScientificName = this.speciesChooser.getValue();
                this.fileTypeChooser.setValue(this.defaultFileType);
                this.redflyOptions.file_type = this.fileTypeChooser.getValue();
                this.setFileTypeOptionPanel(this.defaultFileType);
                this.hide();
            },
            // Scope for the handler
            scope: this,
            text: 'Download'
        });
        var cancelButton = new Ext.Button({
            handler: function() {
                this.speciesChooser.setValue(this.defaultSpeciesScientificName);
                this.redflyOptions.speciesScientificName = this.speciesChooser.getValue();
                this.fileTypeChooser.setValue(this.defaultFileType);
                this.redflyOptions.file_type = this.fileTypeChooser.getValue();
                this.setFileTypeOptionPanel(this.defaultFileType);
                this.hide();
            },
            // Scope for the handler
            scope: this,
            text: 'Cancel'            
        });
        Ext.apply(this, {
            buttons: [
                downloadButton,
                cancelButton
            ],
            items: [ form ]
        });
        REDfly.window.download.superclass.initComponent.apply(this, arguments);
    }
});
// ------------------------------------------------------------------------------------------
// Create a split button with a menu of options to operate on all selected windows
// ------------------------------------------------------------------------------------------
REDfly.search.windowTabSelectorBtn = Ext.extend(Ext.SplitButton, {
    handler: function() {
        this.showMenu(true)
    },
    text: 'Window Tab Selector',
    initComponent: function() {
        // Create the menu for the split button
        var buttonMenu = [];
        // Rather than call a function to find a specific id, build tab-switching into each window.
        var item = new Ext.menu.Item({
            text: 'Information Tab'
        });
        item.on(
            'click',
            function () {
                REDfly.state.windowGroup.each(function(w) {
                    var windowId = w.getId();
                    w.redflyOptions.tabPanel.setActiveTab('tab-info-' + windowId);
                });
            },
            this
        );
        buttonMenu.push(item);
        var item = new Ext.menu.Item({
            text: 'Location Tab'
        });
        item.on(
            'click',
            function () {
                REDfly.state.windowGroup.each(function(w) {
                    var windowId = w.getId();
                    w.redflyOptions.tabPanel.setActiveTab('tab-location-' + windowId);
                });
            },
            this
        );
        buttonMenu.push(item);
        var item = new Ext.menu.Item({
            text: 'Image Tab'
        });
        item.on(
            'click',
            function () {
                REDfly.state.windowGroup.each(function(w) {
                    var windowId = w.getId();
                    w.redflyOptions.tabPanel.setActiveTab('tab-image-' + windowId);
                });
            },
            this
        );
        buttonMenu.push(item);
        var item = new Ext.menu.Item({
            text: 'Citation Tab'
        });
        item.on('click', function () {
            REDfly.state.windowGroup.each(function(w) {
                var windowId = w.getId();
                w.redflyOptions.tabPanel.setActiveTab('tab-citation-' + windowId);
            });
        }, this);
        buttonMenu.push(item);
        var item = new Ext.menu.Item({
            text: 'Associated RCs/TFBSs Tab'
        });
        item.on(
            'click',
            function () {
                REDfly.state.windowGroup.each(function(w) {
                    var windowId = w.getId();
                    w.redflyOptions.tabPanel.setActiveTab('tab-assoc-' + windowId);
                });
            },
            this
        );
        buttonMenu.push(item);
        var item = new Ext.menu.Item({
            text: 'Sequence Tab'
        });
        item.on(
            'click',
            function () {
                REDfly.state.windowGroup.each(function(w) {
                    var windowId = w.getId();
                    w.redflyOptions.tabPanel.setActiveTab('tab-seq-' + windowId);
                });
            },
            this
        );
        buttonMenu.push(item);
        var item = new Ext.menu.Item({
            text: 'Anatomical Expressions Tab'
        });
        item.on(
            'click',
            function () {
                REDfly.state.windowGroup.each(function(w) {
                    var windowId = w.getId();
                    w.redflyOptions.tabPanel.setActiveTab('tab-expr-' + windowId);
                });
            },
            this
        );
        buttonMenu.push(item);
        var item = new Ext.menu.Item({
            text: 'Notes Tab'
        });
        item.on(
            'click',
            function () {
                REDfly.state.windowGroup.each(function(w) {
                    var windowId = w.getId();
                    w.redflyOptions.tabPanel.setActiveTab('tab-notes-' + windowId);
                });
            },
            this
        );
        buttonMenu.push(item);
        Ext.apply(
            this,
            {
                menu: buttonMenu
            }
        );
        REDfly.search.windowTabSelectorBtn.superclass.initComponent.apply(this, arguments);
    }
});
// ------------------------------------------------------------------------------------------
// Create a button that will tile selected window on the screen
// ------------------------------------------------------------------------------------------
REDfly.search.tileWindowsBtn = Ext.extend(Ext.Button, {
    enableToggle: true,
    handler: function() {
        // If the tile windows button is toggled on, tile any existing windows and continue to tile
        // new windows. If it is toggled off, new windows will not be tiled but existing windows
        // will remain where they are.
        if ( this.pressed ) {
            // Tile existing windows
            REDfly.state.tileWindows = true;
            REDfly.fn.tileWindows(REDfly.state.windowGroup)
        } else {
            // Reset the (x, y) coordinates for new windows.
            REDfly.state.tileWindows = false;
            REDfly.fn.resetWindowCoordinates();
        }
    },
    text: 'Tile Windows',
    windowGroup: null,
    initComponent: function() {
        Ext.apply(this, {});
        REDfly.search.tileWindowsBtn.superclass.initComponent.apply(this, arguments);
    }
});
// ------------------------------------------------------------------------------------------
// Create a button that will download the selected entities.
// An object containing selection models for each tab must be provided at instantiation:
// new REDfly.search.downloadSelectedBtn({
//      selectionModels: {
//          crm: crmCheckboxSelectionModel,
//          crmsegment: crmSegmentCheckboxSelectionModel,
//          predictedcrm: predictedCrmCheckboxSelectionModel,
//          tfbs: tfbsCheckboxSelectionModel
//      }
//  });
// ------------------------------------------------------------------------------------------
REDfly.search.downloadSelectedBtn = Ext.extend(Ext.Button, {
    handler: function() {
        // Create an array of the REDfly identifiers to download
        REDfly.window.downloadSelectEntries.redflyOptions.redflyId = new Array();
        // CRMs
        var crmRecords = this.selectionModels.crm.getSelections();
        for ( index = 0;
            index < crmRecords.length;
            index++ ) {
            REDfly.window.downloadSelectEntries.redflyOptions.redflyId.push(crmRecords[index].get('redfly_id'));
        }
        // CRM Segments  
        var crmSegmentRecords = this.selectionModels.crmsegment.getSelections();
        for ( index = 0;
            index < crmSegmentRecords.length;
            index++ ) {
            REDfly.window.downloadSelectEntries.redflyOptions.redflyId.push(crmSegmentRecords[index].get('redfly_id'));
        }
        // Predicted CRMs
        var predictedCrmRecords = this.selectionModels.predictedcrm.getSelections();
        for ( index = 0;
            index < predictedCrmRecords.length;
            index++ ) {
            REDfly.window.downloadSelectEntries.redflyOptions.redflyId.push(predictedCrmRecords[index].data.redfly_id);
        }
        // TFBSs
        var tfbsRecords = this.selectionModels.tfbs.getSelections();
        for ( index = 0;
            index < tfbsRecords.length;
            index++ ) {
            REDfly.window.downloadSelectEntries.redflyOptions.redflyId.push(tfbsRecords[index].data.redfly_id);
        }
        // The default file type is the BED one by alphabetical sort
        REDfly.window.downloadSelectEntries.fileTypeChooser.setValue('BED');
        REDfly.window.downloadSelectEntries.setFileTypeOptionPanel('BED');
        REDfly.window.downloadSelectEntries.show();
    },
    selectionModels: null,
    text: 'Download Selected',
    initComponent: function() {
        Ext.apply(this, {});
        REDfly.search.downloadSelectedBtn.superclass.initComponent.apply(this, arguments);
    }
});
// ------------------------------------------------------------------------------------------
// Create a button that will view the selected entities.
//  An object containing selection models for each tab must be provided at instantiation:
// new REDfly.search.viewSelectedBtn({
//      selectionModels: {
//          crm: crmCheckboxSelectionModel,
//          crmsegment: crmSegmentCheckboxSelectionModel,
//          predictedcrm: predictedCrmCheckboxSelectionModel,
//          tfbs: tfbsCheckboxSelectionModel
//      }
//  });
//
// ------------------------------------------------------------------------------------------
REDfly.search.viewSelectedBtn = Ext.extend(Ext.Button, {
    handler: function() {
        var crmRecords = this.selectionModels.crm.getSelections();
        var crmSegmentRecords = this.selectionModels.crmsegment.getSelections();
        var predictedCrmRecords = this.selectionModels.predictedcrm.getSelections();
        var tfbsRecords = this.selectionModels.tfbs.getSelections();
        var totalRecords = crmRecords.length +
            crmSegmentRecords.length +
            predictedCrmRecords.length +
            tfbsRecords.length;
        if ( totalRecords > 5 ) {
            Ext.MessageBox.confirm(
                'Warning',
                'Are you sure you want to open ' + totalRecords + ' windows?',
                this.confirmationHandler,
                this
            );
        }
        else {
            this.viewSelected();
        }
   },
   selectionModels: null,
   text: 'View Selected',
    // ------------------------------------------------------------------------------------------
    // Handle the confirmation dialog.
    // Parameters
    // buttonId: Id of the button that was clicked
    // ------------------------------------------------------------------------------------------
    confirmationHandler: function(buttonId) {
        if ( buttonId === 'yes' ) this.viewSelected();
    },
    // ------------------------------------------------------------------------------------------
    // Display the selected entity windows
    // ------------------------------------------------------------------------------------------
    viewSelected: function() {
        var crmRecords = this.selectionModels.crm.getSelections();
        var crmSegmentRecords = this.selectionModels.crmsegment.getSelections();
        var predictedCrmRecords = this.selectionModels.predictedcrm.getSelections();
        var tfbsRecords = this.selectionModels.tfbs.getSelections();
        for ( var index = 0;
            index < crmRecords.length;
            index++ ) {
            REDfly.fn.showOrCreateEntityWindow(
                crmRecords[index].get('redfly_id'),
                crmRecords[index].get('name')
            );
        }
        for ( var index = 0;
            index < crmSegmentRecords.length;
            index++ ) {
            REDfly.fn.showOrCreateEntityWindow(
                crmSegmentRecords[index].get('redfly_id'),
                crmSegmentRecords[index].get('name')
            );
        }
        for ( var index = 0;
            index < predictedCrmRecords.length;
            index++ ) {
            REDfly.fn.showOrCreateEntityWindow(
                predictedCrmRecords[index].get('redfly_id'),
                predictedCrmRecords[index].get('name')
            );
        }
        for ( var index = 0;
            index < tfbsRecords.length;
            index++ ) {
            REDfly.fn.showOrCreateEntityWindow(
                tfbsRecords[index].get('redfly_id'),
                tfbsRecords[index].get('name')
            );
        }
    },
    initComponent: function() {
        Ext.apply(
            this,
            {
                // Scope for the handler, must be applied here to reference the button and not in the
                // static initializations
                scope: this
            }
        );
        REDfly.search.viewSelectedBtn.superclass.initComponent.apply(this, arguments);
    }
});
// ------------------------------------------------------------------------------------------
// Create a button that will close all the open entity windows.
// ------------------------------------------------------------------------------------------
REDfly.search.closeAllBtn = Ext.extend(Ext.Button, {
    handler: function() {
        REDfly.fn.closeAllWindows(REDfly.state.windowGroup);
    },
    text: 'Close All',
    windowGroup: null,
    initComponent: function() {
        Ext.apply(this, {});
        REDfly.search.closeAllBtn.superclass.initComponent.apply(this, arguments);
    }
});