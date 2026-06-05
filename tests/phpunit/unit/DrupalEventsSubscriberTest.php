<?php

namespace DigitalPolygon\PolymerPantheonDrupalTest\phpunit\unit;

use DigitalPolygon\Polymer\Drupal\Contracts\Event\CollectSettingsFilesEvent;
use DigitalPolygon\Polymer\Drupal\Contracts\Event\DrupalSettingsEvents;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Drupal\EventSubscriber\DrupalEventsSubscriber;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Robo\Config\Config;

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

    public function testDefaultSiteReceivesPantheonSettingsSnippet(): void
    {
        [$subscriber, $root] = $this->subscriberWithProjectLayout();
        $event = new CollectSettingsFilesEvent();
        $event->setSite('default');

        $subscriber->addDefaultSiteSettings($event);

        $files = $event->getSettingsFiles();
        $this->assertArrayHasKey('pantheonDrupalSettings', $files);
        $this->assertStringContainsString(
            "DRUPAL_ROOT . '/../vendor/pantheon-systems/drupal-integrations'",
            $files['pantheonDrupalSettings']
        );
        $this->removeProjectLayout($root);
    }

    public function testNonDefaultSiteGetsNoSnippet(): void
    {
        [$subscriber, $root] = $this->subscriberWithProjectLayout();
        $event = new CollectSettingsFilesEvent();
        $event->setSite('multisite_a');

        $subscriber->addDefaultSiteSettings($event);

        $this->assertSame([], $event->getSettingsFiles());
        $this->removeProjectLayout($root);
    }

    public function testMissingIntegrationsPackageWarnsAndContributesNothing(): void
    {
        $logger = $this->spyLogger();
        $subscriber = new DrupalEventsSubscriber();
        $subscriber->setConfig(new Config([
            'docroot' => '/nonexistent/web',
            'composer' => ['bin' => '/nonexistent/vendor/bin'],
        ]));
        $subscriber->setLogger($logger);
        $event = new CollectSettingsFilesEvent();
        $event->setSite('default');

        $subscriber->addDefaultSiteSettings($event);

        $this->assertSame([], $event->getSettingsFiles());
        $this->assertNotEmpty($logger->records);
        $this->assertSame('warning', (string) $logger->records[0][0]);
    }

    /**
     * @return array{DrupalEventsSubscriber, string}
     */
    private function subscriberWithProjectLayout(): array
    {
        $root = sys_get_temp_dir() . '/polymer-pantheon-test-' . bin2hex(random_bytes(4));
        mkdir($root . '/web', 0777, true);
        mkdir($root . '/vendor/pantheon-systems/drupal-integrations', 0777, true);

        $subscriber = new DrupalEventsSubscriber();
        $subscriber->setConfig(new Config([
            'docroot' => $root . '/web',
            'composer' => ['bin' => $root . '/vendor/bin'],
        ]));
        $subscriber->setLogger($this->spyLogger());

        return [$subscriber, $root];
    }

    private function removeProjectLayout(string $root): void
    {
        rmdir($root . '/vendor/pantheon-systems/drupal-integrations');
        rmdir($root . '/vendor/pantheon-systems');
        rmdir($root . '/vendor');
        rmdir($root . '/web');
        rmdir($root);
    }

    /**
     * @return AbstractLogger&object{records: array<int, array{mixed, string}>}
     */
    private function spyLogger(): AbstractLogger
    {
        return new class extends AbstractLogger {
            /** @var array<int, array{mixed, string}> */
            public array $records = [];

            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = [$level, (string) $message];
            }
        };
    }
}
