#!/bin/bash

if [ ! -d vendor ];
then
  echo Retrieving composer dependencies...
  php composer.phar install
fi

mkdir -p build
vendor/bin/phpunit --log-junit build/test-report.xml --include-path "src/main/php:src/test/resources" test/main/php
