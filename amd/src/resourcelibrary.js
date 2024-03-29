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
 * Javascript to initialise the Resource Library page.
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
[
    'jquery',
    'local_resourcelibrary/view',
    'local_resourcelibrary/view_nav'
],
function(
    $,
    View,
    ViewNav
) {
    /**
     * Initialise all of the modules for the imt resourcelibrary local plugin.
     * Inspired from block myoverview.
     * @param {object} root The root element for the overview block.
     */
    var init = function(root) {
        root = $(root);
        // Initialise the course navigation elements.
        ViewNav.init(root);
        // Initialise the courses view modules.
        View.init(root);
    };

    return {
        init: init
    };
});
