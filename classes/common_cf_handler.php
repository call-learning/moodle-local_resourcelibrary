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
 * Course handler for metadata fields trait
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_resourcelibrary;

defined('MOODLE_INTERNAL') || die;

use core_customfield\api;
use core_customfield\field_controller;

trait common_cf_handler  {
    /**
     * @var common_cf_handler
     */
    static protected $singleton;

    /**
     * @var \context
     */
    protected $parentcontext;


    /**
     * Returns a singleton
     *
     * @param int $itemid
     * @return common_cf_handler
     */
    public static function create(int $itemid = 0) : \core_customfield\handler {
        if (static::$singleton === null) {
            static::$singleton = new static(0);
        }
        return self::$singleton;
    }

    /**
     * Run reset code after unit tests to reset the singleton usage.
     */
    public static function reset_caches(): void {
        if (!PHPUNIT_TEST) {
            throw new \coding_exception('This feature is only intended for use in unit tests');
        }
        static::$singleton = null;
    }

    /**
     * The current user can configure custom fields on this component.
     *
     * @return bool true if the current can configure custom fields, false otherwise
     */
    public function can_configure() : bool {
        return has_capability('local/resourcelibrary:configurecustomfields', $this->get_configuration_context());
    }

    /**
     * The current user can edit custom fields on the given field.
     *
     * @param field_controller $field
     * @param int $instanceid id of the course to test edit permission
     * @return bool true if the current can edit custom fields, false otherwise
     */
    public function can_edit(field_controller $field, int $instanceid = 0) : bool {
        if ($instanceid) {
            $context = $this->get_instance_context($instanceid);
            return (!$field->get_configdata_property('locked') ||
                has_capability('local/resourcelibrary::changelockedcustomfields', $context));
        } else {
            $context = $this->get_parent_context();
            return (!$field->get_configdata_property('locked') ||
                guess_if_creator_will_have_course_capability('local/resourcelibrary:changelockedcustomfields', $context));
        }
    }


    /**
     * Sets parent context for the module
     *
     * This may be needed when module is being created, there is no module context but we need to check capabilities
     *
     * @param \context $context
     */
    public function set_parent_context(\context $context) {
        $this->parentcontext = $context;
    }

    /**
     * Context that should be used for new categories created by this handler
     *
     * @return \context the context for configuration
     */
    public function get_configuration_context() : \context {
        return \context_system::instance();
    }

    /**
     * Here we don't use categories
     *
     * @return bool
     */
    public function uses_categories() : bool {
        return false;
    }

    /**
     * The current user can view custom fields
     *
     * @param field_controller $field
     * @param int $instanceid id of the course to test edit permission
     * @return bool true if the current can view custom fields, false otherwise
     * @throws \coding_exception
     */
    public function can_view(field_controller $field, int $instanceid): bool {
        global $USER;
        $visibility = $field->get_configdata_property('visibility');

        return ($visibility == self::NOTVISIBLE && is_primary_admin($USER->id)) ||
            has_capability('local/resourcelibrary:view', $this->get_instance_context($instanceid));
    }

    /**
     * Set up page customfield/edit.php
     *
     * @param field_controller $field
     * @return string page heading
     */
    protected function setup_edit_page_with_external(field_controller $field, $externalpagename) : string {
        global $CFG, $PAGE;
        require_once($CFG->libdir.'/adminlib.php');

        $title = parent::setup_edit_page($field);
        admin_externalpage_setup($externalpagename);
        $PAGE->navbar->add($title);
        return $title;
    }
}