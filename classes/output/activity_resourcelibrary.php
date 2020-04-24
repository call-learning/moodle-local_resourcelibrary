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
 * Course resourcelibrary
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\output;

defined('MOODLE_INTERNAL') || die();

use local_resourcelibrary\filters\filter_form;
use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for the course resourcelibrary.
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_resourcelibrary extends base_resourcelibrary {

    /**
     * main constructor.
     * Initialize the user preferences
     *
     * @param string $grouping Grouping user preference
     * @param string $sort Sort user preference
     * @param string $view Display user preference
     * @throws \dml_exception
     */
    public function __construct(
        $courseid,
        $sortcolumn = 'fullname',
        $sortorder = 'DESC',
        $view = self::VIEW_CARD,
        $paging = self::PAGING_12) {
        parent::__construct($sortcolumn, $sortorder, $view, $paging);
        $this->courseid = $courseid;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output) {
        $handler = \local_resourcelibrary\customfield\coursemodule_handler::create();
        $defaultvariables = $this->get_export_defaults($output, $handler);
        $defaultvariables['parentid'] = $this->courseid;
        $preferences = $this->get_preferences();
        return array_merge($defaultvariables, $preferences);

    }
}