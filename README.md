# Devliver

Your private composer repository.

##  Installation Instruction

### 1. Download the project from github

[Releases](https://github.com/shapecode/devliver/releases)

### 2. Unzip on your Server

`unzip release.zip -d devliver/`

### 3. Install

Go in the project directory.

Run `php bin/composer install` and follow instructions.

### 4. Database structure

Update your database structure.
 
```bash
bin/console doctrine:schema:update --force
```

#### 5. Create user

Create an admin user.

```bash
bin/console fos:user:create --super-admin
```

Follow the instructions and your user will be created.

#### 6. That's it

Done;) Go on!
