Ext.define(
    'REDfly.view.admin.audit.Audit',
    {
        controller: 'audit',
        extend: 'Ext.panel.Panel',
        items: [{
            border: false,
            padding: '10, 100, 10, 100',
            width: 1800,
            xtype: 'audit-form'
        }, {
            height: 400,
            items: [
                {
                    bind: {
                        title: 'RC/CRM ({rcCount})'
                    },
                    reference: 'rc-list',
                    xtype: 'audit-rc-list'
                }, {
                    bind: {
                        title: 'RC/CRM With No Staging ({rcNoTsCount})'
                    },
                    reference: 'rc-no-ts-list',
                    xtype: 'audit-rc-no-ts-list'
                }, {
                    bind: {
                        title: 'RC/CRM With Staging ({rcTsCount})'
                    },
                    reference: 'rc-ts-list',
                    xtype: 'audit-rc-ts-list'
                }, {
                    bind: {
                        title: 'TFBS ({tfbsCount})'
                    },
                    reference: 'tfbs-list',
                    xtype: 'audit-tfbs-list'
                }, {
                    bind: {
                        title: 'CRM Segment ({crmSegmentCount})'
                    },
                    reference: 'crm-segment-list',
                    xtype: 'audit-crm-segment-list'
                }, {
                    bind: {
                        title: 'CRM Segment With No Staging ({crmSegmentNoTsCount})'
                    },
                    reference: 'crm-segment-no-ts-list',
                    xtype: 'audit-crm-segment-no-ts-list'
                }, {
                    bind: {
                        title: 'CRM Segment With Staging ({crmSegmentTsCount})'
                    },
                    reference: 'crm-segment-ts-list',
                    xtype: 'audit-crm-segment-ts-list'
                }, {
                    bind: {
                        title: 'Predicted CRM ({predictedCrmCount})'
                    },
                    reference: 'predicted-crm-list',
                    xtype: 'audit-predicted-crm-list'
                }, {
                    bind: {
                        title: 'Predicted CRM With No Staging ({predictedCrmNoTsCount})'
                    },
                    reference: 'predicted-crm-no-ts-list',
                    xtype: 'audit-predicted-crm-no-ts-list'
                }, {
                    bind: {
                        title: 'Predicted CRM With Staging ({predictedCrmTsCount})'
                    },
                    reference: 'predicted-crm-ts-list',
                    xtype: 'audit-predicted-crm-ts-list'
                }
            ],
            listeners: {
                beforetabchange: 'onBeforeTabChange'
            },
            padding: '10, 10, 10, 10',
            reference: 'search-results-panel',
            width: 1800,
            xtype: 'tabpanel'
        }],
        requires: [
            'REDfly.view.admin.audit.AuditController',
            'REDfly.view.admin.audit.AuditViewModel',
            'REDfly.view.admin.audit.CrmSegmentList',
            'REDfly.view.admin.audit.CrmSegmentNoTsList',
            'REDfly.view.admin.audit.CrmSegmentTsList',
            'REDfly.view.admin.audit.Form',
            'REDfly.view.admin.audit.RcList',
            'REDfly.view.admin.audit.RcNoTsList',
            'REDfly.view.admin.audit.RcTsList',
            'REDfly.view.admin.audit.PredictedCrmList',
            'REDfly.view.admin.audit.PredictedCrmNoTsList',
            'REDfly.view.admin.audit.PredictedCrmTsList',
            'REDfly.view.admin.audit.TfbsList'
        ],    
        viewModel: {
            type: 'audit'
        },
        xtype: 'audit'
    }
);