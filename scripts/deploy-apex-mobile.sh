#!/usr/bin/env bash

echo "Changing directory to $1"
cd /var/www/dlcportal.ui.edu.ng/public_html

echo "Unzipping deployment $1"
unzip "$1"

echo "Removing old deployment"
rm -r apex

echo "Serving new deployment"
mv dist apex

echo "Removing deployment archive"
rm "$1"

echo "Deployment Done..."
