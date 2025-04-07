# Workflows

## GitHub Actions

To install the workflows, run the following command:

```bash
polymer template:generate:collection pantheon
```

Provided by this extension are two workflows:

- [pantheon-push.yml](#pantheon-push-yml)
- [pantheon-push-dev.yml](#pantheon-push-devyml)
- [pantheon-pr-multidev-create.yml](#pantheon-pr-multidev-createyml)
- [pantheon-pr-multidev-delete.yml](#pantheon-pr-multidev-deleteyml)

### `pantheon-push.yml`

[Associated template](templates.md#github-pantheon-push)

This workflow is meant to be invoked by other workflows.

It requires two inputs:

- `artifact`: The configured artifact ID to compile.
- `branch`: The branch pushed to Pantheon, with the compiled artifact.

It requires two secrets:

- `SSH_KEY`: The GitHub Actions secret which contains an SSH private key that can be used to push to Pantheon.
- `KNOWN_HOSTS`: The GitHub Actions secret which contains the known hosts file for the Pantheon repository.

!!! note

    The workflow may need alterations to satisfy all build requirements for the artifact, such as installing `npm`
    or `yarn` at specific versions.

### `pantheon-push-dev.yml`

[Associated template](templates.md#github-pantheon-push-dev)

This workflow is meant to be invoked by the `pantheon-push.yml` workflow.

By default, this workflow will run on pushes to the `main` branch of the source
repository and will push the compiled artifact to the `master` branch on
Pantheon, which will deploy the artifact to the `dev` environment.

### `pantheon-pr-multidev-create.yml`

[Associated template](templates.md#github-pantheon-pr-multidev-create)

A GitHub workflow that operates on pull requests into the default branch. It will build and push an artifact on the
`pr-{number}` branch of the configured git repositories. If this is successful, a multidev environment will then
be created for it.

Requires the following templates to be installed:

### `pantheon-pr-multidev-delete.yml`

[Associated template](templates.md#github-pantheon-pr-multidev-delete)

A GitHub workflow that runs when a pull request is closed that was based against the default branch. It a multidev
environment exists named `pr-{number}`, it will be deleted.
