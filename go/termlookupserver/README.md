The term lookup microservice
----------------------------

This microservice reads and caches terms from various external sources (FlyBase, ontologies, etc) and makes them available via a simple RESTful API.

While not strictly required, using Docker is highly recommended to compile and run the service. Use the `Dockerfile` to build the image (e.g. `docker build . -t redfly_termlookup_server`, then run it, making sure to map the port for web access and the volume for the assets (more below). An example command could look like `docker run --rm -dp 80:8080 -v $PWD/assets:/assets redfly_termlookup_server`.

If you prefer to compile and run it yourself, take a look at the `Dockerfile` for details on how to accomplish this.

On run, the service will check for a `cache.db` SQLite database file in the `assets` folder. If it is not present, a new cache will be built, otherwise the caching step will be skipped. Consequently, to update the cache, simply move the `cache.db` file out of the `assets` folder and restart the service.

**Tip**: consider moving, not deleting, the `cache.db` file when updating the terms, in case things go south when re-building. If that happens, the old file can simply be restored and service re-started to avoid excessive downtime.

All external files must be downloaded and put into the `assets` folder (create it if it does not exist) prior to running the service for the first time. The easiest way to do this is to simply run the `download-external-assets.sh` shell script in the `assets` folder. This script will download the latest versions of all required files from FlyBase and Gene Ontology.

The data sources are as follows:

 - The FlyBase Drosophila melanogaster precomputed genome GFF file: See https://wiki.flybase.org/wiki/FlyBase:Downloads_Overview#GFF_files for more details on how to obtain the file. Make sure to download the `dmel-all-no-analysis-r<release-number>.gff.gz` version of the file. Uncompress and save this file as `dmel.gff`.

 - The FlyBase Drosophila melanogaster gross anatomy ontology: See http://www.obofoundry.org/ontology/fbbt.html for details on this ontology. Download the `.owl` version of the file and save it as `fbbt.owl`.

 - The FlyBase Drosophila melanogaster developmental ontology: See http://www.obofoundry.org/ontology/fbdv.html for details on this ontology. Download the `.owl` version of the file and save it as `fbdv.owl`.

 - The Gene Ontology: See http://geneontology.org/page/download-ontology for details on this ontology. Simply download and save the `go.owl` file.

 - The FlyBase Chado database: See http://gmod.org/wiki/FlyBase_Chado for details. Unlike the above, this does not need to be downloaded; the service will query the remote database directly for all transgenic constructs and their associated PubMed references.
