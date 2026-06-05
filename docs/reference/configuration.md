# Configuration reference (Pantheon)

Defaults shipped by `digitalpolygon/polymer-pantheon-drupal` in its
`config/default.yml`, all under the `pantheon.*` namespace. Override per
project in `polymer/polymer.yml` (or any higher-priority context).

## `pantheon` (top level)

| Key | Default | Description |
| --- | --- | --- |
| `pantheon.git-repo-regex` | Pantheon codeserver SSH URL pattern | Regex identifying Pantheon git remotes among `git.remotes`, used by the artifact-deploy validation hook (branch naming rules, no tags). |

## `pantheon.site-info`

| Key | Default | Description |
| --- | --- | --- |
| `pantheon.site-info.id` | `~` | The Pantheon site UUID (visible in the dashboard URL). |
| `pantheon.site-info.name` | `~` | The Pantheon site name (`terminus site:list --filter=id=<uuid> --field=name`). |

## `pantheon.terminus`

| Key | Default | Description |
| --- | --- | --- |
| `pantheon.terminus.machine-token` | `~` | Machine token used to authenticate Terminus. See [Pantheon's machine-token docs](https://docs.pantheon.io/machine-tokens#create-a-machine-token). |
| `pantheon.terminus.bin` | `terminus` | Terminus executable. |
| `pantheon.terminus.default-site-name` | `${pantheon.site-info.name}` | Default site for Terminus commands that need one. |
| `pantheon.terminus.plugins` | `quicksilver` (`pantheon-systems/terminus-quicksilver-plugin:1.x-dev`), `secrets` (`pantheon-systems/terminus-secrets-manager-plugin`) | Plugins installed by `pantheon:terminus:plugins:install`. Each entry is keyed by a stable id so projects can override individual plugins; values are either a `name[:version]` string or a `{name, version}` map. |

## `pantheon.quicksilver`

| Key | Default | Description |
| --- | --- | --- |
| `pantheon.quicksilver.install-profiles` | `[deployment]` | Quicksilver profiles installed by `pantheon:quicksilver:install-profile`. Profile webhook collections are defined in `~/.quicksilver/quicksilver.yml`. |

## `pantheon.multidev`

| Key | Default | Description |
| --- | --- | --- |
| `pantheon.multidev.default-source-env` | `dev` | Environment new multidevs are based on. |

## `pantheon.new-relic`

| Key | Default | Description |
| --- | --- | --- |
| `pantheon.new-relic.enable` | `false` | Enable the New Relic integration (`pantheon:new-relic:setup`). |

## `pantheon.file-mappings`

Template source/destination mappings for the files this extension manages.
Each mapping has `name`, `source`, and `destination`; override any element to
relocate or rename a file.

| Mapping | Destination default | Description |
| --- | --- | --- |
| `pantheon-settings` | `${repo.root}/pantheon.yml` | The Pantheon configuration file. |
| `drush-site` | `${repo.root}/drush/sites/${pantheon.site-info.name}.site.yml` | The Drush site alias file (tokens filled from `pantheon.site-info`). |
| `quicksilver-config` | `${user.home}/.quicksilver/quicksilver.yml` | The Quicksilver plugin configuration (`${user.home}` is resolved at runtime). |

Sources default to files shipped in this extension
(`${extension.polymer_pantheon_drupal.root}/pantheon_files/…`).
