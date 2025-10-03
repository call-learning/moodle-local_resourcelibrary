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
 * Catalogue pages management
 *
 * @package    local_resourcelibrary
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

use local_resourcelibrary\output\catalogue_pages_management;

require_login();
require_capability('local/resourcelibrary:managecatalogues', context_system::instance());

$PAGE->set_url('/local/resourcelibrary/catalogue_pages.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('cataloguepages', 'local_resourcelibrary'));
$PAGE->set_heading(get_string('cataloguepages', 'local_resourcelibrary'));
$PAGE->navbar->add(get_string('cataloguepages', 'local_resourcelibrary'));

echo $OUTPUT->header();

$renderable = new catalogue_pages_management();
echo $OUTPUT->render($renderable);

echo $OUTPUT->footer();
