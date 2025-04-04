<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Template\GitHubWorkflows;

use DigitalPolygon\Polymer\Robo\Template\GitHub\GitHubWorkflowTemplateBase;
use DigitalPolygon\Polymer\Robo\Template\Token;

final class PantheonPush extends GitHubWorkflowTemplateBase
{
    public static function id(): string
    {
        return 'github-pantheon-push';
    }

    public function source(): string
    {
        return $this
        ->getConfig()
        ->get('extension.polymer_pantheon_drupal.root') . '/workflows/github/pantheon-push.yml';
    }

    public function description(): string
    {
        return 'A GitHub callee workflow for building artifacts and pushing them to configured remotes. Must be called by other workflows.';
    }

    public function destination(): string
    {
        return $this->getGitHubWorkflowDir() . '/pantheon-push.yml';
    }

    public function tokens(): array
    {
        return [
        new Token('#php-version#', '8.3', true),
        ];
    }

    public static function collections(): array
    {
        $collections = parent::collections();
        $collections[] = 'pantheon';
        return $collections;
    }
}
