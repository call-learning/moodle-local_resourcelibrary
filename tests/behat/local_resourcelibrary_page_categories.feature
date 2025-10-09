@local @local_resourcelibrary @core @javascript
Feature: As an admin I should be able to create resource library pages with category filtering

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
    And the following "categories" exist:
      | name       | category | idnumber | visible |
      | Category A | 0        | CATA     | 1       |
      | Category B | 0        | CATB     | 1       |
      | Category C | 0        | CATC     | 1       |
    And the following "courses" exist:
      | shortname | fullname        | category | visible |
      | CA1       | Math Course A1  | CATA     | 1       |
      | CA2       | Science A2      | CATA     | 1       |
      | CA3       | History A3      | CATA     | 1       |
      | CB1       | Math Course B1  | CATB     | 1       |
      | CB2       | Science B2      | CATB     | 1       |
      | CB3       | Art Course B3   | CATB     | 1       |
      | CC1       | Math Course C1  | CATC     | 1       |
      | CC2       | Advanced C2     | CATC     | 1       |
      | CC3       | History C3      | CATC     | 1       |
    And the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value        | courseshortname | activityidnumber | activity |
      | CF1            | 1            | CA1             |                  |          |
      | CF1            | 2            | CA2             |                  |          |
      | CF1            | 3            | CA3             |                  |          |
      | CF1            | 1            | CB1             |                  |          |
      | CF1            | 2            | CB2             |                  |          |
      | CF1            | 4            | CB3             |                  |          |
      | CF1            | 1            | CC1             |                  |          |
      | CF1            | 3            | CC3             |                  |          |
      | CF2            | 1            | CA1             |                  |          |
      | CF2            | 2            | CA2             |                  |          |
      | CF2            | 1            | CA3             |                  |          |
      | CF2            | 2            | CB1             |                  |          |
      | CF2            | 1            | CB2             |                  |          |
      | CF2            | 3            | CB3             |                  |          |
      | CF2            | 3            | CC2             |                  |          |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | CA1    | student |
      | student1 | CB1    | student |
      | student1 | CC1    | student |

  Scenario: Create a resource library page for Category A only and verify course visibility
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to catalogue pages management
    And I click on "Add catalogue page" "link"
    And I set the following fields to these values:
      | Name        | Category A Courses |
    And I expand all fieldsets
    And I set the field "Course categories" to "Category A"
    And I click on "Save changes" "button"
    And I should see "Category A Courses"

    # View the created page to test functionality
    And I view the catalogue page "Category A Courses"
    And I wait until the page is ready

    # Test as student - should only see Category A courses
    And I log out
    And I log in as "student1"
    When I navigate to resource library "Category A Courses" page
    And I wait until the page is ready
    Then I should see the texts "Math Course A1, Science A2, History A3"
    And I should not see the texts "Math Course B1, Science B2, Art Course B3, Math Course C1, Advanced C2, History C3"

  Scenario: Add Category B to the page and verify both categories are visible
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to catalogue pages management
    And I click on "Add catalogue page" "link"
    And I set the following fields to these values:
      | Name        | Multi Category Page |
    And I expand all fieldsets
    And I set the field "Course categories" to "Category A, Category B"
    And I click on "Save changes" "button"
    And I should see "Multi Category Page"

    # View the created page to verify configuration
    And I view the catalogue page "Multi Category Page"
    And I wait until the page is ready

    # Test as student - should see both Category A and B courses
    And I log out
    And I log in as "student1"
    When I navigate to resource library "Multi Category Page" page
    And I wait until the page is ready
    Then I should see the texts "Math Course A1, Science A2, History A3, Math Course B1, Science B2, Art Course B3"
    And I should not see the texts "Math Course C1, Advanced C2, History C3"

  Scenario: Test filtering works only within configured categories
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to catalogue pages management
    And I click on "Add catalogue page" "link"
    And I set the following fields to these values:
      | Name        | Category A and B Filter Test |
    And I expand all fieldsets
    And I set the field "Course categories" to "Category A, Category B"
    And I click on "Save changes" "button"
    And I should see "Category A and B Filter Test"

    # Test filtering as student
    And I log out
    And I log in as "student1"
    And I navigate to resource library "Category A and B Filter Test" page
    And I wait until the page is ready

    # Verify initial state - should see courses from both categories
    And I should see the texts "Math Course A1, Science A2, History A3, Math Course B1, Science B2, Art Course B3"
    And I should not see the texts "Math Course C1, Advanced C2, History C3"

    # Filter by Math subject - should only see Math courses from categories A and B, not C
    And I expand all fieldsets
    And I set the field "Subject" to "Math"
    And I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "Math Course A1, Math Course B1"
    And I should not see the texts "Science A2, History A3, Science B2, Art Course B3, Math Course C1"

    # Filter by Science subject - should only see Science courses from categories A and B
    And I set the field "Subject" to "Science"
    And I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "Science A2, Science B2"
    And I should not see the texts "Math Course A1, History A3, Math Course B1, Art Course B3"

    # Filter by Advanced level - should only see Advanced courses from categories A and B (CB3), not from C
    And I set the field "Subject" to ""
    And I set the field "Level" to "Advanced"
    And I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "Art Course B3"
    And I should not see the texts "Math Course A1, Science A2, History A3, Math Course B1, Science B2, Advanced C2"

  Scenario: Test that unconfigured categories are not visible even with matching filters
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to catalogue pages management
    And I click on "Add catalogue page" "link"
    And I set the following fields to these values:
      | Name        | Category A Only Test |
    And I expand all fieldsets
    And I set the field "Course categories" to "Category A"
    And I click on "Save changes" "button"

    # Test as student with specific filters
    And I log out
    And I log in as "student1"
    And I navigate to resource library "Category A Only Test" page
    And I wait until the page is ready
    And I expand all fieldsets

    # Filter by Math - should only see Math Course A1, not CB1 or CC1
    And I set the field "Subject" to "Math"
    And I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "Math Course A1"
    And I should not see the texts "Math Course B1, Math Course C1"

    # Filter by History - should only see History A3, not CC3
    And I set the field "Subject" to "History"
    And I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "History A3"
    And I should not see the texts "History C3"

    # Clear filters and verify only Category A courses are shown
    And I set the field "Subject" to ""
    And I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "Math Course A1, Science A2, History A3"
    And I should not see the texts "Math Course B1, Science B2, Art Course B3, Math Course C1, Advanced C2, History C3"

  Scenario: Test editing an existing catalogue page
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to catalogue pages management
    And I click on "Add catalogue page" "link"
    And I set the following fields to these values:
      | Name        | Test Edit Page |
    And I expand all fieldsets
    And I set the field "Course categories" to "Category A"
    And I click on "Save changes" "button"
    And I should see "Test Edit Page"

    # Edit the page to add Category B
    And I click on "button[id*='actions-dropdown']" "css_element" in the "Test Edit Page" "table_row"
    And I click on ".dropdown-item[href*='edit.php']" "css_element" in the "Test Edit Page" "table_row"
    And I wait until the page is ready
    And I should see "Edit catalogue page"
    And I expand all fieldsets
    And I set the field "Course categories" to "Category A, Category B"
    And I click on "Save changes" "button"
    And I should see "Test Edit Page"

    # Verify the edited page now shows courses from both categories
    And I view the catalogue page "Test Edit Page"
    And I wait until the page is ready
    And I log out
    And I log in as "student1"
    When I navigate to resource library "Test Edit Page" page
    And I wait until the page is ready
    Then I should see the texts "Math Course A1, Science A2, History A3, Math Course B1, Science B2, Art Course B3"
    And I should not see the texts "Math Course C1, Advanced C2, History C3"
