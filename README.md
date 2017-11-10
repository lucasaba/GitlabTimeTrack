Gitlab Timetrack
================

A Symfony project to display time track info from Gitlab Projects

Installation
------------

Clone the repo:

`git clone https://www.github.com/lucasaba/gitlab-timetrack`

Install the dependences with [composer](https://getcomposer.org/):

```bash
user@server:/path/to/gitlab-timetrack-project$ composer install
```

Create the schema

```bash
user@server:/path/to/gitlab-timetrack-project$ php bin/console doctrine:schema:create

```

Fetch the data from your gitlab server:

```bash
user@server:/path/to/gitlab-timetrack-project$ php bin/console gitlab:fetch:all

```

If you don't want to configure your personal web server, just start the php web server:

```bash
user@server:/path/to/gitlab-timetrack-project$ php bin/console server:start

```

Head to [http://localhost:8000](http://localhost:8000)

That's all!