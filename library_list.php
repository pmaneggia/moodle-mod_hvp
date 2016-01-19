<?php
require_once("../../config.php");
require_once($CFG->libdir.'/adminlib.php');
require_once("locallib.php");

// No guest autologin.
require_login(0, false);

$pageurl = new moodle_url('/mod/hvp/toolproxies.php');
$PAGE->set_url($pageurl);

// Inform moodle which menu entry currently is active!
admin_externalpage_setup('h5plibraries');

$PAGE->set_title("{$SITE->shortname}: " . get_string('libraries', 'hvp'));

// Create upload libraries form
$uploadform = new \mod_hvp\upload_libraries_form();
if ($formdata = $uploadform->get_data()) {
    // Handle submitted valid form
    $h5pStorage = \mod_hvp\framework::instance('storage');
    $h5pStorage->savePackage(NULL, NULL, TRUE);
}

$core = \mod_hvp\framework::instance();
$numNotFiltered = $core->h5pF->getNumNotFiltered();
$libraries = $core->h5pF->loadLibraries();

// Add settings for each library
$settings = array();
$i = 0;
foreach ($libraries as $versions) {
  foreach ($versions as $library) {
    $usage = $core->h5pF->getLibraryUsage($library->id, $numNotFiltered ? TRUE : FALSE);
    if ($library->runnable) {
      $upgrades = $core->getUpgrades($library, $versions);
      $upgradeUrl = empty($upgrades) ? FALSE : (new moodle_url('/mod/hvp/upgrade_content_page.php', array(
        'library_id' => $library->id
      )))->out(false);

      $restricted = (isset($library->restricted) && $library->restricted == 1 ? TRUE : FALSE);
      $restricted_url = (new moodle_url('/mod/hvp/ajax.php', array(
        'action' => 'restrict_library',
        'token' => hvp_get_token('library_' . $library->id),
        'restrict' => ($restricted ? 0 : 1),
        'library_id' => $library->id
      )))->out(false);
    }
    else {
      $upgradeUrl = NULL;
      $restricted = NULL;
      $restricted_url = NULL;
    }

    $settings['libraryList']['listData'][] = array(
        'title' => $library->title . ' (' . H5PCore::libraryVersion($library) . ')',
        'restricted' => $restricted,
        'restrictedUrl' => $restricted_url,
        'numContent' => $core->h5pF->getNumContent($library->id),
        'numContentDependencies' => $usage['content'] === -1 ? '' : $usage['content'],
        'numLibraryDependencies' => $usage['libraries'],
        'upgradeUrl' => $upgradeUrl,
        'detailsUrl' => NULL, // Not implemented in Moodle
        'deleteUrl' => NULL // Not implemented in Moodle
    );

    $i++;
  }
}

// All translations are made server side
$settings['libraryList']['listHeaders'] = array(
    get_string('librarylisttitle', 'hvp'),
    get_string('librarylistrestricted', 'hvp'),
    get_string('librarylistinstances', 'hvp'),
    get_string('librarylistinstancedependencies', 'hvp'),
    get_string('librarylistlibrarydependencies', 'hvp'),
    get_string('librarylistactions', 'hvp')
);

//if ($numNotFiltered) {
    // Not implemented in Moodle
    // $settings['libraryList']['notCached'] = h5p_get_not_cached_settings($numNotFiltered);
//}

// Add js
$lib_url = $CFG->httpswwwroot . '/mod/hvp/library/';

hvp_admin_add_generic_css_and_js($PAGE, $lib_url, $settings);
$PAGE->requires->js(new moodle_url($lib_url . 'js/h5p-library-list.js' . hvp_get_cache_buster()), true);

// RENDER PAGE OUTPUT

echo $OUTPUT->header();

// Print any messages
\mod_hvp\framework::printMessages('info', \mod_hvp\framework::messages('info'));
\mod_hvp\framework::printMessages('error', \mod_hvp\framework::messages('error'));

// Page Header
echo '<h2>' . get_string('libraries', 'hvp') . '</h2>';

// Upload Form
echo '<h3 class="h5p-admin-header">' . get_string('uploadlibraries', 'hvp') . '</h3>';
$uploadform->display();

// Installed Libraries List
echo '<h3 class="h5p-admin-header">' . get_string('installedlibraries', 'hvp')  . '</h3>';
echo '<div id="h5p-admin-container"></div>';

echo $OUTPUT->footer();
