DOC_FOLDER=doc

.PHONY: all doc test doc-folder doc-clean lint

all: lint test doc

doc: doc-clean vendor
	php -d date.timezone="UTC" \
		vendor/bin/phpdoc -d src -t ${DOC_FOLDER} \
		--template='vendor/cvuorinen/phpdoc-markdown-public/data/templates/markdown-public'

	rm -r ${DOC_FOLDER}/phpdoc-cache-*
	mv "${DOC_FOLDER}/README.md" "${DOC_FOLDER}/api.md"

doc-folder:
	mkdir -p "${DOC_FOLDER}"

doc-clean: doc-folder
	rm -r "${DOC_FOLDER}"

lint:
	find src  -name '*.php' | xargs -n1 php -l
	find test -name '*.php' | xargs -n1 php -l

test: vendor
	composer test

vendor:
	composer install