# Devliver

Your private self-hosted composer repository.

[![paypal](https://img.shields.io/badge/Donate-Paypal-blue.svg)](http://paypal.me/nloges)
[![liberapay](https://img.shields.io/badge/Donate-Liberapay-yellow.svg)](https://liberapay.com/nicklog/donate)
[![License](https://img.shields.io/docker/cloud/build/nicklog/devliver.svg)](https://hub.docker.com/r/nicklog/devliver)
[![License](https://img.shields.io/github/license/nicklog/devliver.svg)](https://github.com/nicklog/devliver)

[comment]: <> ([![License]&#40;https://img.shields.io/docker/build/nicklog/devliver.svg&#41;]&#40;https://hub.docker.com/r/nicklog/devliver&#41;)

## Info

> This repo is under development!  

##  Requirements

* **Docker**
* **MariaDB/MySQL**
* the running docker container has **access** to **private** git repositories with **ssh**.

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

With this example setup the website is not secured by https.  
When you want to secure it I suggest to use a reverse proxy.

## User

On first call of the website you can create a user. Create one and then login.  
The first user becomes an admin and can create more user im necessary.

## Clients

The packages.json, available under `https://devliver-domain.url/packages.json`, is secured by basic http authentication.  
Add clients. These clients have access to the packages.json and can download archives.  
Each client gets a token automatically. 

## Repository Authentication

The git repositories will usually be protected. 
You have to store an SSH key in the ssh directory in the home directory for the corresponding web server
or in the directory of the docker-compose directory.  
No matter, it must be ensured in any case that the SSH keys are available in the Docker container like in the example.

## How to use in composer.json 

To use your Devliver installation in Composer, there is a package repository you have to add to the composer.json in your projects.  
This is your repository of private packages.

```json
{
  "repositories": [
    {
      "type": "composer",
      "url": "https://devliver-domain.url",
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
