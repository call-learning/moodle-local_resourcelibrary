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

import getString from 'core/str';
import Toast from 'core/toast';
import Templates from 'core/templates';
import Notification from 'core/notification';

let catalogURL = null;

export const setupCopyLink = (triggerid, targetid) => {
    const triggerElement = document.querySelector(`#${triggerid}`);
    if (triggerElement) {
        triggerElement.addEventListener('click', async() => {
            const target = document.getElementById(targetid);
            if (target) {
                target.select();
                try {
                    await navigator.clipboard.writeText(target.value);
                    const copiedString = await getString('copied', 'local_resourcelibrary');
                    Toast.add(copiedString, null, 'success');
                } catch (error) {
                    // Fallback pour les navigateurs plus anciens
                    if (document.execCommand('copy')) {
                        const copiedString = await getString('copied', 'local_resourcelibrary');
                        Toast.add(copiedString, null, 'success');
                    }
                }
            }
        });
    }
};

export const init = () => {
    catalogURL = new URL(window.location.href);

    document.addEventListener('resourcelibrary-filters-change', async(e)=> {
        const filterarray = e.detail;

        // Reset search params for filters
        const paramsToRemove = [];
        for (const [key] of catalogURL.searchParams.entries()) {
            if (key.startsWith('customfield_')) {
                paramsToRemove.push(key);
            }
        }
        paramsToRemove.forEach(param => catalogURL.searchParams.delete(param));

        // Add new filter parameters
        filterarray.forEach(f => {
            const fieldname = `customfield_${f.shortname}`;
            if (f.value) {
                catalogURL.searchParams.append(`${fieldname}[operator]`, f.operator);
                catalogURL.searchParams.append(`${fieldname}[value]`, f.value);
                catalogURL.searchParams.append(`${fieldname}[type]`, f.type);
            }
        });

        Templates.render('local_resourcelibrary/permalink', {
            url: catalogURL.toString()
        }).then(async(html, js) => {
            Templates.replaceNodeContents('#resourcelibrary-permalink', html, js);
            return;
        }).catch(Notification.exception);
    });
};

