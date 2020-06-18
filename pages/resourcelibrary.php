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
 * Resource Library page
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
global $CFG;
require_once($CFG->dirroot . '/course/lib.php');

$courseid = optional_param('courseid', SITEID, PARAM_INT);
if ($CFG->forcelogin) {
    require_login($courseid, true); // We make sure the course exists and we can access it.
}

$PAGE->set_pagelayout('standard');
$pageparams = array();
if ($courseid != SITEID) {
    $pageparams['courseid'] = $courseid;
}

$pageurl = new moodle_url('/local/resourcelibrary/pages/resourcelibrary.php', $pageparams);
$PAGE->set_context(context_system::instance());
$PAGE->set_url($pageurl);

$site = get_site();

$strresourcelibrary = get_string('resourcelibrary', 'local_resourcelibrary');

$pagedesc = $strresourcelibrary;
$title = $strresourcelibrary;

$renderable = null;
if ($courseid != SITEID) {
    $PAGE->set_context(context_course::instance($courseid));
    $renderable = new local_resourcelibrary\output\activity_resourcelibrary($courseid);
} else {
    $renderable = new local_resourcelibrary\output\course_resourcelibrary();
}

$PAGE->set_title($title);
$PAGE->set_heading($pagedesc);

$renderer = $PAGE->get_renderer('local_resourcelibrary');
echo $OUTPUT->header();

echo $renderer->render($renderable);

echo $OUTPUT->footer();
