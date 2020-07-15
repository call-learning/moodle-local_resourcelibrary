Resource Library Local plugin
=============================

[![Build Status](https://travis-ci.org/call-learning/moodle-local_resourcelibrary.svg?branch=master)](https://travis-ci.org/call-learning/moodle-local_resourcelibrary)

This plugin adds new customs fields (using the new customfield API in Moodle 3.7) to Courses and Activities so they can be searched and classified.
The customfields are then used to filter courses and activities on a catalog page.

The plugin has been developed for Institut Mines Telecom for its ([Pedagothèque Numérique](https://www.imt.fr/formation/academie-transformations-educatives/ressources-pedagogiques/pedagotheque-numerique/)),
a course, teaching and learning activities catalog.

Installation
============

Add plugin code into the moodle local folder and run an update/upgrade.  You should now see a new plugin and new menus under the Administration > Courses menu.
If you want to edit the course resource library field directly into the course edit form, add the following to your config.php file:

    $CFG->customscripts = dirname(__FILE__) . '/local/resourcelibrary/customscripts/';

Usage
=====

The plugin will add a menu under Administration > Courses menu so two new types of custom fields can be added:
* custom field for courses
* custom fields for activities

Those custom fields will then be taken into account in the resource library page and each field will have its related search form entry.

The plugin will also add a new navigation menu called "Resources Library" that will list
all available courses. If you need to make this page accessible through non logged in users, make sure
you set the "autologin" to on in the Administration > Site administration > Users > Permissions > User policies 
(See [Auto Login Guest](https://docs.moodle.org/39/en/Guest_access)). If not a login prompt will appear to see the page.

If you need to hide courses regardless of the course visibility status, you can do so
by adding the course ids in the 'hiddencoursesid' settings. The course will not appear in the
 resource library.
This is a temporary solution while looking at more generic solution such as hiding a course per category, tag or other.
 
Authors
=======
Project initiated and produced by DP Pole IRM - Institut Mines-Télécom.
Realised by Laurent David - SAS CALL Learning

TODO
====
 * Allow ordering of activities by last modification date
 * Add more information on the thumbnails
 * Check visibility of courses and activities
 * Add tags to courses and module to mark them as invisible on the resource library
 





 