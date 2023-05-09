<?php

/**
 * @file
 * Agile Slide Viewer service file.
 *
 */

namespace Drupal\agile_slide_viewer\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Wrapper methods for Agile Slide Viewer API methods.
 *
 *
 * @ingroup agile_slide_viewer
 */
class AgileSlideViewerService {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The language manager service
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The config factory service
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a AgileSlideViewerService object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ModuleHandlerInterface $module_handler, ThemeManagerInterface $theme_manager, LanguageManager $language_manager, ConfigFactoryInterface $config_factory) {
    $this->moduleHandler = $module_handler;
    $this->themeManager = $theme_manager;
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * Get default slide viewer options.
   *
   * @return array
   *   An associative array of default options for Agile Slide Viewer.
   */
  public function getAgileSlideViewerDefaultOptions() {
    $options = [
      'title' => '',
      'hastitle' => FALSE,
      'introduction' => null,
      'buttonText' => '',
      'subtitle' => '',
      'slidesperrow' => 1,
      'centerMode' => FALSE,
      'autoplay' => FALSE,
      'autoplaySpeed' => 3000,
      'fade' => FALSE,
      'speed' => 500,
      'infinite' => FALSE,
      'extraOptions' => [],
    ];

    // Loazyloading classes are auto-calculated for user simplicity. When
    // lazysizes is used without a Drupal module, this means DX is able to use
    // hook_agile_slide_viewer_default_options_alter or hook_agile_slide_viewer_options_form_alter to
    // override this setting.
    if ($this->moduleHandler->moduleExists('lazy')) {
      $config = $this->configFactory->get('lazy.settings');
      $options['imageLazyloadSelector'] = $config->get('lazysizes.lazyClass');
      $options['imageLazyloadedSelector'] = $config->get('lazysizes.loadedClass');
    }

    return $options;
  }

  /**
   * Apply Agile Slide Viewer to a container.
   *
   * @param array $form
   *   The form to which the JS will be attached.
   * @param string $container
   *   The CSS selector of the container element to apply Agile Slide Viewer to.
   * @param string $item_selector
   *   The CSS selector of the items within the container.
   * @param array $options
   *   An associative array of Agile Slide Viewer options.
   * @param string[] $viewer_ids
   */
  public function applySlideViewerDisplay(&$form, $container, $item_selector, $options = [], $viewer_ids = ['agile_slide_viewer_default']) {

    if (!empty($container)) {
      // For any options not specified, use default options.
      $options += $this->getAgileSlideViewerDefaultOptions();
      if (!isset($item_selector)) {
        $item_selector = '';
      }

      // Setup Agile Slide Viewer component.
      $agile_slide_viewer = [
        'agile_slide_viewer' => [
          $container => [
            'viewer_ids' => $viewer_ids,
            'title_slide' => [
              'title' => $options['title'],
              'subtitle' => $options['subtitle'],
              'hastitle' => (bool) $options['hastitle'],
              'introduction' => $options['introduction'],
              'buttonText' => (string) $options['buttonText']
              ],
            'slidesperrow' => (int) $options['slidesperrow'],
            'centerMode' => (bool) $options['centerMode'],
            'autoplay' => (bool) $options['autoplay'],
            'autoplaySpeed' => (bool) $options['autoplaySpeed'],
            'fade' => (bool) $options['fade'],
            'speed' => (bool) $options['speed'],
            'infinite' => (bool) $options['infinite'],
            'extra_options' => $options['extraOptions'],
          ],
        ],
      ];

      // Allow other modules and themes to alter the settings.
      $context = [
        'container' => $container,
        'item_selector' => $item_selector,
        'options' => $options,
      ];
      $this->moduleHandler->alter('agile_slide_viewer_component', $agile_slide_viewer, $context);
      $this->themeManager->alter('agile_slide_viewer_component', $agile_slide_viewer, $context);

      $form['#attached']['library'][] = 'agile_slide_viewer/agile.viewer';
      if (isset($form['#attached']['drupalSettings'])) {
        $form['#attached']['drupalSettings'] += $agile_slide_viewer;
      }
      else {
        $form['#attached']['drupalSettings'] = $agile_slide_viewer;
      }
    }
  }

  /**
   * Build the slide viewer setting configuration form.
   *
   * @param array (optional)
   *   The default values for the form.
   *
   * @return array
   *   The form
   */
  public function buildSettingsForm($default_values = []) {

    // Load module default values if empty.
    if (empty($default_values)) {
      $default_values = $this->getAgileSlideViewerDefaultOptions();
    }

    /*
     *       'title' => '',
      'hastitle' => FALSE,
      'introduction' => null,
      'buttonText' => '',
      'subtitle' => '',
      'slidesperrow' => 1,
      'autoplay' => FALSE,
      'fade' => FALSE,
      'extraOptions' => []
     */


    $form['title_slide'] = [
      '#type' => 'fieldset',
      '#title' => t('Title Slide')
    ];

    $form['title_slide']['hastitle'] = [
      '#type' => 'checkbox',
      '#title' => t('Use Title Slide'),
      '#description' => t("Provide a title slide. This must be checked for the values below to be visible."),
      '#default_value' => $default_values['title_slide']['hastitle'],
    ];


    $form['title_slide']['title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#description' => t("Appears on the title slide"),
      '#default_value' => $default_values['title_slide']['title'],
    ];

    $form['title_slide']['subtitle'] = [
      '#type' => 'textfield',
      '#title' => t('Subtitle'),
      '#description' => t("Appears on the title slide"),
      '#default_value' => $default_values['title_slide']['subtitle'],
    ];

    $form['title_slide']['introduction'] = [
      '#type' => 'text_format',
      '#title' => t('Introduction'),
      '#description' => 'Text for the title slide.',
      '#format' => $default_values['title_slide']['introduction']['format'],
      '#default_value' =>  $default_values['title_slide']['introduction']['value']
    ];

    $form['title_slide']['buttonText'] = [
      '#type' => 'textfield',
      '#title' => t('Button Text'),
      '#description' => t("Text for the call to action button"),
      '#default_value' => $default_values['title_slide']['buttonText'],
    ];

    $form['slidesperrow'] = [
      '#type' => 'textfield',
      '#title' => t('Items per slide'),
      '#description' => t("The number of items to appear on each slide."),
      '#default_value' => $default_values['slidesperrow'],
      '#size' => 2,
      '#maxlength' => 2
    ];

    $form['centerMode'] = [
      '#type' => 'checkbox',
      '#title' => t('Centre Mode'),
      '#description' => t("Centres slides in container."),
      '#default_value' => $default_values['centermode'],
    ];


    $form['autoplay'] = [
      '#type' => 'checkbox',
      '#title' => t('Autoplay'),
      '#description' => t("Play slideshow without user interaction."),
      '#default_value' => $default_values['autoplay'],
    ];

    $form['autoplaySpeed'] = [
      '#type' => 'textfield',
      '#title' => t('Autoplay speed'),
      '#description' => t("Autoplay speed in milliseconds (1000ms = 1s)."),
      '#default_value' => $default_values['autoplaySpeed'],
    ];

    $form['fade'] = [
      '#type' => 'checkbox',
      '#title' => t('Crossfade'),
      '#description' => t("Use a crossfade effect between slides."),
      '#default_value' => $default_values['fade'],
    ];

    $form['speed'] = [
      '#type' => 'textfield',
      '#title' => t('Transition speed'),
      '#description' => t("Crossfade / swipe speed in milliseconds (1000ms = 1s)."),
      '#default_value' => $default_values['speed'],
    ];

    $form['infinite'] = [
      '#type' => 'checkbox',
      '#title' => t('Infinite loop'),
      '#description' => t("Loops from the end back to the beginning."),
      '#default_value' => $default_values['infinite'],
    ];





    // Allow other modules and themes to alter the form.
    $this->moduleHandler->alter('agile_slide_viewer_options_form', $form, $default_values);
    $this->themeManager->alter('agile_slide_viewer_options_form', $form, $default_values);

    return $form;
  }

  /*
   * Check that prerequisites are installed.
   *
   * STUB FUNCTION
   */

  public function flightCheck(): bool
  {
    return TRUE;
  }

  /**
   * Check if the ImagesLoaded library is installed.
   *
   * @return string|NULL
   *   The imagesloaded library install path.
   */
  public function isImagesloadedInstalled() {

    if (\Drupal::hasService('library.libraries_directory_file_finder')) {
      $library_path = \Drupal::service('library.libraries_directory_file_finder')->find('imagesloaded/imagesloaded.pkgd.min.js');
    }
    elseif ($this->moduleHandler->moduleExists('libraries')) {
      $library_path = libraries_get_path('imagesloaded') . '/imagesloaded.pkgd.min.js';
    }
    else {
      $library_path = 'libraries/imagesloaded/imagesloaded.pkgd.min.js';
    }

    return file_exists($library_path) ? $library_path : NULL;
  }
}
