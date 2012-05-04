<?php

/**
 * @file
 * ZipCart admin pages.
 */

/**
 * Settings form
 */
function zipcart_settings_form() {
  $methods = _zipcart_get_available_methods();
  foreach ( $methods as $k => $v ) {
    $options[$k] = $v['title'];
  }
  $form['zipcart_zip_method'] = array(
    '#title' => t('Zip Method'),
    '#type' => 'select',
    '#default_value' => variable_get('zipcart_zip_method', ''),
    '#options' => $options,
    '#description' => t('Method used to generate bundle files.'),
  );

/*
  $items['zip_builtin'] = array(
    '#type' => 'fieldset',
    '#title' => 'PHP Zip settings',
  );

  $items['zip_pecl'] = array(
    '#type' => 'fieldset',
    '#title' => 'PECL Zip settings',
  );

  $items['zip_external'] = array(
    '#type' => 'fieldset',
    '#title' => 'External Zip binary settings',
  );
*/

  return system_settings_form($form);
}

