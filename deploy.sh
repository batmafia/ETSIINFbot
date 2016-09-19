#!/bin/sh


git pull &&
php composer.phar update &&
./yii migrate/up --interactive=0