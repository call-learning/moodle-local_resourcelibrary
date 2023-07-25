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
 * A javascript module to display the current's filter permanent link
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/config', 'core/templates', 'core/toast', 'core/str'], function ($, config, Templates, Toast, Str) {

    var Permalink = {};
    Permalink.catalogURL = null;
    Permalink.setupCopyLink = function (triggerid, targetid) {
        document.querySelector("#" + triggerid).addEventListener("click",
            function () {
                document.getElementById(targetid).select();
                document.execCommand("copy");
                Toast.add(Str.get_string('copied', 'local_resourcelibrary'), null, 'success');
            });
    };

    Permalink.init = function () {
        Permalink.catalogURL = new URL(window.location.href);
        $(document).on('resourcelibrary-filters-change', function (e, filterarray) {
                filterarray.forEach(function (f) {
                    const fieldname = 'customfield_' + f.shortname;
                    if (f.value) {
                        Permalink.catalogURL.searchParams.append(fieldname + '[operator]', f.operator);
                        Permalink.catalogURL.searchParams.append(fieldname + '[value]', f.value);
                        Permalink.catalogURL.searchParams.append(fieldname + '[type]', f.type);
                    }
                });
                Templates.render('local_resourcelibrary/permalink',
                    {url: Permalink.catalogURL.toString()}).then(
                    function (html, js) {
                        Templates.replaceNodeContents('#resourcelibrary-permalink', html, js);
                    }
                );
            }
        );
    };
    return Permalink;
});
