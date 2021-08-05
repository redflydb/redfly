BLAT = docker-compose exec blat_server blat
MARIADB = docker-compose exec mariadb_server mysql
PHP = docker-compose exec php_server php
PHP_BASH = docker-compose exec php_server

.PHONY: analyze$\
	build$\
	clean$\
	configuration$\
	database-backup$\
	datadumps$\
	docker-initialize$\
	docker-restart$\
	extjs-libraries-reload$\
	help$\
	help-update$\
	html/admin/new/ext$\
	html/images/flyexpress$\
	html/js/extjs$\
	lint$\
	logs$\
	mariadb-access$\
	mariadb-audit-plugin-start$\
	mariadb-audit-plugin-stop$\
	mysqldump$\
	php-libraries-reload$\
	redfly-database-curator-users$\
	redfly-database-development-user$\
	release$\
	schema-backup$\
	statistics$\
	student-creation$\
	student-reset$\
	ubir-backup$\
	update$\
	vendor$\
	vscode-debug-settings-configuration$\
	xdebug

help:
	@ echo "Usage: make [target]"
	@ echo "  analyze                             Run the PHPStan static analysis tool on the codebase."
	@ echo "  build                               Build the project for deployment."
	@ echo "  clean                               Clean all artifacts managed by make."
	@ echo "  configuration                       Configure the REDfly application using environment variables."
	@ echo "                                      The REDFLY_BASE_URL, GMAIL_ADDRESS, GMAIL_CLIENT_ID,"
	@ echo "                                      GMAIL_CLIENT_SECRET, GMAIL_REFRESH_TOKEN, GOOGLE_ANALYTICS_API_3_TRACKING_ID,"
	@ echo "                                      GOOGLE_ANALYTICS_API_3_VIEW_ID, GOOGLE_OAUTH2_CREDENTIALS_FILE_PATH,"
	@ echo "                                      GOOGLE_ANALYTICS_4_MEASUREMENT_ID, NCBI_API_KEY, RECAPTCHA_PUBLIC_KEY,"
	@ echo "                                      and RECAPTCHA_PRIVATE_KEY environment variables must be set to configure"
	@ echo "                                      the application for deployment."
	@ echo "  database-backup                     Dump the current database to the db/dumps directory."
	@ echo "  datadumps                           Generate GFF and Gbrowse dumps of REDfly data."
	@ echo "  docker-initialize                   Rebuild the images and start their docker containers."
	@ echo "  docker-restart                      Restart the docker containers. This will reload the database"
	@ echo "                                      from the files in the db/dumps directory. Note that mariadb-access should"
	@ echo "                                      be called after re-loadig the database."
	@ echo "  extjs-libraries-reload              Reload the (existing and new) ExtJS libraries used by the"
	@ echo "                                      REDfly application"
	@ echo "  help                                Show the REDfly Makefile arguments and their functions"
	@ echo "  help-update                         Update the Help documentation in HTML using the GitBook engine with" 
	@ echo "                                      new information from .md file(s) and new pictures."
	@ echo "  html/admin/new/ext                  Deploy the new Sencha ExtJS 6.0 for the REDfly application"
	@ echo "  html/images/flyexpress              Deploy the FlyExpress images for the REDfly application"
	@ echo "  html/js/extjs                       Deploy the old Sencha ExtJS 3.4 for the REDfly application"
	@ echo "  lint                                Run the PHP CodeSniffer tool on the codebase and"
	@ echo "                                      attempts to fix any errors found with the PHP Code"
	@ echo "                                      Beautifier and Fixer tool."
	@ echo "  logs                                Make the logs directory for the REDfly application"
	@ echo "  mariadb-access                      Apply mysql permissions and create users. Must be called after"
	@ echo "                                      restarting the mariadb docker."
	@ echo "  mariadb-audit-plugin-start          Start the MariaDB audit plugin."
	@ echo "  mariadb-audit-plugin-stop           Stop the MariaDB audit plugin."
	@ echo "  mysqldump                           Dump the current database to the directory specified by the"
	@ echo "                                      MYSQL_DUMP_DIR environment variable. This is designed to be"
	@ echo "                                      scheduled on a regular basis for disaster recovery."
	@ echo "  php-libraries-reload                Reload the (existing and new) PHP libraries used by the"
	@ echo "                                      REDfly application"
	@ echo "  redfly-database-curator-users       Create REDfly curator user(s) in the REDfly database."
	@ echo "                                      The user information is provided on the command line using the following parameters:"
	@ echo "                                      CURATOR_USERNAME=curator username"
	@ echo "                                      CURATOR_PASSWORD=curator password"
	@ echo "                                      CURATOR_FIRSTNAME=curator firstname"
	@ echo "                                      CURATOR_LASTNAME=curator lastname"
	@ echo "  redfly-database-development-user    Create the development user in the REDfly database with the REDFLY_USER and REDFLY_PASSWORD"
	@ echo "                                      environment variables. If the user exists the password will be updated."
	@ echo "                                      This is useful after importing a database dump from production."
	@ echo "                                      CURATOR_EMAIL=curator email (optional)"
	@ echo "  release                             Release all approved records in the REDfly and make"
	@ echo "                                      them searchable by the public. The REDFLY_USER,"
	@ echo "                                      REDFLY_PASSWORD, REDFLY_BASE_URL, MYSQL_USER and"
	@ echo "                                      MYSQL_PASSWORD environment variables must be set to"
	@ echo "                                      authenticate against the REDfly administrative API and"
	@ echo "                                      connect to the REDfly database. Be patient, this target"
	@ echo "                                      will take a while to complete!"
	@ echo "  schema-backup                       Dump the current database schema to the db directory."
	@ echo "  statistics                          Generate a report with various statistics for the"
	@ echo "                                      curated data in the REDfly database. The MYSQL_USER and"
	@ echo "                                      MYSQL_PASSWORD environment variables must be set to"
	@ echo "                                      connect to the REDfly database."
	@ echo "  student-creation                    Create a REDfly student user. The user information is provided on the"
	@ echo "                                      command line using the following parameters:"
	@ echo "                                      CURATOR_FIRSTNAME=student firstname"
	@ echo "                                      CURATOR_LASTNAME=student lastname"
	@ echo "                                      CURATOR_EMAIL=student email"
	@ echo "  student-reset                       Update a REDfly student user. The user information is provided on the"
	@ echo "                                      command line using the following parameters:"
	@ echo "                                      CURATOR_FIRSTNAME=student firstname"
	@ echo "                                      CURATOR_LASTNAME=student lastname"
	@ echo "                                      CURATOR_EMAIL=student email"
	@ echo "                                      CURATOR_PASSWORD=student password"	
	@ echo "  ubir-backup                         Generate the zip file of all the REDfly data for the UBIR"
	@ echo "  update                              Update all the REDfly data dependent from the external data sources"
	@ echo "  vendor-install                      Rebuilds the Composer image and install all the PHP libraries used by the REDfly application"
	@ echo "  vendor-update                       Rebuilds the Composer image and upgrade/downgrade all the PHP libraries used by the REDfly application"	
	@ echo "  vscode-debug-settings-configuration Configure the Visual Studio Code debug settings"
	@ echo "  xdebug                              Activate the XDebug module for PHP debugging."

