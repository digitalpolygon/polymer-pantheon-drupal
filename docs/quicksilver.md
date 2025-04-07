# Quicksilver

We recommend reviewing Pantheon's [Quicksilver documentation](https://docs.pantheon.io/guides/quicksilver).

This extension ships with some methods for configuring your project to leverage Quicksilver more effectively. It
installs webhooks directly into your application and updates `pantheon.yml` to use them.

We use the Terminus Quicksilver plugin to manage the webhooks. To install the plugin, run:

```bash
polymer pantheon:terminus:plugins:install
```

We leverage the Quicksilver plugin's configuration file to associate collections of webhooks with defined profiles.
Install the file by running:

```bash
polymer template:generate:file quicksilver-config
```

The following profile/webhook collections is configured by default in the Quicksilver configuration file:

- deployment
    - drush_deploy_hooks

With `drush_deploy_hooks`, Drush deployment hooks will be installed into your application and configured in
pantheon.yml. It breaks the deployment process into 4 separate steps:

- `drush updb`
- `drush cim`
- `drush cr`
- `drush deploy:hook`

!!! tip

    Why not just use `drush deploy`? Because a Quicksilver webhook script will time out after 2 minutes of operation. If we
    used `drush deploy` in a single script, the entire deployment would need to be completed in 2 minutes or less. By
    breaking `drush deploy` out into its 4 components, each step gets the maximum 2 minutes to complete.

You can see all available webhook collections at:

- https://github.com/digitalpolygon/polymer-pantheon-quicksilver
- https://github.com/pantheon-systems/quicksilver-examples

You can install any webhook collection you find in either of these repositories by running:

```bash
terminus quicksilver:install <webhook_collection>
```
