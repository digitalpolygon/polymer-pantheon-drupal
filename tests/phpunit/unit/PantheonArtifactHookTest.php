<?php

namespace DigitalPolygon\PolymerPantheonDrupalTest\phpunit\unit;

use DigitalPolygon\Polymer\polymer_pantheon_drupal\Plugin\Hooks\Hosting\PantheonArtifactHook;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Robo\Config\Config;

/**
 * Exposes the protected validation helpers.
 */
class InspectablePantheonArtifactHook extends PantheonArtifactHook
{
    public function checkBranchName(string $branchName): void
    {
        $this->branchNameIsValid($branchName);
    }

    /**
     * @return string[]
     */
    public function pantheonRemotes(): array
    {
        return $this->getPantheonRemotes();
    }

    public function repoRegex(): string
    {
        return $this->getPantheonRepoRegex();
    }
}

class PantheonArtifactHookTest extends TestCase
{
    private const PANTHEON_REMOTE = 'ssh://codeserver.dev.0a1b2c3d-4e5f-6789-abcd-ef0123456789@codeserver.dev.0a1b2c3d-4e5f-6789-abcd-ef0123456789.drush.in:2222/~/repository.git';

    /**
     * @param array<string, mixed> $config
     */
    private function hook(array $config = []): InspectablePantheonArtifactHook
    {
        $hook = new InspectablePantheonArtifactHook();
        $hook->setConfig(new Config($config));
        return $hook;
    }

    public function testMasterBranchIsAlwaysAllowed(): void
    {
        $this->hook()->checkBranchName('master');
        $this->addToAssertionCount(1);
    }

    public function testValidMultidevBranchNameIsAllowed(): void
    {
        $this->hook()->checkBranchName('feature-1');
        $this->addToAssertionCount(1);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidBranchNames(): array
    {
        return [
            'uppercase' => ['Feature'],
            'too long (12 chars)' => ['feature-1234'],
            'two dashes' => ['a-b-c'],
            'underscore' => ['my_branch'],
        ];
    }

    /**
     * @dataProvider invalidBranchNames
     */
    public function testInvalidMultidevBranchNamesAreRejected(string $branchName): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/invalid/');
        $this->hook()->checkBranchName($branchName);
    }

    public function testPantheonReservedNamesAreRejected(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/reserved|cannot be any of/i');
        $this->hook()->checkBranchName('settings');
    }

    public function testOnlyPantheonRemotesAreDetected(): void
    {
        $hook = $this->hook([
            'git' => ['remotes' => [
                self::PANTHEON_REMOTE,
                'git@github.com:digitalpolygon/some-site.git',
            ]],
        ]);

        $this->assertSame([self::PANTHEON_REMOTE], $hook->pantheonRemotes());
    }

    public function testNoRemotesMeansNoPantheonRemotes(): void
    {
        $this->assertSame([], $this->hook(['git' => ['remotes' => []]])->pantheonRemotes());
    }

    public function testInvalidConfiguredRepoRegexLogsWarningAndFallsBack(): void
    {
        $logger = new class extends AbstractLogger {
            /** @var array<int, array{mixed, string}> */
            public array $records = [];

            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = [$level, (string) $message];
            }
        };

        $hook = $this->hook(['pantheon' => ['git-repo-regex' => 'not-a-regex']]);
        $hook->setLogger($logger);

        $regex = $hook->repoRegex();

        $this->assertMatchesRegularExpression($regex, self::PANTHEON_REMOTE);
        $this->assertNotEmpty($logger->records, 'An invalid configured regex must log a warning.');
        $this->assertSame('warning', (string) $logger->records[0][0]);
    }
}
