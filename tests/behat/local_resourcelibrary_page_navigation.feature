@local @local_resourcelibrary @core @javascript
Feature: As an admin I should be able to navigate through the resource library

  Background:
    Given the following "courses" exist:
      | shortname | fullname  |
      | C1        | Course 01 |
      | C2        | Course 02 |
      | C3        | Course 03 |
      | C4        | Course 04 |
      | C5        | Course 05 |
      | C6        | Course 06 |
      | C7        | Course 07 |
      | C8        | Course 08 |
      | C9        | Course 09 |
      | C10       | Course 10 |
      | C11       | Course 11 |
      | C12       | Course 12 |
      | C13       | Course 13 |
      | C14       | Course 14 |
      | C15       | Course 15 |
      | C16       | Course 16 |
      | C17       | Course 17 |
      | C18       | Course 18 |
    And the following config values are set as admin:
      | config                | value |
      | enableresourcelibrary | 1     |

  Scenario: As an admin I should see all courses and activities on the resource library page
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to resource library "Home" page
    And I wait until the page is ready
    And I should see "Course 01"
    And I should see "Course 11"
    And I should not see "Course 13"
    And I click on "li.page-item[data-control='next'] a" "css"
    When I should see "Course 14"
    Then I should see "Course 18"

  Scenario: If Toggle the page limit between page reloads, it should keep it as it was set
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to resource library "Home" page
    And I wait until the page is ready
    And I click on "Show 12 items per page" "button"
    And I click on "24" "link"
    And I should see "Course 18"
    And I reload the page
    When I should see "Course 18"
    Then I should see "24" in the "[data-action='limit-toggle']" "css_element"
