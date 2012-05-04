<?php
/**
 * @file
 * Implement an audio field, based on the file module's file field.
 */

/**
 * Implements hook_field_load().
 TODO: Do we need this?
 */
function audiofield_field_load($entity_type, $entities, $field, $instances, $langcode, &$items, $age) {
  file_field_load($entity_type, $entities, $field, $instances, $langcode, $items, $age);
}

/**
 * Implements hook_field_prepare_view().
 */
function audiofield_field_prepare_view($entity_type, $entities, $field, $instances, $langcode, &$items) {
  //TODO: Check this:
  //  Remove files specified to not be displayed.
  foreach ($entities as $id => $entity) {
    foreach ($items[$id] as $delta => $item) {
      if (!file_field_displayed($item, $field)) {
        unset($items[$id][$delta]);
      }
      // Ensure consecutive deltas.
      $items[$id] = array_values($items[$id]);
    }
  }
}

/**
 * Implements hook_field_is_empty().
 */
function audiofield_field_is_empty($item, $field) {
  return file_field_is_empty($item, $field);
}

/**
 * Implements hook_field_widget_info().
 */
function audiofield_field_widget_info() {
  return array(
    'audiofield_widget' => array(
      'label' => t('Audio Upload'),
      'field types' => array('file'),
      'settings' => array(
        //'progress_indicator' => 'throbber',
        //'file_extensions' => 'mp3',
      ),
      'behaviors' => array(
        'multiple values' => FIELD_BEHAVIOR_CUSTOM,
        'default value' => FIELD_BEHAVIOR_NONE,
      ),
    ),
  );
}

/**
 * Implements hook_field_widget_settings_form().
 */
 /*
function audiofield_field_widget_settings_form($field, $instance) {
  $widget = $instance['widget'];
  $settings = $widget['settings'];

  // Use the file widget settings form.
  //$form = file_field_instance_settings_form($field, $instance);
 
  $form = file_field_widget_settings_form($field, $instance);
  
  return $form;
}
*/


/**
 * Implements hook_field_widget_form().
 */
function audiofield_field_widget_form(&$form, &$form_state, $field, $instance, $langcode, $items, $delta, $element) {

  // Add display_field setting to field because file_field_widget_form() assumes it is set.
  $field['settings']['display_field'] = 0;

  $elements = file_field_widget_form($form, $form_state, $field, $instance, $langcode, $items, $delta, $element);
  $settings = $instance['settings'];
/*
  foreach (element_children($elements) as $delta) {

    // If not using custom extension validation, ensure this is an audio.
    //$supported_extensions = array('mp3');
    //$extensions = isset($elements[$delta]['#upload_validators']['file_validate_extensions'][0]) ? $elements[$delta]['#upload_validators']['file_validate_extensions'][0] : implode(' ', $supported_extensions);
    //$extensions = array_intersect(explode(' ', $extensions), $supported_extensions);
    //$elements[$delta]['#upload_validators']['file_validate_extensions'][0] = implode(' ', $extensions);

    // Add all extra functionality provided by the Audio widget.
    //$elements[$delta]['#process'][] = 'audiofield_field_widget_process';
  }
*/
  if ($field['cardinality'] == 1) {
    // If there's only one field, return it as delta 0.
    if (empty($elements[0]['#default_value']['fid'])) {
      $elements[0]['#description'] = theme('file_upload_help', array('description' => $instance['description'], 'upload_validators' => $elements[0]['#upload_validators']));
    }
  }
  else {
    $elements['#file_upload_description'] = theme('file_upload_help', array('upload_validators' => $elements[0]['#upload_validators']));
  }

  return $elements;
}

/**
 * An element #process callback for the audiofield_widget field type.
 *
 * Expands the audiofield_widget type to additional fields.
 */
 
function audiofield_field_widget_process($element, &$form_state, $form) {
  $item = $element['#value'];
  $item['fid'] = $element['fid']['#value'];

  $instance = field_widget_instance($element, $form_state);

  $settings = $instance['settings'];
  $widget_settings = $instance['widget']['settings'];

  return $element;
}

