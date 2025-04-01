<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Commands;

use Composer\IO\ConsoleIO;
use Consolidation\AnnotatedCommand\Attributes\Command;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;

class WorkflowCommands extends TaskBase
{
  /**
   * Generate Pantheon GitHub workflow.
   *
   * @param \Composer\IO\ConsoleIO $io
   *
   * @return int
   */
    #[Command(name: 'pantheon:workflows:generate:drupal')]
    public function testCommand(ConsoleIO $io): int
    {
        $io->write("Test workflows command.");
        return 0;
    }
}
