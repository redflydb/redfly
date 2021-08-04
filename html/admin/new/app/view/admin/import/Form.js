Ext.define(
    'REDfly.view.admin.import.Form',
    {
        buttons: [{
            bind: {
                disabled: '{validateButtonDisabled}'
            },
            handler: 'onValidateButtonClicked',
            text: 'Validate'
        }, {
            bind: {
                disabled: '{importButtonDisabled}'
            },
            handler: 'onImportButtonClicked',
            text: 'Import'
        }],
        extend: 'Ext.form.Panel',
        items: [{
            border: false,
            columnWidth: 0.5,
            items: [{
                allowBlank: false,
                bind: '{entityTypeValue}',
                columns: 1,
                items: [{
                    boxLabel: 'RC/CRM',
                    bubbleEvents: ['change'],
                    checked: true,
                    inputValue: 'rc'
                }, {
                    boxLabel: 'TFBS',
                    bubbleEvents: ['change'],
                    disabled: true,
                    inputValue: 'tfbs'
                }, {
                    boxLabel: 'Predicted CRM',
                    bubbleEvents: ['change'],
                    inputValue: 'predicted_crm'
                }],
                listeners: {
                    change: 'onFormChange'
                },
                name: 'entityType',
                simpleValue: true,
                vertical: true,
                xtype: 'radiogroup'
            }],
            title: 'Entity Type',
            xtype: 'fieldset'
        }, 
        {
            border: false,
            columnWidth: 1,
            items: [{
                items: [{
                    allowBlank: true,
                    bind: '{attributeTsvFilePath}',
                    buttonConfig: {
                        width: 150
                    },
                    buttonOnly: true,
                    buttonText: 'Choose Attribute TSV',
                    clearOnSubmit: false,
                    listeners: {
                        blur: 'onFormChange'
                    },
                    name: 'attributeTsv',
                    xtype: 'filefield'
                }, {
                    bind: '{attributeTsvFilePath}',
                    renderer: function (value) {
                        return value ? value.split('\\').pop() : value;
                    },
                    xtype: 'displayfield'
                }],
                layout: 'hbox',
                xtype: 'fieldcontainer'
            }, 
            {
                items: [{
                    allowBlank: true,
                    bind: '{fastaFilePath}',
                    buttonOnly: true,
                    buttonConfig: {
                        width: 150
                    },
                    buttonText: 'Choose FASTA',
                    clearOnSubmit: false,
                    listeners: {
                        blur: 'onFormChange' 
                    },
                    name: 'fasta',
                    xtype: 'filefield'
                }, {
                    bind: '{fastaFilePath}',
                    renderer: function (value) {
                        return value ? value.split('\\').pop() : value;
                    },
                    xtype: 'displayfield'                    
                }],
                layout: 'hbox',
                xtype: 'fieldcontainer'
            },
            {
                items: [{
                    allowBlank: true,
                    bind: '{expressionTsvFilePath}',
                    buttonOnly: true,
                    buttonConfig: {
                        width: 150
                    },
                    buttonText: 'Choose Expression TSV',
                    clearOnSubmit: false,
                    listeners: {
                        blur: 'onFormChange' 
                    },
                    name: 'expressionTsv',
                    xtype: 'filefield'
                }, {
                    bind: '{expressionTsvFilePath}',
                    renderer: function (value) {
                        return value ? value.split('\\').pop() : value;
                    },
                    xtype: 'displayfield'
                }],
                layout: 'hbox',
                xtype: 'fieldcontainer'
            },
            {
                defaultType: 'checkboxfield',
                fieldLabel: '',
                items: [{
                    bind: {
                        disabled: '{updateExpressionsCheckboxDisabled}',
                        value: '{updateExpressions}'
                    },
                    boxLabel: 'Update Expression Data For Existing Reporter Construct(s)',
                    name: 'updateExpressions',
                    id: 'checkbox1',
                    inputValue: '1',
                    listeners: {
                        blur: 'onFormChange' 
                    }
                }],
                layout: 'hbox',
                xtype: 'fieldcontainer'
            }],
            title: 'File Upload',
            xtype: 'fieldset'
        }],
        layout: 'column',
        xtype: 'import-form'
    }
);