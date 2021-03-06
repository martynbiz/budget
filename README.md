# Budget

Personal budget management software written in Slim 3 framework.

## Install the Application

```
$ git clone https://github.com/martynbiz/budget.git budget
$ cd budget
$ composer install
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

To make Gulp run every time a scss/css/js file changes:

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

## API

Login

```
curl -d "email=info@example.com&password=secure" -X POST http://budget.vagrant/api/session/login
```

Get transactions

```
$ curl -H "Authorization: f41d0b...e3f" http://budget.vagrant/api/transactions?fund=1
$ curl -H "Authorization: f41d0b...e3f" http://budget.vagrant/api/transactions?fund=1&month=2018-06
$ curl -H "Authorization: f41d0b...e3f" http://budget.vagrant/api/transactions?fund=1&start=10&limit=10
$ curl -H "Authorization: f41d0b...e3f" http://budget.vagrant/api/transactions?fund=1&order_by=category&order_dir=desc
$ curl -H "Authorization: f41d0b...e3f" http://budget.vagrant/api/transactions?fund=1&category=1
$ curl -H "Authorization: f41d0b...e3f" http://budget.vagrant/api/transactions?fund=1&tag=1
```

Create transaction

```
$ curl -H "Authorization: f41d0b...e3f" -d "fund=1&description=Hello+world" -X POST http://budget.vagrant/api/transactions/create
```







TODO

add primary keys to migration for user_id

default fund when none selected - do we have duplicate transactions for martyn2???
only allow a user to access transactions of their funds

apis
* users/current
* tags
* groups
* widgets/*

Validator code: move it into model so that we can share it between web app and api

test
* user cannot edit funds/etc that doesn't belong to them (api and app)
* api: check user can only access transactions of their own funds

api:

* store token in db.api_access_tokens on authentication
* code requireApiToken to validate access token

!!! Error response - e.g. "Invalid token"
https://www.slimframework.com/docs/handlers/error.html

categories_id=0 .. drill down might not add up to parent category total
- create new column categories.category_id
- index groups is now categories.category_id=0
- add/edit has a parent field
- add trans can add to either

benefits
- filter trans of parent
- less tables/forms, simpler
- transactions can be assigned to a general category eg. "leisure" or "stuff"

Widget: set target, how long to get there? add fund amount to nationwide from 1st may
durations - rather than tagging ALL as e.g. "London201707"
Transfer to/from fund (good for credit cards, loans etc)



bug: view transactions of category/tag, can't then switch months

settings
* users::edit/update/delete
* feature: edit profile, dashboard settings, lang etc
* drop support for multiple currencies per user (instead, user can set their currency in settings)
* feature: order, close on dash. save as user settings. user settings page.

* docblocks

!!! mail not working
* reset password, forgot password, ! mail not working

* category with parent_id? groups is confusing. see how well tags goes .. endless drilldown pie?
! as a transaction can exist under multiple tags, doesn't work for categories .. optgroups for categories filter


* feature: split transactions up
* feature: fund types - saving, etc
* feature: projected savings?
* feature: create your own currencies - alter table currencies add column user_id int;


* welcome page - one page template, https://startbootstrap.com/template-overviews/creative/
* eloquent events again? .. remove key from cache when amount changes, tidy up empty groups
* dashboard widget: Budgeted items - budgeted items order of remaining and overspent



* mobile display: how does +/- % look on mobile? right and left padding not same on mobile

* transtool not checking valid lang array (e.g. translatetool update ar)
* scheduled backups of mysql, sync script
* can auth use aura for sessions?
* switch to aura session for flash messages
* test exceptions, pie chart data, deletes and child dependencies, auth token, category/group is not saved when empty string, models - deletes, tidy ups ..
* favicon
* security! all good?
* performance! all good? cache homepage,

http://www.pcworld.com/article/3093363/data-center-cloud/the-5-best-budgeting-apps-for-tracking-and-planning-your-financial-life.html, http://ndesaintheme.com/edumix/version_1.2/,
