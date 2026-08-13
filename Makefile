PHP ?= php

.PHONY: default help quality validate audit rector rector-dry phpcbf phpstan phpunit phpunit-coverage infection

default: help


help:
	@cat docs/makefile.txt



rector:
	@$(PHP) vendor/bin/rector


rector-dry:
	@$(PHP) vendor/bin/rector --dry-run


phpcs:
	@$(PHP) vendor/bin/phpcs

phpcbf:
	@$(PHP) vendor/bin/phpcbf || $(PHP) vendor/bin/phpcs


phpstan:
	@$(PHP) vendor/bin/phpstan analyse


phpunit:
	@$(PHP) vendor/bin/phpunit


phpunit-coverage:
	@$(PHP) vendor/bin/phpunit --coverage-html build/coverage --coverage-filter src


infection:
	@mkdir -p build/infection
	@$(PHP) vendor/bin/phpunit --coverage-xml build/infection/coverage-xml --log-junit build/infection/junit.xml --coverage-filter src
	@$(PHP) vendor/bin/infection --test-framework=phpunit --threads=1 --skip-initial-tests --coverage=build/infection --no-progress


ci\:quality: phpcs phpstan rector-dry phpunit-coverage composer\:validate

composer\:validate:
	@composer validate --strict

composer\:audit:
	@composer audit --locked --abandoned=fail


