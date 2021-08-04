package main

import (
	"database/sql"

	"redfly.edu/statistics"
)

func fillCRMSegmentStatistics(
	db *sql.DB,
	speciesID string,
	databaseLastUpdatedFromReport string) statistics.CRMSegmentStatistics {
	var total,
		new,
		revised,
		archived,
		vivo,
		assays,
		other,
		genes,
		withStagingData,
		withoutStagingData int
	var sql,
		latest string
	// The number of current cis-regulatory module segments
	sql = `SELECT NumberOfCurrentCisRegulatoryModuleSegments(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&total); err != nil {
		panic(err)
	}
	// The lastest update date from the current cis-regulatory module segments
	if speciesID == "0" {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		   	   FROM CRMSegment
			   WHERE state = 'current';`
	} else {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		   	   FROM CRMSegment
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current';`
	}
	if err := db.QueryRow(sql).Scan(&latest); err != nil {
		panic(err)
	}
	// The number of new current cis-regulatory module segments
	if speciesID == "0" {
		sql = `SELECT COUNT(crm_segment_id)
		       FROM CRMSegment
		       WHERE state = 'current' AND
			       version = 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(crm_segment_id)
		       FROM CRMSegment
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
			       version = 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	}
	if err := db.QueryRow(
		sql,
		databaseLastUpdatedFromReport,
		latest).Scan(&new); err != nil {
		panic(err)
	}
	// The number of current cis-regulatory module segments revised
	if speciesID == "0" {
		sql = `SELECT COUNT(crm_segment_id)
		       FROM CRMSegment
		       WHERE state = 'current' AND
			       version > 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(crm_segment_id)
		       FROM CRMSegment
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
			       version > 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	}
	if err := db.QueryRow(
		sql,
		databaseLastUpdatedFromReport,
		latest).Scan(&revised); err != nil {
		panic(err)
	}
	// The number of new archived cis-regulatory module segments
	if speciesID == "0" {
		sql = `SELECT COUNT(crms1.crm_segment_id)
		       FROM CRMSegment crms1,
				    (SELECT entity_id,
					     MAX(version) AS maximum_version
				     FROM CRMSegment
				     WHERE entity_id IS NOT NULL
				     GROUP BY entity_id) AS crms2
		       WHERE crms1.state = 'archived' AND
		           crms1.entity_id = crms2.entity_id AND
			       crms1.version = crms2.maximum_version AND
			       ? < DATE(crms1.last_update) AND
				   DATE(crms1.last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(crms1.crm_segment_id)
		       FROM CRMSegment crms1,
				    (SELECT entity_id,
					     MAX(version) AS maximum_version
				     FROM CRMSegment
					 WHERE sequence_from_species_id = ` + speciesID + ` AND
					     entity_id IS NOT NULL
				     GROUP BY entity_id) AS crms2
			   WHERE crms1.sequence_from_species_id = ` + speciesID + ` AND
			       crms1.state = 'archived' AND
		           crms1.entity_id = crms2.entity_id AND
			       crms1.version = crms2.maximum_version AND
			       ? < DATE(crms1.last_update) AND
				   DATE(crms1.last_update) <= ?;`
	}
	if err := db.QueryRow(
		sql,
		databaseLastUpdatedFromReport,
		latest).Scan(&archived); err != nil {
		panic(err)
	}
	// The number of current in-vivo cis-regulatory module segments
	if speciesID == "0" {
		sql = `SELECT COUNT(crm_segment_id)
		       FROM CRMSegment
		       WHERE state = 'current' AND
				   evidence_id = 2;`
	} else {
		sql = `SELECT COUNT(crm_segment_id)
		       FROM CRMSegment
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
				   evidence_id = 2;`
	}
	if err := db.QueryRow(sql).Scan(&vivo); err != nil {
		panic(err)
	}
	// The number of current cis-regulatory module segments only with cell culture
	if speciesID == "0" {
		sql = `SELECT COUNT(crm_segment_id)
		       FROM CRMSegment
		       WHERE state = 'current' AND
				   cell_culture_only = 1;`
	} else {
		sql = `SELECT COUNT(crm_segment_id)
		       FROM CRMSegment
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
				   cell_culture_only = 1;`
	}
	if err := db.QueryRow(sql).Scan(&assays); err != nil {
		panic(err)
	}
	// The number of current non in-vivo cis-regulatory module segments not having any cell culture
	if speciesID == "0" {
		sql = `SELECT COUNT(crm_segment_id)
		       FROM CRMSegment
		       WHERE state = 'current' AND
			       evidence_id != 2 AND
				   cell_culture_only = 0;`
	} else {
		sql = `SELECT COUNT(crm_segment_id)
		       FROM CRMSegment
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
			       evidence_id != 2 AND
				   cell_culture_only = 0;`
	}
	if err := db.QueryRow(sql).Scan(&other); err != nil {
		panic(err)
	}
	// The number of genes used by current cis-regulatory module segments
	sql = `SELECT NumberOfCurrentCisRegulatoryModuleSegmentGenes(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&genes); err != nil {
		panic(err)
	}
	// The number of current cis-regulatory module segments with staging data
	sql = `SELECT NumberOfCurrentCisRegulatoryModuleSegmentsWithStagingData(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&withStagingData); err != nil {
		panic(err)
	}
	// The number of current cis-regulatory module segments without any staging data
	sql = `SELECT NumberOfCurrentCisRegulatoryModuleSegmentsWithoutStagingData(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&withoutStagingData); err != nil {
		panic(err)
	}

	return statistics.CRMSegmentStatistics{
		Total:                   total,
		New:                     new,
		Revised:                 revised,
		Archived:                archived,
		FromInVivoReporterGenes: vivo,
		FromCellCultureAssays:   assays,
		FromOtherEvidence:       other,
		AssociatedWithGenes:     genes,
		WithStagingData:         withStagingData,
		WithoutStagingData:      withoutStagingData}
}
