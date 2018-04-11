#!/bin/bash

set -ex

MODULE_HOME=${MODULE_HOME:="$(dirname "$(readlink -f $(dirname "$0"))")"}
PHP_VERSION="$(php -r 'echo phpversion();')"
PHPCS_VERSION=${PHPCS_VERSION:=2.9.1}

if [ "$PHP_VERSION" '<' 5.6.0 ]; then
  PHPUNIT_VERSION=${PHPUNIT_VERSION:=4.8}
else
  PHPUNIT_VERSION=${PHPUNIT_VERSION:=5.7}
fi

cd ${MODULE_HOME}

test -d vendor || mkdir vendor
cd vendor/

# phpunit
phpunit_path="phpunit-${PHPUNIT_VERSION}"
if [ ! -e "${phpunit_path}".phar ]; then
  wget -O "${phpunit_path}".phar https://phar.phpunit.de/phpunit-${PHPUNIT_VERSION}.phar
fi
ln -svf "${phpunit_path}".phar phpunit.phar

# phpcs
phpcs_path="phpcs-${PHPCS_VERSION}"
if [ ! -e "${phpcs_path}".phar ]; then
  wget -O "${phpcs_path}".phar \
    https://github.com/squizlabs/PHP_CodeSniffer/releases/download/${PHPCS_VERSION}/phpcs.phar
fi
ln -svf "${phpcs_path}".phar phpcs.phar
