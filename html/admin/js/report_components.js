// --------------------------------------------------------------------------------
// Report Download window
// --------------------------------------------------------------------------------
REDfly.component.ReportList = Ext.extend(Ext.Window, {
    autoShow: true,
    buttonAlign: 'center',
    layout: 'fit',
    modal: true,
    title: 'Download Reports',
    initComponent: function() {
        this.width = Ext.min([Ext.getBody().getViewSize().width, 800]);
        this.height = Ext.min([Ext.getBody().getViewSize().height, 600]);
        var reportStore = new Ext.data.JsonStore({
            autoLoad: true,
            fields: [
                'date',
                'file',
                'name',
                'time',
                'type'
            ],
            proxy: new Ext.data.HttpProxy({
                method: 'POST',
                url: REDfly.config.apiUrl + '/jsonstore/report/list'
            }),
            root: 'results'
        });
        var selModel = new Ext.grid.RowSelectionModel({ singleSelect: true });
        var grid = new Ext.grid.GridPanel({
            colModel: new Ext.grid.ColumnModel({
                columns: [
                    {
                        dataIndex: 'name',
                        header: 'Name',
                        width: 120
                    }, {
                        dataIndex: 'type',
                        header: 'Type',
                        width: 120
                    }, {
                        dataIndex: 'date',
                        header: 'Date',
                        width: 120
                    }, {
                        dataIndex: 'time',
                        header: 'Time',
                        width: 120
                    }
                ]
            }),
            listeners: {
                rowdblclick: {
                    fn: function(grid, rowIndex) {
                        this.download(reportStore.getAt(rowIndex).get('file'));
                    },
                    scope: this
                }
            },
            selModel: selModel,
            store: reportStore,
            viewConfig: {
                autoFill: true
            }
        });
        Ext.apply(this, {
            buttons: [
                {
                    handler: function() {
                        var report = this.selModel.getSelected();
                        if ( ! Ext.isEmpty(report) ) {
                            this.download(report.get('file'));
                        }
                    },
                    scope: this,
                    text: 'Download Selected'
                }
            ],
            items: [grid],
            selModel: selModel
        });
        REDfly.component.ReportList.superclass.initComponent.apply(this, arguments);
        this.show();
    },
    // --------------------------------------------------------------------------------
    // Download a report file.
    // --------------------------------------------------------------------------------
    download: function(filename) {
        var url = REDfly.config.apiUrl + '/raw/report/download?file=' + encodeURIComponent(filename);
        window.location = url;
    }
});