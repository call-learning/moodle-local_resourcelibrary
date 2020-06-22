@local @local_resourcelibrary @core @javascript
Feature: As an admin I want to be able to turn on and off the plugin and custom field menus

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

  Scenario: As an admin if I turn off the plugin feature, I should not see any catalog related field in the course menu or activity edit form
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
    Then I should not see "Test Field"
    Given I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    Then I should not see "Resource Library: Generic fields"
    Then I should not see "Test Field"


  Scenario: As an admin if I turn on the plugin feature, I should a catalog related field in the course menu or activity edit form
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
    Then I should see "Test Field"
    Given I am on "Course 1" course homepage
    And I navigate to "Resource Library Field Settings" in current page administration
    And I expand all fieldsets
    Then I should see "Resource Library: Generic fields"
    Then I should see "Test Field"

