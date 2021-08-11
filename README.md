# REDfly: Regulatory Element Database for Drosophila

## TL;DR

 - Have `docker-ce`, `docker-compose`, `gitbook-cli`, `php`, and `php-cli` installed for all the development, production, and test environments.
 - Have `code` and `go` installed only for the development environment.
 - Move `*.sql.gz` dump files into `db/dumps`.  
   These will be used to automatically initialize the mariadb docker instance.  
   **Note that file permissions must be world readable.**
 - Update `.env.dist` by filling out all fields denoted with `<...>` and save the edited file as `.env`
 - Most processes are handled by the Makefile in conjunction with the configuration information
   contained in the `.env` file.  
   Run `make help` to list available targets and a description of what they do.
 - The configuration and build process is controlled via a Makekefile and uses the environment defined
   in the `.env` file.  
   Run the following make targets to configure, build, start the services, and create the database-backup user:
   ```
   make $(cat .env | xargs) configuration
   make $(cat .env | xargs) build
   make $(cat .env | xargs) docker-initialize
   make $(cat .env | xargs) mariadb-access
   ```
- After copying a dump of the production database and rebuilding the development servers (e.g., using
  `make $(cat .env | xargs) docker-initialize` or `make $(cat .env | xargs) docker-restart`) the redfly user
  credentials will likely need to be updated for the development environment:
  ```
  make $(cat .env | xargs) redfly-database-development-user
  ```
- Make a manual dump of the database, if desired.
  ```
  make $(cat .env | xargs) database-backup
  ```
