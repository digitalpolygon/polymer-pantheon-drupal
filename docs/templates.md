# Templates

The templates available in this extension are documented below.

!!! note

    For an up-to-date list of templates shipped by this extension, run
    `polymer template:list:templates --collection=pantheon`.


## pantheon-settings

Suitable default pantheon.yml file for a Drupal project.

## pantheon-drush-site

A Drush site file for interacting with the configured Pantheon application. The following configuration must be set for
this to produce a useful site file:

- `pantheon.site-info.id`
- `pantheon.site-info.name`

See Drush's [Wildcard Alises for Service Providers](https://www.drush.org/13.x/site-aliases/#wildcard-aliases-for-service-providers)
documentation to read more about how this file functions.

## quicksilver-config

A configuration file for the Quicksilver Terminus plugin. This file is used to configure available Quicksilver project
repositories and profiles.

See https://github.com/pantheon-systems/terminus-quicksilver-plugin/blob/1.x/example-user-config.yml for an example
and explanation of the configuration file.

## github-pantheon-push

A GitHub workflow that builds and pushes an artifact. The workflow must called by other workflows.

## github-pantheon-push-dev

A GitHub workflow that runs on pushes to the default branch. It will build and push an artifact on the `master` branch
of the configured git repositories. Assuming a Pantheon repository target is configured, this will result in the
Pantheon application's dev environment being updated.

Requires the following templates to be installed:

- [github-pantheon-push](#github-pantheon-push)

## github-pantheon-pr-multidev-create

A GitHub workflow that operates on pull requests into the default branch. It will build and push an artifact on the
`pr-{number}` branch of the configured git repositories. If this is successful, a multidev environment will then
be created for it.

Requires the following templates to be installed:

- [github-pantheon-push](#github-pantheon-push)

## github-pantheon-pr-multidev-delete

A GitHub workflow that runs when a pull request is closed that was based against the default branch. It a multidev
environment exists named `pr-{number}`, it will be deleted.
