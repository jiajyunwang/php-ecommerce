#!/bin/bash

yum install -y ImageMagick ImageMagick-devel gcc php-devel php-pear

yes '' | pecl install imagick

/usr/bin/composer.phar update
/usr/bin/composer.phar install --no-dev --optimize-autoloader