// ------------------------------------------------------------------------------------------
// Window for displaying Cis Regulatory Module Segments
// ------------------------------------------------------------------------------------------
REDfly.window.crmSegment = Ext.extend(Ext.Window, {
    autoscroll: true,
    closable: false,
    height: REDfly.config.windowHeight,
    // id will be set to the REDfly identifier and used by Ext.WindowGroup to identify the window    
    id: null,
    layout: 'fit',
    plain: true,
    resizable: true,
    stateful: false,
    title: 'CRM Segment',
    width: REDfly.config.windowWidth,
    // REDfly configs
    // We must assign the options and stores objects in initComponent() or it will use a static copy
    // shared among all windows
    entityName: null,
    redflyId: null,
    redflyOptions: null,
    redflyStores: null,
    // ------------------------------------------------------------------------------------------
    // Create the CRMS window. This is done after all of the stores have loaded.
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
            crmSegment: null,
            image: null
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
                url: redflyApiUrl + '/jsonstore/crmsegment/images?redfly_id=' +
                    this.redflyOptions.redflyId
            }),
            root: 'results',
            totalProperty: 'num'
        });
        this.redflyOptions.storesNumber++;
        this.redflyStores.crmSegment = new Ext.data.JsonStore({
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
                'evidence_subtype_term',
                'figure_labels',
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
                'sequence_source',
                'start'
            ],
            idProperty: 'id',
            listeners: {
                exception: function() {
                    Ext.Msg.alert(
                        'Error',
                        'Error loading CRM Segment'
                    );
                },
                load: {
                    fn: this.allStoresLoaded,
                    scope: this
                }
            },
            proxy: new Ext.data.HttpProxy({
                method: 'GET',
                url: redflyApiUrl + '/jsonstore/crmsegment/get?redfly_id=' +
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
                    REDfly.window.downloadSingleCrmSegment.redflyOptions.redflyId = new Array(this.redflyOptions.redflyId);
                    // The default file type is the BED one by alphabetical sort
                    REDfly.window.downloadSingleCrmSegment.fileTypeChooser.setValue('BED');
                    REDfly.window.downloadSingleCrmSegment.setFileTypeOptionPanel('BED');
                    REDfly.window.downloadSingleCrmSegment.show();
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
                title: 'CRM Segment: ' + this.redflyOptions.entityName,
                id: this.redflyOptions.redflyId,
                items: [ this.redflyOptions.tabPanel ],
                x: REDfly.config.nextWindowCoordinates[0],
                y: REDfly.config.nextWindowCoordinates[1],
                width: REDfly.config.windowWidth + 500,
                height: REDfly.config.windowHeight
            }
        );
        this.redflyStores.anatomicalExpressionTerms.load();
        this.redflyStores.crmSegment.load();
        this.redflyStores.coordinates.load();
        this.redflyStores.image.load();
        // Increment the coordinates of the next window.
        REDfly.config.nextWindowCoordinates = REDfly.fn.getNextWindowCoordinates(
            REDfly.config.nextWindowCoordinates[0],
            REDfly.config.nextWindowCoordinates[1]
        );
        REDfly.window.crmSegment.superclass.initComponent.apply(
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
            // Reset the last CRM segment selected so that dialog may be reopened
            if ( REDfly.state.lastSelectedCrmSegment === this.redflyOptions.redflyId )
                REDfly.state.lastSelectedCrmSegment = null;
        },
        move: REDfly.fn.browserBorderCheck
    },
    // ------------------------------------------------------------------------------------------
    // Create the CRM segment window. This is done after all of the stores have loaded.
    // ------------------------------------------------------------------------------------------    
    createComponents: function() {
        var crmSegmentRecord = this.redflyStores.crmSegment.getAt(0);
        var sequenceFromSpeciesScientificName = crmSegmentRecord.get('sequence_from_species_scientific_name');
        var assayedInSpeciesScientificName = crmSegmentRecord.get('assayed_in_species_scientific_name');
        var figureLabels = crmSegmentRecord.get('figure_labels');
        var crmSegmentName = crmSegmentRecord.get('name');
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
        if ( crmSegmentName.match(/[a-zA-Z0-9()-]_VT[0-9]/g) ) {
            var vc = crmSegmentName.split('_')[1];
            vc_href = 'http://enhancers.starklab.org/tile/'+ vc;
            imageString += '<br><b>Fly Enhancers: </b> <a target="_blank" href="' + 
                vc_href + '/">' + vc + '</a>';
        }
        var sequence = crmSegmentRecord.get('sequence');
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
        var notes = ( Ext.isEmpty(crmSegmentRecord.get('notes'))
            ? ''
            : crmSegmentRecord.get('notes') );
        var hasFbtp = '<b>FBtp ID:</b> None';
        if ( ! Ext.isEmpty(crmSegmentRecord.get('fbtp')) ) {
            hasFbtp = '<b>FBtp ID:</b> <a href="http://flybase.org/reports/' + crmSegmentRecord.get('fbtp')
                + '.html" target="_blank">' + crmSegmentRecord.get('fbtp') + '</a>';
        }
        // --------------------------------------------------------------------------------
        // Create the tab panel and tabs for the window
        // --------------------------------------------------------------------------------
        // Since new entities do not have old coordinates we may have a case where there
        // are no previous coordinates to show. Dynamically build this widget.
        var previousReleases = crmSegmentRecord.get('archived_genome_assembly_release_versions').split(',');
        var previousCoordinates = crmSegmentRecord.get('archived_coordinates').split(',');
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
        if ( (crmSegmentRecord.get('gene_identifier').toLowerCase() !== 'unspecified') && 
            (crmSegmentRecord.get('public_database_names') !== null) &&
            (crmSegmentRecord.get('public_database_names') !== '') ) {
            var publicDatabaseNames = crmSegmentRecord.get('public_database_names').split(',');
            var publicDatabaseLinks = crmSegmentRecord.get('public_database_links').split(',');    
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
                        publicDatabaseLinks[publicDatabaseNameIndex].replace('gene_identifier', crmSegmentRecord.get('gene_identifier')) +
                        '" target="_blank">' +
                        publicDatabaseNames[publicDatabaseNameIndex] +
                        '</a>';
                }
            }
            publicDatabaseInformations += ')<br />';
        }
        var publicBrowserInformations = '';
        if ( (crmSegmentRecord.get('public_browser_names') !== null) &&
            (crmSegmentRecord.get('public_browser_names') !== '') ) {
            var publicBrowserNames = crmSegmentRecord.get('public_browser_names').split(',');
            var publicBrowserLinks = crmSegmentRecord.get('public_browser_links').split(',');
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
                                .replace('coordinates', crmSegmentRecord.get('coordinates'))
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
                                .replace('chromosome', crmSegmentRecord.get('chromosome'))
                                .replace('start..end', crmSegmentRecord.get('start') + '..' + crmSegmentRecord.get('end')) +
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
                                .replace('release_version', crmSegmentRecord.get('release_version'))
                                .replace('chromosome', crmSegmentRecord.get('chromosome'))
                                .replace('start-end', crmSegmentRecord.get('start') + '-' + crmSegmentRecord.get('end')) +
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
                html: '<b>Release ' + crmSegmentRecord.get('release_version') + ' Coordinates: </b>' +
                    crmSegmentRecord.get('coordinates') + ' <br /><br />'
            },
            // The previous coordinates no longer needed 
            //previousCoordinatesWidget,
            {
                html: '<b>"Sequence From" Species: </b>' + sequenceFromSpeciesScientificName + '<br />'
            }, {
                html: '<b>"Assayed In" Species: </b>' + assayedInSpeciesScientificName + '<br />'
            }, {
                html: '<b>Gene Name: </b>' + crmSegmentRecord.get('gene_name') + publicDatabaseInformations
            }, {
                html: hasFbtp + '<br /><br />'
            }, {
                html: '<b>RedFly ID:</b> ' + this.redflyOptions.redflyId + '<br />'
            }, {
                html: '<b>Last Update: </b>' + crmSegmentRecord.get('last_update') + '<br /><br />'
            }, {
                html: publicBrowserInformations
            }],
            tabTip: 'Basic information about the selected CRM Segment',
            title: 'Information'
        });
        this.redflyOptions.tabPanel.add(infoPanel);
        if ( crmSegmentRecord.get('sequence_from_species_scientific_name').toLowerCase() === 'drosophila melanogaster' ) {
            var locations = crmSegmentRecord.get('location').split('$');
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
                    html: '<img src="' + REDfly.config.urls.flybase_images + crmSegmentRecord.get('image') + 
                        '" alt=""/><br /><br />'
                }],
                tabTip: 'Location information for the selected CRM Segment',
                title: 'Location'
            });
            this.redflyOptions.tabPanel.add(locationPanel);
        }
        var imagePanel = new Ext.Panel({
            id: 'tab-image-' + this.redflyOptions.redflyId,
            html: [ imageString ],
            tabTip: 'Images of reporter gene expression regulated by the CRM segment. ' +
                'Click on the  image to go to FlyExpress.',
            title: 'Image (' + this.redflyStores.image.getTotalCount() + ')'
        });
        this.redflyOptions.tabPanel.add(imagePanel);
        if ( crmSegmentName.includes('Unspecified_VT') ) {
            var vc = crmSegmentName.split('_')[1];
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
                (newSecondaryPubmedReference !== crmSegmentRecord.get('pubmed_id')) && 
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
        if ( crmSegmentRecord.get('previous_curator_full_names') !== '' ) {
            previousCurators += '<b>Previous Curator(s):</b> ' + crmSegmentRecord.get('previous_curator_full_names') + '<br /><br />';
        } else {
            previousCurators = '<br />';
        }
        var citationPanel = new Ext.Panel({
            id: 'tab-citation-' + this.redflyOptions.redflyId,
            html: [
                {
                    html: '<b>Citation:</b> ' + crmSegmentRecord.get('contents') + '<br /><br />'
                }, {
                    html: '<b>Pubmed Reference:</b> ' +
                        REDfly.templates.pubmed_identifier_url.apply({
                            pubmed_id: crmSegmentRecord.get('pubmed_id'),
                            text: crmSegmentRecord.get('pubmed_id'),
                            url: REDfly.config.urls.pubmed
                        })
                }, {
                    html: secondaryPubmedReferenceLinks
                }, {
                    html: vtHTMLString
                }, {
                    html: '<b>Curator:</b> ' + crmSegmentRecord.get('curator_full_name') + '<br />'
                }, {
                    html: previousCurators
                }, {
                    html: '<b>Sequence Source:</b> ' + crmSegmentRecord.get('sequence_source') + '<br />'
                }, {
                    html: '<b>Evidence For Element:</b> ' + crmSegmentRecord.get('evidence_term') + '<br />'
                }, {
                    html: '<b>Evidence Subtype For Element:</b> ' + crmSegmentRecord.get('evidence_subtype_term') + '<br />'
                }
            ],
            tabTip: 'Citation information/link to the PubMed record',
            title: 'Citation'
        });
        this.redflyOptions.tabPanel.add(citationPanel);
        var sequencePanel = new Ext.Panel({
            id: 'tab-seq-' + this.redflyOptions.redflyId,
            html: '<b>Size:</b> ' + crmSegmentRecord.get('sequence').length +
                '<br /><br /><b>Sequence:</b><font face="courier">' +
                formattedSequence + '</font>',
            tabTip: 'The length and nucleotide sequence of this CRM Segment',
            title: 'Sequence',
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
                            text: value,
                            url: REDfly.config.urls.redfly
                        });
                        var flybaseLink = REDfly.templates.flybase_identifier_url.apply({
                            flybase_id: store.getAt(rowIndex).get('identifier'),
                            text: 'FlyBaseID',
                            url: REDfly.config.urls.flybase_cv_term_report                            
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
                            (pubmedIdentifier !== crmSegmentRecord.get('pubmed_id')) ) {
                            return REDfly.templates.pubmed_identifier_url.apply({
                                pubmed_id: store.getAt(rowIndex).get('pubmed_id'),
                                url: REDfly.config.urls.pubmed,
                                text: store.getAt(rowIndex).get('pubmed_id')
                            });
                        } else {
                            return REDfly.templates.pubmed_identifier_url.apply({
                                pubmed_id: crmSegmentRecord.get('pubmed_id'),
                                url: REDfly.config.urls.pubmed,
                                text: crmSegmentRecord.get('pubmed_id')
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
                                    text: store.getAt(rowIndex).get('stage_on_term'),
                                    url: REDfly.config.urls.flybase_cv_term_report
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
                                    text: store.getAt(rowIndex).get('stage_off_term'),
                                    url: REDfly.config.urls.flybase_cv_term_report
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
            tabTip: 'Anatomical expression terms associated with this CRM Segment. ' +
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
