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

namespace mod_hvp;

/**
 * Tests for the HVP module
 *
 * @package   mod_hvp
 * @category  test
 * @copyright 2026 ISB
 * @author    Paola Maneggia
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lib_test extends \advanced_testcase {
    /**
     * Test create and delete module
     *
     * @covers ::hvp_add_instance
     * @covers ::hvp_delete_instance
     * @return void
     */
    public function test_create_delete_module(): void {
        global $DB;
        $this->resetAfterTest();

        // Disable recycle bin so we are testing module deletion and not backup.
        set_config('coursebinenable', 0, 'tool_recyclebin');

        // Create an instance of a module.
        $course = $this->getDataGenerator()->create_course();
        $mod = $this->getDataGenerator()->create_module(
            'hvp',
            ['course' => $course->id]
        );
        $cm = get_coursemodule_from_instance('hvp', $mod->id);

        // Assert it was created.
        $this->assertNotEmpty(\context_module::instance($mod->cmid));
        $this->assertEquals($mod->id, $cm->instance);
        $this->assertEquals('hvp', $cm->modname);
        $this->assertEquals(1, $DB->count_records('hvp', ['id' => $mod->id]));
        $this->assertEquals(1, $DB->count_records('course_modules', ['id' => $cm->id]));

        // Delete module.
        course_delete_module($cm->id);
        $this->assertEquals(0, $DB->count_records('hvp', ['id' => $mod->id]));
        $this->assertEquals(0, $DB->count_records('course_modules', ['id' => $cm->id]));
    }

    /**
     * Test module backup and restore by duplicating it
     *
     * @covers \backup_hvp_activity_structure_step
     * @covers \restore_hvp_activity_structure_step
     * @return void
     */
    public function test_backup_restore(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        // Createa a module.
        $course = $this->getDataGenerator()->create_course();
        $mod = $this->getDataGenerator()->create_module(
            'hvp',
            ['course' => $course->id, 'name' => 'My test module']
        );
        $cm = get_coursemodule_from_instance('hvp', $mod->id);

        // Call duplicate_module - it will backup and restore this module.
        $cmnew = duplicate_module($course, $cm);

        $this->assertNotNull($cmnew);
        $this->assertGreaterThan($cm->id, $cmnew->id);
        $this->assertGreaterThan($mod->id, $cmnew->instance);
        $this->assertEquals('hvp', $cmnew->modname);

        $name = $DB->get_field('hvp', 'name', ['id' => $cmnew->instance]);
        $this->assertEquals('My test module (copy)', $name);
        // TODO: check other fields and related tables.
    }

    /**
     * Test that resetting user data removes attempts, xAPI results and grades.
     *
     * @covers ::hvp_reset_userdata
     * @return void
     */
    public function test_reset_userdata(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        /** @var \mod_hvp_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Create two instances and populate user data for both.
        $hvp1 = $generator->create_instance(['course' => $course->id]);
        $hvp2 = $generator->create_instance(['course' => $course->id]);

        foreach ([$hvp1, $hvp2] as $hvp) {
            $generator->create_content_user_data($hvp, ['user_id' => $student->id]);
            $generator->create_xapi_result($hvp, ['user_id' => $student->id]);
        }

        // Confirm the data is present before the reset.
        $this->assertEquals(2, $DB->count_records('hvp_content_user_data'));
        $this->assertEquals(2, $DB->count_records('hvp_xapi_results'));

        // Perform the reset.
        $data = (object)['courseid' => $course->id, 'reset_hvp_attempts' => 1];
        $status = hvp_reset_userdata($data);

        // All user attempt data for the course must be gone.
        $this->assertEquals(0, $DB->count_records('hvp_content_user_data'));
        $this->assertEquals(0, $DB->count_records('hvp_xapi_results'));

        // A single status entry must be returned for the reset item.
        $this->assertCount(1, $status);
        $this->assertFalse($status[0]['error']);
    }

    /**
     * Test that resetting user data does nothing when the option is not selected.
     *
     * @covers ::hvp_reset_userdata
     * @return void
     */
    public function test_reset_userdata_not_selected(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        /** @var \mod_hvp_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $hvp = $generator->create_instance(['course' => $course->id]);
        $generator->create_content_user_data($hvp, ['user_id' => $student->id]);
        $generator->create_xapi_result($hvp, ['user_id' => $student->id]);

        // Reset without selecting the hvp attempts option.
        $data = (object)['courseid' => $course->id, 'reset_hvp_attempts' => 0];
        $status = hvp_reset_userdata($data);

        // Nothing should have been deleted and no status returned.
        $this->assertEquals(1, $DB->count_records('hvp_content_user_data'));
        $this->assertEquals(1, $DB->count_records('hvp_xapi_results'));
        $this->assertCount(0, $status);
    }
}
