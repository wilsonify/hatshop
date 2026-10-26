# The chapter directories this target used to fan out to (c02 - ... c17 - ...)
# were removed in the src/ refactor; the application and its tests now live in
# src/hatshop. core-unit is hermetic; test-integration below calls a deployed
# site and needs HATSHOP_BASE_URL (see docs/content/docs/*/integration-testing.md)
# or reachability of the production URL.
test:
	cd src/hatshop && ./core/vendor/bin/phpunit --testsuite core-unit

test-integration:
	cd src/hatshop && ./core/vendor/bin/phpunit --testsuite core-integration