analyze: vendor
	$(PHP) ./vendor/bin/phpstan analyse -l 7 -c phpstan.neon lib

build: logs html/admin/new/ext html/js/extjs html/images/flyexpress vendor-install
	cd ./go/blatserver/assets && sh ./download-external-assets.sh
	cd ./go/termlookupserver/assets && sh ./download-external-assets.sh

clean:
	sudo rm -f ./html/admin/new/bootstrap.*
	sudo rm -rf ./html/admin/new/build
	sudo rm -rf ./html/admin/new/ext
	sudo rm -rf ./html/admin/new/overrides
	sudo rm -rf ./html/admin/new/packages
	sudo rm -rf ./html/admin/new/resources
	sudo rm -rf ./html/admin/new/sass
	sudo rm -rf ./html/admin/new/.sencha
	rm -rf ./html/datadumps
	rm -rf ./html/gitbook
	rm -rf ./html/images/flyexpress	
	rm -rf ./html/js/extjs
	rm -rf ./logs
	rm -rf ./vendor
	rm -f ./go/termlookupserver/assets/*.db
	rm -f ./go/termlookupserver/assets/*.owl
	rm -f ./go/termlookupserver/assets/*.gff
	rm -f ./go/termlookupserver/assets/*.gff3
	rm -f ./go/blatserver/assets/*.2bit

configuration:
	cp ./config/settings.yml.dist ./config/settings.yml
	chmod 0644 ./config/settings.yml
	sed -i 's,<ENVIRONMENT_NAME>,$(ENVIRONMENT_NAME),g' ./config/settings.yml	
	sed -i 's,<REDFLY_BASE_URL>,$(REDFLY_BASE_URL),g' ./config/settings.yml
	sed -i 's,<SITE_AUTH_REALM>,$(REDFLY_BASE_URL),g' ./config/settings.yml
	sed -i 's,<GMAIL_ADDRESS>,$(GMAIL_ADDRESS),g' ./config/settings.yml
	sed -i 's,<GMAIL_CLIENT_ID>,$(GMAIL_CLIENT_ID),g' ./config/settings.yml
	sed -i 's,<GMAIL_CLIENT_SECRET>,$(GMAIL_CLIENT_SECRET),g' ./config/settings.yml
	sed -i 's,<GMAIL_REFRESH_TOKEN>,$(GMAIL_REFRESH_TOKEN),g' ./config/settings.yml
	sed -i 's,<GOOGLE_ANALYTICS_API_3_TRACKING_ID>,$(GOOGLE_ANALYTICS_API_3_TRACKING_ID),g' ./config/settings.yml
	sed -i 's,<GOOGLE_ANALYTICS_API_3_VIEW_ID>,$(GOOGLE_ANALYTICS_API_3_VIEW_ID),g' ./config/settings.yml
	sed -i 's,<GOOGLE_OAUTH2_CREDENTIALS_FILE>,$(GOOGLE_OAUTH2_CREDENTIALS_FILE),g' ./config/settings.yml
	sed -i 's,<GOOGLE_ANALYTICS_4_MEASUREMENT_ID>,$(GOOGLE_ANALYTICS_4_MEASUREMENT_ID),g' ./config/settings.yml	
	sed -i 's,<MYSQL_PASSWORD>,$(MYSQL_PASSWORD),g' ./config/settings.yml
	sed -i 's,<NCBI_API_KEY>,$(NCBI_API_KEY),g' ./config/settings.yml
	sed -i 's,<RECAPTCHA_PUBLIC_KEY>,$(RECAPTCHA_PUBLIC_KEY),g' ./config/settings.yml
	sed -i 's,<RECAPTCHA_PRIVATE_KEY>,$(RECAPTCHA_PRIVATE_KEY),g' ./config/settings.yml
	cp ./html/admin/new/app/Config.js.dist ./html/admin/new/app/Config.js
	chmod 0644 ./html/admin/new/app/Config.js
	sed -i 's,<REDFLY_BASE_URL>,$(REDFLY_BASE_URL),g' ./html/admin/new/app/Config.js

database-backup:
	docker build -t mariadb_database_backup ./utilities/mariadb/database-backup/
	docker run --rm -tv $(PWD)/db/dumps:/db/dumps --user $$(id -u):$$(id -g) --env-file ./.env --network redfly_default mariadb_database_backup

# Note that the datadumps procedure will write the new output data to the directory: html/datadumps
datadumps: vendor
	[ -d html/datadumps ] || (mkdir -p ./html/datadumps && chmod -R g+rwX,o+rwX ./html/datadumps)
	docker-compose exec --user $$(id -u):$$(id -g) php_server php ./html/utilities/dump_flybase.php --rc --out crm_dump.gff3
	docker-compose exec --user $$(id -u):$$(id -g) php_server php ./html/utilities/dump_flybase.php --tfbs --out tfbs_dump.gff3
	docker-compose exec --user $$(id -u):$$(id -g) php_server php ./html/utilities/dump_gbrowse.php --rc --rc_cell_culture_only --tfbs --out redfly.fff

docker-initialize:
	mkdir -p ./db/dumps
	chown $$(id -u):$$(id -g) ./db/dumps
	chmod 0644 ./db/dumps/*.sql.gz
	docker-compose down -v
	docker-compose build
	docker-compose up -d
	sleep 2m

# Useful for restarting containers and re-loading the database from the ./db/dumps directory
docker-restart:
	docker-compose down -v
	docker-compose up -d
	sleep 2m

extjs-libraries-reload:
	docker build -t sencha ./utilities/sencha/
	docker run --rm -tv $(PWD)/html/admin/new:/sencha sencha app refresh
	docker run --rm -tv $(PWD)/html/admin/new:/sencha sencha app build -e development

help-update:
	cd ./docs/help && gitbook build
	rm -rf ./html/gitbook
	mv ./docs/help/_book ./html/gitbook

html/admin/new/ext:
	docker build -t sencha ./utilities/sencha/
	docker run --rm -tv $(PWD)/html/admin/new:/sencha sencha app install -f /sencha-sdks
	docker run --rm -tv $(PWD)/html/admin/new:/sencha sencha app refresh
	docker run --rm -tv $(PWD)/html/admin/new:/sencha sencha app build -e development
	sudo rm ./html/admin/new/index.html

html/images/flyexpress:
	mkdir -p ./html/images/flyexpress
	tar -xzf ./assets/flyexpress/flyexpress_images_2016_05_17.tgz -C ./html/images/flyexpress

html/js/extjs:
	unzip -qo ./assets/extjs/ext-3.4.1.1-gpl.zip -d ./html/js
	rm -rf ./html/js/extjs
	mv ./html/js/ext-3.4.1 ./html/js/extjs

lint: vendor
	$(PHP) ./vendor/bin/phpcbf | true
	$(PHP) ./vendor/bin/phpcs

logs:
	mkdir -p ./logs
	chown $$(id -u):$$(id -g) ./logs
	chmod 0777 ./logs

# Create users and apply permissions after database initialization
mariadb-access:
	$(MARIADB) -uroot -p$(MYSQL_ROOT_PASSWORD) -e "DROP USER IF EXISTS '$(MYSQL_BACKUP_USER)'@'%';"
	$(MARIADB) -uroot -p$(MYSQL_ROOT_PASSWORD) -e "CREATE USER '$(MYSQL_BACKUP_USER)'@'%' IDENTIFIED BY '$(MYSQL_BACKUP_PASSWORD)';"
	$(MARIADB) -uroot -p$(MYSQL_ROOT_PASSWORD) -e "GRANT SELECT ON mysql.proc to '$(MYSQL_BACKUP_USER)'@'%';"
	$(MARIADB) -uroot -p$(MYSQL_ROOT_PASSWORD) -e "GRANT SELECT ON redfly.* TO '$(MYSQL_BACKUP_USER)'@'%';"
	$(MARIADB) -uroot -p$(MYSQL_ROOT_PASSWORD) -e "GRANT SHOW VIEW ON redfly.* TO '$(MYSQL_BACKUP_USER)'@'%';"
	$(MARIADB) -uroot -p$(MYSQL_ROOT_PASSWORD) -e "GRANT TRIGGER ON redfly.* TO '$(MYSQL_BACKUP_USER)'@'%';"

mariadb-audit-plugin-start: logs
	$(MARIADB) -uroot -p$(MYSQL_ROOT_PASSWORD) -e "SET GLOBAL server_audit_logging=ON;"

mariadb-audit-plugin-stop:
	$(MARIADB) -uroot -p$(MYSQL_ROOT_PASSWORD) -e "SET GLOBAL server_audit_logging=OFF;"

mysqldump:
ifndef MYSQL_DUMP_DIR
	$(error MySQL dump directory not specified.)
endif
ifeq ($(MYSQL_DUMP_DIR),/)
	$(error MySQL dump directory cannot be '/')
endif
	docker build -t mariadb_database_backup ./utilities/mariadb/database-backup/
	docker run --rm -tv $(MYSQL_DUMP_DIR):/db/dumps --user $$(id -u):$$(id -g) --env-file ./.env --network redfly_default mariadb_database_backup
	# Clean up backups older than 30 days
	find $(MYSQL_DUMP_DIR) -maxdepth 1 -type f -mtime +30 -name '*.sql.gz' -execdir rm -- '{}' \;

php-libraries-reload:
	docker pull composer:latest
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer --version
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer validate
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer dump-autoload -o

# Note the "$$" in the shell command below, we must escape the $ so it is not interpreted as a make variable
redfly-database-curator-users:
ifndef CURATOR_USERNAME
	$(error CURATOR_USERNAME not specified on command line.)
endif
ifndef CURATOR_PASSWORD
	$(error CURATOR_PASSWORD not specified on command line.)
endif
ifndef CURATOR_FIRSTNAME
	$(error CURATOR_FIRSTNAME not specified on command line.)
endif
ifndef CURATOR_LASTNAME
	$(error CURATOR_LASTNAME not specified on command line.)
endif
ifndef CURATOR_EMAIL
	$(error CURATOR_EMAIL not specified on command line.)
endif
	sh create_new_curators.sh "$(MARIADB)" $(MYSQL_ROOT_PASSWORD) $(CURATOR_USERNAME) $(CURATOR_PASSWORD) $(CURATOR_FIRSTNAME) $(CURATOR_LASTNAME) $(CURATOR_EMAIL)

# Note the "$$" in the shell command below, we must escape the $ so it is not interpreted as a make variable
redfly-database-development-user:
	$(eval PASSWORD_HASH=$(shell env $$(cat .env) php -r "print '{SHA}' . base64_encode(sha1(getenv('REDFLY_PASSWORD'), true));" ))
	$(MARIADB) -uroot -p$(MYSQL_ROOT_PASSWORD) -e "INSERT INTO redfly.Users (username,password,first_name,last_name,email,date_added,state,role) VALUES('$(REDFLY_USER)','$(PASSWORD_HASH)','Development','User','',NOW(),'active','admin') ON DUPLICATE KEY UPDATE password=VALUES(password);"

# Note that the release procedure will write the new reports to the directory: html/datadumps/reports
release:
	[ -d release_output ] || (mkdir -p ./release_output && chmod -R g+rwX,o+rwX ./release_output)
	curl -u $(REDFLY_USER):$(REDFLY_PASSWORD) $(REDFLY_BASE_URL)api/v2/admin/release || (echo "release failed $$?"; exit 1)
	curl -u $(REDFLY_USER):$(REDFLY_PASSWORD) $(REDFLY_BASE_URL)api/v2/admin/delete || (echo "deletion failed $$?"; exit 1)
	[ -d html/datadumps/reports ] || (mkdir -p ./html/datadumps/reports && chmod -R g+rwX,o+rwX ./html/datadumps)
	curl -u $(REDFLY_USER):$(REDFLY_PASSWORD) $(REDFLY_BASE_URL)api/rest/jsonstore/reporterconstruct/minimization > ./release_output/minimization_output_$(date +'%Y%m%d').json || (echo "minimization failed $$?"; exit 1)
	curl -u $(REDFLY_USER):$(REDFLY_PASSWORD) $(REDFLY_BASE_URL)api/rest/jsonstore/reporterconstruct/calculateCrm > ./release_output/calculateCrm_output_$(date +'%Y%m%d').json || (echo "calculateCrm failed $$?"; exit 1)
	docker build -t redfly_icrm:latest ./go/icrm/
	docker run --rm -tv $(PWD)/go/icrm/cmd:/go/src/cmd redfly_icrm
	docker rmi $$(docker images | grep "redfly_icrm" | tr -s " " | cut -d" " -f3)	
	(cd ./go/icrm/cmd && ./icrm_calculation --username=$(MYSQL_USER) --password=$(MYSQL_PASSWORD)) || (echo "icrm_calculation failed $$?"; exit 1)

schema-backup:
	docker build -t mariadb_schema_backup ./utilities/mariadb/schema-backup/
	docker run --rm -tv $(PWD)/db:/db --user $$(id -u):$$(id -g) --env-file ./.env --network redfly_default mariadb_schema_backup

statistics:
	docker build -t redfly_statistics:latest ./go/statistics/
	docker run --rm -tv $(PWD)/go/statistics/cmd:/go/src/cmd redfly_statistics
	docker rmi $$(docker images | grep "redfly_statistics" | tr -s " " | cut -d" " -f3)
	cd ./go/statistics/cmd && ./statistics_report -username=$(MYSQL_USER) -password=$(MYSQL_PASSWORD)

# Note the "$$" in the shell command below, we must escape the $ so it is not interpreted as a make variable
student-creation:
ifndef STUDENT_FIRSTNAME
	$(error STUDENT_FIRSTNAME not specified on command line.)
endif
ifndef STUDENT_LASTNAME
	$(error STUDENT_LASTNAME not specified on command line.)
endif
ifndef STUDENT_EMAIL
	$(error STUDENT_EMAIL not specified on command line.)
endif
	sh create_new_students.sh "$(MARIADB)" $(MYSQL_ROOT_PASSWORD) $(STUDENT_FIRSTNAME) $(STUDENT_LASTNAME) $(STUDENT_EMAIL)

# Note the "$$" in the shell command below, we must escape the $ so it is not interpreted as a make variable
student-reset:
ifndef STUDENT_FIRSTNAME
	$(error STUDENT_FIRSTNAME not specified on command line.)
endif
ifndef STUDENT_LASTNAME
	$(error STUDENT_LASTNAME not specified on command line.)
endif
ifndef STUDENT_EMAIL
	$(error STUDENT_EMAIL not specified on command line.)
endif
ifndef STUDENT_PASSWORD
	$(error STUDENT_PASSWORD not specified on command line.)
endif
	sh reset_existing_students.sh "$(MARIADB)" $(MYSQL_ROOT_PASSWORD) $(STUDENT_FIRSTNAME) $(STUDENT_LASTNAME) $(STUDENT_EMAIL) $(STUDENT_PASSWORD)

ubir-backup:
	[ -d ubir ] || (mkdir -p ./ubir && chmod -R g+rwX,o+rwX ./ubir)
	$(eval REDFLY_CURRENT_VERSION=$(shell cat ./config/settings.yml | grep redfly_version | sed "s/    redfly_version: //g" | sed "s/\./-/g"))
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/crm_segment?species_scientific_name=Aedes%20aegypti > all_crm_segments_Aedes_aegypti.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/crm?species_scientific_name=Aedes%20aegypti > all_crms_Aedes_aegypti.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/predicted_crm?species_scientific_name=Aedes%20aegypti > all_predicted_crms_Aedes_aegypti.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/rc?species_scientific_name=Aedes%20aegypti > all_rcs_Aedes_aegypti.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/tfbs?species_scientific_name=Aedes%20aegypti > all_tfbss_Aedes_aegypti.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/crm_segment?species_scientific_name=Anopheles%20gambiae > all_crm_segments_Anopheles_gambiae.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/crm?species_scientific_name=Anopheles%20gambiae > all_crms_Anopheles_gambiae.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/predicted_crm?species_scientific_name=Anopheles%20gambiae > all_predicted_crms_Anopheles_gambiae.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/rc?species_scientific_name=Anopheles%20gambiae > all_rcs_Anopheles_gambiae.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/tfbs?species_scientific_name=Anopheles%20gambiae > all_tfbss_Anopheles_gambiae.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/crm_segment?species_scientific_name=Drosophila%20melanogaster > all_crm_segments_Drosophila_melanogaster.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/crm?species_scientific_name=Drosophila%20melanogaster > all_crms_Drosophila_melanogaster.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/predicted_crm?species_scientific_name=Drosophila%20melanogaster > all_predicted_crms_Drosophila_melanogaster.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/rc?species_scientific_name=Drosophila%20melanogaster > all_rcs_Drosophila_melanogaster.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/tfbs?species_scientific_name=Drosophila%20melanogaster > all_tfbss_Drosophila_melanogaster.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/crm_segment?species_scientific_name=Tribolium%20castaneum > all_crm_segments_Tribolium_castaneum.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/crm?species_scientific_name=Tribolium%20castaneum > all_crms_Tribolium_castaneum.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/predicted_crm?species_scientific_name=Tribolium%20castaneum > all_predicted_crms_Tribolium_castaneum.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/rc?species_scientific_name=Tribolium%20castaneum > all_rcs_Tribolium_castaneum.csv
	cd ./ubir && curl -X POST $(REDFLY_BASE_URL)api/v2/file/CSV/tfbs?species_scientific_name=Tribolium%20castaneum > all_tfbss_Tribolium_castaneum.csv
	cd ./ubir && zip redfly-v$(REDFLY_CURRENT_VERSION).zip \
		all_crm_segments_Aedes_aegypti.csv all_crms_Aedes_aegypti.csv all_predicted_crms_Aedes_aegypti.csv all_rcs_Aedes_aegypti.csv all_tfbss_Aedes_aegypti.csv \
		all_crm_segments_Anopheles_gambiae.csv all_crms_Anopheles_gambiae.csv all_predicted_crms_Anopheles_gambiae.csv all_rcs_Anopheles_gambiae.csv all_tfbss_Anopheles_gambiae.csv \
		all_crm_segments_Drosophila_melanogaster.csv all_crms_Drosophila_melanogaster.csv all_predicted_crms_Drosophila_melanogaster.csv all_rcs_Drosophila_melanogaster.csv all_tfbss_Drosophila_melanogaster.csv \
		all_crm_segments_Tribolium_castaneum.csv all_crms_Tribolium_castaneum.csv all_predicted_crms_Tribolium_castaneum.csv all_rcs_Tribolium_castaneum.csv all_tfbss_Tribolium_castaneum.csv
	cd ./ubir && rm *.csv

update:
	[ -d update_output ] || (mkdir -p ./update_output && chmod -R g+rwX,o+rwX ./update_output)
	curl -u $(REDFLY_USER):$(REDFLY_PASSWORD) $(REDFLY_BASE_URL)api/v2/admin/update/citations > ./update_output/citations_output_$(date +'%Y%m%d').tsv || (echo "citations update failed $$?"; exit 1)
	curl -u $(REDFLY_USER):$(REDFLY_PASSWORD) $(REDFLY_BASE_URL)api/v2/admin/update/anatomical_expressions > ./update_output/anatomical_expressions_output_$(date +'%Y%m%d').tsv || (echo "anatomical expressions update failed $$?"; exit 1)
	curl -u $(REDFLY_USER):$(REDFLY_PASSWORD) $(REDFLY_BASE_URL)api/v2/admin/update/genes > ./update_output/genes_output_$(date +'%Y%m%d').tsv || (echo "genes update failed $$?"; exit 1)
#	curl -u $(REDFLY_USER):$(REDFLY_PASSWORD) $(REDFLY_BASE_URL)api/v2/admin/update/features > ./update_output/features_output_$(date +'%Y%m%d').tsv || (echo "features update failed $$?"; exit 1)
	curl -u $(REDFLY_USER):$(REDFLY_PASSWORD) $(REDFLY_BASE_URL)api/v2/admin/update/developmental_stages > ./update_output/developmental_stages_output_$(date +'%Y%m%d').tsv || (echo "developmental stages update failed $$?"; exit 1)
	curl -u $(REDFLY_USER):$(REDFLY_PASSWORD) $(REDFLY_BASE_URL)api/v2/admin/update/biological_processes > ./update_output/biological_processes_output_$(date +'%Y%m%d').tsv  || (echo "biological processes update failed $$?"; exit 1)

vendor-install: composer.json
	[ -f composer.lock ] && rm composer.lock || true
	docker pull composer:latest
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer --version
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer validate
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer install

vendor-update: composer.json
	[ -f composer.lock ] && rm composer.lock || true
	docker pull composer:latest
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer --version
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer validate
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer update

vscode-debug-settings-configuration:
	cp ./.vscode/launch.json.dist ./.vscode/launch.json
	chmod 0664 ./.vscode/launch.json
	sed -i 's,<REDFLY_BASE_URL>,$(REDFLY_BASE_URL),g' ./.vscode/launch.json
	cp ./go/icrm/.vscode/launch.json.dist ./go/icrm/.vscode/launch.json
	chmod 0664 ./go/icrm/.vscode/launch.json
	sed -i 's,<MYSQL_USER>,$(MYSQL_USER),g' ./go/icrm/.vscode/launch.json
	sed -i 's,<MYSQL_PASSWORD>,$(MYSQL_PASSWORD),g' ./go/icrm/.vscode/launch.json
	sed -i 's,<MYSQL_IP_ADDRESS>,$(MYSQL_IP_ADDRESS),g' ./go/icrm/.vscode/launch.json
	sed -i 's,<MYSQL_PORT>,$(MYSQL_PORT),g' ./go/icrm/.vscode/launch.json
	sed -i 's,<MYSQL_DATABASE>,$(MYSQL_DATABASE),g' ./go/icrm/.vscode/launch.json
	cp ./go/statistics/.vscode/launch.json.dist ./go/statistics/.vscode/launch.json
	chmod 0664 ./go/statistics/.vscode/launch.json
	sed -i 's,<MYSQL_USER>,$(MYSQL_USER),g' ./go/statistics/.vscode/launch.json
	sed -i 's,<MYSQL_PASSWORD>,$(MYSQL_PASSWORD),g' ./go/statistics/.vscode/launch.json
	sed -i 's,<MYSQL_IP_ADDRESS>,$(MYSQL_IP_ADDRESS),g' ./go/statistics/.vscode/launch.json
	sed -i 's,<MYSQL_PORT>,$(MYSQL_PORT),g' ./go/statistics/.vscode/launch.json
	sed -i 's,<MYSQL_DATABASE>,$(MYSQL_DATABASE),g' ./go/statistics/.vscode/launch.json

xdebug:
	$(PHP_BASH) cp ./assets/xdebug.ini.dist /usr/local/etc/php/conf.d/xdebug.ini
	$(PHP_BASH) chmod 0644 /usr/local/etc/php/conf.d/xdebug.ini
	$(PHP_BASH) sed -i 's,<XDEBUG_IP_ADDRESS>,$(XDEBUG_IP_ADDRESS),g' /usr/local/etc/php/conf.d/xdebug.ini
	docker-compose restart php_server
