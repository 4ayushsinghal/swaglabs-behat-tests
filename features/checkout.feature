Feature: Checkout Process
  In order to complete purchases
  As a shopper
  I want to add items to the cart, verify totals and finish checkout

  Scenario: Complete checkout with backpack and bike light
    Given I am logged in as "standard_user" with password "secret_sauce"
    When I add "Sauce Labs Backpack" to the cart
    And I add "Sauce Labs Bike Light" to the cart
    And I go to the cart
    And I proceed to checkout with:
      | first_name | John |
      | last_name  | Doe  |
      | postal_code| 12345|
    Then the total price before tax should equal the sum of item prices
    And the final price should equal item total plus tax
    And I should see the message "THANK YOU FOR YOUR ORDER"