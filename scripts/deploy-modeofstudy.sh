#!/usr/bin/env bash

echo "changing directory to $1"
cd /var/www/modeofstudy.ui.edu.ng/public_html || { echo "Failed to change directory"; exit 1; }

echo "backing up files..."
rsync -av * ./old_app

echo "remove all files except the backup..."
find . -maxdepth 1 \
    -type d ! -name 'putme' ! -name 'old_app' ! -name '.' -exec rm -rf {} + \
    -o \
    -type f ! -name '*.zip' -exec rm -f {} +

echo "unzipping deployment $1"
unzip "$1"

echo "removing deployment archive"
rm -rf "$1"

echo "deployment Done..."