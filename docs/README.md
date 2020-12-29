# Devliver

Your private self-hosted composer repository.

[![paypal](https://img.shields.io/badge/Donate-Paypal-blue.svg)](http://paypal.me/nloges)
[![liberapay](https://img.shields.io/badge/Donate-Liberapay-yellow.svg)](https://liberapay.com/nicklog/donate)

## Info

> This repo is under development!  

##  Requirements

* **Docker**
* optional a **MariaDB/MySQL** database
* the running web server has **access** to **private** git repositories over **ssh**.

##  Installation

Create a `docker-compose.yml` file in an empty directory.

```yaml
version: '3.6'

services:
  devliver:
    image: nicklog/devliver:latest
    volumes:
      - ./data:/var/www/html/data
      - ${HOME}/.ssh:/home/docker/.ssh
      - ${HOME}/.composer/:/home/docker/.composer/
    environment:
      - TZ=Europe/Berlin
      - DATABASE_NAME=devliver
      - DATABASE_USER=devliver
      - DATABASE_PASSWORD=devliver
      - DATABASE_HOST=database
      - DATABASE_PORT=3306
    depends_on:
      - database
    networks:
      - default
    ports:
      - "9000:80"

  database:
    image: mariadb:latest
    environment:
      - MYSQL_DATABASE=devliver
      - MYSQL_USER=devliver
      - MYSQL_PASSWORD=devliver
      - MYSQL_ROOT_PASSWORD=devliver
    networks:
      - default
```
Change any settings to your needs and then run simply `docker-compose up -d`.  
You should now be able to access the site under port `9000` or the port you set.  
On first call you can create a user. Create one and then login.

## Clients

The packages.json is secured by basic http authentication.  
Add clients. These clients have access to the packages.json and can download archives.  
Each client gets a token automatically. 

## Authentication

The repositories will usually be protected. 
You have to store an SSH key in the ssh directory in the home directory for the corresponding web server. 

## How to Use

To use your Devliver installation in Composer, there is one package repository you have to add to the composer.json in your projects.  
Composer will you ask for credentials to access the packages.json when you update your project.
  
This is your repository of private packages.

```json
{
  "repositories": [
    {
      "type": "composer",
      "url": "https://yourdomain.url",
      "options": {
        "http": {
          "header": [
            "token: CLIENT_TOKEN"
          ]
        }
      }
    }
  ]
}
```

##  Update

Just update the docker image with...
```bash
docker-compose pull
docker-compose up -d
```
