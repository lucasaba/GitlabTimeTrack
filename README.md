Gitlab TimeTrack 2
==================

A Symfony project to display time track info from Gitlab Projects

Version 2 upgrades symfony and Docker environment

![Gitlab TimeTrack screenshot](src/AppBundle/Resources/images/screenshot.png)

Docker installation
-------------------

To run the project via docker-compose, make sure you have docker and
docker-compose installed on your system.

> Note: Please configure your GitLab Server URL and your GitLab personal
> access token in [docker-compose.yml](./docker-compose.yml) before you spin
> up your containers.

To spin up the containers, just run:

```bash
docker-compose up -d
```

Dependencies installation and DB setup on docker
------------------------------------------------

The first time, you'll need to install dependencies. For this, I've made available some `make` command:

```bash
make install
``` 

The script will install dependencies and initialize the DB

You'll have to provide some information. The default value should be good.

You'll have to provide the gitlab token and server url. The gitlab token can be obtained at
`https://your.gitlab.server.host/profile/personal_access_tokens`

* gitlab_token (yourSuperSecretGitlabToken):
* gitlab_server_url ('https://your.gitlab.server.host/api/v4'):

On docker, you need to copy the `docker-compose.override.yml.dist` to
`docker-compose.override.yml.dist` and update its values:

* GITLAB_TOKEN: 'YourGitlAbToKeN'
* GITLAB_SERVER_URL: 'https://gitlab.com' 

Head to [http://localhost:8080](http://localhost:8080)

That's all!
