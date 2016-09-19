#!/bin/sh

export COMPOSER_HOME=/root/ETSIINFbot

git pull &&
php composer.phar update &&
./yii migrate/up --interactive=0