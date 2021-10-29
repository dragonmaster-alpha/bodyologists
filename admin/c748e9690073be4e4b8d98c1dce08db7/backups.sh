#!/bin/sh

HOMEDIR="home2"
USERDIR="famesportswear"

cd "/"${HOMEDIR}"/"${USERDIR}"/public_html/admin/c748e9690073be4e4b8d98c1dce08db7/"

DBUSER=$(grep _DB_USER "../../config/main.php" |cut -d "'" -f 4)
DBPASS=$(grep _DB_PASSWORD "../../config/main.php" |cut -d "'" -f 4)
DBNAME=$(grep _DB_NAME "../../config/main.php" |cut -d "'" -f 4)
FILENAME=$(date +"%Y-%m-%d")
DIR="${PWD##*/}"

# conjob command should be: 
# /home2/username/public_html/admin/c748e9690073be4e4b8d98c1dce08db7/./backups.sh >/dev/null 2>&1

cd "../../"
mkdir "database"
cd "database"

mysqldump --routines "--user=${DBUSER}"  --password=$DBPASS $DBNAME > $DBNAME".sql"
tar -czf $FILENAME".tar.gz" "../uploads/" "../config/" "../database"

mv $FILENAME.tar.gz "../admin/"$DIR
cd "../"
rm -rf "database"

echo
echo "========================================="
echo "Database backup completed"
echo "========================================="
echo