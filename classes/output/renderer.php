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
 * Resource Library renderer
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use renderable;

/**
 * Resource Library renderer
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Return the main content for the course list
     *
     * @param course_resourcelibrary $courserl The main renderable
     * @return string HTML string
     */
    public function render_course_resourcelibrary(course_resourcelibrary $courserl) {
        return $this->render_from_template('local_resourcelibrary/resourcelibrary',
            $courserl->export_for_template($this));
    }

    /**
     * Return the main content for the block overview.
     *
     * @param activity_resourcelibrary $activityrl The main renderable
     * @return string HTML string
     */
    public function render_activity_resourcelibrary(activity_resourcelibrary $activityrl) {
        return $this->render_from_template('local_resourcelibrary/resourcelibrary',
            $activityrl->export_for_template($this));
    }

}
