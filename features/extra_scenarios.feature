Feature: Extra Critical Scenarios for Swag Labs
  In order to ensure robustness of the Swag Labs application
  As a QA engineer
  I want to validate additional scenarios beyond the core assignment

  Background:
    Given I am logged in as "standard_user" with password "secret_sauce"

  Scenario: Session remains active after page refresh
    When I refresh the page
    Then I should still see the inventory page

  Scenario: Checkout is blocked with empty cart
    And I go to the cart
    And I click the checkout button
    Then I should see empty cart

  Scenario: Cart retains items after navigation
    When I add "Sauce Labs Backpack" to the cart
    And I go to the cart
    And I return to the inventory page
    Then the cart should contain 1 items

  Scenario: Logout redirects to login page
    When I log out
    Then I should see the login page
