@local @local_resourcelibrary @core @javascript
Feature: As an admin I want to be able to turn on and off the plugin and custom field menus

  Background:
    Given the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
    And the following config values are set as admin:
      | enablecourserequests | 0 |
    And the following "local_resourcelibrary > category" exist:
      | component   | area   | name                             |
      | core_course | course | Resource Library: Generic fields |
    And the following "local_resourcelibrary > field" exist:
      | component             | area         | name                | customfieldcategory              | shortname | type        | configdata                                                                                                          |
      | core_course           | course       | Test Field Text     | Resource Library: Generic fields | CF1       | text        |                                                                                                                     |

  Scenario: As an admin if I turn off the plugin feature, I should not see any catalog related in settings
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to "Advanced features" in site administration
    And I should see "Enable Resource Library"
    And I set the field "Enable Resource Library" to "0"
    And I click on "Save changes" "button"
    And I click on "Site administration" "link"
    When I click on "Courses" "link"
    Then I should not see "Resource Library"

  Scenario: As an admin if I turn on the plugin feature, I should a catalog related field in settings
    Given I am on site homepage
    And I log in as "admin"
    And I navigate to "Advanced features" in site administration
    And I should see "Enable Resource Library"
    And I set the field "Enable Resource Library" to "1"
    And I click on "Save changes" "button"
    And I click on "Site administration" "link"
    When I click on "Courses" "link"
    Then I should see "Resource Library"
