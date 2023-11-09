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
 * Manage which courses are shown in the catalogue.
 *
 * @module     local_resourcelibrary/item_visibility
 * @copyright  2023 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';

// Set the constants for the visibility.
const LOCAL_RESOURCELIBRARY_ITEM_VISIBLE = 0;
const LOCAL_RESOURCELIBRARY_ITEM_HIDDEN = 1;

class ItemVisibility {
    constructor() {
        this.changeToButtons();
        this.addEventListeners();
    }

    /**
     * Find all elements that have the data-action hidefromcatalogue or showincatalogue
     * and change the link to a button.
     */
    changeToButtons() {
        const elements = document.querySelectorAll('a[data-action="hidefromcatalogue"], a[data-action="showincatalogue"]');
        elements.forEach((element) => {
            // Get the data attributes, create a new button and replace the link with the button,
            // then set the data attributes to the button.
            const dataset = element.dataset;
            const button = document.createElement('span');
            // Get the CSS classes from the link and add them to the button.
            const classes = element.classList;
            classes.forEach((cssclass) => {
                button.classList.add(cssclass);
            });
            // Apply the element dataset to the button.
            Object.keys(dataset).forEach((key) => {
                button.dataset[key] = dataset[key];
            });
            button.classList.add('btn-link');
            button.setAttribute('role', 'button');

            // Set the button text.
            button.innerHTML = element.innerHTML;
            // Replace the link with the button.
            element.parentNode.replaceChild(button, element);
        });
    }

    /**
     * Add event listeners.
     */
    addEventListeners() {
        document.addEventListener('click', (event) => {
            const hidefromcatalogue = event.target.closest('[data-action="hidefromcatalogue"]');
            if (hidefromcatalogue) {
                event.preventDefault();
                this.hideFromCatalogue(hidefromcatalogue);
            }
            const showincatalogue = event.target.closest('[data-action="showincatalogue"]');
            if (showincatalogue) {
                event.preventDefault();
                this.showInCatalogue(showincatalogue);
            }
        });
    }

    /**
     * Hide item from catalogue.
     *
     * @param {HTMLElement} element
     */
    hideFromCatalogue(element) {
        const id = element.dataset.id;
        const itemid = element.dataset.itemid;
        const itemtype = element.dataset.itemtype;
        const params = {
            id: id,
            itemid: itemid,
            itemtype: itemtype,
            visibility: LOCAL_RESOURCELIBRARY_ITEM_HIDDEN
        };
        this.sendRequest(params);
    }

    /**
     * Show item in catalogue.
     *
     * @param {HTMLElement} element
     */
    showInCatalogue(element) {
        const id = element.dataset.id;
        const itemid = element.dataset.itemid;
        const itemtype = element.dataset.itemtype;
        const params = {
            id: id,
            itemid: itemid,
            itemtype: itemtype,
            visibility: LOCAL_RESOURCELIBRARY_ITEM_VISIBLE
        };
        this.sendRequest(params);
    }

    /**
     * Send the AJAX request.
     * @param {Object} params
     */
    sendRequest(params) {
        const items = {
          items: [params]
        };
        Ajax.call([{
            methodname: 'local_resourcelibrary_set_items_visibility',
            args: items,
            done: (data) => {
                if (data.returneditems) {
                    this.updateButtons(data.returneditems);
                } else {
                    Notification.exception(data);
                }
            }
        }]);
    }

    /**
     * Show / Hide the correct buttons based on the Ajax response.
     * @param {Array} items
     */
    updateButtons(items) {
        items.forEach((item) => {
            const itemid = item.itemid;
            const itemtype = item.itemtype;
            const visibility = item.visibility;
            const showInCatalogueButton = document.querySelector(
                `[data-action="showincatalogue"][data-itemid="${itemid}"][data-itemtype="${itemtype}"]`);
            const hideFromCatalogueButton = document.querySelector(
                `[data-action="hidefromcatalogue"][data-itemid="${itemid}"][data-itemtype="${itemtype}"]`);

            if (visibility === LOCAL_RESOURCELIBRARY_ITEM_VISIBLE) {
                if (showInCatalogueButton) {
                    showInCatalogueButton.classList.add('d-none');
                }
                if (hideFromCatalogueButton) {
                    hideFromCatalogueButton.classList.remove('d-none');
                    hideFromCatalogueButton.classList.add('blink');
                    setTimeout(() => {
                        hideFromCatalogueButton.classList.remove('blink');
                    }, 1000);
                }
            } else if (visibility === LOCAL_RESOURCELIBRARY_ITEM_HIDDEN) {
                if (showInCatalogueButton) {
                    showInCatalogueButton.classList.remove('d-none');
                    showInCatalogueButton.classList.add('blink');
                    setTimeout(() => {
                        showInCatalogueButton.classList.remove('blink');
                    }, 1000);
                }
                if (hideFromCatalogueButton) {
                    hideFromCatalogueButton.classList.add('d-none');
                }
            }
        });
    }
}

const init = () => {
    new ItemVisibility();
};

export default {
    init: init
};