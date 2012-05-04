<?php
/**
 * @file
 * Administrative functions to configure DrupalChat.
 */

/**
 * Callback for admin/settings/drupalchat.
 */
function drupalchat_settings_form($form, &$form_state) {
  drupal_add_js(drupal_get_path('module', 'drupalchat') . '/drupalchat.admin.js');
  drupalchat_yui_path();
  if (!variable_get('drupalchat_yui_path', NULL)) {
    drupal_set_message(t("You need to download the latest stable release of the !library and extract the entire folder into sites/all/libraries.", array('!library' => l(t('YUI library'), 'http://developer.yahoo.com/yui/3/', array('attributes' => array('target' => '_blank'))))));
  }
  $seconds = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7=>7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => 19, 20 => 20);
  $themes = _drupalchat_load_themes(drupal_get_path('module', 'drupalchat') . '/themes', 'css');
  $polling_method = variable_get('drupalchat_polling_method', DRUPALCHAT_LONGPOLL);

  if ($polling_method == DRUPALCHAT_LONGPOLL && ini_get('max_execution_time') < 30) {
    drupal_set_message(t('For DrupalChat Long Polling to be effective, please set max_execution_time to above 30 in your server php.ini file.'), 'warning');
  }

  $form = array();
  $form['libraries'] = array(
    '#type' => 'fieldset',
    '#title' => t('Library info'),
    '#collapsible' => TRUE,
  );
  $form['libraries']['yui'] = array(
    '#type' => 'fieldset',
    '#title' => t('YUI'),
    '#collapsible' => TRUE,
  );
  $form['libraries']['yui']['drupalchat_yui_path'] = array(
    '#type' => 'textfield',
    '#title' => t('YUI library'),
    '#description' => t("You need to download the latest stable release of the !library and extract the entire folder into sites/all/libraries. The path will be automatically discovered; you may override the directory path here if you wish to use another version; do not add slashes before or after the path.", array('!library' => l(t('YUI library'), 'http://developer.yahoo.com/yui/3/', array('attributes' => array('target' => '_blank'))))),
    '#default_value' => variable_get('drupalchat_yui_path', NULL),
  );
  $form['drupalchat_theme'] = array(
    '#type' => 'select',
    '#title' => t('Theme'),
    '#description' => t('All themes from inside the <em>themes</em> folder will be displayed here.'),
    '#options' => $themes,
    '#default_value' => variable_get('drupalchat_theme', 'light'),
  );
  $form['drupalchat_notification_sound'] = array(
    '#type' => 'select',
    '#title' => t('Notification Sound'),
    '#description' => t('Select whether to play notification sound when a new message is received.'),
    '#options' => array(1 => 'Yes', 2 => 'No'),
    '#default_value' => variable_get('drupalchat_notification_sound', 1),
  );
  $form['drupalchat_polling_method'] = array(
		'#type' => 'radios',
		'#title' => t('Choose Polling Method'),
		'#default_value' => variable_get('drupalchat_polling_method', DRUPALCHAT_LONGPOLL),
		'#options' => array(DRUPALCHAT_AJAX => t('Normal AJAX'), DRUPALCHAT_LONGPOLL => t('Long Polling'), DRUPALCHAT_NODEJS => t('Node.js')),
		'#description' => t('Decide the server backend for Drupal Chat.'),
  );
  $form['drupalchat_user_latency'] = array(
    '#type' => 'select',
    '#title' => t('Chat List Latency'),
    '#description' => t('The delay, in seconds, after which the user will be shown offline in the chat list(i.e. removed from the chat list) from the time he/she goes offline. Increase this value if you find the chat list is unstable and keeps on changing a lot (for example - when a user navigates from one page to another he/she goes offline and then comes back online again). Decrease it if you find that the users are shown in the chat list for too long after they have left your website.'),
  	'#options' => $seconds,
    '#default_value' => variable_get('drupalchat_user_latency', 2),
  );
  $form['drupalchat_refresh_rate'] = array(
    '#type' => 'select',
    '#title' => t('Normal AJAX Refesh Rate'),
    '#description' => t('The time interval, in seconds, after which DrupalChat checks for new messages.'),
  	'#options' => $seconds,
    '#default_value' => variable_get('drupalchat_refresh_rate', 2),
		'#disabled' => variable_get('drupalchat_polling_method', DRUPALCHAT_LONGPOLL) == DRUPALCHAT_LONGPOLL ? TRUE : FALSE,
  );
  $form['drupalchat_send_rate'] = array(
    '#type' => 'select',
    '#title' => t('Normal AJAX Send message delay'),
    '#description' => t('The delay, in seconds, to send a message to the server.'),
  	'#options' => $seconds,
    '#default_value' => variable_get('drupalchat_send_rate', 2),
  	'#disabled' => variable_get('drupalchat_polling_method', DRUPALCHAT_LONGPOLL) == DRUPALCHAT_LONGPOLL ? TRUE : FALSE,
  );
  $form['drupalchat_rel'] = array(
    '#type' => 'radios',
    '#title' => t('Relationship method'),
    '#default_value' => variable_get('drupalchat_rel', DRUPALCHAT_REL_AUTH),
    '#options' => array(
      DRUPALCHAT_REL_AUTH => t('All authenticated users'),
    ),
    '#description' => t('This determines the method for creating the chat buddylist.'),
  );
  if (module_exists('user_relationships')) {
    $form['drupalchat_rel']['#options'][DRUPALCHAT_REL_UR] = t('User Relationship module');
  }
  if (module_exists('flag_friend')) {
    $form['drupalchat_rel']['#options'][DRUPALCHAT_REL_FF] = t('Flag Friend module');
  }
  $form['drupalchat_ur_name'] = array(
    '#type' => 'textfield',
    '#title' => t('User Relationships Role Names to integrate with'),
    '#description' => t('The singular form of User Relationships Role Names (e.g. buddy, friend, coworker, spouse) separated by comma.'),
    '#default_value' => variable_get('drupalchat_ur_name', NULL),
	'#autocomplete_path' => 'drupalchat/ur-autocomplete',
  );

  return system_settings_form($form);
}

