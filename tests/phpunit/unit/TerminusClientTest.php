<?php

namespace DigitalPolygon\PolymerPantheonDrupalTest\phpunit\unit;

use DigitalPolygon\Polymer\polymer_pantheon_drupal\Hosting\Terminus\TerminusClient;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Hosting\Terminus\TerminusPlugins;
use PHPUnit\Framework\TestCase;
use Robo\Config\Config;

class TerminusClientTest extends TestCase
{
    /**
     * @param array<string, mixed> $config
     */
    private function client(array $config = []): TerminusClient
    {
        return new TerminusClient(new Config($config));
    }

    public function testBinDefaultsToTerminus(): void
    {
        $this->assertSame('terminus', $this->client()->bin());
    }

    public function testBinHonorsConfiguration(): void
    {
        $client = $this->client(['pantheon' => ['terminus' => ['bin' => '/usr/local/bin/terminus']]]);
        $this->assertSame('/usr/local/bin/terminus', $client->bin());
    }

    public function testCommandComposition(): void
    {
        $client = $this->client();
        $this->assertSame('terminus site:list', $client->command('site:list'));
        $this->assertSame('terminus site:list --no-interaction', $client->command('site:list', false));
    }

    public function testPluginCommands(): void
    {
        $client = $this->client();
        $this->assertSame('terminus self:plugin:list | grep some-plugin', $client->pluginIsInstalledCommand('some-plugin'));
        $this->assertSame('terminus self:plugin:install vendor/plugin:1.x-dev --no-interaction', $client->pluginInstallCommand('vendor/plugin:1.x-dev', false));
        $this->assertSame('terminus quicksilver:profile deployment --no-interaction', $client->quicksilverProfileCommand('deployment', false));
    }

    public function testNormalizeSpecAcceptsStrings(): void
    {
        $this->assertSame('vendor/plugin', TerminusPlugins::normalizeSpec('vendor/plugin'));
    }

    public function testNormalizeSpecComposesNameAndVersion(): void
    {
        $this->assertSame('vendor/plugin', TerminusPlugins::normalizeSpec(['name' => 'vendor/plugin']));
        $this->assertSame(
            'vendor/plugin:1.x-dev',
            TerminusPlugins::normalizeSpec(['name' => 'vendor/plugin', 'version' => '1.x-dev'])
        );
    }

    public function testNormalizeSpecRejectsMissingName(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('without a name');
        TerminusPlugins::normalizeSpec(['version' => '1.x-dev']);
    }

    public function testNormalizeSpecRejectsNonStringVersion(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('version must be a string');
        TerminusPlugins::normalizeSpec(['name' => 'vendor/plugin', 'version' => 1]);
    }

    public function testNormalizeSpecRejectsOtherTypes(): void
    {
        $this->expectException(\LogicException::class);
        TerminusPlugins::normalizeSpec(42);
    }
}
