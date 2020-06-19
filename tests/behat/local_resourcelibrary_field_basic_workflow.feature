@local_resourcelibrary @core @javascript
Feature: As an admin I should be able to set and retrieve values from custom field

  Background:
    Given the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
    Given the following "local_resourcelibrary > field" exist:
      | area         | name       | customfieldcategory |
      | course       | Test Field | Resource Library: Generic fields |
      | coursemodule | Test Field | Resource Library: Generic fields |
    And the following "activities" exist:
      | activity | name      | intro     | course | idnumber |
      | page     | PageName1 | PageDesc1 | C1     | PAGE1    |

  Scenario: As an admin if I set a value for a custom field, then I should be able to retrieve it after.
    Given I am on "Course 1" course homepage
    And I turn editing mode on
    And I open "PageName1" actions menu
    And I click on "Edit settings" "link" in the "PageName1" activity
    And I expand all fieldsets
    Then I should not see "Test Field"
    Given I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    Then I should not see "Resource Library: Generic fields"
    Then I should not see "Test Field"
