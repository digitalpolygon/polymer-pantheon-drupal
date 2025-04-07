# Setup instructions

## Prerequisites

### Install Terminus

Before you get started, if you don't already have Terminus installed in your environment, install it. See the
[Terminus installation instructions](https://docs.pantheon.io/terminus/install) for more information.

### Update Polymer project configuration

Update your `polymer/polymer.yml` file to include the following configuration:

```yaml
pantheon:
  site-info:
    id: <your pantheon site id>
    name: <your pantheon site name>
```

## Install critical files and plugins

To fully integrate this extension with your Drupal and Pantheon application, run:

```bash
polymer pantheon:files:setup:drupal
```

It will do the following:

- Create the Drush site file needed to interact with the remote Pantheon application.
- Create a `pantheon.yml` file if it doesn't already exist.
- Install the Terminus Quicksilver plugin and configuration file.
- Install Quicksilver webhooks into your application and updates `pantheon.yml` to use them.
- Integrate `sites/default/settings.php` with Pantheon's settings file.
