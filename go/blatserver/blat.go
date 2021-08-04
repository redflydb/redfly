package blatserver

import (
	"os"
	"os/exec"
)

// Search checks the genome in the 2bit database chosen for any match
// from all sequences provided in the FASTA file, and writes the match
// results into the PSLX file.
func Search(
	genomeDatabase *os.File,
	fasta *os.File,
	pslx *os.File) {
	blat(
		genomeDatabase.Name(),
		fasta.Name(),
		pslx.Name())
}

// blat invokes the BLAT commandline tool. It takes the filenames of the
// 2bit database and the FASTA files, and returns the filename of the resulting
// PSLX file. The tool is assumed to be on the PATH and is invoked with default
// arguments with the following exceptions:
// -minIdentity is set to 95 limiting the results only to 95% matches
// -noHead is set to remove the headers from the output file
// -out is set to "pslx" for the PSLX file format.
// -q is set to "dna" only accepting DNA sequences as the input
// See https://genome.ucsc.edu/goldenpath/help/blatSpec.html#blatUsage for
// details on the BLAT commandline tool.
func blat(
	genomeDatabase string,
	in string,
	out string) {
	cmd := exec.Command(
		"blat",
		"-minIdentity=95",
		"-noHead",
		"-out=pslx",
		"-q=dna",
		genomeDatabase,
		in,
		out)
	if _error := cmd.Run(); _error != nil {
		panic(_error)
	}
}
