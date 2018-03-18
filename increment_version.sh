#!/bin/sh
VERSION=$(git tag -l --sort -version:refname | head -n1);
if [ -z "$VERSION" ]; then
    echo "No version tag found!!!";
    echo "Add a git tag with the current version (eg. 0.0.1) and try again.";
    exit 1;
else
    echo "Old version: " $VERSION;
    NEW_VERSION=$(echo $VERSION | awk -F. -v OFS=. 'NF==1{print ++$NF}; NF>1{$NF=sprintf("%0*d", length($NF), ($NF+1)); print}');
    echo "New version: " $NEW_VERSION;
    git tag $NEW_VERSION;
    git push origin --tags;
fi
