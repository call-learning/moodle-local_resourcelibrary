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
 * Manage the courses or course modules view for the Resource Library.
 *
 * Inspired from the Course overview block.
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


import {updateUserPreferences} from 'local_resourcelibrary/repository';
import State from 'local_resourcelibrary/local/state';


export default class ViewNav {
    static SELECTORS = {
        MODIFIERS: '[data-region="display-modifiers"]',
        SORT_OPTION: '[data-sort]',
        DISPLAY_OPTION: '[data-display-option]'
    };

    /**
     * Update the user preference for the block.
     *
     * @param {String} filter The type of filter: display/sort.
     * @param {String} value The current preferred value.
     */
    static updatePreferences(filter, value) {
        let type = null;
        if (filter === 'display') {
            type = 'local_resourcelibrary_user_view_preference';
        } else if (filter === 'sort') {
            type = 'local_resourcelibrary_user_sort_preference';
        }
        if (type) {
            updateUserPreferences({
                preferences: [
                    {
                        type: type,
                        value: value
                    }
                ]
            });
        }
    }

    /**
     * Event listener for the Display filter (cards, list).
     *
     * @param {Element} root The root element for the overview block
     */
    static registerSelector(root) {
        const selector = root.querySelector(ViewNav.SELECTORS.MODIFIERS);

        if (!selector) {
            return;
        }

        selector.addEventListener('click', (e) => {
            const sortTarget = e.target.closest(ViewNav.SELECTORS.SORT_OPTION);
            if (sortTarget) {
                if (sortTarget.classList.contains('active')) {
                    return;
                }

                const sortoption = sortTarget.getAttribute('data-sort');
                const sortcolumn = sortTarget.getAttribute('data-column');

                // Update visual active states
                const allSortItems = root.querySelectorAll(ViewNav.SELECTORS.SORT_OPTION);
                allSortItems.forEach(item => item.classList.remove('active'));
                sortTarget.classList.add('active');

                // Update state instead of DOM attributes
                State.setValue('sorting', [{column: sortcolumn, order: sortoption.toUpperCase()}]);
                State.setValue('currentPage', 1); // Reset to first page
                ViewNav.updatePreferences('sort', sortcolumn + ',' + sortoption);

                // Trigger data reload
                State.loadCurrentPage();

                e.preventDefault();
                return;
            }

            const displayTarget = e.target.closest(ViewNav.SELECTORS.DISPLAY_OPTION);
            if (displayTarget) {
                if (displayTarget.classList.contains('active')) {
                    return;
                }

                const displayoptions = displayTarget.getAttribute('data-display-option');

                // Update visual active states
                const allDisplayItems = root.querySelectorAll(ViewNav.SELECTORS.DISPLAY_OPTION);
                allDisplayItems.forEach(item => item.classList.remove('active'));
                displayTarget.classList.add('active');

                // Update state instead of DOM attributes
                State.setValue('display', displayoptions);
                ViewNav.updatePreferences('display', displayoptions);

                e.preventDefault();
            }
        });
    }

    /**
     * Initialise the timeline view navigation by adding event listeners to
     * the navigation elements.
     *
     * @param {Element} root The root element for the Resource Library
     */
    static init(root) {
        if (typeof root === 'string') {
            root = document.querySelector(root);
        }
        ViewNav.registerSelector(root);
    }
}
