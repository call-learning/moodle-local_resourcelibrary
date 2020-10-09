@local @local_resourcelibrary @core @javascript
Feature: As an admin I should be able filter with all custom field types

  Background:
    Given the following "local_resourcelibrary > field" exist:
      | area         | name                | customfieldcategory              | shortname | type        | configdata                                                                                                             |
      | course       | Test Field Text     | Resource Library: Generic fields | CF1       | text        |                                                                                                                        |
      | course       | Test Field Checkbox | Resource Library: Generic fields | CF2       | checkbox    |                                                                                                                        |
      | course       | Test Field Select   | Resource Library: Generic fields | CF4       | select      | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD\nE","defaultvalue":"A,C","locked":"0","visibility":"2"} |
      | course       | Test Field Textarea | Resource Library: Generic fields | CF5       | textarea    |                                                                                                                        |
      | coursemodule | Test Field Text     | Resource Library: Generic fields | CM1       | text        |                                                                                                                        |
      | coursemodule | Test Field Checkbox | Resource Library: Generic fields | CM2       | checkbox    |                                                                                                                        |
      | coursemodule | Test Field Select   | Resource Library: Generic fields | CM4       | select      | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD\nE","defaultvalue":"A,C","locked":"0","visibility":"2"} |
      | coursemodule | Test Field Textarea | Resource Library: Generic fields | CM5       | textarea    |                                                                                                                        |
    Given the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
      | C2        | Course 2 |
      | C3        | Course 3 |
      | C4        | Course 4 |
      | C5        | Course 5 |
    And the following "activities" exist:
      | activity | name      | intro     | course | idnumber |
      | page     | Page 1 | PageDesc1 | C1     | PAGE1    |
      | page     | Page 2 | PageDesc2 | C1     | PAGE2    |
      | page     | Page 3 | PageDesc3 | C1     | PAGE3    |
      | page     | Page 4 | PageDesc4 | C1     | PAGE4    |
      | page     | Page 5 | PageDesc5 | C1     | PAGE5    |
    Given the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value    | courseshortname | activityidnumber | activity |
      | CF1            | ABCDEFC1 | C1              |                  |          |
      | CF1            | ABCDEFC2 | C2              |                  |          |
      | CF1            | ABCDEFC3 | C3              |                  |          |
      | CF1            | ABCDEFC4 | C4              |                  |          |
      | CF1            | ABCDEFC5 | C5              |                  |          |
      | CM1            | ABCDEFP1 | C1              | PAGE1            | page     |
      | CM1            | ABCDEFP2 | C1              | PAGE2            | page     |
      | CM1            | ABCDEFP3 | C1              | PAGE3            | page     |
      | CM1            | ABCDEFP4 | C1              | PAGE4            | page     |
      | CM1            | ABCDEFP5 | C1              | PAGE5            | page     |
    Given the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value | courseshortname | activityidnumber | activity |
      | CF2            | 1     | C1              |                  |          |
      | CF2            | 0     | C2              |                  |          |
      | CF2            | 0     | C3              |                  |          |
      | CF2            | 0     | C4              |                  |          |
      | CF2            | 0     | C5              |                  |          |
      | CM2            | 1     | C1              | PAGE1            | page     |
      | CM2            | 0     | C1              | PAGE2            | page     |
      | CM2            | 0     | C1              | PAGE3            | page     |
      | CM2            | 0     | C1              | PAGE4            | page     |
      | CM2            | 0     | C1              | PAGE5            | page     |
    Given the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value | courseshortname | activityidnumber | activity |
      | CF4            | 1     | C1              |                  |          |
      | CF4            | 2     | C2              |                  |          |
      | CF4            | 3     | C3              |                  |          |
      | CF4            | 4     | C4              |                  |          |
      | CF4            | 5     | C5              |                  |          |
      | CM4            | 1     | C1              | PAGE1            | page     |
      | CM4            | 2     | C1              | PAGE2            | page     |
      | CM4            | 3     | C1              | PAGE3            | page     |
      | CM4            | 4     | C1              | PAGE4            | page     |
      | CM4            | 5     | C1              | PAGE5            | page     |
    Given the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value          | courseshortname | activityidnumber | activity |
      | CF5            | ABCDEF Text C1 | C1              |                  |          |
      | CF5            | ABCDEF Text C2 | C2              |                  |          |
      | CF5            | ABCDEF Text C3 | C3              |                  |          |
      | CF5            | ABCDEF Text C4 | C4              |                  |          |
      | CF5            | ABCDEF Text C5 | C5              |                  |          |
      | CM5            | ABCDEF Text P1 | C1              | PAGE1            | page     |
      | CM5            | ABCDEF Text P2 | C1              | PAGE2            | page     |
      | CM5            | ABCDEF Text P3 | C1              | PAGE3            | page     |
      | CM5            | ABCDEF Text P4 | C1              | PAGE4            | page     |
      | CM5            | ABCDEF Text P5 | C1              | PAGE5            | page     |

  Scenario: As an admin I should see all courses and activities on the resource library page
    Given I am on site homepage
    And I log in as "admin"
    Given I am on homepage
    And I follow "Resource library"
    Then I should see "Course 1"
    And I should see "Course 2"
    And I should see "Course 3"
    And I should see "Course 4"
    And I should see "Course 5"

  Scenario: As an admin I should see all activities on the resource library page for activities
    Given I am on site homepage
    And I log in as "admin"
    Given I am on "Course 1" course homepage
    And I follow "Resource library"
    Then I should see "Page 1"
    And I should see "Page 2"
    And I should see "Page 3"
    And I should see "Page 4"
    And I should see "Page 5"

  Scenario: As an admin I should be able to filter through a text field for courses
    Given I am on site homepage
    And I log in as "admin"
    Given I am on homepage
    And I follow "Resource library"
    And I set the field "Test Field Text" to "ABCDEFC1"
    And I click on "Filter" "button"
    Then I should see "Course 1"
    And I should not see "Course 2"
    And I should not see "Course 3"
    And I should not see "Course 4"
    And I should not see "Course 5"

  Scenario: As an admin I should be able to filter through a text field for activities
    Given I am on site homepage
    And I log in as "admin"
    Given I am on "Course 1" course homepage
    And I follow "Resource library"
    And I set the field "Test Field Text" to "ABCDEFP1"
    And I click on "Filter" "button"
    Then I should see "Page 1"
    And I should not see "Page 2"
    And I should not see "Page 3"
    And I should not see "Page 4"
    And I should not see "Page 5"

  Scenario: As an admin I should be able to filter through a checkbox for courses
    Given I am on site homepage
    And I log in as "admin"
    Given I am on homepage
    And I follow "Resource library"
    And I set the field "Test Field Checkbox" to "1"
    And I click on "Filter" "button"
    Then I should see "Course 1"
    And I should not see "Course 2"
    And I should not see "Course 3"
    And I should not see "Course 4"
    And I should not see "Course 5"

  Scenario: As an admin I should be able to filter through a checkbox for activities
    Given I am on site homepage
    And I log in as "admin"
    Given I am on "Course 1" course homepage
    And I follow "Resource library"
    And I set the field "Test Field Checkbox" to "1"
    And I click on "Filter" "button"
    Then I should see "Page 1"
    And I should not see "Page 2"
    And I should not see "Page 3"
    And I should not see "Page 4"
    And I should not see "Page 5"

  Scenario: As an admin I should be able to filter through a multi-select
    Given multiselect field is installed
    Given the following "local_resourcelibrary > field" exist:
      | core_course           | course       | Test Field MSelect  | Resource Library: Generic fields | CF3       | multiselect | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD","defaultvalue":"A,C","locked":"0","visibility":"2"} |
    Given the following "local_resourcelibrary > fielddata" exist:
      | CF3            | 0           | C1              |                  |          |
    Given the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value | courseshortname | activityidnumber | activity |
      | CF3            | 0     | C1              |                  |          |
      | CF3            | 0,1   | C2              |                  |          |
      | CF3            | 2,3   | C3              |                  |          |
      | CF3            | 4     | C4              |                  |          |
      | CF3            | 0,4   | C5              |                  |          |
    Given I am on site homepage
    And I log in as "admin"
    Given I am on homepage
    And I follow "Resource library"
    And I set the field "Test Field MSelect" to "A"
    And I set the field "Test Field MSelect" to "B"
    And I click on "Filter" "button"
    Then I should see "Course 1"
    And I should see "Course 2"
    And I should not see "Course 3"
    And I should not see "Course 4"
    And I should not see "Course 5"

  Scenario: As an admin I should be able to filter through a multi-select for activities
    Given multiselect field is installed
    Given the following "local_resourcelibrary > field" exist:
      | local_resourcelibrary | coursemodule | Test Field MSelect  | Resource Library: Generic fields | CM3       | multiselect | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD","defaultvalue":"A,C","locked":"0","visibility":"2"} |
    Given the following "local_resourcelibrary > fielddata" exist:
      | CM3            | 0           | C1              | PAGE1            | page     |
    Given the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value | courseshortname | activityidnumber | activity |
      | CM3            | 0     | C1              | PAGE1            | page     |
      | CM3            | 0,1   | C1              | PAGE2            | page     |
      | CM3            | 2,3   | C1              | PAGE3            | page     |
      | CM3            | 4     | C1              | PAGE4            | page     |
      | CM3            | 0,4   | C1              | PAGE5            | page     |

    Given I am on site homepage
    And I log in as "admin"
    Given I am on "Course 1" course homepage
    And I follow "Resource library"
    And I set the field "Test Field MSelect" to "A"
    And I set the field "Test Field MSelect" to "B"
    And I click on "Filter" "button"
    Then I should see "Page 1"
    And I should see "Page 2"
    And I should not see "Page 3"
    And I should not see "Page 4"
    And I should not see "Page 5"

  Scenario: As an admin I should be able to filter through a select
    Given I am on site homepage
    And I log in as "admin"
    Given I am on homepage
    And I follow "Resource library"
    And I set the field "Test Field MSelect" to "A"
    And I click on "Filter" "button"
    Then I should see "Course 1"
    And I should not see "Course 2"
    And I should not see "Course 3"
    And I should not see "Course 4"
    And I should not see "Course 5"

  Scenario: As an admin I should be able to filter through a select for activities
    Given I am on site homepage
    And I log in as "admin"
    Given I am on "Course 1" course homepage
    And I follow "Resource library"
    And I set the field "Test Field MSelect" to "A"
    And I click on "Filter" "button"
    Then I should see "Page 1"
    And I should not see "Page 2"
    And I should not see "Page 3"
    And I should not see "Page 4"
    And I should not see "Page 5"

  Scenario: As an admin I should be able to filter through a textarea for course
    Given I am on site homepage
    And I log in as "admin"
    Given I am on homepage
    And I follow "Resource library"
    And I set the field "Test Field Textarea" to "ABCDEF Text C2"
    And I click on "Filter" "button"
    Then I should not see "Course 1"
    And I should see "Course 2"
    And I should not see "Course 3"
    And I should not see "Course 4"
    And I should not see "Course 5"


  Scenario: As an admin I should be able to filter through a textarea for activities
    Given I am on site homepage
    And I log in as "admin"
    Given I am on "Course 1" course homepage
    And I follow "Resource library"
    And I set the field "Test Field Textarea" to "ABCDEF Text P2"
    And I click on "Filter" "button"
    Then I should not see "Page 1"
    And I should see "Page 2"
    And I should not see "Page 3"
    And I should not see "Page 4"
    And I should not see "Page 5"

  Scenario: As an admin I should be able to filter through a multicriteria search for courses
    # Note that by multicriteria we mean is a AND between different selected values
    Given I am on site homepage
    And I log in as "admin"
    Given I am on homepage
    And I follow "Resource library"
    And I set the field "Test Field Text" to "ABCDEFC1"
    And I set the field "Test Field Checkbox" to "1"
    And I click on "Filter" "button"
    Then I should see "Course 1"
    And I should not see "Course 2"
    And I should not see "Course 3"
    And I should not see "Course 4"
    And I should not see "Course 5"
    Given I am on homepage
    And I follow "Resource library"
    And I set the field "Test Field Text" to "2"
    And I set the field "Test Field MSelect" to "A,B"
    And I click on "Filter" "button"
    Then I should not see "Course 1"
    And I should see "Course 2"
    And I should not see "Course 3"
    And I should not see "Course 4"
    And I should not see "Course 5"

  Scenario: As an admin I should be able to filter through a multicriteria search for activities
    # Note that by multicriteria we mean is a AND between different selected values
    Given I am on site homepage
    And I log in as "admin"
    Given I am on "Course 1" course homepage
    And I follow "Resource library"
    And I set the field "Test Field Text" to "ABCDEFP1"
    And I set the field "Test Field Checkbox" to "1"
    And I click on "Filter" "button"
    Then I should see "Page 1"
    And I should not see "Page 2"
    And I should not see "Page 3"
    And I should not see "Page 4"
    Given I am on site homepage
    Given I am on "Course 1" course homepage
    And I follow "Resource library"
    And I set the field "Test Field Text" to "2"
    And I set the field "Test Field MSelect" to "A,B"
    And I click on "Filter" "button"
    Then I should not see "Page 1"
    And I should see "Page 2"
    And I should not see "Page 3"
    And I should not see "Page 4"