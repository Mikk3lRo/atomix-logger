#!/bin/sh
VERSION=$(git tag -l --sort -version:refname);
echo "Old version: " $VERSION;
NEW_VERSION=$(echo $VERSION | awk -F. -v OFS=. 'NF==1{print ++$NF}; NF>1{$NF=sprintf("%0*d", length($NF), ($NF+1)); print}');
echo "New version: " $NEW_VERSION;
