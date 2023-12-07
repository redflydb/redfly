#!/bin/sh

set -e;

# The Aedes aegypti species
#
# Get the VectorBase genome in the GFF format for the Aedes aegypti species
wget -O - https://vectorbase.org/common/downloads/release-53/AaegyptiLVP_AGWG/gff/data/VectorBase-53_AaegyptiLVP_AGWG.gff > ./raw_aaeg.gff
# Filter only the necessary terms from the VectorBase genome of the Aedes aegypti species
sed -i 's/AaegL5_1/1/g' raw_aaeg.gff
sed -i 's/AaegL5_2/2/g' raw_aaeg.gff
sed -i 's/AaegL5_3/3/g' raw_aaeg.gff
sed -i 's/AaegL5_Mt/Mt/g' raw_aaeg.gff
cat raw_aaeg.gff | awk -F "\t" 'NF == 9 \
        && $1 ~ /^(1|2|3|Mt)$/ \
        && $3 ~ /^(CDS|exon|five_prime_UTR|gene|ncRNA_gene|pseudogene|three_prime_UTR)$/' \
    > ./aaeg.gff;
rm raw_aaeg.gff

# The Anopheles gambiae species
#
# Get the VectorBase genome in the GFF format for the Anopheles gambiae species
wget -O - https://vectorbase.org/common/downloads/release-53/AgambiaePEST/gff/data/VectorBase-53_AgambiaePEST.gff > ./raw_agam.gff
# Filter only the necessary terms from the VectorBase genome of the Anopheles gambiae species
sed -i 's/AgamP4_2L/2L/g' raw_agam.gff
sed -i 's/AgamP4_2R/2R/g' raw_agam.gff
sed -i 's/AgamP4_3L/3L/g' raw_agam.gff
sed -i 's/AgamP4_3R/3R/g' raw_agam.gff
sed -i 's/AgamP4_Mt/Mt/g' raw_agam.gff
sed -i 's/AgamP4_UNKN/UNKN/g' raw_agam.gff
sed -i 's/AgamP4_X/X/g' raw_agam.gff
sed -i 's/AgamP4_Y_unplaced/Y_unplaced/g' raw_agam.gff
cat raw_agam.gff | awk -F "\t" 'NF == 9 \
        && $1 ~ /^(2L|2R|3L|3R|Mt|UNKN|X|Y_unplaced)$/ \
        && $3 ~ /^(CDS|exon|five_prime_UTR|gene|ncRNA_gene|pseudogene|three_prime_UTR)$/' \
    > ./agam.gff;
rm raw_agam.gff
# Get the VectorBase anatomy ontology for the Anopheles gambiae species
# Note: it is no longer available in the remote website
#wget -O - https://www.vectorbase.org/download/anatomytgma1122013-06obogz | gunzip > ./tgma.obo

# The Drosophila melanogaster species
#
# Get the FlyBase genome in the GFF format for the Drosophila melanogaster species
wget -O - ftp://ftp.flybase.net/genomes/dmel/current/gff/dmel-all-r*.gff.gz | gunzip -c > dmel-all-no-analysis.gff;
# Filter only the necessary terms from the FlyBase genome of the Drosophila melanogaster species
cat dmel-all-no-analysis.gff | awk -F "\t" 'NF == 9 \
        && $1 ~ /^(2L|2R|3L|3R|4|U|X|Y|211000022278279|211000022278436|211000022278449|211000022278760|211000022279165|211000022279188|211000022279264|211000022279392|211000022279681|211000022280328|211000022280341211000022280347|211000022280481|211000022280494|211000022280703|Unmapped_Scaffold_8_D1580_D1567)$/ \
        && $3 ~ /^(CDS|exon|five_prime_UTR|gene|intron|mRNA|ncRNA|snoRNA|snRNA|pseudogene|three_prime_UTR|tRNA)$/' \
    > ./dmel.gff;
