package main

import (
	"database/sql"

	"redfly.edu/statistics"
)

func fillRCStatistics(
	db *sql.DB,
	speciesID string,
	databaseLastUpdatedFromReport string) statistics.RCStatistics {
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
	// The number of current reporter constructs
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
			   WHERE state = 'current';`
	} else {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current';`
	}
	if err := db.QueryRow(sql).Scan(&total); err != nil {
		panic(err)
	}
	// The lastest update date from the current reporter constructs
	if speciesID == "0" {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		       FROM ReporterConstruct
			   WHERE state = 'current';`
	} else {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current';`
	}
	if err := db.QueryRow(sql).Scan(&latest); err != nil {
		panic(err)
	}
	// The number of new current reporter constructs
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
			       version = 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
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
	// The number of current reporter constructs revised
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
		   	       version > 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
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
	// The number of new archived reporter constructs
	if speciesID == "0" {
		sql = `SELECT COUNT(rc1.rc_id)
		       FROM ReporterConstruct rc1,
				    (SELECT entity_id,
					     MAX(version) AS maximum_version
				     FROM ReporterConstruct
					 WHERE entity_id IS NOT NULL
				     GROUP BY entity_id) AS rc2
			   WHERE rc1.state = 'archived' AND
			       rc1.entity_id = rc2.entity_id AND
			       rc1.version = rc2.maximum_version AND
			       ? < DATE(rc1.last_update) AND
				   DATE(rc1.last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(rc1.rc_id)
		       FROM ReporterConstruct rc1,
				    (SELECT entity_id,
					     MAX(version) AS maximum_version
				     FROM ReporterConstruct
					 WHERE sequence_from_species_id = ` + speciesID + ` AND
					     entity_id IS NOT NULL
				     GROUP BY entity_id) AS rc2
			   WHERE rc1.sequence_from_species_id = ` + speciesID + ` AND
			       rc1.state = 'archived' AND
			       rc1.entity_id = rc2.entity_id AND
			       rc1.version = rc2.maximum_version AND
			       ? < DATE(rc1.last_update) AND
				   DATE(rc1.last_update) <= ?;`
	}
	if err := db.QueryRow(
		sql,
		databaseLastUpdatedFromReport,
		latest).Scan(&archived); err != nil {
		panic(err)
	}
	// The number of current reporter constructs (in vivo)
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
			       evidence_id = 2;`
	} else {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
				   evidence_id = 2;`
	}
	if err := db.QueryRow(sql).Scan(&vivo); err != nil {
		panic(err)
	}
	// The number of current reporter constructs only with cell culture
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
				   cell_culture_only = 1;`
	} else {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
				   cell_culture_only = 1;`
	}
	if err := db.QueryRow(sql).Scan(&assays); err != nil {
		panic(err)
	}
	// The number of current non in-vivo reporter constructs not having any cell culture
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
			       evidence_id != 2 AND
				   cell_culture_only = 0;`
	} else {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
			       evidence_id != 2 AND
				   cell_culture_only = 0;`
	}
	if err := db.QueryRow(sql).Scan(&other); err != nil {
		panic(err)
	}
	// The number of genes used by current reporter constructs
	if speciesID == "0" {
		sql = `SELECT COUNT(DISTINCT gene_id)
		       FROM ReporterConstruct
			   WHERE state = 'current';`
	} else {
		sql = `SELECT COUNT(DISTINCT gene_id)
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current';`
	}
	if err := db.QueryRow(sql).Scan(&genes); err != nil {
		panic(err)
	}
	// The number of current reporter constructs with staging data
	sql = `SELECT NumberOfCurrentReporterConstructsWithStagingData(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&withStagingData); err != nil {
		panic(err)
	}
	// The number of current reporter constructs without any staging data
	sql = `SELECT NumberOfCurrentReporterConstructsWithoutStagingData(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&withoutStagingData); err != nil {
		panic(err)
	}

	return statistics.RCStatistics{
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
