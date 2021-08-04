Ext.define(
    'REDfly.view.admin.audit.Form',
    {
        buttonAlign: 'center',
        extend: 'Ext.form.Panel',
        fbar: [{
            handler: 'onSearchClicked',
            text: 'Search',
            xtype: 'button'
        }, {
            bind: {
                disabled: '{approvalButtonDisabled}'
            },
            handler: 'onSubmitSelectedForApprovalClicked',
            text: 'Submit Selected for Approval',
            xtype: 'button'
        }, {
            bind: {
                disabled: '{notifyAuthorsCheckBoxDisabled}',
                value: '{notifyAuthorsCheckBoxValue}'
            },
            boxLabel: 'Notify paper authors',
            xtype: 'checkbox'
        }, {
            bind: {
                disabled: '{approveButtonDisabled}'
            },
            handler: 'onApproveSelectedClicked',
            text: 'Approve Selected',
            xtype: 'button'
        }, {
            bind: {
                disabled: '{rejectButtonDisabled}'
            },
            handler: 'onRejectSelectedClicked',
            text: 'Reject Selected',
            xtype: 'button'
        }, {
            bind: {
                disabled: '{deleteButtonDisabled}'
            },
            handler: 'onDeleteSelectedClicked',
            text: 'Mark Selected for Deletion',
            xtype: 'button'
        }, {
            handler: 'onResetFormClicked',
            text: 'Reset Form',
            xtype: 'button'
        }],
        items: [{
            border: false,
            columnWidth: 0.15,
            items: [{
                bind: {
                    value: '{sequenceFromSpeciesComboBoxValue}'
                },
                displayField: 'display',
                editable: false,
                fieldLabel: '"Sequence From" Species',
                forceSelection: true,
                labelAlign: 'top',
                listeners: {
                    'select': 'onSequenceFromSpeciesSelected'
                },
                queryMode: 'remote',
                store: {
                    type: 'dynamic-species'
                },
                valueField: 'scientific_name',
                width: 200,
                xtype: 'combobox'
            }, {
                bind: {
                    value: '{assayedInSpeciesComboBoxValue}'
                },
                displayField: 'display',
                editable: false,
                fieldLabel: '"Assayed In" Species (not applied to predicted CRMs)',
                forceSelection: true,
                labelAlign: 'top',
                listeners: {
                    'select': 'onAssayedInSpeciesSelected'
                },
                queryMode: 'remote',
                store: {
                    type: 'dynamic-species'
                },
                valueField: 'scientific_name',
                width: 200,
                xtype: 'combobox'
            }, {            
                bind: {
                    value: '{elementNameTextFieldValue}'
                },
                checkChangeBuffer: 750,
                fieldLabel: 'Element Name',
                labelAlign: 'top',
                minChars: 1,
                xtype: 'textfield'
            }, {
                bind: {
                    value: '{curatorFullNameComboBoxValue}'
                },
                displayField: 'full_name',
                fieldLabel: 'Curator',
                forceSelection: true,
                minChars: 1,
                labelAlign: 'top',
                queryMode: 'remote',
                store: {
                    type: 'dynamic-curator'
                },
                valueField: 'full_name',
                xtype: 'combobox'
            }, ]
        }, {
            border: false,
            columnWidth: 0.185,
            items: [{
                bind: {
                    value: '{stateComboBoxValue}'
                },
                displayField: 'display',
                editable: false,
                fieldLabel: 'State',
                forceSelection: true,
                labelAlign: 'top',
                queryMode: 'local',
                store: {
                    type: 'dynamic-state'
                },
                value: predefinedState,
                valueField: 'state',
                xtype: 'combobox'
            }, {
                allowDecimals: false,
                allowExponential: false,
                bind: {
                    value: '{pmidTextFieldValue}'
                },
                checkChangeBuffer: 750,
                fieldLabel: 'PMID',
                hideTrigger: true,
                keyNavEnabled: false,
                labelAlign: 'top',
                minValue: 0,
                mouseWheelEnabled: false,
                xtype: 'numberfield'
            }, {
                bind: {
                    store: '{geneStore}',
                    value: '{geneComboBoxValue}'
                },
                displayField: 'display',
                fieldLabel: 'Gene (not applied to predicted CRMs)',
                forceSelection: true,
                minChars: 1,             
                labelAlign: 'top',
                queryMode: 'remote',                
                valueField: 'display',
                width: 250,
                xtype: 'combobox'
            }, {
                bind: {
                    store: '{transcriptionFactorStore}',
                    value: '{transcriptionFactorComboBoxValue}'
                },
                displayField: 'display',
                fieldLabel: 'Transcription Factor (only applied to TFBSs)',
                forceSelection: true,
                minChars: 1,             
                labelAlign: 'top',
                queryMode: 'remote',                
                valueField: 'display',
                width: 250,
                xtype: 'combobox'
            }]
        }, {
            border: false,
            columnWidth: 0.345,
            items: [{
                fieldLabel: 'Chromsome',
                items: [{
                    bind: {
                        store: '{chromosomeStore}',
                        value: '{chromosomeComboBoxValue}'
                    },
                    displayField: 'display',
                    forceSelection: true,
                    minChars: 1,
                    queryMode: 'remote',
                    valueField: 'display',
                    width: 180,
                    xtype: 'combobox'
                }, {
                    value: ':',
                    xtype: 'displayfield'
                }, {
                    allowDecimals: false,
                    allowExponential: false,
                    bind: {
                        value: '{startTextFieldValue}'
                    },
                    checkChangeBuffer: 750,
                    hideTrigger: true,
                    keyNavEnabled: false,
                    minValue: 0,
                    mouseWheelEnabled: false,
                    xtype: 'numberfield'
                }, {
                    value: '..',
                    xtype: 'displayfield'
                }, {
                    allowDecimals: false,
                    allowExponential: false,
                    bind: {
                        value: '{endTextFieldValue}'
                    },
                    checkChangeBuffer: 750,
                    hideTrigger: true,
                    keyNavEnabled: false,
                    minValue: 0,
                    mouseWheelEnabled: false,
                    xtype: 'numberfield'
                }],
                labelAlign: 'top',
                layout: 'hbox',
                xtype: 'fieldcontainer'
            }]
        }, {
            border: false,
            columnWidth: 0.35,
            items: [{
                bind: {
                    store: '{anatomicalExpressionStore}',
                    value: '{anatomicalExpressionComboBoxValue}'
                },
                displayField: 'display',
                fieldLabel: 'Anatomical Expression (not applied to TFBSs)',
                forceSelection: true,
                labelAlign: 'top',
                minChars: 1,
                queryMode: 'remote',
                valueField: 'display',
                width: 450,
                xtype: 'combobox'
            }, {
                bind: {
                    store: '{onDevelopmentalStageStore}',
                    value: '{onDevelopmentalStageComboBoxValue}'
                },
                displayField: 'display',
                fieldLabel: '"On" Developmental Stage (not applied to TFBSs)',
                forceSelection: true,
                labelAlign: 'top',
                minChars: 1,
                queryMode: 'remote',
                valueField: 'display',
                width: 450,
                xtype: 'combobox'
            }, {
                bind: {
                    store: '{offDevelopmentalStageStore}',
                    value: '{offDevelopmentalStageComboBoxValue}'
                },
                displayField: 'display',
                fieldLabel: '"Off" Developmental Stage (not applied to TFBSs)',
                forceSelection: true,
                labelAlign: 'top',
                minChars: 1,
                queryMode: 'remote',
                valueField: 'display',
                width: 450,
                xtype: 'combobox'
            }, {
                bind: {
                    store: '{biologicalProcessStore}',
                    value: '{biologicalProcessComboBoxValue}'
                },
                displayField: 'display',
                fieldLabel: 'Biological Process (not applied to TFBSs)',
                forceSelection: true,
                labelAlign: 'top',
                minChars: 1,
                queryMode: 'remote',
                valueField: 'display',
                width: 450,
                xtype: 'combobox'
            }]
        }],
        layout: 'column',
        requires: [
            'REDfly.store.dynamic.AnatomicalExpression',
            'REDfly.store.dynamic.BiologicalProcess',
            'REDfly.store.dynamic.Chromosome',
            'REDfly.store.dynamic.Curator',
            'REDfly.store.dynamic.Gene',
            'REDfly.store.dynamic.OffDevelopmentalStage',
            'REDfly.store.dynamic.OnDevelopmentalStage',
            'REDfly.store.dynamic.Species',
            'REDfly.store.dynamic.State'
        ],
        xtype: 'audit-form'
    }
);