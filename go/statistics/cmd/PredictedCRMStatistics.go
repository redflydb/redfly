package main

import (
	"database/sql"

	"redfly.edu/statistics"
)

func fillPredictedCRMStatistics(
	db *sql.DB,
	speciesID string,
	databaseLastUpdatedFromReport string) statistics.PredictedCRMStatistics {
	var total,
		new,
		revised,
		archived,
		withStagingData,
		withoutStagingData int
	var sql,
		latest string
	// The number of current predicted cis-regulatory modules
	sql = `SELECT NumberOfCurrentPredictedCisRegulatoryModules(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&total); err != nil {
		panic(err)
	}
	// The lastest update date from the current predicted cis-regulatory modules
	if speciesID == "0" {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		   	   FROM PredictedCRM
			   WHERE state = 'current';`
	} else {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		   	   FROM PredictedCRM
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current';`
	}
	if err := db.QueryRow(sql).Scan(&latest); err != nil {
		panic(err)
	}
	// The number of new current predicted cis-regulatory modules
	if speciesID == "0" {
		sql = `SELECT COUNT(predicted_crm_id)
		       FROM PredictedCRM 
		       WHERE state = 'current' AND
			       version = 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(predicted_crm_id)
		       FROM PredictedCRM 
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
	// The number of current predicted cis-regulatory modules revised
	if speciesID == "0" {
		sql = `SELECT COUNT(predicted_crm_id)
		   	   FROM PredictedCRM
		       WHERE state = 'current' AND
			       version > 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(predicted_crm_id)
		   	   FROM PredictedCRM
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
	// The number of new archived predicted cis-regulatory modules
	if speciesID == "0" {
		sql = `SELECT COUNT(pcrm1.predicted_crm_id)
		       FROM PredictedCRM pcrm1,
				    (SELECT entity_id,
					     MAX(version) AS maximum_version
				     FROM PredictedCRM
				     WHERE entity_id IS NOT NULL
				     GROUP BY entity_id) AS pcrm2
		       WHERE pcrm1.state = 'archived' AND
		   	       pcrm1.entity_id = pcrm2.entity_id AND
			       pcrm1.version = pcrm2.maximum_version AND
			       ? < DATE(pcrm1.last_update) AND
				   DATE(pcrm1.last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(pcrm1.predicted_crm_id)
		       FROM PredictedCRM pcrm1,
				    (SELECT entity_id,
					     MAX(version) AS maximum_version
				     FROM PredictedCRM
					 WHERE sequence_from_species_id = ` + speciesID + ` AND
					     entity_id IS NOT NULL
				     GROUP BY entity_id) AS pcrm2
			   WHERE pcrm1.sequence_from_species_id = ` + speciesID + ` AND
			       pcrm1.state = 'archived' AND
		   	       pcrm1.entity_id = pcrm2.entity_id AND
			       pcrm1.version = pcrm2.maximum_version AND
			       ? < DATE(pcrm1.last_update) AND
				   DATE(pcrm1.last_update) <= ?;`
	}
	if err := db.QueryRow(
		sql,
		databaseLastUpdatedFromReport,
		latest).Scan(&archived); err != nil {
		panic(err)
	}
	// The number of current predicted cis-regulatory modules with staging data
	sql = `SELECT NumberOfCurrentPredictedCisRegulatoryModulesWithStagingData(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&withStagingData); err != nil {
		panic(err)
	}
	// The number of current predicted cis-regulatory modules without any staging data
	sql = `SELECT NumberOfCurrentPredictedCisRegulatoryModulesWithoutStagingData(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&withoutStagingData); err != nil {
		panic(err)
	}

	return statistics.PredictedCRMStatistics{
		Total:              total,
		New:                new,
		Revised:            revised,
		Archived:           archived,
		WithStagingData:    withStagingData,
		WithoutStagingData: withoutStagingData}
}
