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
 * Unit tests for patch hide social options.
 *
 * @package   mod_hvp
 * @copyright 2018 Andreas Wagner, ISB Bayern
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for patch hide social options.
 *
 * @package   mod_hvp
 * @copyright 2018 Andreas Wagner, ISB Bayern
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group     mod_hvp
 * @group     mebis
 */
class mod_hvp_patch_social_options_test extends \advanced_testcase {

    /**
     * Add suitable library for the test.
     */
    private function add_library() {
        global $DB;

        $lib = (object) [
                'machine_name' => 'H5P.CoursePresentation',
                'title' => 'Course Presentation',
                'major_version' => 1,
                'minor_version' => 19,
                'patch_version' => 3,
                'runnable' => 1,
                'fullscreen' => 1,
                'embed_types' => 'iframe',
                'preloaded_js' => 'dist/h5p-course-presentation.js',
                'preloaded_css' => 'dist/h5p-course-presentation.css',
                'drop_library_css' => '',
                'semantics' => '[]',
                'restricted' => 0,
                'tutorial_url' => null,
                'has_icon' => 1
        ];
        $DB->insert_record('hvp_libraries', $lib);
    }

    /**
     * Test the removal of social options.
     */
    public function test_remove_social_options() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/hvp/lib.php');

        $this->resetAfterTest();
        $this->setAdminUser();

        // Add library for the Course Presentation.
        $this->add_library();

        // Include simulation of formdata.
        $formdata = new \stdClass();
        include($CFG->dirroot . '/mod/hvp/tests/fixtures/formdata.php');

        $course1 = $this->getDataGenerator()->create_course(['fullname' => 'SuperMath']);
        $formdata->course = $course1->id;

        // Ensure the options are set to true.
        $params = json_decode($formdata->params);
        foreach (\mod_hvp\patch\patch_social_options::$options as $shareoption) {
            $this->assertTrue($params->override->social->{$shareoption});
        }

        hvp_add_instance($formdata);

        $hvps = $DB->get_records('hvp');
        $this->assertEquals(1, count($hvps));

        $hvp = array_shift($hvps);
        $content = json_decode($hvp->json_content);
        foreach (\mod_hvp\patch\patch_social_options::$options as $shareoption) {
            $this->assertEquals(0, $content->override->social->{$shareoption});
        }
    }

}