/**
 * Returns HTML for an audio field widget.
 *
 * @param $variables
 *   An associative array containing:
 *   - element: A render element representing the audio field widget.
 *
 * @ingroup themeable
 *//*
function theme_audiofield_widget($variables) {
  global $user;
  $element = $variables['element'];
  dcp( $element );
  //Don't render player if there is no file.
  if (!$element['#item']['filepath']) {
    return '';
  }

  $info = pathinfo($element['#item']['filename']);
  $op = $info['extension'];
  $audiofile=file_create_url($element['#item']['filepath']);
  $output= audiofield_get_player($audiofile, $op);
  
  //If user is file owner check for download permission and display download link
  if ($user->uid==$element['#item']['uid'] && user_access('download own audio files')) {
    $output .='<div class="audio-download"><b>Download:</b>' . theme('filefield_file', $element['#item']) . '</div>';
  }
  elseif (user_access('download all audio files')) {
  //If user has permission to download all audio files, then display download link
    $output .='<div class="audio-download"><b>Download:</b>' . theme('filefield_file', $element['#item']) . '</div>';
  }

  return $output;
}
*/
/**
 * Implements hook_field_formatter_info().
 */
function audiofield_field_formatter_info() {
  $formatters = array(
    'audiofield_embedded' => array(
      'label' => t('Audio'),
      'field types' => array('file'),
      'description' => t('Displays audio embedded in a web page.'),
    ),
  );

  return $formatters;
}


/**
 * Implements hook_field_formatter_settings_summary().
 TODO: Can add various settings for formatter
 *//*
function audiofield_field_formatter_settings_summary($field, $instance, $view_mode) {
  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];

  $summary = array();

  $image_styles = image_style_options(FALSE);
  // Unset possible 'No defined styles' option.
  unset($image_styles['']);
  // Styles could be lost because of enabled/disabled modules that defines
  // their styles in code.
  if (isset($settings['image_style']) && isset($image_styles[$settings['image_style']])) {
    $summary[] = t('Image style: @style', array('@style' => $image_styles[$settings['image_style']]));
  }
  else {
    $summary[] = t('Original image');
  }

  $link_types = array(
    'content' => t('Linked to content'),
    'file' => t('Linked to file'),
  );
  // Display this setting only if image is linked.
  if (isset($settings['image_link']) && isset($link_types[$settings['image_link']])) {
    $summary[] = $link_types[$settings['image_link']];
  }

  return implode('<br />', $summary);
}
*/

/**
 * Implements hook_field_formatter_view().TODO
 */
function audiofield_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  $elements = array();

  foreach ($items as $delta => $item) {
    	$elements[] =  array( '#markup' => theme( 'audiofield_formatter_audiofield_embedded', array( 'file' => (object)$item )));    	
  }

  return $elements;
}

/**
 * Returns HTML for an image field formatter.
 *TODO
 * @param $variables
 *   An associative array containing:
 *   - item: An array of image data.
 *   - image_style: An optional image style.
 *   - path: An array containing the link 'path' and link 'options'.
 *
 * @ingroup themeable
 *//*
function theme_audiofield_formatter($variables) {
  $item = $variables['item'];
  $image = array(
    'path' => $item['uri'],
    'alt' => $item['alt'],
  );
  // Do not output an empty 'title' attribute.
  if (drupal_strlen($item['title']) > 0) {
    $image['title'] = $item['title'];
  }

  if ($variables['image_style']) {
    $image['style_name'] = $variables['image_style'];
    $output = theme('image_style', $image);
  }
  else {
    $output = theme('image', $image);
  }

  if ($variables['path']) {
    $path = $variables['path']['path'];
    $options = $variables['path']['options'];
    // When displaying an image inside a link, the html option must be TRUE.
    $options['html'] = TRUE;
    $output = l($output, $path, $options);
  }

  return $output;
}*/
