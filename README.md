# Devliver

Your private composer repository.

##  Installation Instruction

### 1. Download

... the project from [github.com](https://github.com/shapecode/devliver/releases)

### 2. Extract

`unzip release.zip -d devliver/`

### 3. Install

Go in the project directory.

Run `php bin/composer install --no-dev --optimize-autoloader` and follow instructions.

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

Done;) Go on!
