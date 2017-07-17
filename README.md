# php-memory-cache

[![Build Status](https://travis-ci.org/sjinks/php-memory-cache.svg?branch=master)](https://travis-ci.org/sjinks/php-memory-cache)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sjinks/php-memory-cache/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sjinks/php-memory-cache/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/sjinks/php-memory-cache/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sjinks/php-memory-cache/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/sjinks/php-memory-cache/badges/build.png?b=master)](https://scrutinizer-ci.com/g/sjinks/php-memory-cache/build-status/master)

PSR-6 and PSR-16 compliant memory cache.

Because the cache does not outlive the request, the package should probably not be used in a production environment;
however, it still can be useful to run tests.
