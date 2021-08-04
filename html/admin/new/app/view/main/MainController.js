Ext.define(
    'REDfly.view.main.MainController',
    {
        alias: 'controller.main',
        extend: 'Ext.app.ViewController',
        onAudit: function() {
            this.getView().add({
                title: 'Batch Audit',
                xtype: 'audit'
            })
        },
        onImport: function () {
            this.getView().add({
                title: 'Batch Import',
                xtype: 'import'
            });
        },
        requires: [
            'REDfly.view.admin.audit.Audit',
            'REDfly.view.admin.import.Import'
        ],
        routes: {
            'audit': 'onAudit',
            'import': 'onImport'
        }
    }
);