# New Relic Integration

Integrate New Relic with your Pantheon site by following these steps:

1. [Activate New Relic on your site](https://docs.pantheon.io/guides/new-relic/activate-new-relic).
2. In the Polymer configuration, set `pantheon.new-relic.enable` to `true`.
3. Run the following command to install required plugins:
   ```bash
   polymer pantheon:terminus:plugins:install
   ```
4. Set up New Relic integration by running:
   ```bash
   polymer pantheon:new-relic:setup
   ```

### What Happens Next?

- A Quicksilver script will be installed to flag deployments in New Relic. This script runs on `deploy` and `sync_code` events.
- The New Relic API key secret will be stored in Pantheon, enabling the Quicksilver script to function.

When a deployment or `sync_code` operation occurs, the Git commit of the deployed code will be used as a deployment marker in New Relic for the corresponding environment.

### Accessing New Relic Data

To view New Relic data for an environment:

1. Navigate to the Pantheon dashboard for the environment.
2. Click the **New Relic** tab.
3. Click **Go to New Relic** to access the data.

!!! tip
    Use New Relic to monitor performance, identify bottlenecks, and optimize your site's behavior during deployments.
