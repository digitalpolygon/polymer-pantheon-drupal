<?php

namespace DigitalPolygon\Polymer\polymer_pantheon_drupal\Hosting\Pantheon;

use Consolidation\Config\ConfigInterface;
use DigitalPolygon\Polymer\Core\Robo\Exceptions\BadConfigurationValueException;
use Psr\Log\LoggerInterface;

/**
 * Pantheon repository and multidev naming rules.
 *
 * Agnostic Pantheon knowledge (no Drupal references): the single source for
 * the git-repository URL regex (previously duplicated between a hook
 * constant and config/default.yml) and the multidev branch-name rules.
 * Registered as `pantheonRepository`.
 */
final class PantheonRepository
{
    /**
     * Default pattern for a Pantheon code-server git remote. Override with
     * the `pantheon.git-repo-regex` configuration value.
     */
    public const DEFAULT_GIT_URL_REGEX = '/^ssh:\/\/codeserver\.dev\.[a-f0-9\-]+@codeserver\.dev\.[a-f0-9\-]+\.drush\.in:2222\/~\/repository\.git$/';

    /**
     * Multidev names: all lowercase, max 11 chars, at most one dash.
     *
     * @see https://docs.pantheon.io/guides/multidev/create-multidev
     */
    public const MULTIDEV_NAME_REGEX = '/^(?!.*-.*-)[a-z0-9\-]{1,11}$/';

    /**
     * Environment names reserved by Pantheon.
     *
     * @var string[]
     */
    public const RESERVED_MULTIDEV_NAMES = [
        'settings',
        'team',
        'support',
        'multidev',
        'debug',
        'files',
        'tags',
        'billing',
    ];

    public function __construct(private ConfigInterface $config, private ?LoggerInterface $logger = null)
    {
    }

    /**
     * The regex identifying Pantheon git remotes.
     *
     * Honors a valid `pantheon.git-repo-regex` override; an invalid pattern
     * logs a warning and falls back to the default.
     */
    public function gitRepoRegex(): string
    {
        $configured = $this->config->get('pantheon.git-repo-regex');
        if (is_string($configured) && $configured !== '') {
            if (@preg_match($configured, '') !== false) {
                return $configured;
            }
            $exception = new BadConfigurationValueException('pantheon.git-repo-regex', $configured, self::DEFAULT_GIT_URL_REGEX);
            $this->logger?->warning($exception->getMessage());
        }
        return self::DEFAULT_GIT_URL_REGEX;
    }

    /**
     * Whether the given git remote URL is a Pantheon code server.
     */
    public function isPantheonGitUrl(string $url): bool
    {
        return (bool) preg_match($this->gitRepoRegex(), $url);
    }

    /**
     * Validate a branch name destined for a Pantheon remote.
     *
     * `master` is always allowed; anything else is assumed to target a
     * multidev environment and must satisfy Pantheon's naming rules.
     *
     * @throws \Exception
     */
    public function assertValidMultidevBranch(string $branchName): void
    {
        if ('master' === $branchName) {
            return;
        }

        if (!preg_match(self::MULTIDEV_NAME_REGEX, $branchName)) {
            throw new \Exception("Branch name '$branchName' is invalid. Pantheon multidev branch names must be all lowercase, no more than 11 characters, and can contain a dash (-).");
        }

        if (in_array($branchName, self::RESERVED_MULTIDEV_NAMES, true)) {
            throw new \Exception("Branch name '$branchName' is invalid. Pantheon multidev branch names cannot be any of the following: " . implode(', ', self::RESERVED_MULTIDEV_NAMES));
        }
    }
}
