<?php
/**
 * @file
 * Helper functions for the Envolve Chat module.
 */

/**
 * Wrapper that creates the Envolve HTML to be included for an anonymous user.
 *
 * @param $api_key
 *   A string containing the site's Envolve API key.
 * @param $use_ssl
 *   A boolean to determine whether to use SSL.
 *
 * @return
 *   A string containing the HTML to include in the host page to activate
 *   Envolve.
 */
function _envolve_chat_get_code_for_anon_user($api_key, $use_ssl) {
  return _envolve_chat_get_html($api_key, _envolve_chat_get_logout_command($api_key), $use_ssl);
}

/**
 * Wrapper that creates the Envolve HTML to be included for a logged-in user.
 *
 * @param $api_key
 *   A string containing the site's Envolve API key.
 * @param $first_name
 *   A string containing the first name or username for the user. Required.
 * @param $last_name
 *   A string containing the last name of the user. Pass NULL if unused.
 * @param $picture
 *   A string containing an absolute URL to the location of the user's profile
 *   picture. Pass NULL if unused.
 * @param $is_admin
 *   A boolean determining whether this user an administrator.
 * @param $use_ssl
 *   A boolean determining whether the outputted HTML should use SSL.
 *
 * @return
 *   A string containing the HTML to include in the host page to activate
 *   Envolve.
 */
function _envolve_chat_get_html_for_reg_user($api_key, $first_name, $last_name, $picture, $is_admin, $use_ssl) {
  $command = _envolve_chat_get_login_command($api_key, $first_name, $last_name, $picture, $is_admin);
  return _envolve_chat_get_html($api_key, $command, $use_ssl);
}

/**
 * Creates a login command string to be passed to Envolve to log a user in.
 *
 * Creates a login command to pass to Envolve to programmatically log a user in.
 * Used by _envolve_chat_get_html_for_reg_user().
 *
 * @param $api_key
 *   A string containing the site's Envolve API key.
 * @param $first_name
 *   A string containing the first name or username for the user. Required.
 * @param $last_name
 *   A string containing the last name of the user. Pass NULL if unused.
 * @param $picture
 *   A string containing an absolute URL to the location of the user's profile
 *   picture. Pass NULL if unused.
 * @param $is_admin
 *   A boolean determining whether this user an administrator.
 *
 * @return
 *   A command string to log a user in with all specified options.
 */
function _envolve_chat_get_login_command($api_key, $first_name, $last_name, $picture, $is_admin) {
  // Specify the API version number, function, and first name (required).
  $command_string =  "v=0.2,c=login,fn=" . base64_encode($first_name);
  if ($first_name == NULL) {
    error_log("EnvolveAPI: You must provide a first name to log in to Envolve");
    return;
  }
  // Adds a last name if it exists.
  if ($last_name != NULL) {
    $command_string = $command_string . ",ln=" . base64_encode($last_name);
  }
  // Adds a link to a picture if it exists.
  if ($picture != NULL) {
    $command_string = $command_string . ",pic=" . base64_encode($picture);
  }
  // Adds a command to make the user an admin if specified.
  if ($is_admin) {
    $command_string = $command_string . ",admin=t";
  }
  return _envolve_chat_sign_command($api_key, $command_string);
}

/**
 * Creates a logout command to tell Envolve to replace the current user.
 *
 * Creates a command so Envolve logs out the current user and replaces them with
 * an anonymous user with a generated user name.
 *
 * @param $api_key
 *   A string containing the site's Envolve API Key.
 *
 * @return
 *   A command string to pass to replace the existing user with an anonymous
 *   one.
 */
function _envolve_chat_get_logout_command($api_key) {
  return _envolve_chat_sign_command($api_key, 'c=logout');
}

/**
 * Signs the command string by hashing it with part of the API key.
 *
 * Prepends a hashed version of the data in combination with the current time
 * using the secret API key as the hash key.
 *
 * @param $api_key
 *   A string containing the site's Envolve API key.
 * @param $command
 *   The command string to be passed to the Envolve chat API.
 *
 * @return
 *   The original command string and the hashed version for authenticity checks.
 */
function _envolve_chat_sign_command($api_key, $command) {
  // Validates the API key and split it into its component parts.
  $api_key_pieces = preg_split('/-/', $api_key);
  if ((count($api_key_pieces) != 2) || (((int) $api_key_pieces[0]) == 0) ) {
    error_log("EnvolveAPI: Invalid API Key");
    return;
  }
  $secret_key = $api_key_pieces[1];

  // Appends the current time in ms to the command.
  $data =  time() . '000' . ';' . $command;
  // Hashes the data using the API key second part as the hash key.
  $hash = hash_hmac('sha1', utf8_encode($data), $secret_key, FALSE);
  // Prepends the hashed version of the data to the command string.
  return $hash . ";" . $data;
}

/**
 * Pieces together the HTML given the specific settings.
 *
 * Creates the HTML to add Envolve chat to the footer of a page given the
 * specific settings of the module.
 *
 * @param $api_key
 *   A string containing the site's Envolve API key.
 * @param $command
 *   The command string outputted from _envolve_chat_sign_command().
 * @param $use_ssl
 *   A boolean determining whether Envolve should use SSL.
 *
 * @return
 *   A string containing the actual HTML code to be appended to the footer of
 *   every page that should contain an Envolve chat bar.
 */
function _envolve_chat_get_html($api_key, $command, $use_ssl) {
  // Generates the URL of the chat JavaScript including SSL if applicable.
  $envolve_js_root = ($use_ssl ? "https" : "http" ) . "://d.envolve.com/env.nocache.js";

  // Validates the API key and split it into its component parts.
  $api_key_pieces = preg_split('/-/', $api_key);
  if ((count($api_key_pieces) != 2) || (((int) $api_key_pieces[0]) == 0) ) {
    error_log("EnvolveAPI: Invalid API Key");
    return;
  }
  $site_ID = (int) $api_key_pieces[0];

  // Creates the markup including environment variables for the JavaScript.
  $ret_val = '<script type="text/javascript">' . "\n" . 'envoSn=' . $site_ID . ';';
  if ($use_ssl) {
    $ret_val .= "\n" . 'envoOptions={enableSSL: true};';
  }
  if ($command != NULL) {
    $ret_val = $ret_val . "\n" . 'env_commandString="' . $command . '";' . "\n";
  }
  $ret_val = $ret_val . '</script>';
  $ret_val = $ret_val . '<script type="text/javascript" src="' . $envolve_js_root . '"></script>';
  return $ret_val;
}
