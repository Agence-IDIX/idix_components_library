<?php

namespace Drupal\idix_components_library\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Yaml;

class StyleguideController extends ControllerBase {

  public function getTitle ($component_id = NULL) {
    if ($component_id) {
      $component = $this->getComponent($component_id);
      return $component['name'];
    }
    return 'Liste des composants';
  }

  public function page ($component_id = NULL) {
    if ($component_id) {
      return $this->componentPage($component_id);
    }

    $activeTheme = \Drupal::theme()->getActiveTheme();
    $allComponents = $this->getAllComponents();
    $links = [];
    foreach ($allComponents as $component) {
      $url = Url::fromRoute('idix_components_library.page', array('component_id' => $component['id']));
      $link = Link::fromTextAndUrl($component['name'], $url)->toRenderable();
      $link['#attributes'] = ['data-name' => $component['name']];
      $links[] = $link;
    }

    usort($links, function ($a, $b) {
      return strcmp($a['#title'], $b['#title']);
    });

    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['icl_wrapper']],
      'title' => ['#markup' => '<h1>Liste des composants du thème ' . $activeTheme->getExtension()->info['name'] . '</h1>'],
      'search' => [
        '#type' => 'search',
        '#attributes' => [
          'placeholder' => 'Rechercher',
          'autofocus' => 'autofocus',
          'class' => ['icl_search']
        ]
      ],
      'list' => [
        '#type' => 'container',
        'links' => $links,
        '#attributes' => ['class' => ['icl_component-list']]
      ],
      '#attached' => [
        'library' => [
          'idix_components_library/global',
          'idix_components_library/list'
        ]
      ]
    ];
  }

  public function componentPage ($component_id) {
    $component = $this->getComponent($component_id);
    if (is_null($component)) {
      $messenger = \Drupal::messenger();
      $messenger->addMessage('Composant "' . $component_id . '" introuvable.', $messenger::TYPE_ERROR);
      throw new NotFoundHttpException();
    }

    $samples = [];
    foreach ($component['samples'] as $sample) {
      $usage = NULL;
      $usage_language = NULL;
      $view = ['#type' => 'inline_template'];
      if (isset($sample['template'])) {
        $view['#template'] = $sample['template'];
        $usage = $view;
        $usage = htmlentities(\Drupal::service('renderer')->render($usage));
        $usage_language = 'html';
      }
      elseif ($component['twig']) {
        $view['#template'] = "{% include '" . $component['twig'] . "' only %}";
        if (isset($sample['variables'])) {
          $view['#template'] = "{% include '" . $component['twig'] . "' with " . json_encode($sample['variables'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . " only %}";
        }
        $usage = $view['#template'];
        $usage_language = 'twig';
      }

      if ($usage) {
        $usage = [
          '#type' => 'details',
          '#title' => 'Code',
          '#open' => FALSE,
          '#attributes' => ['class' => ['icl_component-sample_details']],
          '#markup' => '<pre class="icl_component-sample_usage"><code class="language-' . $usage_language . '">' . $usage . '</code></pre>',
          '#attached' => ['library' => ['idix_components_library/prism']]
        ];
      }

      $samples[] = [
        'sample' => [
          '#type' => 'container',
          '#attributes' => ['class' => ['icl_component-sample']],
          'title' => [
            '#markup' => '<h2>' . $sample['name'] . '</h2>'
          ],
          'view' => [
            '#type' => 'container',
            '#attributes' => ['class' => ['icl_component-sample_view']],
            '#attached' => ['library' => ['idix_components_library/fullscreen']],
            'content' => $view
          ],
          'usage' => $usage
        ]
      ];
    }

    $variablesRows = [];
    foreach ($component['variables'] as $variable) {
      $variablesRows[] = [
        [
          'data' => $variable['name'],
          'class' => ['name']
        ],
        [
          'data' => $variable['type'],
          'class' => ['type']
        ],
        [
          'data' => ['#markup' => '<pre>' . $variable['default'] . '</pre>'],
          'class' => ['default']
        ],
        [
          'data' => $variable['description'],
          'class' => ['description']
        ]
      ];
    }

    $overrides = [];
    foreach ($component['overrides'] as $type => $override) {
      if ($type == 'css') {
        $overrides[] = [
          '#type' => 'inline_template',
          '#template' => '<style>' . $override . '</style>'
        ];
      }
    }

    $readme = NULL;
    if ($component['readme']) {
      $readme = [
        '#markup' => $component['readme']
      ];
    }

    $allComponents = $this->getAllComponents();
    $nav_links = [];
    foreach ($allComponents as $key => $value) {
      $link_url = Url::fromRoute('idix_components_library.page', ['component_id' => $value['id']]);
      $nav_links[] = Link::fromTextAndUrl($value['name'], $link_url)->toRenderable();
    }
    $components_nav = [
      '#type' => 'details',
      '#title' => 'Liste des composants',
      '#open' => FALSE,
      '#attributes' => ['class' => ['icl_nav']],
      'links' => [
        '#theme' => 'item_list',
        '#items' => $nav_links
      ]
    ];

    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['icl_wrapper']],
      'header' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['icl_header']],
        'content' => [
          'title' => ['#markup' => '<h1>' . $component['name'] . '</h1>'],
          'nav' => $components_nav,
        ]
      ],
      'overrides' => !empty($overrides) ? $overrides : [],
      'samples' => !empty($samples) ? [
        '#theme' => 'item_list',
        '#items' => $samples,
        '#wrapper_attributes' => ['class' => ['icl_component-samples']]
      ] : [],
      'variables' => !empty($variablesRows) ? [
        '#type' => 'details',
        '#title' => 'Variables',
        '#open' => FALSE,
        '#attributes' => ['class' => ['icl_variables-toggle']],
        'content' => [
          '#type' => 'table',
          '#attributes' => ['class' => ['icl_variables-table']],
          '#header' => [
            [
              'data' => 'Name',
              'class' => ['name']
            ],
            [
              'data' => 'Type',
              'class' => ['type']
            ],
            [
              'data' => 'Default',
              'class' => ['default']
            ],
            [
              'data' => 'Description',
              'class' => ['description']
            ]
          ],
          '#rows' => $variablesRows
        ]
      ] : [],
      'readme' => !empty($readme) ? [
        '#markup' => '<h2>Readme</h2>',
        'content' => [
          '#type' => 'container',
          '#attributes' => ['class' => ['icl_readme']],
          'content' => $readme
        ]
      ] : [],
      '#attached' => [
        'library' => [
          'idix_components_library/global',
          'idix_components_library/detail'
        ]
      ]
    ];
  }

  protected function getAllComponents () {
    $components = [];
    $activeTheme = \Drupal::theme()->getActiveTheme();
    $componentsDir = 'library/components';
    $basePath = $activeTheme->getPath() . '/' . $componentsDir;
    $files = file_scan_directory($basePath, '/.styleguide.yml$/');
    foreach ($files as $file) {
      $component = Yaml::parseFile($file->uri);

      preg_match('/(.*)\.styleguide/', $file->name, $matches);
      $component['id'] = $matches[1];

      if (!isset($component['variables'])) {
        $component['variables'] = [];
      }

      if (!isset($component['samples'])) {
        $component['samples'] = [];
      }

      if (!isset($component['overrides'])) {
        $component['overrides'] = [];
      }

      if (!isset($component['readme'])) {
        $component['readme'] = '';
      }

      $twig_path =  $component['id'] . '/' . $component['id'] . '.twig';
      $namespace = '@' . $activeTheme->getName() . '_' . $componentsDir;
      $component['twig'] = file_exists($basePath . '/' . $twig_path) ? $namespace . '/' . $twig_path : FALSE;

      $components[] = $component;
    }
    return $components;
  }

  protected function getComponent ($needle) {
    $allComponents = $this->getAllComponents();
    foreach ($allComponents as $component) {
      if ($component['id'] == $needle) {
        return $component;
      }
    }
    return NULL;
  }
}
