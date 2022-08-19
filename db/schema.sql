-- MariaDB dump 10.19  Distrib 10.5.15-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: mariadb_server    Database: redfly
-- ------------------------------------------------------
-- Server version	10.5.15-MariaDB-1:10.5.15+maria~focal

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `BS_has_FigureLabel`
--

DROP TABLE IF EXISTS `BS_has_FigureLabel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BS_has_FigureLabel` (
  `tfbs_id` int(10) unsigned NOT NULL,
  `label` varchar(32) NOT NULL,
  PRIMARY KEY (`tfbs_id`,`label`),
  KEY `tfbs_id` (`tfbs_id`),
  KEY `label` (`label`),
  CONSTRAINT `BS_has_FigureLabel_ibfk_1` FOREIGN KEY (`tfbs_id`) REFERENCES `BindingSite` (`tfbs_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `BindingSite`
--

DROP TABLE IF EXISTS `BindingSite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BindingSite` (
  `tfbs_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sequence_from_species_id` int(10) unsigned NOT NULL,
  `assayed_in_species_id` int(10) unsigned NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `pubmed_id` varchar(64) NOT NULL,
  `state` enum('approval','approved','archived','current','deleted','editing') DEFAULT NULL,
  `version` smallint(6) NOT NULL DEFAULT 0,
  `gene_id` int(10) unsigned NOT NULL,
  `tf_id` int(10) unsigned NOT NULL,
  `chromosome_id` int(10) unsigned NOT NULL,
  `evidence_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `figure_labels` text DEFAULT NULL,
  `curator_id` int(10) unsigned DEFAULT NULL,
  `date_added` datetime DEFAULT current_timestamp(),
  `auditor_id` int(10) unsigned DEFAULT NULL,
  `last_audit` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `archive_date` datetime DEFAULT NULL,
  `sequence` text DEFAULT NULL,
  `sequence_with_flank` text DEFAULT NULL,
  `num_flank_bp` int(11) NOT NULL DEFAULT 20,
  `size` int(10) unsigned DEFAULT NULL,
  `current_genome_assembly_release_version` varchar(32) DEFAULT '',
  `current_start` int(11) DEFAULT 0,
  `current_end` int(11) DEFAULT 0,
  `archived_genome_assembly_release_versions` varchar(256) DEFAULT '',
  `archived_starts` varchar(512) DEFAULT '',
  `archived_ends` varchar(512) DEFAULT '',
  `has_rc` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tfbs_id`),
  KEY `species_id` (`sequence_from_species_id`),
  KEY `gene` (`gene_id`),
  KEY `tf` (`tf_id`),
  KEY `curator` (`curator_id`),
  KEY `auditor` (`auditor_id`),
  KEY `chromosome` (`chromosome_id`),
  KEY `evidence` (`evidence_id`),
  KEY `state` (`state`),
  KEY `location` (`chromosome_id`),
  KEY `entity_id` (`entity_id`,`version`),
  KEY `assayed_in_species_id` (`assayed_in_species_id`),
  KEY `pubmed_id` (`pubmed_id`),
  CONSTRAINT `BindingSite_ibfk_1` FOREIGN KEY (`sequence_from_species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `BindingSite_ibfk_2` FOREIGN KEY (`assayed_in_species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_{12F8D282-A894-4EF7-8E77-DECB7039D1C7}` FOREIGN KEY (`curator_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_{1CC44F58-504B-4F06-B667-634302DA7525}` FOREIGN KEY (`evidence_id`) REFERENCES `EvidenceTerm` (`evidence_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_{37F2B4D3-E48F-4CA6-B5B5-952A1088B427}` FOREIGN KEY (`gene_id`) REFERENCES `Gene` (`gene_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_{9C263B85-7A07-4EAF-8E89-787DD33795D7}` FOREIGN KEY (`auditor_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_{B211B76A-19E3-4506-8D36-560270DC37D3}` FOREIGN KEY (`tf_id`) REFERENCES `Gene` (`gene_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_{D895F07B-5B3D-408E-9F33-5D3A520234D4}` FOREIGN KEY (`chromosome_id`) REFERENCES `Chromosome` (`chromosome_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7923 DEFAULT CHARSET=utf8 COMMENT='Transcription Factor Binding Sites';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `BiologicalProcess`
--

DROP TABLE IF EXISTS `BiologicalProcess`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BiologicalProcess` (
  `process_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `term` varchar(255) NOT NULL DEFAULT '',
  `go_id` varchar(32) NOT NULL,
  `is_deprecated` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`process_id`),
  KEY `process_id` (`process_id`),
  KEY `go_id` (`go_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66310 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `CRMSegment`
--

DROP TABLE IF EXISTS `CRMSegment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CRMSegment` (
  `crm_segment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sequence_from_species_id` int(10) unsigned NOT NULL,
  `assayed_in_species_id` int(10) unsigned NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `pubmed_id` varchar(64) NOT NULL,
  `state` enum('approval','approved','archived','current','deleted','editing') DEFAULT NULL,
  `version` smallint(6) NOT NULL DEFAULT 0,
  `gene_id` int(10) unsigned NOT NULL,
  `chromosome_id` int(10) unsigned NOT NULL,
  `evidence_id` int(10) unsigned NOT NULL,
  `evidence_subtype_id` int(10) unsigned DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `figure_labels` text DEFAULT NULL,
  `has_flyexpress_images` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `is_crm` tinyint(1) NOT NULL DEFAULT 0,
  `is_override` tinyint(1) NOT NULL DEFAULT 0,
  `is_negative` tinyint(1) NOT NULL DEFAULT 0,
  `is_minimalized` tinyint(1) NOT NULL DEFAULT 0,
  `fbtp` varchar(64) DEFAULT NULL,
  `fbal` varchar(64) DEFAULT NULL,
  `curator_id` int(10) unsigned DEFAULT NULL,
  `date_added` datetime DEFAULT current_timestamp(),
  `auditor_id` int(10) unsigned DEFAULT NULL,
  `last_audit` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `archive_date` datetime DEFAULT NULL,
  `sequence` text DEFAULT NULL,
  `sequence_source_id` int(10) unsigned NOT NULL,
  `size` int(10) unsigned DEFAULT NULL,
  `current_genome_assembly_release_version` varchar(32) DEFAULT '',
  `current_start` int(11) DEFAULT 0,
  `current_end` int(11) DEFAULT 0,
  `archived_genome_assembly_release_versions` varchar(256) DEFAULT '',
  `archived_starts` varchar(512) DEFAULT '',
  `archived_ends` varchar(512) DEFAULT '',
  `has_tfbs` tinyint(1) NOT NULL DEFAULT 0,
  `cell_culture_only` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`crm_segment_id`),
  KEY `species_id` (`sequence_from_species_id`),
  KEY `name` (`name`),
  KEY `state` (`state`),
  KEY `gene` (`gene_id`),
  KEY `chromosome` (`chromosome_id`),
  KEY `evidence` (`evidence_id`),
  KEY `evidence_subtype` (`evidence_subtype_id`),
  KEY `entity_id` (`entity_id`,`version`),
  KEY `curator` (`curator_id`),
  KEY `auditor` (`auditor_id`),
  KEY `sequence_source` (`sequence_source_id`),
  KEY `location` (`chromosome_id`),
  KEY `minimization` (`chromosome_id`,`state`),
  KEY `assayed_in_species_id` (`assayed_in_species_id`),
  KEY `pubmed_id` (`pubmed_id`),
  CONSTRAINT `CRMSegment_ibfk_10` FOREIGN KEY (`assayed_in_species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `CRMSegment_ibfk_2` FOREIGN KEY (`gene_id`) REFERENCES `Gene` (`gene_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `CRMSegment_ibfk_3` FOREIGN KEY (`chromosome_id`) REFERENCES `Chromosome` (`chromosome_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `CRMSegment_ibfk_4` FOREIGN KEY (`evidence_id`) REFERENCES `EvidenceTerm` (`evidence_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `CRMSegment_ibfk_5` FOREIGN KEY (`evidence_subtype_id`) REFERENCES `EvidenceSubtypeTerm` (`evidence_subtype_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `CRMSegment_ibfk_6` FOREIGN KEY (`curator_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `CRMSegment_ibfk_7` FOREIGN KEY (`auditor_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `CRMSegment_ibfk_8` FOREIGN KEY (`sequence_source_id`) REFERENCES `SequenceSourceTerm` (`source_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `CRMSegment_ibfk_9` FOREIGN KEY (`sequence_from_species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `CRMSegment_has_Expression_Term`
--

DROP TABLE IF EXISTS `CRMSegment_has_Expression_Term`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CRMSegment_has_Expression_Term` (
  `crm_segment_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`crm_segment_id`,`term_id`),
  KEY `term_id` (`term_id`),
  CONSTRAINT `CRMSegment_has_Expression_Term_ibfk_1` FOREIGN KEY (`crm_segment_id`) REFERENCES `CRMSegment` (`crm_segment_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `CRMSegment_has_Expression_Term_ibfk_2` FOREIGN KEY (`term_id`) REFERENCES `ExpressionTerm` (`term_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `CRMSegment_has_FigureLabel`
--

DROP TABLE IF EXISTS `CRMSegment_has_FigureLabel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CRMSegment_has_FigureLabel` (
  `crm_segment_id` int(10) unsigned NOT NULL,
  `label` varchar(32) NOT NULL,
  PRIMARY KEY (`crm_segment_id`,`label`),
  KEY `crm_segment_id` (`crm_segment_id`),
  KEY `label` (`label`),
  CONSTRAINT `CRMSegment_has_FigureLabel_ibfk_1` FOREIGN KEY (`crm_segment_id`) REFERENCES `CRMSegment` (`crm_segment_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Chromosome`
--

DROP TABLE IF EXISTS `Chromosome`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Chromosome` (
  `chromosome_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `species_id` int(10) unsigned NOT NULL,
  `genome_assembly_id` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `length` int(11) NOT NULL COMMENT '		',
  PRIMARY KEY (`chromosome_id`),
  KEY `species` (`chromosome_id`,`species_id`),
  KEY `species_id` (`species_id`),
  KEY `genome_assembly_id` (`genome_assembly_id`),
  CONSTRAINT `Chromosome_ibfk_1` FOREIGN KEY (`genome_assembly_id`) REFERENCES `GenomeAssembly` (`genome_assembly_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_{E1F5F3DD-B186-456B-B79C-CE9F2075E951}` FOREIGN KEY (`species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=760 DEFAULT CHARSET=utf8 COMMENT='Chromosomes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Citation`
--

DROP TABLE IF EXISTS `Citation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Citation` (
  `citation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `citation_type` enum('PUBMED') NOT NULL,
  `external_id` varchar(64) NOT NULL,
  `author_email` varchar(128) DEFAULT NULL,
  `author_contacted` tinyint(1) DEFAULT NULL,
  `author_contact_date` datetime DEFAULT NULL,
  `author_list` text DEFAULT NULL,
  `title` varchar(512) DEFAULT NULL,
  `contents` text DEFAULT NULL,
  `journal_name` varchar(256) DEFAULT NULL,
  `year` int(10) unsigned DEFAULT NULL,
  `month` varchar(32) DEFAULT NULL,
  `volume` varchar(32) DEFAULT NULL,
  `issue` varchar(32) DEFAULT NULL,
  `pages` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`citation_id`),
  UNIQUE KEY `citation_type` (`citation_type`,`external_id`),
  KEY `external_id` (`external_id`(16))
) ENGINE=InnoDB AUTO_INCREMENT=2210 DEFAULT CHARSET=utf8 COMMENT='Document citations, typically pulled from PubMed';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `DevelopmentalStage`
--

DROP TABLE IF EXISTS `DevelopmentalStage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DevelopmentalStage` (
  `stage_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `species_id` int(10) unsigned NOT NULL,
  `term` varchar(255) NOT NULL DEFAULT '',
  `identifier` varchar(64) NOT NULL,
  `is_deprecated` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`stage_id`),
  KEY `species_id` (`species_id`),
  KEY `stage_id` (`stage_id`,`species_id`),
  KEY `identifier` (`identifier`),
  CONSTRAINT `DevelopmentalStage_ibfk_1` FOREIGN KEY (`species_id`) REFERENCES `Species` (`species_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=268 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `EvidenceSubtypeTerm`
--

DROP TABLE IF EXISTS `EvidenceSubtypeTerm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EvidenceSubtypeTerm` (
  `evidence_subtype_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `term` varchar(255) NOT NULL,
  PRIMARY KEY (`evidence_subtype_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `EvidenceTerm`
--

DROP TABLE IF EXISTS `EvidenceTerm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EvidenceTerm` (
  `evidence_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `term` varchar(255) NOT NULL,
  PRIMARY KEY (`evidence_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ExpressionTerm`
--

DROP TABLE IF EXISTS `ExpressionTerm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ExpressionTerm` (
  `term_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `species_id` int(10) unsigned NOT NULL,
  `term` varchar(255) NOT NULL DEFAULT '',
  `identifier` varchar(64) NOT NULL,
  `is_deprecated` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`term_id`),
  KEY `species` (`term_id`,`species_id`),
  KEY `species_id` (`species_id`),
  KEY `identifier` (`identifier`),
  CONSTRAINT `fk_{76ED8668-409F-4CB3-81ED-952F2F8EBFDB}` FOREIGN KEY (`species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=19282 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Features`
--

DROP TABLE IF EXISTS `Features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Features` (
  `feature_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sequence_from_species_id` int(10) unsigned NOT NULL,
  `gene_id` int(10) unsigned DEFAULT NULL,
  `type` enum('cds','exon','five_prime_utr','intron','mrna','ncrna','pseudogene','snorna','snrna','three_prime_utr','trna') DEFAULT NULL,
  `start` int(11) DEFAULT NULL COMMENT 'Must not be unsigned due to feature distance calculations',
  `end` int(11) DEFAULT NULL COMMENT 'Must not be unsigned due to feature distance calculations',
  `strand` varchar(1) DEFAULT NULL,
  `identifier` varchar(64) NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  `parent` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`feature_id`),
  KEY `gene_id` (`gene_id`),
  KEY `type` (`type`),
  KEY `sequence_from_species_id` (`sequence_from_species_id`),
  KEY `identifier` (`identifier`),
  CONSTRAINT `Features_ibfk_1` FOREIGN KEY (`gene_id`) REFERENCES `Gene` (`gene_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `Features_ibfk_2` FOREIGN KEY (`sequence_from_species_id`) REFERENCES `Species` (`species_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Gene features extracted from the FlyBase dmel GFF file';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Gene`
--

DROP TABLE IF EXISTS `Gene`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gene` (
  `gene_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `species_id` int(10) unsigned NOT NULL,
  `genome_assembly_id` int(10) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `identifier` varchar(64) NOT NULL,
  `start` int(11) NOT NULL,
  `stop` int(11) NOT NULL,
  `strand` varchar(1) NOT NULL,
  `chrm_id` int(10) unsigned NOT NULL DEFAULT 16,
  PRIMARY KEY (`gene_id`),
  KEY `species` (`species_id`),
  KEY `chrm_id` (`chrm_id`),
  KEY `identifier` (`identifier`),
  KEY `genome_assembly_id` (`genome_assembly_id`),
  CONSTRAINT `Gene_ibfk_1` FOREIGN KEY (`chrm_id`) REFERENCES `Chromosome` (`chromosome_id`),
  CONSTRAINT `Gene_ibfk_2` FOREIGN KEY (`genome_assembly_id`) REFERENCES `GenomeAssembly` (`genome_assembly_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_{CDDFC633-C07B-4C56-9C80-07E705CF810E}` FOREIGN KEY (`species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=699925 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `GenomeAssembly`
--

DROP TABLE IF EXISTS `GenomeAssembly`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GenomeAssembly` (
  `genome_assembly_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `species_id` int(10) unsigned NOT NULL,
  `release_version` varchar(32) NOT NULL,
  `is_deprecated` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`genome_assembly_id`),
  KEY `species_id` (`species_id`),
  KEY `release_version` (`release_version`),
  CONSTRAINT `GenomeAssembly_ibfk_1` FOREIGN KEY (`species_id`) REFERENCES `Species` (`species_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `PredictedCRM`
--

DROP TABLE IF EXISTS `PredictedCRM`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PredictedCRM` (
  `predicted_crm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sequence_from_species_id` int(10) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `pubmed_id` varchar(64) NOT NULL,
  `state` enum('approval','approved','archived','current','deleted','editing') DEFAULT NULL,
  `version` smallint(6) DEFAULT NULL,
  `chromosome_id` int(10) unsigned NOT NULL,
  `evidence_id` int(10) unsigned NOT NULL,
  `evidence_subtype_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `curator_id` int(10) unsigned DEFAULT NULL,
  `date_added` datetime DEFAULT current_timestamp(),
  `auditor_id` int(10) unsigned DEFAULT NULL,
  `last_audit` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `archive_date` datetime DEFAULT NULL,
  `sequence` text DEFAULT NULL,
  `sequence_source_id` int(10) unsigned NOT NULL,
  `size` int(10) unsigned DEFAULT NULL,
  `current_genome_assembly_release_version` varchar(32) DEFAULT '',
  `current_start` int(11) DEFAULT 0,
  `current_end` int(11) DEFAULT 0,
  `archived_genome_assembly_release_versions` varchar(256) DEFAULT '',
  `archived_starts` varchar(512) DEFAULT '',
  `archived_ends` varchar(512) DEFAULT '',
  `gene_identifiers` text DEFAULT '',
  `gene_locus` text DEFAULT '',
  `has_crm` tinyint(1) NOT NULL DEFAULT 0,
  `has_tfbs` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`predicted_crm_id`),
  KEY `species_id` (`sequence_from_species_id`),
  KEY `name` (`name`),
  KEY `state` (`state`),
  KEY `chromosome_id` (`chromosome_id`),
  KEY `evidence_id` (`evidence_id`),
  KEY `subtype_id` (`evidence_subtype_id`),
  KEY `entity_id` (`entity_id`,`version`),
  KEY `curator_id` (`curator_id`),
  KEY `auditor_id` (`auditor_id`),
  KEY `source_id` (`sequence_source_id`),
  KEY `pubmed_id` (`pubmed_id`),
  CONSTRAINT `PredictedCRM_ibfk_10` FOREIGN KEY (`chromosome_id`) REFERENCES `Chromosome` (`chromosome_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `PredictedCRM_ibfk_3` FOREIGN KEY (`evidence_id`) REFERENCES `EvidenceTerm` (`evidence_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `PredictedCRM_ibfk_4` FOREIGN KEY (`evidence_subtype_id`) REFERENCES `EvidenceSubtypeTerm` (`evidence_subtype_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `PredictedCRM_ibfk_5` FOREIGN KEY (`curator_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `PredictedCRM_ibfk_6` FOREIGN KEY (`auditor_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `PredictedCRM_ibfk_7` FOREIGN KEY (`sequence_source_id`) REFERENCES `SequenceSourceTerm` (`source_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `PredictedCRM_ibfk_8` FOREIGN KEY (`sequence_from_species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=16415 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `PredictedCRM_has_Expression_Term`
--

DROP TABLE IF EXISTS `PredictedCRM_has_Expression_Term`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PredictedCRM_has_Expression_Term` (
  `predicted_crm_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  KEY `pcrm_id` (`predicted_crm_id`),
  KEY `term_id` (`term_id`),
  CONSTRAINT `PredictedCRM_has_Expression_Term_ibfk_1` FOREIGN KEY (`predicted_crm_id`) REFERENCES `PredictedCRM` (`predicted_crm_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `PredictedCRM_has_Expression_Term_ibfk_2` FOREIGN KEY (`term_id`) REFERENCES `ExpressionTerm` (`term_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `RC_associated_BS`
--

DROP TABLE IF EXISTS `RC_associated_BS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RC_associated_BS` (
  `rc_id` int(10) unsigned NOT NULL DEFAULT 0,
  `tfbs_id` int(10) unsigned NOT NULL,
  KEY `rc_id` (`rc_id`),
  KEY `tfbs_id` (`tfbs_id`),
  CONSTRAINT `RC_associated_BS_ibfk_1` FOREIGN KEY (`rc_id`) REFERENCES `ReporterConstruct` (`rc_id`) ON DELETE CASCADE,
  CONSTRAINT `RC_associated_BS_ibfk_2` FOREIGN KEY (`tfbs_id`) REFERENCES `BindingSite` (`tfbs_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `RC_has_ExprTerm`
--

DROP TABLE IF EXISTS `RC_has_ExprTerm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RC_has_ExprTerm` (
  `rc_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`rc_id`,`term_id`),
  KEY `term_id` (`term_id`),
  CONSTRAINT `RC_has_ExprTerm_ibfk_1` FOREIGN KEY (`rc_id`) REFERENCES `ReporterConstruct` (`rc_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `RC_has_ExprTerm_ibfk_2` FOREIGN KEY (`term_id`) REFERENCES `ExpressionTerm` (`term_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `RC_has_FigureLabel`
--

DROP TABLE IF EXISTS `RC_has_FigureLabel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RC_has_FigureLabel` (
  `rc_id` int(10) unsigned NOT NULL,
  `label` varchar(32) NOT NULL,
  PRIMARY KEY (`rc_id`,`label`),
  KEY `rc_id` (`rc_id`),
  KEY `label` (`label`),
  CONSTRAINT `RC_has_FigureLabel_ibfk_1` FOREIGN KEY (`rc_id`) REFERENCES `ReporterConstruct` (`rc_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ReporterConstruct`
--

DROP TABLE IF EXISTS `ReporterConstruct`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ReporterConstruct` (
  `rc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sequence_from_species_id` int(10) unsigned NOT NULL,
  `assayed_in_species_id` int(10) unsigned NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `pubmed_id` varchar(64) NOT NULL,
  `state` enum('approval','approved','archived','current','deleted','editing') DEFAULT NULL,
  `version` smallint(6) NOT NULL DEFAULT 0,
  `gene_id` int(10) unsigned NOT NULL,
  `chromosome_id` int(10) unsigned NOT NULL,
  `evidence_id` int(10) unsigned NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `figure_labels` text DEFAULT NULL,
  `has_flyexpress_images` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `is_crm` tinyint(1) NOT NULL DEFAULT 0,
  `is_override` tinyint(1) NOT NULL DEFAULT 0,
  `is_negative` tinyint(1) NOT NULL DEFAULT 0,
  `is_minimalized` tinyint(1) NOT NULL DEFAULT 0,
  `fbtp` varchar(64) DEFAULT NULL,
  `fbal` varchar(64) DEFAULT NULL,
  `curator_id` int(10) unsigned DEFAULT NULL,
  `date_added` datetime DEFAULT current_timestamp(),
  `auditor_id` int(10) unsigned DEFAULT NULL,
  `last_audit` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `archive_date` datetime DEFAULT NULL,
  `sequence` text DEFAULT NULL,
  `sequence_source_id` int(10) unsigned NOT NULL,
  `size` int(10) unsigned DEFAULT NULL,
  `current_genome_assembly_release_version` varchar(32) DEFAULT '',
  `current_start` int(11) DEFAULT 0,
  `current_end` int(11) DEFAULT 0,
  `archived_genome_assembly_release_versions` varchar(256) DEFAULT '',
  `archived_starts` varchar(512) DEFAULT '',
  `archived_ends` varchar(512) DEFAULT '',
  `has_tfbs` tinyint(1) NOT NULL DEFAULT 0,
  `cell_culture_only` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rc_id`),
  KEY `species_id` (`sequence_from_species_id`),
  KEY `gene` (`gene_id`),
  KEY `chromosome` (`chromosome_id`),
  KEY `evidence` (`evidence_id`),
  KEY `curator` (`curator_id`),
  KEY `auditor` (`auditor_id`),
  KEY `seqsource` (`sequence_source_id`),
  KEY `state` (`state`),
  KEY `location` (`chromosome_id`),
  KEY `entity_id` (`entity_id`,`version`),
  KEY `minimization` (`chromosome_id`,`state`),
  KEY `assayed_in_species_id` (`assayed_in_species_id`),
  KEY `pubmed_id` (`pubmed_id`),
  CONSTRAINT `ReporterConstruct_ibfk_1` FOREIGN KEY (`sequence_from_species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `ReporterConstruct_ibfk_2` FOREIGN KEY (`assayed_in_species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_{2D448E5D-1329-4031-83DC-D568F3510DF0}` FOREIGN KEY (`curator_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_{2FBCDCDB-B49F-4542-921C-D05F93B442B5}` FOREIGN KEY (`evidence_id`) REFERENCES `EvidenceTerm` (`evidence_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_{81208A04-0AD3-499C-9862-50B9B930F93F}` FOREIGN KEY (`auditor_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_{B5CF8D06-B5F8-4553-BF62-1DDA2C25233F}` FOREIGN KEY (`gene_id`) REFERENCES `Gene` (`gene_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_{C2EDB40C-47C4-4079-B283-2B776F996ACC}` FOREIGN KEY (`chromosome_id`) REFERENCES `Chromosome` (`chromosome_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_{F53FB70F-C2AD-45CB-BEB3-002484A019BC}` FOREIGN KEY (`sequence_source_id`) REFERENCES `SequenceSourceTerm` (`source_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=129415 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `SequenceSourceTerm`
--

DROP TABLE IF EXISTS `SequenceSourceTerm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SequenceSourceTerm` (
  `source_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `term` varchar(255) NOT NULL,
  PRIMARY KEY (`source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Species`
--

DROP TABLE IF EXISTS `Species`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Species` (
  `species_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `scientific_name` varchar(255) NOT NULL,
  `short_name` varchar(32) NOT NULL,
  `public_database_names` text DEFAULT NULL,
  `public_database_links` text DEFAULT NULL,
  `public_browser_names` text DEFAULT NULL,
  `public_browser_links` text DEFAULT NULL,
  PRIMARY KEY (`species_id`),
  KEY `index2` (`short_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `password` varchar(64) DEFAULT NULL,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `email` varchar(128) NOT NULL,
  `date_added` timestamp NULL DEFAULT NULL,
  `state` enum('active','disabled','confirm') NOT NULL DEFAULT 'disabled',
  `role` enum('admin','curator') NOT NULL DEFAULT 'curator',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='List of curators and auditors';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ext_FlyExpressImage`
--

DROP TABLE IF EXISTS `ext_FlyExpressImage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ext_FlyExpressImage` (
  `pubmed_id` varchar(64) NOT NULL,
  `label` varchar(32) NOT NULL,
  PRIMARY KEY (`pubmed_id`,`label`),
  KEY `pubmed_id` (`pubmed_id`(16))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `icrm_has_expr_term`
--

DROP TABLE IF EXISTS `icrm_has_expr_term`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icrm_has_expr_term` (
  `icrm_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  KEY `icrm_id` (`icrm_id`),
  KEY `term_id` (`term_id`),
  CONSTRAINT `icrm_has_expr_term_ibfk_1` FOREIGN KEY (`icrm_id`) REFERENCES `inferred_crm` (`icrm_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `icrm_has_expr_term_ibfk_2` FOREIGN KEY (`term_id`) REFERENCES `ExpressionTerm` (`term_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `icrm_has_rc`
--

DROP TABLE IF EXISTS `icrm_has_rc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icrm_has_rc` (
  `icrm_id` int(10) unsigned NOT NULL,
  `rc_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`icrm_id`,`rc_id`),
  KEY `icrm_id` (`icrm_id`),
  KEY `rc_id` (`rc_id`),
  CONSTRAINT `icrm_has_rc_ibfk_1` FOREIGN KEY (`icrm_id`) REFERENCES `inferred_crm` (`icrm_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `icrm_has_rc_ibfk_2` FOREIGN KEY (`rc_id`) REFERENCES `ReporterConstruct` (`rc_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inferred_crm`
--

DROP TABLE IF EXISTS `inferred_crm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inferred_crm` (
  `icrm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sequence_from_species_id` int(10) unsigned NOT NULL,
  `assayed_in_species_id` int(10) unsigned NOT NULL,
  `size` int(10) unsigned DEFAULT NULL,
  `current_genome_assembly_release_version` varchar(32) DEFAULT '',
  `current_start` int(11) DEFAULT 0,
  `current_end` int(11) DEFAULT 0,
  `archived_genome_assembly_release_versions` varchar(256) DEFAULT '',
  `archived_starts` varchar(512) DEFAULT '',
  `archived_ends` varchar(512) DEFAULT '',
  `chromosome_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`icrm_id`),
  KEY `chromosome_id` (`chromosome_id`),
  KEY `sequence_from_species_id` (`sequence_from_species_id`),
  KEY `assayed_in_species_id` (`assayed_in_species_id`),
  CONSTRAINT `inferred_crm_ibfk_2` FOREIGN KEY (`sequence_from_species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `inferred_crm_ibfk_3` FOREIGN KEY (`assayed_in_species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `inferred_crm_ibfk_4` FOREIGN KEY (`chromosome_id`) REFERENCES `Chromosome` (`chromosome_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=182478 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inferred_crm_read_model`
--

DROP TABLE IF EXISTS `inferred_crm_read_model`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inferred_crm_read_model` (
  `id` int(10) unsigned NOT NULL,
  `sequence_from_species_id` int(10) unsigned NOT NULL,
  `assayed_in_species_id` int(10) unsigned NOT NULL,
  `gene` text DEFAULT NULL,
  `gene_locus` text DEFAULT NULL,
  `chromosome_id` int(10) unsigned NOT NULL,
  `chromosome` varchar(255) DEFAULT NULL,
  `current_start` int(10) unsigned DEFAULT NULL,
  `current_end` int(10) unsigned DEFAULT NULL,
  `size` int(10) unsigned NOT NULL,
  `coordinates` varchar(255) DEFAULT NULL,
  `components` text DEFAULT NULL,
  `expressions` text DEFAULT NULL,
  `expression_identifiers` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sequence_from_species_id` (`sequence_from_species_id`),
  KEY `assayed_in_species_id` (`assayed_in_species_id`),
  KEY `chromosome_id` (`chromosome_id`),
  CONSTRAINT `inferred_crm_read_model_ibfk_1` FOREIGN KEY (`sequence_from_species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `inferred_crm_read_model_ibfk_2` FOREIGN KEY (`assayed_in_species_id`) REFERENCES `Species` (`species_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `inferred_crm_read_model_ibfk_3` FOREIGN KEY (`chromosome_id`) REFERENCES `Chromosome` (`chromosome_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staging_biological_process_update`
--

DROP TABLE IF EXISTS `staging_biological_process_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staging_biological_process_update` (
  `term` varchar(255) NOT NULL,
  `go_id` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staging_developmental_stage_update`
--

DROP TABLE IF EXISTS `staging_developmental_stage_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staging_developmental_stage_update` (
  `species_short_name` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `term` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staging_expression_update`
--

DROP TABLE IF EXISTS `staging_expression_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staging_expression_update` (
  `species_short_name` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `term` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staging_feature_update`
--

DROP TABLE IF EXISTS `staging_feature_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staging_feature_update` (
  `species_short_name` varchar(255) NOT NULL,
  `type` enum('cds','exon','five_prime_utr','intron','mrna','ncrna','pseudogene','snorna','snrna','three_prime_utr','trna') NOT NULL,
  `start` int(11) NOT NULL,
  `end` int(11) NOT NULL,
  `strand` varchar(1) NOT NULL,
  `identifier` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `parent` varchar(32) NOT NULL,
  KEY `type` (`type`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staging_gene_update`
--

DROP TABLE IF EXISTS `staging_gene_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staging_gene_update` (
  `species_id` int(10) unsigned DEFAULT NULL,
  `species_short_name` varchar(255) NOT NULL,
  `genome_assembly_id` int(10) unsigned DEFAULT NULL,
  `genome_assembly_release_version` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `chromosome_id` int(10) unsigned DEFAULT NULL,
  `chromosome_name` varchar(255) NOT NULL,
  `start` int(11) NOT NULL,
  `end` int(11) NOT NULL,
  `strand` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `triplestore_crm_segment`
--

DROP TABLE IF EXISTS `triplestore_crm_segment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `triplestore_crm_segment` (
  `ts_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `crm_segment_id` int(10) unsigned NOT NULL,
  `expression` varchar(32) NOT NULL,
  `pubmed_id` varchar(64) NOT NULL,
  `stage_on` varchar(32) NOT NULL DEFAULT 'none',
  `stage_off` varchar(32) NOT NULL DEFAULT 'none',
  `biological_process` varchar(32) NOT NULL DEFAULT '' CHECK (`biological_process` regexp '^GO:[0-9]{7}$|^$'),
  `sex` enum('m','f','both') NOT NULL DEFAULT 'both',
  `ectopic` tinyint(1) NOT NULL DEFAULT 0,
  `silencer` enum('enhancer','silencer') NOT NULL DEFAULT 'enhancer',
  PRIMARY KEY (`ts_id`),
  KEY `crm_segment_id` (`crm_segment_id`),
  KEY `expression` (`expression`),
  KEY `stage_on` (`stage_on`),
  KEY `stage_off` (`stage_off`),
  KEY `biological_process` (`biological_process`),
  KEY `pubmed_id` (`pubmed_id`),
  CONSTRAINT `triplestore_crm_segment_ibfk_1` FOREIGN KEY (`crm_segment_id`) REFERENCES `CRMSegment` (`crm_segment_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `triplestore_predicted_crm`
--

DROP TABLE IF EXISTS `triplestore_predicted_crm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `triplestore_predicted_crm` (
  `ts_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `predicted_crm_id` int(10) unsigned NOT NULL,
  `expression` varchar(32) NOT NULL,
  `pubmed_id` varchar(64) NOT NULL,
  `stage_on` varchar(32) NOT NULL DEFAULT 'none',
  `stage_off` varchar(32) NOT NULL DEFAULT 'none',
  `biological_process` varchar(32) NOT NULL DEFAULT '' CHECK (`biological_process` regexp '^GO:[0-9]{7}$|^$'),
  `sex` enum('m','f','both') NOT NULL DEFAULT 'both',
  `silencer` enum('enhancer','silencer') NOT NULL DEFAULT 'enhancer',
  PRIMARY KEY (`ts_id`),
  KEY `pcrm_id` (`predicted_crm_id`),
  KEY `expression` (`expression`),
  KEY `stage_on` (`stage_on`),
  KEY `stage_off` (`stage_off`),
  KEY `biological_process` (`biological_process`),
  KEY `pubmed_id` (`pubmed_id`),
  CONSTRAINT `triplestore_predicted_crm_ibfk_1` FOREIGN KEY (`predicted_crm_id`) REFERENCES `PredictedCRM` (`predicted_crm_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7443 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `triplestore_rc`
--

DROP TABLE IF EXISTS `triplestore_rc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `triplestore_rc` (
  `ts_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rc_id` int(10) unsigned NOT NULL,
  `expression` varchar(32) NOT NULL,
  `pubmed_id` varchar(64) NOT NULL,
  `stage_on` varchar(32) NOT NULL DEFAULT 'none',
  `stage_off` varchar(32) NOT NULL DEFAULT 'none',
  `biological_process` varchar(32) NOT NULL DEFAULT '' CHECK (`biological_process` regexp '^GO:[0-9]{7}$|^$'),
  `sex` enum('m','f','both') NOT NULL DEFAULT 'both',
  `ectopic` tinyint(1) NOT NULL DEFAULT 0,
  `silencer` enum('enhancer','silencer') NOT NULL DEFAULT 'enhancer',
  PRIMARY KEY (`ts_id`),
  KEY `rc_id` (`rc_id`),
  KEY `expression` (`expression`),
  KEY `stage_on` (`stage_on`),
  KEY `stage_off` (`stage_off`),
  KEY `biological_process` (`biological_process`),
  KEY `pubmed_id` (`pubmed_id`),
  CONSTRAINT `triplestore_rc_ibfk_1` FOREIGN KEY (`rc_id`) REFERENCES `ReporterConstruct` (`rc_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=90557 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `v_cis_regulatory_module_overlaps`
--

DROP TABLE IF EXISTS `v_cis_regulatory_module_overlaps`;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_overlaps`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_cis_regulatory_module_overlaps` (
  `rc_id` tinyint NOT NULL,
  `overlap_id` tinyint NOT NULL,
  `sequence_from_species_id` tinyint NOT NULL,
  `current_genome_assembly_release_version` tinyint NOT NULL,
  `chromosome_id` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `assayed_in_species_id` tinyint NOT NULL,
  `terms` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_cis_regulatory_module_segment_audit`
--

DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_audit`;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_audit`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_cis_regulatory_module_segment_audit` (
  `id` tinyint NOT NULL,
  `state` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `curator_id` tinyint NOT NULL,
  `curator_username` tinyint NOT NULL,
  `curator_full_name` tinyint NOT NULL,
  `auditor_id` tinyint NOT NULL,
  `auditor_username` tinyint NOT NULL,
  `auditor_full_name` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `assayed_in_species_scientific_name` tinyint NOT NULL,
  `gene_display` tinyint NOT NULL,
  `chromosome_display` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `coordinates` tinyint NOT NULL,
  `sequence` tinyint NOT NULL,
  `fbtp` tinyint NOT NULL,
  `figure_labels` tinyint NOT NULL,
  `evidence` tinyint NOT NULL,
  `evidence_subtype` tinyint NOT NULL,
  `anatomical_expression_identifiers` tinyint NOT NULL,
  `anatomical_expression_terms` tinyint NOT NULL,
  `anatomical_expression_displays` tinyint NOT NULL,
  `notes` tinyint NOT NULL,
  `sequence_source` tinyint NOT NULL,
  `date_added` tinyint NOT NULL,
  `last_update` tinyint NOT NULL,
  `last_audit` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_cis_regulatory_module_segment_feature_location`
--

DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_feature_location`;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_feature_location`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_cis_regulatory_module_segment_feature_location` (
  `id` tinyint NOT NULL,
  `type` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `parent` tinyint NOT NULL,
  `feature_id` tinyint NOT NULL,
  `identifier` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `f_start` tinyint NOT NULL,
  `f_end` tinyint NOT NULL,
  `strand` tinyint NOT NULL,
  `relative_start` tinyint NOT NULL,
  `relative_end` tinyint NOT NULL,
  `start_dist` tinyint NOT NULL,
  `end_dist` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_cis_regulatory_module_segment_file`
--

DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_file`;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_file`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_cis_regulatory_module_segment_file` (
  `redfly_id` tinyint NOT NULL,
  `redfly_id_unversioned` tinyint NOT NULL,
  `crm_segment_id` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `fbtp` tinyint NOT NULL,
  `label` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `assayed_in_species_scientific_name` tinyint NOT NULL,
  `gene_name` tinyint NOT NULL,
  `gene_identifier` tinyint NOT NULL,
  `sequence` tinyint NOT NULL,
  `evidence_term` tinyint NOT NULL,
  `evidence_subtype_term` tinyint NOT NULL,
  `chromosome` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `ontology_term` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_cis_regulatory_module_segment_no_ts_audit`
--

DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_no_ts_audit`;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_no_ts_audit`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_cis_regulatory_module_segment_no_ts_audit` (
  `id` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `assayed_in_species_scientific_name` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `curator_full_name` tinyint NOT NULL,
  `state` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `chromosome_display` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `gene_display` tinyint NOT NULL,
  `anatomical_expression_display` tinyint NOT NULL,
  `on_developmental_stage_display` tinyint NOT NULL,
  `off_developmental_stage_display` tinyint NOT NULL,
  `biological_process_display` tinyint NOT NULL,
  `sex` tinyint NOT NULL,
  `ectopic` tinyint NOT NULL,
  `enhancer_or_silencer` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_cis_regulatory_module_segment_staging_data_file`
--

DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_staging_data_file`;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_staging_data_file`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_cis_regulatory_module_segment_staging_data_file` (
  `entity_type` tinyint NOT NULL,
  `parent_id` tinyint NOT NULL,
  `parent_pubmed_id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `expression_identifier` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `stage_on_identifier` tinyint NOT NULL,
  `stage_off_identifier` tinyint NOT NULL,
  `biological_process_identifier` tinyint NOT NULL,
  `sex` tinyint NOT NULL,
  `ectopic` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_cis_regulatory_module_segment_ts_audit`
--

DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_ts_audit`;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_ts_audit`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_cis_regulatory_module_segment_ts_audit` (
  `id` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `assayed_in_species_scientific_name` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `curator_full_name` tinyint NOT NULL,
  `state` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `chromosome_display` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `gene_display` tinyint NOT NULL,
  `anatomical_expression_display` tinyint NOT NULL,
  `on_developmental_stage_display` tinyint NOT NULL,
  `off_developmental_stage_display` tinyint NOT NULL,
  `biological_process_display` tinyint NOT NULL,
  `sex` tinyint NOT NULL,
  `ectopic` tinyint NOT NULL,
  `enhancer_or_silencer` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_cis_regulatory_module_segment_ts_notify_author`
--

DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_ts_notify_author`;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_ts_notify_author`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_cis_regulatory_module_segment_ts_notify_author` (
  `crm_segment_id` tinyint NOT NULL,
  `expression_identifier` tinyint NOT NULL,
  `stage_on_term` tinyint NOT NULL,
  `stage_off_term` tinyint NOT NULL,
  `biological_process_term` tinyint NOT NULL,
  `sex_term` tinyint NOT NULL,
  `ectopic_term` tinyint NOT NULL,
  `enhancer_or_silencer` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_inferred_cis_regulatory_module_audit`
--

DROP TABLE IF EXISTS `v_inferred_cis_regulatory_module_audit`;
/*!50001 DROP VIEW IF EXISTS `v_inferred_cis_regulatory_module_audit`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_inferred_cis_regulatory_module_audit` (
  `id` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `assayed_in_species_scientific_name` tinyint NOT NULL,
  `chromosome` tinyint NOT NULL,
  `chromosome_display` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `coordinates` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_predicted_cis_regulatory_module_audit`
--

DROP TABLE IF EXISTS `v_predicted_cis_regulatory_module_audit`;
/*!50001 DROP VIEW IF EXISTS `v_predicted_cis_regulatory_module_audit`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_predicted_cis_regulatory_module_audit` (
  `id` tinyint NOT NULL,
  `state` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `curator_id` tinyint NOT NULL,
  `curator_username` tinyint NOT NULL,
  `curator_full_name` tinyint NOT NULL,
  `auditor_id` tinyint NOT NULL,
  `auditor_username` tinyint NOT NULL,
  `auditor_full_name` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `chromosome_display` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `coordinates` tinyint NOT NULL,
  `sequence` tinyint NOT NULL,
  `evidence` tinyint NOT NULL,
  `evidence_subtype` tinyint NOT NULL,
  `anatomical_expression_displays` tinyint NOT NULL,
  `notes` tinyint NOT NULL,
  `sequence_source` tinyint NOT NULL,
  `date_added` tinyint NOT NULL,
  `last_update` tinyint NOT NULL,
  `last_audit` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_predicted_cis_regulatory_module_file`
--

DROP TABLE IF EXISTS `v_predicted_cis_regulatory_module_file`;
/*!50001 DROP VIEW IF EXISTS `v_predicted_cis_regulatory_module_file`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_predicted_cis_regulatory_module_file` (
  `redfly_id` tinyint NOT NULL,
  `redfly_id_unversioned` tinyint NOT NULL,
  `predicted_crm_id` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `label` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `gene_locus` tinyint NOT NULL,
  `gene_identifiers` tinyint NOT NULL,
  `sequence` tinyint NOT NULL,
  `evidence_term` tinyint NOT NULL,
  `evidence_subtype_term` tinyint NOT NULL,
  `chromosome` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `ontology_term` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_predicted_cis_regulatory_module_no_ts_audit`
--

DROP TABLE IF EXISTS `v_predicted_cis_regulatory_module_no_ts_audit`;
/*!50001 DROP VIEW IF EXISTS `v_predicted_cis_regulatory_module_no_ts_audit`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_predicted_cis_regulatory_module_no_ts_audit` (
  `id` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `curator_full_name` tinyint NOT NULL,
  `state` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `chromosome_display` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `anatomical_expression_display` tinyint NOT NULL,
  `on_developmental_stage_display` tinyint NOT NULL,
  `off_developmental_stage_display` tinyint NOT NULL,
  `biological_process_display` tinyint NOT NULL,
  `sex` tinyint NOT NULL,
  `ectopic` tinyint NOT NULL,
  `enhancer_or_silencer` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_predicted_cis_regulatory_module_staging_data_file`
--

DROP TABLE IF EXISTS `v_predicted_cis_regulatory_module_staging_data_file`;
/*!50001 DROP VIEW IF EXISTS `v_predicted_cis_regulatory_module_staging_data_file`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_predicted_cis_regulatory_module_staging_data_file` (
  `entity_type` tinyint NOT NULL,
  `parent_id` tinyint NOT NULL,
  `parent_pubmed_id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `expression_identifier` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `stage_on_identifier` tinyint NOT NULL,
  `stage_off_identifier` tinyint NOT NULL,
  `biological_process_identifier` tinyint NOT NULL,
  `sex` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_predicted_cis_regulatory_module_ts_audit`
--

DROP TABLE IF EXISTS `v_predicted_cis_regulatory_module_ts_audit`;
/*!50001 DROP VIEW IF EXISTS `v_predicted_cis_regulatory_module_ts_audit`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_predicted_cis_regulatory_module_ts_audit` (
  `id` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `curator_full_name` tinyint NOT NULL,
  `state` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `chromosome_display` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `anatomical_expression_display` tinyint NOT NULL,
  `on_developmental_stage_display` tinyint NOT NULL,
  `off_developmental_stage_display` tinyint NOT NULL,
  `biological_process_display` tinyint NOT NULL,
  `sex` tinyint NOT NULL,
  `enhancer_or_silencer` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_predicted_cis_regulatory_module_ts_notify_author`
--

DROP TABLE IF EXISTS `v_predicted_cis_regulatory_module_ts_notify_author`;
/*!50001 DROP VIEW IF EXISTS `v_predicted_cis_regulatory_module_ts_notify_author`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_predicted_cis_regulatory_module_ts_notify_author` (
  `predicted_crm_id` tinyint NOT NULL,
  `expression_identifier` tinyint NOT NULL,
  `stage_on_term` tinyint NOT NULL,
  `stage_off_term` tinyint NOT NULL,
  `biological_process_term` tinyint NOT NULL,
  `sex_term` tinyint NOT NULL,
  `enhancer_or_silencer` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_reporter_construct_audit`
--

DROP TABLE IF EXISTS `v_reporter_construct_audit`;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_audit`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_reporter_construct_audit` (
  `id` tinyint NOT NULL,
  `state` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `curator_id` tinyint NOT NULL,
  `curator_username` tinyint NOT NULL,
  `curator_full_name` tinyint NOT NULL,
  `auditor_id` tinyint NOT NULL,
  `auditor_username` tinyint NOT NULL,
  `auditor_full_name` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `assayed_in_species_scientific_name` tinyint NOT NULL,
  `gene_display` tinyint NOT NULL,
  `chromosome_display` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `coordinates` tinyint NOT NULL,
  `sequence` tinyint NOT NULL,
  `fbtp` tinyint NOT NULL,
  `figure_labels` tinyint NOT NULL,
  `evidence` tinyint NOT NULL,
  `anatomical_expression_identifiers` tinyint NOT NULL,
  `anatomical_expression_terms` tinyint NOT NULL,
  `anatomical_expression_displays` tinyint NOT NULL,
  `notes` tinyint NOT NULL,
  `sequence_source` tinyint NOT NULL,
  `date_added` tinyint NOT NULL,
  `last_update` tinyint NOT NULL,
  `last_audit` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_reporter_construct_feature_location`
--

DROP TABLE IF EXISTS `v_reporter_construct_feature_location`;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_feature_location`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_reporter_construct_feature_location` (
  `id` tinyint NOT NULL,
  `type` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `parent` tinyint NOT NULL,
  `feature_id` tinyint NOT NULL,
  `identifier` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `f_start` tinyint NOT NULL,
  `f_end` tinyint NOT NULL,
  `strand` tinyint NOT NULL,
  `relative_start` tinyint NOT NULL,
  `relative_end` tinyint NOT NULL,
  `start_dist` tinyint NOT NULL,
  `end_dist` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_reporter_construct_file`
--

DROP TABLE IF EXISTS `v_reporter_construct_file`;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_file`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_reporter_construct_file` (
  `redfly_id` tinyint NOT NULL,
  `redfly_id_unversioned` tinyint NOT NULL,
  `rc_id` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `fbtp` tinyint NOT NULL,
  `label` tinyint NOT NULL,
  `is_crm` tinyint NOT NULL,
  `cell_culture_only` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `assayed_in_species_scientific_name` tinyint NOT NULL,
  `gene_name` tinyint NOT NULL,
  `gene_identifier` tinyint NOT NULL,
  `sequence` tinyint NOT NULL,
  `evidence_term` tinyint NOT NULL,
  `chromosome` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `associated_tfbs` tinyint NOT NULL,
  `ontology_term` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_reporter_construct_no_ts_audit`
--

DROP TABLE IF EXISTS `v_reporter_construct_no_ts_audit`;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_no_ts_audit`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_reporter_construct_no_ts_audit` (
  `id` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `assayed_in_species_scientific_name` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `curator_full_name` tinyint NOT NULL,
  `state` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `chromosome_display` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `gene_display` tinyint NOT NULL,
  `anatomical_expression_display` tinyint NOT NULL,
  `on_developmental_stage_display` tinyint NOT NULL,
  `off_developmental_stage_display` tinyint NOT NULL,
  `biological_process_display` tinyint NOT NULL,
  `sex` tinyint NOT NULL,
  `ectopic` tinyint NOT NULL,
  `enhancer_or_silencer` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_reporter_construct_staging_data_file`
--

DROP TABLE IF EXISTS `v_reporter_construct_staging_data_file`;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_staging_data_file`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_reporter_construct_staging_data_file` (
  `entity_type` tinyint NOT NULL,
  `parent_id` tinyint NOT NULL,
  `parent_pubmed_id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `expression_identifier` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `stage_on_identifier` tinyint NOT NULL,
  `stage_off_identifier` tinyint NOT NULL,
  `biological_process_identifier` tinyint NOT NULL,
  `sex` tinyint NOT NULL,
  `ectopic` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_reporter_construct_ts_audit`
--

DROP TABLE IF EXISTS `v_reporter_construct_ts_audit`;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_ts_audit`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_reporter_construct_ts_audit` (
  `id` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `assayed_in_species_scientific_name` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `curator_full_name` tinyint NOT NULL,
  `state` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `chromosome_display` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `gene_display` tinyint NOT NULL,
  `anatomical_expression_display` tinyint NOT NULL,
  `on_developmental_stage_display` tinyint NOT NULL,
  `off_developmental_stage_display` tinyint NOT NULL,
  `biological_process_display` tinyint NOT NULL,
  `sex` tinyint NOT NULL,
  `ectopic` tinyint NOT NULL,
  `enhancer_or_silencer` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_reporter_construct_ts_notify_author`
--

DROP TABLE IF EXISTS `v_reporter_construct_ts_notify_author`;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_ts_notify_author`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_reporter_construct_ts_notify_author` (
  `rc_id` tinyint NOT NULL,
  `expression_identifier` tinyint NOT NULL,
  `stage_on_term` tinyint NOT NULL,
  `stage_off_term` tinyint NOT NULL,
  `biological_process_term` tinyint NOT NULL,
  `sex_term` tinyint NOT NULL,
  `ectopic_term` tinyint NOT NULL,
  `enhancer_or_silencer` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_transcription_factor_binding_site_audit`
--

DROP TABLE IF EXISTS `v_transcription_factor_binding_site_audit`;
/*!50001 DROP VIEW IF EXISTS `v_transcription_factor_binding_site_audit`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_transcription_factor_binding_site_audit` (
  `id` tinyint NOT NULL,
  `state` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `curator_id` tinyint NOT NULL,
  `curator_full_name` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `assayed_in_species_scientific_name` tinyint NOT NULL,
  `gene_display` tinyint NOT NULL,
  `transcription_factor_display` tinyint NOT NULL,
  `chromosome_display` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `coordinates` tinyint NOT NULL,
  `notes` tinyint NOT NULL,
  `date_added` tinyint NOT NULL,
  `last_update` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_transcription_factor_binding_site_feature_location`
--

DROP TABLE IF EXISTS `v_transcription_factor_binding_site_feature_location`;
/*!50001 DROP VIEW IF EXISTS `v_transcription_factor_binding_site_feature_location`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_transcription_factor_binding_site_feature_location` (
  `id` tinyint NOT NULL,
  `type` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `parent` tinyint NOT NULL,
  `feature_id` tinyint NOT NULL,
  `identifier` tinyint NOT NULL,
  `current_start` tinyint NOT NULL,
  `current_end` tinyint NOT NULL,
  `f_start` tinyint NOT NULL,
  `f_end` tinyint NOT NULL,
  `strand` tinyint NOT NULL,
  `relative_start` tinyint NOT NULL,
  `relative_end` tinyint NOT NULL,
  `start_dist` tinyint NOT NULL,
  `end_dist` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_transcription_factor_binding_site_file`
--

DROP TABLE IF EXISTS `v_transcription_factor_binding_site_file`;
/*!50001 DROP VIEW IF EXISTS `v_transcription_factor_binding_site_file`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `v_transcription_factor_binding_site_file` (
  `redfly_id` tinyint NOT NULL,
  `redfly_id_unversioned` tinyint NOT NULL,
  `tfbs_id` tinyint NOT NULL,
  `pubmed_id` tinyint NOT NULL,
  `label` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `sequence_from_species_scientific_name` tinyint NOT NULL,
  `assayed_in_species_scientific_name` tinyint NOT NULL,
  `gene_name` tinyint NOT NULL,
  `tf_name` tinyint NOT NULL,
  `gene_identifier` tinyint NOT NULL,
  `tf_identifier` tinyint NOT NULL,
  `sequence` tinyint NOT NULL,
  `sequence_with_flank` tinyint NOT NULL,
  `evidence_term` tinyint NOT NULL,
  `chromosome` tinyint NOT NULL,
  `start` tinyint NOT NULL,
  `end` tinyint NOT NULL,
  `associated_rc` tinyint NOT NULL,
  `ontology_term` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Dumping routines for database 'redfly'
--
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCuratedPublications` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCuratedPublications`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_curated_publications INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(DISTINCT u.pubmed_id)
	    INTO number_of_curated_publications
	    FROM (SELECT DISTINCT pubmed_id
		      FROM BindingSite
		      WHERE state = 'current'
		      UNION
		      SELECT DISTINCT pubmed_id
		      FROM CRMSegment
		      WHERE state = 'current' 
		      UNION
		      SELECT DISTINCT pubmed_id
		      FROM PredictedCRM
		      WHERE state = 'current'
		      UNION
		      SELECT DISTINCT pubmed_id
		      FROM ReporterConstruct
		      WHERE state = 'current') AS u;
    ELSE
	    SELECT COUNT(DISTINCT u.pubmed_id)
	    INTO number_of_curated_publications
	    FROM (SELECT DISTINCT pubmed_id
		      FROM BindingSite
		      WHERE sequence_from_species_id = species_id AND
                  state = 'current'
		      UNION
		      SELECT DISTINCT pubmed_id
		      FROM CRMSegment
		      WHERE sequence_from_species_id = species_id AND
                  state = 'current' 
		      UNION
		      SELECT DISTINCT pubmed_id
		      FROM PredictedCRM
		      WHERE sequence_from_species_id = species_id AND
                  state = 'current'
		      UNION
		      SELECT DISTINCT pubmed_id
		      FROM ReporterConstruct
		      WHERE sequence_from_species_id = species_id AND
                  state = 'current') AS u;
    END IF;

	RETURN number_of_curated_publications;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentCisRegulatoryModuleGenes` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentCisRegulatoryModuleGenes`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_genes INT;

    IF species_id = 0
    THEN
        SELECT COUNT(DISTINCT gene_id)
	    INTO number_of_current_cis_regulatory_module_genes
        FROM ReporterConstruct
        WHERE state = 'current' AND
            is_crm = 1;
    ELSE
        SELECT COUNT(DISTINCT gene_id)
	    INTO number_of_current_cis_regulatory_module_genes
        FROM ReporterConstruct
        WHERE sequence_from_species_id = species_id AND
            state = 'current' AND
            is_crm = 1;    
    END IF;

	RETURN number_of_current_cis_regulatory_module_genes;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentCisRegulatoryModules` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentCisRegulatoryModules`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules INT;

    IF species_id = 0
    THEN
        SELECT COUNT(rc_id)
	    INTO number_of_current_cis_regulatory_modules
        FROM ReporterConstruct
        WHERE state = 'current' AND
            is_crm = 1;
    ELSE
        SELECT COUNT(rc_id)
	    INTO number_of_current_cis_regulatory_modules
        FROM ReporterConstruct
        WHERE sequence_from_species_id = species_id AND
            state = 'current' AND
            is_crm = 1;    
    END IF;
				
	RETURN number_of_current_cis_regulatory_modules;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentCisRegulatoryModuleSegmentGenes` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentCisRegulatoryModuleSegmentGenes`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_segment_genes INT;

    IF species_id = 0
    THEN
        SELECT COUNT(DISTINCT gene_id)
	    INTO number_of_current_cis_regulatory_module_segment_genes
        FROM CRMSegment
        WHERE state = 'current';
    ELSE
        SELECT COUNT(DISTINCT gene_id)
	    INTO number_of_current_cis_regulatory_module_segment_genes
        FROM CRMSegment
        WHERE sequence_from_species_id = species_id AND
            state = 'current';
    END IF;

	RETURN number_of_current_cis_regulatory_module_segment_genes;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentCisRegulatoryModuleSegments` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentCisRegulatoryModuleSegments`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_segments INT;

    IF species_id = 0
    THEN
        SELECT COUNT(crm_segment_id)
	    INTO number_of_current_cis_regulatory_module_segments
        FROM CRMSegment
	    WHERE state = 'current';
    ELSE 
        SELECT COUNT(crm_segment_id)
	    INTO number_of_current_cis_regulatory_module_segments
        FROM CRMSegment
	    WHERE sequence_from_species_id = species_id AND
            state = 'current';    
    END IF;

	RETURN number_of_current_cis_regulatory_module_segments;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentCisRegulatoryModuleSegmentsWithoutStagingData` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentCisRegulatoryModuleSegmentsWithoutStagingData`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_segments_without_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(crms.name)
	    INTO number_of_current_cis_regulatory_module_segments_without_staging_data 
	    FROM CRMSegment crms
	    WHERE crms.state = 'current' AND
		    NOT EXISTS (SELECT DISTINCT crms.crm_segment_id
	                    FROM triplestore_crm_segment tcs
		                WHERE crms.crm_segment_id = tcs.crm_segment_id);
    ELSE
	    SELECT COUNT(crms.name)
	    INTO number_of_current_cis_regulatory_module_segments_without_staging_data 
	    FROM CRMSegment crms
	    WHERE crms.sequence_from_species_id = species_id AND
            crms.state = 'current' AND
		    NOT EXISTS (SELECT DISTINCT crms.crm_segment_id
	                    FROM triplestore_crm_segment tcs
		                WHERE crms.crm_segment_id = tcs.crm_segment_id);    
    END IF;
				
	RETURN number_of_current_cis_regulatory_module_segments_without_staging_data;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentCisRegulatoryModuleSegmentsWithStagingData` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentCisRegulatoryModuleSegmentsWithStagingData`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_module_segments_with_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(crms.name)
	    INTO number_of_current_cis_regulatory_module_segments_with_staging_data 
	    FROM CRMSegment crms
	    WHERE crms.state = 'current' AND
		    EXISTS (SELECT DISTINCT crms.crm_segment_id
	                FROM triplestore_crm_segment tcs
		            WHERE crms.crm_segment_id = tcs.crm_segment_id);
    ELSE
	    SELECT COUNT(crms.name)
	    INTO number_of_current_cis_regulatory_module_segments_with_staging_data 
	    FROM CRMSegment crms
	    WHERE crms.sequence_from_species_id = species_id AND
            crms.state = 'current' AND
		    EXISTS (SELECT DISTINCT crms.crm_segment_id
	                FROM triplestore_crm_segment tcs
		            WHERE crms.crm_segment_id = tcs.crm_segment_id);    
    END IF;
				
	RETURN number_of_current_cis_regulatory_module_segments_with_staging_data;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentCisRegulatoryModulesHavingCellCultureOnly` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentCisRegulatoryModulesHavingCellCultureOnly`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules_having_cell_culture_only INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc_id)
	    INTO number_of_current_cis_regulatory_modules_having_cell_culture_only
	    FROM ReporterConstruct
	    WHERE state = 'current' AND
		    is_crm = 1 AND
		    cell_culture_only = 1;
    ELSE
	    SELECT COUNT(rc_id)
	    INTO number_of_current_cis_regulatory_modules_having_cell_culture_only
	    FROM ReporterConstruct
	    WHERE sequence_from_species_id = species_id AND
            state = 'current' AND
		    is_crm = 1 AND
		    cell_culture_only = 1;    
    END IF;

	RETURN number_of_current_cis_regulatory_modules_having_cell_culture_only;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentCisRegulatoryModulesWithoutStagingData` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentCisRegulatoryModulesWithoutStagingData`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules_without_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc.name)
	    INTO number_of_current_cis_regulatory_modules_without_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.state = 'current' AND
            rc.is_crm = 1 AND
		    NOT EXISTS (SELECT DISTINCT rc.rc_id
	                    FROM triplestore_rc ts
		                WHERE rc.rc_id = ts.rc_id);
    ELSE 
	    SELECT COUNT(rc.name)
	    INTO number_of_current_cis_regulatory_modules_without_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.sequence_from_species_id = species_id AND
            rc.state = 'current' AND
            rc.is_crm = 1 AND
		    NOT EXISTS (SELECT DISTINCT rc.rc_id
	                    FROM triplestore_rc ts
		                WHERE rc.rc_id = ts.rc_id);    
    END IF;
				
	RETURN number_of_current_cis_regulatory_modules_without_staging_data;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentCisRegulatoryModulesWithStagingData` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentCisRegulatoryModulesWithStagingData`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_cis_regulatory_modules_with_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc.name)
	    INTO number_of_current_cis_regulatory_modules_with_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.state = 'current' AND
            rc.is_crm = 1 AND  
		    EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.rc_id = ts.rc_id);
    ELSE
	    SELECT COUNT(rc.name)
	    INTO number_of_current_cis_regulatory_modules_with_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.sequence_from_species_id = species_id AND
            rc.state = 'current' AND
            rc.is_crm = 1 AND  
		    EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.rc_id = ts.rc_id);    
    END IF;
				
	RETURN number_of_current_cis_regulatory_modules_with_staging_data;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentInVivoCisRegulatoryModules` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentInVivoCisRegulatoryModules`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_in_vivo_cis_regulatory_modules INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc_id)
	    INTO number_of_current_in_vivo_cis_regulatory_modules
	    FROM ReporterConstruct
	    WHERE state = 'current' AND
		    is_crm = 1 AND
		    evidence_id = 2;
    ELSE
	    SELECT COUNT(rc_id)
	    INTO number_of_current_in_vivo_cis_regulatory_modules
	    FROM ReporterConstruct
	    WHERE sequence_from_species_id = species_id AND
            state = 'current' AND
		    is_crm = 1 AND
		    evidence_id = 2;    
    END IF;

	RETURN number_of_current_in_vivo_cis_regulatory_modules;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentNonCisRegulatoryModulesWithoutStagingData` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentNonCisRegulatoryModulesWithoutStagingData`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_non_cis_regulatory_modules_without_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc.name)
	    INTO number_of_current_non_cis_regulatory_modules_without_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.state = 'current' AND
            rc.is_crm = 0 AND
		    NOT EXISTS (SELECT DISTINCT rc.rc_id
	                    FROM triplestore_rc ts
		                WHERE rc.rc_id = ts.rc_id);
    ELSE
	    SELECT COUNT(rc.name)
	    INTO number_of_current_non_cis_regulatory_modules_without_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.sequence_from_species_id = species_id AND
            rc.state = 'current' AND
            rc.is_crm = 0 AND
		    NOT EXISTS (SELECT DISTINCT rc.rc_id
	                    FROM triplestore_rc ts
		                WHERE rc.rc_id = ts.rc_id);    
    END IF;
				
	RETURN number_of_current_non_cis_regulatory_modules_without_staging_data;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentNonCisRegulatoryModulesWithStagingData` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentNonCisRegulatoryModulesWithStagingData`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_non_cis_regulatory_modules_with_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc.name)
	    INTO number_of_current_non_cis_regulatory_modules_with_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.state = 'current' AND
            rc.is_crm = 0 AND
		    EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.rc_id = ts.rc_id);
    ELSE
	    SELECT COUNT(rc.name)
	    INTO number_of_current_non_cis_regulatory_modules_with_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.sequence_from_species_id = species_id AND
            rc.state = 'current' AND
            rc.is_crm = 0 AND
		    EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.rc_id = ts.rc_id);    
    END IF;
				
	RETURN number_of_current_non_cis_regulatory_modules_with_staging_data;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentNonInVivoCisRegulatoryModulesHavingNoCellCulture` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentNonInVivoCisRegulatoryModulesHavingNoCellCulture`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_culture INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc_id)
	    INTO number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_culture
	    FROM ReporterConstruct
	    WHERE state = 'current' AND
		    is_crm = 1 AND
		    evidence_id != 2 AND
		    cell_culture_only = 0;
    ELSE
	    SELECT COUNT(rc_id)
	    INTO number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_culture
	    FROM ReporterConstruct
	    WHERE sequence_from_species_id = species_id AND
            state = 'current' AND
		    is_crm = 1 AND
		    evidence_id != 2 AND
		    cell_culture_only = 0;    
    END IF;

	RETURN number_of_current_non_in_vivo_cis_regulatory_modules_having_no_cell_culture;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentPredictedCisRegulatoryModules` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentPredictedCisRegulatoryModules`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_predicted_cis_regulatory_modules INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(predicted_crm_id)
	    INTO number_of_current_predicted_cis_regulatory_modules
        FROM PredictedCRM
        WHERE state = 'current';
    ELSE 
	    SELECT COUNT(predicted_crm_id)
	    INTO number_of_current_predicted_cis_regulatory_modules
        FROM PredictedCRM
        WHERE sequence_from_species_id = species_id AND
            state = 'current';    
    END IF;

	RETURN number_of_current_predicted_cis_regulatory_modules;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentPredictedCisRegulatoryModulesWithoutStagingData` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentPredictedCisRegulatoryModulesWithoutStagingData`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_predicted_cis_regulatory_modules_without_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(pcrm.name)
	    INTO number_of_current_predicted_cis_regulatory_modules_without_staging_data 
	    FROM PredictedCRM pcrm
	    WHERE pcrm.state = 'current' AND
		    NOT EXISTS (SELECT DISTINCT pcrm.predicted_crm_id
	                    FROM triplestore_predicted_crm tpc
		                WHERE pcrm.predicted_crm_id = tpc.predicted_crm_id);
    ELSE
	    SELECT COUNT(pcrm.name)
	    INTO number_of_current_predicted_cis_regulatory_modules_without_staging_data 
	    FROM PredictedCRM pcrm
	    WHERE pcrm.sequence_from_species_id = species_id AND
            pcrm.state = 'current' AND
		    NOT EXISTS (SELECT DISTINCT pcrm.predicted_crm_id
	                    FROM triplestore_predicted_crm tpc
		                WHERE pcrm.predicted_crm_id = tpc.predicted_crm_id);    
    END IF;
				
	RETURN number_of_current_predicted_cis_regulatory_modules_without_staging_data;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentPredictedCisRegulatoryModulesWithStagingData` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentPredictedCisRegulatoryModulesWithStagingData`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_predicted_cis_regulatory_modules_with_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(pcrm.name)
	    INTO number_of_current_predicted_cis_regulatory_modules_with_staging_data 
	    FROM PredictedCRM pcrm
	    WHERE pcrm.state = 'current' AND
		    EXISTS (SELECT DISTINCT pcrm.predicted_crm_id
	                FROM triplestore_predicted_crm tpc
		            WHERE pcrm.predicted_crm_id = tpc.predicted_crm_id);
    ELSE
	    SELECT COUNT(pcrm.name)
	    INTO number_of_current_predicted_cis_regulatory_modules_with_staging_data 
	    FROM PredictedCRM pcrm
	    WHERE pcrm.sequence_from_species_id = species_id AND
            pcrm.state = 'current' AND
		    EXISTS (SELECT DISTINCT pcrm.predicted_crm_id
	                FROM triplestore_predicted_crm tpc
		            WHERE pcrm.predicted_crm_id = tpc.predicted_crm_id);
    END IF;                    
				
	RETURN number_of_current_predicted_cis_regulatory_modules_with_staging_data;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentReporterConstructs` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentReporterConstructs`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_reporter_constructs INT;

    IF species_id = 0
    THEN
        SELECT COUNT(rc_id)
	    INTO number_of_current_reporter_constructs
        FROM ReporterConstruct
        WHERE state = 'current';
    ELSE
        SELECT COUNT(rc_id)
	    INTO number_of_current_reporter_constructs
        FROM ReporterConstruct
        WHERE sequence_from_species_id = species_id AND
            state = 'current';
    END IF;
				
	RETURN number_of_current_reporter_constructs;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentReporterConstructsWithoutStagingData` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentReporterConstructsWithoutStagingData`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_reporter_constructs_without_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc.name)
	    INTO number_of_current_reporter_constructs_without_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.state = 'current' AND
		    NOT EXISTS (SELECT DISTINCT rc.rc_id
	                    FROM triplestore_rc ts
			            WHERE rc.rc_id = ts.rc_id);
    ELSE
	    SELECT COUNT(rc.name)
	    INTO number_of_current_reporter_constructs_without_staging_data 
	    FROM ReporterConstruct rc
	    WHERE sequence_from_species_id = species_id AND
            rc.state = 'current' AND
		    NOT EXISTS (SELECT DISTINCT rc.rc_id
	                    FROM triplestore_rc ts
			            WHERE rc.sequence_from_species_id = species_id AND
                            rc.rc_id = ts.rc_id);    
    END IF;
				
	RETURN number_of_current_reporter_constructs_without_staging_data;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentReporterConstructsWithStagingData` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentReporterConstructsWithStagingData`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_reporter_constructs_with_staging_data INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(rc.name)
	    INTO number_of_current_reporter_constructs_with_staging_data 
	    FROM ReporterConstruct rc
	    WHERE rc.state = 'current' AND
		    EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.rc_id = ts.rc_id);
    ELSE
	    SELECT COUNT(rc.name)
	    INTO number_of_current_reporter_constructs_with_staging_data 
	    FROM ReporterConstruct rc
	    WHERE sequence_from_species_id = species_id AND
            rc.state = 'current' AND
		    EXISTS (SELECT DISTINCT rc.rc_id
	                FROM triplestore_rc ts
		            WHERE rc.sequence_from_species_id = species_id AND
                        rc.rc_id = ts.rc_id);
    END IF;
				
	RETURN number_of_current_reporter_constructs_with_staging_data;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentTranscriptionFactorBindingSiteGenes` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentTranscriptionFactorBindingSiteGenes`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_transcription_factor_binding_site_genes INT;

    IF species_id = 0
    THEN
        SELECT COUNT(DISTINCT gene_id)
	    INTO number_of_current_transcription_factor_binding_site_genes
        FROM BindingSite
        WHERE state = 'current';
    ELSE
        SELECT COUNT(DISTINCT gene_id)
	    INTO number_of_current_transcription_factor_binding_site_genes
        FROM BindingSite
        WHERE sequence_from_species_id = species_id AND
            state = 'current';    
    END IF;

	RETURN number_of_current_transcription_factor_binding_site_genes;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentTranscriptionFactorBindingSites` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentTranscriptionFactorBindingSites`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_transcription_factor_binding_sites INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(tfbs_id)
	    INTO number_of_current_transcription_factor_binding_sites
	    FROM BindingSite
	    WHERE state = 'current';
    ELSE 
	    SELECT COUNT(tfbs_id)
	    INTO number_of_current_transcription_factor_binding_sites
	    FROM BindingSite
	    WHERE sequence_from_species_id = species_id AND
            state = 'current';    
    END IF;

	RETURN number_of_current_transcription_factor_binding_sites;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `NumberOfCurrentTranscriptionFactors` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` FUNCTION `NumberOfCurrentTranscriptionFactors`(species_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
	DECLARE number_of_current_transcription_factors INT;

    IF species_id = 0
    THEN
	    SELECT COUNT(DISTINCT tf_id)
	    INTO number_of_current_transcription_factors
	    FROM BindingSite
	    WHERE state = 'current';
    ELSE
	    SELECT COUNT(DISTINCT tf_id)
	    INTO number_of_current_transcription_factors
	    FROM BindingSite
	    WHERE sequence_from_species_id = species_id AND
            state = 'current';    
    END IF;

	RETURN number_of_current_transcription_factors;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `archive_records_marked_for_deletion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` PROCEDURE `archive_records_marked_for_deletion`(OUT new_archived_rcs_number INT,
																OUT new_archived_tfbss_number INT,
                                                                OUT new_archived_crm_segments_number INT,
                                                                OUT new_archived_predicted_crms_number INT)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Archives all records marked for deletion.'
BEGIN
    CREATE TEMPORARY TABLE tmp_archive_staging (record_id INT UNSIGNED);

   	
   
    INSERT INTO tmp_archive_staging (record_id)
    SELECT rc_id
    FROM ReporterConstruct AS rc
         INNER JOIN (SELECT entity_id
                     FROM ReporterConstruct
                     WHERE state = 'deleted' AND
                         entity_id IS NOT NULL) AS a ON rc.entity_id = a.entity_id
    WHERE rc.state = 'current';

    UPDATE ReporterConstruct AS rc
           LEFT OUTER JOIN (SELECT entity_id,
                                MAX(version) AS latest
                            FROM ReporterConstruct
                            WHERE state = 'current'
                            GROUP BY entity_id) AS v ON rc.entity_id = v.entity_id
    SET rc.state = 'archived',
        rc.version = IF(latest IS NOT NULL, latest + 1, 0),
        rc.archive_date = CURRENT_TIMESTAMP
    WHERE rc.state = 'deleted';

    SELECT ROW_COUNT()
  	INTO new_archived_rcs_number;
   
    SET @entity_id = (SELECT IFNULL(MAX(entity_id), 0)
                      FROM ReporterConstruct);

    UPDATE ReporterConstruct
    SET entity_id = (@entity_id := @entity_id + 1)
    WHERE state = 'archived' AND
        entity_id IS NULL;                      

    UPDATE ReporterConstruct
    SET state = 'archived',
        archive_date = CURRENT_TIMESTAMP
    WHERE rc_id IN (SELECT record_id
                    FROM tmp_archive_staging);

    SELECT ROW_COUNT() + new_archived_rcs_number 
  	INTO new_archived_rcs_number;                   
                   
    TRUNCATE TABLE tmp_archive_staging;

   	
   
    INSERT INTO tmp_archive_staging (record_id)
    SELECT tfbs_id
    FROM BindingSite AS bs
         INNER JOIN (SELECT entity_id
                     FROM BindingSite
                     WHERE state = 'deleted' AND
                         entity_id IS NOT NULL) AS a ON bs.entity_id = a.entity_id
    WHERE bs.state = 'current';

    UPDATE BindingSite AS bs
           LEFT OUTER JOIN (SELECT entity_id,
                                MAX(version) AS latest
                            FROM BindingSite
                            WHERE state = 'current'
                            GROUP BY entity_id) AS v ON bs.entity_id = v.entity_id
    SET bs.state = 'archived',
        bs.version = IF(latest IS NOT NULL, latest + 1, 0),
        bs.archive_date = CURRENT_TIMESTAMP
    WHERE bs.state = 'deleted';

    SELECT ROW_COUNT()
  	INTO new_archived_tfbss_number;   
   
    SET @entity_id = (SELECT IFNULL(MAX(entity_id), 0)
                      FROM BindingSite);

    UPDATE BindingSite
    SET entity_id = (@entity_id := @entity_id + 1)
    WHERE state = 'archived' AND
        entity_id IS NULL;

    UPDATE BindingSite
    SET name = REPLACE(name, 'REDFLY:XXX', CONCAT('REDFLY:TF', LPAD(entity_id, 6, '0')))
    WHERE name LIKE '%XXX' AND
        state = 'archived';

    UPDATE BindingSite
    SET state = 'archived',
        archive_date = CURRENT_TIMESTAMP
    WHERE tfbs_id IN (SELECT record_id
                      FROM tmp_archive_staging);

    SELECT ROW_COUNT() + new_archived_tfbss_number 
  	INTO new_archived_tfbss_number;                     
                     
    TRUNCATE TABLE tmp_archive_staging;

   	
   	
    INSERT INTO tmp_archive_staging (record_id)
    SELECT crm_segment_id
    FROM CRMSegment AS crmsegment
         INNER JOIN (SELECT entity_id
                     FROM CRMSegment
                     WHERE state = 'deleted' AND
                         entity_id IS NOT NULL) AS a ON crmsegment.entity_id = a.entity_id
    WHERE crmsegment.state = 'current';

    UPDATE CRMSegment AS crmsegment
           LEFT OUTER JOIN (SELECT entity_id,
                                MAX(version) AS latest
                            FROM CRMSegment
                            WHERE state = 'current'
                            GROUP BY entity_id) AS v ON crmsegment.entity_id = v.entity_id
    SET crmsegment.state = 'archived',
        crmsegment.version = IF(latest IS NOT NULL, latest + 1, 0),
        crmsegment.archive_date = CURRENT_TIMESTAMP
    WHERE crmsegment.state = 'deleted';

    SELECT ROW_COUNT()
  	INTO new_archived_crm_segments_number;   
   
    SET @entity_id = (SELECT IFNULL(MAX(entity_id), 0)
                      FROM CRMSegment);

    UPDATE CRMSegment
    SET entity_id = (@entity_id := @entity_id + 1)
    WHERE state = 'archived' AND
        entity_id IS NULL;                      

    UPDATE CRMSegment
    SET state = 'archived',
        archive_date = CURRENT_TIMESTAMP
    WHERE crm_segment_id IN (SELECT record_id
                             FROM tmp_archive_staging);

    SELECT ROW_COUNT() + new_archived_crm_segments_number
  	INTO new_archived_crm_segments_number;                            
                            
    TRUNCATE TABLE tmp_archive_staging;
   	
   	
   
    INSERT INTO tmp_archive_staging (record_id)
    SELECT predicted_crm_id
    FROM PredictedCRM AS pcrm
         INNER JOIN (SELECT entity_id
                     FROM PredictedCRM
                     WHERE state = 'deleted' AND
                         entity_id IS NOT NULL) AS a ON pcrm.entity_id = a.entity_id
    WHERE pcrm.state = 'current';

    UPDATE PredictedCRM AS pcrm
           LEFT OUTER JOIN (SELECT entity_id,
                                MAX(version) AS latest
                            FROM PredictedCRM
                            WHERE state = 'current'
                            GROUP BY entity_id) AS v ON pcrm.entity_id = v.entity_id
    SET pcrm.state = 'archived',
        pcrm.version = IF(latest IS NOT NULL, latest + 1, 0),
        pcrm.archive_date = CURRENT_TIMESTAMP
    WHERE pcrm.state = 'deleted';

    SELECT ROW_COUNT()
  	INTO new_archived_predicted_crms_number;   
   
    SET @entity_id = (SELECT IFNULL(MAX(entity_id), 0)
                      FROM PredictedCRM);

    UPDATE PredictedCRM
    SET entity_id = (@entity_id := @entity_id + 1)
    WHERE state = 'archived' AND
        entity_id IS NULL;

    UPDATE PredictedCRM AS pcrm
    SET state = 'archived',
        archive_date = CURRENT_TIMESTAMP
    WHERE predicted_crm_id IN (SELECT record_id
                               FROM tmp_archive_staging);

    SELECT ROW_COUNT() + new_archived_predicted_crms_number
  	INTO new_archived_predicted_crms_number;
                          
    DROP TEMPORARY TABLE IF EXISTS tmp_archive_staging;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `insert_inferred_crm` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` PROCEDURE `insert_inferred_crm`(
    IN sequence_from_species_id INT UNSIGNED,
    IN assayed_in_species_id INT UNSIGNED,
    IN current_genome_assembly_release_version VARCHAR(32),
    IN chromosome_id INT UNSIGNED,
    IN current_start INT UNSIGNED,
    IN current_end INT UNSIGNED,
    IN size INT UNSIGNED,
    IN expression_ids TEXT,
    IN component_rc_ids TEXT
)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Inserts a new inferred CRM into the database.'
BEGIN
    DECLARE last_icrm_id BIGINT UNSIGNED;

    INSERT INTO inferred_crm (
        sequence_from_species_id,
        assayed_in_species_id,
        current_genome_assembly_release_version,
        chromosome_id,
        current_start,
        current_end,
        size)
    VALUES (
        sequence_from_species_id,
        assayed_in_species_id,
        current_genome_assembly_release_version,
        chromosome_id,
        current_start,
        current_end,
        size);

    SET last_icrm_id = LAST_INSERT_ID();

    INSERT INTO icrm_has_expr_term (icrm_id, term_id)
    SELECT last_icrm_id, et.term_id
      FROM ExpressionTerm AS et
     WHERE FIND_IN_SET(et.term_id, expression_ids) > 0;

    INSERT INTO icrm_has_rc (icrm_id, rc_id)
    SELECT last_icrm_id, rc.rc_id
      FROM ReporterConstruct AS rc
     WHERE FIND_IN_SET(rc.rc_id, component_rc_ids) > 0;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `refresh_inferred_crm_read_model` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` PROCEDURE `refresh_inferred_crm_read_model`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Refreshes the inferred CRM read model with the latest data'
BEGIN
    DELETE FROM inferred_crm_read_model;

    INSERT INTO inferred_crm_read_model
    SELECT icrm.icrm_id AS id,
        icrm.sequence_from_species_id,
        icrm.assayed_in_species_id,
        GROUP_CONCAT(DISTINCT g.name ORDER BY g.name ASC SEPARATOR ',') AS gene,
        GROUP_CONCAT(DISTINCT gl.name ORDER BY gl.name ASC SEPARATOR ',') AS gene_locus,
        c.chromosome_id, 
        c.name AS chromosome,
        icrm.current_start,
        icrm.current_end,
        icrm.size,
        CONCAT(c.name, ':', icrm.current_start, '..', icrm.current_end) AS coordinates,
        GROUP_CONCAT(DISTINCT rc.name ORDER BY rc.name ASC SEPARATOR ',') AS components,
        GROUP_CONCAT(DISTINCT et.term ORDER BY et.identifier ASC SEPARATOR ',') AS expressions,
        GROUP_CONCAT(DISTINCT et.identifier ORDER BY et.identifier ASC SEPARATOR ',') AS expression_identifiers
    FROM inferred_crm AS icrm
    JOIN Chromosome AS c USING (chromosome_id)
    JOIN icrm_has_rc USING (icrm_id)
    JOIN ReporterConstruct AS rc USING (rc_id)
    JOIN Gene AS g USING (gene_id)
    JOIN icrm_has_expr_term USING (icrm_id)
    JOIN ExpressionTerm AS et USING (term_id)
    LEFT OUTER JOIN Gene AS gl ON icrm.chromosome_id = gl.chrm_id AND
        icrm.current_start > (gl.start - 10000) AND
        icrm.current_end < (gl.stop + 10000)
    GROUP BY id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `release_approved_records` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` PROCEDURE `release_approved_records`(
    OUT new_current_rcs_number INT,
    OUT new_archived_rcs_number INT,
	OUT new_current_tfbss_number INT,
    OUT new_archived_tfbss_number INT,
    OUT new_current_crm_segments_number INT,
    OUT new_archived_crm_segments_number INT,
    OUT new_current_predicted_crms_number INT,
    OUT new_archived_predicted_crms_number INT)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Sets all approved entities to current.'
BEGIN
    CREATE TEMPORARY TABLE tmp_release_staging (record_id INT UNSIGNED);

    INSERT INTO tmp_release_staging (record_id)
    SELECT rc_id
    FROM ReporterConstruct AS rc
         INNER JOIN (SELECT entity_id
                     FROM ReporterConstruct
                     WHERE state = 'approved' AND
                        entity_id IS NOT NULL) AS a
         ON rc.entity_id = a.entity_id
    WHERE rc.state = 'current';

    UPDATE ReporterConstruct AS rc
           LEFT OUTER JOIN (SELECT entity_id,
                                MAX(version) AS latest
                            FROM ReporterConstruct
                            WHERE state = 'current'
                            GROUP BY entity_id) AS v
           ON rc.entity_id = v.entity_id
    SET rc.state = 'current',
        rc.version = IF(latest IS NOT NULL, latest + 1, 0),
        rc.last_update = CURRENT_TIMESTAMP
    WHERE rc.state = 'approved';
   
    SELECT ROW_COUNT()
  	INTO new_current_rcs_number; 

    SET @entity_id = (SELECT IFNULL(MAX(entity_id), 0)
                      FROM ReporterConstruct);

    UPDATE ReporterConstruct
    SET entity_id = (@entity_id := @entity_id + 1)
    WHERE state = 'current' AND
        entity_id IS NULL;

    UPDATE ReporterConstruct
    SET state = 'archived',
        archive_date = CURRENT_TIMESTAMP
    WHERE rc_id IN (SELECT record_id
                    FROM tmp_release_staging);

    SELECT ROW_COUNT()
  	INTO new_archived_rcs_number;     
    
    TRUNCATE TABLE tmp_release_staging;

    INSERT INTO tmp_release_staging (record_id)
    SELECT tfbs_id
    FROM BindingSite AS bs
         INNER JOIN (SELECT entity_id
                     FROM BindingSite
                     WHERE state = 'approved' AND
                        entity_id IS NOT NULL) AS a
         ON bs.entity_id = a.entity_id
    WHERE bs.state = 'current';

    UPDATE BindingSite AS bs
           LEFT OUTER JOIN (SELECT entity_id,
                                MAX(version) AS latest
                            FROM BindingSite
                            WHERE state = 'current'
                            GROUP BY entity_id) AS v
           ON bs.entity_id = v.entity_id
    SET bs.state = 'current',
        bs.version = IF(latest IS NOT NULL, latest + 1, 0),
        bs.last_update = CURRENT_TIMESTAMP
    WHERE bs.state = 'approved';

    SELECT ROW_COUNT()
  	INTO new_current_tfbss_number; 
   
    SET @entity_id = (SELECT IFNULL(MAX(entity_id), 0)
                      FROM BindingSite);

    UPDATE BindingSite
    SET entity_id = (@entity_id := @entity_id + 1)
    WHERE state = 'current' AND
        entity_id IS NULL;

    UPDATE BindingSite
    SET name = REPLACE(name, 'REDFLY:XXX', CONCAT('REDFLY:TF', LPAD(entity_id, 6, '0')))
    WHERE name LIKE '%XXX' AND
        state = 'current';

    UPDATE BindingSite
    SET state = 'archived',
        archive_date = CURRENT_TIMESTAMP
    WHERE tfbs_id IN (SELECT record_id
                      FROM tmp_release_staging);

    SELECT ROW_COUNT()
  	INTO new_archived_tfbss_number;     
    
    TRUNCATE TABLE tmp_release_staging;

    INSERT INTO tmp_release_staging (record_id)
    SELECT crm_segment_id
    FROM CRMSegment AS crmsegment
         INNER JOIN (SELECT entity_id
                     FROM CRMSegment
                     WHERE state = 'approved' AND
                        entity_id IS NOT NULL) AS a
         ON crmsegment.entity_id = a.entity_id
    WHERE crmsegment.state = 'current';

    UPDATE CRMSegment AS crmsegment
           LEFT OUTER JOIN (SELECT entity_id,
                                MAX(version) AS latest
                            FROM CRMSegment
                            WHERE state = 'current'
                            GROUP BY entity_id) AS v
           ON crmsegment.entity_id = v.entity_id
    SET crmsegment.state = 'current',
        crmsegment.version = IF(latest IS NOT NULL, latest + 1, 0),
        crmsegment.last_update = CURRENT_TIMESTAMP
    WHERE crmsegment.state = 'approved';

    SELECT ROW_COUNT()
  	INTO new_current_crm_segments_number; 
   
    SET @entity_id = (SELECT IFNULL(MAX(entity_id), 0)
                      FROM CRMSegment);

    UPDATE CRMSegment
    SET entity_id = (@entity_id := @entity_id + 1)
    WHERE state = 'current' AND
       entity_id IS NULL;

    UPDATE CRMSegment
    SET state = 'archived',
        archive_date = CURRENT_TIMESTAMP
    WHERE crm_segment_id IN (SELECT record_id
                             FROM tmp_release_staging);

    SELECT ROW_COUNT()
  	INTO new_archived_crm_segments_number;    
    
    TRUNCATE TABLE tmp_release_staging;
   
    INSERT INTO tmp_release_staging (record_id)
    SELECT predicted_crm_id
    FROM PredictedCRM AS pcrm
         INNER JOIN (SELECT entity_id
                     FROM PredictedCRM
                     WHERE state = 'approved' AND
                        entity_id IS NOT NULL) AS a
         ON pcrm.entity_id = a.entity_id
    WHERE pcrm.state = 'current';

    UPDATE PredictedCRM AS pcrm
           LEFT OUTER JOIN (SELECT entity_id,
                                MAX(version) AS latest
                            FROM PredictedCRM
                            WHERE state = 'current'
                            GROUP BY entity_id) AS v
            ON pcrm.entity_id = v.entity_id
    SET pcrm.state = 'current',
        pcrm.version = IF(latest IS NOT NULL, latest + 1, 0),
        pcrm.last_update = CURRENT_TIMESTAMP
    WHERE pcrm.state = 'approved';

    SELECT ROW_COUNT()
  	INTO new_current_predicted_crms_number;    
   
    SET @entity_id = (SELECT IFNULL(MAX(entity_id), 0)
                      FROM PredictedCRM);

    UPDATE PredictedCRM
    SET entity_id = (@entity_id := @entity_id + 1)
    WHERE state = 'current' AND
       entity_id IS NULL;

    UPDATE PredictedCRM
    SET state = 'archived',
        archive_date = CURRENT_TIMESTAMP
    WHERE predicted_crm_id IN (SELECT record_id
                               FROM tmp_release_staging);

    SELECT ROW_COUNT()
  	INTO new_archived_predicted_crms_number;    
    
    DROP TEMPORARY TABLE IF EXISTS tmp_release_staging;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `search_genes_which_both_name_and_identifier_do_not_match` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` PROCEDURE `search_genes_which_both_name_and_identifier_do_not_match`(IN species_short_name VARCHAR(32))
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN

SELECT g.name 
FROM Gene g
JOIN Species s ON (s.short_name = species_short_name AND
    g.species_id = s.species_id)
WHERE g.gene_id NOT IN (SELECT old.gene_id
                        FROM Gene old
                        JOIN Species s ON (s.short_name = species_short_name AND
                        	old.species_id = s.species_id) 
                        JOIN staging_gene_update new ON (BINARY old.name = BINARY new.name AND
                            old.identifier = new.identifier AND
                            s.short_name = new.species_short_name)) AND
      g.gene_id NOT IN (SELECT old.gene_id
                        FROM Gene old
                        JOIN Species s ON (s.short_name = species_short_name AND
                        	old.species_id = s.species_id)
                        JOIN staging_gene_update new ON (BINARY old.name = BINARY new.name AND
                            old.identifier != new.identifier AND
                            s.short_name = new.species_short_name)) AND
      g.gene_id NOT IN (SELECT old.gene_id
                        FROM Gene old
                        JOIN Species s ON (s.short_name = species_short_name AND
                        	old.species_id = s.species_id)
                        JOIN staging_gene_update new ON (BINARY old.name != BINARY new.name AND
                            old.identifier = new.identifier AND
                            s.short_name = new.species_short_name));

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_anatomical_expressions` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` PROCEDURE `update_anatomical_expressions`(
    OUT identifiers TEXT,
    OUT old_terms LONGTEXT,
    OUT new_terms LONGTEXT,
    OUT updated_anatomical_expressions_number INT,
    OUT deleted_anatomical_expressions_number INT,
    OUT new_anatomical_expressions_number INT
)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Updates the anatomical expressions based on the data stored in staging'
BEGIN
    SET identifiers :=  (
	    SELECT GROUP_CONCAT(old.identifier ORDER BY old.identifier SEPARATOR '\t')
        FROM ExpressionTerm AS old
        JOIN Species AS s USING(species_id)
        JOIN staging_expression_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            BINARY old.term != BINARY new.term
    );

    SET old_terms :=  (
	    SELECT GROUP_CONCAT(old.term ORDER BY old.identifier SEPARATOR '\t')
        FROM ExpressionTerm AS old
        JOIN Species AS s USING(species_id)
        JOIN staging_expression_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            BINARY old.term != BINARY new.term
    );

    SET new_terms :=  (
	    SELECT GROUP_CONCAT(new.term ORDER BY old.identifier SEPARATOR '\t')
        FROM ExpressionTerm AS old
        JOIN Species AS s USING(species_id)
        JOIN staging_expression_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            BINARY old.term != BINARY new.term
    );

    UPDATE ExpressionTerm AS old
    JOIN Species AS s USING(species_id)
    JOIN staging_expression_update AS new ON old.identifier = new.identifier
    SET old.term = new.term
    WHERE old.species_id = s.species_id AND
        s.short_name = new.species_short_name AND
        BINARY old.term != BINARY new.term;

    SELECT ROW_COUNT()
    INTO updated_anatomical_expressions_number;
   
    UPDATE ExpressionTerm AS old
    JOIN Species AS s USING(species_id)
    LEFT OUTER JOIN staging_expression_update AS new ON old.identifier = new.identifier
    SET old.is_deprecated = true
    WHERE old.species_id = s.species_id AND
        s.short_name = new.species_short_name AND
        new.identifier IS NULL;

    DELETE FROM ExpressionTerm
    WHERE term_id NOT IN (SELECT DISTINCT term_id
                          FROM RC_has_ExprTerm) AND
        term_id NOT IN (SELECT DISTINCT term_id
                        FROM icrm_has_expr_term) AND
        term_id NOT IN (SELECT DISTINCT term_id
                        FROM CRMSegment_has_Expression_Term) AND
        term_id NOT IN (SELECT DISTINCT term_id
                        FROM PredictedCRM_has_Expression_Term) AND                        
        is_deprecated = true;

    SELECT ROW_COUNT()
    INTO deleted_anatomical_expressions_number;
              
    INSERT INTO ExpressionTerm (
        species_id,
        term,
        identifier,
        is_deprecated)
    SELECT s.species_id,
        new.term,
        new.identifier,
        false
    FROM staging_expression_update AS new
    JOIN Species AS s ON new.species_short_name = s.short_name
    WHERE new.identifier NOT IN (SELECT identifier
                                 FROM ExpressionTerm et
                                 WHERE s.species_id = et.species_id);
                                
    SELECT ROW_COUNT()
    INTO new_anatomical_expressions_number;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_biological_processes` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` PROCEDURE `update_biological_processes`(
    OUT go_ids TEXT,
    OUT old_terms TEXT,
    OUT new_terms TEXT,
    OUT updated_biological_processes_number_with_new_term INT,
    OUT deleted_biological_processes_number INT,
    OUT new_biological_processes_number INT)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Updates the biological processes based on the data stored in staging'
BEGIN
    SET go_ids :=  (
	    SELECT GROUP_CONCAT(old.go_id ORDER BY old.go_id SEPARATOR '\t')
        FROM BiologicalProcess AS old
        JOIN staging_biological_process_update AS new USING (go_id)
        WHERE BINARY old.term != BINARY new.term
    );

    SET old_terms :=  (
	    SELECT GROUP_CONCAT(old.term ORDER BY old.go_id SEPARATOR '\t')
        FROM BiologicalProcess AS old
        JOIN staging_biological_process_update AS new USING (go_id)
        WHERE BINARY old.term != BINARY new.term
    );

    SET new_terms :=  (
	    SELECT GROUP_CONCAT(new.term ORDER BY old.go_id SEPARATOR '\t')
        FROM BiologicalProcess AS old
        JOIN staging_biological_process_update AS new USING (go_id)
        WHERE BINARY old.term != BINARY new.term
    );

    UPDATE BiologicalProcess AS old
    JOIN staging_biological_process_update AS new USING (go_id)
    SET old.term = new.term
    WHERE BINARY old.term != BINARY new.term;
 
    SELECT ROW_COUNT()
    INTO updated_biological_processes_number_with_new_term; 

    UPDATE BiologicalProcess AS old
    LEFT OUTER JOIN staging_biological_process_update AS new USING (go_id)
    SET old.is_deprecated = true
    WHERE new.go_id IS NULL;

    DELETE FROM BiologicalProcess
    WHERE is_deprecated = true;

    SELECT ROW_COUNT()
    INTO deleted_biological_processes_number;
 
    INSERT INTO BiologicalProcess (
        term,
        go_id,
        is_deprecated)
    SELECT new.term,
        new.go_id,
        false
    FROM staging_biological_process_update AS new
    WHERE new.go_id NOT IN (SELECT go_id FROM BiologicalProcess);
 
    SELECT ROW_COUNT()
    INTO new_biological_processes_number; 
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_citations` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` PROCEDURE `update_citations`(OUT deleted_citations_number INT,
                                             OUT updated_citations_number INT)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Clean up unused citations and update author contact status.'
BEGIN
	DELETE FROM Citation
    WHERE external_id NOT IN (SELECT DISTINCT pubmed_id FROM ReporterConstruct) AND
        external_id NOT IN (SELECT DISTINCT pubmed_id FROM BindingSite) AND
        external_id NOT IN (SELECT DISTINCT pubmed_id FROM CRMSegment) AND
        external_id NOT IN (SELECT DISTINCT pubmed_id FROM PredictedCRM);

    SELECT ROW_COUNT()
    INTO deleted_citations_number;

    UPDATE Citation
    SET author_contacted = true
    WHERE external_id IN (SELECT DISTINCT pubmed_id
                          FROM ReporterConstruct
                          WHERE state = 'current'
                          UNION
                          SELECT DISTINCT pubmed_id
                          FROM CRMSegment
                          WHERE state = 'current');

    SELECT ROW_COUNT()
    INTO updated_citations_number;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_developmental_stages` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` PROCEDURE `update_developmental_stages`(
    OUT identifiers TEXT,
    OUT old_terms TEXT,
    OUT new_terms TEXT,
    OUT updated_developmental_stages_number_with_new_term INT,
    OUT deleted_developmental_stages_number INT,
    OUT new_developmental_stages_number INT)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Updates the developmental stages based on the data stored in staging'
BEGIN
	DECLARE finished INT DEFAULT 0;
	DECLARE id INT;
	DECLARE species_cursor CURSOR FOR 
		SELECT species_id FROM Species;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

    SET identifiers :=  (
	    SELECT GROUP_CONCAT(old.identifier ORDER BY old.identifier SEPARATOR '\t')
        FROM DevelopmentalStage AS old
        JOIN Species AS s USING(species_id)
        JOIN staging_developmental_stage_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            BINARY old.term != BINARY new.term
    );

    SET old_terms :=  (
	    SELECT GROUP_CONCAT(old.term ORDER BY old.identifier SEPARATOR '\t')
        FROM DevelopmentalStage AS old
        JOIN Species AS s USING(species_id)
        JOIN staging_developmental_stage_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            BINARY old.term != BINARY new.term
    );

    SET new_terms :=  (
	    SELECT GROUP_CONCAT(new.term ORDER BY old.identifier SEPARATOR '\t')
        FROM DevelopmentalStage AS old
        JOIN Species AS s USING(species_id)
        JOIN staging_developmental_stage_update AS new ON old.identifier = new.identifier
        WHERE old.species_id = s.species_id AND
            s.short_name = new.species_short_name AND
            BINARY old.term != BINARY new.term
    );

    UPDATE DevelopmentalStage AS old
    JOIN Species AS s USING(species_id)
    JOIN staging_developmental_stage_update AS new ON (old.identifier = new.identifier)
    SET old.term = new.term
    WHERE old.species_id = s.species_id AND
        s.short_name = new.species_short_name AND
        old.term != new.term;

    SELECT ROW_COUNT()
    INTO updated_developmental_stages_number_with_new_term;
   
    UPDATE DevelopmentalStage AS old
    JOIN Species AS s USING(species_id)
    LEFT OUTER JOIN staging_developmental_stage_update AS new ON (old.identifier = new.identifier)
    SET old.is_deprecated = true
    WHERE old.species_id = s.species_id AND
        s.short_name = new.species_short_name AND
        new.identifier IS NULL;

    DELETE FROM DevelopmentalStage
    WHERE is_deprecated = true;
   
    SELECT ROW_COUNT()
    INTO deleted_developmental_stages_number;   

    INSERT INTO DevelopmentalStage (
        species_id,
        term,
        identifier,
        is_deprecated)
    SELECT s.species_id,
        new.term,
        new.identifier,
        false
    FROM staging_developmental_stage_update AS new
    JOIN Species AS s ON (new.species_short_name = s.short_name)
    WHERE new.identifier NOT IN (SELECT identifier
                                 FROM DevelopmentalStage);

    SELECT ROW_COUNT()
    INTO new_developmental_stages_number;   
   
    OPEN species_cursor;

    species_id_insert: LOOP
        FETCH species_cursor INTO id;
        IF finished = 1 THEN
            LEAVE species_id_insert;
        END IF;
        IF (SELECT species_id
            FROM DevelopmentalStage
            WHERE species_id = id AND
            	term = 'none' AND
            	identifier = 'none') != id THEN
	        INSERT INTO DevelopmentalStage (
    	        species_id,
        	    term,
            	identifier,
	            is_deprecated) 
    	    VALUES (id,
        	    'none',
            	'none',
	            0);
	    END IF;
    END LOOP species_id_insert;

    CLOSE species_cursor;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_features` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` PROCEDURE `update_features`(
    OUT new_mrna_features_number INT,
    OUT new_exon_and_intron_features_number INT)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Updates the features based on the data stored in staging'
BEGIN
    DELETE FROM Features;

    INSERT INTO Features (
        species_id,
        gene_id,
        type,
        start,
        end,
        strand,
        identifier,
        name)
    SELECT s.species_id,
        g.gene_id,
        new.type,
        new.start,
        new.end,
        new.strand,
        new.identifier,
        new.name
    FROM staging_feature_update AS new
    JOIN Species AS s ON (new.species_short_name = s.short_name)
    JOIN Gene AS g ON (new.parent = g.identifier)
    WHERE new.type = 'mrna';

    SELECT ROW_COUNT()
    INTO new_mrna_features_number;   
   
    CREATE TEMPORARY TABLE tmp_locations AS
    SELECT s.species_id,
        new.type,
        new.start,
        new.end,
        new.strand,
        new.parent
    FROM staging_feature_update AS new
    JOIN Features AS f ON (new.parent = f.identifier)
    JOIN Species AS s ON (new.species_short_name = s.short_name)
    WHERE new.type IN ('exon', 'intron');

    INSERT INTO Features (
        species_id,
        type,
        start,
        end,
        strand,
        parent)
    SELECT species_id,
        type,
        start,
        end,
        strand,
        parent
    FROM tmp_locations;
   
	SELECT ROW_COUNT()
    INTO new_exon_and_intron_features_number;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_genes` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`redfly`@`%` PROCEDURE `update_genes`(
    IN  species_id_in INT,
    IN  species_short_name_in CHAR(32),
    IN  genome_assembly_id_in INT,
    IN  genome_assembly_release_version_in CHAR(32),
    OUT deleted_genes_number INT,
    OUT identifiers MEDIUMTEXT,
    OUT old_names MEDIUMTEXT,
    OUT new_names MEDIUMTEXT,
    OUT updated_genes_number_with_new_name INT,
    OUT old_identifiers MEDIUMTEXT,
    OUT new_identifiers MEDIUMTEXT,    
    OUT updated_genes_number_with_new_identifier INT,
    OUT renamed_crm_segment_names MEDIUMTEXT,
    OUT updated_crm_segments_number_with_new_gene_name INT,    
    OUT renamed_reporter_construct_names MEDIUMTEXT,
    OUT updated_reporter_constructs_number_with_new_gene_name INT,    
    OUT renamed_transcription_factor_binding_site_names_by_transcription_factor MEDIUMTEXT,
    OUT updated_transcription_factor_binding_sites_number_with_new_transcription_factor_name INT,
    OUT renamed_transcription_factor_binding_site_names_by_gene MEDIUMTEXT,    
    OUT updated_transcription_factor_binding_sites_number_with_new_gene_name INT,    
    OUT new_genes_number INT)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Updates the genes based on the data stored in staging'
BEGIN
    DELETE FROM Features;

    
    DELETE FROM Gene
    WHERE gene_id NOT IN (SELECT DISTINCT gene_id 
                          FROM BindingSite
                          WHERE sequence_from_species_id = species_id_in AND
                              current_genome_assembly_release_version = genome_assembly_release_version_in
                          UNION
                          SELECT DISTINCT tf_id 
                          FROM BindingSite
                          WHERE sequence_from_species_id = species_id_in AND
                              current_genome_assembly_release_version = genome_assembly_release_version_in
                          UNION
                          SELECT DISTINCT gene_id 
                          FROM ReporterConstruct
                          WHERE sequence_from_species_id = species_id_in AND
                              current_genome_assembly_release_version = genome_assembly_release_version_in
                          UNION
                          SELECT DISTINCT gene_id 
                          FROM CRMSegment
                          WHERE sequence_from_species_id = species_id_in AND
                              current_genome_assembly_release_version = genome_assembly_release_version_in) AND
        species_id = species_id_in AND
        genome_assembly_id = genome_assembly_id_in;

    SELECT ROW_COUNT()
    INTO deleted_genes_number;

    
    UPDATE staging_gene_update new
    JOIN Species s ON s.short_name = new.species_short_name
    JOIN GenomeAssembly ga ON ga.is_deprecated = 0 AND
        ga.species_id = s.species_id AND
        ga.release_version = new.genome_assembly_release_version
    JOIN Chromosome c ON c.species_id = s.species_id AND
        c.genome_assembly_id = ga.genome_assembly_id AND
        c.name = new.chromosome_name
    SET new.species_id = s.species_id,
        new.genome_assembly_id = ga.genome_assembly_id,
        new.chromosome_id = c.chromosome_id;

    
	SELECT GROUP_CONCAT(old.identifier ORDER BY old.identifier SEPARATOR '\t'),
        GROUP_CONCAT(old.name ORDER BY old.identifier SEPARATOR '\t'),
        GROUP_CONCAT(new.name ORDER BY old.identifier SEPARATOR '\t') INTO
        identifiers,
        old_names,
        new_names
    FROM Species s
    JOIN GenomeAssembly ga ON s.short_name = species_short_name_in AND
        ga.species_id = s.species_id AND
        ga.release_version = genome_assembly_release_version_in
    JOIN Gene old ON old.species_id = s.species_id AND
        old.genome_assembly_id = ga.genome_assembly_id
    JOIN staging_gene_update new ON new.species_short_name = species_short_name_in AND
        new.genome_assembly_release_version = genome_assembly_release_version_in AND
        new.identifier = old.identifier AND
        BINARY new.name != BINARY old.name;

    
    UPDATE Gene old
    JOIN Species s ON s.short_name = species_short_name_in AND
        s.species_id = old.species_id
    JOIN GenomeAssembly ga ON ga.release_version = genome_assembly_release_version_in AND
        ga.species_id = s.species_id AND
        ga.genome_assembly_id = old.genome_assembly_id
    JOIN staging_gene_update new ON new.species_short_name = species_short_name_in AND
        new.genome_assembly_release_version = genome_assembly_release_version_in AND
        new.identifier = old.identifier AND
        BINARY new.name != BINARY old.name
    SET old.name = new.name,
        old.genome_assembly_id = new.genome_assembly_id,
        old.chrm_id = new.chromosome_id,
        old.start = new.start,
        old.stop = new.end,
        old.strand = new.strand;

    SELECT ROW_COUNT()
    INTO updated_genes_number_with_new_name;

    
	SELECT GROUP_CONCAT(old.identifier ORDER BY old.identifier SEPARATOR '\t'),
        GROUP_CONCAT(new.identifier ORDER BY old.identifier SEPARATOR '\t') INTO
        old_identifiers, 
        new_identifiers
    FROM Species s
    JOIN GenomeAssembly ga ON s.short_name = species_short_name_in AND
        ga.species_id = s.species_id AND
        ga.release_version = genome_assembly_release_version_in
    JOIN Gene old ON old.species_id = s.species_id AND
        old.genome_assembly_id = ga.genome_assembly_id
    JOIN staging_gene_update new ON new.species_short_name = species_short_name_in AND
        new.genome_assembly_release_version = genome_assembly_release_version_in AND
        new.identifier != old.identifier AND
        BINARY new.name = BINARY old.name;

    
    UPDATE Gene old
    JOIN Species s ON s.short_name = species_short_name_in AND
        old.species_id = s.species_id
    JOIN GenomeAssembly ga ON ga.release_version = genome_assembly_release_version_in AND
        ga.species_id = s.species_id AND
        ga.genome_assembly_id = old.genome_assembly_id
    JOIN staging_gene_update new ON new.species_short_name = species_short_name_in AND
        new.genome_assembly_release_version = genome_assembly_release_version_in AND
        new.identifier != old.identifier AND
        BINARY new.name = BINARY old.name
    SET old.identifier = new.identifier,
        old.genome_assembly_id = new.genome_assembly_id,
        old.chrm_id = new.chromosome_id,
        old.start = new.start,
        old.stop = new.end,
        old.strand = new.strand;

    SELECT ROW_COUNT()
    INTO updated_genes_number_with_new_identifier;

    
    SELECT GROUP_CONCAT(crm_segment.name ORDER BY crm_segment.name SEPARATOR '\t')
    INTO renamed_crm_segment_names
    FROM CRMSegment crm_segment
    JOIN Gene g ON crm_segment.sequence_from_species_id = species_id_in AND
        crm_segment.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        crm_segment.gene_id = g.gene_id AND
        BINARY SUBSTRING_INDEX(crm_segment.name, '_', 1) != BINARY g.name;

    
    UPDATE CRMSegment crm_segment
    JOIN Gene g ON crm_segment.sequence_from_species_id = species_id_in AND
        crm_segment.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        crm_segment.gene_id = g.gene_id AND
        BINARY SUBSTRING_INDEX(crm_segment.name, '_', 1) != BINARY g.name
    SET crm_segment.name = CONCAT(g.name, SUBSTRING(crm_segment.name, LOCATE('_', crm_segment.name)));

    SELECT ROW_COUNT()
    INTO updated_crm_segments_number_with_new_gene_name;

               
    SELECT GROUP_CONCAT(rc.name ORDER BY rc.name SEPARATOR '\t')
    INTO renamed_reporter_construct_names
    FROM ReporterConstruct rc
    JOIN Gene g ON rc.sequence_from_species_id = species_id_in AND
        rc.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        rc.gene_id = g.gene_id AND
        BINARY SUBSTRING_INDEX(rc.name, '_', 1) != BINARY g.name;

    
    UPDATE ReporterConstruct rc
    JOIN Gene g ON rc.sequence_from_species_id = species_id_in AND
        rc.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        rc.gene_id = g.gene_id AND
        BINARY SUBSTRING_INDEX(rc.name, '_', 1) != BINARY g.name
    SET rc.name = CONCAT(g.name, SUBSTRING(rc.name, LOCATE('_', rc.name)));

    SELECT ROW_COUNT()
    INTO updated_reporter_constructs_number_with_new_gene_name;

    
    SELECT GROUP_CONCAT(bs.name ORDER BY bs.name SEPARATOR '\t')
    INTO renamed_transcription_factor_binding_site_names_by_transcription_factor
    FROM BindingSite bs
    JOIN Gene g ON bs.sequence_from_species_id = species_id_in AND
        bs.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        bs.tf_id = g.gene_id AND
        BINARY SUBSTRING_INDEX(bs.name, '_', 1) != BINARY g.name;

    
    UPDATE BindingSite bs
    JOIN Gene g ON bs.sequence_from_species_id = species_id_in AND
        bs.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        bs.tf_id = g.gene_id AND
        BINARY SUBSTRING_INDEX(bs.name, '_', 1) != BINARY g.name
    SET bs.name = CONCAT(g.name,
                         SUBSTRING(bs.name, LOCATE('_', bs.name)));

    SELECT ROW_COUNT()
    INTO updated_transcription_factor_binding_sites_number_with_new_transcription_factor_name;

    
    SELECT GROUP_CONCAT(bs.name ORDER BY bs.name SEPARATOR '\t')
    INTO renamed_transcription_factor_binding_site_names_by_gene
    FROM BindingSite bs
    JOIN Gene g ON bs.sequence_from_species_id = species_id_in AND
        bs.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        bs.gene_id = g.gene_id AND
        BINARY SUBSTRING(SUBSTRING(bs.name, 1, LOCATE(':REDFLY:', bs.name) - 1), LOCATE('_', bs.name) + 1) != BINARY g.name;

    
    UPDATE BindingSite bs
    JOIN Gene g ON bs.sequence_from_species_id = species_id_in AND
        bs.current_genome_assembly_release_version = genome_assembly_release_version_in AND
        bs.gene_id = g.gene_id AND
        BINARY SUBSTRING(SUBSTRING(bs.name, 1, LOCATE(':REDFLY:', bs.name) - 1), LOCATE('_', bs.name) + 1) != BINARY g.name
    SET bs.name = CONCAT(SUBSTRING(bs.name, 1, LOCATE('_', bs.name)), 
				         g.name,
                	     SUBSTRING(bs.name, LOCATE(':REDFLY:', bs.name)));    

    SELECT ROW_COUNT()
    INTO updated_transcription_factor_binding_sites_number_with_new_gene_name;

    
    INSERT INTO Gene (
        species_id,
        name,
        identifier,
        genome_assembly_id,
        chrm_id,
        start,
        stop,
        strand)
    SELECT c.species_id,
        new.name,
        new.identifier,
        ga.genome_assembly_id,
        c.chromosome_id,
        new.start,
        new.end,
        new.strand
    FROM Species s
    JOIN GenomeAssembly ga ON s.short_name = species_short_name_in AND 
        ga.release_version = genome_assembly_release_version_in AND
        ga.species_id = s.species_id
    JOIN Chromosome c ON c.species_id = s.species_id AND
        c.genome_assembly_id = ga.genome_assembly_id
    JOIN staging_gene_update new ON new.species_id = s.species_id AND
        new.genome_assembly_id = ga.genome_assembly_id AND
        new.chromosome_name = c.name AND
        new.identifier NOT IN (SELECT identifier 
                               FROM Gene
                               WHERE species_id = species_id_in AND
                                   genome_assembly_id = genome_assembly_id_in);
    
    SELECT ROW_COUNT()
    INTO new_genes_number;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `v_cis_regulatory_module_overlaps`
--

/*!50001 DROP TABLE IF EXISTS `v_cis_regulatory_module_overlaps`*/;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_overlaps`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_cis_regulatory_module_overlaps` AS select `ro`.`rc_id` AS `rc_id`,`ro`.`overlap_id` AS `overlap_id`,`ro`.`sequence_from_species_id` AS `sequence_from_species_id`,`ro`.`current_genome_assembly_release_version` AS `current_genome_assembly_release_version`,`ro`.`chromosome_id` AS `chromosome_id`,`ro`.`current_start` AS `start`,`ro`.`current_end` AS `end`,`ro`.`assayed_in_species_id` AS `assayed_in_species_id`,group_concat(`rt`.`term_id` separator ',') AS `terms` from (((select `r`.`rc_id` AS `rc_id`,`o`.`rc_id` AS `overlap_id`,`r`.`sequence_from_species_id` AS `sequence_from_species_id`,`r`.`current_genome_assembly_release_version` AS `current_genome_assembly_release_version`,`r`.`chromosome_id` AS `chromosome_id`,greatest(`r`.`current_start`,`o`.`current_start`) AS `current_start`,least(`r`.`current_end`,`o`.`current_end`) AS `current_end`,`r`.`assayed_in_species_id` AS `assayed_in_species_id` from (`redfly`.`ReporterConstruct` `r` join `redfly`.`ReporterConstruct` `o` on(`r`.`state` = `o`.`state` and `r`.`current_genome_assembly_release_version` = `o`.`current_genome_assembly_release_version` and `r`.`chromosome_id` = `o`.`chromosome_id` and `r`.`is_crm` = `o`.`is_crm` and `r`.`is_negative` = `o`.`is_negative`)) where `r`.`rc_id` < `o`.`rc_id` and `r`.`state` = 'current' and `r`.`is_crm` = 1 and `r`.`is_negative` = 0) `ro` join `redfly`.`RC_has_ExprTerm` `rt` on(`ro`.`rc_id` = `rt`.`rc_id`)) join `redfly`.`RC_has_ExprTerm` `ot` on(`ro`.`overlap_id` = `ot`.`rc_id`)) where `rt`.`term_id` = `ot`.`term_id` group by `ro`.`rc_id`,`ro`.`overlap_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_cis_regulatory_module_segment_audit`
--

/*!50001 DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_audit`*/;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_audit`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_cis_regulatory_module_segment_audit` AS select `crms`.`crm_segment_id` AS `id`,`crms`.`state` AS `state`,`crms`.`name` AS `name`,`crms`.`pubmed_id` AS `pubmed_id`,`crms`.`curator_id` AS `curator_id`,`curator`.`username` AS `curator_username`,concat(`curator`.`first_name`,' ',`curator`.`last_name`) AS `curator_full_name`,case `crms`.`auditor_id` when NULL then 0 else `crms`.`auditor_id` end AS `auditor_id`,case `crms`.`auditor_id` when NULL then '' else `auditor`.`username` end AS `auditor_username`,case `crms`.`auditor_id` when NULL then '' else concat(`auditor`.`first_name`,' ',`auditor`.`last_name`) end AS `auditor_full_name`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,`ais`.`scientific_name` AS `assayed_in_species_scientific_name`,concat(`g`.`name`,' (',`g`.`identifier`,')') AS `gene_display`,concat(`c`.`name`,' (',`sfs`.`short_name`,')') AS `chromosome_display`,`crms`.`current_start` AS `start`,`crms`.`current_end` AS `end`,concat(`c`.`name`,':',`crms`.`current_start`,'..',`crms`.`current_end`) AS `coordinates`,`crms`.`sequence` AS `sequence`,`crms`.`fbtp` AS `fbtp`,`crms`.`figure_labels` AS `figure_labels`,`e`.`term` AS `evidence`,case `es`.`term` when NULL then '' else `es`.`term` end AS `evidence_subtype`,group_concat(distinct concat(`et`.`identifier`) order by `et`.`term` ASC separator ',') AS `anatomical_expression_identifiers`,group_concat(distinct concat(`et`.`term`) order by `et`.`term` ASC separator ',') AS `anatomical_expression_terms`,group_concat(distinct concat(`et`.`term`,' (',`et`.`identifier`,')') order by `et`.`term` ASC separator ',') AS `anatomical_expression_displays`,`crms`.`notes` AS `notes`,`ss`.`term` AS `sequence_source`,`crms`.`date_added` AS `date_added`,`crms`.`last_update` AS `last_update`,`crms`.`last_audit` AS `last_audit` from (((((((((((`CRMSegment` `crms` join `Users` `curator` on(`crms`.`curator_id` = `curator`.`user_id`)) left join `Users` `auditor` on(`crms`.`auditor_id` = `auditor`.`user_id`)) join `Species` `sfs` on(`crms`.`sequence_from_species_id` = `sfs`.`species_id`)) join `Species` `ais` on(`crms`.`assayed_in_species_id` = `ais`.`species_id`)) join `Gene` `g` on(`crms`.`gene_id` = `g`.`gene_id`)) join `Chromosome` `c` on(`crms`.`chromosome_id` = `c`.`chromosome_id`)) join `EvidenceTerm` `e` on(`crms`.`evidence_id` = `e`.`evidence_id`)) left join `EvidenceSubtypeTerm` `es` on(`crms`.`evidence_subtype_id` = `es`.`evidence_subtype_id`)) join `SequenceSourceTerm` `ss` on(`crms`.`sequence_source_id` = `ss`.`source_id`)) left join `CRMSegment_has_Expression_Term` on(`crms`.`crm_segment_id` = `CRMSegment_has_Expression_Term`.`crm_segment_id`)) left join `ExpressionTerm` `et` on(`CRMSegment_has_Expression_Term`.`term_id` = `et`.`term_id`)) where `crms`.`state` in ('approval','approved','deleted','editing') group by `crms`.`crm_segment_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_cis_regulatory_module_segment_feature_location`
--

/*!50001 DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_feature_location`*/;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_feature_location`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_cis_regulatory_module_segment_feature_location` AS select `crms`.`crm_segment_id` AS `id`,`f`.`type` AS `type`,`f`.`name` AS `name`,`f`.`parent` AS `parent`,`f`.`feature_id` AS `feature_id`,`f`.`identifier` AS `identifier`,`crms`.`current_start` AS `start`,`crms`.`current_end` AS `end`,`f`.`start` AS `f_start`,`f`.`end` AS `f_end`,`f`.`strand` AS `strand`,if(`f`.`strand` = '+',if(`crms`.`current_start` < `f`.`start` + 5,5,if(`crms`.`current_start` > `f`.`end` + 5,3,0)),if(`crms`.`current_end` < `f`.`start` + 5,3,if(`crms`.`current_end` > `f`.`end` + 5,5,0))) AS `relative_start`,if(`f`.`strand` = '+',if(`crms`.`current_end` < `f`.`start` + 5,5,if(`crms`.`current_end` > `f`.`end` + 5,3,0)),if(`crms`.`current_start` < `f`.`start` + 5,3,if(`crms`.`current_start` > `f`.`end` + 5,5,0))) AS `relative_end`,if(`f`.`strand` = '+',if(`crms`.`current_start` < `f`.`start` + 5,abs(`f`.`start` - `crms`.`current_start`),if(`crms`.`current_start` > `f`.`end` + 5,abs(`crms`.`current_start` - `f`.`end`),0)),if(`crms`.`current_end` < `f`.`start` + 5,abs(`f`.`start` - `crms`.`current_end`),if(`crms`.`current_end` > `f`.`end` + 5,abs(`crms`.`current_end` - `f`.`end`),0))) AS `start_dist`,if(`f`.`strand` = '+',if(`crms`.`current_end` < `f`.`start` + 5,abs(`f`.`start` - `crms`.`current_end`),if(`crms`.`current_end` > `f`.`end` + 5,abs(`crms`.`current_end` - `f`.`end`),0)),if(`crms`.`current_start` < `f`.`start` + 5,abs(`f`.`start` - `crms`.`current_start`),if(`crms`.`current_start` > `f`.`end` + 5,abs(`f`.`end` - `crms`.`current_start`),0))) AS `end_dist` from (`Features` `f` left join `CRMSegment` `crms` on(`f`.`gene_id` = `crms`.`gene_id`)) where `crms`.`state` = 'current' order by `crms`.`crm_segment_id`,`f`.`feature_id`,`f`.`parent` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_cis_regulatory_module_segment_file`
--

/*!50001 DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_file`*/;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_file`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_cis_regulatory_module_segment_file` AS select concat('RFSEG:',lpad(`crms`.`entity_id`,10,'0'),'.',lpad(`crms`.`version`,3,'0')) AS `redfly_id`,concat('RFSEG:',lpad(`crms`.`entity_id`,10,'0')) AS `redfly_id_unversioned`,`crms`.`crm_segment_id` AS `crm_segment_id`,`crms`.`pubmed_id` AS `pubmed_id`,`crms`.`fbtp` AS `fbtp`,'REDfly_RFSEG' AS `label`,`crms`.`name` AS `name`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,`ais`.`scientific_name` AS `assayed_in_species_scientific_name`,`g`.`name` AS `gene_name`,`g`.`identifier` AS `gene_identifier`,`crms`.`sequence` AS `sequence`,`e`.`term` AS `evidence_term`,ifnull(`es`.`term`,'') AS `evidence_subtype_term`,`c`.`name` AS `chromosome`,`crms`.`current_start` AS `start`,`crms`.`current_end` AS `end`,ifnull(group_concat(distinct `et`.`identifier` order by `et`.`identifier` ASC separator ','),'') AS `ontology_term` from ((((((((`CRMSegment` `crms` left join `Species` `sfs` on(`crms`.`sequence_from_species_id` = `sfs`.`species_id`)) left join `Species` `ais` on(`crms`.`assayed_in_species_id` = `ais`.`species_id`)) left join `Gene` `g` on(`crms`.`gene_id` = `g`.`gene_id`)) left join `Chromosome` `c` on(`crms`.`chromosome_id` = `c`.`chromosome_id`)) left join `EvidenceTerm` `e` on(`crms`.`evidence_id` = `e`.`evidence_id`)) left join `EvidenceSubtypeTerm` `es` on(`crms`.`evidence_subtype_id` = `es`.`evidence_subtype_id`)) left join `CRMSegment_has_Expression_Term` on(`crms`.`crm_segment_id` = `CRMSegment_has_Expression_Term`.`crm_segment_id`)) left join `ExpressionTerm` `et` on(`CRMSegment_has_Expression_Term`.`term_id` = `et`.`term_id`)) where `crms`.`state` = 'current' group by `crms`.`crm_segment_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_cis_regulatory_module_segment_no_ts_audit`
--

/*!50001 DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_no_ts_audit`*/;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_no_ts_audit`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_cis_regulatory_module_segment_no_ts_audit` AS select `crms`.`crm_segment_id` AS `id`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,`ais`.`scientific_name` AS `assayed_in_species_scientific_name`,concat(`crms`.`name`,' ') AS `name`,concat(`curator`.`first_name`,' ',`curator`.`last_name`) AS `curator_full_name`,`crms`.`state` AS `state`,`crms`.`pubmed_id` AS `pubmed_id`,concat(`c`.`name`,' (',`sfs`.`short_name`,')') AS `chromosome_display`,`crms`.`current_start` AS `start`,`crms`.`current_end` AS `end`,concat(`g`.`name`,' (',`g`.`identifier`,')') AS `gene_display`,concat(`et`.`term`,' (',`et`.`identifier`,')') AS `anatomical_expression_display`,'' AS `on_developmental_stage_display`,'' AS `off_developmental_stage_display`,'' AS `biological_process_display`,'' AS `sex`,'' AS `ectopic`,'' AS `enhancer_or_silencer` from (((((((`CRMSegment` `crms` join `Users` `curator` on(`crms`.`curator_id` = `curator`.`user_id`)) join `Species` `sfs` on(`crms`.`sequence_from_species_id` = `sfs`.`species_id`)) join `Gene` `g` on(`crms`.`gene_id` = `g`.`gene_id`)) join `Chromosome` `c` on(`crms`.`chromosome_id` = `c`.`chromosome_id`)) join `Species` `ais` on(`crms`.`assayed_in_species_id` = `ais`.`species_id`)) join `CRMSegment_has_Expression_Term` `chet` on(`crms`.`crm_segment_id` = `chet`.`crm_segment_id`)) join `ExpressionTerm` `et` on(`chet`.`term_id` = `et`.`term_id`)) where `crms`.`state` in ('approval','approved','deleted','editing') and !exists(select `triplestore_crm_segment`.`crm_segment_id` from `triplestore_crm_segment` where `chet`.`crm_segment_id` = `triplestore_crm_segment`.`crm_segment_id` limit 1) order by concat(`crms`.`name`,' '),concat(`et`.`term`,' (',`et`.`identifier`,')') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_cis_regulatory_module_segment_staging_data_file`
--

/*!50001 DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_staging_data_file`*/;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_staging_data_file`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_cis_regulatory_module_segment_staging_data_file` AS select 'RFSEG' AS `entity_type`,`crms`.`crm_segment_id` AS `parent_id`,`crms`.`pubmed_id` AS `parent_pubmed_id`,`crms`.`name` AS `name`,`ts`.`expression` AS `expression_identifier`,`ts`.`pubmed_id` AS `pubmed_id`,`ts`.`stage_on` AS `stage_on_identifier`,`ts`.`stage_off` AS `stage_off_identifier`,`ts`.`biological_process` AS `biological_process_identifier`,`ts`.`sex` AS `sex`,`ts`.`ectopic` AS `ectopic` from (`CRMSegment` `crms` join `triplestore_crm_segment` `ts` on(`crms`.`crm_segment_id` = `ts`.`crm_segment_id`)) where `crms`.`state` = 'current' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_cis_regulatory_module_segment_ts_audit`
--

/*!50001 DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_ts_audit`*/;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_ts_audit`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_cis_regulatory_module_segment_ts_audit` AS select `crms`.`crm_segment_id` AS `id`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,`ais`.`scientific_name` AS `assayed_in_species_scientific_name`,concat(`crms`.`name`,' ') AS `name`,concat(`curator`.`first_name`,' ',`curator`.`last_name`) AS `curator_full_name`,`crms`.`state` AS `state`,case `ts`.`pubmed_id` when NULL then '' else `ts`.`pubmed_id` end AS `pubmed_id`,concat(`c`.`name`,' (',`sfs`.`short_name`,')') AS `chromosome_display`,`crms`.`current_start` AS `start`,`crms`.`current_end` AS `end`,concat(`g`.`name`,' (',`g`.`identifier`,')') AS `gene_display`,concat(`et`.`term`,' (',`et`.`identifier`,')') AS `anatomical_expression_display`,case `ds_on`.`identifier` when NULL then '' else concat(`ds_on`.`term`,' (',`ds_on`.`identifier`,')') end AS `on_developmental_stage_display`,case `ds_off`.`identifier` when NULL then '' else concat(`ds_off`.`term`,' (',`ds_off`.`identifier`,')') end AS `off_developmental_stage_display`,case `bp`.`go_id` when NULL then '' else concat(`bp`.`term`,' (',`bp`.`go_id`,')') end AS `biological_process_display`,case `ts`.`sex` when NULL then '' else `ts`.`sex` end AS `sex`,case `ts`.`ectopic` when NULL then '' else `ts`.`ectopic` end AS `ectopic`,`ts`.`silencer` AS `enhancer_or_silencer` from ((((((((((`CRMSegment` `crms` join `Users` `curator` on(`crms`.`curator_id` = `curator`.`user_id`)) join `Species` `sfs` on(`crms`.`sequence_from_species_id` = `sfs`.`species_id`)) join `Gene` `g` on(`crms`.`gene_id` = `g`.`gene_id`)) join `Chromosome` `c` on(`crms`.`chromosome_id` = `c`.`chromosome_id`)) join `Species` `ais` on(`crms`.`assayed_in_species_id` = `ais`.`species_id`)) join `triplestore_crm_segment` `ts` on(`crms`.`crm_segment_id` = `ts`.`crm_segment_id`)) join `ExpressionTerm` `et` on(`ts`.`expression` = `et`.`identifier`)) join `DevelopmentalStage` `ds_on` on(`ais`.`species_id` = `ds_on`.`species_id` and `ts`.`stage_on` = `ds_on`.`identifier`)) join `DevelopmentalStage` `ds_off` on(`ais`.`species_id` = `ds_off`.`species_id` and `ts`.`stage_off` = `ds_off`.`identifier`)) left join `BiologicalProcess` `bp` on(`ts`.`biological_process` = `bp`.`go_id`)) where `crms`.`state` in ('approval','approved','deleted','editing') and `et`.`term` <> '' order by `crms`.`name`,`et`.`term` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_cis_regulatory_module_segment_ts_notify_author`
--

/*!50001 DROP TABLE IF EXISTS `v_cis_regulatory_module_segment_ts_notify_author`*/;
/*!50001 DROP VIEW IF EXISTS `v_cis_regulatory_module_segment_ts_notify_author`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_cis_regulatory_module_segment_ts_notify_author` AS select `ts`.`crm_segment_id` AS `crm_segment_id`,`ts`.`expression` AS `expression_identifier`,`ds_on`.`term` AS `stage_on_term`,`ds_off`.`term` AS `stage_off_term`,case `bp`.`term` when NULL then '' else `bp`.`term` end AS `biological_process_term`,ucase(`ts`.`sex`) AS `sex_term`,case `ts`.`ectopic` when 0 then 'F' else 'T' end AS `ectopic_term`,ucase(`ts`.`silencer`) AS `enhancer_or_silencer` from (((((`CRMSegment` `crms` join `Species` `ais` on(`crms`.`assayed_in_species_id` = `ais`.`species_id`)) join `triplestore_crm_segment` `ts` on(`crms`.`crm_segment_id` = `ts`.`crm_segment_id`)) join `DevelopmentalStage` `ds_on` on(`ais`.`species_id` = `ds_on`.`species_id` and `ts`.`stage_on` = `ds_on`.`identifier`)) join `DevelopmentalStage` `ds_off` on(`ais`.`species_id` = `ds_off`.`species_id` and `ts`.`stage_off` = `ds_off`.`identifier`)) left join `BiologicalProcess` `bp` on(`ts`.`biological_process` = `bp`.`go_id`)) where `crms`.`state` = 'approved' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_inferred_cis_regulatory_module_audit`
--

/*!50001 DROP TABLE IF EXISTS `v_inferred_cis_regulatory_module_audit`*/;
/*!50001 DROP VIEW IF EXISTS `v_inferred_cis_regulatory_module_audit`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_inferred_cis_regulatory_module_audit` AS select `icrm`.`icrm_id` AS `id`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,`ais`.`scientific_name` AS `assayed_in_species_scientific_name`,`c`.`name` AS `chromosome`,concat(`c`.`name`,' (',`sfs`.`short_name`,')') AS `chromosome_display`,`icrm`.`current_start` AS `start`,`icrm`.`current_end` AS `end`,concat(`c`.`name`,':',`icrm`.`current_start`,'..',`icrm`.`current_end`) AS `coordinates` from (((`inferred_crm` `icrm` left join `Species` `sfs` on(`icrm`.`sequence_from_species_id` = `sfs`.`species_id`)) left join `Species` `ais` on(`icrm`.`assayed_in_species_id` = `ais`.`species_id`)) left join `Chromosome` `c` on(`icrm`.`chromosome_id` = `c`.`chromosome_id`)) group by `icrm`.`icrm_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_predicted_cis_regulatory_module_audit`
--

/*!50001 DROP TABLE IF EXISTS `v_predicted_cis_regulatory_module_audit`*/;
/*!50001 DROP VIEW IF EXISTS `v_predicted_cis_regulatory_module_audit`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_predicted_cis_regulatory_module_audit` AS select `pcrm`.`predicted_crm_id` AS `id`,`pcrm`.`state` AS `state`,`pcrm`.`name` AS `name`,`pcrm`.`pubmed_id` AS `pubmed_id`,`pcrm`.`curator_id` AS `curator_id`,`curator`.`username` AS `curator_username`,concat(`curator`.`first_name`,' ',`curator`.`last_name`) AS `curator_full_name`,case `pcrm`.`auditor_id` when NULL then 0 else `pcrm`.`auditor_id` end AS `auditor_id`,case `pcrm`.`auditor_id` when NULL then '' else `auditor`.`username` end AS `auditor_username`,case `pcrm`.`auditor_id` when NULL then '' else concat(`auditor`.`first_name`,' ',`auditor`.`last_name`) end AS `auditor_full_name`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,concat(`c`.`name`,' (',`sfs`.`short_name`,')') AS `chromosome_display`,`pcrm`.`current_start` AS `start`,`pcrm`.`current_end` AS `end`,concat(`c`.`name`,':',`pcrm`.`current_start`,'..',`pcrm`.`current_end`) AS `coordinates`,`pcrm`.`sequence` AS `sequence`,`e`.`term` AS `evidence`,case `es`.`term` when NULL then '' else `es`.`term` end AS `evidence_subtype`,group_concat(distinct concat(`et`.`term`,' (',`et`.`identifier`,')') order by `et`.`term` ASC separator ',') AS `anatomical_expression_displays`,`pcrm`.`notes` AS `notes`,`ss`.`term` AS `sequence_source`,`pcrm`.`date_added` AS `date_added`,`pcrm`.`last_update` AS `last_update`,`pcrm`.`last_audit` AS `last_audit` from (((((((((`PredictedCRM` `pcrm` join `Users` `curator` on(`pcrm`.`curator_id` = `curator`.`user_id`)) left join `Users` `auditor` on(`pcrm`.`auditor_id` = `auditor`.`user_id`)) join `Species` `sfs` on(`pcrm`.`sequence_from_species_id` = `sfs`.`species_id`)) join `Chromosome` `c` on(`pcrm`.`chromosome_id` = `c`.`chromosome_id`)) join `EvidenceTerm` `e` on(`pcrm`.`evidence_id` = `e`.`evidence_id`)) left join `EvidenceSubtypeTerm` `es` on(`pcrm`.`evidence_subtype_id` = `es`.`evidence_subtype_id`)) join `SequenceSourceTerm` `ss` on(`pcrm`.`sequence_source_id` = `ss`.`source_id`)) left join `PredictedCRM_has_Expression_Term` on(`pcrm`.`predicted_crm_id` = `PredictedCRM_has_Expression_Term`.`predicted_crm_id`)) left join `ExpressionTerm` `et` on(`PredictedCRM_has_Expression_Term`.`term_id` = `et`.`term_id`)) where `pcrm`.`state` in ('approval','approved','deleted','editing') group by `pcrm`.`predicted_crm_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_predicted_cis_regulatory_module_file`
--

/*!50001 DROP TABLE IF EXISTS `v_predicted_cis_regulatory_module_file`*/;
/*!50001 DROP VIEW IF EXISTS `v_predicted_cis_regulatory_module_file`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_predicted_cis_regulatory_module_file` AS select concat('RFPCRM:',lpad(`pcrm`.`entity_id`,10,'0'),'.',lpad(`pcrm`.`version`,3,'0')) AS `redfly_id`,concat('RFPCRM:',lpad(`pcrm`.`entity_id`,10,'0')) AS `redfly_id_unversioned`,`pcrm`.`predicted_crm_id` AS `predicted_crm_id`,`pcrm`.`pubmed_id` AS `pubmed_id`,'REDfly_PCRM' AS `label`,`pcrm`.`name` AS `name`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,ifnull(`pcrm`.`gene_locus`,'') AS `gene_locus`,ifnull(`pcrm`.`gene_identifiers`,'') AS `gene_identifiers`,`pcrm`.`sequence` AS `sequence`,`e`.`term` AS `evidence_term`,`es`.`term` AS `evidence_subtype_term`,`c`.`name` AS `chromosome`,`pcrm`.`current_start` AS `start`,`pcrm`.`current_end` AS `end`,ifnull(group_concat(distinct `et`.`identifier` order by `et`.`identifier` ASC separator ','),'') AS `ontology_term` from ((((((`PredictedCRM` `pcrm` left join `Species` `sfs` on(`pcrm`.`sequence_from_species_id` = `sfs`.`species_id`)) left join `Chromosome` `c` on(`pcrm`.`chromosome_id` = `c`.`chromosome_id`)) left join `EvidenceTerm` `e` on(`pcrm`.`evidence_id` = `e`.`evidence_id`)) left join `EvidenceSubtypeTerm` `es` on(`pcrm`.`evidence_subtype_id` = `es`.`evidence_subtype_id`)) left join `PredictedCRM_has_Expression_Term` on(`pcrm`.`predicted_crm_id` = `PredictedCRM_has_Expression_Term`.`predicted_crm_id`)) left join `ExpressionTerm` `et` on(`PredictedCRM_has_Expression_Term`.`term_id` = `et`.`term_id`)) where `pcrm`.`state` = 'current' group by `pcrm`.`predicted_crm_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_predicted_cis_regulatory_module_no_ts_audit`
--

/*!50001 DROP TABLE IF EXISTS `v_predicted_cis_regulatory_module_no_ts_audit`*/;
/*!50001 DROP VIEW IF EXISTS `v_predicted_cis_regulatory_module_no_ts_audit`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_predicted_cis_regulatory_module_no_ts_audit` AS select `pcrm`.`predicted_crm_id` AS `id`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,concat(`pcrm`.`name`,' ') AS `name`,concat(`curator`.`first_name`,' ',`curator`.`last_name`) AS `curator_full_name`,`pcrm`.`state` AS `state`,`pcrm`.`pubmed_id` AS `pubmed_id`,concat(`c`.`name`,' (',`sfs`.`short_name`,')') AS `chromosome_display`,`pcrm`.`current_start` AS `start`,`pcrm`.`current_end` AS `end`,concat(`et`.`term`,' (',`et`.`identifier`,')') AS `anatomical_expression_display`,'' AS `on_developmental_stage_display`,'' AS `off_developmental_stage_display`,'' AS `biological_process_display`,'' AS `sex`,'' AS `ectopic`,'' AS `enhancer_or_silencer` from (((((`PredictedCRM` `pcrm` join `Users` `curator` on(`pcrm`.`curator_id` = `curator`.`user_id`)) join `Species` `sfs` on(`pcrm`.`sequence_from_species_id` = `sfs`.`species_id`)) join `Chromosome` `c` on(`pcrm`.`chromosome_id` = `c`.`chromosome_id`)) join `PredictedCRM_has_Expression_Term` `phet` on(`pcrm`.`predicted_crm_id` = `phet`.`predicted_crm_id`)) join `ExpressionTerm` `et` on(`phet`.`term_id` = `et`.`term_id`)) where `pcrm`.`state` in ('approval','approved','deleted','editing') and !exists(select `triplestore_predicted_crm`.`predicted_crm_id` from `triplestore_predicted_crm` where `phet`.`predicted_crm_id` = `triplestore_predicted_crm`.`predicted_crm_id` limit 1) order by concat(`pcrm`.`name`,' '),concat(`et`.`term`,' (',`et`.`identifier`,')') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_predicted_cis_regulatory_module_staging_data_file`
--

/*!50001 DROP TABLE IF EXISTS `v_predicted_cis_regulatory_module_staging_data_file`*/;
/*!50001 DROP VIEW IF EXISTS `v_predicted_cis_regulatory_module_staging_data_file`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_predicted_cis_regulatory_module_staging_data_file` AS select 'RFPCRM' AS `entity_type`,`pcrm`.`predicted_crm_id` AS `parent_id`,`pcrm`.`pubmed_id` AS `parent_pubmed_id`,`pcrm`.`name` AS `name`,`ts`.`expression` AS `expression_identifier`,`ts`.`pubmed_id` AS `pubmed_id`,`ts`.`stage_on` AS `stage_on_identifier`,`ts`.`stage_off` AS `stage_off_identifier`,`ts`.`biological_process` AS `biological_process_identifier`,`ts`.`sex` AS `sex` from (`PredictedCRM` `pcrm` join `triplestore_predicted_crm` `ts` on(`pcrm`.`predicted_crm_id` = `ts`.`predicted_crm_id`)) where `pcrm`.`state` = 'current' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_predicted_cis_regulatory_module_ts_audit`
--

/*!50001 DROP TABLE IF EXISTS `v_predicted_cis_regulatory_module_ts_audit`*/;
/*!50001 DROP VIEW IF EXISTS `v_predicted_cis_regulatory_module_ts_audit`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_predicted_cis_regulatory_module_ts_audit` AS select `pcrm`.`predicted_crm_id` AS `id`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,concat(`pcrm`.`name`,' ') AS `name`,concat(`curator`.`first_name`,' ',`curator`.`last_name`) AS `curator_full_name`,`pcrm`.`state` AS `state`,case `ts`.`pubmed_id` when NULL then '' else `ts`.`pubmed_id` end AS `pubmed_id`,concat(`c`.`name`,' (',`sfs`.`short_name`,')') AS `chromosome_display`,`pcrm`.`current_start` AS `start`,`pcrm`.`current_end` AS `end`,concat(`et`.`term`,' (',`et`.`identifier`,')') AS `anatomical_expression_display`,case `ds_on`.`identifier` when NULL then '' else concat(`ds_on`.`term`,' (',`ds_on`.`identifier`,')') end AS `on_developmental_stage_display`,case `ds_off`.`identifier` when NULL then '' else concat(`ds_off`.`term`,' (',`ds_off`.`identifier`,')') end AS `off_developmental_stage_display`,case `bp`.`go_id` when NULL then '' else concat(`bp`.`term`,' (',`bp`.`go_id`,')') end AS `biological_process_display`,case `ts`.`sex` when NULL then '' else `ts`.`sex` end AS `sex`,`ts`.`silencer` AS `enhancer_or_silencer` from ((((((((`PredictedCRM` `pcrm` join `Users` `curator` on(`pcrm`.`curator_id` = `curator`.`user_id`)) join `Species` `sfs` on(`pcrm`.`sequence_from_species_id` = `sfs`.`species_id`)) join `Chromosome` `c` on(`pcrm`.`chromosome_id` = `c`.`chromosome_id`)) join `triplestore_predicted_crm` `ts` on(`pcrm`.`predicted_crm_id` = `ts`.`predicted_crm_id`)) join `ExpressionTerm` `et` on(`ts`.`expression` = `et`.`identifier`)) join `DevelopmentalStage` `ds_on` on(`sfs`.`species_id` = `ds_on`.`species_id` and `ts`.`stage_on` = `ds_on`.`identifier`)) join `DevelopmentalStage` `ds_off` on(`sfs`.`species_id` = `ds_off`.`species_id` and `ts`.`stage_off` = `ds_off`.`identifier`)) left join `BiologicalProcess` `bp` on(`ts`.`biological_process` = `bp`.`go_id`)) where `pcrm`.`state` in ('approval','approved','deleted','editing') and `et`.`term` <> '' order by `pcrm`.`name`,`et`.`term` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_predicted_cis_regulatory_module_ts_notify_author`
--

/*!50001 DROP TABLE IF EXISTS `v_predicted_cis_regulatory_module_ts_notify_author`*/;
/*!50001 DROP VIEW IF EXISTS `v_predicted_cis_regulatory_module_ts_notify_author`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_predicted_cis_regulatory_module_ts_notify_author` AS select `ts`.`predicted_crm_id` AS `predicted_crm_id`,`ts`.`expression` AS `expression_identifier`,`ds_on`.`term` AS `stage_on_term`,`ds_off`.`term` AS `stage_off_term`,case `bp`.`term` when NULL then '' else `bp`.`term` end AS `biological_process_term`,ucase(`ts`.`sex`) AS `sex_term`,ucase(`ts`.`silencer`) AS `enhancer_or_silencer` from (((((`PredictedCRM` `pcrm` join `Species` `sfs` on(`pcrm`.`sequence_from_species_id` = `sfs`.`species_id`)) join `triplestore_predicted_crm` `ts` on(`pcrm`.`predicted_crm_id` = `ts`.`predicted_crm_id`)) join `DevelopmentalStage` `ds_on` on(`sfs`.`species_id` = `ds_on`.`species_id` and `ts`.`stage_on` = `ds_on`.`identifier`)) join `DevelopmentalStage` `ds_off` on(`sfs`.`species_id` = `ds_off`.`species_id` and `ts`.`stage_off` = `ds_off`.`identifier`)) left join `BiologicalProcess` `bp` on(`ts`.`biological_process` = `bp`.`go_id`)) where `pcrm`.`state` = 'approved' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_reporter_construct_audit`
--

/*!50001 DROP TABLE IF EXISTS `v_reporter_construct_audit`*/;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_audit`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_reporter_construct_audit` AS select `rc`.`rc_id` AS `id`,`rc`.`state` AS `state`,`rc`.`name` AS `name`,`rc`.`pubmed_id` AS `pubmed_id`,`rc`.`curator_id` AS `curator_id`,`curator`.`username` AS `curator_username`,concat(`curator`.`first_name`,' ',`curator`.`last_name`) AS `curator_full_name`,case `rc`.`auditor_id` when NULL then 0 else `rc`.`auditor_id` end AS `auditor_id`,case `rc`.`auditor_id` when NULL then '' else `auditor`.`username` end AS `auditor_username`,case `rc`.`auditor_id` when NULL then '' else concat(`auditor`.`first_name`,' ',`auditor`.`last_name`) end AS `auditor_full_name`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,`ais`.`scientific_name` AS `assayed_in_species_scientific_name`,concat(`g`.`name`,' (',`g`.`identifier`,')') AS `gene_display`,concat(`c`.`name`,' (',`sfs`.`short_name`,')') AS `chromosome_display`,`rc`.`current_start` AS `start`,`rc`.`current_end` AS `end`,concat(`c`.`name`,':',`rc`.`current_start`,'..',`rc`.`current_end`) AS `coordinates`,`rc`.`sequence` AS `sequence`,`rc`.`fbtp` AS `fbtp`,`rc`.`figure_labels` AS `figure_labels`,`e`.`term` AS `evidence`,group_concat(distinct concat(`et`.`identifier`) order by `et`.`term` ASC separator ',') AS `anatomical_expression_identifiers`,group_concat(distinct concat(`et`.`term`) order by `et`.`term` ASC separator ',') AS `anatomical_expression_terms`,group_concat(distinct concat(`et`.`term`,' (',`et`.`identifier`,')') order by `et`.`term` ASC separator ',') AS `anatomical_expression_displays`,`rc`.`notes` AS `notes`,`ss`.`term` AS `sequence_source`,`rc`.`date_added` AS `date_added`,`rc`.`last_update` AS `last_update`,`rc`.`last_audit` AS `last_audit` from ((((((((((`ReporterConstruct` `rc` join `Users` `curator` on(`rc`.`curator_id` = `curator`.`user_id`)) left join `Users` `auditor` on(`rc`.`auditor_id` = `auditor`.`user_id`)) join `Species` `sfs` on(`rc`.`sequence_from_species_id` = `sfs`.`species_id`)) join `Species` `ais` on(`rc`.`assayed_in_species_id` = `ais`.`species_id`)) join `Gene` `g` on(`rc`.`gene_id` = `g`.`gene_id`)) join `Chromosome` `c` on(`rc`.`chromosome_id` = `c`.`chromosome_id`)) join `EvidenceTerm` `e` on(`rc`.`evidence_id` = `e`.`evidence_id`)) join `SequenceSourceTerm` `ss` on(`rc`.`sequence_source_id` = `ss`.`source_id`)) left join `RC_has_ExprTerm` on(`rc`.`rc_id` = `RC_has_ExprTerm`.`rc_id`)) left join `ExpressionTerm` `et` on(`RC_has_ExprTerm`.`term_id` = `et`.`term_id`)) where `rc`.`state` in ('approval','approved','deleted','editing') group by `rc`.`rc_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_reporter_construct_feature_location`
--

/*!50001 DROP TABLE IF EXISTS `v_reporter_construct_feature_location`*/;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_feature_location`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_reporter_construct_feature_location` AS select `rc`.`rc_id` AS `id`,`f`.`type` AS `type`,`f`.`name` AS `name`,`f`.`parent` AS `parent`,`f`.`feature_id` AS `feature_id`,`f`.`identifier` AS `identifier`,`rc`.`current_start` AS `start`,`rc`.`current_end` AS `end`,`f`.`start` AS `f_start`,`f`.`end` AS `f_end`,`f`.`strand` AS `strand`,if(`f`.`strand` = '+',if(`rc`.`current_start` < `f`.`start` + 5,5,if(`rc`.`current_start` > `f`.`end` + 5,3,0)),if(`rc`.`current_end` < `f`.`start` + 5,3,if(`rc`.`current_end` > `f`.`end` + 5,5,0))) AS `relative_start`,if(`f`.`strand` = '+',if(`rc`.`current_end` < `f`.`start` + 5,5,if(`rc`.`current_end` > `f`.`end` + 5,3,0)),if(`rc`.`current_start` < `f`.`start` + 5,3,if(`rc`.`current_start` > `f`.`end` + 5,5,0))) AS `relative_end`,if(`f`.`strand` = '+',if(`rc`.`current_start` < `f`.`start` + 5,abs(`f`.`start` - `rc`.`current_start`),if(`rc`.`current_start` > `f`.`end` + 5,abs(`rc`.`current_start` - `f`.`end`),0)),if(`rc`.`current_end` < `f`.`start` + 5,abs(`f`.`start` - `rc`.`current_end`),if(`rc`.`current_end` > `f`.`end` + 5,abs(`rc`.`current_end` - `f`.`end`),0))) AS `start_dist`,if(`f`.`strand` = '+',if(`rc`.`current_end` < `f`.`start` + 5,abs(`f`.`start` - `rc`.`current_end`),if(`rc`.`current_end` > `f`.`end` + 5,abs(`rc`.`current_end` - `f`.`end`),0)),if(`rc`.`current_start` < `f`.`start` + 5,abs(`f`.`start` - `rc`.`current_start`),if(`rc`.`current_start` > `f`.`end` + 5,abs(`f`.`end` - `rc`.`current_start`),0))) AS `end_dist` from (`Features` `f` left join `ReporterConstruct` `rc` on(`f`.`gene_id` = `rc`.`gene_id`)) where `rc`.`state` = 'current' order by `rc`.`rc_id`,`f`.`feature_id`,`f`.`parent` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_reporter_construct_file`
--

/*!50001 DROP TABLE IF EXISTS `v_reporter_construct_file`*/;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_file`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_reporter_construct_file` AS select concat('RFRC:',lpad(`rc`.`entity_id`,10,'0'),'.',lpad(`rc`.`version`,3,'0')) AS `redfly_id`,concat('RFRC:',lpad(`rc`.`entity_id`,10,'0')) AS `redfly_id_unversioned`,`rc`.`rc_id` AS `rc_id`,`rc`.`pubmed_id` AS `pubmed_id`,`rc`.`fbtp` AS `fbtp`,case when `rc`.`is_crm` = 1 then 'REDfly_CRM' when `rc`.`cell_culture_only` = 1 then 'REDfly_RC_CLO' else 'REDfly_RC' end AS `label`,`rc`.`is_crm` AS `is_crm`,`rc`.`cell_culture_only` AS `cell_culture_only`,`rc`.`name` AS `name`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,`ais`.`scientific_name` AS `assayed_in_species_scientific_name`,`g`.`name` AS `gene_name`,`g`.`identifier` AS `gene_identifier`,`rc`.`sequence` AS `sequence`,`e`.`term` AS `evidence_term`,`c`.`name` AS `chromosome`,`rc`.`current_start` AS `start`,`rc`.`current_end` AS `end`,ifnull(group_concat(distinct `tfbs`.`name` order by `tfbs`.`name` ASC separator ','),'') AS `associated_tfbs`,ifnull(group_concat(distinct `et`.`identifier` order by `et`.`identifier` ASC separator ','),'') AS `ontology_term` from (((((((((`ReporterConstruct` `rc` left join `Species` `sfs` on(`rc`.`sequence_from_species_id` = `sfs`.`species_id`)) left join `Species` `ais` on(`rc`.`assayed_in_species_id` = `ais`.`species_id`)) left join `Gene` `g` on(`rc`.`gene_id` = `g`.`gene_id`)) left join `Chromosome` `c` on(`rc`.`chromosome_id` = `c`.`chromosome_id`)) left join `EvidenceTerm` `e` on(`rc`.`evidence_id` = `e`.`evidence_id`)) left join `RC_associated_BS` on(`rc`.`rc_id` = `RC_associated_BS`.`rc_id`)) left join `BindingSite` `tfbs` on(`RC_associated_BS`.`tfbs_id` = `tfbs`.`tfbs_id`)) left join `RC_has_ExprTerm` on(`rc`.`rc_id` = `RC_has_ExprTerm`.`rc_id`)) left join `ExpressionTerm` `et` on(`RC_has_ExprTerm`.`term_id` = `et`.`term_id`)) where `rc`.`state` = 'current' group by `rc`.`rc_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_reporter_construct_no_ts_audit`
--

/*!50001 DROP TABLE IF EXISTS `v_reporter_construct_no_ts_audit`*/;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_no_ts_audit`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_reporter_construct_no_ts_audit` AS select `rc`.`rc_id` AS `id`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,`ais`.`scientific_name` AS `assayed_in_species_scientific_name`,concat(`rc`.`name`,' ') AS `name`,concat(`curator`.`first_name`,' ',`curator`.`last_name`) AS `curator_full_name`,`rc`.`state` AS `state`,`rc`.`pubmed_id` AS `pubmed_id`,concat(`c`.`name`,' (',`sfs`.`short_name`,')') AS `chromosome_display`,`rc`.`current_start` AS `start`,`rc`.`current_end` AS `end`,concat(`g`.`name`,' (',`g`.`identifier`,')') AS `gene_display`,concat(`et`.`term`,' (',`et`.`identifier`,')') AS `anatomical_expression_display`,'' AS `on_developmental_stage_display`,'' AS `off_developmental_stage_display`,'' AS `biological_process_display`,'' AS `sex`,'' AS `ectopic`,'' AS `enhancer_or_silencer` from (((((((`ReporterConstruct` `rc` join `Users` `curator` on(`rc`.`curator_id` = `curator`.`user_id`)) join `Species` `sfs` on(`rc`.`sequence_from_species_id` = `sfs`.`species_id`)) join `Gene` `g` on(`rc`.`gene_id` = `g`.`gene_id`)) join `Chromosome` `c` on(`rc`.`chromosome_id` = `c`.`chromosome_id`)) join `Species` `ais` on(`rc`.`assayed_in_species_id` = `ais`.`species_id`)) join `RC_has_ExprTerm` `rhet` on(`rc`.`rc_id` = `rhet`.`rc_id`)) join `ExpressionTerm` `et` on(`rhet`.`term_id` = `et`.`term_id`)) where `rc`.`state` in ('approval','approved','deleted','editing') and !exists(select `triplestore_rc`.`rc_id` from `triplestore_rc` where `rhet`.`rc_id` = `triplestore_rc`.`rc_id` limit 1) order by concat(`rc`.`name`,' '),concat(`et`.`term`,' (',`et`.`identifier`,')') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_reporter_construct_staging_data_file`
--

/*!50001 DROP TABLE IF EXISTS `v_reporter_construct_staging_data_file`*/;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_staging_data_file`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_reporter_construct_staging_data_file` AS select 'RFRC' AS `entity_type`,`rc`.`rc_id` AS `parent_id`,`rc`.`pubmed_id` AS `parent_pubmed_id`,`rc`.`name` AS `name`,`ts`.`expression` AS `expression_identifier`,`ts`.`pubmed_id` AS `pubmed_id`,`ts`.`stage_on` AS `stage_on_identifier`,`ts`.`stage_off` AS `stage_off_identifier`,`ts`.`biological_process` AS `biological_process_identifier`,`ts`.`sex` AS `sex`,`ts`.`ectopic` AS `ectopic` from (`ReporterConstruct` `rc` join `triplestore_rc` `ts` on(`rc`.`rc_id` = `ts`.`rc_id`)) where `rc`.`state` = 'current' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_reporter_construct_ts_audit`
--

/*!50001 DROP TABLE IF EXISTS `v_reporter_construct_ts_audit`*/;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_ts_audit`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_reporter_construct_ts_audit` AS select `rc`.`rc_id` AS `id`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,`ais`.`scientific_name` AS `assayed_in_species_scientific_name`,concat(`rc`.`name`,' ') AS `name`,concat(`curator`.`first_name`,' ',`curator`.`last_name`) AS `curator_full_name`,`rc`.`state` AS `state`,case `ts`.`pubmed_id` when NULL then '' else `ts`.`pubmed_id` end AS `pubmed_id`,concat(`c`.`name`,' (',`sfs`.`short_name`,')') AS `chromosome_display`,`rc`.`current_start` AS `start`,`rc`.`current_end` AS `end`,concat(`g`.`name`,' (',`g`.`identifier`,')') AS `gene_display`,concat(`et`.`term`,' (',`et`.`identifier`,')') AS `anatomical_expression_display`,case `ds_on`.`identifier` when NULL then '' else concat(`ds_on`.`term`,' (',`ds_on`.`identifier`,')') end AS `on_developmental_stage_display`,case `ds_off`.`identifier` when NULL then '' else concat(`ds_off`.`term`,' (',`ds_off`.`identifier`,')') end AS `off_developmental_stage_display`,case `bp`.`go_id` when NULL then '' else concat(`bp`.`term`,' (',`bp`.`go_id`,')') end AS `biological_process_display`,case `ts`.`sex` when NULL then '' else `ts`.`sex` end AS `sex`,case `ts`.`ectopic` when NULL then '' else `ts`.`ectopic` end AS `ectopic`,`ts`.`silencer` AS `enhancer_or_silencer` from ((((((((((`ReporterConstruct` `rc` join `Users` `curator` on(`rc`.`curator_id` = `curator`.`user_id`)) join `Species` `sfs` on(`rc`.`sequence_from_species_id` = `sfs`.`species_id`)) join `Gene` `g` on(`rc`.`gene_id` = `g`.`gene_id`)) join `Chromosome` `c` on(`rc`.`chromosome_id` = `c`.`chromosome_id`)) join `Species` `ais` on(`rc`.`assayed_in_species_id` = `ais`.`species_id`)) join `triplestore_rc` `ts` on(`rc`.`rc_id` = `ts`.`rc_id`)) join `ExpressionTerm` `et` on(`ts`.`expression` = `et`.`identifier`)) join `DevelopmentalStage` `ds_on` on(`ais`.`species_id` = `ds_on`.`species_id` and `ts`.`stage_on` = `ds_on`.`identifier`)) join `DevelopmentalStage` `ds_off` on(`ais`.`species_id` = `ds_off`.`species_id` and `ts`.`stage_off` = `ds_off`.`identifier`)) left join `BiologicalProcess` `bp` on(`ts`.`biological_process` = `bp`.`go_id`)) where `rc`.`state` in ('approval','approved','deleted','editing') and `et`.`term` <> '' order by `rc`.`name`,`et`.`term` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_reporter_construct_ts_notify_author`
--

/*!50001 DROP TABLE IF EXISTS `v_reporter_construct_ts_notify_author`*/;
/*!50001 DROP VIEW IF EXISTS `v_reporter_construct_ts_notify_author`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_reporter_construct_ts_notify_author` AS select `ts`.`rc_id` AS `rc_id`,`ts`.`expression` AS `expression_identifier`,`ds_on`.`term` AS `stage_on_term`,`ds_off`.`term` AS `stage_off_term`,case `bp`.`term` when NULL then '' else `bp`.`term` end AS `biological_process_term`,ucase(`ts`.`sex`) AS `sex_term`,case `ts`.`ectopic` when 0 then 'F' else 'T' end AS `ectopic_term`,ucase(`ts`.`silencer`) AS `enhancer_or_silencer` from (((((`ReporterConstruct` `rc` join `Species` `ais` on(`rc`.`assayed_in_species_id` = `ais`.`species_id`)) join `triplestore_rc` `ts` on(`rc`.`rc_id` = `ts`.`rc_id`)) join `DevelopmentalStage` `ds_on` on(`ais`.`species_id` = `ds_on`.`species_id` and `ts`.`stage_on` = `ds_on`.`identifier`)) join `DevelopmentalStage` `ds_off` on(`ais`.`species_id` = `ds_off`.`species_id` and `ts`.`stage_off` = `ds_off`.`identifier`)) left join `BiologicalProcess` `bp` on(`ts`.`biological_process` = `bp`.`go_id`)) where `rc`.`state` = 'approved' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_transcription_factor_binding_site_audit`
--

/*!50001 DROP TABLE IF EXISTS `v_transcription_factor_binding_site_audit`*/;
/*!50001 DROP VIEW IF EXISTS `v_transcription_factor_binding_site_audit`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_transcription_factor_binding_site_audit` AS select `tfbs`.`tfbs_id` AS `id`,`tfbs`.`state` AS `state`,`tfbs`.`name` AS `name`,`tfbs`.`pubmed_id` AS `pubmed_id`,`tfbs`.`curator_id` AS `curator_id`,concat(`u`.`first_name`,' ',`u`.`last_name`) AS `curator_full_name`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,`ais`.`scientific_name` AS `assayed_in_species_scientific_name`,concat(`g`.`name`,' (',`g`.`identifier`,')') AS `gene_display`,concat(`g2`.`name`,' (',`g2`.`identifier`,')') AS `transcription_factor_display`,concat(`c`.`name`,' (',`sfs`.`short_name`,')') AS `chromosome_display`,`tfbs`.`current_start` AS `start`,`tfbs`.`current_end` AS `end`,concat(`c`.`name`,':',`tfbs`.`current_start`,'..',`tfbs`.`current_end`) AS `coordinates`,`tfbs`.`notes` AS `notes`,`tfbs`.`date_added` AS `date_added`,`tfbs`.`last_update` AS `last_update` from ((((((`BindingSite` `tfbs` join `Species` `sfs` on(`tfbs`.`sequence_from_species_id` = `sfs`.`species_id`)) join `Species` `ais` on(`tfbs`.`assayed_in_species_id` = `ais`.`species_id`)) join `Users` `u` on(`tfbs`.`curator_id` = `u`.`user_id`)) join `Gene` `g` on(`tfbs`.`gene_id` = `g`.`gene_id`)) join `Gene` `g2` on(`tfbs`.`tf_id` = `g2`.`gene_id`)) join `Chromosome` `c` on(`tfbs`.`chromosome_id` = `c`.`chromosome_id`)) where `tfbs`.`state` in ('approval','approved','editing','rejected') order by `tfbs`.`tfbs_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_transcription_factor_binding_site_feature_location`
--

/*!50001 DROP TABLE IF EXISTS `v_transcription_factor_binding_site_feature_location`*/;
/*!50001 DROP VIEW IF EXISTS `v_transcription_factor_binding_site_feature_location`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_transcription_factor_binding_site_feature_location` AS select `tfbs`.`tfbs_id` AS `id`,`f`.`type` AS `type`,`f`.`name` AS `name`,`f`.`parent` AS `parent`,`f`.`feature_id` AS `feature_id`,`f`.`identifier` AS `identifier`,`tfbs`.`current_start` AS `current_start`,`tfbs`.`current_end` AS `current_end`,`f`.`start` AS `f_start`,`f`.`end` AS `f_end`,`f`.`strand` AS `strand`,if(`f`.`strand` = '+',if(`tfbs`.`current_start` < `f`.`start` + 5,5,if(`tfbs`.`current_start` > `f`.`end` + 5,3,0)),if(`tfbs`.`current_end` < `f`.`start` + 5,3,if(`tfbs`.`current_end` > `f`.`end` + 5,5,0))) AS `relative_start`,if(`f`.`strand` = '+',if(`tfbs`.`current_end` < `f`.`start` + 5,5,if(`tfbs`.`current_end` > `f`.`end` + 5,3,0)),if(`tfbs`.`current_start` < `f`.`start` + 5,3,if(`tfbs`.`current_start` > `f`.`end` + 5,5,0))) AS `relative_end`,if(`f`.`strand` = '+',if(`tfbs`.`current_start` < `f`.`start` + 5,abs(`f`.`start` - `tfbs`.`current_start`),if(`tfbs`.`current_start` > `f`.`end` + 5,abs(`tfbs`.`current_start` - `f`.`end`),0)),if(`tfbs`.`current_end` < `f`.`start` + 5,abs(`f`.`start` - `tfbs`.`current_end`),if(`tfbs`.`current_end` > `f`.`end` + 5,abs(`tfbs`.`current_end` - `f`.`end`),0))) AS `start_dist`,if(`f`.`strand` = '+',if(`tfbs`.`current_end` < `f`.`start` + 5,abs(`f`.`start` - `tfbs`.`current_end`),if(`tfbs`.`current_end` > `f`.`end` + 5,abs(`tfbs`.`current_end` - `f`.`end`),0)),if(`tfbs`.`current_start` < `f`.`start` + 5,abs(`f`.`start` - `tfbs`.`current_start`),if(`tfbs`.`current_start` > `f`.`end` + 5,abs(`f`.`end` - `tfbs`.`current_start`),0))) AS `end_dist` from (`Features` `f` left join `BindingSite` `tfbs` on(`f`.`gene_id` = `tfbs`.`gene_id`)) where `tfbs`.`state` = 'current' order by `tfbs`.`tfbs_id`,`f`.`feature_id`,`f`.`parent` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_transcription_factor_binding_site_file`
--

/*!50001 DROP TABLE IF EXISTS `v_transcription_factor_binding_site_file`*/;
/*!50001 DROP VIEW IF EXISTS `v_transcription_factor_binding_site_file`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`redfly`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `v_transcription_factor_binding_site_file` AS select concat('RFTF:',lpad(`tfbs`.`entity_id`,10,'0'),'.',lpad(`tfbs`.`version`,3,'0')) AS `redfly_id`,concat('RFTF:',lpad(`tfbs`.`entity_id`,10,'0')) AS `redfly_id_unversioned`,`tfbs`.`tfbs_id` AS `tfbs_id`,`tfbs`.`pubmed_id` AS `pubmed_id`,'REDfly_TFBS' AS `label`,`tfbs`.`name` AS `name`,`sfs`.`scientific_name` AS `sequence_from_species_scientific_name`,`ais`.`scientific_name` AS `assayed_in_species_scientific_name`,`g`.`name` AS `gene_name`,`tf`.`name` AS `tf_name`,`g`.`identifier` AS `gene_identifier`,`tf`.`identifier` AS `tf_identifier`,`tfbs`.`sequence` AS `sequence`,`tfbs`.`sequence_with_flank` AS `sequence_with_flank`,`e`.`term` AS `evidence_term`,`c`.`name` AS `chromosome`,`tfbs`.`current_start` AS `start`,`tfbs`.`current_end` AS `end`,ifnull(group_concat(distinct `rc`.`name` order by `rc`.`name` ASC separator ','),'') AS `associated_rc`,ifnull(group_concat(distinct `et`.`identifier` order by `et`.`identifier` ASC separator ','),'') AS `ontology_term` from ((((((((((`BindingSite` `tfbs` left join `Species` `sfs` on(`tfbs`.`sequence_from_species_id` = `sfs`.`species_id`)) left join `Species` `ais` on(`tfbs`.`assayed_in_species_id` = `ais`.`species_id`)) left join `Gene` `g` on(`tfbs`.`gene_id` = `g`.`gene_id`)) left join `Gene` `tf` on(`tfbs`.`tf_id` = `tf`.`gene_id`)) left join `EvidenceTerm` `e` on(`tfbs`.`evidence_id` = `e`.`evidence_id`)) left join `Chromosome` `c` on(`tfbs`.`chromosome_id` = `c`.`chromosome_id`)) left join `RC_associated_BS` on(`tfbs`.`tfbs_id` = `RC_associated_BS`.`tfbs_id`)) left join `ReporterConstruct` `rc` on(`RC_associated_BS`.`rc_id` = `rc`.`rc_id`)) left join `RC_has_ExprTerm` on(`RC_associated_BS`.`rc_id` = `RC_has_ExprTerm`.`rc_id`)) left join `ExpressionTerm` `et` on(`RC_has_ExprTerm`.`term_id` = `et`.`term_id`)) where `tfbs`.`state` = 'current' group by `tfbs`.`tfbs_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-08-19 15:03:27
