IFS='
'
for line in $(tr -s '\t' < tcas5.2_chromosomes_and_scaffolds_information.txt | tr '\t' ' ' | sort)
do
    IFS='  '
    read -ra line_parts <<< $line
    name=${line_parts[0]}
    length=${line_parts[1]}
    echo "\"${name}\": true," >> go_cachebuilder_lines.txt
done
