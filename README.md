> [!IMPORTANT]
> This repository is a **read-only split mirror** of [`packages/pantheon-drupal`](https://github.com/digitalpolygon/polymer-drupal-monorepo/tree/0.x/packages/pantheon-drupal)
> in the [polymer-drupal-monorepo](https://github.com/digitalpolygon/polymer-drupal-monorepo).
> Development happens there — please open issues and pull requests against the monorepo.

[![CI](https://github.com/digitalpolygon/polymer-drupal-monorepo/actions/workflows/ci.yml/badge.svg?branch=0.x)](https://github.com/digitalpolygon/polymer-drupal-monorepo/actions/workflows/ci.yml)
[![pages-build-deployment](https://github.com/digitalpolygon/polymer-pantheon-drupal/actions/workflows/pages/pages-build-deployment/badge.svg)](https://digitalpolygon.github.io/polymer-pantheon-drupal/latest/)

# Polymer for Pantheon Drupal Applications

Polymer extension that provides useful integrations for Pantheon Drupal
applications.

- Quicksilver hooks
- GitHub Actions workflows
- Drush site file generation

## Internal seam: `Hosting/` + `Drupal/`

This package is structured so the agnostic Pantheon code can be lifted into a
standalone `digitalpolygon/polymer-pantheon` later (when a second CMS — e.g.
WordPress — needs it) as a clean move + namespace find-replace. See DESIGN.md §7.

```
src/
├── Hosting/   ← agnostic Pantheon services / value objects / exceptions. The
│                future DigitalPolygon\Polymer\Pantheon\. NO Drupal references.
├── Drupal/    ← glue: listens to polymer-drupal events (via
│                polymer-drupal-contracts) and calls into Hosting/.
├── Plugin/    ← discovered Robo commands / hooks / templates; the seam is
│   │            nested UNDER the fixed discovery roots (see note below):
│   ├── Commands/                 (+ Commands/Hosting, Commands/Drupal)
│   ├── Hooks/Hosting/            agnostic Pantheon hooks (PantheonArtifactHook)
│   └── Template/
│       ├── Hosting/              agnostic config + CI (PantheonYaml,
│       │                         QuicksilverYaml, GitHubWorkflows/…)
│       └── Drupal/               Drupal-coupled (DrushSiteYaml)
├── ExtensionInfo.php
└── PolymerPantheonDrupalServiceProvider.php
```

### The one-way dependency rule

**Nothing under `Hosting/` may import Drupal** — not `Drupal\…` (Drupal core),
not `DigitalPolygon\Polymer\Drupal\…` (the Drupal contracts), and not the
`polymer_drupal` extension namespace. The urge to add such a `use` in `Hosting/`
is the signal the code belongs in `Drupal/` instead. This single rule keeps the
future `polymer-pantheon` truly CMS-agnostic, and it is enforced in CI (the
"Hosting seam" job) across **every** `Hosting/` location — top-level and the ones
nested under `Plugin/`.

The `Drupal/` glue is free to depend on both sides: it listens to
`polymer-drupal`'s events through `polymer-drupal-contracts` and calls into
`Hosting/`.

### Discovery & the nested seam

Polymer discovers commands, hooks, and templates with a **recursive** scan rooted
at the fixed `Plugin\Commands` / `Plugin\Hooks` / `Plugin\Template` namespaces,
building each class's FQN from its file path. Because the scan recurses, the seam
is expressed by **nesting under** those roots — `Plugin\Template\Hosting\PantheonYaml`
is discovered exactly like `Plugin\Template\PantheonYaml` would be. No core change
is needed; the fixed discovery roots stay the uniform convention for every plugin.

**Commands are seamed** like everything else (PWT-130): agnostic Terminus and
platform-file commands live under `Plugin\Commands\Hosting`
(`TerminusCommands`, `PantheonFileCommands`, `NewRelicCommands`), Drupal-coupled
commands under `Plugin\Commands\Drupal` (`PantheonDrupalCommands`). The shared
Terminus knowledge lives in `Hosting\Terminus\` (`TerminusClient` for the
configurable binary, `TerminusPlugins` for plugin ids and the validator
selector), and the Pantheon repository/multidev rules in
`Hosting\Pantheon\PantheonRepository`. Command names and aliases are unchanged.
