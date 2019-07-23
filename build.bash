#!/bin/bash

rm -rf src/vendor
cd src
composer install
composer dumpautoload -o
cd ..

sass -t compressed --scss --sourcemap=none src/web/scss/app.scss src/web/css/app.css

rm -rf src/vendor/**/.git
tar -zchf dist.tgz -X tar_dist.exclude -T tar_dist.include 
