<?php
// $Id$

/**
 * @file
 * Contains administrative pages for Bookmark Organizers.
 */

/**
 * Bookmark Organizer administration page.
 */
function bookmarkorganizer_admin_form($form, $form_state) {

  $flags = flag_get_flags();
  $flag_types = array(-1 => '--- Select ---');
  foreach ($flags as $key => $flag) {
    $flag_types[$key] = $flag->title;
  }
  $form['bookmarkorganizer_flag_name'] = array(
    '#type' => 'select',
    '#options' => $flag_types,
    '#title' => t("Flag type"),
    '#description' => t("Select a flag type to use for bookmarks."),
    '#default_value' => variable_get('bookmarkorganizer_flag_name'),
  );

  $form['bookmarkorganizer_enable_folders'] = array(
    '#type' => 'checkbox',
    '#title' => t("Enable folders"),
    '#description' => t("Grant user to add folders and place bookmarks into them."),
    '#default_value' => variable_get('bookmarkorganizer_enable_folders'),
  );

  $form['bookmarkorganizer_enable_preview_pane'] = array(
    '#type' => 'checkbox',
    '#title' => t("Enable preview pane"),
    '#description' => t("Display previews about bookmarks and contents of folders on the Bookmark Organizer page."),
    '#default_value' => variable_get('bookmarkorganizer_enable_preview_pane'),
  );

  $form['bookmarkorganizer_remove_sidebars'] = array(
    '#type' => 'checkbox',
    '#title' => t("Remove sidebars"),
    '#description' => t("Do not display sidebar regions on Bookmark Organizer page. Bookmark Organizer needs horizontal space to display previews beside bookmarks menu. Preview pane is shown under the menu if this checkbox is not enabled"),
    '#default_value' => variable_get('bookmarkorganizer_remove_sidebars'),
  );

  $entity_info = entity_get_info('node');
  $options = array();
  foreach ($entity_info['view modes'] as $key => $mode) {
    $options[$key] = $mode['label'];
  }
  $form['bookmarkorganizer_preview_mode'] = array(
    '#type' => 'select',
    '#options' => $options,
    '#title' => t("Display mode of bookmarked nodes"),
    '#description' => t("Select a content type display mode that is used on the preview pane."),
    '#default_value' => variable_get('bookmarkorganizer_preview_mode'),
  );

  if (module_exists('views')) {
    $views = views_get_all_views();
    $options = array();
    foreach ($views as $key => $view) {
      $options[$key] = $key;
    }
  }
  else {
    $options = array();
  }
  $form['bookmarkorganizer_default_preview_list'] = array(
    '#type' => 'select',
    '#options' => $options,
    '#title' => t("Display contents of a folder as a list"),
    '#description' => t("Select a view that is used on the preview pane to display contents of a folder. Requires <a href=\"http://drupal.org/project/views\">Views</a> module."),
    '#default_value' => variable_get('bookmarkorganizer_default_preview_list'),
  );

  $form['bookmarkorganizer_enable_folder_publication'] = array(
    '#type' => 'checkbox',
    '#title' => t("Folder publication"),
    '#description' => t("Grant user to publish a folder to other users."),
    '#default_value' => variable_get('bookmarkorganizer_enable_folder_publication'),
  );

  return system_settings_form($form);

}
