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
 * Behat data generator for local_resourcelibrary.
 *
 * @category    test
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Behat data generator for resource library
 *
 * @package    local_resourcelibrary
 * @category    test
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_resourcelibrary_generator extends behat_generator_base {

    /**
     * Get all entities that can be created
     *
     * @return array|array[]
     */
    protected function get_creatable_entities(): array {
        return [
            'category' => [
                'datagenerator' => 'category',
                'required' => ['component', 'area']
            ],
            'field' => [
                'datagenerator' => 'field',
                'required' => ['component', 'area']
            ],
            'fielddata' => [
                'datagenerator' => 'fielddata',
                'required' => ['fieldshortname', 'courseshortname']
            ]
        ];
    }

    /**
     * Look up the id of a custom field category from its name.
     *
     * @param array $elementdata current data array
     * @return array $elementdata
     * @throws dml_exception
     */
    protected function preprocess_field($elementdata) {
        global $DB;
        $elementdata['categoryid'] = $DB->get_field('customfield_category', 'id',
            ['name' => trim($elementdata['customfieldcategory']),
                'area' => $elementdata['area'], 'component' => $elementdata['component']]);
        return $elementdata;
    }

    /**
     * Field Data Identifier
     *
     * @param string $shortname
     * @return bool|mixed
     * @throws dml_exception
     */
    protected function get_fieldid_id($shortname) {
        global $DB;
        return $DB->get_field('customfield_category', 'id',
            ['shortname' => trim($shortname)]);
    }

    /**
     * Look up the id of for the instance depending on the area the field is in
     *
     * @param array $elementdata current data array
     * @return array corresponding id.
     * @throws dml_exception
     */
    protected function preprocess_fielddata($elementdata) {
        global $DB;
        $elementdata['fieldid'] = $DB->get_field('customfield_field', 'id',
            ['shortname' => trim($elementdata['fieldshortname'])]);
        $field = core_customfield\field_controller::create($elementdata['fieldid']);
        $courseid = $DB->get_field('course', 'id',
            ['shortname' => trim($elementdata['courseshortname'])]);
        switch ($field->get_handler()->get_area()) {
            case 'course':
                $elementdata['instanceid'] = $courseid;
                break;
            case 'coursemodule':
                $modinfo = course_modinfo::instance($courseid);
                $activities = $modinfo->get_instances_of($elementdata['activity']);
                foreach ($activities as $activity) {
                    if (trim($activity->idnumber) == trim($elementdata['activityidnumber'])) {
                        $elementdata['instanceid'] = $activity->id;
                    }
                }
                break;
        }
        return $elementdata;
    }

}
