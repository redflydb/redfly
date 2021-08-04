Ext.define(
    'REDfly.view.admin.import.ImportController',
    {
        alias: 'controller.import',
        extend: 'Ext.app.ViewController',
        onFormChange: function () {
            // Preventing a loophole when the curator hits the Esc key during any file upload
            if (this.getViewModel().get('attributeTsvFilePath') === '') {
                this.getViewModel().set('attributeTsvFilePath', 'no attribute TSV file selected');
            }
            if (this.getViewModel().get('fastaFilePath') === '') {
                this.getViewModel().set('fastaFilePath', 'no FASTA file selected');
            }
            if (this.getViewModel().get('expressionTsvFilePath') === '') {
                this.getViewModel().set('expressionTsvFilePath', 'no expression TSV file selected');
            }
            if (this.getViewModel().get('entityTypeValue') === '') {
                var entityType = 'rc';
            } else{
                var entityType = this.getViewModel().get('entityTypeValue');
            }
            var attributeData = (this.getViewModel().get('attributeTsvFilePath') !== 'no attribute TSV file selected');
            var fastaData = (this.getViewModel().get('fastaFilePath') !== 'no FASTA file selected');
            var expressionData = (this.getViewModel().get('expressionTsvFilePath') !== 'no expression TSV file selected');
            switch (entityType) {
                case 'predicted_crm':
                    this.getViewModel().set('updateExpressionsCheckboxDisabled', true);
                    break;
                case 'rc':
                    if ((! attributeData) && (! fastaData) && expressionData) {
                        this.getViewModel().set('updateExpressionsCheckboxDisabled', false);
                    } else {
                        this.getViewModel().set('updateExpressionsCheckboxDisabled', true);
                    }
                    break;
                default:
                    this.getViewModel().set('updateExpressionsCheckboxDisabled', true);
            }
            var updateExpressionsCheckboxCheckedOn = this.getViewModel().get('updateExpressions');
            switch (entityType) {
                case 'predicted_crm':
                    if (attributeData && fastaData && expressionData) {
                        this.getViewModel().set('validateButtonDisabled', false);
                    } else {
                        this.getViewModel().set('validateButtonDisabled', true);
                    } 
                    break;                    
                case 'rc':
                    if ((attributeData && fastaData && expressionData) || 
                        ((! attributeData) && (! fastaData) && expressionData && updateExpressionsCheckboxCheckedOn)) {
                        this.getViewModel().set('validateButtonDisabled', false);
                    } else {
                        this.getViewModel().set('validateButtonDisabled', true);
                    }
                    break;                    
                default:
                    this.getViewModel().set('validateButtonDisabled', true);
            }
            this.getViewModel().set('importButtonDisabled', true);
        },
        onImportButtonClicked: function () {
            this.doImport();
        },
        doImport: function () {
            var form = this.getView().down('form').getForm();
            if ( form.isValid() ) {
                form.submit({
                    failure: function (form, action) {
                        Ext.Msg.alert('Error', action.result.error);
                    },
                    scope: this,
                    success: function(form, action) {
                        this.getViewModel().set('importButtonDisabled', true);
                        Ext.Msg.alert('Success', 'Import successful. ' +
                            'Use the Batch Audit tool to check the entries ' + 
                            'imported with their new state set as "editing".');
                    },
                    url: REDfly.Config.getApiV2Url() + '/batch/import',
                    waitMsg: 'Importing...'
                });
            }
        },
        doValidate: function () {
            var form = this.getView().down('form').getForm();
            if ( form.isValid() ) {
                form.submit({
                    failure: function (form, action) {
                        Ext.Msg.alert('Error', action.result.error);
                    },
                    scope: this,
                    success: function(form, action) {
                        var store = this.lookup('error-list').getStore();
                        store.loadRawData(action.response);
                        this.getViewModel().set('validateButtonDisabled', (store.getTotalCount() === 0));
                        this.getViewModel().set('importButtonDisabled', (store.getTotalCount() > 0));
                        if (store.getTotalCount() === 0) {
                            Ext.Msg.alert('Success', 'All the uploaded data have been validated. ' +
                                'Click the Import button to import all the validated data.');
                        }
                    },
                    url: REDfly.Config.getApiV2Url() + '/batch/validate',
                    waitMsg: 'Validating...'
                });
            }
        },
        onValidateButtonClicked: function () {
            this.doValidate();
        }
    }
);