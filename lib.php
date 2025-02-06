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
 * Add form hooks for course and modules
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define("LOCAL_RESOURCELIBRARY_ITEMTYPE_CATEGORY", 1);
define("LOCAL_RESOURCELIBRARY_ITEMTYPE_COURSE", 2);

define("LOCAL_RESOURCELIBRARY_ITEM_VISIBLE", 0);
define("LOCAL_RESOURCELIBRARY_ITEM_HIDDEN", 1);



/**
 * Nothing for now
 */
function local_resourcelibrary_enable_disable_plugin_callback() {
    // Nothing for now.
}

/**
 * Extend course navigation setting so we can add a specific setting for course resourcelibrary data.
 * This will allow not to use the customscript trick.
 */

/**
 * Extends navigation for the plugin (link to the resource library).
 *
 * Also replace navigation so go directly to the course catalog from the breadcrumb.
 *
 * @param global_navigation $nav
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function local_resourcelibrary_extend_navigation(global_navigation $nav) {
    global $CFG;
    if (empty($CFG->enableresourcelibrary)) {
        return;
    }
    list($urltext, $url) = \local_resourcelibrary\locallib\utils::get_catalog_url();
    $mycoursesnode = $nav->find('mycourses', null);
    if ($mycoursesnode) {
        $node = $nav->create($urltext, $url, navigation_node::NODETYPE_LEAF, null, 'resourcelibrary',
            new pix_icon('i/course', 'resourcelibrary'));
        $node->showinflatnavigation = true;
        $nav->add_node($node, 'mycourses');
    }
    $replacenavigation = get_config('local_resourcelibrary', 'replacecourseindex');
    if ($replacenavigation) {
        $coursenav = $nav->find('courses', global_navigation::TYPE_ROOTNODE);
        $coursenav->action = new moodle_url('/local/resourcelibrary/index.php');
    }
}

/**
 * Get the current user preferences that are available
 *
 * @return mixed Array representing current options along with defaults
 */
function local_resourcelibrary_user_preferences() {
    $preferences['local_resourcelibrary_user_sort_preference'] = [
        'null' => NULL_NOT_ALLOWED,
        'default' => local_resourcelibrary\output\base_resourcelibrary::SORT_FULLNAME_ASC,
        'type' => PARAM_ALPHA,
        'choices' => [
            local_resourcelibrary\output\base_resourcelibrary::SORT_FULLNAME_ASC,
            local_resourcelibrary\output\base_resourcelibrary::SORT_FULLNAME_DESC,
            local_resourcelibrary\output\base_resourcelibrary::SORT_LASTMODIF_ASC,
            local_resourcelibrary\output\base_resourcelibrary::SORT_LASTMODIF_DESC,
        ],
    ];
    $preferences['local_resourcelibrary_user_view_preference'] = [
        'null' => NULL_NOT_ALLOWED,
        'default' => local_resourcelibrary\output\base_resourcelibrary::VIEW_CARD,
        'type' => PARAM_ALPHA,
        'choices' => [
            local_resourcelibrary\output\base_resourcelibrary::VIEW_CARD,
            local_resourcelibrary\output\base_resourcelibrary::VIEW_LIST,
        ],
    ];

    $preferences['local_resourcelibrary_user_paging_preference'] = [
        'null' => NULL_NOT_ALLOWED,
        'default' => local_resourcelibrary\output\base_resourcelibrary::PAGING_12,
        'type' => PARAM_INT,
        'choices' => [
            local_resourcelibrary\output\base_resourcelibrary::PAGING_12,
            local_resourcelibrary\output\base_resourcelibrary::PAGING_24,
            local_resourcelibrary\output\base_resourcelibrary::PAGING_48,
        ],
    ];

    return $preferences;
}
