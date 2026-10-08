# Orbis Tasks

## Inspiration

- GitHub issues
  - https://docs.github.com/en/rest/issues
- Basecamp to-dos
  - https://github.com/basecamp/bc3-api/blob/master/sections/todos.md

## Templates

The plugin ships a single and an archive task template, modelled after `single-orbis_task.php` and `archive-orbis_task.php` of the Orbis 5 theme. They are used unless the theme has its own `single-orbis_task.php` or `archive-orbis_task.php`.

| Template | Shown on | Content |
|---|---|---|
| `templates/archive-orbis_task.php` | Tasks archive | Table with task, project, assignee, time, due date and actions |
| `templates/single-orbis_task.php` | Single task | Layout with description, comments and task details |
| `templates/search-form-filter.php` | Tasks archive | Filter on assignee, through the `get_template_part_templates/filter` action of the theme search form, unless the theme has its own `templates/filter-orbis_task.php` |
| `templates/task-details.php` | Single task | Task details, through the `orbis_before_side_content` action |
| `templates/project-tasks.php` | Single project | Tasks of the project, as a tab through the `orbis_project_sections` filter |

## Deploy

The plugin is deployed with [Deployer](https://deployer.org/). The hosts are not part of this repository, they are imported from a separate file via the `DEPLOYER_IMPORT` environment variable:

```
DEPLOYER_IMPORT=~/deployer-orbis.php vendor/bin/dep deploy
```

Or via the Composer script, which uses `~/deployer-orbis.php` unless `DEPLOYER_IMPORT` is set:

```
composer deploy
```
