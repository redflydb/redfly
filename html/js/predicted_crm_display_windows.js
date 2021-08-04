// ------------------------------------------------------------------------------------------
// Window for displaying Predicted Cis Regulatory Modules
// ------------------------------------------------------------------------------------------
REDfly.window.predictedCrm = Ext.extend(Ext.Window, {
    autoscroll: true,
    closable: false,
    height: REDfly.config.windowHeight,
    // id will be set to the REDfly identifier and used by Ext.WindowGroup to identify the window
    id: null,
    layout: 'fit',
    plain: true,
    resizable: true,
    stateful: false,
    title: 'Predicted CRM',
    width: REDfly.config.windowWidth,
    // REDfly configs
    // We must assign the options and stores objects in initComponent() or it will use a static copy
    // shared among all windows
    entityName: null,
    redflyId: null,    
    redflyOptions: null,
    redflyStores: null,
    // ------------------------------------------------------------------------------------------
    // Create the predicted CRM window. This is done after all of the stores have loaded.
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
            coordinates: null,
            predictedCrm: null
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
        this.redflyStores.predictedCrm = new Ext.data.JsonStore({
            fields: [
                'archived_coordinates',
                'archived_genome_assembly_release_versions',
                'chromosome',
                'contents',
                'coordinates',
                'curator_full_name',
                'end',
                'evidence_term',
                'evidence_subtype_term',
                'gene_identifiers',
                'gene_locus',
                'last_update',
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
                        'Error loading predicted CRM'
                    );
                },
                load: {
                    fn: this.allStoresLoaded,
                    scope: this
                }
            },
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                url: redflyApiUrl + '/jsonstore/predictedcrm/get?redfly_id=' +
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
                    REDfly.window.downloadSinglePredictedCrm.redflyOptions.redflyId = new Array(this.redflyOptions.redflyId);
                    // The default file type is the BED one by alphabetical sort
                    REDfly.window.downloadSinglePredictedCrm.fileTypeChooser.setValue('BED');
                    REDfly.window.downloadSinglePredictedCrm.setFileTypeOptionPanel('BED');
                    REDfly.window.downloadSinglePredictedCrm.show();
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
                title: 'Predicted CRM: ' + this.redflyOptions.entityName,
                id: this.redflyOptions.redflyId,
                items: [ this.redflyOptions.tabPanel ],
                x: REDfly.config.nextWindowCoordinates[0],
                y: REDfly.config.nextWindowCoordinates[1],
                width: REDfly.config.windowWidth + 400,
                height: REDfly.config.windowHeight
            }
        );
        this.redflyStores.anatomicalExpressionTerms.load();
        this.redflyStores.coordinates.load();
        this.redflyStores.predictedCrm.load();
        // Increment the coordinates of the next window.
        REDfly.config.nextWindowCoordinates = REDfly.fn.getNextWindowCoordinates(
            REDfly.config.nextWindowCoordinates[0],
            REDfly.config.nextWindowCoordinates[1]
        );
        REDfly.window.predictedCrm.superclass.initComponent.apply(
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
            // Reset the last predicted CRM selected so that dialog may be reopened
            if ( REDfly.state.lastSelectedPredictedCrm === this.redflyOptions.redflyId )
                REDfly.state.lastSelectedPredictedCrm = null;
        },
        move: REDfly.fn.browserBorderCheck
    },
    // ------------------------------------------------------------------------------------------
    // Create the predicted CRM window. This is done after all of the stores have loaded.
    // ------------------------------------------------------------------------------------------    
    createComponents: function() {
        var predictedCrmRecord = this.redflyStores.predictedCrm.getAt(0);
        var sequenceFromSpeciesScientificName = predictedCrmRecord.get('sequence_from_species_scientific_name');
        var predictedCrmName = predictedCrmRecord.get('name');
        var sequence = predictedCrmRecord.get('sequence');
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
        var notes = ( Ext.isEmpty(predictedCrmRecord.get('notes'))
            ? ''
            : predictedCrmRecord.get('notes') );
        // --------------------------------------------------------------------------------
        // Create the tab panel and tabs for the window
        // --------------------------------------------------------------------------------
        // Since new entities do not have old coordinates we may have a case where there
        // are no previous coordinates to show. Dynamically build this widget.
        var previousReleases = predictedCrmRecord.get('archived_genome_assembly_release_versions').split(',');
        var previousCoordinates = predictedCrmRecord.get('archived_coordinates').split(',');
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
        if ( (predictedCrmRecord.get('public_database_names') !== null) &&
            (predictedCrmRecord.get('public_database_names') !== '') ) {
            var geneLoci = predictedCrmRecord.get('gene_locus').split(',');
            var geneLociIdentifiers = predictedCrmRecord.get('gene_identifiers').split(',');
            var publicDatabaseNames = predictedCrmRecord.get('public_database_names').split(',');
            var publicDatabaseLinks = predictedCrmRecord.get('public_database_links').split(',');
            for (var geneLocusIndex = 0;
                geneLocusIndex < geneLoci.length;
                geneLocusIndex++) {
                if ( geneLocusIndex !== 0 ) {
                    publicDatabaseInformations += ', ';
                }
                for ( var publicDatabaseNameIndex = 0;
                    publicDatabaseNameIndex < publicDatabaseNames.length;
                    publicDatabaseNameIndex++ ) {
                    if ( publicDatabaseNames[publicDatabaseNameIndex] !== 'FlyTF' ) {
                        if ( publicDatabaseNameIndex === 0 ) {
                            publicDatabaseInformations += geneLoci[geneLocusIndex] + ' (';
                        } else {
                            publicDatabaseInformations += ' | ';
                        }
                        publicDatabaseInformations += '<a href="' +
                            publicDatabaseLinks[publicDatabaseNameIndex].replace('gene_identifier', geneLociIdentifiers[geneLocusIndex]) +
                            '" target="_blank">' + publicDatabaseNames[publicDatabaseNameIndex] + '</a>';
                    }
                }
                publicDatabaseInformations += ')';
            }
            publicDatabaseInformations += '<br />';
        }
        var publicBrowserInformations = '';
        if ( (predictedCrmRecord.get('public_browser_names') !== null) &&
            (predictedCrmRecord.get('public_browser_names') !== '') ) {
            var publicBrowserNames = predictedCrmRecord.get('public_browser_names').split(',');
            var publicBrowserLinks = predictedCrmRecord.get('public_browser_links').split(',');
            for ( index = 0;
                index < publicBrowserNames.length;
                index++ ) {
                if ( publicBrowserInformations === '' ) {
                    publicBrowserInformations = '<b>Browser Links: </b>';
                }
                switch (publicBrowserNames[index]) {
                    case 'GBrowse':
                        if ( publicBrowserInformations !== '<b>Browser Links: </b>' ) {
                            publicBrowserInformations += ' | ';
                        }
                        publicBrowserInformations += '<a href="' +
                            publicBrowserLinks[index]
                                .replace('coordinates', predictedCrmRecord.get('coordinates'))
                                .replace('redflyBaseUrl', redflyBaseUrl) +
                            '" target="_blank">' +
                            publicBrowserNames[index] +
                            '</a>';
                        break;
                    case 'JBrowse':
                        if ( publicBrowserInformations !== '<b>Browser Links: </b>' ) {
                            publicBrowserInformations += ' | ';
                        }
                        publicBrowserInformations += '<a href="' +
                            publicBrowserLinks[index]
                                .replace('chromosome', predictedCrmRecord.get('chromosome'))
                                .replace('start..end', predictedCrmRecord.get('start') + '..' + predictedCrmRecord.get('end')) +
                            '" target="_blank">' +
                            publicBrowserNames[index] +
                            '</a>';
                        break;
                    case 'UCSC':
                        if ( publicBrowserInformations !== '<b>Browser Links: </b>' ) {
                            publicBrowserInformations += ' | ';
                        }
                        publicBrowserInformations += '<a href="' +
                            publicBrowserLinks[index]
                                .replace('release_version', predictedCrmRecord.get('release_version'))
                                .replace('chromosome', predictedCrmRecord.get('chromosome'))
                                .replace('start-end', predictedCrmRecord.get('start') + '-' + predictedCrmRecord.get('end')) +
                            '" target="_blank">' +
                            publicBrowserNames[index] +
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
                html: '<b>Release ' + predictedCrmRecord.get('release_version') + ' Coordinates: </b>' +
                    predictedCrmRecord.get('coordinates') + ' <br /><br />'
            },
            // The previous coordinates no longer needed  
            //previousCoordinatesWidget,
            {
                html: '<b>"Sequence From" Species: </b>' + sequenceFromSpeciesScientificName + '<br />'
            }, {
                html: '<b>Gene Locus: </b>' + publicDatabaseInformations
            }, {
                html: '<b>RedFly ID:</b> ' + this.redflyOptions.redflyId + '<br />'
            }, {
                html: '<b>Last Update: </b>' + predictedCrmRecord.get('last_update') + '<br /><br />'
            }, {
                html: publicBrowserInformations
            }],
            tabTip: 'Basic information about the selected predicted CRM',
            title: 'Information'
        });
        this.redflyOptions.tabPanel.add(infoPanel);
        if ( predictedCrmName.match(/[a-zA-Z0-9()-]_VT[0-9]/g) ) {
            var vc = predictedCrmName.split('_')[1];
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
                (newSecondaryPubmedReference !== predictedCrmRecord.get('pubmed_id')) && 
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
            secondaryPubmedReferenceLinks = '<b>Secondary PubMed Reference(s): </b> ' +
                secondaryPubmedReferenceLinks +
                '<br /><br />';
        }
        var previousCurators = '';
        if ( predictedCrmRecord.get('previous_curator_full_names') !== '' ) {
            previousCurators += '<b>Previous Curator(s):</b> ' + predictedCrmRecord.get('previous_curator_full_names') + '<br /><br />';
        } else {
            previousCurators = '<br />';
        }
        var citationPanel = new Ext.Panel({
            id: 'tab-citation-' + this.redflyOptions.redflyId,
            html: [
                {
                    html: '<b>Citation:</b> ' + predictedCrmRecord.get('contents') + '<br /><br />'
                }, {
                    html: '<b>PubMed Reference:</b> ' +
                        REDfly.templates.pubmed_identifier_url.apply({
                            pubmed_id: predictedCrmRecord.get('pubmed_id'),
                            text: predictedCrmRecord.get('pubmed_id'),
                            url: REDfly.config.urls.pubmed
                        })
                }, {
                    html: secondaryPubmedReferenceLinks
                }, {
                    html: vtHTMLString
                }, {
                    html: '<b>Curator:</b> ' + predictedCrmRecord.get('curator_full_name') + '<br />'
                }, {
                    html: previousCurators
                }, {
                    html: '<b>Sequence Source:</b> ' + predictedCrmRecord.get('sequence_source') + '<br />'
                }, {
                    html: '<b>Evidence For Element:</b> ' + predictedCrmRecord.get('evidence_term') + '<br />'
                }, {
                    html: '<b>Evidence Subtype For Element:</b> ' + predictedCrmRecord.get('evidence_subtype_term') + '<br />'
                }                
            ],
            tabTip: 'Citation information/link to the PubMed record',
            title: 'Citation'
        });
        this.redflyOptions.tabPanel.add(citationPanel);
        var sequencePanel = new Ext.Panel({
            id: 'tab-seq-' + this.redflyOptions.redflyId,
            html: '<b>Size:</b> ' + predictedCrmRecord.get('sequence').length +
                '<br /><br /><b>Sequence:</b><font face="courier">' +
                formattedSequence + '</font>',
            tabTip: 'The length and nucleotide sequence of this predicted CRM',
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
                            (pubmedIdentifier !== predictedCrmRecord.get('pubmed_id')) ) {
                            return REDfly.templates.pubmed_identifier_url.apply({
                                pubmed_id: store.getAt(rowIndex).get('pubmed_id'),
                                url: REDfly.config.urls.pubmed,
                                text: store.getAt(rowIndex).get('pubmed_id')
                            });
                        } else {
                            return REDfly.templates.pubmed_identifier_url.apply({
                                pubmed_id: predictedCrmRecord.get('pubmed_id'),
                                url: REDfly.config.urls.pubmed,
                                text: predictedCrmRecord.get('pubmed_id')
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
            tabTip: 'Anatomical expression terms associated with this predicted CRM. ' +
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
