@local @local_resourcelibrary @core @javascript
Feature: As an admin I should be able filter with all custom field types

  Background:
    Given the following "local_resourcelibrary > category" exist:
      | component   | area   | name                             |
      | core_course | course | Resource Library: Generic fields |
    And the following config values are set as admin:
      | config                | value |
      | enableresourcelibrary | 1     |
    And the following "local_resourcelibrary > field" exist:
      | component             | area         | name                | customfieldcategory              | shortname | type     | configdata                                                                                                          |
      | core_course           | course       | Test Field Text     | Resource Library: Generic fields | CF1       | text     |                                                                                                                     |
      | core_course           | course       | Test Field Checkbox | Resource Library: Generic fields | CF2       | checkbox |                                                                                                                     |
      | core_course           | course       | Test Field Select   | Resource Library: Generic fields | CF4       | select   | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD","defaultvalue":"A,C","locked":"0","visibility":"2"} |
      | core_course           | course       | Test Field Textarea | Resource Library: Generic fields | CF5       | textarea |                                                                                                                     |
    And the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
      | C2        | Course 2 |
      | C3        | Course 3 |
      | C4        | Course 4 |
      | C5        | Course 5 |
    And the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value    | courseshortname | activityidnumber | activity |
      | CF1            | ABCDEFC1 | C1              |                  |          |
      | CF1            | ABCDEFC2 | C2              |                  |          |
      | CF1            | ABCDEFC3 | C3              |                  |          |
      | CF1            | ABCDEFC4 | C4              |                  |          |
      | CF1            | ABCDEFC5 | C5              |                  |          |
    And the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value | courseshortname | activityidnumber | activity |
      | CF2            | 1     | C1              |                  |          |
      | CF2            | 0     | C2              |                  |          |
      | CF2            | 0     | C3              |                  |          |
      | CF2            | 0     | C4              |                  |          |
      | CF2            | 0     | C5              |                  |          |
    And the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value | courseshortname | activityidnumber | activity |
      | CF4            | 1     | C1              |                  |          |
      | CF4            | 2     | C2              |                  |          |
      | CF4            | 3     | C3              |                  |          |
      | CF4            | 4     | C4              |                  |          |
      | CF4            | 5     | C5              |                  |          |
    And the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value          | courseshortname | activityidnumber | activity |
      | CF5            | ABCDEF Text C1 | C1              |                  |          |
      | CF5            | ABCDEF Text C2 | C2              |                  |          |
      | CF5            | ABCDEF Text C3 | C3              |                  |          |
      | CF5            | ABCDEF Text C4 | C4              |                  |          |
      | CF5            | ABCDEF Text C5 | C5              |                  |          |

  Scenario Outline: As an admin I should see all courses on the resource library page
    Given I am on site homepage
    And I log in as "admin"
    When I navigate to resource library "<page>" page
    Then I should see the texts "<see>"
    Examples:
      | page     | see                                              |
      | Home     | Course 1, Course 2, Course 3, Course 4, Course 5 |

  Scenario Outline: As an admin I should be able to filter through a text field for courses
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to resource library "<page>" page
    And I expand all fieldsets
    And I set the field "Test Field Text" to "<field>"
    When I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "<see>"
    And I should not see the texts "<notsee>"
    Examples:
      | page     | see      | notsee                                 | field    |
      | Home     | Course 1 | Course 2, Course 3, Course 4, Course 5 | ABCDEFC1 |

  Scenario Outline: As an admin I should be able to filter through a checkbox for courses
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to resource library "<page>" page
    And I expand all fieldsets
    And I set the field "Test Field Checkbox" to "1"
    When I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "<see>"
    And I should not see the texts "<notsee>"
    Examples:
      | page     | see      | notsee                                 |
      | Home     | Course 1 | Course 2, Course 3, Course 4, Course 5 |

  Scenario Outline: As an admin I should be able to filter through a multi-select for courses
    Given multiselect field is installed
    And the following "local_resourcelibrary > field" exist:
      | component             | area         | name               | customfieldcategory              | shortname | type        | configdata                                                                                                          |
      | core_course           | course       | Test Field MSelect | Resource Library: Generic fields | CF3       | multiselect | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD","defaultvalue":"A,C","locked":"0","visibility":"2"} |
    And the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value | courseshortname | activityidnumber | activity |
      | CF3            | 0     | C1              |                  |          |
      | CF3            | 0,1   | C2              |                  |          |
      | CF3            | 2,3   | C3              |                  |          |
      | CF3            | 4     | C4              |                  |          |
      | CF3            | 0,4   | C5              |                  |          |
    And I log in as "admin"
    And I navigate to resource library "<page>" page
    And I expand all fieldsets
    And I set the field "Test Field MSelect" to "<selection>"
    When I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "<see>"
    And I should not see the texts "<notsee>"
    Examples:
      | page     | see                          | notsee                                 | selection |
      | Home     | Course 1, Course 2, Course 5 | Course 3, Course 4                     | A,B       |
      | Home     | Course 2                     | Course 1, Course 3, Course 4, Course 5 | B         |

  Scenario Outline: As an admin I should be able to filter through a textarea for course
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to resource library "<page>" page
    And I expand all fieldsets
    And I set the field "Test Field Textarea" to "<textfield>"
    When I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "<see>"
    And I should not see the texts "<notsee>"
    Examples:
      | page     | see      | notsee                                 | textfield      |
      | Home     | Course 2 | Course 1, Course 3, Course 4, Course 5 | ABCDEF Text C2 |

  Scenario Outline: As an admin I should be able to filter through a multicriteria search for courses and activities
    # Note that by multicriteria we mean is a AND between different selected values
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to resource library "<page>" page
    And I expand all fieldsets
    And I set the field "<field1name>" to "<field1value>"
    And I set the field "<field2name>" to "<field2value>"
    When I click on "filterbutton" "button"
    Then I should see the texts "<see>"
    And I should not see the texts "<notsee>"
    Examples:
      | page     | see      | notsee                                 | field1name      | field1value | field2name          | field2value |
      | Home     | Course 1 | Course 2, Course 3, Course 4, Course 5 | Test Field Text | ABCDEFC1    | Test Field Checkbox | 1           |
      | Home     | Course 2 | Course 1, Course 3, Course 4, Course 5 | Test Field Text | 2           | Test Field Select   | B           |

  Scenario Outline: As an admin I should be able to filter through a multicriteria search for courses and activities (Multiselect)
    Given multiselect field is installed
    And the following "local_resourcelibrary > field" exist:
      | component   | area   | name               | customfieldcategory              | shortname | type        | configdata                                                                                                          |
      | core_course | course | Test Field MSelect | Resource Library: Generic fields | CF3       | multiselect | {"required":"1","uniquevalues":"0","options":"A\r\nB\r\nC\r\nD","defaultvalue":"A,C","locked":"0","visibility":"2"} |
    And the following "local_resourcelibrary > fielddata" exist:
      | fieldshortname | value | courseshortname | activityidnumber | activity |
      | CF3            | 0     | C1              |                  |          |
      | CF3            | 0,1   | C2              |                  |          |
      | CF3            | 2,3   | C3              |                  |          |
      | CF3            | 4     | C4              |                  |          |
      | CF3            | 0,4   | C5              |                  |          |
    # Note that by multicriteria we mean is a AND between different selected values
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to resource library "<page>" page
    And I expand all fieldsets
    And I set the field "<field1name>" to "<field1value>"
    And I set the field "<field2name>" to "<field2value>"
    When I click on "filterbutton" "button"
    And I wait until the page is ready
    Then I should see the texts "<see>"
    And I should not see the texts "<notsee>"
    Examples:
      | page     | see      | notsee                                 | field1name      | field1value | field2name          | field2value |
      | Home     | Course 1 | Course 2, Course 3, Course 4, Course 5 | Test Field Text | ABCDEFC1    | Test Field Checkbox | 1           |
      | Home     | Course 2 | Course 1, Course 3, Course 4, Course 5 | Test Field Text | 2           | Test Field MSelect  | A,B         |
