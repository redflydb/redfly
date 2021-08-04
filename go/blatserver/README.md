The BLAT server microservice
----------------------------

This microservice provides a simple wrapper around the BLAT commandline tool so it can be used as a service.

While not strictly required, using Docker is highly recommended to compile and run the service. Use the `Dockerfile` to build the image (e.g. `docker build . -t redfly_blat_server`, then run it, making sure to map the port for web access and the volume for the assets (more below). An example command could look like `docker run --rm -dp 80:8080 -v $PWD/assets:/assets redfly_blat_server`.

If you prefer to compile and run it yourself, take a look at the `Dockerfile` for details on how to accomplish this.

On run, the service will check for a `dm6.2bit` genome database file of the Drosophila melanogaster species in the `assets` folder. If it is not present, the program will panic and exit. Consequently, to update the database, simply update the `dm6.2bit` file and restart the service.
The same one for the `agam4.2bit` genome database file of the Anopheles gambiae species, for example.

All external files must be downloaded and put into the `assets` folder (create it if it does not exist) prior to running the service for the first time. The easiest way to do this is to simply run the `download-external-assets.sh` shell script in the `assets` folder. This script will download the latest versions of all required files from UCSC.
If there is no 2bit file for a species, then there must be a new 2bit one obtained by the FASTA file of the same species following the instructions in https://genome.ucsc.edu/goldenPath/help/twoBit.html and gitted into the GitHub repository.\
fa -> 2bit:\
./faToTwoBit genome.fa genome.2bit\
2bit -> fa:\
./twoBitToFa genome.2bit genome.fa

The current command-line arguments used by th Dockerized BLAT executable are as follows:\
-minIdentity=95 (limiting the results only to 95% matches, at least)\
-q=dna (only accepting DNA sequences as the input)
