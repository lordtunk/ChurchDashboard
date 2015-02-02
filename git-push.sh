#!/bin/bash
if [ $# -eq 0 ]
  then
    echo "No arguments supplied"
    exit 1
fi
if [ -z "$1" ]
  then
    echo "No argument supplied"
    exit 1
fi
gulp
gulp bump-patch
git add --all
git commit -m "$1"
git push origin develop