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
 * Patch to suppress social links to submit results to facebook, twitter or google.
 *
 * Note that htis path also includes theme changes in _cu-activities.scss to hide
 * ths form elements for social options.
 *
 * @package    mod_hvp
 * @copyright  Andreas Wagner, ISB Bayern
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hvp\patch;

defined('MOODLE_INTERNAL') || die();

/**
 * Patch to suppress social links to submit results to facebook, twitter or google.
 *
 * @package    mod_hvp
 * @copyright  Andreas Wagner, ISB Bayern
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class patch_social_options {

    /** @var array list of options to set to 0 when saving. */
    public static $options = [
        'showFacebookShare', 'showTwitterShare', 'showGoogleShare'
    ];

    /**
     * Set social options to 0, when saving a h5p module.
     * HACK is called in saveContent() of h5p.classes.php.
     *
     * @param array $content with json encoded params.
     */
    public static function remove_social_options(&$content) {

        $params = json_decode($content['params']);

        foreach (self::$options as $shareoption) {

            if (isset($params->override->social->{$shareoption})) {
                $params->override->social->{$shareoption} = 0;
            }
        }

        $content['params'] = json_encode($params);
    }

}