# Get the FlyBase anatomy ontology for the Drosophila melanogaster species
wget -O - http://purl.obolibrary.org/obo/fbbt.owl > ./fbbt.owl;
# Get the FlyBase developmental ontology for the Drosophila melanogaster species
wget -O - http://purl.obolibrary.org/obo/fbdv.owl > ./fbdv.owl;
# Pull the release versions and dates from the downloaded files
echo -n 'Gene Annotation Version: ' > releases.log
fgrep 'genome-build' dmel-all-no-analysis.gff | awk '{ match($0, /r([0-9.]{1,})/, version); print version[1]; }' >> releases.log
echo -n 'Anatomy Ontology Date: ' >> releases.log
fgrep 'owl:versionIRI' fbbt.owl | awk '{ match($0, /[0-9]{,4}-[0-9]{,2}-[0-9]{,2}/, date); print date[0];}' >> releases.log
echo -n 'Development Ontology Date: ' >> releases.log
fgrep 'owl:versionIRI' fbbt.owl | awk '{ match($0, /[0-9]{,4}-[0-9]{,2}-[0-9]{,2}/, date); print date[0];}' >> releases.log
echo -n 'GO Ontology Date: ' >> releases.log
fgrep 'owl:versionIRI' go.owl | awk '{ match($0, /[0-9]{,4}-[0-9]{,2}-[0-9]{,2}/, date); print date[0];}' >> releases.log
rm dmel-all-no-analysis.gff

# The Tribolium castaneum species
#
# Get the USDA genome in the GFF format for the Tribolium castaneum species
wget -O - "https://i5k.nal.usda.gov/data/Arthropoda/tricas-(Tribolium_castaneum)/Current Genome Assembly/2.Official or Primary Gene Set/TCAS_OGS_v3/GCA_000002335.3_Tcas5.2_genomic_RefSeqIDs.gff" > ./raw_tcas.gff
# Filter only the necessary terms from the  genome of the Tribolium castaneum species
cat raw_tcas.gff | awk -F "\t" 'NF == 9 \
        && $1 ~ /^(NC_|NW_)/ \
        && $3 ~ /^(CDS|exon|five_prime_UTR|gene|intron|mRNA|ncRNA|snoRNA|snRNA|pseudogene|three_prime_UTR|tRNA)$/' \
    > ./tcas.gff;
