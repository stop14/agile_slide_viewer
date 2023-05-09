<?php

/**
 * @file
 * Contains \Drupal\agile_slide_viewer\Plugin\views\style\AgileSlideViewer.
 *
 */

namespace Drupal\agile_slide_viewer\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\views\ViewExecutable;

/**
 * Style plugin to render each item in a Slide Viewer Layout.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "agile_slide_viewer",
 *   title = @Translation("Agile Slide Viewer"),
 *   help = @Translation("Display the results as a series of slides."),
 *   theme = "views_view_agile_slide_viewer",
 *   display_types = {"normal"}
 * )
 */
class AgileSlideViewer extends StylePluginBase {

  /**
   * Does the style plugin allows to use style plugins.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesRowClass = TRUE;

  /**
   * Does the style plugin support grouping of rows.
   *
   * @var bool
   */
  protected $usesGrouping = FALSE;

  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
  }


  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    // Get default options from the Agile Viewer.
    $default_options = \Drupal::service('agile.viewer.service')
      ->getAgileSlideViewerDefaultOptions();

    // Set default values for the Agile Viewer.
    foreach ($default_options as $option => $default_value) {
      $options[$option] = [
        'default' => $default_value,
      ];
      if (is_bool($default_value)) {
        $options[$option]['bool'] = TRUE;
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Add viewer options to views form.
    $form['agile_slide_viewer'] = [
      '#type' => 'details',
      '#title' => $this->t('Agile Slide Viewer'),
      '#open' => TRUE,
    ];
    if (\Drupal::service('agile.viewer.service')->flightCheck()) {
      $form += \Drupal::service('agile.viewer.service')
        ->buildSettingsForm($this->options);

      // Display each option within the viewer fieldset.
      foreach (\Drupal::service('agile.viewer.service')
                 ->getAgileSlideViewerDefaultOptions() as $option => $default_value) {
        $form[$option]['#fieldset'] = 'agile_slide_viewer';
      }
    }
    else {
      // Disable Agile Slide Viewer as plugin is not installed.
      $form['agile_slide_viewer_disabled'] = [
        '#markup' => $this->t('Drupal is missing one or more supporting libraries. The Agile Slide Viewer has been disabled.'),
        '#fieldset' => 'agile_slide_viewer',
      ];
    }
  }
}
