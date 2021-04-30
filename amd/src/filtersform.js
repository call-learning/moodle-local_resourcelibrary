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
 * A javascript module to retrieve the filter form and put it in the right location.
 * This will also make sure that submit of this form will be sent to the filter form.
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/config'], function ($, config) {

    /**
     * FilterForm class.
     *
     * @class FilterForm
     * @param {String} selector The selector for the form
     */
    var FiltersForm = {};

    /**
     * Refresh after
     * @param target
     */
    function get_filter_data(target, ignoresesskey) {
        var data = $(target).serializeArray();
        var filterdata = {};
        // Check sesskey (if not ignore request).
        var sesskeyconfirmed = false;
        data.forEach(function (d) {
            if (d.name === 'sesskey') {
                sesskeyconfirmed = d.value === config.sesskey;
            } else {
                var parsename = d.name.match(/(customfield_)?(\w+)\[(\w+)\]\[?(\w*)\]?/);
                if (parsename) {
                    var hasCustomShortName = false;
                    if (parsename.length >= 4) { // This is a customfield (value, operator, type).
                        parsename.shift();
                        hasCustomShortName = true;
                    }
                    var rootname = parsename[1];
                    var type = parsename[2];
                    if (filterdata[rootname] === undefined) {
                        filterdata[rootname] = {};
                    }
                    if (hasCustomShortName && filterdata[rootname].shortname === undefined) {
                        Object.defineProperty(filterdata[rootname], 'shortname', {
                            enumerable: true, // For JSON Stringify.
                            value: rootname
                        });
                    }
                    if (d.value != "_qf__force_multiselect_submission") // Specific case for multiselect
                    {
                        if (typeof filterdata[rootname].value == "undefined"
                        ) {
                            Object.defineProperty(filterdata[rootname], type, {
                                enumerable: true, // For JSON Stringify.
                                value: d.value,
                                writable: true
                            });
                        } else {
                            filterdata[rootname].value += ',' + d.value;
                        }
                    }
                }
            }
        });
        var filterdataarray = Object.values(filterdata).filter(function (v) {
            return v.value !== undefined || (v.value === null);
        }); // Remove filters for which value is undefined or null.
        if (sesskeyconfirmed || ignoresesskey) {
            return filterdataarray;
        }
        return false;
    }
    FiltersForm.init = function (selector) {
        var target = $(selector);
        // Remove any attempt to submit the form for real.
        target.on('submit', 'form', function (e) {
            e.preventDefault();
            // Now we get all the current values from the form.
            var filterdataarray = get_filter_data(target.children('form'), false);
            if (filterdataarray) {
                $(document).trigger('resourcelibrary-filters-change', [filterdataarray]);
            }
        });
        $('#id_resetbutton').on('click', function () {
            $(target).children('form.resourcelibrary-filters-form')[0].reset();
        });
        var filterdataarray = get_filter_data(target.children('form'), true);
        $(document).trigger('resourcelibrary-filters-inited', [filterdataarray]); // Filter are now initialised.
    };
    return FiltersForm;
});
