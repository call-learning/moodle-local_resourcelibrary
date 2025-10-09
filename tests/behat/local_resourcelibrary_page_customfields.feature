@local @local_resourcelibrary @core @javascript
Feature: As an admin I should be able to configure custom fields for catalogue pages

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
    And the following config values are set as admin:
      | config                | value |
      | enableresourcelibrary | 1     |
    And the following "local_resourcelibrary > category" exist:
      | component   | area   | name                             |
      | core_course | course | Resource Library: Generic fields |
    And the following "local_resourcelibrary > field" exist:
      | component   | area   | name        | customfieldcategory              | shortname | type   | configdata                                                                                                          |
      | core_course | course | Subject     | Resource Library: Generic fields | CF1       | select | {"required":"0","uniquevalues":"0","options":"Math\r\nScience\r\nHistory\r\nArt","defaultvalue":"","locked":"0","visibility":"2"} |
      | core_course | course | Level       | Resource Library: Generic fields | CF2       | select | {"required":"0","uniquevalues":"0","options":"Beginner\r\nIntermediate\r\nAdvanced","defaultvalue":"","locked":"0","visibility":"2"} |
      | core_course | course | Duration    | Resource Library: Generic fields | CF3       | select | {"required":"0","uniquevalues":"0","options":"Short\r\nMedium\r\nLong","defaultvalue":"","locked":"0","visibility":"2"} |
      | core_course | course | Rating      | Resource Library: Generic fields | CF4       | select | {"required":"0","uniquevalues":"0","options":"1 Star\r\n2 Stars\r\n3 Stars\r\n4 Stars\r\n5 Stars","defaultvalue":"","locked":"0","visibility":"2"} |
    And the following "categories" exist:
      | name       | category | idnumber | visible |
      | Category A | 0        | CATA     | 1       |
      | Category B | 0        | CATB     | 1       |
    And the following "courses" exist:
      | shortname | fullname        | category | visible |
      | CA1       | Math Course A1  | CATA     | 1       |
      | CA2       | Science A2      | CATA     | 1       |
      | CA3       | History A3      | CATA     | 1       |
      | CB1       | Math Course B1  | CATB     | 1       |
      | CB2       | Science B2      | CATB     | 1       |
      | CB3       | Art Course B3   | CATB     | 1       |
    And the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value        | courseshortname | activityidnumber | activity |
      | CF1            | 1            | CA1             |                  |          |
      | CF1            | 2            | CA2             |                  |          |
      | CF1            | 3            | CA3             |                  |          |
      | CF1            | 1            | CB1             |                  |          |
      | CF1            | 2            | CB2             |                  |          |
      | CF1            | 4            | CB3             |                  |          |
      | CF2            | 1            | CA1             |                  |          |
      | CF2            | 2            | CA2             |                  |          |
      | CF2            | 1            | CA3             |                  |          |
      | CF2            | 2            | CB1             |                  |          |
      | CF2            | 2            | CB2             |                  |          |
      | CF2            | 3            | CB3             |                  |          |
      | CF3            | 1            | CA1             |                  |          |
      | CF3            | 2            | CA2             |                  |          |
      | CF3            | 3            | CA3             |                  |          |
      | CF3            | 1            | CB1             |                  |          |
      | CF3            | 2            | CB2             |                  |          |
      | CF3            | 3            | CB3             |                  |          |
      | CF4            | 1            | CA1             |                  |          |
      | CF4            | 2            | CA2             |                  |          |
      | CF4            | 3            | CA3             |                  |          |
      | CF4            | 4            | CB1             |                  |          |
      | CF4            | 5            | CB2             |                  |          |
      | CF4            | 1            | CB3             |                  |          |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | CA1    | student |
      | student1 | CB1    | student |
      | student1 | CA2    | student |

  Scenario: Create a catalogue page with selected custom fields and verify field visibility
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to catalogue pages management
    And I click on "Add catalogue page" "link"
    And I set the following fields to these values:
      | Name        | Custom Fields Test Page |
    And I expand all fieldsets
    And I set the field "Course categories" to "Category A, Category B"
    And I set the field "Custom fields" to "Subject, Level"
    And I click on "Save changes" "button"
    And I should see "Custom Fields Test Page"

    # Test as student - should see configured custom fields after clicking Show more
    And I log out
    And I log in as "student1"
    When I navigate to resource library "Custom Fields Test Page" page
    And I wait until the page is ready
    And I wait "2" seconds
    And I click on ".moreless-toggler" "css_element"
    And I wait until the page is ready
    Then I should see "Subject"
    And I should see "Level"
    And I should not see "Duration"
    And I should not see "Rating"

  Scenario: Test custom field filtering functionality with configured fields
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to catalogue pages management
    And I click on "Add catalogue page" "link"
    And I set the following fields to these values:
      | Name        | Subject and Level Filter Page |
    And I expand all fieldsets
    And I set the field "Course categories" to "Category A, Category B"
    And I set the field "Custom fields" to "Subject, Level"
    And I click on "Save changes" "button"
    And I should see "Subject and Level Filter Page"

    # Test filtering by Subject field
    And I log out
    And I log in as "student1"
    And I navigate to resource library "Subject and Level Filter Page" page
    And I wait until the page is ready
    And I wait "2" seconds
    And I click on ".moreless-toggler" "css_element"
    And I wait until the page is ready
    And I set the field "Subject" to "Math"
    And I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "Math Course A1, Math Course B1"
    And I should not see the texts "Science A2, History A3, Science B2, Art Course B3"

    # Test filtering by Level field
    And I set the field "Subject" to ""
    And I set the field "Level" to "Advanced"
    And I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "Art Course B3"
    And I should not see the texts "Math Course A1, Science A2, History A3, Math Course B1, Science B2"

    # Test combined filtering (Subject + Level)
    And I set the field "Subject" to "Science"
    And I set the field "Level" to "Intermediate"
    And I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "Science A2, Science B2"
    And I should not see the texts "Math Course A1, History A3, Math Course B1, Art Course B3"

  Scenario: Test that unconfigured custom fields are not visible or functional
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to catalogue pages management
    And I click on "Add catalogue page" "link"
    And I set the following fields to these values:
      | Name        | Limited Fields Page |
    And I expand all fieldsets
    And I set the field "Course categories" to "Category A, Category B"
    And I set the field "Custom fields" to "Subject"
    And I click on "Save changes" "button"
    And I should see "Limited Fields Page"

    # Test that only configured fields appear
    And I log out
    And I log in as "student1"
    And I navigate to resource library "Limited Fields Page" page
    And I wait until the page is ready
    And I wait "2" seconds
    And I click on ".moreless-toggler" "css_element"
    And I wait until the page is ready
    Then I should see "Subject"
    And I should not see "Level"
    And I should not see "Duration"
    And I should not see "Rating"

    # Test that filtering works only for configured field
    And I set the field "Subject" to "History"
    And I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "History A3"
    And I should not see the texts "Math Course A1, Science A2, Math Course B1, Science B2, Art Course B3"

  Scenario: Test editing a catalogue page to add/remove custom fields
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to catalogue pages management
    And I click on "Add catalogue page" "link"
    And I set the following fields to these values:
      | Name        | Edit Custom Fields Page |
    And I expand all fieldsets
    And I set the field "Course categories" to "Category A"
    And I set the field "Custom fields" to "Subject"
    And I click on "Save changes" "button"
    And I should see "Edit Custom Fields Page"

    # Edit the page to add more custom fields
    And I click on "button[id*='actions-dropdown']" "css_element" in the "Edit Custom Fields Page" "table_row"
    And I click on ".dropdown-item[href*='edit.php']" "css_element" in the "Edit Custom Fields Page" "table_row"
    And I wait until the page is ready
    And I expand all fieldsets
    And I set the field "Custom fields" to "Subject, Level, Duration"
    And I click on "Save changes" "button"
    And I should see "Edit Custom Fields Page"

    # Verify the updated fields are now visible
    And I log out
    And I log in as "student1"
    And I navigate to resource library "Edit Custom Fields Page" page
    And I wait until the page is ready
    And I wait "2" seconds
    And I click on ".moreless-toggler" "css_element"
    And I wait until the page is ready
    Then I should see "Subject"
    And I should see "Level"
    And I should see "Duration"
    And I should not see "Rating"

    # Test that all configured fields work for filtering
    And I set the field "Duration" to "Long"
    And I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "History A3"
    And I should not see the texts "Math Course A1, Science A2"

  Scenario: Test catalogue page with all custom fields configured
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to catalogue pages management
    And I click on "Add catalogue page" "link"
    And I set the following fields to these values:
      | Name        | All Fields Page |
    And I expand all fieldsets
    And I set the field "Course categories" to "Category A, Category B"
    And I set the field "Custom fields" to "Subject, Level, Duration, Rating"
    And I click on "Save changes" "button"
    And I should see "All Fields Page"

    # Test that all fields are visible
    And I log out
    And I log in as "student1"
    And I navigate to resource library "All Fields Page" page
    And I wait until the page is ready
    And I wait "2" seconds
    And I click on ".moreless-toggler" "css_element"
    And I wait until the page is ready
    Then I should see "Subject"
    And I should see "Level"
    And I should see "Duration"
    And I should see "Rating"

    # Test complex multi-field filtering
    And I set the field "Subject" to "Math"
    And I set the field "Level" to "Beginner"
    And I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "Math Course A1"
    And I should not see the texts "Math Course B1, Science A2, History A3, Science B2, Art Course B3"
