<?php

namespace DigitalPolygon\PolymerPantheonDrupalTest\phpunit\unit;

use DigitalPolygon\Polymer\polymer_pantheon_drupal\Hosting\Pantheon\PantheonRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Robo\Config\Config;

class PantheonRepositoryTest extends TestCase
{
    private const PANTHEON_REMOTE = 'ssh://codeserver.dev.0a1b2c3d-4e5f-6789-abcd-ef0123456789@codeserver.dev.0a1b2c3d-4e5f-6789-abcd-ef0123456789.drush.in:2222/~/repository.git';

    /**
     * @param array<string, mixed> $config
     */
    private function repository(array $config = [], ?AbstractLogger $logger = null): PantheonRepository
    {
        return new PantheonRepository(new Config($config), $logger);
    }

    public function testDefaultRegexMatchesPantheonRemotes(): void
    {
        $repository = $this->repository();
        $this->assertTrue($repository->isPantheonGitUrl(self::PANTHEON_REMOTE));
        $this->assertFalse($repository->isPantheonGitUrl('git@github.com:digitalpolygon/some-site.git'));
    }

    /**
     * A valid configured override must take effect — previously the
     * configured value was validated and then discarded (PWT-130).
     */
    public function testValidConfiguredRegexOverridesTheDefault(): void
    {
        $repository = $this->repository(['pantheon' => ['git-repo-regex' => '/^custom-remote$/']]);

        $this->assertSame('/^custom-remote$/', $repository->gitRepoRegex());
        $this->assertTrue($repository->isPantheonGitUrl('custom-remote'));
        $this->assertFalse($repository->isPantheonGitUrl(self::PANTHEON_REMOTE));
    }

    public function testInvalidConfiguredRegexLogsWarningAndFallsBack(): void
    {
        $logger = new class extends AbstractLogger {
            /** @var array<int, array{mixed, string}> */
            public array $records = [];

            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = [$level, (string) $message];
            }
        };

        $repository = $this->repository(['pantheon' => ['git-repo-regex' => 'not-a-regex']], $logger);

        $this->assertSame(PantheonRepository::DEFAULT_GIT_URL_REGEX, $repository->gitRepoRegex());
        $this->assertNotEmpty($logger->records, 'An invalid configured regex must log a warning.');
        $this->assertSame('warning', (string) $logger->records[0][0]);
    }

    public function testMasterIsAlwaysAValidBranch(): void
    {
        $this->repository()->assertValidMultidevBranch('master');
        $this->addToAssertionCount(1);
    }

    public function testReservedMultidevNamesAreRejected(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/cannot be any of/');
        $this->repository()->assertValidMultidevBranch('billing');
    }

    public function testInvalidMultidevNamesAreRejected(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/invalid/');
        $this->repository()->assertValidMultidevBranch('Feature-Branch-Name');
    }
}
