#!/bin/bash
curator_usernames=$3
IFS=',' read -ra curator_usernames_array <<< "$curator_usernames"
curator_passwords=$4
IFS=',' read -ra curator_passwords_array <<< "$curator_passwords"
curator_first_names=$5
IFS=',' read -ra curator_first_names_array <<< "$curator_first_names"
curator_last_names=$6
IFS=',' read -ra curator_last_names_array <<< "$curator_last_names"
curator_emails=$7
IFS=',' read -ra curator_emails_array <<< "$curator_emails"
curators_number=${#curator_usernames_array[@]}
for (( index=0; index<curators_number; index++ ))
do
	curator_password_hash=$(php -r "print '{SHA}' . base64_encode(sha1('${curator_passwords_array[$index]}', true));")
    # The MariaDB root password needs to be changed for the production environment
    $1 -uroot -p$2 -e "INSERT INTO redfly.Users (
        username,
        password,
        first_name,
        last_name,
        email,
        date_added,
        state,
        role
    ) VALUES(
        '${curator_usernames_array[$index]}',
        '${curator_password_hash}',
        '${curator_first_names_array[$index]}',
        '${curator_last_names_array[$index]}',
        '${curator_emails_array[$index]}',
        NOW(),
        'active',
        'curator'
    ) ON DUPLICATE KEY UPDATE password=VALUES(password);"
done
