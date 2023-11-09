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
 * Resource Library additional steps
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_resourcelibrary\locallib\utils;
use Moodle\BehatExtension\Exception\SkippedException;

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Steps definitions
 *
 * @package    local_resourcelibrary
 * @category   test
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_resourcelibrary extends behat_base {

    /**
     * Checks that the Multiselect Custom Field is installed.
     *
     * @Given /^multiselect field is installed$/
     */
    public function multiselect_field_is_installed() {

        if (!utils::is_multiselect_installed()) {
            throw new SkippedException;
        }
    }

    /**
     * Add a step to navigate to /local/resourcelibrary/index.php
     *
     * @param string $coursefullname
     * @Given /^I navigate to resource library "(?P<coursefullname_string>(?:[^"]|\\")*)" page$/
     */
    public function i_navigate_to_resource_librar_course_content(string $coursefullname) {
        $url = new moodle_url('/local/resourcelibrary/index.php');
        if ($coursefullname != "Home") {
            $courseid = $this->get_course_id($coursefullname);
            $url->param('courseid', $courseid);
        }
        $this->execute('behat_general::i_visit', [$url]);
    }

    /**
     * Check that a page contains a list of texts (separated by commas)
     *
     * @param string $texts
     *
     * @Given /^I should see the texts "(?P<texts>(?:[^"]|\\")*)"$/
     */
    public function i_should_see_the_texts(string $texts) {
        $textarray = array_map('trim', explode(',', $texts));
        foreach ($textarray as $text) {
            $text = str_replace('\\"', '"', $text);
            $this->assertSession()->pageTextContains($text);
        }
    }

    /**
     * Check that a page does not contains a list of texts (separated by commas)
     *
     * @param string $texts
     *
     * @Given /^I should not see the texts "(?P<texts>(?:[^"]|\\")*)"$/
     */
    public function i_should_not_see_the_texts(string $texts) {
        if (trim($texts) == '') {
            return;
        }
        $textarray = array_map('trim', explode(',', $texts));
        foreach ($textarray as $text) {
            $text = str_replace('\\"', '"', $text);
            $this->assertSession()->pageTextNotContains($text);
        }
    }

}
