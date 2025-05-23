<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\GitHubWorkflows;

use DigitalPolygon\Polymer\Robo\Template\GitHub\GitHubWorkflowTemplateBase;
use DigitalPolygon\Polymer\Robo\Template\Token;

final class PantheonPrMultidevCreate extends GitHubWorkflowTemplateBase
{
    public const BASE_FILENAME = 'pantheon-pr-multidev-create.yml';

    public static function id(): string
    {
        return 'github-pantheon-pr-multidev-create';
    }

    public function description(): string
    {
        return 'A GitHub workflow that runs on pull requests to the default branch, builds an artifact, pushes it, and finally creates a Pantheon multidev environment for the artifact.';
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
        new Token('#default-multidev-source-env#', $this->getConfigValue('pantheon.multidev.default-source-env', 'dev'), true),
        ];
    }

    public static function collections(): array
    {
        $collections = parent::collections();
        $collections[] = 'pantheon';
        return $collections;
    }
}
