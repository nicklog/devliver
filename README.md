# Devliver

Your private self-hosted composer repository.

##  Requirements

* **PHP 7.0** or higher
* **MariaDB/MySQL**
* **git** installed on server
* the running web user has **access** to **private** git repositories over **ssh**.

##  Installation Instruction

### 1. Download

... the project from [github.com](https://github.com/shapecode/devliver/releases) to your web directory.
The document root of your vhost have to point to the `public` directory.

### 2. Extract

Login into your terminal and run following command in the project directory.

`unzip release.zip -d ./`

### 3. Install

Now run `php bin/composer install --no-dev --optimize-autoloader` and follow instructions.

### 4. Database

Update your database structure.
 
```bash
bin/console doctrine:schema:update --force
```

#### 5. User

Create an admin user.

```bash
bin/console fos:user:create --super-admin
```

Follow the instructions and your user will be created.

#### 6. That's it

Done ;) Go on!


##  Update Instruction

### 1. Download

... the latest version from [github.com](https://github.com/shapecode/devliver/releases) to your web directory.

### 2. Update

Login into your terminal and run following commands.

```bash
rm -rf bin/ config/ public/ src/ templates/ translations/
unzip release.zip -d ./
php bin/composer install --no-dev --optimize-autoloader
php bin/console doctrine:schema:update --force
```
