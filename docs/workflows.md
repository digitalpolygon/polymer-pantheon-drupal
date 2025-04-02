# Workflows

## GitHub Actions

To install the workflows, run the following command:

```bash
polymer pantheon:workflow:generate github
```

Provided by this extension are two workflows:

- pantheon-push.yml
- pantheon-push-dev.yml

### `pantheon-push.yml`

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

This workflow is meant to be invoked by the `pantheon-push.yml` workflow.

By default, this workflow will run on pushes to the `main` branch of the source
repository and will push the compiled artifact to the `master` branch on
Pantheon, which will deploy the artifact to the `dev` environment.
