#!/bin/bash
student_first_names=$3
IFS=',' read -ra student_first_names_array <<< "$student_first_names"
student_last_names=$4
IFS=',' read -ra student_last_names_array <<< "$student_last_names"
student_emails=$5
IFS=',' read -ra student_emails_array <<< "$student_emails"
student_passwords=$6
IFS=',' read -ra student_passwords_array <<< "$student_passwords"
students_number=${#student_first_names_array[@]}
for (( index=0; index<students_number; index++ ))
do
    lower_student_first_name=${student_first_names_array[$index],,}
    lower_student_last_name=${student_last_names_array[$index],,}
    lower_student_email=${student_emails_array[$index],,}
    student_username=${lower_student_first_name:0:1}${lower_student_last_name}
    student_password=${student_passwords_array[$index]}
	student_password_hash=$(php -r "print '{SHA}' . base64_encode(sha1('${student_password}', true));")
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
        '${student_username}',
        '${student_password_hash}',
        '${student_first_names_array[$index]}',
        '${student_last_names_array[$index]}',
        '${lower_student_email}',
        NOW(),
        'active',
        'curator'
    ) ON DUPLICATE KEY UPDATE password=VALUES(password);"
    sleep 2
done
