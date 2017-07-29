#!/bin/sh

alias p='vendor/bin/phpunit --stop-on-failure'
alias pc='p --exclude-group db --coverage-html coverage --coverage-clover ./clover.xml' 
alias cov-check='php coverage-checker.php clover.xml 100';
alias git-push='git push origin HEAD'
alias sync-master="git fetch upstream && git checkout master && git merge upstream/master && git push origin HEAD"
alias pcf='vendor/bin/php-cs-fixer --rules=ordered_imports,ordered_class_elements'
alias cs-fix='pcf fix app && pcf fix tests && pcf fix public'
alias updb='php tools/upgrade-db.php'
alias my='php tools/generate-mysql-opts-file.php && mysql --defaults-file=./mysql-opts && rm -f ./mysql-opts'
alias pd='vendor/bin/phpdoc -d app -d tests'
alias it='tests/integration.sh'
alias hb='vendor/bin/humbug'
