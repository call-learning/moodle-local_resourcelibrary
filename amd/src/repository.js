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
 * A javascript module to retrieve a course list from the server.
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Ajax from 'core/ajax';
import Notification from 'core/notification';

/**
 * Retrieve a list of courses.
 *
 * Valid args are:
 * filters                  array of filters values (see get_filtered_courses_parameters)
 *
 * @method getFilteredCourseList
 * @param {object} args The request arguments
 * @return {promise} Resolved with an array of courses
 */
export const getFilteredCourseList = (args) => {
    const request = {
        methodname: 'local_resourcelibrary_get_filtered_courses',
        args: args
    };

    return Ajax.call([request])[0];
};

/**
 * Update the user preferences.
 *
 * @param {Object} args Arguments send to the webservice.
 *
 * Sample args:
 * {
 *     preferences: [
 *         {
 *             type: 'block_example_user_sort_preference'
 *             value: 'title'
 *         }
 *     ]
 * }
 */
export const updateUserPreferences = (args) => {
    const request = {
        methodname: 'core_user_update_user_preferences',
        args: args
    };

    Ajax.call([request])[0].fail(Notification.exception);
};


/**
 * Hide fields filters.
 *
 * @param {String} component
 * @param {String} area
 * @param {Array} fieldshortnames
 */
export const hideFieldsFilters = (component, area, fieldshortnames) => {
    const request = {
        methodname: 'local_resourcelibrary_hide_fields_filters',
        args: {
            component: component,
            area: area,
            fieldshortnames: fieldshortnames
        }
    };

    Ajax.call([request])[0].fail(Notification.exception);
};

/**
 * Show fields filters.
 *
 * @param {String} component
 * @param {String} area
 * @param {Array} fieldshortnames
 */
export const showFieldsFilters = (component, area, fieldshortnames) => {
    const request = {
        methodname: 'local_resourcelibrary_show_fields_filters',
        args: {
            component: component,
            area: area,
            fieldshortnames: fieldshortnames
        }
    };

    Ajax.call([request])[0].fail(Notification.exception);
};
