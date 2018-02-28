# README

# DEVELOPMENT


### Grunt Task Runner  

**ALWAYS** use Grunt to build the production branches. Use the following commands:  

Category | Command | Action
---------| ------- | ------
Test | `grunt test` | Runs the unit tests.
**Build** | `grunt` | Run all default tasks
**Build** | `grunt forminator` | Run build scripts for js on admin and on front


### Set up grunt

#### 1. npm

First install node.js from: <http://nodejs.org/>  

```
#!bash 
# Test it:
$ npm -v

# Install it system wide (optional but recommended):
$ npm install -g npm
```

#### 2. grunt

Install grunt by running this command in command line:

```
#!bash 
# Install grunt:
$ npm install -g grunt-cli
```

#### 3. requirejs

Install requirejs by running this command in command line:

```
#!bash 
# Install requirejs:
$ npm install -g requirejs
```


#### 4. Setup project

In command line switch to the `forminator` plugin folder. Run this command to set up grunt for the Forminator plugin:

```
#!bash 
# Install automation tools for Forminator:
$ cd <path-to-wordpress>/wp-content/plugins/forminator
$ npm install

```

#### 5. Install required tools

Same as 3: Run commands in the `forminator` plugin folder:

```
#!bash 
$ cd <path-to-wordpress>/wp-content/plugins/forminator

# Install composer:
$ php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
$ php composer-setup.php --filename=composer
$ php -r "unlink('composer-setup.php');"

# Install PHP Unit
$ php composer require --dev "phpunit/phpunit=4.8.*"

# Config git with your Name/Email
$ git config user.email "<your email>"
$ git config user.name "<your name>"
```

### Set up wordpress-develop for unit tests

If the command `grunt test` fails you possibly need to follow these steps and install the wordpress-develop repository to your server.

The repository must exist at one of those directories:

* `/srv/www/wptest/wordpress-develop`
* `/srv/www/wordpress-develop/trunk`    
* Or set the environment variable `WP_TESTS_DIR` to the directory

(See: tests/php/bootstrap.php line 12-21 for logic)

```
#!bash 
# Create the directory at correct place:
$ mkdir /srv/www/wordpress-develop

# Download the WP-developer repository:
$ cd /srv/www/wordpress-develop
$ svn co http://develop.svn.wordpress.org/trunk/

# Run this to download latest WP updates:
$ cd /srv/www/wordpress-develop/trunk
$ svn up
```


### Unit testing notes

Introduction to unit testing in WordPress: http://codesymphony.co/writing-wordpress-plugin-unit-tests/

----

# RELEASE
Release notes here