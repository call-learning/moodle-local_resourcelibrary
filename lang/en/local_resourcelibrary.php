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
 * Plugin to manage Resource Library
 *
 * @link https://www.imt.fr/formation/academie-transformations-educatives/ressources-pedagogiques/pedagotheque-numerique/
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Resource Library';

$string['activity_metadata'] = 'Metatdata for Resource Library';
$string['aria:card'] = 'Switch to card view';
$string['aria:controls'] = 'Display controls';
$string['aria:displaydropdown'] = 'Display drop-down menu';
$string['aria:sortingdropdown'] = 'Sorting drop-down menu';
$string['aria:list'] = 'Switch to list view';
$string['aria:lastmodification'] = 'Sort by last modification date';
$string['aria:lastmodification:desc'] = 'Sort by last modification date (Descending)';
$string['aria:title'] = 'Full name';
$string['aria:title:desc'] = 'Full name (Descending)';
$string['card'] = 'Card';
$string['copied'] = 'Copied to clipboard';
$string['course_metadata'] = 'Metatdata for course Resource Library';
$string['hiddenfromstudents'] = 'Closed';
$string['mainresourcelibrary'] = 'Main library';
$string['manage:hiddenfilter'] = 'Hidden in Filter?';

$string['resourcelibrary'] = 'Resource library';
$string['resourcelibrary:menutextoverride'] = 'Text for menu/link';
$string['resourcelibrary:menutextoverride:desc'] = 'Text for ressource library menu/link, defaults to "resourcelibrary". We define
 one for each language for example {$a}';
$string['resourcelibrary:replacecourseindex'] = 'Replace course index with the Ressource Library page.';
$string['resourcelibrary:replacecourseindex:desc'] = 'Replace course index with the Ressourcelibrary page.';

$string['resourcelibraryfieldsettings'] = 'Resource Library Field Settings';
$string['resourcelibrary_course_customfield'] = 'Manage custom Resource Library field for course';
$string['resourcelibrary_coursemodule_customfield'] = 'Manage custom Resource Library field for course modules';
$string['resourcelibraryfield_islocked'] = 'Field is locked';
$string['resourcelibraryfield_islocked_help'] = 'Field is locked';
$string['resourcelibraryfield_visibletoall'] = 'Field is visible to all';
$string['resourcelibraryfield_islocked_help'] = 'Field is visible to all';
$string['resourcelibraryfield_visibletoteachers'] = 'Resource Library Field is visible to teachers';
$string['resourcelibraryfield_visibletoteachers_help'] = 'Resource Library Field is visible to teachers';
$string['resourcelibraryfield_visibility'] = 'Resource Library Field visibility';
$string['resourcelibraryfield_visibility_help'] = 'Resource Library Field visibility';
$string['resourcelibraryfield_notvisible'] = 'Not visible';
$string['resourcelibraryfield_notvisible'] = 'Resource Library Field not visible';
$string['resourcelibrary:courseviewbaseurl'] = 'Base URL for course view.';
$string['resourcelibrary:courseviewbaseurl:desc'] = 'Base URL for course view. Most of the time it will be /course/view.php.'
    .' We add the identifier \'id\' to the URL with the id of the course.';
$string['resourcelibrarymainsettings'] = 'Resource Library: Global Settings';
$string['resourcelibrary:hiddencoursesid'] = 'Hidden courses Id';
$string['resourcelibrary:hiddencoursesid:desc'] = 'List of comma separated
 courses identifiers (course id) that will be invisible on the catalog';

$string['category:general'] = 'Resource Library: Generic fields';

$string['enableresourcelibrary'] = 'Enable Resource Library';
$string['filters'] = 'Filters';
$string['filter:anyvalue'] = 'Any';
$string['filter:submit'] = 'Filter';
$string['resourcelibrary:activateactivitylibrary'] = 'Activate Activity library';
$string['resourcelibrary:activateactivitylibrary:desc'] =
    'The activity library is similar to the course library but for activities';
$string['resourcelibrary:manage'] = 'Can manage Resource Library';
$string['resourcelibrary:managefields'] = 'Can manage Resource Library Fields';
$string['resourcelibrary:editvalue'] = 'Can edit Resource Library Custom Field values';
$string['resourcelibrary:configurecustomfields'] = 'Can configure Resource Library Custom Field values';
$string['resourcelibrary:changelockedcustomfields'] = 'Can change locked Resource Library Custom Field values';
$string['resourcelibrary:setitemsvisibility'] = 'Can set items visibility';
$string['settingvisibilitynotallowed'] = 'Setting visibility is not allowed';
$string['resourcelibrary:view'] = 'Can view locked Resource Library Custom Field values';
$string['showincatalogue'] = 'Show in catalogue';
$string['hidefromcatalogue'] = 'Hide from catalogue';
$string['list'] = 'List';
$string['lastmodification'] = 'Last modification';
$string['lastmodification:desc'] = 'Last modification (Descending)';
$string['noentities'] = 'Nothing found ! Please select another filter.';
$string['operator:instructions:greaterthan'] = 'Greater Than';
$string['privacy:metadata:resourcelibrarypagingpreference'] = 'Paging preference';
$string['privacy:metadata:resourcelibraryviewpreference'] = 'View (List/Card) preference';
$string['privacy:metadata:resourcelibrarysortpreference'] = 'Sort preference';
$string['permalink:copy'] = 'Copy';
$string['title'] = 'Full name';
$string['title:desc'] = 'Full name (Descending)';
$string['viewitem'] = 'View';
$string['wronghandlerforfilter'] = 'Wrong handler {$a->handlername} for filter {$a->filtername}';
