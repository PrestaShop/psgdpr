LOCAL_USER_UID   ?= $(shell id -u)
LOCAL_USER_GID   ?= $(shell id -g)

DOCKER_COMPOSE_CMD ?= docker-compose --file "./docker/docker-compose.yml"

# target: default											- Calling help by default
default: help

# target: help												- Get help on this file
help:
	@egrep "^#" Makefile

# target: php-tests										- Launch all php tests/lints suite
tests:
	php-tests

# target: php-cs-fixer-fix						- Run php cs fixer fix
php-cs-fixer-fix:
	${DOCKER_COMPOSE_CMD} run --rm php sh -c "vendor/bin/php-cs-fixer fix"

# target: php-cs-fixer-lint						- Run php cs fixer dry run
php-cs-fixer-lint:
	${DOCKER_COMPOSE_CMD} run --rm php sh -c "vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no"

# target: php-stan										- Run php stan
php-stan:
	docker run -tid --rm -v ps-volume:/var/www/html --name temp-ps prestashop/prestashop; docker run --rm --volumes-from temp-ps -v $(shell echo $(PWD)):/web/module -e _PS_ROOT_DIR_=/var/www/html --workdir=/web/module ghcr.io/phpstan/phpstan analyse --configuration=/web/module/tests/phpstan/phpstan.neon

