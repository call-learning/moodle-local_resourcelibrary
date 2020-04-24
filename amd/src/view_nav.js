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
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
    [
        'jquery',
        'core/custom_interaction_events',
        'local_resourcelibrary/repository',
        'local_resourcelibrary/view',
        'local_resourcelibrary/selectors'
    ],
    function (
        $,
        CustomEvents,
        Repository,
        View,
        Selectors
    ) {

        var SELECTORS = {
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
        var updatePreferences = function (filter, value) {
            var type = null;
            if (filter === 'display') {
                type = 'local_resourcelibrary_user_view_preference';
            } else {
                type = 'local_resourcelibrary_user_sort_preference';
            }

            Repository.updateUserPreferences({
                preferences: [
                    {
                        type: type,
                        value: value
                }
                ]
            });
        };

        /**
         * Event listener for the Display filter (cards, list).
         *
         * @param {object} root The root element for the overview block
         */
        var registerSelector = function (root) {

            var Selector = root.find(SELECTORS.MODIFIERS);

            CustomEvents.define(Selector, [CustomEvents.events.activate]);
            Selector.on(
                CustomEvents.events.activate,
                SELECTORS.SORT_OPTION,
                function (e, data) {
                    var option = $(e.target);

                    if (option.hasClass('active')) {
                        // If it's already active then we don't need to do anything.
                        return;
                    }

                    var sortoption = option.attr('data-sort');
                    var sortcolumn = option.attr('data-column');
                    // Update model.
                    root.find(Selectors.entityView.region).attr('data-sort-column', sortcolumn);
                    root.find(Selectors.entityView.region).attr('data-sort-order', sortoption);
                    updatePreferences('sort', sortcolumn + ',' + sortoption);

                    // Reset the views.
                    View.init(root);
                    data.originalEvent.preventDefault();
                }
            );

            CustomEvents.define(Selector, [CustomEvents.events.activate]);
            Selector.on(
                CustomEvents.events.activate,
                SELECTORS.DISPLAY_OPTION,
                function (e, data) {
                    var option = $(e.target);

                    if (option.hasClass('active')) {
                        return;
                    }

                    var displayoptions = option.attr('data-display-option');

                    // Update model.
                    root.find(Selectors.entityView.region).attr('data-display', displayoptions);
                    updatePreferences('display', displayoptions);

                    // Reset the views.
                    View.reset(root);
                    data.originalEvent.preventDefault();
                }
            );
        };

        /**
         * Initialise the timeline view navigation by adding event listeners to
         * the navigation elements.
         *
         * @param {object} root The root element for the Resource Library
         */
        var init = function (root) {
            root = $(root);
            registerSelector(root);
        };

        return {
            init: init
        };
    });
