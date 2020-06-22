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
        'local_resourcelibrary/repository',
        'core/paged_content_factory',
        'core/pubsub',
        'core/custom_interaction_events',
        'core/notification',
        'core/templates',
        'core_course/events',
        'local_resourcelibrary/selectors',
        'core/paged_content_events',
    ],
    function (
        $,
        Repository,
        PagedContentFactory,
        PubSub,
        CustomEvents,
        Notification,
        Templates,
        CourseEvents,
        Selectors,
        PagedContentEvents
    ) {

        var TEMPLATES = {
            ENTITES_CARDS: 'local_resourcelibrary/view-cards',
            ENTITIES_LIST: 'local_resourcelibrary/view-list',
            NOENTITIES: 'local_resourcelibrary/no-entities'
        };

        var NUMCOURSES_PERPAGE = [12, 24, 48];

        var loadedPages = [];

        var lastPage = 0;

        var lastLimit = 0;

        var namespace = null;

        var currentFilters = [];

        var entityType = 'course';

        var courseId = 0;

        /**
         * Get display modifier values from DOM.
         * This will either change the sorting order, the way we display the cards or list
         * and if we display categories or sections
         * @param {object} root The root element for the entities view.
         * @return {object} display modifier Set.
         */
        var getDisplayModifierValues = function (root) {
            var entityRegion = root.find(Selectors.entityView.region);
            return {
                display: entityRegion.attr('data-display'),
                sort: { column: entityRegion.attr('data-sort-column'), order: entityRegion.attr('data-sort-order') },
                displaycategories: entityRegion.attr('data-displaycategories'),
            };
        };

        // We want the paged content controls below the paged content area.
        // and the controls should be ignored while data is loading.
        var DEFAULT_PAGED_CONTENT_CONFIG = {
            ignoreControlWhileLoading: true,
            controlPlacementBottom: true,
            persistentLimitKey: 'local_resourcelibrary_user_paging_preference'
        };

        /**
         * Get enrolled entities from backend.
         * @param {object} modifiers The display modifier for this view.
         * @param {object} filters The filters for this view.
         * @param {int} limit The number of entities to show.
         * @param {int} offset to start with The number of entities to show.
         * @return {promise} Resolved with an array of entities.
         */
        var getEntities = function (modifiers, filters, limit, offset) {
            if (entityType === 'course') {
                return Repository.getFilteredCourseList({
                    categoryid: 0,
                    sorting: [{ column: modifiers.sort.column, order: modifiers.sort.order}],
                    filters: filters,
                    limit: limit,
                    offset: offset
                });
            } else {
                return Repository.getFilteredModulesList({
                    courseid: courseId,
                    sorting: [{ column: modifiers.sort.column, order: modifiers.sort.order}],
                    filters: filters,
                    limit: limit,
                    offset: offset
                });
            }
        };

        /**
         * Get the paged content container element.
         *
         * @param  {Object} root The entity overview container
         * @param  {Number} index Rendered page index.
         * @return {Object} The rendered paged container.
         */
        var getPagedContentContainer = function (root, index) {
            return root.find('[data-region="paged-content-page"][data-page="' + index + '"]');
        };

        /**
         * Render the dashboard entities.
         *
         * @param {object} root The root element for the entities view.
         * @param {array} pageData containing the page data as setup in LoadPage
         * @return {promise} jQuery promise resolved after rendering is complete.
         */
        var renderEntities = function (root, pageData) {

            var entities = [];
            if (pageData.entities !== undefined) {
                entities = pageData.entities;
            }
            var filters = getDisplayModifierValues(root);

            var currentTemplate = '';
            if (filters.display === 'list') {
                currentTemplate = TEMPLATES.ENTITIES_LIST;
            } else {
                currentTemplate = TEMPLATES.ENTITES_CARDS;
            }

            // Delete the entity category if it is not to be displayed.
            if (filters.displaycategories !== 'on') {
                entities = entities.map(function (entity) {
                    delete entity.category;
                    return entity;
                });
            }

            if (entities.length) {
                return Templates.render(currentTemplate, {
                    entities: entities,
                });
            } else {
                var noentitiesimg = root.find(Selectors.entityView.region).attr('data-noentitiesimg');
                return Templates.render(TEMPLATES.NOENTITIES, {
                    noentitiesimg: noentitiesimg
                });
            }
        };

        /**
         * Return the callback to be passed to the subscribe event
         *
         * @param {Number} limit The paged limit that is passed through the event
         */
        var setLimit = function (limit) {
            this.find(Selectors.entityView.region).attr('data-paging', limit);
        };

        /**
         * Intialise the paged list and cards views on page load.
         * Returns an array of paged contents that we would like to handle here
         *
         * @param {object} root The root element for the entities view
         * @param {string} namespace The namespace for all the events attached
         */
        var registerPagedEventHandlers = function (root, namespace) {
            var event = namespace + PagedContentEvents.SET_ITEMS_PER_PAGE_LIMIT;
            PubSub.subscribe(event, setLimit.bind(root));
        };

        /**
         * Get the maximum item per page
         * @param rootNode
         * @returns {number[]}
         */
        var getItemPerPage = function(rootNode) {
            var itemsPerPage = NUMCOURSES_PERPAGE;
            var pagingLimit = parseInt(rootNode.find(Selectors.entityView.region).attr('data-paging'), 10);
            if (pagingLimit) {
                itemsPerPage = NUMCOURSES_PERPAGE.map(function (value) {
                    var active = false;
                    if (value === pagingLimit) {
                        active = true;
                    }

                    return {
                        value: value,
                        active: active
                    };
                });
            }
            return itemsPerPage;
        };

        /**
         * Intialise the entities list and cards views on page load.
         *
         * @param {object} root The root element for the entities view.
         * @param {object} content The content element for the entities view.
         */
        var initializePagedContent = function (root) {
            namespace = "local_resourcelibrary" + root.attr('id') + "_" + Math.random();

            var itemsPerPage = getItemPerPage(root);


            var modifiers = getDisplayModifierValues(root);
            var config = $.extend({}, DEFAULT_PAGED_CONTENT_CONFIG);
            config.eventNamespace = namespace;

            var pagedContentPromise = PagedContentFactory.createWithLimit(
                itemsPerPage,
                function (pagesData, actions) {
                    var promises = [];

                    pagesData.forEach(function (pageData) {
                        var currentPage = pageData.pageNumber;
                        var limit = pageData.limit;

                        // Reset local variables if limits have changed.
                        if (lastLimit !== limit) {
                            loadedPages = [];
                            lastPage = 0;
                        }

                        if (lastPage === currentPage) {
                            // If we are on the last page and have it's data then load it from cache.
                            actions.allItemsLoaded(lastPage);
                            promises.push(renderEntities(root, loadedPages[currentPage]));
                            return;
                        }
                        lastLimit = limit;
                        var additionalValues = {};
                        if (entityType !== 'course') {
                            additionalValues =  courseId;
                        }
                        var pagePromise = getEntities(
                            modifiers,
                            currentFilters,
                            limit,
                            limit * (currentPage - 1),
                            additionalValues
                        ).then(function (entities) {
                            // Finished setting up the current page.
                            loadedPages[currentPage] = {
                                entities: entities
                            };
                            // Set the last page to either the current or next page.
                            if (loadedPages[currentPage].entities.length < pageData.limit) {
                                lastPage = currentPage;
                                actions.allItemsLoaded(currentPage);
                            }
                            return renderEntities(root, loadedPages[currentPage]);
                        }).catch(Notification.exception);

                        promises.push(pagePromise);
                    });

                    return promises;
                },
                config
            );

            pagedContentPromise.then(function (html, js) {
                registerPagedEventHandlers(root, namespace);
                return Templates.replaceNodeContents(root.find(Selectors.entityView.region), html, js);
            }).catch(Notification.exception);
        };

        /**
         * Listen to, and handle events for the Resource Library page.
         *
         * @param {Object} root resourcelibrary page
         */
        var registerEventListeners = function (root) {
            CustomEvents.define(root, [
                CustomEvents.events.activate
            ]);
        };

        /**
         * Intialise the entities list and cards views on page load.
         *
         * @param {object} root The root element for the entities view.
         */
        var refresh = function (root) {
            root = $(root);
            loadedPages = [];
            lastPage = 0;
            initializePagedContent(root);
            entityType = root.attr('data-entity-type');
            courseId = parseInt(root.attr('data-parent-id'));
            if (!root.attr('data-init')) {
                registerEventListeners(root);
                root.attr('data-init', true);
            }
        };
        var init = function (root) {
            this.refresh(root);
            // Reset the views when we receive a change in filters.
            $(document).on('resourcelibrary-filters-change',
                function(e, formdata) {
                    currentFilters = formdata;
                    this.refresh(root);
                }.bind(this)
            );
        };


        /**

         * Reset the entities views to their original
         * state on first page load.entityOffset
         *
         * This is called when configuration has changed for the event lists
         * to cause them to reload their data.
         *
         * @param {Object} root The root element for the timeline view.
         */
        var reset = function (root) {
            if (loadedPages.length > 0) {
                loadedPages.forEach(function (entityList, index) {
                    var pagedContentPage = getPagedContentContainer(root, index);
                    renderEntities(root, entityList).then(function (html, js) {
                        return Templates.replaceNodeContents(pagedContentPage, html, js);
                    }).catch(Notification.exception);
                });
            } else {
                refresh(root);
            }
        };

        return {
            init: init,
            reset: reset,
            refresh: refresh,
        };
    });
