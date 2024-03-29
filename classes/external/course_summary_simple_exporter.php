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
 * Same as the course summary_exporter
 *
 * Remove any non relevant information to speedup the rendering
 *
 * @package   local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\external;

use core_course\external\course_summary_exporter;
use renderer_base;
use moodle_url;

/**
 * Class for exporting a course summary from an stdClass.
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_summary_simple_exporter extends course_summary_exporter {

    /**
     * COURSE_VIEW_DEFAULT_URL
     */
    const COURSE_VIEW_DEFAULT_URL = '/course/view.php';

    /**
     * Define related context
     *
     * @return array|string[]
     */
    protected static function define_related() {
        // We cache the context so it does not need to be retrieved from the course.
        return ['context' => '\\context', 'isfavourite' => 'bool?'];
    }

    /**
     * Get additional values
     *
     * @param renderer_base $output
     * @return array
     * @throws \moodle_exception
     */
    protected function get_other_values(renderer_base $output) {
        $courseimage = self::get_course_image($this->data);
        if (!$courseimage) {
            $courseimage = $output->get_generated_image_for_id($this->data->id);
        }
        $courseviewurl = get_config('local_resourcelibrary', 'courseviewbaseurl');
        if (!$courseviewurl) {
            $courseviewurl = self::COURSE_VIEW_DEFAULT_URL;
        }
        return [
            'viewurl' => (new moodle_url($courseviewurl, ['id' => $this->data->id]))->out(false),
            'image' => $courseimage,
        ];
    }

    /**
     * Properties from DB
     *
     * @return array|array[]
     */
    public static function define_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
            ],
            'fullname' => [
                'type' => PARAM_TEXT,
            ],
            'shortname' => [
                'type' => PARAM_TEXT,
            ],
            'idnumber' => [
                'type' => PARAM_RAW,
            ],
            'startdate' => [
                'type' => PARAM_INT,
            ],
            'enddate' => [
                'type' => PARAM_INT,
            ],
            'visible' => [
                'type' => PARAM_BOOL,
            ],
            'timecreated' => [
                'type' => PARAM_INT,
            ],
            'timemodified' => [
                'type' => PARAM_INT,
            ],
        ];
    }

    /**
     * Get the formatting parameters for the summary.
     *
     * @return array
     */
    protected function get_format_parameters_for_summary() {
        return [
            'component' => 'course',
            'filearea' => 'summary',
        ];
    }

    /**
     * Additional properties
     *
     * @return array|array[]
     */
    public static function define_other_properties() {
        return [
            'viewurl' => [
                'type' => PARAM_URL,
            ],
            'image' => [
                'type' => PARAM_RAW,
            ],
        ];
    }
}
