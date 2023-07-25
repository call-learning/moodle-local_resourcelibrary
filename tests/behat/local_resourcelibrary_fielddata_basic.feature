@local @local_resourcelibrary @core @javascript
Feature: As an admin I should be able to set and retrieve values from basic custom field

  Background:
    Given the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
    Given the following "local_resourcelibrary > category" exist:
      | component   | area   | name                             |
      | core_course | course | Resource Library: Generic fields |
    Given the following "local_resourcelibrary > field" exist:
      | component             | area         | name                | customfieldcategory              | shortname | type        | configdata                                                                                                          |
      | core_course           | course       | Test Field Text     | Resource Library: Generic fields | CF1       | text        |                                                                                                                     |
      | local_resourcelibrary | coursemodule | Test Field Text     | Resource Library: Generic fields | CM1       | text        |                                                                                                                     |
    And the following "activities" exist:
      | activity | name      | intro     | course | idnumber |
      | page     | PageName1 | PageDesc1 | C1     | PAGE1    |
    Given the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value  | courseshortname | activityidnumber | activity |
      | CF1            | ABCDEF | C1              |                  |          |
      | CM1            | ABCDEF | C1              | PAGE1            | page     |

  Scenario: As an admin if I set a value for a course custom field, then I should be able to retrieve it after.
    Given I am on site homepage
    And I log in as "admin"
    Given I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    Then I wait until the page is ready
    Then I should see "Resource Library: Generic fields"
    Then I should see "Test Field"
    And the field "Test Field" matches value "ABCDEF"
    Then I set the field "Test Field" to "ACDBE"
    And I click on "Save" "button"
    Given I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And the field "Test Field" matches value "ACDBE"

  Scenario: As an admin if I set a value for a course custom field, then I should be able to retrieve it after.
    Given I am on site homepage
    And I log in as "admin"
    Given I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    Then I wait until the page is ready
    Then I should see "Resource Library: Generic fields"
    Then I should see "Test Field"
    And the field "Test Field" matches value "ABCDEF"
    Then I set the field "Test Field" to "ACDBE"
    And I click on "Save" "button"
    Given I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And the field "Test Field" matches value "ACDBE"
