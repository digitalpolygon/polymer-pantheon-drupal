<?php

namespace DigitalPolygon\PolymerPantheonDrupalTest\phpunit\unit;

use DigitalPolygon\Polymer\polymer_pantheon_drupal\Hosting\Exception\TerminusPluginNotInstalledException;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Template\Drupal\DrushSiteYaml;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Template\Hosting\GitHubWorkflows\PantheonPush;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Template\Hosting\PantheonYaml;
use DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Template\Hosting\QuicksilverYaml;
use PHPUnit\Framework\TestCase;
use Robo\Config\Config;

class HostingTemplatesTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $serverBackup;

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<string, mixed> $config
     * @return T
     */
    private function template(string $class, array $config = [])
    {
        $template = new $class();
        $template->setConfig(new Config($config));
        return $template;
    }

    public function testPantheonYamlResolvesMappingFromConfig(): void
    {
        $template = $this->template(PantheonYaml::class, [
            'pantheon' => ['file-mappings' => ['pantheon-settings' => [
                'source' => '/ext/pantheon_files/pantheon.yml',
                'destination' => '/repo/pantheon.yml',
            ]]],
        ]);

        $this->assertSame('pantheon-settings', PantheonYaml::id());
        $this->assertSame('/ext/pantheon_files/pantheon.yml', $template->source());
        $this->assertSame('/repo/pantheon.yml', $template->destination());
        $this->assertContains('pantheon', PantheonYaml::collections());
        $this->assertContains('all', PantheonYaml::collections());

        $tokens = $template->tokens();
        $this->assertCount(1, $tokens);
        $this->assertSame('#php-version#', $tokens[0]->getName());
        $this->assertTrue($tokens[0]->getIsRequired());
    }

    public function testQuicksilverYamlInjectsHomeDirectoryIntoConfig(): void
    {
        $_SERVER['HOME'] = '/home/tester';
        $config = new Config([
            'pantheon' => ['file-mappings' => ['quicksilver-config' => [
                'source' => '/ext/pantheon_files/quicksilver.yml',
                'destination' => '/resolved/.quicksilver/quicksilver.yml',
            ]]],
        ]);
        $template = new QuicksilverYaml();
        $template->setConfig($config);

        $this->assertSame('/resolved/.quicksilver/quicksilver.yml', $template->destination());
        $this->assertSame('/home/tester', $config->get('user.home'));
        $this->assertContains('quicksilver', QuicksilverYaml::collections());
    }

    public function testQuicksilverYamlThrowsWhenHomeCannotBeDetermined(): void
    {
        unset($_SERVER['HOME'], $_SERVER['HOMEDRIVE'], $_SERVER['HOMEPATH'], $_SERVER['USERPROFILE']);
        $template = $this->template(QuicksilverYaml::class, [
            'pantheon' => ['file-mappings' => ['quicksilver-config' => ['destination' => 'x']]],
        ]);

        $this->expectException(\RuntimeException::class);
        $template->destination();
    }

    public function testDrushSiteYamlTokensCarrySiteInfo(): void
    {
        $template = $this->template(DrushSiteYaml::class, [
            'pantheon' => ['site-info' => ['id' => 'abc-123', 'name' => 'my-site']],
        ]);

        $tokens = $template->tokens();
        $values = [];
        foreach ($tokens as $token) {
            $values[$token->getName()] = $token->getValue();
        }

        $this->assertSame(['#site-id#' => 'abc-123', '#site-name#' => 'my-site'], $values);
        $this->assertContains('drush', DrushSiteYaml::collections());
        $this->assertContains('drupal', DrushSiteYaml::collections());
    }

    public function testGitHubWorkflowTemplateBuildsPathsFromConfig(): void
    {
        $template = $this->template(PantheonPush::class, [
            'repo' => ['root' => '/repo'],
            'extension' => ['polymer_pantheon_drupal' => ['root' => '/ext']],
        ]);

        $this->assertSame('github-pantheon-push', PantheonPush::id());
        $this->assertSame('/ext/workflows/github/pantheon-push.yml', $template->source());
        $this->assertSame('/repo/.github/workflows/pantheon-push.yml', $template->destination());
        $this->assertContains('github-workflows', PantheonPush::collections());
        $this->assertContains('pantheon', PantheonPush::collections());
    }

    public function testTerminusPluginNotInstalledExceptionMessage(): void
    {
        $exception = new TerminusPluginNotInstalledException('terminus-quicksilver-plugin');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame(
            'The Terminus plugin "terminus-quicksilver-plugin" is not installed.',
            $exception->getMessage()
        );
    }
}
