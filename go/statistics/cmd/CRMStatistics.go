package main

import (
	"database/sql"

	"redfly.edu/statistics"
)

func fillCRMStatistics(
	db *sql.DB,
	speciesID string,
	databaseLastUpdatedFromReport string) statistics.CRMStatistics {
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
	// The number of current cis-regulatory modules
	sql = `SELECT NumberOfCurrentCisRegulatoryModules(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&total); err != nil {
		panic(err)
	}
	// The lastest update date from the current cis-regulatory modules
	if speciesID == "0" {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
			       is_crm = 1;`
	} else {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
				   is_crm = 1;`
	}
	if err := db.QueryRow(sql).Scan(&latest); err != nil {
		panic(err)
	}
	// The number of new current cis-regulatory modules
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
			       is_crm = 1 AND
			       version = 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
			       is_crm = 1 AND
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
	// The number of current cis-regulatory modules revised
	if speciesID == "0" {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
		       WHERE state = 'current' AND
			       is_crm = 1 AND
			       version > 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(rc_id)
		       FROM ReporterConstruct
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current' AND
			       is_crm = 1 AND
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
	// The number of new archived cis-regulatory modules
	if speciesID == "0" {
		sql = `SELECT COUNT(rc1.rc_id)
		       FROM ReporterConstruct rc1,
				    (SELECT entity_id,
					     MAX(version) AS maximum_version
				     FROM ReporterConstruct
				     WHERE entity_id IS NOT NULL
				     GROUP BY entity_id) AS rc2
		       WHERE rc1.state = 'archived' AND
		   	       rc1.is_crm = 1 AND
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
		   	       rc1.is_crm = 1 AND
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
	// The number of current in-vivo cis-regulatory modules
	sql = `SELECT NumberOfCurrentInVivoCisRegulatoryModules(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&vivo); err != nil {
		panic(err)
	}
	// The number of current cis-regulatory modules having cell culture only
	sql = `SELECT NumberOfCurrentCisRegulatoryModulesHavingCellCultureOnly(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&assays); err != nil {
		panic(err)
	}
	// The number of current non in-vivo cis-regulatory modules having no cell culture
	sql = `SELECT NumberOfCurrentNonInVivoCisRegulatoryModulesHavingNoCellCulture(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&other); err != nil {
		panic(err)
	}
	// The number of genes used by current cis-regulatory modules
	sql = `SELECT NumberOfCurrentCisRegulatoryModuleGenes(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&genes); err != nil {
		panic(err)
	}
	// The number of current cis-regulatory modules with staging data
	sql = `SELECT NumberOfCurrentCisRegulatoryModulesWithStagingData(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&withStagingData); err != nil {
		panic(err)
	}
	// The number of current cis-regulatory modules without any staging data
	sql = `SELECT NumberOfCurrentCisRegulatoryModulesWithoutStagingData(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&withoutStagingData); err != nil {
		panic(err)
	}

	return statistics.CRMStatistics{
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
