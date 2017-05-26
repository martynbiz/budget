# Budget

TODO

* don't we need $transactionsQuery in dashboard.phtml?
* Money owed to me
* how to attach tags to funds? (e.g. we need to show currency formatting), should be cross fund (e.g. credit card, savings accounts etc)
* Make /reports controller - move /tags to there
* fund types - saving, etc
* reset password, forgot password, ! mail not working
* welcome page - one page template, https://startbootstrap.com/template-overviews/creative/
* eloquent events again? .. remove key from cache when amount changes, tidy up empty groups
* dashboard widget: Budgeted items - budgeted items order of remaining and overspent

* edit profile - connect with gravatar

* how does +/- % look on mobile?
* right and left padding not same on mobile
* graph on homepage - look at other sites, projected savings?
* transtool not checking valid lang array (e.g. translatetool update ar)
* scheduled backups of mysql, sync script
* can auth use aura for sessions?
* switch to aura session for flash messages
* test exceptions, pie chart data, deletes and child dependencies, auth token, category/group is not saved when empty string, models,
* favicon
* rename funds as accounts
* create your own currencies - alter table currencies add column user_id int;
* security! all good?
* performance! all good? cache homepage,

maybe
* filter categories
* restore sliding-menu.js

http://www.pcworld.com/article/3093363/data-center-cloud/the-5-best-budgeting-apps-for-tracking-and-planning-your-financial-life.html, http://ndesaintheme.com/edumix/version_1.2/,


## Install the Application

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
$ cd /var/www/budget/website
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
