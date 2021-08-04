Ext.define(
    'REDfly.view.admin.import.ImportViewModel',
    {
        alias: 'viewmodel.import',
        extend: 'Ext.app.ViewModel',
        data: {
            attributeTsvFilePath: 'no attribute TSV file selected',
            entityTypeValue: '',
            expressionTsvFilePath: 'no expression TSV file selected',
            fastaFilePath: 'no FASTA file selected',
            importButtonDisabled: true,
            updateExpressionsCheckboxDisabled: true,
            validateButtonDisabled: true
        }
    }
);