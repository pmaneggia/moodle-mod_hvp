<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * mod_hvp data generator.
 *
 * @package    mod_hvp
 * @category   test
 * @copyright  2026 ISB
 * @author     Paola Maneggia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * mod_hvp data generator class.
 *
 * The full H5P authoring pipeline (uploading a package or using the JavaScript
 * editor) cannot be exercised from PHPUnit, so this generator installs a minimal
 * "runnable" library with empty semantics and creates content directly through
 * the normal {@see hvp_add_instance()} path. This produces a real activity
 * instance (course module, context and grade item) that is good enough for
 * server-side unit tests, without requiring any real H5P library or file.
 *
 * @package    mod_hvp
 * @category   test
 * @copyright  2026 ISB
 * @author     Paola Maneggia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_hvp_generator extends testing_module_generator {

    /** @var int Keeps track of how many content user data rows have been created. */
    protected $contentuserdatacount = 0;

    /** @var int Keeps track of how many xAPI results have been created. */
    protected $xapiresultcount = 0;

    /**
     * To be called from data reset code only, do not use in tests.
     *
     * @return void
     */
    public function reset() {
        $this->contentuserdatacount = 0;
        $this->xapiresultcount = 0;
        parent::reset();
    }

    /**
     * Creates a new hvp module instance.
     *
     * A minimal fake library is installed automatically so that the content can
     * be stored without a real H5P package or the JavaScript editor. The library
     * to use can be overridden with the 'h5plibrary' record field (a library
     * string such as 'H5P.Blanks 1.0'); if it does not exist yet it will be
     * created on the fly.
     *
     * @param array|stdClass $record data for module being generated. Requires 'course' key.
     * @param null|array $options general options for course module.
     * @return stdClass record from the hvp table with the additional cmid field.
     */
    public function create_instance($record = null, ?array $options = null) {
        global $PAGE;

        $record = (object)(array)$record;

        // Ensure a library exists and default the content to use it.
        if (empty($record->h5plibrary)) {
            $library = $this->create_library();
            $record->h5plibrary = "{$library->machine_name} {$library->major_version}.{$library->minor_version}";
        } else {
            $this->ensure_library_from_string($record->h5plibrary);
        }

        // Create (not upload) the content, bypassing the JavaScript editor.
        if (!isset($record->h5paction)) {
            $record->h5paction = 'create';
        }
        // Empty parameters are enough because the fake library has no semantics.
        if (!isset($record->params)) {
            $record->params = '{}';
        }
        // The title is normally provided by the editor metadata; mirror that here.
        if (!isset($record->metadata)) {
            $record->metadata = new stdClass();
        }
        if (empty($record->metadata->title)) {
            $record->metadata->title = $record->name ?? get_string('pluginname', 'mod_hvp');
        }
        if (empty($record->name)) {
            $record->name = $record->metadata->title;
        }

        // Saving hvp content always initialises the theme and output because the H5P
        // framework loads the module renderer while altering library semantics (see
        // \mod_hvp\framework::alterLibrarySemantics()). Initialise it up front so the
        // module generator does not flag this expected side effect as an error.
        $PAGE->initialise_theme_and_output();

        return parent::create_instance($record, (array)$options);
    }

    /**
     * Installs a minimal runnable H5P library with empty semantics.
     *
     * The empty semantics ('[]') make the editor parameter processing a no-op,
     * so no real library files are required.
     *
     * @param array|stdClass $record optional overrides for the library record.
     * @return stdClass the created hvp_libraries record.
     */
    public function create_library($record = null): stdClass {
        global $DB;

        $record = (array)$record + [
            'machine_name' => 'H5P.FakeLibrary',
            'title' => 'Fake library',
            'major_version' => 1,
            'minor_version' => 0,
            'patch_version' => 0,
            'runnable' => 1,
            'fullscreen' => 0,
            'embed_types' => 'div',
            'preloaded_js' => '',
            'preloaded_css' => '',
            'drop_library_css' => '',
            'semantics' => '[]',
            'restricted' => 0,
            'has_icon' => 0,
        ];

        // Reuse an existing matching library if one is already installed.
        $existing = $DB->get_record('hvp_libraries', [
            'machine_name' => $record['machine_name'],
            'major_version' => $record['major_version'],
            'minor_version' => $record['minor_version'],
        ]);
        if ($existing) {
            return $existing;
        }

        $record['id'] = $DB->insert_record('hvp_libraries', (object)$record);

        return (object)$record;
    }

    /**
     * Ensures the library described by a library string is installed.
     *
     * @param string $librarystring library string such as 'H5P.Blanks 1.0'.
     * @return void
     */
    protected function ensure_library_from_string(string $librarystring): void {
        $library = H5PCore::libraryFromString($librarystring);
        if (!$library) {
            throw new coding_exception("Invalid H5P library string: {$librarystring}");
        }
        $this->create_library([
            'machine_name' => $library['machineName'],
            'title' => $library['machineName'],
            'major_version' => $library['majorVersion'],
            'minor_version' => $library['minorVersion'],
        ]);
    }

    /**
     * Creates a content user data row for an hvp instance.
     *
     * This represents the state a user has stored for an activity and is one of
     * the things removed by the course reset / instance deletion.
     *
     * @param stdClass $instance instance returned from {@see create_instance()}.
     * @param array $record overrides for the hvp_content_user_data record.
     * @return stdClass the created hvp_content_user_data record.
     */
    public function create_content_user_data(stdClass $instance, array $record = []): stdClass {
        global $DB, $USER;

        $this->contentuserdatacount++;
        $record = $record + [
            'user_id' => $USER->id,
            'hvp_id' => $instance->id,
            'sub_content_id' => 0,
            'data_id' => 'state',
            'data' => 'User data '.$this->contentuserdatacount,
            'preloaded' => 1,
            'delete_on_content_change' => 0,
        ];

        $record['id'] = $DB->insert_record('hvp_content_user_data', (object)$record);

        return (object)$record;
    }

    /**
     * Creates an xAPI result row for an hvp instance.
     *
     * @param stdClass $instance instance returned from {@see create_instance()}.
     * @param array $record overrides for the hvp_xapi_results record.
     * @return stdClass the created hvp_xapi_results record.
     */
    public function create_xapi_result(stdClass $instance, array $record = []): stdClass {
        global $DB, $USER;

        $this->xapiresultcount++;
        $record = $record + [
            'content_id' => $instance->id,
            'user_id' => $USER->id,
            'parent_id' => null,
            'interaction_type' => 'compound',
            'description' => 'Result '.$this->xapiresultcount,
            'correct_responses_pattern' => '',
            'response' => '',
            'additionals' => '',
            'raw_score' => 1,
            'max_score' => 1,
        ];

        $record['id'] = $DB->insert_record('hvp_xapi_results', (object)$record);

        return (object)$record;
    }
}
