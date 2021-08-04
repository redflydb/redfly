Ext.define(
    'REDfly.view.admin.audit.AuditViewModel',
    {
        alias: 'viewmodel.audit',
        extend: 'Ext.app.ViewModel',
        requires: [
            'REDfly.store.dynamic.Chromosome',
            'REDfly.store.dynamic.Gene',
            'REDfly.store.dynamic.TranscriptionFactor',
            'REDfly.store.dynamic.AnatomicalExpression',
            'REDfly.store.dynamic.OnDevelopmentalStage',
            'REDfly.store.dynamic.OffDevelopmentalStage',
            'REDfly.store.dynamic.BiologicalProcess',
            'REDfly.store.audit.Rc',
            'REDfly.store.audit.RcNoTs',
            'REDfly.store.audit.RcTs',
            'REDfly.store.audit.Tfbs',
            'REDfly.store.audit.CrmSegment',
            'REDfly.store.audit.CrmSegmentNoTs',
            'REDfly.store.audit.CrmSegmentTs',
            'REDfly.store.audit.PredictedCrm',
            'REDfly.store.audit.PredictedCrmNoTs',
            'REDfly.store.audit.PredictedCrmTs'
        ],
        stores: {
            chromosomeStore: {
                type: 'dynamic-chromosome'
            },
            geneStore: {
                type: 'dynamic-gene'
            },
            transcriptionFactorStore: {
                type: 'dynamic-transcription-factor'
            },            
            anatomicalExpressionStore: {
                type: 'dynamic-anatomical-expression'
            },
            onDevelopmentalStageStore: {
                type: 'dynamic-on-developmental-stage'
            },
            offDevelopmentalStageStore: {
                type: 'dynamic-off-developmental-stage'
            },
            biologicalProcessStore: {
                type: 'dynamic-biological-process'
            },            
            rcStore: {
                autoLoad: true,
                filters: [{
                    property: 'state',
                    value: predefinedState
                }],
                listeners: {
                    load: 'onRcLoad'
                },
                type: 'audit-rc'
            },
            rcNoTsStore: {
                listeners: {
                    load: 'onRcNoTsLoad'
                },
                type: 'audit-rc-no-ts'
            },
            rcTsStore: {
                listeners: {
                    load: 'onRcTsLoad'
                },
                type: 'audit-rc-ts'
            },
            tfbsStore: {
                listeners: {
                    load: 'onTfbsLoad'
                },
                type: 'audit-tfbs'
            },
            crmSegmentStore: {
                listeners: {
                    load: 'onCrmSegmentLoad'
                },
                type: 'audit-crm-segment'
            },
            crmSegmentNoTsStore: {
                listeners: {
                    load: 'onCrmSegmentNoTsLoad'
                },
                type: 'audit-crm-segment-no-ts'
            },
            crmSegmentTsStore: {
                listeners: {
                    load: 'onCrmSegmentTsLoad'
                },
                type: 'audit-crm-segment-ts'
            },
            predictedCrmStore: {
                listeners: {
                    load: 'onPredictedCrmLoad'
                },
                type: 'audit-predicted-crm'
            },
            predictedCrmNoTsStore: {
                listeners: {
                    load: 'onPredictedCrmNoTsLoad'
                },
                type: 'audit-predicted-crm-no-ts'
            },
            predictedCrmTsStore: {
                listeners: {
                    load: 'onPredictedCrmTsLoad'
                },
                type: 'audit-predicted-crm-ts'
            }
        },
        data: {
            approvalButtonDisabled: adminRole,
            approveButtonDisabled: !adminRole,
            crmSegmentCount: '',
            crmSegmentNoTsCount: '',
            crmSegmentTsCount: '',
            deleteButtonDisabled: false,
            notifyAuthorsCheckBox: false,
            notifyAuthorsCheckBoxDisabled: !adminRole,
            predictedCrmCount: '',
            predictedCrmNoTsCount: '',
            predictedCrmTsCount: '',
            rcCount: '',
            rcNoTsCount: '',
            rcTsCount: '',
            rejectButtonDisabled: !adminRole,
            tfbsCount: ''        }
    }
);