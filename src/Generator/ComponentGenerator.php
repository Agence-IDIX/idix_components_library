<?php

namespace Drupal\idix_components_library\Generator;

use Drupal\Console\Core\Generator\Generator;
use Drupal\Console\Core\Generator\GeneratorInterface;
use Drupal\Console\Extension\Manager;
use Drupal\Console\Core\Utils\TwigRenderer;

/**
 * Class ComponentGenerator
 *
 * @package Drupal\Console\Generator
 */
class ComponentGenerator extends Generator implements GeneratorInterface {

  /**
   * @var Manager
   */
  protected $extensionManager;

  /**
   * The twig renderer.
   *
   * @var \Drupal\Console\Core\Utils\TwigRenderer
   */
  protected $renderer;

  /**
   * Constructs a new ComponentCommand object.
   */
  public function __construct(
    Manager $extensionManager,
    TwigRenderer $renderer
  ) {
    $this->extensionManager = $extensionManager;

    $renderer->addSkeletonDir(__DIR__ . '/../../templates/');
    $this->setRenderer($renderer);
  }

  /**
   * {@inheritdoc}
   */
  public function generate(array $parameters) {
    $theme = $parameters['theme'];
    $directory = $parameters['directory'];
    $machineName = $parameters['machine_name'];
    $twig = $parameters['twig'];
    $javascript = $parameters['javascript'];

    $themeInstance = $this->extensionManager->getTheme($theme);

    $baseDir = $themeInstance->getPath() . '/' . $directory . '/' . $machineName;
    $baseFile = $baseDir . '/' . $machineName;

    $this->renderFile(
      'component/styleguide.yml.twig',
      $baseFile . '.styleguide.yml',
      $parameters
    );

    $this->renderFile(
      'component/style.scss.twig',
      $baseFile . '.scss',
      $parameters
    );

    if ($twig) {
      $this->renderFile(
        'component/template.twig.twig',
        $baseFile . '.twig',
        $parameters
      );
    }

    if ($javascript) {
      $this->renderFile(
        'component/script.js.twig',
        $baseFile . '.js',
        $parameters
      );
    }

    $this->renderFile(
      'component/library.yml.twig',
      $themeInstance->getPath() . '/' . $theme . '.libraries.yml',
      $parameters,
      FILE_APPEND
    );
  }

}
