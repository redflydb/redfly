# REDfly Test Data

REDfly administrative tools are found at <http://redfly.ccr.buffalo.edu/admin>

## Batch Upload

Access to the batch upload is available through the "Batch Import" button on the administrative
interface.

Batch upload requires 2 files:

- A file in the tab-separated value (TSV) format containing one line for each entry to import. Each line
  contains the values for the entry (i.e., sequence_from_species, PMID, author_email, transgenic_construct,
  gene_name, name, etc.). The first line is assumed to contain the header fields.
- A FASTA formated text file containing the sequence data for the entries specified in the TSV file.
  Note that the order of the sequences in this file must match the order of the data in the TSV
  file. The sequence case is not important and will be normalized on import.

Example TSV file:

```
sequence_from_species PMID  author_email  gene_name name  transgenic_construct  evidence  sequence_source coordinates expression_terms  notes figure_label  is_negative
Drosophila melanogaster 30315166  Richard.Benton@unil.ch  Ir7c  SA2   Reporter construct (in vivo)  Sequence ends provided in reference X:7873233..7874827  FBbt:00003023,FBbt:00004729,FBbt:00004640,FBbt:00004162,FBbt:00110639,FBbt:00004052,FBbt:00110174,FBbt:00110175,FBbt:00110176,FBbt:00110173 adult abdomen, adult wings/halteres, adult legs, adult labellar taste bristles, adult subesophageal zone, thoracico-abdominal ganglion (prothoracic, mesothoracic, metathoracic and abdominal neuromeres) 3^S2  0
```

Example FASTA file:
```
>X:7762665..7764982
GGTGAAGAATAGAGTGTGGCgctgtttgaatttctgctgaagttaacatttttataattccattaagcgaacggtcgtgtctctatctctatcgctcctccgctaccccattctctcccatgtgtaacatgaggattttcccactcacacacacacaaaggcaaaacggccgtacaaagttggtccgaaaagtttttgcattcataaatcatttccctctttttttttgcccagcctcaccttcacatccatgtttaaaggtggaaaatacttttcgctgaatttgtagaatttctcggggttggggggggtgctcatgtttcatatgccataaaggggttttaagggcggtggatgcggggggatcggaatgtgggcaacgttaagttgcaacttgttgccttcttggttacacgatgccaatccacccacccatcctggcatatccttgtcatttttaatttatgcgcagaccaagcctgaactttcgcaaaacttaaggaagaaactgtggctggggtaagggaaaggggttgtcttgttgtcttgttttttgcccttgggcagtaatttaattggacccatgattgggattttttgtttgccctaatcgtgttatggtcttaaaacggtataatagatattaaaagaggttaagtcaaatcaactttgtttggaaccaaaaaaaaaaaattgctgctgcggaaatgaggcacgaaaatgtggaataactgctaacaagtatcataattacagttcacaaaacatgctcacacatacgagtatactcgtacatgtgtatgtgcagttttccgcagctgcttgactgccaactctttccccccaacattttccattttccatccacttcagcactccttttctctagtgacttttgcatactttttggaaaacttttaattttcgaacttttgtcgcttggctggcatatggtccgtgctctttgctctgtgttccttactccttgctccttactccttcccccaatcccaactttccacactttctcctcgattggcccaatctgatgacgtcttattaggcaaatgaaaagctgcaaagtttttcctcgagagtcactcgagccatgtggatttccagaaggaaatgctctgcaagccgggattttcttaaatatcgttttgcatctcatattcttttcccttctacccggttctttatcgctttgtttataaaagtcgcccactaattgcatacataggtaatgggcggggggcgtggaggaggtgggcgaaaaggggggttaggcggagataagcgagtatgtgaaaagttgcctggctctcaagggcactgtcaaaaattaattaaacctctttgccggcttctttaagttaaatataaatgtattcttaacaaaaatgatgaatagtatttataaattagaaatatttaaaactaaagagtaaacgcaagtatattttaaagagcaacattttctcccagtgtaccgtgtgcattcatgcatgcagctggcgctgttgcggtgaggggcgtggctccggcgttgagtgggcgtttcggacatatcgcttcgtttttacccgggcaacagtaccgcatttgcatttatatgcacatcctttgtgcccaaaggatattacatttttccacttcaagcatttttgtgaatttaatgaaatttttaatttttatgcgcgccagatgggcgtgaagtgtagacagagggggggcgtggcagtgcctcctttcgcttggactaacaagccgcagagatatgtatgctgcatttgctttgcattctcggtgggtccagtgcggtaactactttccacaaagataaataaaaactggtcctagccaggggaataccccttatccttatcctttgctcatccccaaaccccgcacttgtggtcaaaaggagaaaaacagttgaattggcttttggtttgcacttgcattgcgttttacactgttctcgggattgcggccactcttgcgatgattgctgctgcttgattaccaagcactcaatgcgtaaaaaacggaagtaactctgaaatattacattttaaaggattaaaaaaagttagttatcatcagtaatcagtctcgcttatacaaatgttattaaacccttaacattaagctaattggatggggcgataggataagattggatcgaggccaaagtggctagcttagcattaatgaaacatggagcagtggcgattaagttgaacagaaaagctcaccattagggaaattagacagcgcatttacttgtgaactCGCAACAGTTTCGTTTCAAA
```

## Individual Curation

Individual curation is performed directly through the administrative interface by clicking "Create"
or by searching an element and selecting the individual element for editing.

