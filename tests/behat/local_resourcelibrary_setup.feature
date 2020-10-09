@local @local_resourcelibrary @core @javascript
Feature: As an admin I want to be able to turn on and off the plugin and custom field menus

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

  Scenario: As an admin if I turn off the plugin feature, I should not see any catalog related field in the activity edit form
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to "Advanced features" in site administration
    And I should see "Enable Resource Library"
    And I set the field "Enable Resource Library" to "0"
    And I click on "Save changes" "button"
    And I navigate to "Courses" in site administration
    Then I should not see "Resource Library"
    Given I am on "Course 1" course homepage
    And I turn editing mode on
    And I open "PageName1" actions menu
    And I click on "Edit settings" "link" in the "PageName1" activity
    And I expand all fieldsets
    Then I wait until the page is ready
    Then I should not see "Test Field"

  Scenario: As an admin if I turn on the plugin feature, I should a catalog related field in the activity edit form
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to "Advanced features" in site administration
    And I should see "Enable Resource Library"
    And I set the field "Enable Resource Library" to "1"
    And I click on "Save changes" "button"
    And I navigate to "Courses" in site administration
    Then I should see "Resource Library"
    Given I am on "Course 1" course homepage
    And I turn editing mode on
    And I open "PageName1" actions menu
    And I click on "Edit settings" "link" in the "PageName1" activity
    And I expand all fieldsets
    Then I wait until the page is ready
    Then I should see "Test Field"