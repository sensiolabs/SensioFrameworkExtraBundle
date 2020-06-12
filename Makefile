test:
	composer update --prefer-dist --prefer-stable ${COMPOSER_ARGS}
	vendor/bin/simple-phpunit

test-lowest:
	COMPOSER_ARGS='--prefer-lowest' $(MAKE) test
