package main

import (
	"database/sql"

	"redfly.edu/statistics"
)

func fillNonCRMStatistics(
	db *sql.DB,
	speciesID string,
	databaseLastUpdatedFromReport string) statistics.NonCRMStatistics {
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
	// The number of current non-cis-regulatory modules
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
			   FROM ReporterConstruct
		   	   WHERE state = 'current' AND
				   is_crm = 0;`
	} else {
		sql = `SELECT COUNT(rc_id)
			   FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
				   is_crm = 0;`
	}
	if err := db.QueryRow(sql).Scan(&total); err != nil {
		panic(err)
	}
	// The lastest update date from the current non-cis-regulatory modules
	if speciesID == "0" {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
			       is_crm = 0;`
	} else {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
			       is_crm = 0;`
	}
	if err := db.QueryRow(sql).Scan(&latest); err != nil {
		panic(err)
	}
	// The number of new current non-cis-regulatory modules
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
		   	       is_crm = 0 AND
			       version = 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
		   	       is_crm = 0 AND
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
	// The number of current non-cis-regulatory modules revised
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
			       is_crm = 0 AND
			       version > 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
			       is_crm = 0 AND
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
	// The number of new archived non-cis-regulatory modules
	if speciesID == "0" {
		sql = `SELECT COUNT(rc1.rc_id)
		       FROM ReporterConstruct rc1,
				    (SELECT entity_id,
					     MAX(version) AS maximum_version
				     FROM ReporterConstruct
				     WHERE entity_id IS NOT NULL
				     GROUP BY entity_id) AS rc2
		       WHERE rc1.state = 'archived' AND
		   	       rc1.is_crm = 0 AND
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
		   	       rc1.is_crm = 0 AND
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
	// The number of current non-cis-regulatory modules (in vivo)
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
			       is_crm = 0 AND
				   evidence_id = 2;`
	} else {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
			       is_crm = 0 AND
				   evidence_id = 2;`
	}
	if err := db.QueryRow(sql).Scan(&vivo); err != nil {
		panic(err)
	}
	// The number of current non-cis-regulatory modules only with cell culture
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
		   	   FROM ReporterConstruct
		       WHERE state = 'current' AND 
			       ! is_crm AND
				   cell_culture_only;`
	} else {
		sql = `SELECT COUNT(rc_id)
		   	   FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND 
			       ! is_crm AND
				   cell_culture_only;`
	}
	if err := db.QueryRow(sql).Scan(&assays); err != nil {
		panic(err)
	}
	// The number of current non in-vivo non-cis-regulatory modules not having any cell culture
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
		           is_crm = 0 AND
			       evidence_id != 2 AND
				   cell_culture_only = 0;`
	} else {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
		           is_crm = 0 AND
			       evidence_id != 2 AND
				   cell_culture_only = 0;`
	}
	if err := db.QueryRow(sql).Scan(&other); err != nil {
		panic(err)
	}
	// The number of genes used by current non-cis-regulatory modules
	if speciesID == "0" {
		sql = `SELECT COUNT(DISTINCT gene_id)
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
			       is_crm = 0;`
	} else {
		sql = `SELECT COUNT(DISTINCT gene_id)
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
			       is_crm = 0;`
	}
	if err := db.QueryRow(sql).Scan(&genes); err != nil {
		panic(err)
	}
	// The number of current non-cis-regulatory modules with staging data
	sql = `SELECT NumberOfCurrentNonCisRegulatoryModulesWithStagingData(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&withStagingData); err != nil {
		panic(err)
	}
	// The number of current non-cis-regulatory modules without any staging data
	sql = `SELECT NumberOfCurrentNonCisRegulatoryModulesWithoutStagingData(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&withoutStagingData); err != nil {
		panic(err)
	}

	return statistics.NonCRMStatistics{
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