/**
 * @todo Please document this function.
 * @see http://drupal.org/node/1354
 */
function drupalchat_settings_form_validate($form, &$form_state) {
  if ($form_state['values']['drupalchat_rel'] == DRUPALCHAT_REL_UR) {
  /*if (!$form['drupalchat_ur_name']['#value']) {
	  form_set_error('drupalchat_ur_name', t('You must provide the user relationship name in the Drupal UR Settings section.));
    }*/
    if ($form_state['values']['drupalchat_ur_name']) {
	  $array = drupal_explode_tags($form_state['values']['drupalchat_ur_name']);
	  $error = array();
	  foreach($array as $key) {
	    if(!db_query("SELECT COUNT(*) FROM {user_relationship_types} WHERE name = :name", array(':name' => $key))->fetchField())
          $error[] = $key;		
      }
	  if(!empty($error))
	    form_set_error('drupalchat_ur_name', t('User Relationship type %type was not found.', array('%type' => drupal_implode_tags($error))));	  
    }
  }
}

function _drupalchat_load_themes($outerDir, $x) {
  $dirs = array_diff(scandir($outerDir), array('.', '..'));

  $dir_array = array();
  foreach ($dirs as $d) {
    if (is_dir($outerDir . "/" . $d)) {
      if ($innerDir = _drupalchat_load_themes($outerDir . '/' . $d, $x)) {
        $dir_array[$d] = $innerDir;
      }
    }
    elseif (($x) ? preg_match('/' . $x . '$/', $d) : 1) {
      $name = _drupalchat_remove_extension($d);
      $dir_array[$name] = $name;
    }
  }
  return $dir_array;
}

function _drupalchat_remove_extension($strName) {
  $ext = strrchr($strName, '.');

  if ($ext !== false) {
    $strName = substr($strName, 0, -strlen($ext));
  }
  return $strName;
}
function _drupalchat_autodiscover_path($match, $filename) {
  $path = '';
  //echo $match;

  $files = drupal_system_listing($match, 'libraries', 'basename', 0);

  /*echo '<pre>'; print_r($files); echo '</pre>';
  die;*/
  foreach ($files as $key => $value){
      if (preg_match("/$filename/i", $key)) {
          /*$path = dirname($value->filename);
          echo $path;
          die;*/
          $path = dirname($value->uri);
      }
  }

  /*echo $filename;
  echo '<pre>'; print_r($files); echo '</pre>';
  if (isset($files[$filename])) {
    echo 'ici';
    $path = dirname($files[$filename]->filename);
      
  }*/
  return $path;
}
