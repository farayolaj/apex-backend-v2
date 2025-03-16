#!/usr/bin/env bash

echo "copy file from tmp dir"
cp ~/import_lms_db/tmp/lms_mood_remote.sql ~/import_lms_db/lms_mood_remote.sql

echo "importing the db"
mysql -u uidlc -p6YFUwapUJ9eGKn9c lms_remote_mood < ~/import_lms_db/lms_mood_remote.sql

echo "removing the sql"
rm -rf  ~/import_lms_db/lms_mood_remote.sql

echo "loggin the importation logs"
echo `date` "lms::moodle -> importation complete" >> ~/import_lms_db/lms_import_log.txt
