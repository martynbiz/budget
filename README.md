# Book Share

TODO



## Install the Application

```
$ git clone ... bookshare
$ cd bookshare
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
    name: bookshare_dev
    user: root
    pass: 'vagrant1'
    port: 3306
    charset: utf8

testing:
    adapter: mysql
    host: localhost
    name: bookshare_test
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
192.168.33.30     bookshare.vagrant
```

NPM and Bower are used to manage assets:

```
$ npm install
$ bower install
```

## Assets

To make Gulp run everytime a scss/css/js file is saved:

```
$ gulp
```

## Testing

```
$ vagrant ssh
$ cd /var/www/bookshare/website
$ APPLICATION_ENV=vagranttest ./vendor/bin/phpunit tests/
```

When writing new tests, some custom controller assertions have been added:

```php
$this->assertQuery('form#register', (string)$response->getBody());
$this->assertQueryCount('ul.errors li', 3, (string)$response->getBody());
```

## Translations

*_link
*_button
*_label
*_text