The following examples are valid entities that can be used to verify the user interface:

Sample RC/CRM (correct)

```
"Sequence From" Species: Drosophila melanogaster
PMID: 29617378
Gene: Or19a
Name: Or19a_310bp
Transgenic Construct: FBbt:00067000
Sequence: GGTTGTTCGCCTCTTTGAAGCAGCTCTCCGCGTTCCGACCTTTTTATATTCATCCAATAAGCCACCTATGCCGCAAATTATGCAAATTGCTGCAAATTACACCGAAAAAGTTCGATTTTGAACCACCACCAGCAATTACAATGACAATCATTTTTCGCAAATCAATCGCCTCCTTATCATCATTAGCGCCACTTAGCTTTCTGGTAAATGAAAAGGTTTTTATAATTATTCCCACACTTTTGTTTAAATTTTTTTTTTAAAGCGTTATTTTATCTAAAGCGTTGTGTTCAACGGCGTGAAAGGTTTGGCC
Notes: antennal trichoid sensilla at3
```

```
"Sequence From" Species: Drosophila melanogaster
PMID: 29617378
Gene: Or23a
Name: Or23a_210bp
Transgenic Construct: FBbt:00007352
Sequence: TTAAGTGATATATAGATACTCCTTTGCTGAATGGCACTCAAAGGGTGTGCATTTTCCCAAGTTTTGGGAAAATTATATGCGACCCCTCGATAATGATTTAATTTGCGATTTAATAACCCCTGACTAATGGCGCAACTGACACAGAAAGGTGATATAAATGGTCGAGGTCCTTTTGAAAGTATGCCAGTGAAAAAGAAAGACACCAAGAATTCAAA
Notes: ectopic in large number of odorant sensory neurons across the antenna
Figure Labels (separate labels with ^): 5E
```

```
"Sequence From" Species: Drosophila melanogaster
PMID: 29617378
Gene: Or42b
Name: Or42b_430bp
Transgenic Construct: FBbt:00007352
Sequence: CTCGGCGTTATTGTGCGCACTGCTCTGGTGGACGGCTTATATAGGCCGGTTCCTTTCGAACTTGTGACATTTTTATAGATGAAGTTTTCAATGAGCTCGAAAAGTTTTCATTAAGTTTATGGCCCTTGCTGTTGTTTAATTATTAACTTGAGAATTTCTCTGCTGCGCTATGTTATGTTTAATTAACCAGTGAAAGCACTTGACTAGAACAAACATTTATTCAGTGTTGTTGCCCGACTTCCGATTTGACGAATTTGAATTTGAAACTGCATCGGTCGTGCTGTACTCGTTTTCACATGCTGGATTTCCAGCATGCATAAAATATGGAAAAGTTTGCGAAAATACCAGACGGGTCCTTAATGGCAATTAAGACGTTCCTCATACGTAGCGTTGGCCGCGCCGCCTTAACTTCGCCTGGGGCCACACTGTT
Notes: ectopic in large number of odorant sensory neurons across the antenna
```

Sample TFBS (correct)

```
"Sequence From" Species: Drosophila melanogaster
PMID: 29617378
Author Email: brian.gebelein@cchmc.org
Gene Name: rho
Transcription Factor: sv
Evidence: EMSA (gel shift)
Sequence: TCGTTGCAGTTCATTGAT
Sequence with Flank: CTCCGTTCGGTTCGTTGCAGTTCATTGATTGACATTTTTA
Notes: binds Pax2 weakly; required for embryonic induction of abdominal oenocytes by rhoBAD
```

```
"Sequence From" Species: Drosophila melanogaster
PMID: 29617378
Author Email: brian.gebelein@cchmc.org
Gene Name: rho
Transcription Factor: sens
Evidence: EMSA (gel shift)
Sequence: TTCATTGATTGACAT
Sequence with Flank: CGGTTCGTTGCAGTTCATTGATTGACATTTTTATTATGCAT
Notes: binds Sens weakly; regulates embryonic induction of abdominal oenocytes by rhoBAD
```

Sample TFBS (incorrect)

```
"Sequence From" Species: Drosophila melanogaster
PMID: 29617378
Author Email: brian.gebeleincchmc.org
Gene Name: rho
Transcription Factor: exd
Evidence: EMSA (gel shift)
Sequence: ATTGATTG
Sequence with Flank: CGGTTCGTTGCAGTTCATTGATTGACATTTTTATTATGCA
Notes: binds Exd strongly
```

```
"Sequence From" Species: Drosophila melanogaster
PMID: 29617378
Author Email: brian.gebelein@cchmc.org
Gene Name: rho
Transcription Factor: hth
Evidence: EMSA (gel shift)
Sequence: ATTGACAT
Sequence with Flank: CGTTGCAGTTCATTGATTGACATTTTTATTATGCATATT
Notes: binds Hth strongly
```

```
"Sequence From" Species: Drosophila melanogaster
PMID: 2961737
Author Email: brian.gebelein@cchmc.org
Gene Name: rho
Transcription Factor: Abd-A
Evidence: EMSA (gel shift)
Sequence: TTTTATTA
Sequence with Flank: GTTCATTGATTGACATTTTTATTATGCATATTCGCTGGTC
Notes: binds Abd-A with Exd/Hth strongly and alone weakly; this site not necessary for rhoBAD activity
```
