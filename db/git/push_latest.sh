#!/bin/sh
cd cbps-db
git config --global user.name "GITHUB_USERNAME"
git config --global user.email "GITHUB_EMAIL"


git checkout -b $1
git commit -a -m $1
git push https://GITHUB_USERNAME_URLENCODED:GITHUB_PASSWORD_URLENCODED@github.com/KuromeSan/cbps-db.git $1

cd ..

rm -rf cbps-db