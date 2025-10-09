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
 * State template components for Resource Library
 *
 * @module     local_resourcelibrary/local/components/view
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import State from 'local_resourcelibrary/local/state';
import Templates from 'core/templates';

/**
 * Entities template renderer with custom logic.
 * @param {String} root The root region selector.
 */
const entitiesTemplate = (root = 'resourcelibrary') => {
    const app = document.querySelector(`[data-region="${root}"]`);
    if (!app) {
        return;
    }
    const wrapper = app.querySelector('[data-region="resourcelibrary-view"]');
    const region = app.querySelector('[data-region="resourcelibrary-view-content"]');
    if (!region) {
        return;
    }

    const renderEntities = async(data) => {
        if (data.entities === undefined) {
            return;
        }

        let entities = data.entities || [];

        // Handle category display setting
        if (data.displayCategories !== 'on') {
            entities = entities.map((entity) => {
                const newEntity = {...entity};
                delete newEntity.category;
                return newEntity;
            });
        }

        try {
            let html, js;
            if (entities.length) {
                const templateName = data.display === 'list' ?
                    'local_resourcelibrary/view-list' :
                    'local_resourcelibrary/view-cards';
                ({html, js} = await Templates.renderForPromise(templateName, {entities: entities}));
            } else {
                const noentitiesimg = wrapper.getAttribute('data-noentitiesimg');
                ({html, js} = await Templates.renderForPromise('local_resourcelibrary/no-entities', {
                    noentitiesimg: noentitiesimg
                }));
            }
            Templates.replaceNodeContents(region, html, js);
        } catch (error) {
            region.innerHTML = '<div class="alert alert-warning">Error loading content</div>';
        }
    };

    State.subscribe('entities', renderEntities);
    State.subscribe('display', renderEntities);
    State.subscribe('displayCategories', renderEntities);
};

/**
 * Pagination template renderer.
 * @param {String} root The root region selector.
 */
const paginationTemplate = (root = 'resourcelibrary') => {
    const app = document.querySelector(`[data-region="${root}"]`);
    if (!app) {
        return;
    }
    const region = app.querySelector('[data-region="pagination"]');
    if (!region) {
        return;
    }

    const renderPagination = async() => {
        const data = State.getData();
        region.innerHTML = '';

        const totalPages = Math.ceil(data.totalItems / data.itemsPerPage);
        const currentPage = data.currentPage;
        const itemsPerPage = data.itemsPerPage;

        // Build pagination context
        const paginationData = {
            currentPage: currentPage,
            totalPages: totalPages,
            itemsPerPage: itemsPerPage,
            totalItems: data.totalItems,
            startItem: ((currentPage - 1) * itemsPerPage) + 1,
            endItem: Math.min(currentPage * itemsPerPage, data.totalItems),
            previousPage: currentPage - 1,
            nextPage: currentPage + 1,
            showPagination: true,
            hasPrevious: currentPage > 1,
            hasNext: currentPage < totalPages,
            pages: [],
            hasitemsPerPageOptions: true,
            itemsPerPageOptions: [12, 24, 48].map(value => ({
                value: value,
                active: value === itemsPerPage
            }))
        };

        // Build page numbers (simple version - show all pages)
        for (let i = 1; i <= totalPages; i++) {
            paginationData.pages.push({
                number: i,
                active: i === currentPage
            });
        }

        try {
            const {html, js} = await Templates.renderForPromise(
                'local_resourcelibrary/simple_pagination',
                paginationData
            );
            Templates.replaceNodeContents(region, html, js);
        } catch (error) {
            // Pagination is optional, don't break if template fails
        }
    };

    // Subscribe only to changes that require pagination re-render
    // Use a single callback to prevent multiple renders
    const subscribeToChanges = () => {
        renderPagination();
    };

    State.subscribe('entities', subscribeToChanges);
    State.subscribe('currentPage', subscribeToChanges);
    State.subscribe('itemsPerPage', subscribeToChanges);
};

/**
 * Loading template renderer.
 * @param {String} root The root region selector.
 */
const loadingTemplate = (root = 'resourcelibrary') => {
    const app = document.querySelector(`[data-region="${root}"]`);
    if (!app) {
        return;
    }
    const region = app.querySelector('[data-region="loading"]');
    if (!region) {
        return;
    }

    const renderLoading = async(data) => {
        if (data.loading === undefined) {
            return;
        }

        if (data.loading) {
            region.innerHTML = '<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';
        } else {
            region.innerHTML = '';
        }
    };

    State.subscribe('loading', renderLoading);
};

const componentInit = () => {
    // Initialize all template renderers
    entitiesTemplate();
    paginationTemplate();
    loadingTemplate();

    // You can add more specific template renderers here
    // stateTemplate('filters', 'filters-panel');
    // stateTemplate('sorting', 'sorting-controls');
};

export default componentInit;
