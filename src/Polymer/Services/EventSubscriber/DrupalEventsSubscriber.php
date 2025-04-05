<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Services\EventSubscriber;

use Consolidation\Config\ConfigInterface;
use DigitalPolygon\Polymer\Robo\Config\ConfigAwareTrait;
use DigitalPolygon\PolymerDrupal\Polymer\Services\Event\SiteSettingsFiles;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DrupalEventsSubscriber implements EventSubscriberInterface, ConfigAwareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use ConfigAwareTrait;

    public function addDefaultSiteSettings(SiteSettingsFiles $event): void
    {
        $docroot = $this->getConfigValue('docroot');
        $pantheonPath = dirname($this->getConfigValue('composer.bin', '')) . '/pantheon-systems/drupal-integrations';
        if (!is_dir($pantheonPath) || !is_dir($docroot)) {
            $this->logger?->warning('Could not reliably determine the path to the pantheon-systems/drupal-integrations package.');
            return;
        }
        $relative = rtrim($this->getRelativePath($docroot, $pantheonPath), '/');
        $settingFile = <<<SETTING
/**
 * Settings file inclusion provided by pantheon-systems/drupal-integrations.
 * This snippet was generated by the Polymer Pantheon Drupal extension.
 */
\$pantheonDrupalIntegrationsDir = DRUPAL_ROOT . '/$relative';
\$pantheonSettingsFileFromAssets = \$pantheonDrupalIntegrationsDir . '/assets/settings.pantheon.php';
\$settingsDirCallable = ['\Pantheon\Integrations\Assets', 'dir'];
if (is_callable(\$settingsDirCallable)) {
  require_once call_user_func(\$settingsDirCallable) . '/settings.pantheon.php';
}
elseif (file_exists(\$pantheonSettingsFileFromAssets)) {
  require_once \$pantheonSettingsFileFromAssets;
}
SETTING;

        if ('default' === $event->getSite()) {
            $event->addSettingsFile('pantheonDrupalSettings', $settingFile);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SiteSettingsFiles::class => 'addDefaultSiteSettings',
        ];
    }

    /**
     * Calculate the relative path from one directory to another.
     *
     * @param string $from The starting directory.
     * @param string $to The target directory.
     * @return string The relative path.
     */
    public static function getRelativePath(string $from, string $to): string
    {
        // Normalize the paths
        $from = rtrim($from, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $to = rtrim($to, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // Convert paths to arrays
        $fromArr = explode(DIRECTORY_SEPARATOR, $from);
        $toArr = explode(DIRECTORY_SEPARATOR, $to);

        // Find the common path
        $commonLength = 0;
        $maxLength = min(count($fromArr), count($toArr));
        for ($i = 0; $i < $maxLength; $i++) {
            if ($fromArr[$i] === $toArr[$i]) {
                $commonLength++;
            } else {
                break;
            }
        }

        // Calculate the relative path
        $relativePath = str_repeat('..' . DIRECTORY_SEPARATOR, count($fromArr) - $commonLength - 1);
        $relativePath .= implode(DIRECTORY_SEPARATOR, array_slice($toArr, $commonLength));

        return $relativePath;
    }
}
