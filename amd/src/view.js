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
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import * as Repository from 'local_resourcelibrary/repository';
import PagedContentFactory from 'core/paged_content_factory';
import PubSub from 'core/pubsub';
import Notification from 'core/notification';
import Templates from 'core/templates';
import * as Selectors from 'local_resourcelibrary/selectors';
import PagedContentEvents from 'core/paged_content_events';

export default class View {
    static TEMPLATES = {
        ENTITES_CARDS: 'local_resourcelibrary/view-cards',
        ENTITIES_LIST: 'local_resourcelibrary/view-list',
        NOENTITIES: 'local_resourcelibrary/no-entities'
    };

    static NUMCOURSES_PERPAGE = [12, 24, 48];

    static DEFAULT_PAGED_CONTENT_CONFIG = {
        ignoreControlWhileLoading: true,
        controlPlacementBottom: true,
        persistentLimitKey: 'local_resourcelibrary_user_paging_preference'
    };

    static loadedPages = [];
    static lastPage = 0;
    static lastLimit = 0;
    static namespace = null;
    static currentFilters = [];
    static entityType = 'course';
    static courseId = 0;
    static categoryId = 0;

    static getDisplayModifierValues(root) {
        const entityRegion = root.querySelector(Selectors.entityView.region);
        if (!entityRegion) {
            throw new Error('Entity region not found in the provided root element.');
        }
        return {
            display: entityRegion.getAttribute('data-display'),
            sort: {
                column: entityRegion.getAttribute('data-sort-column'),
                order: entityRegion.getAttribute('data-sort-order')
            },
            displaycategories: entityRegion.getAttribute('data-displaycategories'),
        };
    }

    static getEntities(modifiers, filters, limit, offset, additionalValues = {}) {
        if (View.entityType === 'course') {
            return Repository.getFilteredCourseList({
                categoryid: View.categoryId,
                sorting: [{column: modifiers.sort.column, order: modifiers.sort.order}],
                filters: filters,
                limit: limit,
                offset: offset,
                ...additionalValues
            });
        }
        return Promise.resolve([]);
    }

    static getPagedContentContainer(root, index) {
        return root.querySelector(`[data-region="paged-content-page"][data-page="${index}"]`);
    }

    static renderEntities(root, pageData) {
        let entities = [];
        if (pageData.entities !== undefined) {
            entities = pageData.entities;
        }
        const filters = View.getDisplayModifierValues(root);

        let currentTemplate = '';
        if (filters.display === 'list') {
            currentTemplate = View.TEMPLATES.ENTITIES_LIST;
        } else {
            currentTemplate = View.TEMPLATES.ENTITES_CARDS;
        }

        if (filters.displaycategories !== 'on') {
            entities = entities.map((entity) => {
                delete entity.category;
                return entity;
            });
        }

        if (entities.length) {
            return Templates.render(currentTemplate, {
                entities: entities,
            });
        } else {
            const entityRegion = root.querySelector(Selectors.entityView.region);
            if (!entityRegion) {
                throw new Error('Entity region not found in the provided root element.');
            }
            const noentitiesimg = entityRegion.getAttribute('data-noentitiesimg');
            return Templates.render(View.TEMPLATES.NOENTITIES, {
                noentitiesimg: noentitiesimg
            });
        }
    }

    static setLimit(limit) {
        const root = this;
        const entityRegion = root.querySelector(Selectors.entityView.region);
        if (entityRegion) {
            entityRegion.setAttribute('data-paging', limit);
        }
    }

    static registerPagedEventHandlers(root, namespace) {
        const event = namespace + PagedContentEvents.SET_ITEMS_PER_PAGE_LIMIT;
        if (PubSub && typeof PubSub.subscribe === 'function') {
            PubSub.subscribe(event, View.setLimit.bind(root));
        }
    }

    static getItemPerPage(rootNode) {
        let itemsPerPage = View.NUMCOURSES_PERPAGE;
        const entityRegion = rootNode.querySelector(Selectors.entityView.region);
        if (!entityRegion) {
            throw new Error('Entity region not found in the provided root element.');
        }
        const pagingLimit = parseInt(entityRegion.getAttribute('data-paging'), 10);
        if (pagingLimit) {
            itemsPerPage = View.NUMCOURSES_PERPAGE.map((value) => {
                const active = value === pagingLimit;
                return {
                    value: value,
                    active: active
                };
            });
        }
        return itemsPerPage;
    }

