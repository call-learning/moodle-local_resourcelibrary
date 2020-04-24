Catalog Local plugin
========================

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


Authors
=======
Laurent David - SAS CALL Learning
Camille Carlier - Chargée de mission TICE Ingénieure Pédagogique- DP Pole IRM - Institut Mines-Télécom.


TODO
====
 * Allow ordering of activities by last modification date
 * Further test (unit + behat)
 * Add more information on the thumbnails
 * Check visibility of courses and activities
 * When values are removed from the list, we would need to reindex





 