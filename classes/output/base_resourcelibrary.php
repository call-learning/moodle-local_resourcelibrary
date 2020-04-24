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

use core\message\inbound\handler;
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
    const VIEW_CARD = 'cards';
    const VIEW_LIST = 'list';

    /**
     * Constants for the user paging preferences
     */
    const PAGING_12 = 12;
    const PAGING_24 = 24;
    const PAGING_48 = 48;

    /**
     * Constants for the admin category display setting
     */
    const DISPLAY_CATEGORIES_ON = 'on';
    const DISPLAY_CATEGORIES_OFF = 'off';

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
     * @param string $grouping Grouping user preference
     * @param string $sort Sort user preference
     * @param string $view Display user preference
     * @throws \dml_exception
     */
    public function __construct(
        $sortcolumn = 'fullname',
        $sortorder = 'DESC',
        $view = self::VIEW_CARD,
        $paging = self::PAGING_12) {
        $this->sortcolumn = $sortcolumn;
        $this->sortorder = $sortorder;
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
        $preferences[$this->view] = true;
        $preferences['sortorder'] = $this->sortorder;
        $preferences['sortcolumn'] = $this->sortcolumn;
        return $preferences;
    }

    public function get_export_defaults($output, \core_customfield\handler $handler) {
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
    public abstract function export_for_template(renderer_base $output);

    public function get_filters_content($handler) {
        $filterscontent = (new filter_form(null, ['handler' => $handler],
            'post', '', array('class' => 'resourcelibrary-filters-form')))->render();
        // Now this is still a hack, but impossible to cleanly override the renderers in a local plugin.
        // We will strip the mb.
        return preg_replace('/col-md-[0-9]+/', '', $filterscontent);
    }
}