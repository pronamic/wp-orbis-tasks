# Orbis Tasks

## Inspiration

- GitHub issues
  - https://docs.github.com/en/rest/issues
- Basecamp to-dos
  - https://github.com/basecamp/bc3-api/blob/master/sections/todos.md

## Deploy

The plugin is deployed with [Deployer](https://deployer.org/). The hosts are not part of this repository, they are imported from a separate file via the `DEPLOYER_IMPORT` environment variable:

```
DEPLOYER_IMPORT=~/deployer-orbis.php vendor/bin/dep deploy
```

Or via the Composer script, which uses `~/deployer-orbis.php` unless `DEPLOYER_IMPORT` is set:

```
composer deploy
```
