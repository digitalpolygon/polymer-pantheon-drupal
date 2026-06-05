<?php

namespace DigitalPolygon\PolymerPantheonDrupalTest\phpunit\unit;

use DigitalPolygon\Polymer\Drupal\Contracts\Event\DrupalSettingsEvents;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Drupal\EventSubscriber\DrupalEventsSubscriber;
use PHPUnit\Framework\TestCase;

class DrupalEventsSubscriberTest extends TestCase
{
    public function testSubscribesToCollectSettingsFiles(): void
    {
        $this->assertSame(
            [DrupalSettingsEvents::COLLECT_SETTINGS_FILES => 'addDefaultSiteSettings'],
            DrupalEventsSubscriber::getSubscribedEvents()
        );
    }

    public function testRelativePathToSiblingDirectory(): void
    {
        $relative = DrupalEventsSubscriber::getRelativePath('/var/www/web', '/var/www/vendor/bin');
        $this->assertSame('../vendor/bin', rtrim($relative, '/'));
    }

    public function testRelativePathToNestedChild(): void
    {
        $relative = DrupalEventsSubscriber::getRelativePath('/a', '/a/b/c');
        $this->assertSame('b/c', rtrim($relative, '/'));
    }

    public function testRelativePathToAncestor(): void
    {
        $relative = DrupalEventsSubscriber::getRelativePath('/a/b/c', '/a');
        $this->assertSame('../..', rtrim($relative, '/'));
    }
}
