#!/bin/sh

export COMPOSER_HOME=~

git pull &&
php composer.phar update &&
./yii migrate/up --interactive=0