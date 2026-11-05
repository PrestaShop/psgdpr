#!/bin/bash
PS_VERSION=$1

set -e

# Docker images prestashop/prestashop may be used, even if the shop remains uninstalled
echo "Pull PrestaShop files (Tag ${PS_VERSION})"

docker rm -f temp-ps || true
docker volume rm -f ps-volume || true

docker run -tid --rm -v ps-volume:/var/www/html --name temp-ps prestashop/prestashop:$PS_VERSION

# The image copies its files into the volume when it starts: wait for the end of the copy,
# or the module folder is still being written while it is removed below
echo "Wait for the PrestaShop files"
for i in $(seq 1 60); do
  docker logs temp-ps 2>&1 | grep -q "Starting web server" && break
  sleep 2
done
docker logs temp-ps 2>&1 | grep -q "Starting web server" || { docker logs temp-ps; echo "PrestaShop files not ready"; exit 1; }

# Clear previous instance of the module in the PrestaShop volume
echo "Clear previous module"

docker exec -t temp-ps rm -rf /var/www/html/modules/psgdpr

# Run a container for PHPStan, having access to the module content and PrestaShop sources.
# This tool is outside the composer.json because of the compatibility with PHP 5.6
echo "Run PHPStan using phpstan-${PS_VERSION}.neon file"

docker run --rm --volumes-from temp-ps \
       -v $PWD:/var/www/html/modules/psgdpr \
       -e _PS_ROOT_DIR_=/var/www/html \
       --workdir=/var/www/html/modules/psgdpr phpstan/phpstan:0.12 \
       analyse \
       --configuration=/var/www/html/modules/psgdpr/tests/phpstan/phpstan-$PS_VERSION.neon
