@local @local_resourcelibrary @core @javascript
Feature: As an admin I should be able to access values from custom field

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | user     | User      | 1        | user1@example.com    |
      | teacher  | Teacher   | 1        | teacher1@example.com |
    And the following config values are set as admin:
      | config                | value |
      | enableresourcelibrary | 1     |
    And the following "local_resourcelibrary > category" exist:
      | component   | area   | name                             |
      | core_course | course | Resource Library: Generic fields |
    And the following "local_resourcelibrary > field" exist:
      | component             | area         | name                | customfieldcategory              | shortname | type     | configdata                                                                                                          |
      | core_course           | course       | Test Field Text     | Resource Library: Generic fields | CF1       | text     |                                                                                                                     |
      | core_course           | course       | Test Field Checkbox | Resource Library: Generic fields | CF2       | checkbox |                                                                                                                     |
      | core_course           | course       | Test Field Select   | Resource Library: Generic fields | CF4       | select   | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD","defaultvalue":"A,C","locked":"0","visibility":"2"} |
      | core_course           | course       | Test Field Textarea | Resource Library: Generic fields | CF5       | textarea |                                                                                                                     |
    And the following "categories" exist:
      | name  | category | idnumber | visible |
      | Cat 1 | 0        | CAT1     | 1       |
      | Cat 2 | 0        | CAT2     | 0       |
    And the following "courses" exist:
      | shortname | fullname  | category |
      | C1        | Course 01 | CAT1     |
      | C2        | Course 02 | CAT1     |
      | C3        | Course 03 | CAT1     |
      | C4        | Course 04 | CAT1     |
      | C5        | Course 05 | CAT2     |
      | C6        | Course 06 | CAT1     |
      | C7        | Course 07 | CAT1     |
      | C8        | Course 08 | CAT1     |
      | C9        | Course 09 | CAT2     |
      | C10       | Course 10 | CAT1     |
      | C11       | Course 11 | CAT1     |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
      | teacher | C5     | editingteacher |
      | user    | C9     | student        |
      | user    | C1     | student        |

  Scenario Outline: As a user I should see all visible course and activities I usually have access to.
    Given I am on site homepage
    And I log in as "<user>"
    When I navigate to resource library "<page>" page
    Then I wait until the page is ready
    Then I should see the texts "<see>"
    Then I should not see the texts "<notsee>"
    Examples:
      | page      | see                             | notsee           | user    |
      | Home      | Course 01, Course 05, Course 09 |                  | admin   |
      | Home      | Course 01, Course 05            | Course 09        | teacher |
      | Home      | Course 01, Course 09            | Course 05        | user    |
