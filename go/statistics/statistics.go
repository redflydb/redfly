package statistics

import "fmt"

type RCStatistics struct {
	Total,
	New,
	Revised,
	Archived,
	FromInVivoReporterGenes,
	FromCellCultureAssays,
	FromOtherEvidence,
	AssociatedWithGenes,
	WithStagingData,
	WithoutStagingData int
}

type CRMStatistics struct {
	Total,
	New,
	Revised,
	Archived,
	FromInVivoReporterGenes,
	FromCellCultureAssays,
	FromOtherEvidence,
	AssociatedWithGenes,
	WithStagingData,
	WithoutStagingData int
}
type NonCRMStatistics struct {
	Total,
	New,
	Revised,
	Archived,
	FromInVivoReporterGenes,
	FromCellCultureAssays,
	FromOtherEvidence,
	AssociatedWithGenes,
	WithStagingData,
	WithoutStagingData int
}
type TFBSStatistics struct {
	Total,
	New,
	Revised,
	Archived,
	BoundByTranscriptionFactors,
	ActingOnTargetGenes int
}
type PredictedCRMStatistics struct {
	Total,
	New,
	Revised,
	Archived,
	WithStagingData,
	WithoutStagingData int
}
type CRMSegmentStatistics struct {
	Total,
	New,
	Revised,
	Archived,
	FromInVivoReporterGenes,
	FromCellCultureAssays,
	FromOtherEvidence,
	AssociatedWithGenes,
	WithStagingData,
	WithoutStagingData int
}
type DatabaseStatistics struct {
	LastUpdated           string
	SpeciesScientificName string
	RCs                   RCStatistics
	CRMs                  CRMStatistics
	NonCRMs               NonCRMStatistics
	TFBSs                 TFBSStatistics
	PredictedCRMs         PredictedCRMStatistics
	CRMSegments           CRMSegmentStatistics
	PublicationsCurated   int
}

func (ds DatabaseStatistics) String() string {
	return fmt.Sprintf(
		"Database last updated: %s\n"+
			"Species: %s\n"+
			"RCs:\n"+
			"\tTotal: %d\n"+
			"\tNew: %d\n"+
			"\tRevised: %d\n"+
			"\tArchived: %d\n"+
			"\tFrom in vivo reporter genes: %d\n"+
			"\tFrom cell culture assays: %d\n"+
			"\tFrom other evidence: %d\n"+
			"\tAssociated with genes: %d\n"+
			"\tAssociated with staging data: %d\n"+
			"\tAssociated without staging data: %d\n"+
			"CRMs:\n"+
			"\tTotal: %d\n"+
			"\tNew: %d\n"+
			"\tRevised: %d\n"+
			"\tArchived: %d\n"+
			"\tFrom in vivo reporter genes: %d\n"+
			"\tFrom cell culture assays: %d\n"+
			"\tFrom other evidence: %d\n"+
			"\tAssociated with genes: %d\n"+
			"\tAssociated with staging data: %d\n"+
			"\tAssociated without staging data: %d\n"+
			"Non-CRMs:\n"+
			"\tTotal: %d\n"+
			"\tNew: %d\n"+
			"\tRevised: %d\n"+
			"\tArchived: %d\n"+
			"\tFrom in vivo reporter genes: %d\n"+
			"\tFrom cell culture assays: %d\n"+
			"\tFrom other evidence: %d\n"+
			"\tAssociated with genes: %d\n"+
			"\tAssociated with staging data: %d\n"+
			"\tAssociated without staging data: %d\n"+
			"TFBSs:\n"+
			"\tTotal: %d\n"+
			"\tNew: %d\n"+
			"\tRevised: %d\n"+
			"\tArchived: %d\n"+
			"\tBound by transcription factors: %d\n"+
			"\tActing on target genes: %d\n"+
			"Predicted CRMs:\n"+
			"\tTotal: %d\n"+
			"\tNew: %d\n"+
			"\tRevised: %d\n"+
			"\tArchived: %d\n"+
			"\tAssociated with staging data: %d\n"+
			"\tAssociated without staging data: %d\n"+
			"CRM Segments:\n"+
			"\tTotal: %d\n"+
			"\tNew: %d\n"+
			"\tRevised: %d\n"+
			"\tArchived: %d\n"+
			"\tFrom in vivo reporter genes: %d\n"+
			"\tFrom cell culture assays: %d\n"+
			"\tFrom other evidence: %d\n"+
			"\tAssociated with genes: %d\n"+
			"\tAssociated with staging data: %d\n"+
			"\tAssociated without staging data: %d\n"+
			"Publications curated: %d\n",
		ds.LastUpdated,
		ds.SpeciesScientificName,
		ds.RCs.Total,
		ds.RCs.New,
		ds.RCs.Revised,
		ds.RCs.Archived,
		ds.RCs.FromInVivoReporterGenes,
		ds.RCs.FromCellCultureAssays,
		ds.RCs.FromOtherEvidence,
		ds.RCs.AssociatedWithGenes,
		ds.RCs.WithStagingData,
		ds.RCs.WithoutStagingData,
		ds.CRMs.Total,
		ds.CRMs.New,
		ds.CRMs.Revised,
		ds.CRMs.Archived,
		ds.CRMs.FromInVivoReporterGenes,
		ds.CRMs.FromCellCultureAssays,
		ds.CRMs.FromOtherEvidence,
		ds.CRMs.AssociatedWithGenes,
		ds.CRMs.WithStagingData,
		ds.CRMs.WithoutStagingData,
		ds.NonCRMs.Total,
		ds.NonCRMs.New,
		ds.NonCRMs.Revised,
		ds.NonCRMs.Archived,
		ds.NonCRMs.FromInVivoReporterGenes,
		ds.NonCRMs.FromCellCultureAssays,
		ds.NonCRMs.FromOtherEvidence,
		ds.NonCRMs.AssociatedWithGenes,
		ds.NonCRMs.WithStagingData,
		ds.NonCRMs.WithoutStagingData,
		ds.TFBSs.Total,
		ds.TFBSs.New,
		ds.TFBSs.Revised,
		ds.TFBSs.Archived,
		ds.TFBSs.BoundByTranscriptionFactors,
		ds.TFBSs.ActingOnTargetGenes,
		ds.PredictedCRMs.Total,
		ds.PredictedCRMs.New,
		ds.PredictedCRMs.Revised,
		ds.PredictedCRMs.Archived,
		ds.PredictedCRMs.WithStagingData,
		ds.PredictedCRMs.WithoutStagingData,
		ds.CRMSegments.Total,
		ds.CRMSegments.New,
		ds.CRMSegments.Revised,
		ds.CRMSegments.Archived,
		ds.CRMSegments.FromInVivoReporterGenes,
		ds.CRMSegments.FromCellCultureAssays,
		ds.CRMSegments.FromOtherEvidence,
		ds.CRMSegments.AssociatedWithGenes,
		ds.CRMSegments.WithStagingData,
		ds.CRMSegments.WithoutStagingData,
		ds.PublicationsCurated,
	)
}
