#!/usr/bin/env bash

echo "Changing directory to $1"
cd /var/www/apex/public_html

echo "Unzipping deployment into application_new directory"
unzip "$1" -d application_new

echo "Removing old redundant deployment if found"
rm -r application_old

echo "Renaming old deployment [--application]"
mv application application_old

echo "Restructure directory to server new deployment"
mv application_new/application application

echo "Removing deployment archive"
rm -r application_new
rm  -rf "$1"

echo "Done"