rm raw_tcas.gff
# Setting the right gene nomenclature preferred by the PI for the Tribolium castaneum species
sed -i 's/AUGUSTUS-3.0.2_/TC0/g' tcas.gff
sed -i 's/Akh2/TC030001/g' tcas.gff
sed -i 's/Atn/TC006446/g' tcas.gff
sed -i 's/Atp5s/TC030054/g' tcas.gff
sed -i 's/FGF-8/TC000278/g' tcas.gff
sed -i 's/Fz2/TC003407/g' tcas.gff
sed -i 's/GLEAN_03595-OG17340/TC030638/g' tcas.gff
sed -i 's/GLEAN_04788-OG24003/TC030689/g' tcas.gff
sed -i 's/GLEAN_09215-OG24222/TC030692/g' tcas.gff
sed -i 's/GLEAN_10008-OG25682/TC030700/g' tcas.gff
sed -i 's/GLEAN_10179-OG20100/TC030662/g' tcas.gff
sed -i 's/GLEAN_11473-OG10857/TC030579/g' tcas.gff
sed -i 's/GLEAN_11753-OG17716/TC030643/g' tcas.gff
sed -i 's/GLEAN_11947-OG24168/TC030690/g' tcas.gff
sed -i 's/GLEAN_12043-OG24493/TC030694/g' tcas.gff
sed -i 's/GLEAN_12108-OG13450/TC030603/g' tcas.gff
sed -i 's/GLEAN_12108-OG23140/TC030680/g' tcas.gff
sed -i 's/GLEAN_13986-OG23035/TC030678/g' tcas.gff
sed -i 's/GLEAN_13986-OG8500/TC030774/g' tcas.gff
sed -i 's/GLEAN_/TC0/g' tcas.gff
sed -i 's/Gr205/TC004248/g' tcas.gff
sed -i 's/Lcch3/TC007145/g' tcas.gff
sed -i 's/NDUFA7/TC030049/g' tcas.gff
sed -i 's/NONGLEAN-OG21903/TC030572/g' tcas.gff
sed -i 's/NONGLEAN-OG23891/TC030567/g' tcas.gff
sed -i 's/NONGLEAN-OG25423/TC030555/g' tcas.gff
sed -i 's/NONGLEAN-OG26034/TC030565/g' tcas.gff
sed -i 's/Or105/TC030325/g' tcas.gff
sed -i 's/Or121/TC030334/g' tcas.gff
sed -i 's/Or129/TC030337/g' tcas.gff
sed -i 's/Or131/TC030338/g' tcas.gff
sed -i 's/Or133/TC030339/g' tcas.gff
sed -i 's/Or135/TC030340/g' tcas.gff
sed -i 's/Or146/TC030344/g' tcas.gff
sed -i 's/Or150/TC030347/g' tcas.gff
sed -i 's/Or159/TC030349/g' tcas.gff
sed -i 's/Or15/TC006449/g' tcas.gff
sed -i 's/Or160/TC030350/g' tcas.gff
sed -i 's/Or164/TC030351/g' tcas.gff
sed -i 's/Or165/TC030352/g' tcas.gff
sed -i 's/Or172/TC030353/g' tcas.gff
sed -i 's/Or174/TC030355/g' tcas.gff
sed -i 's/Or175/TC030356/g' tcas.gff
sed -i 's/Or177/TC030357/g' tcas.gff
sed -i 's/Or17/TC003052/g' tcas.gff
sed -i 's/Or183/TC030359/g' tcas.gff
sed -i 's/Or184/TC030360/g' tcas.gff
sed -i 's/Or185/TC030361/g' tcas.gff
sed -i 's/Or186/TC011465/g' tcas.gff
sed -i 's/Or189/TC030362/g' tcas.gff
sed -i 's/Or191/TC011016/g' tcas.gff
sed -i 's/Or194/TC030365/g' tcas.gff
sed -i 's/Or195/TC030366/g' tcas.gff
sed -i 's/Or197/TC030367/g' tcas.gff
sed -i 's/Or198/TC009104/g' tcas.gff
sed -i 's/Or202/TC030369/g' tcas.gff
sed -i 's/Or204/TC011245/g' tcas.gff
sed -i 's/Or205/TC030370/g' tcas.gff
sed -i 's/Or208/TC030371/g' tcas.gff
sed -i 's/Or209/TC030372/g' tcas.gff
sed -i 's/Or211/TC030373/g' tcas.gff
sed -i 's/Or212/TC002491/g' tcas.gff
sed -i 's/Or213/TC030374/g' tcas.gff
sed -i 's/Or214/TC030375/g' tcas.gff
sed -i 's/Or216/TC030377/g' tcas.gff
sed -i 's/Or217/TC030378/g' tcas.gff
sed -i 's/Or218/TC030379/g' tcas.gff
sed -i 's/Or219/TC030380/g' tcas.gff
sed -i 's/Or220/TC030381/g' tcas.gff
sed -i 's/Or221/TC030382/g' tcas.gff
sed -i 's/Or222/TC030383/g' tcas.gff
sed -i 's/Or223/TC030384/g' tcas.gff
sed -i 's/Or225/TC030385/g' tcas.gff
sed -i 's/Or226/TC030386/g' tcas.gff
sed -i 's/Or227/TC030387/g' tcas.gff
sed -i 's/Or228/TC030388/g' tcas.gff
sed -i 's/Or229/TC006770/g' tcas.gff
sed -i 's/Or230/TC030389/g' tcas.gff
sed -i 's/Or231/TC030878/g' tcas.gff
sed -i 's/Or232/TC030390/g' tcas.gff
sed -i 's/Or236/TC030392/g' tcas.gff
sed -i 's/Or238/TC030393/g' tcas.gff
sed -i 's/Or239/TC030394/g' tcas.gff
sed -i 's/Or242/TC030395/g' tcas.gff
sed -i 's/Or243/TC030396/g' tcas.gff
sed -i 's/Or244/TC030397/g' tcas.gff
sed -i 's/Or245/TC030398/g' tcas.gff
sed -i 's/Or247/TC030399/g' tcas.gff
sed -i 's/Or259/TC003855/g' tcas.gff
sed -i 's/Or263/TC030400/g' tcas.gff
sed -i 's/Or264/TC030401/g' tcas.gff
sed -i 's/Or269/TC030402/g' tcas.gff
sed -i 's/Or272/TC013447/g' tcas.gff
sed -i 's/Or276/TC030404/g' tcas.gff
sed -i 's/Or277/TC007582/g' tcas.gff
sed -i 's/Or278/TC030405/g' tcas.gff
sed -i 's/Or281/TC007581/g' tcas.gff
sed -i 's/Or283/TC030407/g' tcas.gff
sed -i 's/Or284/TC003166/g' tcas.gff
sed -i 's/Or285/TC030408/g' tcas.gff
sed -i 's/Or286/TC030409/g' tcas.gff
sed -i 's/Or287/TC030410/g' tcas.gff
sed -i 's/Or288/TC030411/g' tcas.gff
sed -i 's/Or290/TC030412/g' tcas.gff
sed -i 's/Or292/TC030413/g' tcas.gff
sed -i 's/Or293/TC030414/g' tcas.gff
sed -i 's/Or294/TC030415/g' tcas.gff
sed -i 's/Or295/TC030416/g' tcas.gff
sed -i 's/Or300/TC006427/g' tcas.gff
sed -i 's/Or301/TC030419/g' tcas.gff
sed -i 's/Or304/TC030420/g' tcas.gff
sed -i 's/Or305/TC030421/g' tcas.gff
sed -i 's/Or309/TC030423/g' tcas.gff
sed -i 's/Or30/TC030306/g' tcas.gff
sed -i 's/Or310/TC030424/g' tcas.gff
sed -i 's/Or311/TC030425/g' tcas.gff
sed -i 's/Or313/TC030426/g' tcas.gff
sed -i 's/Or314/TC030427/g' tcas.gff
sed -i 's/Or315/TC030428/g' tcas.gff
sed -i 's/Or316/TC030429/g' tcas.gff
sed -i 's/Or318/TC011431/g' tcas.gff
sed -i 's/Or319/TC030430/g' tcas.gff
sed -i 's/Or320/TC030431/g' tcas.gff
sed -i 's/Or321/TC030432/g' tcas.gff
sed -i 's/Or323/TC004875/g' tcas.gff
sed -i 's/Or324/TC030434/g' tcas.gff
sed -i 's/Or325/TC004874/g' tcas.gff
sed -i 's/Or328/TC030435/g' tcas.gff
sed -i 's/Or329/TC030436/g' tcas.gff
sed -i 's/Or330/TC030437/g' tcas.gff
sed -i 's/Or331/TC030438/g' tcas.gff
sed -i 's/Or332/TC030439/g' tcas.gff
sed -i 's/Or333/TC030440/g' tcas.gff
sed -i 's/Or335/TC030441/g' tcas.gff
sed -i 's/Or34/TC000732/g' tcas.gff
sed -i 's/Or47/TC030314/g' tcas.gff
sed -i 's/Or49/TC001273/g' tcas.gff
sed -i 's/Or51/TC001272/g' tcas.gff
sed -i 's/Or61/TC030318/g' tcas.gff
sed -i 's/Or69/TC001314/g' tcas.gff
sed -i 's/Or70/TC001315/g' tcas.gff
sed -i 's/Or93/TC030321/g' tcas.gff
sed -i 's/Or94/TC030322/g' tcas.gff
sed -i 's/Or95/TC004303/g' tcas.gff
sed -i 's/Proct/TC015137/g' tcas.gff
sed -i 's/RecQ4/TC003875/g' tcas.gff
sed -i 's/Rtel1/TC007666/g' tcas.gff
sed -i 's/SCP-x/TC030464/g' tcas.gff
sed -i 's/Sobp/TC000470/g' tcas.gff
sed -i 's/TcCSP2A/TC004430/g' tcas.gff
sed -i 's/TcCSP3A/TC003085/g' tcas.gff
sed -i 's/TcCSP7A/TC015950/g' tcas.gff
sed -i 's/TcCSP7B/TC015902/g' tcas.gff
sed -i 's/TcCSP7C/TC014535/g' tcas.gff
sed -i 's/TcCSP7D/TC014534/g' tcas.gff
sed -i 's/TcCSP7E/TC014533/g' tcas.gff
sed -i 's/TcCSP7F/TC014532/g' tcas.gff
sed -i 's/TcCSP7G/TC014531/g' tcas.gff
sed -i 's/TcCSP7I/TC010151/g' tcas.gff
sed -i 's/TcCSP7J/TC008682/g' tcas.gff
sed -i 's/TcCSP7K/TC008681/g' tcas.gff
sed -i 's/TcCSP7L/TC008680/g' tcas.gff
sed -i 's/TcCSP7M/TC008679/g' tcas.gff
sed -i 's/TcCSP7N/TC008678/g' tcas.gff
sed -i 's/TcCSP7O/TC030501/g' tcas.gff
sed -i 's/TcCSP7P/TC008677/g' tcas.gff
sed -i 's/TcCSP7Q/TC008676/g' tcas.gff
sed -i 's/TcCSP7R/TC008674/g' tcas.gff
sed -i 's/TcGr101/TC030193/g' tcas.gff
sed -i 's/TcGr105/TC030197/g' tcas.gff
sed -i 's/TcGr106/TC030198/g' tcas.gff
sed -i 's/TcGr107/TC030199/g' tcas.gff
sed -i 's/TcGr108/TC030200/g' tcas.gff
sed -i 's/TcGr109/TC030201/g' tcas.gff
sed -i 's/TcGr110/TC030202/g' tcas.gff
sed -i 's/TcGr111/TC030203/g' tcas.gff
sed -i 's/TcGr112/TC030204/g' tcas.gff
sed -i 's/TcGr113/TC030205/g' tcas.gff
sed -i 's/TcGr114/TC030206/g' tcas.gff
sed -i 's/TcGr116/TC030208/g' tcas.gff
sed -i 's/TcGr118/TC030210/g' tcas.gff
sed -i 's/TcGr119/TC030211/g' tcas.gff
sed -i 's/TcGr120/TC030212/g' tcas.gff
sed -i 's/TcGr128/TC030218/g' tcas.gff
sed -i 's/TcGr12/TC030113/g' tcas.gff
sed -i 's/TcGr131/TC030220/g' tcas.gff
sed -i 's/TcGr133/TC030222/g' tcas.gff
sed -i 's/TcGr136/TC030225/g' tcas.gff
sed -i 's/TcGr137/TC030226/g' tcas.gff
sed -i 's/TcGr138/TC030227/g' tcas.gff
sed -i 's/TcGr139/TC030228/g' tcas.gff
sed -i 's/TcGr144/TC030233/g' tcas.gff
sed -i 's/TcGr145/TC030234/g' tcas.gff
sed -i 's/TcGr147/TC030236/g' tcas.gff
sed -i 's/TcGr148/TC030237/g' tcas.gff
sed -i 's/TcGr152/TC030241/g' tcas.gff
sed -i 's/TcGr155/TC030244/g' tcas.gff
sed -i 's/TcGr158/TC030246/g' tcas.gff
sed -i 's/TcGr159/TC030247/g' tcas.gff
sed -i 's/TcGr160/TC030248/g' tcas.gff
sed -i 's/TcGr162/TC030250/g' tcas.gff
sed -i 's/TcGr164/TC030252/g' tcas.gff
sed -i 's/TcGr165/TC030253/g' tcas.gff
sed -i 's/TcGr167/TC030255/g' tcas.gff
sed -i 's/TcGr171/TC030258/g' tcas.gff
sed -i 's/TcGr172/TC030259/g' tcas.gff
sed -i 's/TcGr173/TC030260/g' tcas.gff
sed -i 's/TcGr174/TC030261/g' tcas.gff
sed -i 's/TcGr175/TC030262/g' tcas.gff
sed -i 's/TcGr176/TC030263/g' tcas.gff
sed -i 's/TcGr177/TC030264/g' tcas.gff
sed -i 's/TcGr179/TC030266/g' tcas.gff
sed -i 's/TcGr182/TC030268/g' tcas.gff
sed -i 's/TcGr188/TC030270/g' tcas.gff
sed -i 's/TcGr189/TC030271/g' tcas.gff
sed -i 's/TcGr190/TC030272/g' tcas.gff
sed -i 's/TcGr191/TC030273/g' tcas.gff
sed -i 's/TcGr197/TC030275/g' tcas.gff
sed -i 's/TcGr199/TC030276/g' tcas.gff
sed -i 's/TcGr19/TC030120/g' tcas.gff
sed -i 's/TcGr200/TC030277/g' tcas.gff
sed -i 's/TcGr201/TC030278/g' tcas.gff
sed -i 's/TcGr202/TC030279/g' tcas.gff
sed -i 's/TcGr204/TC030281/g' tcas.gff
sed -i 's/TcGr20/TC030121/g' tcas.gff
sed -i 's/TcGr23/TC030123/g' tcas.gff
sed -i 's/TcGr29/TC030129/g' tcas.gff
sed -i 's/TcGr2/TC030103/g' tcas.gff
sed -i 's/TcGr32/TC030131/g' tcas.gff
sed -i 's/TcGr33/TC030132/g' tcas.gff
sed -i 's/TcGr34/TC030133/g' tcas.gff
sed -i 's/TcGr36/TC030135/g' tcas.gff
sed -i 's/TcGr37/TC030136/g' tcas.gff
sed -i 's/TcGr38/TC030137/g' tcas.gff
sed -i 's/TcGr40/TC030139/g' tcas.gff
sed -i 's/TcGr42/TC030140/g' tcas.gff
sed -i 's/TcGr43/TC030141/g' tcas.gff
sed -i 's/TcGr44/TC030142/g' tcas.gff
sed -i 's/TcGr46/TC030144/g' tcas.gff
sed -i 's/TcGr47/TC030145/g' tcas.gff
sed -i 's/TcGr48/TC030146/g' tcas.gff
sed -i 's/TcGr49/TC030147/g' tcas.gff
sed -i 's/TcGr50/TC030148/g' tcas.gff
sed -i 's/TcGr51/TC030149/g' tcas.gff
sed -i 's/TcGr57/TC030155/g' tcas.gff
sed -i 's/TcGr58/TC030156/g' tcas.gff
sed -i 's/TcGr59/TC030157/g' tcas.gff
sed -i 's/TcGr60/TC030158/g' tcas.gff
sed -i 's/TcGr61/TC030159/g' tcas.gff
sed -i 's/TcGr64/TC030160/g' tcas.gff
sed -i 's/TcGr66/TC030162/g' tcas.gff
sed -i 's/TcGr67/TC030163/g' tcas.gff
sed -i 's/TcGr6/TC030107/g' tcas.gff
sed -i 's/TcGr70/TC030166/g' tcas.gff
sed -i 's/TcGr71/TC030167/g' tcas.gff
sed -i 's/TcGr72/TC030168/g' tcas.gff
sed -i 's/TcGr74/TC030170/g' tcas.gff
sed -i 's/TcGr75/TC030171/g' tcas.gff
sed -i 's/TcGr76/TC030172/g' tcas.gff
sed -i 's/TcGr77/TC030173/g' tcas.gff
sed -i 's/TcGr79/TC030175/g' tcas.gff
sed -i 's/TcGr82/TC030176/g' tcas.gff
sed -i 's/TcGr84/TC030177/g' tcas.gff
sed -i 's/TcGr86/TC030179/g' tcas.gff
sed -i 's/TcGr87/TC030180/g' tcas.gff
sed -i 's/TcGr88/TC030181/g' tcas.gff
sed -i 's/TcGr90/TC030183/g' tcas.gff
sed -i 's/TcGr91/TC030184/g' tcas.gff
sed -i 's/TcGr92/TC030185/g' tcas.gff
sed -i 's/TcGr94/TC030187/g' tcas.gff
sed -i 's/TcGr95/TC030188/g' tcas.gff
sed -i 's/TcGr97/TC030190/g' tcas.gff
sed -i 's/TcGr99/TC030192/g' tcas.gff
sed -i 's/TcOBP0A/TC012904/g' tcas.gff
sed -i 's/TcOBP10A/TC011410/g' tcas.gff
sed -i 's/TcOBP10B/TC011411/g' tcas.gff
sed -i 's/TcOBP10C/TC011412/g' tcas.gff
sed -i 's/TcOBP10D/TC030448/g' tcas.gff
sed -i 's/TcOBP2A/TC000426/g' tcas.gff
sed -i 's/TcOBP3A/TC030449/g' tcas.gff
sed -i 's/TcOBP4A/TC007755/g' tcas.gff
sed -i 's/TcOBP4B/TC007756/g' tcas.gff
sed -i 's/TcOBP4C/TC030445/g' tcas.gff
sed -i 's/TcOBP4D/TC007742/g' tcas.gff
sed -i 's/TcOBP4E/TC007741/g' tcas.gff
sed -i 's/TcOBP4F/TC007729/g' tcas.gff
sed -i 's/TcOBP4G/TC007342/g' tcas.gff
sed -i 's/TcOBP4H/TC008161/g' tcas.gff
sed -i 's/TcOBP4I/TC008162/g' tcas.gff
sed -i 's/TcOBP4J/TC008468/g' tcas.gff
sed -i 's/TcOBP5A/TC016310/g' tcas.gff
sed -i 's/TcOBP5B/TC014486/g' tcas.gff
sed -i 's/TcOBP5C/TC013323/g' tcas.gff
sed -i 's/TcOBP5D/TC013322/g' tcas.gff
sed -i 's/TcOBP5E/TC013160/g' tcas.gff
sed -i 's/TcOBP5F/TC013149/g' tcas.gff
sed -i 's/TcOBP5H/TC030451/g' tcas.gff
sed -i 's/TcOBP6A/TC015201/g' tcas.gff
sed -i 's/TcOBP6B/TC015044/g' tcas.gff
sed -i 's/TcOBP6C/TC015043/g' tcas.gff
sed -i 's/TcOBP6D/TC015656/g' tcas.gff
sed -i 's/TcOBP6E/TC015042/g' tcas.gff
sed -i 's/TcOBP6F/TC015041/g' tcas.gff
sed -i 's/TcOBP7A/TC009459/g' tcas.gff
sed -i 's/TcOBP7B/TC009805/g' tcas.gff
sed -i 's/TcOBP7C/TC009008/g' tcas.gff
sed -i 's/TcOBP7D/TC010063/g' tcas.gff
sed -i 's/TcOBP7E/TC008757/g' tcas.gff
sed -i 's/TcOBP7F/TC010064/g' tcas.gff
sed -i 's/TcOBP7G/TC008756/g' tcas.gff
sed -i 's/TcOBP7H/TC010066/g' tcas.gff
sed -i 's/TcOBP7I/TC010067/g' tcas.gff
sed -i 's/TcOBP7J/TC010068/g' tcas.gff
sed -i 's/TcOBP7K/TC030447/g' tcas.gff
sed -i 's/TcOBP7L/TC010069/g' tcas.gff
sed -i 's/TcOBP7M/TC010070/g' tcas.gff
sed -i 's/TcOBP8A/TC006131/g' tcas.gff
sed -i 's/TcOBP8B/TC005434/g' tcas.gff
sed -i 's/TcOBP9A/TC030450/g' tcas.gff
sed -i 's/TcOBP9B/TC012109/g' tcas.gff
sed -i 's/TcOBP9C/TC011668/g' tcas.gff
sed -i 's/Ucrc/TC030044/g' tcas.gff
sed -i 's/capu/TC012258/g' tcas.gff
sed -i 's/cox-c/TC030047/g' tcas.gff
sed -i 's/lim1/TC014939/g' tcas.gff
sed -i 's/mago/TC016112/g' tcas.gff
sed -i 's/nos/TC030446/g' tcas.gff
sed -i 's/serpin19/TC005751/g' tcas.gff
sed -i 's/tim2/TC000593/g' tcas.gff
sed -i 's/tiotsh/TC012322/g' tcas.gff

# Get the iBeetle-Base ontology for the Tribolium castaneum species
wget -O - http://ibeetle-base.uni-goettingen.de/tribolium.obo > ./tron.obo;

# All the previous species
#
# Get the GO ontology common for all species
wget -O - http://purl.obolibrary.org/obo/go.owl > ./go.owl;
