<?php

namespace Drupal\idix_components_library\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Console\Core\Generator\GeneratorInterface;
use Symfony\Component\Console\Input\InputOption;
use Drupal\Console\Extension\Manager;
use Webmozart\PathUtil\Path;
use Drupal\Console\Core\Utils\StringConverter;
use Drupal\Console\Utils\Validator;
use Drupal\Console\Command\Shared\ExtensionTrait;
use Drupal\Console\Core\Utils\ChainQueue;

/**
 * Class ComponentCommand.
 *
 * @DrupalCommand (
 *     extension="idix_components_library",
 *     extensionType="module"
 * )
 */
class ComponentCommand extends ContainerAwareCommand {

  use ExtensionTrait;

  /**
   * Drupal\Console\Core\Generator\GeneratorInterface definition.
   *
   * @var \Drupal\Console\Core\Generator\GeneratorInterface
   */
  protected $generator;

  /**
   * @var Manager
   */
  protected $extensionManager;

  /**
   * @var StringConverter
   */
  protected $stringConverter;

  /**
   * @var Validator
   */
  protected $validator;

  /**
   * @var ChainQueue
   */
  protected $chainQueue;

  /**
   * @var string
   */
  protected $appRoot;

  /**
   * Constructs a new ComponentCommand object.
   */
  public function __construct(
    GeneratorInterface $idix_components_library_idix_components_library_generate_component_generator,
    Manager $extensionManager,
    StringConverter $stringConverter,
    Validator $validator,
    ChainQueue $chainQueue,
    $appRoot
  ) {
    $this->generator = $idix_components_library_idix_components_library_generate_component_generator;
    $this->extensionManager = $extensionManager;
    $this->stringConverter = $stringConverter;
    $this->validator = $validator;
    $this->chainQueue = $chainQueue;
    $this->appRoot = $appRoot;
    parent::__construct();
  }
  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('idix_components_library:generate:component')
      ->setDescription($this->trans('commands.idix_components_library.generate.component.description'))
      ->setAliases(['iclgc'])
      ->addOption(
        'component',
        null,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.idix_components_library.generate.component.component')
      )
      ->addOption(
        'class-name',
        null,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.idix_components_library.generate.component.class-name')
    )
      ->addOption(
        'theme',
        null,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.idix_components_library.generate.component.theme')
      )
      ->addOption(
        'directory',
        null,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.idix_components_library.generate.component.directory')
      )
      ->addOption(
        'twig',
        null,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.idix_components_library.generate.component.twig')
      )
      ->addOption(
        'javascript',
        null,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.idix_components_library.generate.component.javascript')
      );
  }

 /**
  * {@inheritdoc}
  */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $validator = $this->validator;

    $component = $input->getOption('component');
    if (!$component) {
      $component = $this->getIo()->ask(
        $this->trans('commands.idix_components_library.generate.component.questions.component'),
        null
      );
      $input->setOption('component', $component);
    }

    $machineName = $input->getOption('class-name');
    if (!$machineName) {
      $machineName = $this->getIo()->ask(
        $this->trans('commands.idix_components_library.generate.component.questions.class-name'),
        $this->createClassName($component)
      );
      $input->setOption('class-name', $machineName);
    }

    $theme = $input->getOption('theme');
    if (!$theme) {
      $theme = $this->extensionQuestion(false, true);
      $input->setOption('theme', $theme->getName());
    }

    $directory = $input->getOption('directory');
    if (!$directory) {
      $directory = $this->getIo()->ask(
        $this->trans('commands.idix_components_library.generate.component.questions.directory'),
        'library/components',
        function ($directory) use ($machineName, $theme) {
          $fullPath = Path::isAbsolute($directory) ? $directory : Path::makeAbsolute($theme->getPath() . '/' . $directory, $this->appRoot);
          $fullPath = $fullPath . '/' . $machineName . '/' . $machineName . '.styleguide.yml';
          if (file_exists($fullPath)) {
            throw new \InvalidArgumentException(
              sprintf(
                $this->trans('commands.generate.module.errors.directory-exists'),
                $fullPath
              )
            );
          }
          return $directory;
        }
      );
      $input->setOption('directory', $directory);
    }

    $twig = $input->getOption('twig');
    if (!$twig) {
      $twig = $this->getIo()->confirm(
        $this->trans('commands.idix_components_library.generate.component.questions.twig'),
        true
      );
      $input->setOption('twig', $twig);
    }

    $javascript = $input->getOption('javascript');
    if (!$javascript) {
      $javascript = $this->getIo()->confirm(
        $this->trans('commands.idix_components_library.generate.component.questions.javascript'),
        false
      );
      $input->setOption('javascript', $javascript);
    }
  }

  private function createClassName ($name) {
    $delimiter = '-';
    $reg = '@[^a-z0-9'  . $delimiter . ']+@';
    $machine_name = preg_replace($reg, $delimiter, strtolower($name));
    return trim($machine_name, $delimiter);
  }

  private function createScriptObjectName ($className) {
    return preg_replace('/-/', '_', $className);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $component = $input->getOption('component');
    $machineName = $input->getOption('class-name');
    $theme = $input->getOption('theme');
    $directory = $input->getOption('directory');
    $twig = $input->getOption('twig');
    $javascript = $input->getOption('javascript');

    $this->generator->generate([
      'component' => $component,
      'machine_name' => $machineName,
      'theme' => $theme,
      'directory' => $directory,
      'twig' => $twig,
      'javascript' => $javascript,
      'javascript_object_name' => $this->createScriptObjectName($machineName)
    ]);

    $this->chainQueue->addCommand('cr', ["all"]);
  }
}
