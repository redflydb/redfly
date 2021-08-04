function allGenesHelp() {
    Ext.MessageBox.alert(
        'Help',
        'When searching for a gene name/identifier, restricts the search to the target gene and/or ' +
            'the transcription factor gene. ' + 
            'All the target gene results are restricted by the current species shown ' +
            'in the \'"Sequence From" Species\' field'
    );
}
function anatomicalExpressionTermHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Use to search by anatomical expression pattern. ' +
            'The anatomical expression term and its descendant terms are restricted by ' +
            'the current species shown in the \'"Sequence From" Species\' field'
    );
}
function assayedInSpeciesHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Use this field to select the species in which the assay was performed. Not applied on predicted CRMs'
    );
}
function biologicalProcessTermHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Use to search by biological process pattern. ' +
        'It is not applied to transcription factor binding sites and inferred CRMs'
    );
}
function coordinatesHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Restrict the search to any entity lying within the specified sequence range (-/+) ' +
            'the error margin, 5 bp (not applied to TFBS), using the coordinates in ' +
            'the format \'Chromosome:Start Coord. .. End Coord.\''
    );
}
function crmHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Restrict the search to the reporter constructs that are designated as CRMs'
    );
}
function crmWithTfbsHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Restrict the search to the CRMs containing annotated TFBSs'
    );
}
function dateAddedHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Use this field to search for the entries that were added on or after this date. ' +
        'It is not applied to inferred CRMs'
    );
}
function developmentalStageTermHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Use to search by developmental stage pattern. ' +
            'The developmental stage term and its descendant terms are restricted by ' +
            'the current species shown in the \'"Assayed In" Species\' field. ' +
            'It is not applied to transcription factor binding sites and inferred CRMs'
    );
}
function elementNameorFBtpIdentifierHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Use this field to search using the name of a specific REDfly element ' +
            '(e.g., eve_stripe2) or the FlyBase transgenic construct ID (e.g. FBtp0004177). ' +
            'A wild-card is automatically appended to both start and end of the search ' +
            'string. FlyBase transgenic construct searches are applied only to reporter ' +
            'constructs and CRM segments'
    );
}
function evidenceHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Restrict the search to the sequences supported by only certain types of evidence, ' + 
            'e.g., TFBSs supported by DNAse I footprinting only. ' +
            'It is not applied to inferred CRMs'
    );
}
function exactAnatomicalExpressionTermHelp() {
    Ext.MessageBox.alert(
        'Help',
        'If checked, only the anatomical expression term will be used, not its descendant terms'
    );
}
function exactBiologicalProcessTermHelp() {
    Ext.MessageBox.alert(
        'Help',
        'If checked, only the biological process term will be used, not its descendant terms'
    );
}
function exactDevelopmentalStageTermHelp() {
    Ext.MessageBox.alert(
        'Help',
        'If checked, only the developmental stage term will be used, not its descendant terms'
    );
}
function excludeCellCultureHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Check this box to prevent returning results based exclusively on cell culture assays. ' + 
            'Uncheck to return all the records meeting the search criteria. ' +
            'It is applied to reporter constructs and CRM segments'
    );
}
function excludeEnhancerHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Check this box to return CRMs/RCs, CRM segments, and predicted CRMs excluding enhancers '
    );
}
function excludeSilencerHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Check this box to return CRMs/RCs, CRM segments, and predicted CRMs excluding silencers '
    );
}
function expressionNegativeHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Restrict the search to the CRMs/RCs and CRM segments negative for expression'
    );
}
function expressionPositiveHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Restrict the search to the CRMs/RCs and CRM segments positive for expression'
    );
}
function geneHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Use this field to search by primary gene symbol (e.g., dpp) or ' +
            'FlyBase ID (e.g., FBgn0000490). If \'by name\' is selected, ' +
            'retrieve the records associated with this gene name only. ' + 
            'If \'by locus\' is selected, ' +
            'retrieve all the records within 10000 bp of the gene being searched ' + 
            'besides the records associated with this gene name. ' + 
            'To change the search range, use the \'Search Range Interval\' ' +
            'field under the \'Advanced Search\' expandable menu. ' +
            'All the gene results are restricted by the current species shown ' +
            'in the \'"Sequence From" Species\' field. ' +
            'Predicted and inferred CRMs can be searched only by locus' 
    );
}
function includeEnhancerHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Check this box to return CRMs/RCs, CRM segments, and predicted CRMs including enhancers'
    );
}
function includeRangeSearchHelp() {
    Ext.MessageBox.alert(
        'Help',
        'If \'by name\' is selected, retrieve the records associated with this gene only. ' + 
            'If \'by locus\' is selected, retrieve all the records within 10000 bp of ' +
            'the gene being searched. To change the search range, use the \'Search Range ' +
            'Interval\' field under the \'Advanced Search\' expandable menu'
    );
}
function includeSilencerHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Check this box to return CRMs/RCs, CRM segments, and predicted CRMs including silencers '
    );
}
function lastUpdateHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Use this field to search for the entries that were last updated on or after this date. ' +
        'It is not applied to inferred CRMs'
    );
}
function maximumSequenceSizeHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Use this field to exclude any entity whose length is greater than the specified value ' +
        'in basepairs'
    );
}
function minimizedHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Restrict the search to the CRMs/RCs and CRM segments that have been minimized'
    );
}
function numHelp() {
    Ext.MessageBox.alert(
        'Help',
        'The numerator shows the number of records matching your search criteria and ' +
            'the denominator shows the total records available. ' + 
            'The "Total RC" number includes CRMs'
    );
}
function pubmedIdHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Use this field to search for all the elements described in the reference ' +
            'with the Pubmed ID as primary (for entities) and/or secondary ' +
            '(for staging data, if applicable). Pubmed ID searches are not applied ' +
            'to inferred CRMs'
    );
}
function positionHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Restrict the search to elements with the indicated position(s) ' +
            'relative to their associated gene. Options are non-exclusive, ' +
            'i.e., a RC that begins 5\' to the gene and extends through ' +
            'the first intron will be found by a search for any of 5\', ' +
            'intron, or exon. An element must extend >5 bp into a genomic ' +
            'feature to be considered as overlapping the feature. Position ' +
            'searches are not applied to predicted and inferred CRMs'
    );
}
function rcHasImagesHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Restrict the search to the CRMs/RCs containing images of reporter gene expression'
    );
}
function searchRangeIntervalHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Specify the search interval. Default: +/- 10000 bp. ' +
            'This has no effect if the \'by name\' radio button of the \'Gene Name\' ' +
            'field is selected. ' +
            'The search interval is not applied to predicted and inferred CRMs'
    );
}
function sequenceFromSpeciesHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Use this field to select the species to which the regulatory element belongs. ' +
            'The following fields: \'Gene Name\', \'Chromosome\', and \'Anatomical ' +
            'Expression Term\' are restricted by the current species shown ' +
            'in the \'"Sequence From" Species\' field'
    );
}
function targetGeneHelp() {
    Ext.MessageBox.alert(
        'Help',
        'When searching for a gene name/identifier, restricts the search to the target gene ' +
            '(regulated by the TFBS) only. ' + 
            'All the target gene results are restricted by the current species shown ' +
            'in the \'"Sequence From" Species\' field'
    );
}
function tfbsHasImagesHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Restrict the search to the TFBSs containing images of reporter gene expression'
    );
}
function tfbsWithCrmHelp() {
    Ext.MessageBox.alert(
        'Help',
        'Restrict the search to the TFBSs contained within annotated CRMs'
    );
}
function tfGeneHelp() {
    Ext.MessageBox.alert(
        'Help',
        'When searching for a gene name/identifier, restricts the search to the transcription factor ' +
            'that binds to the TFBS only. ' + 
            'All the transcription factor gene results are restricted by the current species shown ' +
            'in the \'"Sequence From" Species\' field'
    );
}    
