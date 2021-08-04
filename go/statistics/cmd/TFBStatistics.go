package main

import (
	"database/sql"

	"redfly.edu/statistics"
)

func fillTFBSStatistics(
	db *sql.DB,
	speciesID string,
	databaseLastUpdatedFromReport string) statistics.TFBSStatistics {
	var total,
		new,
		revised,
		archived,
		factors,
		genes int
	var sql,
		latest string
	// The number of current transcription factor binding sites
	sql = `SELECT NumberOfCurrentTranscriptionFactorBindingSites(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&total); err != nil {
		panic(err)
	}
	// The lastest update date from the current transcription factor binding sites
	if speciesID == "0" {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		   	   FROM BindingSite
			   WHERE state = 'current';`
	} else {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		   	   FROM BindingSite
			   WHERE sequence_from_species_id = ` + speciesID + ` AND
			       state = 'current';`
	}
	if err := db.QueryRow(sql).Scan(&latest); err != nil {
		panic(err)
	}
	// The number of new current transcription factor binding sites
	if speciesID == "0" {
		sql = `SELECT COUNT(tfbs_id)
		       FROM BindingSite
		       WHERE state = 'current' AND
			       version = 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(tfbs_id)
		       FROM BindingSite
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
	// The number of current transcription factor binding sites revised
	if speciesID == "0" {
		sql = `SELECT COUNT(tfbs_id)
		       FROM BindingSite
		       WHERE state = 'current' AND
			       version > 0 AND
			       ? < DATE(last_update) AND
				   DATE(last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(tfbs_id)
		       FROM BindingSite
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
	// The number of new archived transcription factor binding sites
	if speciesID == "0" {
		sql = `SELECT COUNT(bs1.tfbs_id)
		   	   FROM BindingSite bs1,
				    (SELECT entity_id,
					     MAX(version) AS maximum_version
				     FROM BindingSite
				     WHERE entity_id IS NOT NULL
				     GROUP BY entity_id) AS bs2
		       WHERE bs1.state = 'archived' AND
			       bs1.entity_id = bs2.entity_id AND
			       bs1.version = bs2.maximum_version AND
			       ? < DATE(bs1.last_update) AND
				   DATE(bs1.last_update) <= ?;`
	} else {
		sql = `SELECT COUNT(bs1.tfbs_id)
		   	   FROM BindingSite bs1,
				    (SELECT entity_id,
					     MAX(version) AS maximum_version
				     FROM BindingSite
					 WHERE sequence_from_species_id = ` + speciesID + ` AND
					     entity_id IS NOT NULL
				     GROUP BY entity_id) AS bs2
			   WHERE bs1.sequence_from_species_id = ` + speciesID + ` AND
			       bs1.state = 'archived' AND
			       bs1.entity_id = bs2.entity_id AND
			       bs1.version = bs2.maximum_version AND
			       ? < DATE(bs1.last_update) AND
				   DATE(bs1.last_update) <= ?;`
	}
	if err := db.QueryRow(
		sql,
		databaseLastUpdatedFromReport,
		latest).Scan(&archived); err != nil {
		panic(err)
	}
	// The number of transcription factors used by current transcription factor binding sites
	sql = `SELECT NumberOfCurrentTranscriptionFactors(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&factors); err != nil {
		panic(err)
	}
	// The number of genes used by current transcription factor binding sites
	sql = `SELECT NumberOfCurrentTranscriptionFactorBindingSiteGenes(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&genes); err != nil {
		panic(err)
	}

	return statistics.TFBSStatistics{
		Total:                       total,
		New:                         new,
		Revised:                     revised,
		Archived:                    archived,
		BoundByTranscriptionFactors: factors,
		ActingOnTargetGenes:         genes}
}
