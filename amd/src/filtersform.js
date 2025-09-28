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
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Config from 'core/config';

const getFilterData = (target, ignoresesskey) => {
    const formData = new FormData(target);
    const filterdata = {};
    let sesskeyconfirmed = false;

    for (const [name, value] of formData.entries()) {
        if (name === 'sesskey') {
            sesskeyconfirmed = value === Config.sesskey;
            continue;
        }
        const parsename = name.match(/(customfield_)?(\w+)\[(\w+)\]\[?(\w*)\]?/);
        if (!parsename) {
            continue;
        }
        let hasCustomShortName = false;
        if (parsename.length >= 4) {
            parsename.shift();
            hasCustomShortName = true;
        }
        const rootname = parsename[1];
        const type = parsename[2];

        if (filterdata[rootname] === undefined) {
            filterdata[rootname] = {};
        }

        if (hasCustomShortName && filterdata[rootname].shortname === undefined) {
            Object.defineProperty(filterdata[rootname], 'shortname', {
                enumerable: true,
                value: rootname
            });
        }

        if (value !== "_qf__force_multiselect_submission") {
            if (typeof filterdata[rootname].value === "undefined") {
                Object.defineProperty(filterdata[rootname], type, {
                    enumerable: true,
                    value: value,
                    writable: true
                });
            } else {
                filterdata[rootname].value += ',' + value;
            }
        }
    }

    const filterdataarray = Object.values(filterdata).filter(v => {
        if (v.type === 'date' && v.value !== undefined) {
            return v.value.split(",").length > 3;
        }
        return v.value !== undefined || (v.value === null);
    });

    return (sesskeyconfirmed || ignoresesskey) ? filterdataarray : false;
};

export const init = (selector) => {
    const target = document.querySelector(selector);

    target.addEventListener('submit', (e) => {
        if (e.target.tagName === 'FORM') {
            e.preventDefault();
            const filterdataarray = getFilterData(e.target, false);
            if (filterdataarray) {
                document.dispatchEvent(new CustomEvent('resourcelibrary-filters-change', {
                    detail: filterdataarray
                }));
            }
        }
    });

    const resetButton = document.getElementById('id_resetbutton');
    if (resetButton) {
        resetButton.addEventListener('click', () => {
            const form = target.querySelector('form.resourcelibrary-filters-form');
            if (form) {
                form.reset();
            }
        });
    }

    const form = target.querySelector('form');
    if (form) {
        const filterdataarray = getFilterData(form, true);
        document.dispatchEvent(new CustomEvent('resourcelibrary-filters-inited', {
            detail: filterdataarray
        }));
    }
};
