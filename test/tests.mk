all: functions phpunit phpcsfix

functions:
	./test/functions.sh

.PHONY: phpunit
phpunit:
	~/bin/wfdev wf --dev-run bin/phpunit

.PHONY: phpcsfix
phpcsfix:
	~/bin/wfdev wf --dev-run vendor/bin/php-cs-fixer fix --dry-run
