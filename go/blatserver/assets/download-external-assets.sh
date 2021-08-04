#!/bin/sh

set -e;

# The Aedes aegypti species
#
wget https://vectorbase.org/common/downloads/release-49/AaegyptiLVP_AGWG/fasta/data/VectorBase-49_AaegyptiLVP_AGWG_Genome.fasta
echo "Standardizing the chromosome names..."
echo "...AaegL5_1"
sed -i "/>AaegL5_1/c\>1" VectorBase-49_AaegyptiLVP_AGWG_Genome.fasta
echo "...AaegL5_2"
sed -i "/>AaegL5_2/c\>2" VectorBase-49_AaegyptiLVP_AGWG_Genome.fasta
echo "...AaegL5_3"
sed -i "/>AaegL5_3/c\>3" VectorBase-49_AaegyptiLVP_AGWG_Genome.fasta
echo "...AaegL5_MT"
sed -i "/>AaegL5_MT/c\>MT" VectorBase-49_AaegyptiLVP_AGWG_Genome.fasta
echo "Filtering the chromosome names..."
awk '{ if ((NR>1)&&($0~/^>/)) { printf("\n%s", $0); } else if (NR==1) { printf("%s", $0); } else { printf("\t%s", $0); } }' VectorBase-49_AaegyptiLVP_AGWG_Genome.fasta | grep -E -i -w "1|2|3|MT" - | tr "\t" "\n" > skinned_VectorBase-49_AaegyptiLVP_AGWG_Genome.fasta
rm VectorBase-49_AaegyptiLVP_AGWG_Genome.fasta
echo "Building up aaeg5.2bit..."
./faToTwoBit skinned_VectorBase-49_AaegyptiLVP_AGWG_Genome.fasta aaeg5.2bit
rm skinned_VectorBase-49_AaegyptiLVP_AGWG_Genome.fasta
./twoBitInfo aaeg5.2bit stdout | sort -k2rn > aaeg5_chromosomes_information.txt

# The Anopheles gambiae species
#
wget https://vectorbase.org/common/downloads/release-49/AgambiaePEST/fasta/data/VectorBase-49_AgambiaePEST_Genome.fasta
echo "Standardizing the chromosome names..."
echo "...AgamP4_2L"
sed -i "/>AgamP4_2L/c\>2L" VectorBase-49_AgambiaePEST_Genome.fasta
echo "...AgamP4_2R"
sed -i "/>AgamP4_2R/c\>2R" VectorBase-49_AgambiaePEST_Genome.fasta
echo "...AgamP4_3L"
sed -i "/>AgamP4_3L/c\>3L" VectorBase-49_AgambiaePEST_Genome.fasta
echo "...AgamP4_3R"
sed -i "/>AgamP4_3R/c\>3R" VectorBase-49_AgambiaePEST_Genome.fasta
echo "...AgamP4_UNKN"
sed -i "/>AgamP4_UNKN/c\>UNKN" VectorBase-49_AgambiaePEST_Genome.fasta
echo "...AgamP4_X"
sed -i "/>AgamP4_X/c\>X" VectorBase-49_AgambiaePEST_Genome.fasta
echo "...AgamP4_Y_unplaced"
sed -i "/>AgamP4_Y_unplaced/c\>Y_unplaced" VectorBase-49_AgambiaePEST_Genome.fasta
echo "...AgamP4_Mt"
sed -i "/>AgamP4_Mt/c\>Mt" VectorBase-49_AgambiaePEST_Genome.fasta
echo "Filtering the chromosome names..."
awk '{ if ((NR>1)&&($0~/^>/)) { printf("\n%s", $0); } else if (NR==1) { printf("%s", $0); } else { printf("\t%s", $0); } }' VectorBase-49_AgambiaePEST_Genome.fasta | grep -E -i -w "2R|3R|2L|UNKN|3L|X|Y_unplaced|Mt" - | tr "\t" "\n" > skinned_VectorBase-49_AgambiaePEST_Genome.fasta
rm VectorBase-49_AgambiaePEST_Genome.fasta
echo "Building up agam4.2bit..."
./faToTwoBit skinned_VectorBase-49_AgambiaePEST_Genome.fasta agam4.2bit
rm skinned_VectorBase-49_AgambiaePEST_Genome.fasta
./twoBitInfo agam4.2bit stdout | sort -k2rn > agam4_chromosomes_information.txt

# The Drosophila melanogaster species
# 
wget -O - http://hgdownload.soe.ucsc.edu/goldenPath/dm6/bigZips/dm6.2bit > ./dm6.2bit
./twoBitToFa dm6.2bit dm6.fasta
echo "Filtering the chromosome names..."
awk '{ if ((NR>1)&&($0~/^>/)) { printf("\n%s", $0); } else if (NR==1) { printf("%s", $0); } else { printf("\t%s", $0); } }' dm6.fasta | grep -E -i -w "chr3R|chr3L|chr2R|chrX|chr2L|chrY|chr4" - | tr "\t" "\n" > skinned_dm6.fasta
rm dm6.fasta
echo "Building up agam4.2bit..."
./faToTwoBit skinned_dm6.fasta dm6.2bit
rm skinned_dm6.fasta
./twoBitInfo dm6.2bit stdout | sort -k2rn > dm6_chromosomes_information.txt

# The Tribolium castaneum species
#
wget https://i5k.nal.usda.gov/data/Arthropoda/tricas-%28Tribolium_castaneum%29/Current%20Genome%20Assembly/1.Genome%20Assembly/Tcas5.2-GCF_000002335.3/Scaffolds/GCF_000002335.3_Tcas5.2_genomic_RefSeqIDs.fna.gz
gunzip GCF_000002335.3_Tcas5.2_genomic_RefSeqIDs.fna.gz
echo "Filtering the chromosome and scaffold names..."
targeted_chromosome_and_scaffold_names=$(cat tcas.name_mapping.tsv | cut -f2 | cut -d':' -f1 | sort | uniq | paste -s -d'|')
awk '{ if ((NR>1)&&($0~/^>/)) { printf("\n%s", $0); } else if (NR==1) { printf("%s", $0); } else { printf("\t%s", $0); } }' GCF_000002335.3_Tcas5.2_genomic_RefSeqIDs.fna | grep -E -i $targeted_chromosome_and_scaffold_names - | tr "\t" "\n" > skinned_GCF_000002335.3_Tcas5.2_genomic_RefSeqIDs.fna
rm GCF_000002335.3_Tcas5.2_genomic_RefSeqIDs.fna
echo "Building up tcas5.2.2bit..."
./faToTwoBit skinned_GCF_000002335.3_Tcas5.2_genomic_RefSeqIDs.fna tcas5.2.2bit
rm skinned_GCF_000002335.3_Tcas5.2_genomic_RefSeqIDs.fna
./twoBitInfo tcas5.2.2bit stdout | sort -k2rn > tcas5.2_chromosomes_and_scaffolds_information.txt