- Enable scheduled backups of the database to an alternate volume. See [Database Backups](#database-backups) below.

## Requirements for both production and test environments

- Docker CE 20.10.8+
- Docker Compose 1.27.4+
- GitBook 3.2.3+
- PHP 7.4.22+
- **The current iCRM calculation requires >= 8GB of RAM and >= 30GB of HD space**

## Requirements for the development environment besides the previous ones

- DBeaver 21.1.4+
- Go 1.16.6+
- Visual Studio Code 1.59.0+

## Building REDfly for the First Time

GNU Make is used for most of the project management tasks for the REDfly application.  
Invoke `make` with no arguments to see all targets and a brief description of each.  
The rest of the document provide a more in-depth description of certain deployment tasks.

### Database

The database schema is in the `db` directory.  
If you are starting from scratch with a database empty, you can execute the following command:

```
cat db/schema.sql | env $(cat .env) bash -c 'docker exec -i CONTAINERID mysql -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE'
```

See [MySQL CLI](https://mariadb.com/kb/en/library/mysql-command-line-client/) for more details on mysql.  
Alternatively, the mariadb docker will load a `.sql.gz` file on startup from the `db/dumps` directory for a database full with the REDfly data.  
This file can be copied from another location or can be generated using `make $(cat .env | xargs) database-backup`.  
Place the file in the `db/dumps` folder before invoking `make $(cat .env | xargs) docker-initialize`.

**Note that the database is not currently persisted across restarts of the mariadb container**.  
In order to persist the data, the container's `/var/lib/mysql/` directory should be mounted on a local volume.  
Also, see [Database Backups](#database-backups) below.

### Running the Application:

The REDfly application is built and running by using Docker and Docker Compose.  
Simply invoke the `docker-initialize` target via `make $(cat .env | xargs) docker-initialize` to build the Docker containers and run them.  
Before doing that, however, you must provide a few files yourself:

- You must have one or more gzipped SQL files to initialize the database.  
  Put those in `db/dumps` (see the Database section of this README for more details if you are starting from scratch).
- You must set several environmental variables in a `.env` file placed in the project root.  
  A template is provided in `.env.dist` with several values set to reasonable defaults; only change the default values if you have a reason to.  
  Both special characters, `&` and `\`, must be avoided to be put in such environmental variables (especially passwords) because they would need to be escaped for `sed`.

  - `ENVIRONMENT_NAME`: the environment name to be shown in the main REDfly webpage.  
    Very useful for websites with IP addresses.
  - `REDFLY_USER`: the user to authenticate with the HTTP Basic Authentication.  
    This is the same username you would use to log into the REDfly administration page.  
    **This must be an administrative user in order to perform releases**
  - `REDFLY_PASSWORD`: the password for the above user
  - `REDFLY_BASE_URL`: the base URL of the REDfly web application (e.g. `http://localhost/` if it is deployed locally)
  - `MYSQL_ROOT_PASSWORD`: the root password for the database.  
    It is recommended that the password be a randomized password, e.g. by using https://www.random.org/passwords/
  - `MYSQL_DATABASE`: the database name
  - `MYSQL_USER`: the initial non-root user to set up for the database
  - `MYSQL_PASSWORD`: the password for the above user
  - `MYSQL_HOST`: the database hostname
  - `MYSQL_PORT`: the port the database should be reached at
  - `MYSQL_BACKUP_USER`: the username with sufficient permissions to make database backups
  - `MYSQL_BACKUP_PASSWORD`: the password for the above user
  - `MYSQL_DUMP_DIR`: the directory where the scheduled database backups will be dumped
  - `CURATOR_USERNAME`: the username(s) for the user(s) with the curator role
  - `CURATOR_PASSWORD`: the password(s)  of the curator user(s)
  - `CURATOR_FIRSTNAME`: the first name(s) of the curator user(s)
  - `CURATOR_LASTNAME`: the last name(s) of the curator user(s)

  - `GMAIL_ADDRESS`: the Gmail address to send application-related emails to
  - `GMAIL_CLIENT_ID`: the Gmail API client ID
  - `GMAIL_CLIENT_SECRET`: the Gmail API client secret
  - `GMAIL_REFRESH_TOKEN`: the Gmail API refresh token
  - `GOOGLE_ANALYTICS_API_3_TRACKING_ID`: the Google Analytics API 3 tracking ID
  - `GOOGLE_ANALYTICS_API_3_VIEW_ID`: the Google Analytics API 3 view ID
  - `GOOGLE_OAUTH2_CREDENTIALS_FILE`: the file name of the Google OAuth 2.0 credentials
  - `GOOGLE_ANALYTICS_4_MEASUREMENT_ID`: the Google Analytics API 4 measurement
  - `RECAPTCHA_PUBLIC_KEY`: the ReCAPTCHA API public key
  - `RECAPTCHA_PRIVATE_KEY`: the ReCAPTCHA API private key
  - `NCBI_API_KEY`: the NCBI API private key

Notes:
  - REDfly uses the Gmail API to send emails.  
    Use the [Google Developer Console](https://console.developers.google.com/) to set up the GMail API access for the `.env` file as described above.
  - REDfly also uses the Google Analytics Reporting API 4.0 to make audience reports.  
    Use the [Google Analytics](https://developers.google.com/analytics/devguides/reporting/core/v4) to set up the Google Analytics Reporting API 4.0 access for the `.env` file as described above.
  - REDfly also uses the Google ReCAPTCHA API.  
    Use [ReCAPTCHA Admin](https://www.google.com/recaptcha/admin) to set up the public and private keys for the `.env` file as described above.
  - REDfly also uses the NCBI API.  
    Use [NCBI Admin](https://www.ncbi.nlm.nih.gov/account/) to set up the API key for the `.env` file as described above.

### Configuration

The REDfly configuration can be made in the `.env` file.  
Any time a change is made to this file, the `make $(cat .env | xargs) configuration` target must be invoked to update the REDfly application configuration files.

Note that if you change the names of any of the containers in `docker-compose.yml`, make sure to update their hostnames (that is, the name of the container _is_ the hostname for reaching that service) in `settings.yml` -- this is _not_ managed by `make`.

## Useful Development Procedures

### Updating Database

To update the development database with a copy of the production database:
- **On the production server** run `make $(cat .env | xargs) database-backup`
- Download the new dump file placed in the directory `db/dumps` for your workstation
- Copy the dump file to the `db/dumps` directory of the development environment by using the method `scp`  
  Note that the mariadb docker will load all files ending in `.sql` or `.sql.gz` on startup (see https://hub.docker.com/_/mariadb)
- Restart the development docker containers, reload the database, and re-initialize the permissions:
  ```
  make $(cat .env | xargs) docker-restart mariadb-access redfly-database-curator-users redfly-database-development-user
  ```

## Releasing New Records

### Summary

The commands to update all the external data sources and release the new records are as follows.  
After performing the release:
- The ontology and gene versions must be updated in `config/settings.yml`

```
(cd ./go/termlookupserver/assets && sh ./download-external-assets.sh)
mv ./go/termlookupserver/assets/cache.db ./go/termlookupserver/assets/cache-backup.db
docker-compose restart termlookup_server (after executing it, you should wait two minutes, at least, so that its internal cache, cache.db, can be finally built)
make $(cat .env | xargs) update
make $(cat .env | xargs) release
make $(cat .env | xargs) datadumps
cat go/termlookupserver/assets/releases.log (after executing it, you should update the file, `./config/settings.yml`)
make $(cat .env | xargs) statistics
```

### Update Code and Perform Migrations

Prior to releasing new records, the code may need to be updated from the git repository and migration scripts may need to be run.  
Note that the PHP and MySQL CLI are not installed on the VM by default.  
If migration procedures need to be run prior to a release, we use `docker exec` to run the scripts from within the appropriate docker container.  
If a migration script and database migration need to be run then the method for doing so will depend on how the configuration environment variables will be accessed in the script.  
For example, when running a PHP script that uses environment variables in the scripts the environment can be passed via `docker exec -e` (using multiple `-e` options).  
However, when running a MariaDB command line using environment variables to connect to the database, the environment must be available to the shell.

```
make $(cat .env | xargs) database-backup
git pull --rebase
make $(cat .env | xargs) php-libraries-reload extjs-libraries-reload help-update
cat ./db/migrations/**MIGRATION_FILE** | env $(cat .env) bash -c 'docker exec -i $(docker ps -a | grep "mariadb:10.5" | cut -d" " -f1) mysql $MYSQL_DATABASE -u $MYSQL_USER -p$MYSQL_PASSWORD'
```

Note that if a particular script must be run prior to the release or database migration it can be executed in the PHP container.  
For example:
```
docker exec -e $(cat .env | paste -s | tr -s '\t' '\t' | sed 's/\t/ -e /g') CONTAINERID php /var/www/tools/verify-and-update-tfbs-sequences.php -q
```

### Releasing Curated Entries

Before releasing new records, you should update all the external data sources.  
Those are handled by the `termlookup_server` microservice (see `go/termlookupserver/README.md` for more details on this service).  
To do this, simply rename `go/termlookupserver/assets/cache.db` (to, e.g., `cache-backup.db`) or otherwise delete it, use `go/termlookupserver/assets/download-external-assets.sh` to download the latest versions of all files, and restart the service with `docker-compose restart termlookupserver`.  
Note that if something goes wrong (something changed on FlyBase's end, for example), you can avoid downtime by simply restoring the old `cache.db` file and restarting the service as previously described.  
After updating the external data sources, release the data and update the data dumps.  
This must be run when the server is running (e.g. after `make docker-initialize` is invoked).  
`make release` will release all the approved records (those entered by a curator and approved by an auditor) and make them viewable/searchable by the public.  
This command  will write reports to `html/datadumps/reports` so this directory will be created.

 ```
 make $(cat .env | xargs) release
 ```

 Note that this will take a while -- be patient!

There is an unfortunate caveat to the automated process; certain aspects of updating the genes from the FlyBase genome cannot be automated.  
See the 'Gene update caveat' section below for more details.  
`make $(cat .env | xargs) datadumps` will update all the dump files in `html/datadumps` with the current data in the REDfly database.  
Those files are used by the external services (e.g. FlyBase) to provide interoperability with the REDfly data.  
After the release has been made, the current version of the gene annotation version and ontology will need to be updated in `config/settings.yml`.  
The versions should have been discovered during the update process and are available at `go/termlookupserver/assets/releases.log`.  
Alternatively, the gene annotation version is available in the file names at ftp://ftp.flybase.net/genomes/dmel/current/gff/ and the ontology version is available at the fly anatomy site at https://github.com/FlyBase/drosophila-anatomy-developmental-ontology/releases.

### Updating the Help Documentation

To include new documentation from the .md file(s) placed in the `docs/help` directory as well as new pictures in the `docs/help/images` directory:
```
make $(cat .env | xargs) help-update
```

### Working with the Production Data in the Development Environment

When working with the production data in the development environment, the REDfly user used to perform the release may not be the same.  
To recreate the development user run the following prior to releasing:
```
make $(cat .env | xargs) redfly-database-development-user
```

### Release Statistics

Generate the reports of entity statistics following a release.  
The first statistics report gets all the species together and each species has its own statistics report.  
Note that each statistics report captures the date from the line starting by "Database last updated:" in the current report (if existing) for the date interval: ]database_last_updated, today] to generate the statistics.

```
make $(cat .env | xargs) statistics
```

### Gene update caveat

After each new REDfly release, the SQL consult, `genes_which_both_name_and_identifier_do_not_match.sql`, placed in `./db/checks/`, must be executed in the MariaDB server, preferently in one from both test or development environments, given its long execution time about 43 minutes.  
If you see one or more names listed and different from "Unspecified", you will need to make manual data manipulations.  
This is caused by a situation where _both_ name and ID of a gene do not match any local entries.

First, execute this query (the variable represents the comma-separated list of the terms from above):

```
SELECT g.gene_id AS old_gene_id,
    ug.gene_id AS new_gene_id,
    g.name AS old_name,
    ug.name AS new_name,
    g.identifier AS old_identifier,
    ug.identifier AS new_identifier
FROM Gene AS g
INNER JOIN Gene AS ug ON g.start = ug.start AND
    g.stop = ug.stop
INNER JOIN Chromosome AS uc ON ug.chrm_id = uc.chromosome_id
WHERE g.name IN (:gene_name_list);
```

Review the results from the query above and, using them to the best of your capability, update the records as follows by hand (update the variables based on the new_gene_id, new_name, and new_identifier columns from above), repeating for each result:

```
SELECT rc.rc_id,
    rc.gene_id,
    rc.name
FROM ReporterConstruct rc
WHERE g.gene_id IN (:old_gene_id, :new_gene_id);

UPDATE ReporterConstruct
SET name = REPLACE(name, :old_identifier, :new_identifier),
    gene_id = :new_gene_id
WHERE gene_id = :old_gene_id;

DELETE
FROM Gene
WHERE gene_id = :old_gene_id;
```

The previous steps will be also needed for the other two entity kinds: CRM segments (gene_id) and transcription factor binding sites (gene_id and tf_id)

### Database Backups

There are 2 make targets for performing database backups: `database-backup` and `mysqldump`.  
The backups scripts are found in `utilities/mariadb/database-backup`.

`make $(cat .env | xargs) database-backup` will place a dump of the current database in the `db/dumps` directory and is meant to create a manual backup of the database.  
`make $(cat .env | xargs) mysqldump` is designed to be run on a schedule (i.e., as a cron job) and will place a database backup in the directory specified by the `MYSQL_DUMP_DIR` in the `.env` file.  
It will then clean up any old backups past 30 days.

To mount an additional volume to store backups, attach a new volume and run the following commands to format and mount the volume (assuming the volume is mounted on `/dev/vdb`):

```
mkdir /mysqldumps
parted -a optimal /dev/vdb mklabel gpt
parted -a optimal /dev/vdb mkpart primary 0% 100%
mkfs -t ext4 /dev/vdb1
cp /etc/fstab /etc/fstab-
blkid /dev/vdb1 -o export | awk '/^UUID/ { print $0" /mysqldumps\text4\tdefaults\t0\t0\n";}' >> /etc/fstab
mount -a
```

Schedule the 12-hour backups with cron and paste the following example.

```
crontab -e
```

Example:

```
# Example of job definition:
# .---------------- minute (0 - 59)
# |  .------------- hour (0 - 23)
# |  |  .---------- day of month (1 - 31)
# |  |  |  .------- month (1 - 12) OR jan,feb,mar,apr ...
# |  |  |  |  .---- day of week (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
# |  |  |  |  |
# *  *  *  *  * command to be executed

# Backup the database at each 12 hours
0 0,12 * * * cd $HOME/redfly && make $(cat .env | xargs) mysqldump
```

In order to persist mysql data across container restart, the container's `/var/lib/mysql/` directory should be mounted on a local volume.

### Database Audit Logs

The MariaDB server can be audited by its DML sentences with the exception of SELECT:

```
make $(cat .env | xargs) mariadb-audit-plugin-start
```

It must be executed after the new MariaDB server container already starting to avoid the data coming from the REDfly database building process (about 2 minutes to wait or by checking the container logs) as well as after restarting the same MariaDB server container with the REDfly database already built.  
Then the audit logs can be checked in the `logs` directory by 

```
ls -l ./logs/
sudo cat ./logs/server_audit.log
```
and also in the MariaDB server container:

```
docker-compose exec mariadb_server ls -l /tmp/
docker-compose exec mariadb_server cat /tmp/server_audit.log
```

### Google GMail API

If the refresh token used by the GMail address, redflyteam@gmail.com, would be depleted afer an undetermined number of uses, then a new refresh token for our GMail needs would be needed and created by following this tutorial:  
https://masashi-k.blogspot.com/2013/06/sending-mail-with-gmail-using-xoauth2.html.

### Google Analytics

The REDfly project uses the Google Analytics through its Gmail identifier: redflyteam@gmail.com  
The installation and configurations instructions can be read in the Data Streams section placed in the Admin settings of the Google Analytics webapge:  
https://analytics.google.com/.  
Once being there, click on the REDfly data stream to get the tagging instructions and choose "Add new on-page tag - Global site tag (tag.js)".  
Both current tracking and view IDs are UA-202138293-1 and 246936788 applied for the API version 3.  
The current measurement ID is G-0MNLPM1Y17 for the API version 4.  
They must be kept for the REDfly project.  
The Google Analytics configuration is currently installed in the file, ./html/header.php.  

Each REDfly teammate must have his/her Gmail identifier to get his/her own tracking and view IDs and set up his/her Google Analytics programming and testing tasks without interfering with the one possessed by the REDfly team.  
More development information about it can be found in the documentation file: ./docs/google_analytics.md  

### Student REDfly Environment

It must be set up in an environment separate from the other environments.  
The student data (their first names, last names, and emails) must be typed down in a new `.student` file copied from `assets/.student.dist`  
Then execute:
```
make $(cat .student | xargs) student-creation
```
The new passwords generated are stored in `./passwords.txt`  
If there are already new passwords to be given for the students, then the student data (their first names, last names, emails, and passwords) must be typed down in a new `.student` file copied from `assets/.student.dist` and execute:  
```
make $(cat .student | xargs) student-reset
```
More technical information about all this task can be read in the bash file, `student_reset.sh`
