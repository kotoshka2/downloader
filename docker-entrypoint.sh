#!/bin/bash
set -e

# Fix permissions for writable directories
# We use chmod 777 to ensure the www-data user can write to these directories
# regardless of who owns them on the host machine.
echo "Setting permissions for downloads and api/logs..."
chmod 777 /var/www/html/downloads
chmod 777 /var/www/html/api/logs
touch /var/www/html/cookies.txt && chmod 666 /var/www/html/cookies.txt

# Execute the main container command
exec "$@"
