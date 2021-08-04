# REDfly Test Staging Data

## Batch Upload

Access to the batch upload is available through the "Batch Import" button on the administrative
interface.

Batch upload requires 1 or 2 or 3 files depending on the user input needs:

- An attribute file in the tab-separated value (TSV) format containing one line for each entry to import. 
  Each line contains the values for the attritbute entry (i.e., sequence_from_species, PMID, author_email,
  transgenic_construct, gene_name, name, etc.). The first line is assumed to contain the header fields.
- A FASTA formated text file containing the sequence data for the entries specified in the attribute 
  TSV file. Note that the order of the sequences in this file must match the order of the data in the 
  attribute TSV file. The sequence case is not important and will be normalized on import.
- An expression file in the tab-separated value (TSV) format containing one line for each entry to import.
  Each line contains the values for the entry (i.e., gene_name, name, assayed_in_species, expression,
  stage_on, etc.).
  The gene name and names in each one of its rows must match the ones in the attribute TSV file. The 
  first line is assumed to contain the header fields.

When inserting new reporter constructs (RCs):

- only both attribute TSV and FASTA files are used.

When inserting new RCs and a new triple store (TS) for one of such new RCs, at least:

- all the three files must be used. 

When inserting new TSs for existent RCs:

- only the expression TSV file is used.

Example attribute TSV file:

```
sequence_from_species	PMID  author_email  gene_name	name	transgenic_construct  evidence	sequence_source coordinates expression_terms  notes figure_label  is_negative
Drosophila melanogaster 30315166  Richard.Benton@unil.ch  Ir7c	SA2   Reporter construct (in vivo)	Sequence ends provided in reference X:7873233..7874827  FBbt:00003023,FBbt:00004729,FBbt:00004640,FBbt:00004162,FBbt:00110639,FBbt:00004052,FBbt:00110174,FBbt:00110175,FBbt:00110176,FBbt:00110173 adult abdomen, adult wings/halteres, adult legs, adult labellar taste bristles, adult subesophageal zone, thoracico-abdominal ganglion (prothoracic, mesothoracic, metathoracic and abdominal neuromeres) 3^S2  0	
```

Example FASTA file:

```
>X:7762665..7764982
GGTGAAGAATAGAGTGTGGCgctgtttgaatttctgctgaagttaacatttttataattccattaagcgaacggtcgtgtctctatctctatcgctcctccgctaccccattctctcccatgtgtaacatgaggattttcccactcacacacacacaaaggcaaaacggccgtacaaagttggtccgaaaagtttttgcattcataaatcatttccctctttttttttgcccagcctcaccttcacatccatgtttaaaggtggaaaatacttttcgctgaatttgtagaatttctcggggttggggggggtgctcatgtttcatatgccataaaggggttttaagggcggtggatgcggggggatcggaatgtgggcaacgttaagttgcaacttgttgccttcttggttacacgatgccaatccacccacccatcctggcatatccttgtcatttttaatttatgcgcagaccaagcctgaactttcgcaaaacttaaggaagaaactgtggctggggtaagggaaaggggttgtcttgttgtcttgttttttgcccttgggcagtaatttaattggacccatgattgggattttttgtttgccctaatcgtgttatggtcttaaaacggtataatagatattaaaagaggttaagtcaaatcaactttgtttggaaccaaaaaaaaaaaattgctgctgcggaaatgaggcacgaaaatgtggaataactgctaacaagtatcataattacagttcacaaaacatgctcacacatacgagtatactcgtacatgtgtatgtgcagttttccgcagctgcttgactgccaactctttccccccaacattttccattttccatccacttcagcactccttttctctagtgacttttgcatactttttggaaaacttttaattttcgaacttttgtcgcttggctggcatatggtccgtgctctttgctctgtgttccttactccttgctccttactccttcccccaatcccaactttccacactttctcctcgattggcccaatctgatgacgtcttattaggcaaatgaaaagctgcaaagtttttcctcgagagtcactcgagccatgtggatttccagaaggaaatgctctgcaagccgggattttcttaaatatcgttttgcatctcatattcttttcccttctacccggttctttatcgctttgtttataaaagtcgcccactaattgcatacataggtaatgggcggggggcgtggaggaggtgggcgaaaaggggggttaggcggagataagcgagtatgtgaaaagttgcctggctctcaagggcactgtcaaaaattaattaaacctctttgccggcttctttaagttaaatataaatgtattcttaacaaaaatgatgaatagtatttataaattagaaatatttaaaactaaagagtaaacgcaagtatattttaaagagcaacattttctcccagtgtaccgtgtgcattcatgcatgcagctggcgctgttgcggtgaggggcgtggctccggcgttgagtgggcgtttcggacatatcgcttcgtttttacccgggcaacagtaccgcatttgcatttatatgcacatcctttgtgcccaaaggatattacatttttccacttcaagcatttttgtgaatttaatgaaatttttaatttttatgcgcgccagatgggcgtgaagtgtagacagagggggggcgtggcagtgcctcctttcgcttggactaacaagccgcagagatatgtatgctgcatttgctttgcattctcggtgggtccagtgcggtaactactttccacaaagataaataaaaactggtcctagccaggggaataccccttatccttatcctttgctcatccccaaaccccgcacttgtggtcaaaaggagaaaaacagttgaattggcttttggtttgcacttgcattgcgttttacactgttctcgggattgcggccactcttgcgatgattgctgctgcttgattaccaagcactcaatgcgtaaaaaacggaagtaactctgaaatattacattttaaaggattaaaaaaagttagttatcatcagtaatcagtctcgcttatacaaatgttattaaacccttaacattaagctaattggatggggcgataggataagattggatcgaggccaaagtggctagcttagcattaatgaaacatggagcagtggcgattaagttgaacagaaaagctcaccattagggaaattagacagcgcatttacttgtgaactCGCAACAGTTTCGTTTCAAA
```

Example expression TSV file:

```
name	gene_name	assayed_in_species  expression	stage_on	stage_off	biological_process	sex	ectopic enhancer/silencer
SA2	Ir7c  Drosophila melanogaster FBbt:00003023	none  FBdv:00000000	GO:0000001	m	1 enhancer
```
