#!/usr/bin/env bash

echo "Changing directory to $1"
cd /var/www/dlcportal.ui.edu.ng/public_html

echo "Unzipping deployment"
unzip "$1"

echo "Removing old deployment"
rm -r staging

echo "Serving new deployment"
mv dist staging

echo "Removing deployment archive"
rm -rf "$1"

echo "Done"
