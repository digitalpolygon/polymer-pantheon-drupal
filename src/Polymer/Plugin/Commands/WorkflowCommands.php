<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Consolidation\AnnotatedCommand\Attributes\Command;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;

class WorkflowCommands extends TaskBase
{
  /**
   * Generate Pantheon GitHub workflow.
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
