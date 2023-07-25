@local @local_resourcelibrary @core @javascript
Feature: As an admin I should be able to set and retrieve values from all types of custom field

  Background:
    Given the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
    Given the following "local_resourcelibrary > category" exist:
      | component   | area   | name                             |
      | core_course | course | Resource Library: Generic fields |
    Given the following "local_resourcelibrary > field" exist:
      | component             | area         | name                | customfieldcategory              | shortname | type     | configdata                                                                                                          |
      | core_course           | course       | Test Field Text     | Resource Library: Generic fields | CF1       | text     |                                                                                                                     |
      | core_course           | course       | Test Field Checkbox | Resource Library: Generic fields | CF2       | checkbox |                                                                                                                     |
      | core_course           | course       | Test Field Select   | Resource Library: Generic fields | CF4       | select   | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD","defaultvalue":"A,C","locked":"0","visibility":"2"} |
      | core_course           | course       | Test Field Textarea | Resource Library: Generic fields | CF5       | textarea |                                                                                                                     |
      | local_resourcelibrary | coursemodule | Test Field Text     | Resource Library: Generic fields | CM1       | text     |                                                                                                                     |
      | local_resourcelibrary | coursemodule | Test Field Checkbox | Resource Library: Generic fields | CM2       | checkbox |                                                                                                                     |
      | local_resourcelibrary | coursemodule | Test Field Select   | Resource Library: Generic fields | CM4       | select   | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD","defaultvalue":"A,C","locked":"0","visibility":"2"} |
      | local_resourcelibrary | coursemodule | Test Field Textarea | Resource Library: Generic fields | CM5       | textarea |                                                                                                                     |
    And the following "activities" exist:
      | activity | name      | intro     | course | idnumber |
      | page     | PageName1 | PageDesc1 | C1     | PAGE1    |
    # Note that select field are indexed from 1 to n (the 0 index is for the empty value)
    # And the multiselect are indexed from 0 to n-1
    Given the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value       | courseshortname | activityidnumber | activity |
      | CF1            | ABCDEF      | C1              |                  |          |
      | CM1            | ABCDEF      | C1              | PAGE1            | page     |
      | CF2            | 1           | C1              |                  |          |
      | CM2            | 1           | C1              | PAGE1            | page     |
      | CF4            | 2           | C1              |                  |          |
      | CM4            | 2           | C1              | PAGE1            | page     |
      | CF5            | ABCDEF Text | C1              |                  |          |
      | CM5            | ABCDEF Text | C1              | PAGE1            | page     |

  Scenario: As an admin if I set a value for a course module custom field, then I should be able to retrieve it after.
    Given I am on site homepage
    And I log in as "admin"
    Given I am on "Course 1" course homepage
    And I turn editing mode on
    And I open "PageName1" actions menu
    And I click on "Edit settings" "link" in the "PageName1" activity
    And I expand all fieldsets
    Then I should see "Resource Library: Generic fields"
    And the field "Test Field Text" matches value "ABCDEF"
    And the field "Test Field Checkbox" matches value "1"
    And the field "Test Field Select" matches value "B"
    And the field "Test Field Textarea" matches value "ABCDEF Text"
    Then I set the field "Test Field Text" to "ACDBE"
    Then I set the field "Test Field Checkbox" to "0"
    Then I set the field "Test Field Select" to "C"
    Then I set the field "Test Field Textarea" to "ACDBE Text"
    And I click on "Save and return to course" "button"
    And I open "PageName1" actions menu
    And I click on "Edit settings" "link" in the "PageName1" activity
    And I expand all fieldsets
    And the field "Test Field Text" matches value "ACDBE"
    And the field "Test Field Checkbox" matches value "0"
    And the field "Test Field Select" matches value "C"
    And the field "Test Field Textarea" matches value "ACDBE Text"

  Scenario: As an admin if I set a value for a course custom field, then I should be able to retrieve it after.
    Given I am on site homepage
    And I log in as "admin"
    Given I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    Then I should see "Resource Library: Generic fields"
    And the field "Test Field Text" matches value "ABCDEF"
    And the field "Test Field Checkbox" matches value "1"
    And the field "Test Field Select" matches value "B"
    And the field "Test Field Textarea" matches value "ABCDEF Text"
    Then I set the field "Test Field Text" to "ACDBE"
    Then I set the field "Test Field Checkbox" to "0"
    Then I set the field "Test Field Select" to "C"
    Then I set the field "Test Field Textarea" to "ACDBE Text"
    Then I set the field "Test Field" to "ACDBE"
    And I click on "Save" "button"
    Given I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And the field "Test Field Text" matches value "ACDBE"
    And the field "Test Field Checkbox" matches value "0"
    And the field "Test Field Select" matches value "C"
    And the field "Test Field Textarea" matches value "ACDBE Text"

  Scenario: As an admin if I set a value for a course module custom field, then I should be able to retrieve it after (Multiselect).
    Given multiselect field is installed
    Given the following "local_resourcelibrary > field" exist:
      | component             | area         | name               | customfieldcategory              | shortname | type        | configdata                                                                                                          |
      | core_course           | course       | Test Field MSelect | Resource Library: Generic fields | CF3       | multiselect | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD","defaultvalue":"A,C","locked":"0","visibility":"2"} |
      | local_resourcelibrary | coursemodule | Test Field MSelect | Resource Library: Generic fields | CM3       | multiselect | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD","defaultvalue":"A,C","locked":"0","visibility":"2"} |
    Given the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value | courseshortname | activityidnumber | activity |
      | CF3            | 0     | C1              |                  |          |
      | CM3            | 0     | C1              | PAGE1            | page     |
    Given I am on site homepage
    And I log in as "admin"
    Given I am on "Course 1" course homepage
    And I turn editing mode on
    And I open "PageName1" actions menu
    And I click on "Edit settings" "link" in the "PageName1" activity
    And I expand all fieldsets
    Then I should see "Resource Library: Generic fields"
    And the field "Test Field MSelect" matches value "A"
    Then I set the field "Test Field MSelect" to "A,B"
    And I click on "Save and return to course" "button"
    And I open "PageName1" actions menu
    And I click on "Edit settings" "link" in the "PageName1" activity
    And I expand all fieldsets
    And the field "Test Field MSelect" matches value "A,B"

  Scenario: As an admin if I set a value for a course custom field, then I should be able to retrieve it after (Multiselect).
    Given multiselect field is installed
    Given the following "local_resourcelibrary > field" exist:
      | component             | area         | name               | customfieldcategory              | shortname | type        | configdata                                                                                                          |
      | core_course           | course       | Test Field MSelect | Resource Library: Generic fields | CF3       | multiselect | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD","defaultvalue":"A,C","locked":"0","visibility":"2"} |
      | local_resourcelibrary | coursemodule | Test Field MSelect | Resource Library: Generic fields | CM3       | multiselect | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD","defaultvalue":"A,C","locked":"0","visibility":"2"} |
    Given the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value | courseshortname | activityidnumber | activity |
      | CF3            | 0     | C1              |                  |          |
      | CM3            | 0     | C1              | PAGE1            | page     |
    Given I am on site homepage
    And I log in as "admin"
    Given I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    Then I should see "Resource Library: Generic fields"
    Then I should see "A" in the "Test Field MSelect" "autocomplete"
    Then I set the field "Test Field MSelect" to "A"
    Then I set the field "Test Field MSelect" to "B"
    And I click on "Save" "button"
    Given I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    Then I should see "A" in the "Test Field MSelect" "autocomplete"
    Then I should see "B" in the "Test Field MSelect" "autocomplete"
