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
 * Catalogue page display
 *
 * @package    local_resourcelibrary
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

use local_resourcelibrary\local\persistent\catalogue_page;
use local_resourcelibrary\output\catalogue_page_resourcelibrary;

global $CFG, $PAGE, $DB, $OUTPUT, $USER;

$id = required_param('id', PARAM_INT); // Catalogue page ID
$edit = optional_param('edit', null, PARAM_BOOL);    // Turn editing on and off.

require_login();

// Load the catalogue page
try {
    $cataloguepage = new catalogue_page($id);
} catch (Exception $e) {
    throw new moodle_exception('invalidcataloguepage', 'local_resourcelibrary');
}

$PAGE->set_pagelayout('standard');
$pageparams = ['id' => $id];

$context = context_system::instance();

// Create the renderable for this specific catalogue page
$renderable = new catalogue_page_resourcelibrary($cataloguepage);
$PAGE->add_body_class('resource-library-catalogue-page');

$strresourcelibrary = \local_resourcelibrary\local\utils::get_resource_library_menu_text();
$pagetitle = $cataloguepage->get('name');
$pageurl = new moodle_url('/local/resourcelibrary/page.php', $pageparams);

$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_title($pagetitle);
$PAGE->navbar->add($strresourcelibrary, new moodle_url('/local/resourcelibrary/index.php'));
$PAGE->navbar->add($pagetitle);

$renderer = $PAGE->get_renderer('local_resourcelibrary');

echo $OUTPUT->header();

// Display catalogue page description or filters info
echo $OUTPUT->heading($pagetitle);

echo $renderer->render($renderable);

echo $OUTPUT->footer();
