Catalog Local plugin Architectural decisions
============================================

This document is just more or less an implementation note and will document all architectural
decisions.

Custom fields
=============

It was possible to use course custom field to build the catalog but the issue was that
the fields might be confused later with other less specialised types of custom fields.

Downside of this is that the field will not appear in the Course edit form if we don't use
the "$CFG->customscripts" trick.

The two custom fields are defined in the classes/customfields folder. They both derive
from the same trait that will (common_cf_handler):
* Define if we use categories or not
* Edit/View capabilities 

The custom fields are backed up using moodle 2 standard backup procedure and should be
restored the same way.


Filters
=======

The inspiration of the structure of the course/library filter comes from the user
custom field filters (used in the user management pages).
The idea is that you can add a new filter by just adding the relevant class in classes/filters.
So far all the custom field types have a matching filter.

The trickiest one was the multiselect filter because of the combinations that can result
in trying to do a sql query with several items (for example if we choose a filter that has
got option 1 and 2, we need to match it with courses or activities that have either 1 or 2 in the
corresponding filter).

The resourcelibrary_filter_interface defines a bit more operators than necessary but
the idea is to implement them all at some point.


Resource library page
=====================

Strongly inspired from the block_overview display and routines. There is a difficulty though
that the block overview seems to work around but is not really satisfactory.
The issue being is that the API returns a set of course from an index to another one (to do
pagination). The way it was done in the block overview (and in this implementation too) is
that we use a SQL limit statement AND THEN filter the visible entities.
After testing, even if it seems to work ok, it is not the best way to do it. We should
 really fetch all records, push them into a cache eventually, and then filter them. We will
 do the pagination on the filtered records only. This will be implemented in the next release.

Filters are displayed on the left hand side of the page and are managed by javascript
which submit the form and reload only the list on the page.
The javascript calls the API to fetch the course or module lists.
 
Resource Library API
====================

This is also really inpired from the course external API. We skipped the unnecessary parts and kept
the main way it is working adding more complex filters.
As said above it will probably be necessary at some point, in order to simplify the code, to
rework on the query and SQL limit statements.

The code that will just fetch the course or module list with each custom field value
on a separate column is in customfield_utils.
This is a quite complex code that will need to be looked at, maintained and simplified.
There is also a workaround made in get_datafieldcolumn_value_from_field_handler that hopefully
can disappear if the custom field API evolves and provide the neccessary methods.

