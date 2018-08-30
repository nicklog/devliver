# Devliver

Your private self-hosted composer repository.

[![paypal](https://img.shields.io/badge/Donate-Paypal-blue.svg)](http://paypal.me/nloges)
[![liberapay](https://img.shields.io/badge/Donate-Liberapay-yellow.svg)](https://liberapay.com/nicklog/donate)

##  Requirements

* **PHP 7.1** or higher
* **MariaDB/MySQL**
* **git** installed on server
* the running web user has **access** to **private** git repositories over **ssh**.

##  Installation

1. Download the project from [github.com](https://github.com/shapecode/devliver/releases) to your web directory.  
2. Extract: Login into your terminal and run following command in the project directory. `unzip release.zip -d ./`
3. Run `php bin/composer.phar install --no-dev --optimize-autoloader` and follow instructions.
4. Update your database structure. `php bin/console doctrine:schema:update --force`
5. Create an admin user. `php bin/console fos:user:create --super-admin`. Follow instructions.
7. Make a VirtualHost with DocumentRoot pointing to public/

You should now be able to access the site, create a user, etc.

## Users

The packages.json is secured by basic http authentication. Add users in the Admin Panel with the role `ROLE_REPO`. These users have access to the packages.json and can download archives.

## Authentication

The repositories will usually be protected. 
There are 2 ways to allow devliver access to these repositories. 
You store an SSH key in the ssh directory in the home directory for the corresponding web server user. 
Or you can create an `auth.json` file in the `%document_root%/composer` directory. See the documentation on [getcomposer.org](https://getcomposer.org/doc/articles/http-basic-authentication.md).

## How to Use

To use your Devliver installation in Composer, there is one package repository you have to add to the composer.json in your projects.  
Composer will you ask for credentials to access the packages.json when you update your project.
  
This is your repository of private packages.

```json
    {
        "repositories": [
            {
                "type": "composer",
                "url": "https://yourdomain.url"
            }
        ]
    }
```

##  Update

Login into your terminal and run following commands.

```bash
php bin/console devliver:self-update
```

Follow the instructions.