    static initializePagedContent(root) {
        if (!(root instanceof HTMLElement)) {
            throw new Error('Invalid root element provided. It must be a valid CSS selector or an HTMLElement.');
        }
        View.namespace = "local_resourcelibrary" + root.getAttribute('id') + "_" + Math.random();

        const itemsPerPage = View.getItemPerPage(root);
        const modifiers = View.getDisplayModifierValues(root);
        const config = Object.assign({}, View.DEFAULT_PAGED_CONTENT_CONFIG);
        config.eventNamespace = View.namespace;

        const pagedContentPromise = PagedContentFactory.createWithLimit(
            itemsPerPage,
            (pagesData, actions) => {
                const promises = [];

                pagesData.forEach((pageData) => {
                    const currentPage = pageData.pageNumber;
                    const limit = pageData.limit;

                    if (View.lastLimit !== limit) {
                        View.loadedPages = [];
                        View.lastPage = 0;
                    }

                    if (View.lastPage === currentPage) {
                        actions.allItemsLoaded(View.lastPage);
                        promises.push(View.renderEntities(root, View.loadedPages[currentPage]));
                        return;
                    }
                    View.lastLimit = limit;
                    let additionalValues = {};
                    if (View.entityType !== 'course') {
                        additionalValues.courseId = View.courseId;
                    }
                    const pagePromise = View.getEntities(
                        modifiers,
                        View.currentFilters,
                        limit,
                        limit * (currentPage - 1),
                        additionalValues
                    ).then((entities) => {
                        View.loadedPages[currentPage] = {
                            entities: entities
                        };
                        if (View.loadedPages[currentPage].entities.length < pageData.limit) {
                            View.lastPage = currentPage;
                            actions.allItemsLoaded(currentPage);
                        }
                        return View.renderEntities(root, View.loadedPages[currentPage]);
                    }).catch(Notification.exception);

                    promises.push(pagePromise);
                });

                return promises;
            },
            config
        );

        pagedContentPromise.then((html, js) => {
            View.registerPagedEventHandlers(root, View.namespace);
            return Templates.replaceNodeContents(root.querySelector(Selectors.entityView.region), html, js);
        }).then(() => {
            const rootNode = document.querySelector(Selectors.entityView.region + ' .paged-content-page-container');
            if (!rootNode) {
                return;
            }

            const waitForNodeReplacement = (mutationsList) => {
                if (mutationsList) {
                    mutationsList.forEach((mutation) => {
                        if (mutation.type === 'childList') {
                            const event = new CustomEvent('resource_library_card_rendered', {
                                detail: {rootNode: rootNode}
                            });
                            const haspagecontent = document.querySelector(Selectors.entityView.region
                                + ' .paged-content-page-container [data-region="paged-content-page"]');
                            if (haspagecontent) {
                                document.dispatchEvent(event);
                            }
                        }
                    });
                }
            };
            const observer = new MutationObserver(waitForNodeReplacement);
            const config = {childList: true, subtree: true};
            observer.observe(rootNode, config);
            return;
        }).catch(Notification.exception);
    }

    static refresh(root) {
        if (typeof root === 'string') {
            root = document.querySelector(root);
        }
        if (!(root instanceof HTMLElement)) {
            throw new Error('Invalid root element provided. It must be a valid CSS selector or an HTMLElement.');
        }
        View.loadedPages = [];
        View.lastPage = 0;
        View.initializePagedContent(root);
        View.entityType = root.getAttribute('data-entity-type');
        View.courseId = parseInt(root.getAttribute('data-parent-id'));
        View.categoryId = parseInt(root.getAttribute('data-category-id'));
        if (!root.getAttribute('data-init')) {
            root.setAttribute('data-init', 'true');
        }
    }

    static init(root) {
        document.addEventListener('resourcelibrary-filters-inited', (e) => {
            View.currentFilters = e.detail;
            View.refresh(root);
        });

        document.addEventListener('resourcelibrary-filters-change', (e) => {
            View.currentFilters = e.detail;
            View.refresh(root);
        });
    }

    static reset(root) {
        if (typeof root === 'string') {
            root = document.querySelector(root);
        }
        if (!(root instanceof HTMLElement)) {
            throw new Error('Invalid root element provided. It must be a valid CSS selector or an HTMLElement.');
        }
        if (View.loadedPages.length > 0) {
            View.loadedPages.forEach((entityList, index) => {
                const pagedContentPage = View.getPagedContentContainer(root, index);
                if (pagedContentPage) {
                    View.renderEntities(root, entityList).then((html, js) => {
                        return Templates.replaceNodeContents(pagedContentPage, html, js);
                    }).catch(Notification.exception);
                }
            });
        } else {
            View.refresh(root);
        }
    }
}

