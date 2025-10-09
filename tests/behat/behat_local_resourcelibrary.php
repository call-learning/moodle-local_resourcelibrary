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

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use local_resourcelibrary\local\utils;
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
     * Add a step to navigate to a specific resource library page
     *
     * @param string $pagename The name of the catalogue page
     * @Given /^I navigate to resource library "(?P<pagename_string>(?:[^"]|\\")*)" page$/
     */
    public function i_navigate_to_resource_librar_course_content(string $pagename) {
        $url = new moodle_url('/local/resourcelibrary/index.php');
        if ($pagename != "Home") {
            $pageid = $this->get_catalogue_page_id($pagename);
            $url = new moodle_url('/local/resourcelibrary/page.php', ['id' => $pageid]);
        }
        $this->execute('behat_general::i_visit', [$url]);
    }

    /**
     * Get the catalogue page ID from the page name
     *
     * @param string $pagename
     * @return int
     * @throws Exception
     */
    protected function get_catalogue_page_id(string $pagename): int {
        global $DB;

        $pageid = $DB->get_field('local_resourcelibrary_pages', 'id', ['name' => $pagename]);
        if (!$pageid) {
            throw new Exception("Catalogue page with name '{$pagename}' not found");
        }

        return $pageid;
    }

    /**
     * Navigate to the catalogue pages management interface
     *
     * @Given /^I navigate to catalogue pages management$/
     */
    public function i_navigate_to_catalogue_pages_management() {
        // First navigate to the resource library home page
        $this->execute('behat_general::i_visit', [new moodle_url('/local/resourcelibrary/index.php')]);

        // Then click on the "Catalogue pages" button
        $this->execute('behat_general::i_click_on', ['Catalogue pages', 'button']);
    }

    /**
     * Navigate to view a catalogue page by clicking view in the dropdown
     *
     * @param string $pagename
     * @Given /^I view the catalogue page "(?P<pagename_string>(?:[^"]|\\")*)"$/
     */
    public function i_view_catalogue_page(string $pagename) {
        // Find the actions dropdown for the specific page and click it
        $xpath = "//tr[td/a[contains(text(), '$pagename')]]//button[contains(@id,'actions-dropdown')]";
        $this->execute('behat_general::i_click_on', [$xpath, 'xpath_element']);

        // Click on the view item in the dropdown
        $this->execute('behat_general::i_click_on', ['View', 'link']);
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

    /**
     * BeforeScenario hook to check if the scenario is tagged with @with_multiselect_installed
     *
     * @BeforeScenario @with_multiselect_installed
     *
     * @param BeforeScenarioScope $scope
     */
    public function before_scenario(BeforeScenarioScope $scope) {
        if (!utils::is_multiselect_installed()) {
            throw new SkippedException('Multiselect plugin is not installed, skipping scenario.');
        }
    }
}
