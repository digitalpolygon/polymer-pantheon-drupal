<?php

namespace DigitalPolygon\PolymerPantheon\Drupal\Polymer\Plugin\Commands;

use Consolidation\AnnotatedCommand\Attributes\Argument;
use DigitalPolygon\Polymer\Robo\Workflow\WorkflowHelperTrait;
use Robo\Symfony\ConsoleIO;
use Consolidation\AnnotatedCommand\Attributes\Command;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;

class WorkflowCommands extends TaskBase
{

  use WorkflowHelperTrait;

  /**
   * Generate Pantheon workflows for the specified platform.
   *
   * @param ConsoleIO $io
   *   The console input/output object.
   * @param string $platform
   *   The platform to generate workflows for.
   *
   * @return int
   *   The exit code of the command.
   */
  #[Command(name: 'pantheon:workflow:generate', aliases: ['pwg'])]
  #[Argument(name: 'platform', description: 'The platform to generate workflows for.')]
  public function generateWorkflows(ConsoleIO $io, string $platform): int
  {
    return $this->generateWorkflowFilesFromExtensionAndConfigKey('polymer_pantheon_drupal', 'pantheon.workflow.files', $platform);
  }
}
