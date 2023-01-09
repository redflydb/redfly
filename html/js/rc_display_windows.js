// ------------------------------------------------------------------------------------------
// Window for displaying Reporter Constructs
// ------------------------------------------------------------------------------------------
REDfly.window.reporterConstruct = Ext.extend(Ext.Window, {
    autoscroll: true,
    closable: false,
    height: REDfly.config.windowHeight,
    // id will be set to the REDfly identifier and used by Ext.WindowGroup to identify the window
    id: null,
    layout: 'fit',
    plain: true,
    resizable: true,
    stateful: false,
    title: 'Reporter Construct',
    width: REDfly.config.windowWidth,
    // REDfly configs
    // We must assign the options and stores objects in initComponent() or it will use a static copy
    // shared among all windows
    entityName: null,
    redflyId: null,
    redflyOptions: null,
    redflyStores: null,
    // ------------------------------------------------------------------------------------------
    // Create the RC window. This is done after all of the stores have loaded.
    // ------------------------------------------------------------------------------------------
    initComponent: function() {
        this.redflyOptions = {
            entityName: null,
            loadedStoresNumber: 0,
            redflyId: null,
            storesNumber: 0,
            tabPanel: null
        };
        this.redflyStores = {
            anatomicalExpressionTerms: null,
            associatedTfbs: null,
            coordinates: null,
            image: null,
            rc: null
        };
        if ( ! this.redflyId ) {
            throw new Ext.Error('REDfly identifier not provided');
        } else if ( ! this.entityName ) {
            throw new Ext.Error('Entity name not provided');
        }
        this.redflyOptions.redflyId = this.redflyId;
        this.redflyOptions.entityName = this.entityName;
        this.redflyStores.coordinates = new Ext.data.JsonStore({
            fields: [
                'chromosome',
                'coordinates',
                'current_end',
                'current_start',
                'id',
                'name',
                'redfly_id',
                'species_short_name'
            ],
            idProperty: 'id',
            listeners: {
                exception: function() {
                    Ext.Msg.alert(
                        'Error',
                        'Error loading coordinates'
                    );
                },
                load: {
                    fn: this.allStoresLoaded,
                    scope: this
                }
            },
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                url: redflyApiUrl + '/jsonstore/coordinate/search?redfly_id=' +
                    this.redflyOptions.redflyId
            }),
            root: 'results',
            totalProperty: 'num'
        });
        this.redflyOptions.storesNumber++;
        this.redflyStores.anatomicalExpressionTerms = new Ext.data.JsonStore({
            fields: [
                'biological_process_identifier',
                'biological_process_term',
                'ectopic',
                'identifier',
                'id',
                'pubmed_id',
                'sex',
                'silencer',
                'stage_off_identifier',
                'stage_off_term',
                'stage_on_identifier',
                'stage_on_term',
                'term'
            ],
            idProperty: 'id',
            listeners: {
                exception: function() {
                    Ext.Msg.alert(
                        'Error',
                        'Error loading the anatomical expression terms'
                    );
                },
                load: {
                    fn: this.allStoresLoaded,
                    scope: this
                }
            },
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                url: redflyApiUrl + '/jsonstore/anatomicalexpression/get?redfly_id=' +
                    this.redflyOptions.redflyId + '&triplestore=true' +
                    '&sort=anatomical_expression_term,stage_on_term,stage_off_term,biological_process_term'
            }),
            root: 'results',
            totalProperty: 'num'
        });
        this.redflyOptions.storesNumber++;
        this.redflyStores.associatedTfbs = new Ext.data.JsonStore({
            fields: [
                'name',
                'redfly_id'
            ],
            idProperty: 'id',
            listeners: {
                exception: function() {
                    Ext.Msg.alert(
                        'Error',
                        'Error loading associated TFBSs'
                    );
                },
                load: {
                    fn: this.allStoresLoaded,
                    scope: this
                }
            },
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                url: redflyApiUrl + '/jsonstore/reporterconstruct/associated_tfbs?redfly_id=' +
                    this.redflyOptions.redflyId
            }),
            root: 'results',
            totalProperty: 'num'
        });
        this.redflyOptions.storesNumber++;
        this.redflyStores.image =  new Ext.data.JsonStore({
            fields: [
                'image',
                'redfly_id',
                'target'
            ],
            idProperty: 'id',
            listeners: {
                exception: function() {
                    Ext.Msg.alert(
                        'Error',
                        'Error loading images'
                    );
                },
                load: {
                    fn: this.allStoresLoaded,
                    scope: this
                }
            },
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                url: redflyApiUrl + '/jsonstore/reporterconstruct/images?redfly_id=' +
                    this.redflyOptions.redflyId
            }),
            root: 'results',
            totalProperty: 'num'
        });
        this.redflyOptions.storesNumber++;
        this.redflyStores.rc = new Ext.data.JsonStore({
            fields: [
                'archived_coordinates',
                'archived_genome_assembly_release_versions',
                'assayed_in_species_scientific_name',
                'assayed_in_species_short_name',
                'chromosome',
                'contents',
                'coordinates',
                'curator_full_name',
                'fbtp',
                'end',
                'evidence_term',
                'figure_labels',
                'gene_identifier',
                'gene_name',
                'has_tfbs',
                'image',
                'is_crm',
                'is_minimalized',
                'is_negative',
                'last_update',
                'location',
                'name',
                'notes',
                'previous_curator_full_names',
                'public_browser_links',
                'public_browser_names',
                'public_database_links',
                'public_database_names',
                'pubmed_id',
                'redfly_id',
                'release_version',
                'sequence',
                'sequence_from_species_short_name',
                'sequence_from_species_scientific_name',
                'sequence_source',
                'start'
            ],
            idProperty: 'id',
            listeners: {
                exception: function() {
                    Ext.Msg.alert(
                        'Error',
                        'Error loading RC'
                    );
                },
                load: {
                    fn: this.allStoresLoaded,
                    scope: this
                }
            },
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                url: redflyApiUrl + '/jsonstore/reporterconstruct/get?redfly_id=' +
                    this.redflyOptions.redflyId
            }),
            root: 'results',
            totalProperty: 'num'
        });
        this.redflyOptions.storesNumber++;
        // Create the tab panel to hold all of the tabs but don't populate them
        // until the stores are loaded.
        this.redflyOptions.tabPanel = new Ext.TabPanel({
            activeTab: 0,
            autoTabs: true,
            border: false,
            buttonAlign: 'center',
            buttons: [{
                handler: function () {
                    REDfly.window.downloadSingleRc.redflyOptions.redflyId = new Array(this.redflyOptions.redflyId);
                    // The default file type is the BED one by alphabetical sort
                    REDfly.window.downloadSingleRc.fileTypeChooser.setValue('BED');
                    REDfly.window.downloadSingleRc.setFileTypeOptionPanel('BED');
                    REDfly.window.downloadSingleRc.show();
                },
                scope: this,
                text: 'Download'
            }, {
                handler: function (b, e) {
                    this.close()
                    // If this is the last window, reset the next window coordinates
                    if ( ! REDfly.state.windowGroup.getActive() ) {
                        REDfly.fn.resetWindowCoordinates()
                    }
                },
                scope: this,
                text: 'Close'
            }],
            defaults: {
                autoScroll: true
            },
            deferredRender: false,
            enableTabScroll: true
        });
        Ext.apply(
            this,
            {
                title: 'Reporter Construct: ' + this.redflyOptions.entityName,
                id: this.redflyOptions.redflyId,
                items: [ this.redflyOptions.tabPanel ],
                x: REDfly.config.nextWindowCoordinates[0],
                y: REDfly.config.nextWindowCoordinates[1],
                width: REDfly.config.windowWidth + 500,
                height: REDfly.config.windowHeight
            }
        );
        this.redflyStores.anatomicalExpressionTerms.load();
        this.redflyStores.associatedTfbs.load();
        this.redflyStores.coordinates.load();
        this.redflyStores.image.load();
        this.redflyStores.rc.load();
        // Increment the coordinates of the next window.
        REDfly.config.nextWindowCoordinates = REDfly.fn.getNextWindowCoordinates(
            REDfly.config.nextWindowCoordinates[0],
            REDfly.config.nextWindowCoordinates[1]
        );
        REDfly.window.reporterConstruct.superclass.initComponent.apply(
            this,
            arguments
        );
    },
    // ------------------------------------------------------------------------------------------
    // Collector function to create the window onces all stores have loaded.
    // ------------------------------------------------------------------------------------------
    allStoresLoaded: function(store, records, options) {
        this.redflyOptions.loadedStoresNumber++;
        if ( this.redflyOptions.storesNumber === this.redflyOptions.loadedStoresNumber ) {
            this.createComponents();
        } else if ( this.redflyOptions.loadedStoresNumber > this.redflyOptions.storesNumber ) {
            this.redflyOptions.loadedStoresNumber = 0;
        }
    },
    listeners: {
        beforeclose: function(w) {
            // Reset the last reporter construct selected so that dialog may be reopened
            if ( REDfly.state.lastSelectedReporterConstruct === this.redflyOptions.redflyId )
                REDfly.state.lastSelectedReporterConstruct = null;
        },
        move: REDfly.fn.browserBorderCheck
    },
    // ------------------------------------------------------------------------------------------
    // Create the RC window. This is done after all of the stores have loaded.
    // ------------------------------------------------------------------------------------------
    createComponents: function() {
        var rcRecord = this.redflyStores.rc.getAt(0);
        var sequenceFromSpeciesScientificName = rcRecord.get('sequence_from_species_scientific_name');
        var assayedInSpeciesScientificName = rcRecord.get('assayed_in_species_scientific_name');
        var isMinimalized = ( rcRecord.get('is_minimalized') === '0'
            ? 'No'
            : 'Yes' );
        var isNegative = ( rcRecord.get('is_negative') === '0'
            ? 'Positive'
            : 'Negative' );
        var isCrm = ( rcRecord.get('is_crm') === '0'
            ? 'No'
            : 'Yes' );
        var figureLabels = rcRecord.get('figure_labels');
        var rcName = rcRecord.get('name');
        var imageString = '';
        var imageTmpl = new Ext.Template('<a href="{target}" target="_blank"><img src="..{image}" alt=""/></a><br/><br/>\n');
        var numImages = this.redflyStores.image.getTotalCount();
        if ( numImages === 0 ) {
            if (figureLabels) {
              imageString = '<b> Figure: ' + figureLabels.replace(/\^/g, ', ') + '</b>';
            } else {
              imageString = '<b>No associated figure labels found.</b>';
            }
        } else {
            for ( i = 0;
                i < numImages;
                i++ ) {
                imageString += imageTmpl.apply({
                    target: this.redflyStores.image.getAt(i).get('target'),
                    image: this.redflyStores.image.getAt(i).get('image')
                });
            }
        }
        if ( rcName.match(/[a-zA-Z0-9()-]_VT[0-9]/g) ) {
            var vc = rcName.split('_')[1];
            vc_href = 'http://enhancers.starklab.org/tile/'+ vc;
            imageString += '<br><b>Fly Enhancers: </b> <a target="_blank" href="' +
                vc_href + '/">' + vc + '</a>';
        }
        var sequence = rcRecord.get('sequence');
        var numLines = sequence.length / REDfly.config.sequenceLineLength + 1;
        var formattedSequence = '';
        var startIndex, endIndex;
        for ( i = 0;
            i < numLines;
            i++ ) {
            startIndex = i * REDfly.config.sequenceLineLength;
            endIndex = startIndex + REDfly.config.sequenceLineLength;
            formattedSequence += '<br>' + sequence.substring(startIndex, endIndex);
        }
        formattedSequence = formattedSequence.toUpperCase();
        var notes = ( Ext.isEmpty(rcRecord.get('notes'))
            ? ''
            : rcRecord.get('notes') );
        var hasFbtp = '<b>FBtp ID:</b> None';
        if ( ! Ext.isEmpty(rcRecord.get('fbtp')) ) {
            hasFbtp = '<b>FBtp ID:</b> <a href="http://flybase.org/reports/' + rcRecord.get('fbtp')
                + '.html" target="_blank">' + rcRecord.get('fbtp') + '</a>';
        }
        // --------------------------------------------------------------------------------
        // Create the tab panel and tabs for the window
        // --------------------------------------------------------------------------------
        // Since new entities do not have old coordinates we may have a case where there
        // are no previous coordinates to show. Dynamically build this widget.
        var previousReleases = rcRecord.get('archived_genome_assembly_release_versions').split(',');
        var previousCoordinates = rcRecord.get('archived_coordinates').split(',');
        var previousCoordinatesItems = new Array();
        if ( previousReleases.length != 0 ) {
            var lastIndex = previousReleases.length - 1;
            if ( (previousCoordinates[lastIndex].split(':')[1] !== '..') &&
                (previousCoordinates[lastIndex].split(':')[1] !== '0..0') ) {
                previousCoordinatesItems.push(new Ext.Component({
                    html: '<b>Release ' + previousReleases[lastIndex] + ' Coordinates: </b>' +
                        previousCoordinates[lastIndex] + '<br />'
                }));
            }
        }
        var previousCoordinatesWidget = ( previousCoordinatesItems.length > 0
            ? new Ext.form.FieldSet({
                collapsed: true,
                collapsible: true,
                items: previousCoordinatesItems,
                title: 'Previous Coordinates'
            })
            : new Ext.Component() );
        var publicDatabaseInformations = '';
        if ( (rcRecord.get('gene_identifier').toLowerCase() !== 'unspecified') &&
            (rcRecord.get('public_database_names') !== null) &&
            (rcRecord.get('public_database_names') !== '') ) {
            var publicDatabaseNames = rcRecord.get('public_database_names').split(',');
            var publicDatabaseLinks = rcRecord.get('public_database_links').split(',');
            for ( var publicDatabaseNameIndex = 0;
                publicDatabaseNameIndex < publicDatabaseNames.length;
                publicDatabaseNameIndex++ ) {
                if ( publicDatabaseNames[publicDatabaseNameIndex] !== 'FlyTF' ) {
                    if ( publicDatabaseInformations === '' ) {
                        publicDatabaseInformations += ' (';
                    } else {
                        publicDatabaseInformations += ' | ';
                    }
                    publicDatabaseInformations +=
                        '<a href="' +
                        publicDatabaseLinks[publicDatabaseNameIndex].replace('gene_identifier', rcRecord.get('gene_identifier')) +
                        '" target="_blank">' +
                        publicDatabaseNames[publicDatabaseNameIndex] +
                        '</a>';
                }
            }
            publicDatabaseInformations += ')<br />';
        }
        var publicBrowserInformations = '';
        if ( (rcRecord.get('public_browser_names') !== null) &&
            (rcRecord.get('public_browser_names') !== '') ) {
            var publicBrowserNames = rcRecord.get('public_browser_names').split(',');
            var publicBrowserLinks = rcRecord.get('public_browser_links').split(',');
            for ( var publicBrowserNameIndex = 0;
                publicBrowserNameIndex < publicBrowserNames.length;
                publicBrowserNameIndex++ ) {
                if ( publicBrowserInformations === '' ) {
                    publicBrowserInformations = '<b>Browser Links: </b>';
                }
                switch (publicBrowserNames[publicBrowserNameIndex]) {
                    case 'GBrowse':
                        if ( publicBrowserInformations !== '<b>Browser Links: </b>' ) {
                            publicBrowserInformations += ' | ';
                        }
                        publicBrowserInformations += '<a href="' +
                            publicBrowserLinks[publicBrowserNameIndex]
                                .replace('coordinates', rcRecord.get('coordinates'))
                                .replace('redflyBaseUrl', redflyBaseUrl) +
                            '" target="_blank">' +
                            publicBrowserNames[publicBrowserNameIndex] +
                            '</a>';
                        break;
                    case 'iBeetle-Base-GBrowse':
                        if ( publicBrowserInformations !== '<b>Browser Links: </b>' ) {
                            publicBrowserInformations += ' | ';
                        }
                        publicBrowserInformations += '<a href="' +
                            publicBrowserLinks[publicBrowserNameIndex]
                                .replace('chromosome', rcRecord.get('chromosome'))
                                .replace('start', rcRecord.get('start'))
                                .replace('end', rcRecord.get('end')) +
                            '" target="_blank">' +
                            publicBrowserNames[publicBrowserNameIndex] +
                            '</a>';
                        break;
                    case 'JBrowse':
                        if ( publicBrowserInformations !== '<b>Browser Links: </b>' ) {
                            publicBrowserInformations += ' | ';
                        }
                        publicBrowserInformations += '<a href="' +
                            publicBrowserLinks[publicBrowserNameIndex]
                                .replace('chromosome', rcRecord.get('chromosome'))
                                .replace('start..end', (parseInt(rcRecord.get('start')) - 2000) + '..' + (parseInt(rcRecord.get('end')) + 2000)) +
                            '" target="_blank">' +
                            publicBrowserNames[publicBrowserNameIndex] +
                            '</a>';
                        break;
                    case 'UCSC':
                        if ( publicBrowserInformations !== '<b>Browser Links: </b>' ) {
                            publicBrowserInformations += ' | ';
                        }
                        publicBrowserInformations += '<a href="' +
                            publicBrowserLinks[publicBrowserNameIndex]
                                .replace('release_version', rcRecord.get('release_version'))
                                .replace('chromosome', rcRecord.get('chromosome'))
                                .replace('start-end', rcRecord.get('start') + '-' + rcRecord.get('end')) +
                            '" target="_blank">' +
                            publicBrowserNames[publicBrowserNameIndex] +
                            '</a>';
                        break;
                }
            }
            if ( publicBrowserInformations !== '' ) {
                publicBrowserInformations += '<br />';
            }
        }
        var infoPanel = new Ext.Panel({
            id: 'tab-info-' + this.redflyOptions.redflyId,
            items: [{
                html: '<b>Release ' + rcRecord.get('release_version') + ' Coordinates: </b>' +
                    rcRecord.get('coordinates') + ' <br /><br />'
            },
            // The previous coordinates no longer needed
            //previousCoordinatesWidget,
            {
                html: '<b>"Sequence From" Species: </b>' + sequenceFromSpeciesScientificName + '<br />'
            }, {
                html: '<b>"Assayed In" Species: </b>' + assayedInSpeciesScientificName + '<br />'
            }, {
                html: '<b>Gene Name: </b>' + rcRecord.get('gene_name') + publicDatabaseInformations
            }, {
                html: hasFbtp + '<br /><br />'
            }, {
                html: '<b>Expression: </b>' + isNegative + '<br />'
            }, {
                html: '<b>Is CRM: </b>' + isCrm + '<br />'
            }, {
                html: '<b>Is Minimized: </b>' + isMinimalized + '<br /><br />'
            }, {
                html: '<b>RedFly ID:</b> ' + this.redflyOptions.redflyId + '<br />'
            }, {
                html: '<b>Last Update: </b>' + rcRecord.get('last_update') + '<br /><br />'
            }, {
                html: publicBrowserInformations
            }],
            tabTip: 'Basic information about the selected Reporter Construct',
            title: 'Information'
        });
        this.redflyOptions.tabPanel.add(infoPanel);
        if ( rcRecord.get('sequence_from_species_scientific_name').toLowerCase() === 'drosophila melanogaster' ) {
            var locationInformations = '';
            var locations = rcRecord.get('location').split('$');
            for ( i = 0;
                i < locations.length;
                i++ ) {
                locationInformations += locations[i] + '<br/><br/>';
            }
            var locationPanel = new Ext.Panel({
                id: 'tab-location-' + this.redflyOptions.redflyId,
                html: [{
                    html: locationInformations
                }, {
                    html: '<img src="' + REDfly.config.urls.flybase_images + rcRecord.get('image') +
                        '" alt=""/><br /><br />'
                }],
                tabTip: 'Location information for the selected Reporter Construct',
                title: 'Location'
            });
            this.redflyOptions.tabPanel.add(locationPanel);
        }
        var imagePanel = new Ext.Panel({
            id: 'tab-image-' + this.redflyOptions.redflyId,
            html: [ imageString ],
            tabTip: 'Images of reporter gene expression regulated by the RC/CRM. ' +
                'Click on the  image to go to FlyExpress.',
            title: 'Image (' + this.redflyStores.image.getTotalCount() + ')'
        });
        this.redflyOptions.tabPanel.add(imagePanel);
        if ( rcName.includes('Unspecified_VT') ) {
            var vc = rcName.split('_')[1];
            vc_href = 'http://enhancers.starklab.org/tile/' + vc;
            vtHTMLString = '<b>Fly Enhancers: </b> <a target="_blank" href="' + vc_href + '/">' +
                vc + '</a><br><br>';
        } else {
            vtHTMLString = '';
        }
        var secondaryPubmedReferences = [];
        for ( var index = 0;
            index < this.redflyStores.anatomicalExpressionTerms.getTotalCount();
            index++ ) {
            var newSecondaryPubmedReference = this.redflyStores.anatomicalExpressionTerms.getAt(index).get('pubmed_id');
            if ( (newSecondaryPubmedReference !== null) &&
                (newSecondaryPubmedReference !== '') &&
                (newSecondaryPubmedReference !== rcRecord.get('pubmed_id')) &&
                (! secondaryPubmedReferences.includes(newSecondaryPubmedReference) )) {
                secondaryPubmedReferences.push(newSecondaryPubmedReference);
            }
        }
        var secondaryPubmedReferenceLinks = '';
        for ( var index = 0;
            index < secondaryPubmedReferences.length;
            index++ ) {
            newSecondaryPubmedReferenceLink = REDfly.templates.pubmed_identifier_url.apply({
                pubmed_id: secondaryPubmedReferences[index],
                text: secondaryPubmedReferences[index],
                url: REDfly.config.urls.pubmed
            });
            if ( secondaryPubmedReferenceLinks === '' ) {
                secondaryPubmedReferenceLinks = newSecondaryPubmedReferenceLink;
            } else {
                secondaryPubmedReferenceLinks += ',' + newSecondaryPubmedReferenceLink;
            }
        }
        if ( secondaryPubmedReferenceLinks !== '' ) {
            secondaryPubmedReferenceLinks = '<b>Secondary PubMed Reference(s): </b>' +
                secondaryPubmedReferenceLinks +
                '<br /><br />';
        }
        var previousCurators = '';
        if ( rcRecord.get('previous_curator_full_names') !== '' ) {
            previousCurators += '<b>Previous Curator(s):</b> ' + rcRecord.get('previous_curator_full_names') + '<br /><br />';
        } else {
            previousCurators = '<br />';
        }
        var citationPanel = new Ext.Panel({
            id: 'tab-citation-' + this.redflyOptions.redflyId,
            html: [
                {
                    html: '<b>Citation:</b> ' + rcRecord.get('contents') + '<br /><br />'
                }, {
                    html: '<b>PubMed Reference:</b> ' +
                        REDfly.templates.pubmed_identifier_url.apply({
                            pubmed_id: rcRecord.get('pubmed_id'),
                            text: rcRecord.get('pubmed_id'),
                            url: REDfly.config.urls.pubmed
                        })
                }, {
                    html: secondaryPubmedReferenceLinks
                }, {
                    html: vtHTMLString
                }, {
                    html: '<b>Curator:</b> ' + rcRecord.get('curator_full_name') + '<br />'
                }, {
                    html: previousCurators
                }, {
                    html: '<b>Sequence Source:</b> ' + rcRecord.get('sequence_source') + '<br />'
                }, {
                    html: '<b>Evidence For Element:</b> '  +rcRecord.get('evidence_term') + '<br />'
                }
            ],
            tabTip: 'Citation information/link to the PubMed record',
            title: 'Citation'
        });
        this.redflyOptions.tabPanel.add(citationPanel);
        var associatedTfbsGrid = new Ext.grid.GridPanel({
            columns: [
                {
                    dataIndex: 'name',
                    header: 'Name',
                    id: 'Name',
                    menuDisabled: true,
                    sortable: false,
                    width: 155
                }, {
                    dataIndex: 'redfly_id',
                    header: 'RedFly ID',
                    menuDisabled: true,
                    sortable: false,
                    width: 200
                }
            ],
            listeners: {
                cellclick: function(grid, rowIndex, columnIndex, e) {
                    // This should simply create a new TFBS window
                    var record = grid.getStore().getAt(rowIndex);
                    var entityName = record.get('name');
                    var redflyId = record.get('redfly_id');
                    REDfly.fn.showOrCreateEntityWindow(
                        redflyId,
                        entityName
                    );
                }
            },
            store: this.redflyStores.associatedTfbs
        });
        var assocPanel = new Ext.Panel({
            id: 'tab-assoc-' + this.redflyOptions.redflyId,
            items: [associatedTfbsGrid],
            tabTip: 'TFBS records associated with this Reporter Construct. ' +
                'Click on an entry to access its record.',
            title: 'TFBS (' + this.redflyStores.associatedTfbs.getTotalCount() + ')'
        });
        this.redflyOptions.tabPanel.add(assocPanel);
        var sequencePanel = new Ext.Panel({
            id: 'tab-seq-' + this.redflyOptions.redflyId,
            html: '<b>Size:</b> ' + rcRecord.get('sequence').length +
                '<br /><br /><b>Sequence:</b><font face="courier">' +
                formattedSequence + '</font>',
            tabTip: 'The length and nucleotide sequence of this Reporter Construct',
            title: 'Sequence'
        });
        this.redflyOptions.tabPanel.add(sequencePanel);
        var anatomicalExpressionTermGrid = new Ext.grid.GridPanel({
            columns: [
                {
                    dataIndex: 'term',
                    header: 'Term',
                    menuDisabled: true,
                    renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                        var redflyLink = REDfly.templates.redfly_anatomical_expression_identifier_url.apply({
                            flybase_id: store.getAt(rowIndex).get('identifier'),
                            url: REDfly.config.urls.redfly,
                            text: value
                        });
                        var flybaseLink = REDfly.templates.flybase_identifier_url.apply({
                            flybase_id: store.getAt(rowIndex).get('identifier'),
                            url: REDfly.config.urls.flybase_cv_term_report,
                            text: 'FlyBaseID'
                        });
                        return redflyLink + ' | ' + flybaseLink;
                    },
                    sortable: false,
                    width: 150
                }, {
                    align: 'center',
                    dataIndex: 'secondary_pubmed_id',
                    header: 'Source',
                    menuDisabled: true,
                    renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                        var pubmedIdentifier = store.getAt(rowIndex).get('pubmed_id');
                        if ( (pubmedIdentifier !== null) &&
                            (pubmedIdentifier !== '') &&
                            (pubmedIdentifier !== rcRecord.get('pubmed_id')) ) {
                            return REDfly.templates.pubmed_identifier_url.apply({
                                pubmed_id: store.getAt(rowIndex).get('pubmed_id'),
                                url: REDfly.config.urls.pubmed,
                                text: store.getAt(rowIndex).get('pubmed_id')
                            });
                        } else {
                            return REDfly.templates.pubmed_identifier_url.apply({
                                pubmed_id: rcRecord.get('pubmed_id'),
                                url: REDfly.config.urls.pubmed,
                                text: rcRecord.get('pubmed_id')
                            });
                        }
                    },
                    sortable: false,
                    width: 80
                }, {
                    // Value will be empty when dataIndex = ''
                    dataIndex: '',
                    header: 'Stage On',
                    menuDisabled: true,
                    renderer: function (value, metaData, record, rowIndex, colIndex, store) {
                        switch(store.getAt(rowIndex).get('stage_on_identifier')) {
                            case null:
                            case '':
                                return '';
                                break;
                            case 'aaeg:none':
                            case 'agam:none':
                            case 'dmel:none':
                            case 'tcas:none':
                                return value;
                                break;
                            default:
                                return REDfly.templates.flybase_identifier_url.apply({
                                    flybase_id: store.getAt(rowIndex).get('stage_on_identifier'),
                                    url: REDfly.config.urls.flybase_cv_term_report,
                                    text: store.getAt(rowIndex).get('stage_on_term')
                                });
                        }
                    },
                    sortable: false,
                    width: 120
                }, {
                    // Value will be empty when dataIndex = ''
                    dataIndex: '',
                    header: 'Stage Off',
                    menuDisabled: true,
                    renderer: function (value, metaData, record, rowIndex, colIndex, store) {
                        switch(store.getAt(rowIndex).get('stage_off_identifier')) {
                            case null:
                            case '':
                                return '';
                                break;
                            case 'aaeg:none':
                            case 'agam:none':
                            case 'dmel:none':
                            case 'tcas:none':
                                return value;
                                break;
                            default:
                                return REDfly.templates.flybase_identifier_url.apply({
                                    flybase_id: store.getAt(rowIndex).get('stage_off_identifier'),
                                    url: REDfly.config.urls.flybase_cv_term_report,
                                    text: store.getAt(rowIndex).get('stage_off_term')
                                });
                        }
                    },
                    sortable: false,
                    width: 120
                }, {
                    align: 'center',
                    dataIndex: 'sex',
                    header: 'Sex',
                    menuDisabled: true,
                    renderer: function (value, metaData, record, rowIndex, colIndex, store) {
                        switch(value) {
                            case null:
                            case '':
                                return value;
                                break;
                            case 'both':
                                return 'M/F';
                                break;
                            case 'f':
                                return 'F';
                                break;
                            case 'm':
                                return 'M';
                                break;
                            default:
                                return 'Unknown';
                        }
                    },
                    sortable: false,
                    width: 70
                }, {
                    // Value will be empty when dataIndex = ''
                    dataIndex: '',
                    header: 'Biological Process',
                    menuDisabled: true,
                    renderer: function (value, metaData, record, rowIndex, colIndex, store) {
                        if (( store.getAt(rowIndex).get('biological_process_identifier') === null ) ||
                            ( store.getAt(rowIndex).get('biological_process_identifier') === '' )) {
                            return '';
                        } else {
                            return REDfly.templates.go_identifier_url.apply({
                                go_id: store.getAt(rowIndex).get('biological_process_identifier'),
                                text: store.getAt(rowIndex).get('biological_process_term'),
                                url: REDfly.config.urls.go
                            });
                        }
                    },
                    sortable: false,
                    width: 120
                }, {
                    align: 'center',
                    dataIndex: 'ectopic',
                    header: 'Ectopic',
                    menuDisabled: true,
                    renderer: function (value, metaData, record, rowIndex, colIndex, store) {
                        switch(value) {
                            case null:
                            case '':
                                return value;
                                break;
                            case '0':
                                return 'N';
                                break;
                            case '1':
                                return 'Y';
                                break;
                            default:
                                return 'Unknown';
                        }
                    },
                    sortable: false,
                    width: 70
                }, {
                    align: 'center',
                    dataIndex: 'silencer',
                    header: 'Enhancer/Silencer',
                    menuDisabled: true,
                    renderer: function (value, metaData, record, rowIndex, colIndex, store) {
                        return value.charAt(0).toUpperCase() + value.slice(1);
                    },
                    sortable: false,
                    width: 100
                }
            ],
            store: this.redflyStores.anatomicalExpressionTerms
        });
        var anatomicalExpressionPanel = new Ext.Panel({
            id: 'tab-expr-' + this.redflyOptions.redflyId,
            items: [anatomicalExpressionTermGrid],
            tabTip: 'Anatomical expression terms associated with this Reporter Construct. ' +
                'Click on a column header to sort. ' +
                'Click on an anatomical term to initiate a new REDfly search in a separate browser window.',
            title: 'Anatomical Expressions (' + this.redflyStores.anatomicalExpressionTerms.getTotalCount() + ')'
        });
        this.redflyOptions.tabPanel.add(anatomicalExpressionPanel);
        var notesPanel = new Ext.Panel({
            id: 'tab-notes-' + this.redflyOptions.redflyId,
            html: '<b>Notes:</b><br />' + notes,
            tabTip: 'Check this tab for elaborations on anatomical expression patterns and ' +
                'other notes about the regulatory element',
            title: 'Notes'
        });
        this.redflyOptions.tabPanel.add(notesPanel);
        this.show();
        REDfly.state.windowGroup.register(this);
        REDfly.state.windowGroup.bringToFront(this);
    }
});
