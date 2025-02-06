@local @local_resourcelibrary @core @javascript
Feature: As an admin I should be able to set and retrieve values from basic custom field

  Background:
    Given the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
    And the following config values are set as admin:
      | config                | value |
      | enableresourcelibrary | 1     |
    And the following "local_resourcelibrary > category" exist:
      | component   | area   | name                             |
      | core_course | course | Resource Library: Generic fields |
    And the following "local_resourcelibrary > field" exist:
      | component             | area         | name                | customfieldcategory              | shortname | type        | configdata                                                                                                          |
      | core_course           | course       | Test Field Text     | Resource Library: Generic fields | CF1       | text        |                                                                                                                     |
    And the following "activities" exist:
      | activity | name      | intro     | course | idnumber |
      | page     | PageName1 | PageDesc1 | C1     | PAGE1    |
    And the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value  | courseshortname | activityidnumber | activity |
      | CF1            | ABCDEF | C1              |                  |          |

  Scenario: As an admin if I set a value for a course custom field, then I should be able to retrieve it after.
    Given I am on site homepage
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I wait until the page is ready
    And I should see "Resource Library: Generic fields"
    And I should see "Test Field"
    And the field "Test Field" matches value "ABCDEF"
    And I set the field "Test Field" to "ACDBE"
    And I click on "Save" "button"
    And I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    When I expand all fieldsets
    Then the field "Test Field" matches value "ACDBE"
