<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\GitHubWorkflows;

use DigitalPolygon\Polymer\Robo\Template\GitHub\GitHubWorkflowTemplateBase;
use DigitalPolygon\Polymer\Robo\Template\Token;

final class PantheonPushDev extends GitHubWorkflowTemplateBase
{
    public static function id(): string
    {
        return 'github-pantheon-push-dev';
    }

    public function description(): string
    {
        return 'A GitHub workflow that calls the github-pantheon-push workflow to build and push the artifact to the Pantheon dev environment (master branch).';
    }

    public function source(): string
    {
        return $this
        ->getConfig()
        ->get('extension.polymer_pantheon_drupal.root') . '/workflows/github/pantheon-push-dev.yml';
    }

    public function destination(): string
    {
        return $this->getGitHubWorkflowDir() . '/pantheon-push-dev.yml';
    }

    public function tokens(): array
    {
        return [
        new Token('#default-artifact#', $this->getConfigValue('default_artifact', 'main'), true),
        new Token('#default-branch#', $this->getConfigValue('git.default_branch', 'main'), true),
        ];
    }

    public static function collections(): array
    {
        $collections = parent::collections();
        $collections[] = 'pantheon';
        return $collections;
    }
}
