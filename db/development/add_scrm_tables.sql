DROP TABLE IF EXISTS `TrainingSet`;

CREATE TABLE `TrainingSet` (
    `training_set_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(32) NOT NULL,
    `entity_ids` JSON,
    PRIMARY KEY (`training_set_id`)
);

DROP TABLE IF EXISTS `Ortholog`;

CREATE TABLE `Ortholog` (
    `gene_id` int(10) unsigned NOT NULL,
    `ortholog_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`gene_id`, `ortholog_id`),
    FOREIGN KEY (`gene_id`) REFERENCES `Gene` (`gene_id`),
    FOREIGN KEY (`ortholog_id`) REFERENCES `Gene` (`gene_id`)
);

DROP TABLE IF EXISTS `SCRM`;

CREATE TABLE `SCRM` (
    `scrm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `chromosome_id` int(10) unsigned NOT NULL,
    `start` int(11) NOT NULL,
    `end` int(11) NOT NULL,
    `peak_height` float NOT NULL,
    `score` float NOT NULL,
    `training_set_id` int(10) unsigned NOT NULL,
    `method` enum("imm", "hexmcd", "pac") NOT NULL,
    `rank` int(11) NOT NULL,
    `negative_training_set` varchar(32) NOT NULL DEFAULT 'random non-coding',
    `version` enum("HD", "original") NOT NULL DEFAULT 'HD',
    PRIMARY KEY (`scrm_id`),
    FOREIGN KEY (`chromosome_id`) REFERENCES `Chromosome` (`chromosome_id`),
    FOREIGN KEY (`training_set_id`) REFERENCES `TrainingSet` (`training_set_id`)
);

DROP TABLE IF EXISTS `SCRM_associated_Gene`;

CREATE TABLE `SCRM_associated_Gene` (
    `scrm_id` int(10) unsigned NOT NULL,
    `gene_id` int(10) unsigned NOT NULL,
    `ortholog_id` int(10) unsigned,
    `distance` int(11) NOT NULL,
    `min_local_rank` int(11) NOT NULL,
    `local_rank` JSON NOT NULL,
    PRIMARY KEY (`scrm_id`, `gene_id`),
    FOREIGN KEY (`scrm_id`) REFERENCES `SCRM` (`scrm_id`),
    FOREIGN KEY (`gene_id`) REFERENCES `Gene` (`gene_id`),
    FOREIGN KEY (`ortholog_id`) REFERENCES `Gene` (`gene_id`)
);

DROP TABLE IF EXISTS `SCRM_Gene_location`;

CREATE TABLE `SCRM_Gene_location` (
    `scrm_id` int(10) unsigned NOT NULL,
    `gene_id` int(10) unsigned NOT NULL,
    `location` enum("downstream", "upstream", "inside") NOT NULL,
    PRIMARY KEY (`scrm_id`, `gene_id`, `location`),
    FOREIGN KEY (`scrm_id`) REFERENCES `SCRM` (`scrm_id`),
    FOREIGN KEY (`gene_id`) REFERENCES `Gene` (`gene_id`)
);

INSERT INTO `Species` (
    `scientific_name`,
    `short_name`,
    `public_database_names`,
    `public_database_links`,
    `public_browser_names`,
    `public_browser_links`
) VALUES
("Atta cephalotes", "acep", "", "", "", ""),
("Atta colombica", "acol", "", "", "", ""),
("Brassicogethes aeneus", "baen", "", "", "", ""),
("Bombus impatiens", "bimp", "", "", "", ""),
("Bombyx mori", "bmor", "", "", "", ""),
("Cimex lectularius", "clec", "", "", "", ""),
("Culex quinquefasciatus", "cqui", "", "", "", "");

INSERT INTO `GenomeAssembly` (
    `species_id`,
    `release_version`,
    `is_deprecated`
) SELECT `Species`.`species_id`, "A.ceph_1.0", false FROM `Species`
    WHERE `Species`.`short_name`="acep";

INSERT INTO `GenomeAssembly` (
    `species_id`,
    `release_version`,
    `is_deprecated`
) SELECT `Species`.`species_id`, "Acol_1.0", false FROM `Species`
    WHERE `Species`.`short_name` = "acol";

INSERT INTO `GenomeAssembly` (
    `species_id`,
    `release_version`,
    `is_deprecated`
) SELECT `Species`.`species_id`, "baen1", false FROM `Species`
    WHERE `Species`.`short_name` = "baen";

INSERT INTO `GenomeAssembly` (
    `species_id`,
    `release_version`,
    `is_deprecated`
) SELECT `Species`.`species_id`, "bimp_2.2", false FROM `Species`
    WHERE `Species`.`short_name` = "bimp";

INSERT INTO `GenomeAssembly` (
    `species_id`,
    `release_version`,
    `is_deprecated`
) SELECT `Species`.`species_id`, "bmor_v1", false FROM `Species`
    WHERE `Species`.`short_name` = "bmor";

INSERT INTO `GenomeAssembly` (
    `species_id`,
    `release_version`,
    `is_deprecated`
) SELECT `Species`.`species_id`, "Clec_2.1", false FROM `Species`
    WHERE `Species`.`short_name` = "clec";

INSERT INTO `GenomeAssembly` (
    `species_id`,
    `release_version`,
    `is_deprecated`
) SELECT `Species`.`species_id`, "Cqui_1.0", false FROM `Species`
    WHERE `Species`.`short_name` = "cqui";
