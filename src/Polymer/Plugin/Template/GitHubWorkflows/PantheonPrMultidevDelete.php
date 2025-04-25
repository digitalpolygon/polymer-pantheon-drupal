<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\GitHubWorkflows;

use DigitalPolygon\Polymer\Robo\Template\GitHub\GitHubWorkflowTemplateBase;
use DigitalPolygon\Polymer\Robo\Template\Token;

final class PantheonPrMultidevDelete extends GitHubWorkflowTemplateBase
{
    public const BASE_FILENAME = 'pantheon-pr-multidev-delete.yml';

    public static function id(): string
    {
        return 'github-pantheon-pr-multidev-delete';
    }

    public function description(): string
    {
        return 'A GitHub workflow that runs on pull requests and deletes the associated Pantheon multidev environment.';
    }

    public function source(): string
    {
        return $this
        ->getConfig()
        ->get('extension.polymer_pantheon_drupal.root') . '/workflows/github/' . self::BASE_FILENAME;
    }

    public function destination(): string
    {
        return $this->getGitHubWorkflowDir() . '/' . self::BASE_FILENAME;
    }

    public function tokens(): array
    {
        return [
        new Token('#php-version#', '8.3', true),
        new Token('#default-branch#', $this->getConfigValue('git.default-branch', 'main'), true),
        new Token('#pantheon-site-name#', $this->getConfigValue('pantheon.site-info.name'), true),
        ];
    }

    public static function collections(): array
    {
        $collections = parent::collections();
        $collections[] = 'pantheon';
        return $collections;
    }
}
