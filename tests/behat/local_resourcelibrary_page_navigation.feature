@local @local_resourcelibrary @core @javascript
Feature: As an admin I should be able to set and retrieve values from custom field

  Background:
    Given the following "local_resourcelibrary > field" exist:
      | area         | name                | customfieldcategory              | shortname | type        | configdata                                                                                                             |
      | course       | Test Field Text     | Resource Library: Generic fields | CF1       | text        |                                                                                                                        |
      | course       | Test Field Checkbox | Resource Library: Generic fields | CF2       | checkbox    |                                                                                                                        |
      | course       | Test Field MSelect  | Resource Library: Generic fields | CF3       | multiselect | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD\nE","defaultvalue":"A,C","locked":"0","visibility":"2"} |
      | course       | Test Field Select   | Resource Library: Generic fields | CF4       | select      | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD\nE","defaultvalue":"A,C","locked":"0","visibility":"2"} |
      | course       | Test Field Textarea | Resource Library: Generic fields | CF5       | textarea    |                                                                                                                        |
      | coursemodule | Test Field Text     | Resource Library: Generic fields | CM1       | text        |                                                                                                                        |
      | coursemodule | Test Field Checkbox | Resource Library: Generic fields | CM2       | checkbox    |                                                                                                                        |
      | coursemodule | Test Field MSelect  | Resource Library: Generic fields | CM3       | multiselect | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD\nE","defaultvalue":"A,C","locked":"0","visibility":"2"} |
      | coursemodule | Test Field Select   | Resource Library: Generic fields | CM4       | select      | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD\nE","defaultvalue":"A,C","locked":"0","visibility":"2"} |
      | coursemodule | Test Field Textarea | Resource Library: Generic fields | CM5       | textarea    |                                                                                                                        |
    Given the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 01 |
      | C2        | Course 02 |
      | C3        | Course 03 |
      | C4        | Course 04 |
      | C5        | Course 05 |
      | C6        | Course 06 |
      | C7        | Course 07 |
      | C8        | Course 08 |
      | C9        | Course 09 |
      | C10        | Course 10 |
      | C11        | Course 11 |
      | C12        | Course 12 |
      | C13        | Course 13 |
      | C14        | Course 14 |
      | C15        | Course 15 |
      | C16        | Course 16 |
      | C17        | Course 17 |
      | C18        | Course 18 |

  Scenario: As an admin I should see all courses and activities on the resource library page
    Given I am on site homepage
    And I log in as "admin"
    And I follow "Resource Library"
    Then I should see "Course 01"
    And I should see "Course 11"
    And I should not see "Course 13"
    When I click on "li.page-item[data-control='next'] a" "css"
    And I should see "Course 14"
    And I should see "Course 18"

  Scenario: If Toggle the page limit between page reloads, it should keep it as it was set
    Given I am on site homepage
    Given I log in as "admin"
    And I follow "Resource Library"
    When I click on "Show 12 items per page" "button"
    And I click on "24" "link"
    Then I should see "Course 18"
    And I reload the page
    Then I should see "Course 18"
    And I should see "24" in the "[data-action='limit-toggle']" "css_element"