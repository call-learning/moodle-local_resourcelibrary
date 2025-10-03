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
 * A reactive state class that stores the data for the Resource Library.
 *
 * @module     local_resourcelibrary/local/state
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Repository from 'local_resourcelibrary/repository';

/**
 * A simple state class that stores the data for the Resource Library.
 * Classes can subscribe to this class to get updates.
 */
class State {

    /**
     * Constructor.
     */
    constructor() {
        this.data = {
            entities: [],
            currentPage: 1,
            itemsPerPage: 12,
            totalItems: 0, // Will be set correctly after API call
            loading: false,
            filters: [],
            sorting: [{column: 'fullname', order: 'ASC'}],
            pageId: 0,
            categoryId: 0, // Keep for backward compatibility
            entityType: 'course',
            courseId: 0,
            display: 'cards',
            displayCategories: 'off'
        };
        this.subscribers = [];
    }

    /**
     * Set the data.
     * @param {Object} data The data.
     * @return {Promise} The promise.
     */
    setData(data) {
        return new Promise((resolve) => {
            Object.assign(this.data, data);
            this.notifySubscribers();
            this.debug();
            resolve();
        });
    }

    /**
     * Return the current state instance.
     * @return {Object} The state data.
     */
    getInstance() {
        return this.data;
    }

    /**
     * Set a single value.
     * @param {String} key The key.
     * @param {*} value The value.
     * @return {Promise} The promise.
     */
    async setValue(key, value) {
        return new Promise((resolve) => {
            this.data[key] = value;
            this.notifySubscriber(key);
            this.debug();
            resolve();
        });
    }

    /**
     * Get a single value.
     * @param {String} key The key.
     * @return {*} The value.
     */
    getValue(key) {
        return this.data[key];
    }

    /**
     * Get the data.
     * @return {Object} The data.
     */
    getData() {
        return this.data;
    }

    /**
     * Load entities for the current page.
     * @return {Promise} The promise.
     */
    async loadCurrentPage() {
        // Prevent recursive calls
        if (this.data.loading) {
            return;
        }

        await this.setValue('loading', true);

        try {
            // Get ALL entities from API (since API pagination is broken)
            const allEntities = await Repository.getFilteredCourseList({
                pageid: this.data.pageId,
                categoryid: this.data.categoryId, // Keep for backward compatibility
                sorting: this.data.sorting,
                filters: this.data.filters,
                limit: 0, // Get all
                offset: 0
            });

            // Implement client-side pagination
            const offset = (this.data.currentPage - 1) * this.data.itemsPerPage;
            const entities = allEntities.slice(offset, offset + this.data.itemsPerPage);

            await this.setData({
                entities: entities,
                loading: false,
                totalItems: allEntities.length // Exact count from API
            });
        } catch (error) {
            await this.setData({
                entities: [],
                loading: false,
                totalItems: 0
            });
            throw error;
        }
    }

    /**
     * Go to a specific page.
     * @param {Number} page The page number.
     * @return {Promise} The promise.
     */
    async goToPage(page) {
        if (page < 1) {
            return Promise.reject('Invalid page number');
        }
        await this.setValue('currentPage', page);
        return this.loadCurrentPage();
    }

    /**
     * Set items per page and reload.
     * @param {Number} itemsPerPage The number of items per page.
     * @return {Promise} The promise.
     */
    async setItemsPerPage(itemsPerPage) {
        await this.setData({
            itemsPerPage: itemsPerPage,
            currentPage: 1
        });
        return this.loadCurrentPage();
    }

    /**
     * Set filters and reload.
     * @param {Array} filters The filters array.
     * @return {Promise} The promise.
     */
    async setFilters(filters) {
        await this.setData({
            filters: filters,
            currentPage: 1
        });
        return this.loadCurrentPage();
    }

    /**
     * Set sorting and reload.
     * @param {Array} sorting The sorting array.
     * @return {Promise} The promise.
     */
    async setSorting(sorting) {
        await this.setData({
            sorting: sorting,
            currentPage: 1
        });
        return this.loadCurrentPage();
    }

    /**
     * Subscribe to the state.
     * @param {String} key The key.
     * @param {Function} callback The callback.
     */
    subscribe(key, callback) {
        if (typeof key !== 'string') {
            throw new Error('The key must be a string');
        }
        if (typeof callback !== 'function') {
            throw new Error('The callback must be a function');
        }

        // Check if the key is already subscribed, with the same callback.
        const exists = this.subscribers.find(subscriber => subscriber.key === key && subscriber.callback === callback);
        if (exists) {
            return;
        }
        this.subscribers.push({key, callback});
    }

    /**
     * Unsubscribe from the state.
     * @param {Function} callback The callback.
     */
    unsubscribe(callback) {
        this.subscribers = this.subscribers.filter(subscriber => subscriber.callback !== callback);
    }

    /**
     * Notify the subscribers, but only if the data key exists or has changed.
     */
    notifySubscribers() {
        this.subscribers.forEach(subscriber => {
            if (this.data[subscriber.key] !== undefined) {
                subscriber.callback(this.data);
            }
        });
    }

    /**
     * Notify a single subscriber.
     * @param {String} key The key.
     */
    notifySubscriber(key) {
        const subscribers = this.subscribers.filter(subscriber => subscriber.key === key);
        if (subscribers.length > 0) {
            subscribers.forEach(subscriber => {
                subscriber.callback(this.data);
            });
        }
    }

    /**
     * Debugging function.
     */
    debug() {
        const debugRegion = document.getElementById('debug');
        if (debugRegion) {
            debugRegion.innerHTML = JSON.stringify(this.data, null, 2);
        }
    }
}

const state = new State();
export default state;
