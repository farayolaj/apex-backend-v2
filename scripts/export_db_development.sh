#!/usr/bin/env bash

echo ".......transfering the required file"
rsync -anv /var/www/holynation/gig/edutech/edutech_new/temp/lmsdb/lms_mood_remote.sql root@178.79.176.242:~/import_lms_db/

echo ".......removing the file"
rm -rf /var/www/holynation/gig/edutech/edutech_new/temp/lmsdb/lms_mood_remote.sql
