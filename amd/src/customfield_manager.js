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
import {hideFieldsFilters, showFieldsFilters} from "./repository";
import Pending from 'core/pending';

/**
 * Initialise the hide/show filter fields checkboxes.
 *
 * @param {String} component
 * @param {String} area
 * @param {Boolean} hidefilterlocator
 */
export const init = (component, area, hidefilterlocator) => {
    const elements = document.querySelectorAll(hidefilterlocator);

    elements.forEach(element => {
        element.addEventListener('click', async() => {
            const pending = new Pending('local_resourcelibrary/hide_show_field');
            try {
                if (element.checked) {
                    await hideFieldsFilters(component, area, [element.dataset.fieldShortname]);
                } else {
                    await showFieldsFilters(component, area, [element.dataset.fieldShortname]);
                }
            } catch (error) {
                // Error handling is done in the repository functions
            }
            pending.resolve();
        });
    });
};
