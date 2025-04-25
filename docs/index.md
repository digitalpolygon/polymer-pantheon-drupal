# Polymer for Pantheon Drupal Applications

This package provides a suite of commands designed to integrate Polymer WebOps
Tooling with Pantheon-hosted Drupal applications, enhancing your Drupal
development experience with Pantheon. The commands provided by this package aim
to help users manage their Pantheon applications more effectively by
incorporating what the community has learned over the years from working with
Pantheon-hosted Drupal applications.

## Installation

Require this extension in your `composer.json` file:

```bash
composer require digitalpolygon/polymer-pantheon-drupal:0.x-dev
```

Update your `polymer.yml` file to include the following configuration:

```yaml
pantheon:
  site-info:
    id: <your pantheon site uuid>
    name: <your pantheon site name>
```

To complete installation, follow the [setup instructions in the Setup section](setup.md).
