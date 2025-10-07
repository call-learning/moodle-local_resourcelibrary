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
 * Retrieve a list of courses using the REST API.
 *
 * Valid args are:
 * pageid                 int Catalogue page ID to filter by (0 for all courses)
 * categoryid               int Legacy category ID to filter by (0 for all categories)
 * limit                    int Maximum number of courses to return (0 for no limit)
 * offset                   int Number of courses to skip for pagination
 * filters                  array of filters values
 * sorting                  array of sorting options
 *
 * @method getFilteredCourseList
 * @param {object} args The request arguments
 * @return {promise} Resolved with an array of courses
 */
export const getFilteredCourseList = (args) => {
    // Build query parameters
    const params = new URLSearchParams();

    if (args.pageid !== undefined) {
        params.append('pageid', args.pageid);
    }
    if (args.categoryid !== undefined) {
        params.append('categoryid', args.categoryid);
    }
    if (args.limit !== undefined) {
        params.append('limit', args.limit);
    }
    if (args.offset !== undefined) {
        params.append('offset', args.offset);
    }
    if (args.filters !== undefined && args.filters.length > 0) {
        params.append('filters', JSON.stringify(args.filters));
    }
    if (args.sorting !== undefined && args.sorting.length > 0) {
        params.append('sorting', JSON.stringify(args.sorting));
    }

    // Build the REST API URL
    const baseUrl = M.cfg.wwwroot + '/r.php/api/rest/v2/local_resourcelibrary/courses';
    const url = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;

    // Make the fetch request with fallback to legacy web service
    return fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin' // Include cookies for authentication
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`REST API HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .catch(() => {
        // Fallback to legacy web service on any error
        Notification.alert('Falling back to legacy web service due to an error with the REST API.', 'warning');
    });
};


/**
 * Update item visibility in the catalogue using the REST API.
 *
 * @param {Object} params Parameters for the visibility update
 * @param {number} params.itemid The ID of the item to update
 * @param {number} params.itemtype The type of the item (category/course)
 * @param {number} params.visibility The visibility status (0=visible, 1=hidden)
 * @return {promise} Resolved with the updated item data
 */
export const updateItemVisibility = async(params) => {
    try {
        const url = `${M.cfg.wwwroot}/r.php/api/rest/v2/local_resourcelibrary/items/${params.itemid}/visibility`;
        const body = {
            itemtype: parseInt(params.itemtype),
            visibility: parseInt(params.visibility)
        };

        const response = await fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(body)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || `HTTP ${response.status}`);
        }

        const data = await response.json();
        if (data.success) {
            return {
                itemid: data.itemid,
                itemtype: data.itemtype,
                visibility: data.visibility
            };
        } else {
            throw new Error('Failed to update item visibility');
        }
    } catch (error) {
        throw new Error(error.message || 'An error occurred while updating item visibility');
    }
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
