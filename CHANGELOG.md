# Changelog

## Unreleased

### Added
- CHANGELOG.md file.
- Global DEBUG constant (is true if -vvv passed to run-tests), that could be used in tests to print some more verbose output.
- Possibility to add custom test Publishers (enabled by --publish-results). Pass it as argument of TestStatusListener in phpunit.xml.
- During the run-tests execution, current test results are generated into logs/results.xml. This is useful eg. on long-lasting Jenkins jobs or if you want to know more precise status of the current (or last) test run.

### Changed
- The last argument of run-tests command (browser name) is now always required (as well as the environment name)
- The --publish-results parameter is now used without a value (as a switch to enable publishing test results).
- Unified testcases and test status and results naming:
    - Testcases (= process) statuses: done, prepared, queued
    - Testcases (= process) results (for "done" status): passed, failed, fatal
    - Test statuses: started, done
    - Test results (for "done" status): passed, failed, broken, skipped, incomplete