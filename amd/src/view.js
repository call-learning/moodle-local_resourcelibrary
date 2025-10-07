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
 * State-based view for the Resource Library.
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import * as Selectors from 'local_resourcelibrary/selectors';
import State from 'local_resourcelibrary/local/state';
import stateTemplateInit from 'local_resourcelibrary/local/components/view';
import {updateUserPreferences} from './repository';

export default class View {
    static NUMCOURSES_PERPAGE = [12, 24, 48];

    static init(root) {
        if (typeof root === 'string') {
            root = document.querySelector(root);
        }
        if (!(root instanceof HTMLElement)) {
            throw new Error('Invalid root element provided.');
        }

        // Initialize state from DOM attributes
        const entityRegion = root.querySelector(Selectors.entityView.region);
        if (entityRegion) {
            const initialState = {
                display: entityRegion.getAttribute('data-display') || 'cards',
                sorting: [{
                    column: entityRegion.getAttribute('data-sort-column') || 'fullname',
                    order: entityRegion.getAttribute('data-sort-order') || 'ASC'
                }],
                displayCategories: entityRegion.getAttribute('data-displaycategories') || 'off',
                itemsPerPage: parseInt(entityRegion.getAttribute('data-paging'), 10) || 12,
                entityType: root.getAttribute('data-entity-type') || 'course',
                courseId: parseInt(root.getAttribute('data-parent-id'), 10) || 0,
                pageId: parseInt(root.getAttribute('data-page-id'), 10) || 0,
                // For catalogue pages, initialize with server-rendered pagination data
                totalItems: parseInt(entityRegion.getAttribute('data-total-items'), 10) || 0,
                currentPage: parseInt(entityRegion.getAttribute('data-current-page'), 10) || 1
            };

            State.setData(initialState);
        }

        // Initialize state templates
        stateTemplateInit();

        // Set up pagination controls
        View.setupPagination(root);

        // Load initial data only if not a catalogue page (catalogue pages are pre-rendered)
        const isCataloguePage = root.getAttribute('data-page-id') && parseInt(root.getAttribute('data-page-id'), 10) > 0;
        if (!isCataloguePage) {
            State.loadCurrentPage();
        } else {
            // For catalogue pages, just trigger pagination rendering since pagination data is already in state
            // The entities are already rendered server-side in the template
            View.triggerPaginationRender();
        }

        // Listen for filter changes
        // document.addEventListener('resourcelibrary-filters-inited', (e) => {
        //     State.setFilters(e.detail);
        // });

        document.addEventListener('resourcelibrary-filters-change', (e) => {
            State.setFilters(e.detail);
        });
    }

    static setupPagination(root) {
        // Pagination click handlers
        root.addEventListener('click', (e) => {
            const pageBtn = e.target.closest('[data-page]');
            if (pageBtn) {
                e.preventDefault();
                const page = parseInt(pageBtn.getAttribute('data-page'), 10);
                State.goToPage(page);
            }
        });

        // Items per page change handler
        root.addEventListener('change', (e) => {
            const itemsPerPageSelect = e.target.closest('[data-items-per-page]');
            if (itemsPerPageSelect) {
                e.preventDefault();
                const itemsPerPage = parseInt(itemsPerPageSelect.value, 10);
                State.setItemsPerPage(itemsPerPage);

                // Save user preference
                updateUserPreferences({
                    preferences: [
                        {
                            type: 'local_resourcelibrary_user_paging_preference',
                            value: itemsPerPage.toString()
                        }
                    ]
                });
            }
        });
    }

    static refresh(root) {
        if (typeof root === 'string') {
            root = document.querySelector(root);
        }
        if (!(root instanceof HTMLElement)) {
            throw new Error('Invalid root element provided.');
        }

        State.loadCurrentPage();
    }

    static reset(root) {
        View.refresh(root);
    }

    static getState() {
        return State;
    }

    /**
     * Trigger pagination rendering for catalogue pages
     * The pagination data is already loaded in the state from data attributes
     */
    static triggerPaginationRender() {
        // Simply trigger a state notification to render pagination
        // The pagination data (totalItems, currentPage, itemsPerPage) is already set in the initial state
        State.setValue('itemsPerPage', State.getData().itemsPerPage);
    }
}
