@local @local_resourcelibrary @core @javascript
Feature: As an admin I should be able to access values from custom field

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | user     | User      | 1        | user1@example.com    |
      | teacher  | Teacher   | 1        | teacher1@example.com |
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
    Given the following "categories" exist:
      | name  | category | idnumber | visible |
      | Cat 1 | 0        | CAT1     | 1       |
      | Cat 2 | 0        | CAT2     | 0       |
    Given the following "courses" exist:
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
    And the following "activities" exist:
      | activity | name    | intro     | course | idnumber | visible |
      | page     | Page 01 | PageDesc1 | C1     | PAGE1    | 1       |
      | page     | Page 02 | PageDesc2 | C1     | PAGE2    | 1       |
      | page     | Page 03 | PageDesc3 | C1     | PAGE3    | 1       |
      | page     | Page 04 | PageDesc4 | C1     | PAGE4    | 1       |
      | page     | Page 05 | PageDesc5 | C1     | PAGE5    | 0       |
      | page     | Page 06 | PageDesc1 | C1     | PAGE6    | 1       |
      | page     | Page 07 | PageDesc2 | C1     | PAGE7    | 1       |
      | page     | Page 08 | PageDesc3 | C1     | PAGE8    | 1       |
      | page     | Page 09 | PageDesc4 | C1     | PAGE9    | 0       |
      | page     | Page 10 | PageDesc5 | C1     | PAGE10   | 1       |
      | page     | Page 11 | PageDesc1 | C1     | PAGE11   | 1       |
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
      | Course 01 | Page 01, Page 05, Page 09       |                  | admin   |
      | Course 01 | Page 01, Page 05, Page 09       |                  | teacher |
      | Course 01 | Page 01                         | Page 05, Page 09 | user    |
