#!/usr/bin/env bash

# Source and destination directories
echo "... setting the source directory"
source_directory="/var/www/dlcportal.ui.edu.ng/public_html"

echo "... setting the destination directory"
destination_directory="/var/www/dlcportal.ui.edu.ng/public_html/fe-backup"

# Excluded folder
echo "... excluding not required directories"

# Use rsync to copy files, excluding the specified folder
echo "... copying the backup files"
rsync -av --exclude='fe-backup'  --exclude='i-help' --exclude='admin' --exclude='staging' --exclude='adm' --exclude='apex' --exclude='videos' "$source_directory/" "$destination_directory/"

echo "... running the deployment script"
echo "Changing directory to /var/www/dlcportal.ui.edu.ng/public_html"
cd /var/www/dlcportal.ui.edu.ng/public_html || exit

echo "Unzipping deployment file $1"
unzip $1

echo "Removing old files"
rm -r courses docs images/ _next/ payments results

echo "Copying new files"
mv out/* .

echo "Removing temporary files and archives"
rm -r out/ $1

