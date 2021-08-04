// Initialize the templates used throughout the user interface
REDfly.templates = {
    // Urls for FlyBase identifiers, GO identifiers, PubMed identifiers, and
    // REDfly identifiers in the 'Cite' and 'Expr' tabs of the entity window
    flybase_identifier_url: new Ext.Template('<font color="blue"><b><u><a href="{url}id={flybase_id}" ' +
        'target="_blank">{text}</a></u></b></font>'),
    go_identifier_url: new Ext.Template('<font color="blue"><b><u><a href="{url}{go_id}" ' +
        'target="_blank">{text}</a></u></b></font>'),
    pubmed_identifier_url: new Ext.Template('<font color="blue"><u><a href="{url}{pubmed_id}" ' +
        'target="_blank">{text}</a></u></font>'),
    redfly_anatomical_expression_identifier_url: new Ext.Template('<font color="blue"><b><u><a href="{url}?anatomical_expression_term={flybase_id}" ' +
        'target="_blank">{text}</a></u></b></font>'),
    // Result tab titles
    crm_rc_tab_results_title: new Ext.Template('CRM ({number_of_crms_search_result}/{number_of_database_crms}) / ' + 
        'RC ({number_of_rcs_search_result}/{number_of_database_rcs}) ' +
        '<a style="white-space: nowrap;" href="#" onclick="numHelp();return false;">?</a>'),
    crmsegment_tab_results_title: new Ext.Template('CRM Segment ({number_of_crmsegments_search_result})'),
    predictedcrm_tab_results_title: new Ext.Template('Predicted CRM ({number_of_pcrms_search_result})'),
    tfbs_tab_results_title: new Ext.Template('TFBS ({number_of_tfbss_search_result})'),
    inferredcrm_tab_results_title: new Ext.Template('Inferred CRM ({number_of_icrms_search_result})')
};
// Initialize the configuration object
REDfly.config = {
    // Saved search parameters independent from the page size
    crmSearchParameters: {
        is_crm: true
    },
    // Page size for all the paginated content
    pageSize: 500,
    // Saved search parameters dependent from the page size
    // so they can be passed when switching pages.
    rcSearchParameters: null,
    crmsegmentSearchParameters: null,
    predictedCrmSearchParameters: null,
    tfbsSearchParameters: null,
    inferredCrmSearchParameters: null,
    // Width and height of the entity windows
    windowWidth: 400,
    windowHeight: 350,
    // Width of the search panel (used for entity window layouot)
    searchPanelWidth: 650,
    windowAreaMaxWidth: document.body.clientWidth,
    // (x, y) offset for the next window if not tiling
    nextWindowOffset: Array(20, 20),
    // (x, y) coordinates of the top left of the window area (to the right of the search
    // panel). These are initialized below once the search panel has been rendered.
    windowAreaStartCoordinates: new Array(0, 0),
    // (x, y) coodinates for the next window to be placed in the window area. These are
    // initialized below once the search panel has been rendered.
    nextWindowCoordinates: new Array(0, 0),
    // Max line width for sequences before wrapping the line
    sequenceLineLength: 45,
    // Various external URLs
    urls: {
        flybase: 'http://flybase.org/reports/',
        flybase_cv_term_report: 'http://flybase.org/cgi-bin/cvreport.pl?rel=is_a&',
        flybase_images: 'http://flybase.org/cgi-bin/gbrowse_img/dmel/',
        gbrowse: 'http://flybase.org/cgi-bin/gbrowse2/dmel/',
        go: 'http://amigo.geneontology.org/amigo/term/',
        pubmed: 'http://www.ncbi.nlm.nih.gov/pubmed/',
        redfly: redflyBaseUrl + 'search.php',
        redflyApi: redflyApiUrl,
        ucsc: 'http://genome.ucsc.edu/cgi-bin/hgTracks'
    },
    // Default settings for the results tabs.
    resultTabDefaults: {
        'tab-crm-rc': {
            title: 'CRM/RC',
            loadingTitle: '<b>LOADING...</b>',
            results: REDfly.templates.crm_rc_tab_results_title
        },
        'tab-crmsegment': {
            title: 'CRM Segment',
            loadingTitle: '<b>LOADING...</b>',
            results: REDfly.templates.crmsegment_tab_results_title
        },
        'tab-pcrm': {
            title: 'Predicted CRM',
            loadingTitle: '<b>LOADING...</b>',
            results: REDfly.templates.predictedcrm_tab_results_title
        },            
        'tab-tfbs': {
            title: 'TFBS',
            loadingTitle: '<b>LOADING...</b>',
            results:  REDfly.templates.tfbs_tab_results_title
        },
        'tab-icrm': {
            title: 'Inferred CRM',
            loadingTitle: '<b>LOADING...</b>',
            results: REDfly.templates.inferredcrm_tab_results_title
        }
    }
}
// Initialize the state object.
REDfly.state = {
    // Tile or stack windows. Controlled by the "tile windows" toggle button.
    tileWindows: false,
    // Window manager for all of the window instances
    windowGroup: new Ext.WindowGroup(),
    // Keep track of the redfly id of last entity that was selected.
    lastSelectedReporterConstruct: null,
    lastSelectedCrmSegment: null,
    lastSelectedPredictedCrm: null,
    lastSelectedTranscriptionFactorBindingSite: null,
    lastSelectedInferredCrm: null
};
// Set up the singleton download windows.
// These normally differ only by the title.
REDfly.window.downloadSingleRc = new REDfly.window.download({
    downloadAllItems: false,
    downloadUrlTemplate: new Ext.Template('/raw/download/reporterconstruct?' +
        'format={file_type}'),
    title: 'Download RC'
});
REDfly.window.downloadSingleCrmSegment = new REDfly.window.download({
    downloadAllItems: false,    
    downloadUrlTemplate: new Ext.Template('/raw/download/crmsegment?' +
        'format={file_type}'),
    title: 'Download CRM Segment'
});
REDfly.window.downloadSinglePredictedCrm = new REDfly.window.download({
    downloadAllItems: false,    
    downloadUrlTemplate: new Ext.Template('/raw/download/predictedcrm?' +
        'format={file_type}'),
    title: 'Download Predicted CRM'
});
REDfly.window.downloadSingleTfbs = new REDfly.window.download({
    downloadAllItems: false,    
    downloadUrlTemplate: new Ext.Template('/raw/download/transcriptionfactorbindingsite?' +
        'format={file_type}'),
    title: 'Download TFBS'
});
REDfly.window.downloadSelectEntries = new REDfly.window.download({
    downloadAllItems: false,    
    downloadUrlTemplate: new Ext.Template('/raw/download/list?' +
        'format={file_type}'),
    title: 'Download Selected Entries'
});
