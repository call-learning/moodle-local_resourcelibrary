<?php
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
 * Course resourcelibrary
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourcelibrary\output;

defined('MOODLE_INTERNAL') || die();

use core_customfield\handler;
use local_resourcelibrary\filters\filter_form;
use renderable;
use renderer_base;
use templatable;

/**
 * Base class for activity and course resourcelibrary
 *
 * Strongly inspired from the myoverview block view
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_resourcelibrary implements renderable, templatable {

    /**
     * Constants for the user preferences view options
     */
    /** @var string card view. */
    const VIEW_CARD = 'cards';
    /** @var string list view. */
    const VIEW_LIST = 'list';

    /**
     * Constants for the user paging preferences
     */
    /** @var int  12 items per page. */
    const PAGING_12 = 12;
    /** @var int  24 items per page. */
    const PAGING_24 = 24;
    /** @var int  48 items per page. */
    const PAGING_48 = 48;

    /**
     * Constants for the admin category display setting
     */
    /** @var string  Display category (on|off). */
    const DISPLAY_CATEGORIES_ON = 'on';
    /** @var string  Display category (on|off). */
    const DISPLAY_CATEGORIES_OFF = 'off';
    /** @var string  Sort by fullname ASC. */
    const SORT_FULLNAME_ASC = "fullname,ASC";
    /** @var string  Sort by fullname DESC. */
    const SORT_FULLNAME_DESC = "fullname,DESC";
    /** @var string  Sort by timemodified ASC. */
    const SORT_LASTMODIF_ASC = "timemodified,ASC";
    /** @var string  Sort by timemodified DESC. */
    const SORT_LASTMODIF_DESC = "timemodified,DESC";

    /**
     * Column on which we sort
     *
     * @var string
     */
    protected $sortcolumn;

    /**
     * Order for the sort
     *
     * @var string String matching the sort
     */
    protected $sortorder;

    /**
     * Store the view preference
     *
     * @var string String matching the view/display constants defined in myoverview/lib.php
     */
    protected $view;

    /**
     * Store the paging preference
     *
     * @var string String matching the paging constants defined in myoverview/lib.php
     */
    protected $paging;

    /**
     * Store the display categories config setting
     *
     * @var boolean
     */
    protected $displaycategories;

    /**
     * main constructor.
     * Initialize the user preferences
     *
     * @param string $sort Sort user preference
     * @param string $view Display user preference
     * @param int $paging Paging size
     * @throws \dml_exception
     */
    public function __construct(
        $sort = self::SORT_FULLNAME_ASC,
        $view = self::VIEW_CARD,
        $paging = self::PAGING_12) {

        list($this->sortcolumn, $this->sortorder) = explode(',', $sort);
        $this->view = $view;
        $this->paging = $paging;
        $config = get_config('local_resourcelibrary');
        if (empty($config->displaycategories)) {
            $this->displaycategories = self::DISPLAY_CATEGORIES_OFF;
        } else {
            $this->displaycategories = self::DISPLAY_CATEGORIES_ON;
        }
    }

    /**
     * Get the user preferences to use them in the template
     *
     */
    public function get_preferences() {
        $preferences = [];
        $preferences['view'] = self::VIEW_CARD;
        $preferences['sortorder'] = $this->sortorder;
        $preferences['sortcolumn'] = $this->sortcolumn;

        $sort = get_user_preferences('local_resourcelibrary_user_sort_preference');
        if ($sort) {
            $sortparms = explode(',', $sort);
            $preferences['sortcolumn'] = $sortparms[0];
            $preferences['sortorder'] = $sortparms[1];
        }
        $view = get_user_preferences('local_resourcelibrary_user_view_preference');
        if ($view && in_array($view, [self::VIEW_CARD, self::VIEW_LIST])) {
            $preferences['view'] = $view;
        }

        $paging = get_user_preferences('local_resourcelibrary_user_paging_preference');
        if ($paging) {
            $preferences['paging'] = $paging;
        }
        return $preferences;
    }

    /**
     * Get export defaults
     *
     * @param \core_renderer $output
     * @param handler $handler
     * @return array
     */
    public function get_export_defaults($output, handler $handler) {
        $nocoursesurl = $output->image_url('noentities', 'local_resourcelibrary')->out();
        $defaultvariables = [
            'noentitiesimg' => $nocoursesurl,
            'view' => $this->view,
            'paging' => $this->paging,
            'displaycategories' => $this->displaycategories,
            'entitytype' => $handler->get_area()
        ];
        $defaultvariables['filtersformcontent'] = $this->get_filters_content($handler);
        return $defaultvariables;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     */
    abstract public function export_for_template(renderer_base $output);

    /**
     * Get filters to be displayed
     *
     * @param string $handler
     * @return string|string[]|null
     */
    public function get_filters_content($handler) {
        global $_GET;
        $filterform = new filter_form(null, ['handler' => $handler],
            'post', '', array('class' => 'resourcelibrary-filters-form'));

        $filterscontent = $filterform->render();
        return $filterscontent;
    }
}
