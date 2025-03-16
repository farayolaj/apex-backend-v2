#!/usr/bin/env bash

echo ".......transfering the required file"
rsync -av /var/www/apex/public_html/temp/lmsdb/lms_mood_remote.sql root@178.79.176.242:~/import_lms_db/tmp/

echo ".......removing the file"
rm -rf /var/www/apex/public_html/temp/lmsdb/lms_mood_remote.sql
