// ------------------------------------------------------------------------------------------
// Window for displaying Transcription Factor Binding Sites
// ------------------------------------------------------------------------------------------
REDfly.window.tfbs = Ext.extend(Ext.Window, {
    autoscroll: true,
    closable: false,
    height: REDfly.config.windowHeight,
    // id will be set to the REDfly identifier and used by Ext.WindowGroup to identify the window
    id: null,
    layout: 'fit',
    plain: true,
    resizable: true,
    stateful: false,
    title: 'TFBS',
    width: REDfly.config.windowWidth,
    // REDfly configs
    // We must assign the options and stores objects in initComponent() or it will use a static copy
    // shared among all windows
    entityName: null,
    redflyId: null,
    redflyOptions: null,
    redflyStores: null,
    // ------------------------------------------------------------------------------------------
    // Create the TFBS window. This is done after all of the stores have loaded.
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
            associatedRc: null,
            coordinates: null,
            image: null,
            tfbs: null
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
            root: 'results',
            totalProperty: 'num',
            url: redflyApiUrl + '/jsonstore/coordinate/search?redfly_id=' + this.redflyOptions.redflyId
        });
        this.redflyOptions.storesNumber++;
        this.redflyStores.anatomicalExpressionTerms = new Ext.data.JsonStore({
            fields: [
                'identifier',
                'id',
                'term'
            ],
            idProperty: 'id',
            listeners: {
                load: {
                    fn: this.allStoresLoaded,
                    scope: this
                }
            },
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                url: redflyApiUrl + '/jsonstore/anatomicalexpression/get?redfly_id='
                    + this.redflyOptions.redflyId + '&sort=anatomical_expression_term'
            }),
            root: 'results',
            totalProperty: 'num'
        });
        this.redflyOptions.storesNumber++;
        this.redflyStores.associatedRc = new Ext.data.JsonStore({
            fields: [
                'name',
                'redfly_id'
            ],
            idProperty: 'id',
            listeners: {
                load: {
                    fn: this.allStoresLoaded,
                    scope: this
                }
            },
            root: 'results',
            totalProperty: 'num',
            url: redflyApiUrl + '/jsonstore/transcriptionfactorbindingsite/associated_rc?redfly_id='
                + this.redflyOptions.redflyId
        });
        this.redflyOptions.storesNumber++;
        this.redflyStores.tfbs = new Ext.data.JsonStore({
            fields: [
                'archived_coordinates',
                'archived_genome_assembly_release_versions',
                'assayed_in_species_scientific_name',
                'assayed_in_species_short_name',
                'chromosome',
                'contents',
                'coordinates',
                'curator_full_name',
                'end',
                'evidence_term',
                'gene_identifier',
                'gene_name',
                'image',
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
                'sequence_with_flank',
                'start',
                'tf_identifier',
                'tf_name'
            ],
            idProperty: 'id',
            listeners: {
                load: {
                    fn: this.allStoresLoaded,
                    scope: this
                }
            },
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                url: redflyApiUrl + '/jsonstore/transcriptionfactorbindingsite/get?redfly_id='
                    + this.redflyOptions.redflyId
            }),
            root: 'results',
            totalProperty: 'num'
        });
        this.redflyOptions.storesNumber++;
        // Create the tab panel to hold all of the tabs
        // but do not populate them until the stores are loaded.
        this.redflyOptions.tabPanel = new Ext.TabPanel({
            activeTab: 0,
            autoTabs: true,
            border: false,
            buttonAlign: 'center',
            buttons: [{
                handler: function () {
                    REDfly.window.downloadSingleTfbs.redflyOptions.redflyId = new Array(this.redflyOptions.redflyId);
                    // The default file type is the BED one by alphabetical sort
                    REDfly.window.downloadSingleTfbs.fileTypeChooser.setValue('BED');
                    REDfly.window.downloadSingleTfbs.setFileTypeOptionPanel('BED');
                    REDfly.window.downloadSingleTfbs.show();
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
            enableTabScroll: true,
        });
        Ext.apply(
            this,
            {
                title: 'TFBS: ' + this.redflyOptions.entityName,
                id: this.redflyOptions.redflyId,
                items: [ this.redflyOptions.tabPanel ],
                x: REDfly.config.nextWindowCoordinates[0],
                y: REDfly.config.nextWindowCoordinates[1],
                width: REDfly.config.windowWidth + 500,
                height: REDfly.config.windowHeight
            }
        );
        this.redflyStores.anatomicalExpressionTerms.load();
        this.redflyStores.associatedRc.load();
        this.redflyStores.coordinates.load();
        this.redflyStores.tfbs.load();
        // Increment the coordinates of the next window.
        REDfly.config.nextWindowCoordinates = REDfly.fn.getNextWindowCoordinates(
            REDfly.config.nextWindowCoordinates[0],
            REDfly.config.nextWindowCoordinates[1]
        );
        REDfly.window.tfbs.superclass.initComponent.apply(
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
            // Reset the last transcription factor binding site selected so that dialog may be reopened
            if ( REDfly.state.lastSelectedTranscriptionFactorBindingSite === this.redflyOptions.redflyId )
                REDfly.state.lastSelectedTranscriptionFactorBindingSite = null;
        },
        move: REDfly.fn.browserBorderCheck
    },
    // ------------------------------------------------------------------------------------------
    // Create the TFBS window. This is done after all of the stores have loaded.
    // ------------------------------------------------------------------------------------------
    createComponents: function() {
        var tfbsRecord = this.redflyStores.tfbs.getAt(0);
        var sequenceFromSpeciesScientificName = tfbsRecord.get('sequence_from_species_scientific_name');
        var assayedInSpeciesScientificName = tfbsRecord.get('assayed_in_species_scientific_name');
        var sequence = tfbsRecord.get('sequence');
        var numLines = sequence.length / REDfly.config.sequenceLineLength + 1
        var formattedSequence = '';
        var formattedSequenceWithFlank = '';
        var startIndex, endIndex;
        for ( i = 0;
            i < numLines;
            i++ ) {
            startIndex = i * REDfly.config.sequenceLineLength;
            endIndex = startIndex + REDfly.config.sequenceLineLength
            formattedSequence += '<br>' + sequence.substring(startIndex, endIndex);
        }
        formattedSequence = formattedSequence.toUpperCase();
        sequence = tfbsRecord.get('sequence_with_flank');
        numLines = sequence.length / REDfly.config.sequenceLineLength + 1
        for ( i = 0;
            i < numLines;
            i++ ) {
            startIndex = i * REDfly.config.sequenceLineLength;
            endIndex = startIndex + REDfly.config.sequenceLineLength
            formattedSequenceWithFlank += '<br>' + sequence.substring(startIndex, endIndex);
        }
        formattedSequenceWithFlank = formattedSequenceWithFlank.toUpperCase();
        var notes = ( Ext.isEmpty(tfbsRecord.get('notes'))
            ? ''
            : tfbsRecord.get('notes') );
        // --------------------------------------------------------------------------------
        // Create the tab panel and tabs for the window
        // --------------------------------------------------------------------------------
        // Since new entities do not have old coordinates we may have a case where there
        // are no previous coordinates to show. Dynamically build this widget.
        var previousReleases = tfbsRecord.get('archived_genome_assembly_release_versions').split(',');
        var previousCoordinates = tfbsRecord.get('archived_coordinates').split(',');
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
            : new Ext.Component());
        var publicDatabaseInformationsForTranscriptionFactor = '';
        if ( (tfbsRecord.get('tf_identifier').toLowerCase() !== 'unspecified') &&
            (tfbsRecord.get('public_database_names') !== null) &&
            (tfbsRecord.get('public_database_names') !== '') ) {
            var publicDatabaseNames = tfbsRecord.get('public_database_names').split(',');
            var publicDatabaseLinks = tfbsRecord.get('public_database_links').split(',');
            for ( var publicDatabaseNameIndex = 0;
                publicDatabaseNameIndex < publicDatabaseNames.length;
                publicDatabaseNameIndex++ ) {
                if ( publicDatabaseInformationsForTranscriptionFactor === '' ) {
                    publicDatabaseInformationsForTranscriptionFactor += ' (';
                } else {
                    publicDatabaseInformationsForTranscriptionFactor += ' | ';
                }
                publicDatabaseInformationsForTranscriptionFactor += '<a href="' +
                    publicDatabaseLinks[publicDatabaseNameIndex].replace('gene_identifier', tfbsRecord.get('tf_identifier')) +
                    '" target="_blank">' + publicDatabaseNames[publicDatabaseNameIndex] + '</a>'
            }
            if ( publicDatabaseInformationsForTranscriptionFactor !== '' ) {
                publicDatabaseInformationsForTranscriptionFactor += ')<br />';
            }
        }
        var publicDatabaseInformationsForTranscriptionGene = '';
        if ( (tfbsRecord.get('gene_identifier').toLowerCase() !== 'unspecified') &&
            (tfbsRecord.get('public_database_names') !== null) &&
            (tfbsRecord.get('public_database_names') !== '') ) {
            var publicDatabaseNames = tfbsRecord.get('public_database_names').split(',');
            var publicDatabaseLinks = tfbsRecord.get('public_database_links').split(',');
            for ( var publicDatabaseNameIndex = 0;
                publicDatabaseNameIndex < publicDatabaseNames.length;
                publicDatabaseNameIndex++ ) {
                if ( publicDatabaseNames[publicDatabaseNameIndex] !== 'FlyTF' ) {
                    if ( publicDatabaseInformationsForTranscriptionGene === '' ) {
                        publicDatabaseInformationsForTranscriptionGene += ' (';
                    } else {
                        publicDatabaseInformationsForTranscriptionGene += ' | '
                    }
                    publicDatabaseInformationsForTranscriptionGene += '<a href="' +
                        publicDatabaseLinks[publicDatabaseNameIndex].replace('gene_identifier', tfbsRecord.get('gene_identifier')) +
                        '" target="_blank">' + publicDatabaseNames[publicDatabaseNameIndex] + '</a>'
                }
            }
            if ( publicDatabaseInformationsForTranscriptionGene !== '' ) {
                publicDatabaseInformationsForTranscriptionGene += ')<br />';
            }
        }
        var publicBrowserInformations = '';
        if ( (tfbsRecord.get('public_browser_names') !== null) &&
            (tfbsRecord.get('public_browser_names') !== '') ) {
            var publicBrowserNames = tfbsRecord.get('public_browser_names').split(',');
            var publicBrowserLinks = tfbsRecord.get('public_browser_links').split(',');
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
                                .replace('coordinates', tfbsRecord.get('coordinates'))
                                .replace('redflyBaseUrl', redflyBaseUrl) +
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
                                .replace('chromosome', tfbsRecord.get('chromosome'))
                                .replace('start..end', tfbsRecord.get('start') + '..' + tfbsRecord.get('end')) +
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
                                .replace('release_version', tfbsRecord.get('release_version'))
                                .replace('chromosome', tfbsRecord.get('chromosome'))
                                .replace('start-end', tfbsRecord.get('start') + '-' + tfbsRecord.get('end')) +
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
                html: '<b>Release ' + tfbsRecord.get('release_version') +
                    ' Coordinates: </b>' + tfbsRecord.get('coordinates') + ' <br /><br />'
            },
            // The previous coordinates no longer needed
            //previousCoordinatesWidget,
            {
                html: '<b>"Sequence From" Species: </b>' + sequenceFromSpeciesScientificName + '<br />'
            }, {
                html: '<b>"Assayed In" Species: </b>' + assayedInSpeciesScientificName + '<br />'
            }, {
                html: '<b>Transcription Factor: </b>' + tfbsRecord.get('tf_name') + publicDatabaseInformationsForTranscriptionFactor
            },{
                html: '<b>Transcription Gene: </b>' + tfbsRecord.get('gene_name') + publicDatabaseInformationsForTranscriptionGene
            }, {
                html: '<b>RedFly ID:</b> ' + this.redflyOptions.redflyId + '<br />'
            }, {
                html: '<b>Last Update: </b>' + tfbsRecord.get('last_update') + '<br /><br />'
            }, {
                html: publicBrowserInformations
            }],
            tabTip: 'Basic information about the selected Transcription Factor Binding Site',
            title: 'Information'
        });
        this.redflyOptions.tabPanel.add(infoPanel);
        if ( tfbsRecord.get('sequence_from_species_scientific_name').toLowerCase() === 'drosophila melanogaster' ) {
            var locations = tfbsRecord.get('location').split('$');
            var locationInformations = '';
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
                    html: '<img src="' + REDfly.config.urls.flybase_images + tfbsRecord.get('image') + '" alt=""/><br /><br />'
                }],
                tabTip: 'Location information for the selected Transcription Factor Binding Site',
                title: 'Location'
            });
            this.redflyOptions.tabPanel.add(locationPanel);
        }
        var previousCurators = '';
        if ( tfbsRecord.get('previous_curator_full_names') !== '' ) {
            previousCurators += '<b>Previous Curator(s):</b> ' + tfbsRecord.get('previous_curator_full_names') + '<br /><br />';
        } else {
            previousCurators = '<br />';
        }
        var citationPanel = new Ext.Panel({
            id: 'tab-citation-' + this.redflyOptions.redflyId,
            html: [
                {
                    html: '<b>Citation:</b> ' + tfbsRecord.get('contents') + '<br /><br />'
                }, {
                    html: '<b>Pubmed Reference:</b> ' +
                        REDfly.templates.pubmed_identifier_url.apply({
                            pubmed_id: tfbsRecord.get('pubmed_id'),
                            text: tfbsRecord.get('pubmed_id'),
                            url: REDfly.config.urls.pubmed
                        })
                }, {
                    html: '<b>Curator:</b> ' + tfbsRecord.get('curator_full_name') + '<br />'
                }, {
                    html: previousCurators
                }, {
                    html: '<b>Evidence For Element:</b> ' + tfbsRecord.get('evidence_term') + '<br />'
                }
            ],
            tabTip: 'Citation information/link to the PubMed record',
            title: 'Citation'
        });
        this.redflyOptions.tabPanel.add(citationPanel);
        var associatedRcGrid = new Ext.grid.GridPanel({
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
                    // This should simply create a new RC window
                    var record = grid.getStore().getAt(rowIndex);
                    var entityName = record.get('name');
                    var redflyId = record.get('redfly_id');
                    REDfly.fn.showOrCreateEntityWindow(
                        redflyId,
                        entityName
                    );
                }
            },
            store: this.redflyStores.associatedRc
        });
        var assocPanel = new Ext.Panel({
            id: 'tab-assoc-' + this.redflyOptions.redflyId,
            items: [associatedRcGrid],
            tabTip: 'RC/CRM records associated with this Transcription Factor Binding Site. ' +
                'Click on an entry to access its record.',
            title: 'RC/CRM (' + this.redflyStores.associatedRc.getTotalCount() + ')'
        });
        this.redflyOptions.tabPanel.add(assocPanel);
        var sequencePanel = new Ext.Panel({
            id: 'tab-seq-' + this.redflyOptions.redflyId,
            html: '<b>Size:</b> ' + tfbsRecord.get('sequence').length + '<br><br>' +
                '<b>Sequence:</b><font face="courier">' + formattedSequence + '</font><br><br>' +
                '<b>Sequence with Flank:</b><font face="courier">' + formattedSequenceWithFlank + '</font>',
            tabTip: 'The length and nucleotide sequence of this Transcription Factor Binding Site',
            title: 'Sequence'
        });
        this.redflyOptions.tabPanel.add(sequencePanel);
        var anatomicalExpressionTermGrid = new Ext.grid.GridPanel({
            columns: [
                {
                    dataIndex: 'term',
                    header: 'Term',
                    id: 'term',
                    menuDisabled: true,
                    renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                        return REDfly.templates.redfly_anatomical_expression_identifier_url.apply({
                            url: REDfly.config.urls.redfly,
                            flybase_id: store.getAt(rowIndex).get('identifier'),
                            text: value
                        });
                    },
                    sortable: false,
                    width: 120
                }, {
                    dataIndex: 'identifier',
                    header: 'Identifier',
                    id: 'identifier',
                    menuDisabled: true,
                    sortable: false,
                    width: 95
                }, {
                    // Value will be empty when dataIndex = ''
                    dataIndex: '',
                    header: 'FlyBase Link',
                    menuDisabled: true,
                    renderer: function (value, metaData, record, rowIndex, colIndex, store) {
                        return REDfly.templates.flybase_identifier_url.apply({
                            flybase_id: store.getAt(rowIndex).get('identifier'),
                            url: REDfly.config.urls.flybase_cv_term_report,
                            text: 'FlyBase'
                        });
                    },
                    width: 75
                }
            ],
            store: this.redflyStores.anatomicalExpressionTerms
        });
        var anatomicalExpressionPanel = new Ext.Panel({
            id: 'tab-expr-' + this.redflyOptions.redflyId,
            items: [anatomicalExpressionTermGrid],
            tabTip: 'Anatomical expression terms associated with this Transcription Factor Binding Site. ' +
                'Click on a column header to sort. ' +
                'Click on an anatomical term to initiate a new REDfly search in a separate browser window.',
            title: 'Anatomical Expressions (' + this.redflyStores.anatomicalExpressionTerms.getTotalCount() + ')'
        });
        this.redflyOptions.tabPanel.add(anatomicalExpressionPanel);
        var notesPanel = new Ext.Panel({
            id: 'tab-notes-' + this.redflyOptions.redflyId,
            html: '<b>Notes:</b><br />' + notes,
            tabTip: 'Check this tab for elaborations on anatomical expression patterns ' +
                'and other notes about the regulatory element',
            title: 'Notes'
        });
        this.redflyOptions.tabPanel.add(notesPanel);
        this.show();
        REDfly.state.windowGroup.register(this);
        REDfly.state.windowGroup.bringToFront(this);
    }
});
