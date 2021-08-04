Ext.define(
    'REDfly.view.admin.audit.AuditController',
    {
        alias: 'controller.audit',
        extend: 'Ext.app.ViewController',
        doCapitalize: function (value, metaData) {
            value = Ext.String.htmlEncode(value);
            value = value.charAt(0).toUpperCase() + value.slice(1);
            metaData.tdAttr = 'data-qtip="' + value + '"';
            return value;
        },        
        doNotifyAuthors: function (selection) {
            Ext.Ajax.request({
                failure: function (response) {
                    var errorMessage = 'Notification Error: ' + response.statusText;
                    Ext.MessageBox.show({
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR,
                        msg: errorMessage.replace(' ', '&nbsp;'),
                        title: 'Error'
                    });
                },
                jsonData: {
                    pubmed_ids: Ext.Array.unique(Ext.Array.reduce(
                        selection,
                        function (previous, value) {
                            return Ext.Array.push(previous, value.get('pubmed_id'));
                        },
                        []
                    ))
                },
                method: 'POST',                
                success: function(response) {
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
                    }
                },
                url: REDfly.Config.getApiV2Url() + '/audit/notify/authors'
            });
        },
        doShowTooltip: function (value, metaData) {
            value = Ext.String.htmlEncode(value);
            metaData.tdAttr = 'data-qtip="' + value + '"';
            return value;
        },
        doStateUpdate: function (state) {
            var grid = this.lookup('search-results-panel').getActiveTab(),
            selection = grid.getSelectionModel().getSelection(),
            count = selection.length;
            Ext.Array.forEach(
                selection,
                function (item) {
                    item.set('state', state);
                }
            );
            grid.getStore().sync({
                failure: function () {
                    var errorMessage = 'You are not authorized to update the records selected with "' + 
                        state + '" as your state chosen. Please, contact with an administrator.';
                    Ext.MessageBox.show({
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR,
                        msg: errorMessage.replace(' ', '&nbsp;'),
                        title: 'Error'
                    });
                },
                scope: this,
                success: function () {
                    if ( (state === 'approved') &&
                        this.getViewModel().get('notifyAuthorsCheckBoxValue') ) {
                        this.doNotifyAuthors(selection);
                        this.getViewModel().set('notifyAuthorsCheckBoxValue', false);
                    }
                    grid.getStore().load();
                    switch(grid.getReference()) {
                        case 'rc-list':
                            this.lookup('rc-ts-list').getStore().load();
                            this.lookup('rc-no-ts-list').getStore().load();
                            break;
                        case 'crm-segment-list':
                            this.lookup('crm-segment-ts-list').getStore().load();
                            this.lookup('crm-segment-no-ts-list').getStore().load();
                            break;
                        case 'predicted-crm-list':
                            this.lookup('predicted-crm-ts-list').getStore().load();
                            this.lookup('predicted-crm-no-ts-list').getStore().load();
                            break;                            
                    }
                }
            });
            return count;
        },        
        onApproveSelectedClicked: function () {
            selectedRecordsNumber = this.lookup('search-results-panel').getActiveTab().getSelectionModel().getSelection().length;
            if ( selectedRecordsNumber <= 0 ) {
                Ext.MessageBox.show({
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR,
                    msg: 'No record(s) selected to be approved',
                    title: 'Error'
                });
            } else {
                Ext.MessageBox.show({
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.INFO,
                    msg: this.doStateUpdate('approved') + ' record(s) approved',
                    title: 'Success'
                });
            }
        },
        onAssayedInSpeciesSelected: function() {
            var view = this.getViewModel();
            if ( view.get('assayedInSpeciesComboBoxValue') !== null ) {
                assayedInSpeciesScientificName = view.get('assayedInSpeciesComboBoxValue');
                view.get('anatomicalExpressionStore').filter('species_scientific_name', assayedInSpeciesScientificName);
                view.get('onDevelopmentalStageStore').filter('species_scientific_name', assayedInSpeciesScientificName);
                view.get('offDevelopmentalStageStore').filter('species_scientific_name', assayedInSpeciesScientificName);                
            }
        },        
        onBeforeTabChange: function (tabPanel, newCard, oldCard) {
            var view = this.getViewModel();
            if ( (view.get('startTextFieldValue') !== null) &&
                (view.get('endTextFieldValue') !== null) &&
                (view.get('endTextFieldValue') < view.get('startTextFieldValue')) ) {
                Ext.MessageBox.show({
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR,
                    msg: 'Invalid coordinates',
                    title: 'Error'
                });
                return;
            }            
            oldCard.getSelectionModel().deselectAll();
            view.set(
                'notifyAuthorsCheckBoxDisabled',
                !((newCard.getReference() === 'crm-segment-list') ||
                    (newCard.getReference() === 'predicted-crm-list') ||
                    (newCard.getReference() === 'rc-list'))
            );
            view.set('notifyAuthorsCheckBoxValue', false);
            var reference = newCard.getReference();
            var tabStore = null;
            var errorMargin = null;            
            switch ( reference ) {
                case 'rc-list':
                    tabStore = view.get('rcStore');
                    errorMargin = rcErrorMargin;
                    break;
                case 'rc-no-ts-list':
                    tabStore = view.get('rcNoTsStore');
                    errorMargin = rcErrorMargin;
                    break;
                case 'rc-ts-list':
                    tabStore = view.get('rcTsStore');
                    errorMargin = rcErrorMargin;
                    break;
                case 'tfbs-list':
                    tabStore = view.get('tfbsStore');
                    errorMargin = tfbsErrorMargin;
                    break;
                case 'crm-segment-list':
                    tabStore = view.get('crmSegmentStore');
                    errorMargin = crmSegmentErrorMargin;
                    break;
                case 'crm-segment-no-ts-list':
                    tabStore = view.get('crmSegmentNoTsStore');
                    errorMargin = crmSegmentErrorMargin;
                    break;
                case 'crm-segment-ts-list':
                    tabStore = view.get('crmSegmentTsStore');
                    errorMargin = crmSegmentErrorMargin;
                    break;
                case 'predicted-crm-list':
                    tabStore = view.get('predictedCrmStore');
                    errorMargin = predictedCrmErrorMargin;
                    break;
                case 'predicted-crm-no-ts-list':
                    tabStore = view.get('predictedCrmNoTsStore');
                    errorMargin = predictedCrmErrorMargin;
                    break;
                case 'predicted-crm-ts-list':
                    tabStore = view.get('predictedCrmTsStore');
                    errorMargin = predictedCrmErrorMargin;
                    break;
            }
            tabStore.load();
            tabStore.clearFilter(true);
            var startValue = 0;
            if ( view.get('startTextFieldValue') !== null )  {
                startValue = view.get('startTextFieldValue');
            }
            var endValue = 0;
            if ( view.get('endTextFieldValue') !== null ) {
                endValue = view.get('endTextFieldValue');
            }
            if ( (0 < startValue) &&
                (endValue === 0) ) {
                tabStore.filterBy(function(record) {
                    if ( ((startValue - errorMargin) <= record.get('start')) &&
                        (record.get('start') <= (startValue + errorMargin)) ) {
                        return true;
                    } else {
                        return false; 
                    }
                });
            } else {
                if ( (startValue === 0) &&
                    (0 < endValue) ) {
                    tabStore.filterBy(function(record) {
                        if ( ((endValue - errorMargin) <= record.get('end')) &&
                            (record.get('end') <= (endValue + errorMargin)) ) {
                            return true;
                        } else {
                            return false; 
                        }
                    });
                } else {
                    if ( (0 < startValue) &&
                        (0 < endValue) ) {
                        tabStore.filterBy(function(record) {
                            if ( ((startValue - errorMargin) <= record.get('start')) &&
                                (record.get('start') <= (startValue + errorMargin))  &&
                                ((endValue - errorMargin) <= record.get('end')) &&
                                (record.get('end') <= (endValue + errorMargin)) ) {
                                return true;
                            } else {
                                return false; 
                            }
                        });
                    }
                }
            }
            if ( view.get('sequenceFromSpeciesComboBoxValue') !== null ) {
                tabStore.filter('sequence_from_species_scientific_name', view.get('sequenceFromSpeciesComboBoxValue'));
            }
            if ( view.get('assayedInSpeciesComboBoxValue') !== null ) {
                tabStore.filter('assayed_in_species_scientific_name', view.get('assayedInSpeciesComboBoxValue'));
            }  
            if ( view.get('elementNameTextFieldValue') !== null ) {
                tabStore.filter('name', view.get('elementNameTextFieldValue'));
            }
            if ( view.get('curatorFullNameComboBoxValue') !== null ) {
                tabStore.filter('curator_full_name', view.get('curatorFullNameComboBoxValue'));
            }
            var state = '';
            if ( view.get('stateComboBoxValue') === null ) {
                state = predefinedState;
            } else {
                state = view.get('stateComboBoxValue');
            }
            if ( state !== '' ) {
                tabStore.filter('state', state);
            }
            if ( view.get('pmidTextFieldValue') !== null ) {
                tabStore.filter('pubmed_id', view.get('pmidTextFieldValue'));
            }
            if ( view.get('chromosomeComboBoxValue') !== null ) {
                tabStore.filter('chromosome_display', view.get('chromosomeComboBoxValue'));
            }
            if ( view.get('geneComboBoxValue') !== null ) {
                geneDisplay = view.get('geneComboBoxValue');
                switch ( reference ) {
                    // For the entity lists with gene data
                    case 'rc-list':
                    case 'rc-no-ts-list':
                    case 'rc-ts-list':
                    case 'crm-segment-list':
                    case 'crm-segment-no-ts-list':
                    case 'crm-segment-ts-list':
                    case 'tfbs-list':
                        tabStore.filter('gene_display', geneDisplay);
                        break;
                    // For the entity lists without any gene data
                    case 'predicted-crm-list':
                    case 'predicted-crm-no-ts-list':
                    case 'predicted-crm-ts-list':
                        tabStore.filter('id', '0');
                        break;
                }                
            }
            if ( view.get('transcriptionFactorComboBoxValue') !== null ) {
                transcriptionFactorDisplay = view.get('transcriptionFactorComboBoxValue');
                switch ( reference ) {
                    // For the entity lists with transcription factor data
                    case 'tfbs-list':
                        tabStore.filter('transcription_factor_display', transcriptionFactorDisplay);
                        break;                    
                    // For the entity lists without any transcription factor data
                    case 'rc-list':
                    case 'rc-no-ts-list':
                    case 'rc-ts-list':
                    case 'crm-segment-list':
                    case 'crm-segment-no-ts-list':
                    case 'crm-segment-ts-list':
                    case 'predicted-crm-list':
                    case 'predicted-crm-no-ts-list':
                    case 'predicted-crm-ts-list':
                        tabStore.filter('id', '0');
                        break;

                }
            }            
            if ( view.get('anatomicalExpressionComboBoxValue') !== null ) {
                anatomicalExpressionDisplay = view.get('anatomicalExpressionComboBoxValue');
                switch ( reference ) {
                    // For the entity lists with anatomical expressions                    
                    case 'rc-list':
                    case 'crm-segment-list':
                    case 'predicted-crm-list':
                        tabStore.filter('anatomical_expression_displays', new RegExp(Ext.String.escapeRegex(anatomicalExpressionDisplay)));
                        break;
                    // For the entity lists with anatomical expression data
                    case 'rc-no-ts-list':
                    case 'rc-ts-list':
                    case 'crm-segment-no-ts-list':
                    case 'crm-segment-ts-list':
                    case 'predicted-crm-no-ts-list':
                    case 'predicted-crm-ts-list':
                        tabStore.filter('anatomical_expression_display', anatomicalExpressionDisplay);
                        break;
                    // For the entity lists without any anatomical expression data
                    case 'tfbs-list':
                        tabStore.filter('id', '0');
                        break;
                }
            }
            if ( view.get('onDevelopmentalStageComboBoxValue') !== null ) {
                onDevelopmentalStageDisplay = view.get('onDevelopmentalStageComboBoxValue');
                switch ( reference ) {
                    // For the entity lists with on developmental stage data
                    case 'rc-ts-list':
                    case 'crm-segment-ts-list':
                    case 'predicted-crm-ts-list':
                        tabStore.filter('on_developmental_stage_display', onDevelopmentalStageDisplay);
                        break;
                    // For the entity lists without any on developmental stage data                        
                    case 'rc-list':
                    case 'rc-no-ts-list':
                    case 'crm-segment-list':
                    case 'crm-segment-no-ts-list':                        
                    case 'predicted-crm-list':
                    case 'predicted-crm-no-ts-list':
                        break;
                    case 'tfbs-list':
                        tabStore.filter('id', '0');
                        break;
                }
            }
            if ( view.get('offDevelopmentalStageComboBoxValue') !== null ) {
                offDevelopmentalStageDisplay = view.get('offDevelopmentalStageComboBoxValue');
                switch ( reference ) {
                    // For the entity lists with off developmental stage data
                    case 'rc-ts-list':
                    case 'crm-segment-ts-list':
                    case 'predicted-crm-ts-list':
                        tabStore.filter('off_developmental_stage_display', offDevelopmentalStageDisplay);
                        break;
                    // For the entity lists without any off developmental stage data
                    case 'rc-list':
                    case 'rc-no-ts-list':
                    case 'crm-segment-list':
                    case 'crm-segment-no-ts-list':                        
                    case 'predicted-crm-list':
                    case 'predicted-crm-no-ts-list':
                        break;
                    case 'tfbs-list':                        
                        tabStore.filter('id', '0');
                        break;
                }
            }
            if ( view.get('biologicalProcessComboBoxValue') !== null ) {
                biologicalProcessDisplay = view.get('biologicalProcessComboBoxValue');
                switch ( reference ) {
                    // For the entity lists with biological process data
                    case 'rc-ts-list':
                    case 'crm-segment-ts-list':
                    case 'predicted-crm-ts-list':
                        tabStore.filter('biological_process_display', biologicalProcessDisplay);
                        break;
                    // For the entity lists without any biological process data
                    case 'rc-list':
                    case 'rc-no-ts-list':
                    case 'tfbs-list':
                    case 'crm-segment-list':
                    case 'crm-segment-no-ts-list':                        
                    case 'predicted-crm-list':
                    case 'predicted-crm-no-ts-list':
                        tabStore.filter('id', '0');
                        break;
                }
            }
        },
        onCrmSegmentLoad: function (store) {
            this.getViewModel().set('crmSegmentCount', store.getCount());
        },
        onCrmSegmentNoTsLoad: function (store) {
            this.getViewModel().set('crmSegmentNoTsCount', store.getCount());
        },
        onCrmSegmentTsLoad: function (store) {
            this.getViewModel().set('crmSegmentTsCount', store.getCount());
        },
        onDeleteSelectedClicked: function () {
            selectedRecordsNumber = this.lookup('search-results-panel').getActiveTab().getSelectionModel().getSelection().length;
            if ( selectedRecordsNumber <= 0 ) {
                Ext.MessageBox.show({
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR,
                    msg: 'No record(s) selected to be marked for deletion',
                    title: 'Error'
                });
            } else {
                Ext.Msg.confirm(
                    'Warning to curators',
                    'Are you sure to mark the selected record(s) for deletion?',
                    function (btn) {
                        if (btn === 'yes') {
                            var count = this.doStateUpdate('deleted');
                            Ext.MessageBox.show({
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR,
                                msg: count + ' record(s) marked for deletion',
                                title: 'Success'
                            });                        
                        } 
                    },
                    this
                );
            }
        },
        onPredictedCrmLoad: function (store) {
            this.getViewModel().set('predictedCrmCount', store.getCount());
        },
        onPredictedCrmNoTsLoad: function (store) {
            this.getViewModel().set('predictedCrmNoTsCount', store.getCount());
        },        
        onPredictedCrmTsLoad: function (store) {
            this.getViewModel().set('predictedCrmTsCount', store.getCount());
        },
        onRcLoad: function (store) {
            this.getViewModel().set('rcCount', store.getCount());
        },
        onRcNoTsLoad: function (store) {
            this.getViewModel().set('rcNoTsCount', store.getCount());
        },
        onRcTsLoad: function (store) {
            this.getViewModel().set('rcTsCount', store.getCount());
        },
        onRejectSelectedClicked: function () {
            selection = this.lookup('search-results-panel').getActiveTab().getSelectionModel().getSelection();
            selectedRecordsNumber = selection.length;
            if ( selectedRecordsNumber <= 0 ) {
                Ext.MessageBox.show({
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR,
                    msg: 'No record(s) selected to be rejected',
                    title: 'Error'
                });
            } else {
                var count = this.doStateUpdate('editing');
                Ext.Msg.confirm(
                    'Notify the curator?',
                    'Would you like to notify the curator about the new rejection of the selected element(s)?',
                    function (btn) {
                        if ( btn === 'yes' ) {
                            Ext.Msg.prompt(
                                'Message to the curator',
                                'Optional message to include in the rejection email',
                                function (btn, text) {
                                    if ( btn === 'ok' ) {
                                        Ext.Ajax.request({
                                            failure: function (response) {
                                                var errorMessage = 'Notification Error: ' + response.statusText;
                                                Ext.MessageBox.show({
                                                    buttons: Ext.MessageBox.OK,
                                                    icon: Ext.MessageBox.ERROR,
                                                    msg: errorMessage.replace(' ', '&nbsp;'),
                                                    title: 'Error'
                                                });
                                            },
                                            jsonData: {
                                                rejected_records_array: Ext.Array.reduce(
                                                    selection,
                                                    function (previous, value) {
                                                        return Ext.Array.push(
                                                            previous,
                                                            value.get('pubmed_id') + ',' + value.get('name')
                                                        );
                                                    },
                                                    []
                                                ),
                                                reasons: text,
                                                curator_ids_array: Ext.Array.unique(Ext.Array.reduce(
                                                    selection,
                                                    function (previous, value) {
                                                        return Ext.Array.push(
                                                            previous,
                                                            value.get('curator_id')
                                                        );
                                                    },
                                                    []
                                                ))
                                            },
                                            method: 'POST',
                                            success: function(response) {
                                                Ext.MessageBox.show({
                                                    buttons: Ext.MessageBox.OK,
                                                    icon: Ext.MessageBox.INFO,
                                                    msg: 'A rejection email has been sent to the curator',
                                                    title: 'Success'
                                                });
                                            },
                                            url: REDfly.Config.getApiV2Url() + '/audit/notify/rejections'
                                        });
                                    }
                                    Ext.MessageBox.show({
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.INFO,
                                        msg: count + ' record(s) rejected',
                                        title: 'Success'
                                    });
                                },
                                this,
                                true
                            );
                        } else {
                            Ext.MessageBox.show({
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.INFO,
                                msg: count + ' record(s) rejected',
                                title: 'Success'
                            });
                        }
                    },
                    this
                );
            }
        },
        onResetFormClicked: function (button) {
            var view = this.getViewModel();
            view.get('chromosomeStore').clearFilter(true);
            view.get('geneStore').clearFilter(true);
            view.get('transcriptionFactorStore').clearFilter(true);
            view.get('anatomicalExpressionStore').clearFilter(true);
            view.get('onDevelopmentalStageStore').clearFilter(true);
            view.get('offDevelopmentalStageStore').clearFilter(true);            
            view.get('rcNoTsStore').clearFilter(true);
            view.set('rcNoTsCount', '');
            view.get('rcTsStore').clearFilter(true);
            view.set('rcTsCount', '');
            view.get('tfbsStore').clearFilter(true);
            view.set('tfbsCount', '');
            view.get('crmSegmentStore').clearFilter(true);
            view.set('crmSegmentCount', '');
            view.get('crmSegmentNoTsStore').clearFilter(true);
            view.set('crmSegmentNoTsCount', '');
            view.get('crmSegmentTsStore').clearFilter(true);
            view.set('crmSegmentTsCount', '');
            view.get('predictedCrmStore').clearFilter(true);
            view.set('predictedCrmCount', '');
            view.get('predictedCrmNoTsStore').clearFilter(true);
            view.set('predictedCrmNoTsCount', '');
            view.get('predictedCrmTsStore').clearFilter(true);
            view.set('predictedCrmTsCount', '');
            if ( role === 'admin' ) {
                view.set('notifyAuthorsCheckBoxDisabled', false);
                view.set('approvalButtonDisabled', true);
                view.set('approveButtonDisabled', false);
                view.set('rejectButtonDisabled', false);
                view.set('deleteButtonDisabled', false);
            } else {
                view.set('approvalButtonDisabled', false);
                view.set('approveButtonDisabled', true);
                view.set('rejectButtonDisabled', true);
                view.set('deleteButtonDisabled', false);
            }
            button.up('form').reset();
            view.get('rcStore').clearFilter(true);
            view.get('rcStore').filter('state', predefinedState);
            view.set('rcCount', view.get('rcStore').getCount());
            // Implicitly reloading the Rc store
            this.lookup('search-results-panel').setActiveTab(0);
        },
        onSearchClicked: function() {
            var view = this.getViewModel();
            if ( (view.get('startTextFieldValue') !== null) &&
                (view.get('endTextFieldValue') !== null) &&
                (view.get('endTextFieldValue') < view.get('startTextFieldValue')) ) {
                Ext.MessageBox.show({
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR,
                    msg: 'Invalid coordinates',
                    title: 'Error'
                });
                return;
            }
            var reference = this.lookup('search-results-panel').getActiveTab().getReference();
            var tabStore = null;
            var countVariableName = null;
            var errorMargin = null;
            switch ( reference ) {
                case 'rc-list':
                    tabStore = view.get('rcStore');
                    countVariableName = 'rcCount';
                    errorMargin = rcErrorMargin;
                    break;
                case 'rc-no-ts-list':
                    tabStore = view.get('rcNoTsStore');
                    countVariableName = 'rcNoTsCount';
                    errorMargin = rcErrorMargin;
                    break;
                case 'rc-ts-list':
                    tabStore = view.get('rcTsStore');
                    countVariableName = 'rcTsCount';
                    errorMargin = rcErrorMargin;
                    break;
                case 'tfbs-list':
                    tabStore = view.get('tfbsStore');
                    countVariableName = 'tfbsCount';
                    errorMargin = tfbsErrorMargin;
                    break;
                case 'crm-segment-list':
                    tabStore = view.get('crmSegmentStore');
                    countVariableName = 'crmSegmentCount';
                    errorMargin = crmSegmentErrorMargin;
                    break;
                case 'crm-segment-no-ts-list':
                    tabStore = view.get('crmSegmentNoTsStore');
                    countVariableName = 'crmSegmentNoTsCount';
                    errorMargin = crmSegmentErrorMargin;
                    break;
                case 'crm-segment-ts-list':
                    tabStore = view.get('crmSegmentTsStore');
                    countVariableName = 'crmSegmentTsCount';
                    errorMargin = crmSegmentErrorMargin;
                    break;
                case 'predicted-crm-list':
                    tabStore = view.get('predictedCrmStore');
                    countVariableName = 'predictedCrmCount';
                    errorMargin = predictedCrmErrorMargin;
                    break;
                case 'predicted-crm-no-ts-list':
                    tabStore = view.get('predictedCrmNoTsStore');
                    countVariableName = 'predictedCrmNoTsCount';
                    errorMargin = predictedCrmErrorMargin;
                    break;
                case 'predicted-crm-ts-list':
                    tabStore = view.get('predictedCrmTsStore');
                    countVariableName = 'predictedCrmTsCount';
                    errorMargin = predictedCrmErrorMargin;
                    break;
            }
            if ( tabStore !== null ) {
                tabStore.clearFilter(true);
            }
            var startValue = 0;
            if ( view.get('startTextFieldValue') !== null ) {
                startValue = view.get('startTextFieldValue');
            }
            var endValue = 0;
            if ( view.get('endTextFieldValue') !== null ) {
                endValue = view.get('endTextFieldValue');
            }
            if ( (0 < startValue) &&
                (endValue === 0) ) {
                tabStore.filterBy(function(record) {
                    if ( ((startValue - errorMargin) <= record.get('start')) &&
                        (record.get('start') <= (startValue + errorMargin)) ) {
                        return true;
                    } else {
                        return false; 
                    }
                });
            } else {
                if ( (startValue === 0) &&
                    (0 < endValue) ) {
                    tabStore.filterBy(function(record) {
                        if ( ((endValue - errorMargin) <= record.get('end')) &&
                            (record.get('end') <= (endValue + errorMargin)) ) {
                            return true;
                        } else {
                            return false; 
                        }
                    });
                } else {
                    if ( (0 < startValue) &&
                        (0 < endValue) ) {
                        tabStore.filterBy(function(record) {
                            if ( ((startValue - errorMargin) <= record.get('start')) &&
                                (record.get('start') <= (startValue + errorMargin))  &&
                                ((endValue - errorMargin) <= record.get('end')) &&
                                (record.get('end') <= (endValue + errorMargin)) ) {
                                return true;
                            } else {
                                return false; 
                            }
                        });
                    }
                }
            }
            if ( view.get('sequenceFromSpeciesComboBoxValue') !== null ) {
                tabStore.filter('sequence_from_species_scientific_name', view.get('sequenceFromSpeciesComboBoxValue'));
            }
            if ( view.get('assayedInSpeciesComboBoxValue') !== null ) {
                tabStore.filter('assayed_in_species_scientific_name', view.get('assayedInSpeciesComboBoxValue'));
            }  
            if ( view.get('elementNameTextFieldValue') !== null ) {
                tabStore.filter('name', view.get('elementNameTextFieldValue'));
            }
            if ( view.get('curatorFullNameComboBoxValue') !== null ) {
                tabStore.filter('curator_full_name', view.get('curatorFullNameComboBoxValue'));
            }
            var state = '';
            if ( view.get('stateComboBoxValue') === null ) {
                state = predefinedState;
            } else {
                state = view.get('stateComboBoxValue');
            }
            if ( state !== '' ) {
                tabStore.filter('state', state);
                switch (state) {
                    case 'approval':
                        if ( role === 'admin' ) {
                            view.set('notifyAuthorsCheckBoxDisabled', false);
                            view.set('notifyAuthorsCheckBoxValue', false);
                            view.set('approvalButtonDisabled', true);
                            view.set('approveButtonDisabled', false);
                            view.set('rejectButtonDisabled', false);
                            view.set('deleteButtonDisabled', false);
                        } else {
                            view.set('notifyAuthorsCheckBoxDisabled', true);
                            view.set('notifyAuthorsCheckBoxValue', false);
                            view.set('approvalButtonDisabled', true);
                            view.set('approveButtonDisabled', true);
                            view.set('rejectButtonDisabled', true);
                            view.set('deleteButtonDisabled', false);
                        }
                        break;
                    case 'approved':
                        if ( role === 'admin' ) {
                            view.set('notifyAuthorsCheckBoxDisabled', true);
                            view.set('notifyAuthorsCheckBoxValue', false);
                            view.set('approvalButtonDisabled', true);
                            view.set('approveButtonDisabled', true);
                            view.set('rejectButtonDisabled', true);
                            view.set('deleteButtonDisabled', false);
                        } else {
                            view.set('notifyAuthorsCheckBoxDisabled', true);
                            view.set('notifyAuthorsCheckBoxValue', false);
                            view.set('approvalButtonDisabled', true);
                            view.set('approveButtonDisabled', true);
                            view.set('rejectButtonDisabled', true);
                            view.set('deleteButtonDisabled', false);
                        }                    
                        break;
                    case 'deleted':
                        if ( role === 'admin' ) {
                            view.set('notifyAuthorsCheckBoxDisabled', true);
                            view.set('notifyAuthorsCheckBoxValue', false);
                            view.set('approvalButtonDisabled', true);
                            view.set('approveButtonDisabled', true);
                            view.set('rejectButtonDisabled', false);
                            view.set('deleteButtonDisabled', true);
                        } else {
                            view.set('notifyAuthorsCheckBoxDisabled', true);
                            view.set('notifyAuthorsCheckBoxValue', false);
                            view.set('approvalButtonDisabled', true);
                            view.set('approveButtonDisabled', true);
                            view.set('rejectButtonDisabled', true);
                            view.set('deleteButtonDisabled', true);
                        }
                        break;                
                    case 'editing':
                        if ( role === 'admin' ) {
                            view.set('notifyAuthorsCheckBoxDisabled', true);
                            view.set('notifyAuthorsCheckBoxValue', false);
                            view.set('approvalButtonDisabled', false);
                            view.set('approveButtonDisabled', true);
                            view.set('rejectButtonDisabled', true);
                            view.set('deleteButtonDisabled', false);
                        } else {
                            view.set('notifyAuthorsCheckBoxDisabled', true);
                            view.set('notifyAuthorsCheckBoxValue', false);
                            view.set('approvalButtonDisabled', false);
                            view.set('approveButtonDisabled', true);
                            view.set('rejectButtonDisabled', true);
                            view.set('deleteButtonDisabled', false);
                        }
                        break;                
                }
            }
            if ( view.get('pmidTextFieldValue') !== null ) {
                tabStore.filter('pubmed_id', view.get('pmidTextFieldValue'));
            }
            if ( view.get('chromosomeComboBoxValue') !== null ) {
                tabStore.filter('chromosome_display', view.get('chromosomeComboBoxValue'));
            }
            if ( view.get('geneComboBoxValue') !== null ) {
                geneDisplay = view.get('geneComboBoxValue');
                switch ( reference ) {
                    // For the entity lists with gene data
                    case 'rc-list':
                    case 'rc-no-ts-list':
                    case 'rc-ts-list':
                    case 'crm-segment-list':
                    case 'crm-segment-no-ts-list':
                    case 'crm-segment-ts-list':
                    case 'tfbs-list':
                        tabStore.filter('gene_display', geneDisplay);
                        break;
                    // For the entity lists without any gene data
                    case 'predicted-crm-list':
                    case 'predicted-crm-no-ts-list':
                    case 'predicted-crm-ts-list':
                        tabStore.filter('id', '0');
                        break;
                }                
            }
            if ( view.get('transcriptionFactorComboBoxValue') !== null ) {
                transcriptionFactorDisplay = view.get('transcriptionFactorComboBoxValue');
                switch ( reference ) {
                    // For the entity lists with transcription factor data
                    case 'tfbs-list':
                        tabStore.filter('transcription_factor_display', transcriptionFactorDisplay);
                        break;                    
                    // For the entity lists without any transcription factor data
                    case 'rc-list':
                    case 'rc-no-ts-list':
                    case 'rc-ts-list':
                    case 'crm-segment-list':
                    case 'crm-segment-no-ts-list':
                    case 'crm-segment-ts-list':
                    case 'predicted-crm-list':
                    case 'predicted-crm-no-ts-list':
                    case 'predicted-crm-ts-list':
                        tabStore.filter('id', '0');
                        break;

                }
            }            
            if ( view.get('anatomicalExpressionComboBoxValue') !== null ) {
                anatomicalExpressionDisplay = view.get('anatomicalExpressionComboBoxValue');
                switch ( reference ) {
                    // For the entity lists with anatomical expressions                    
                    case 'rc-list':
                    case 'crm-segment-list':
                    case 'predicted-crm-list':
                        tabStore.filter('anatomical_expression_displays', new RegExp(Ext.String.escapeRegex(anatomicalExpressionDisplay)));
                        break;
                    // For the entity lists with anatomical expression data
                    case 'rc-no-ts-list':
                    case 'rc-ts-list':
                    case 'crm-segment-no-ts-list':
                    case 'crm-segment-ts-list':
                    case 'predicted-crm-no-ts-list':
                    case 'predicted-crm-ts-list':
                        tabStore.filter('anatomical_expression_display', anatomicalExpressionDisplay);
                        break;
                    // For the entity lists without any anatomical expression data
                    case 'tfbs-list':
                        tabStore.filter('id', '0');
                        break;
                }
            }
            if ( view.get('onDevelopmentalStageComboBoxValue') !== null ) {
                onDevelopmentalStageDisplay = view.get('onDevelopmentalStageComboBoxValue');
                switch ( reference ) {
                    // For the entity lists with on developmental stage data
                    case 'rc-ts-list':
                    case 'crm-segment-ts-list':
                    case 'predicted-crm-ts-list':
                        tabStore.filter('on_developmental_stage_display', onDevelopmentalStageDisplay);
                        break;
                    // For the entity lists without any on developmental stage data                        
                    case 'rc-list':
                    case 'rc-no-ts-list':
                    case 'crm-segment-list':
                    case 'crm-segment-no-ts-list':                        
                    case 'predicted-crm-list':
                    case 'predicted-crm-no-ts-list':
                        break;
                    case 'tfbs-list':                        
                        tabStore.filter('id', '0');
                        break;
                }
            }
            if ( view.get('offDevelopmentalStageComboBoxValue') !== null ) {
                offDevelopmentalStageDisplay = view.get('offDevelopmentalStageComboBoxValue');
                switch ( reference ) {
                    // For the entity lists with off developmental stage data
                    case 'rc-ts-list':
                    case 'crm-segment-ts-list':
                    case 'predicted-crm-ts-list':
                        tabStore.filter('off_developmental_stage_display', offDevelopmentalStageDisplay);
                        break;
                    // For the entity lists without any off developmental stage data
                    case 'rc-list':
                    case 'rc-no-ts-list':
                    case 'crm-segment-list':
                    case 'crm-segment-no-ts-list':                        
                    case 'predicted-crm-list':
                    case 'predicted-crm-no-ts-list':
                        break;
                    case 'tfbs-list':                        
                        tabStore.filter('id', '0');
                        break;
                }
            }
            if ( view.get('biologicalProcessComboBoxValue') !== null ) {
                biologicalProcessDisplay = view.get('biologicalProcessComboBoxValue');
                switch ( reference ) {
                    // For the entity lists with biological process data
                    case 'rc-ts-list':
                    case 'crm-segment-ts-list':
                    case 'predicted-crm-ts-list':
                        tabStore.filter('biological_process_display', biologicalProcessDisplay);
                        break;
                    // For the entity lists without any biological process data
                    case 'rc-list':
                    case 'rc-no-ts-list':
                    case 'tfbs-list':
                    case 'crm-segment-list':
                    case 'crm-segment-no-ts-list':                        
                    case 'predicted-crm-list':
                    case 'predicted-crm-no-ts-list':
                        tabStore.filter('id', '0');
                        break;
                }
            }
            this.getViewModel().set(countVariableName, tabStore.getCount());
        },
        onSequenceFromSpeciesSelected: function() {
            var view = this.getViewModel();
            if ( view.get('sequenceFromSpeciesComboBoxValue') !== null ) {
                sequenceFromSpeciesScientificName = view.get('sequenceFromSpeciesComboBoxValue');
                view.get('chromosomeStore').filter('species_scientific_name', sequenceFromSpeciesScientificName);
                view.get('geneStore').filter('species_scientific_name', sequenceFromSpeciesScientificName);
                view.get('transcriptionFactorStore').filter('species_scientific_name', sequenceFromSpeciesScientificName);
            }
        },
        onSubmitSelectedForApprovalClicked: function () {
            selectedRecordsNumber = this.lookup('search-results-panel').getActiveTab().getSelectionModel().getSelection().length;
            if ( selectedRecordsNumber <= 0 ) {
                Ext.MessageBox.show({
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR,
                    msg: 'No record(s) selected to be submitted for approval',
                    title: 'Error'
                });
            } else {
                Ext.MessageBox.show({
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.INFO,
                    msg: this.doStateUpdate('approval') + ' record(s) submitted for approval',
                    title: 'Success'
                });
            }
        },
        onTfbsLoad: function (store) {
            this.getViewModel().set('tfbsCount', store.getCount());
        }
    }
);