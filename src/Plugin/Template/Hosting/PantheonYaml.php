<?php

namespace DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Template\Hosting;

use DigitalPolygon\Polymer\Core\Robo\Template\Template;
use DigitalPolygon\Polymer\Core\Robo\Template\Token;

class PantheonYaml extends Template
{
    public static function id(): string
    {
        return 'pantheon-settings';
    }

    public function description(): string
    {
        return 'Pantheon configuration file.';
    }

    public function source(): string
    {
        return $this->getConfigValue('pantheon.file-mappings.pantheon-settings.source');
    }

    public function destination(): string
    {
        return $this->getConfigValue('pantheon.file-mappings.pantheon-settings.destination');
    }

    public function tokens(): array
    {
        return [
            new Token('#php-version#', '8.2', true),
        ];
    }

    public static function collections(): array
    {
        return array_merge(parent::collections(), ['pantheon']);
    }
}
