# Pantheon Files and Quicksilver

Run `polymer pantheon:files:setup:drupal` to fully set up needed Pantheon files.

The following files will be (relative to repository root) created or modified:

- polymer.yml
- web/private/scripts/cache_rebuild.sh
- web/private/scripts/database_updates.sh
- web/private/scripts/drush_config_import.sh
- web/private/scripts/drush_deploy_hook.sh

## Quicksilver

The `pantheon:quicksilver-scripts:inject-hooks` command will inject Drupal
deployment workflow scripts into the project's `pantheon.yml` file. The steps
are the equivalent of running `drush deploy`.

The commands are broken out individually because Quicksilver script execution
times out after 2 minutes. Instead of having a single command that can time out
after 2 minutes, we instead run each deployment command individually, each
getting the maximum 2 minutes to run before timing out. This maximizes the
chances of a successful deployment.

!!! note

    The `drush deploy` command is effectively broken down into:

    - `drush cr`
    - `drush updb`
    - `drush cim`
    - `drush deploy:hook`
