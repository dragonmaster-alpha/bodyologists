#!/bin/sh

HOMEDIR="home2"
USERDIR="famesportswear"

cd "/"${HOMEDIR}"/"${USERDIR}"/public_html/admin/c748e9690073be4e4b8d98c1dce08db7/"

DBUSER=$(grep _DB_USER "../../config/main.php" |cut -d "'" -f 4)
DBPASS=$(grep _DB_PASSWORD "../../config/main.php" |cut -d "'" -f 4)
DBNAME=$(grep _DB_NAME "../../config/main.php" |cut -d "'" -f 4)

# conjob command should be: 
# /home/username/public_html/admin/c748e9690073be4e4b8d98c1dce08db7/./optimize.sh >/dev/null 2>&1

mysqlcheck "--user=${DBUSER}"  --password=$DBPASS --optimize --databases $DBNAME

echo
echo "========================================="
echo "Database optimization completed"
echo "========================================="
echo