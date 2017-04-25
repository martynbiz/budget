# Budget

...

## Install the Application ##

```
$ git clone ... budget
$ cd budget
$ composer install
$ chgrp
```

Budget uses Vagrant for development to make installation simple. Please install Vagrant for this purpose. Otherwise, please refer to the /provision.sh script to understand dependencies required for other installations.

```
$ vagrant up
```

Add the following to /etc/hosts

```
...     budget.vagrant
```

## Testing ##

```
$ vagrant ssh
$ cd /var/www/budget/website
$ vendor/bin/phpunit tests/
```

When writing new tests, some custom controller assertions have been added:

```php
$this->assertQuery('form#register', (string)$response->getBody());
$this->assertQueryCount('ul.errors li', 3, (string)$response->getBody());
```
