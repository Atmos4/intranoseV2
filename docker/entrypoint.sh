#!/bin/sh
# entrypoint.sh

# install composer dependencies and run migrations
composer sync

# run the main container CMD, via the original ENTRYPOINT
exec docker-php-entrypoint "$@"
#    ^^^^^^^^^^^^^^^^^^^^^
#    this is the base image's ENTRYPOINT