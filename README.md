# Budget

TODO
* indent parent categories in select boxes
* fund switcher
* translations - make keys more descriptive (e.g. transaction_link), japanese translations
* test exceptions
* remove user.findByEmail
* pie chart
* reset password
* fund currency symbols (use sprintf)
* BaseController.getOptions -- ['start': .., 'limit': .., 'page': .., 'total_pages': ..,] .. hide pagiantion if only 1
* JS - date picker, categories autocomplete
* date range
* when deleting a user, does it soft delete child dependencies?
* when redirect to login, store returnTo


## Install the Application ##

```
$ git clone ... budget
$ cd budget
$ composer install
$ chgrp?
```

Create the phinx config file:

```
$ ./vendor/bin/phinx init
```

The edit the phinx.yml file with the following credentials:

```yml
development:
    adapter: mysql
    host: localhost
    name: budget_dev
    user: root
    pass: 'vagrant1'
    port: 3306
    charset: utf8

testing:
    adapter: mysql
    host: localhost
    name: budget_test
    user: root
    pass: 'vagrant1'
    port: 3306
    charset: utf8
```

Budget uses Vagrant for development to make installation simple. Please install Vagrant for this purpose. Otherwise, please refer to the /provision.sh script to understand dependencies required for other installations.

```
$ vagrant up
```

Add the following to /etc/hosts

```
192.168.33.20     budget.vagrant
```

## Testing ##

```
$ vagrant ssh
$ cd /var/www/budget/website
$ APPLICATION_ENV=vagranttest ./vendor/bin/phpunit tests/
```

When writing new tests, some custom controller assertions have been added:

```php
$this->assertQuery('form#register', (string)$response->getBody());
$this->assertQueryCount('ul.errors li', 3, (string)$response->getBody());
```
