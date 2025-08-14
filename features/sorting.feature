Feature: Product Sorting
  In order to view products in different orders
  As a shopper
  I want to sort products by name Z to A and verify ordering

  @smoke
  Scenario: Sort products by name (Z to A)
    Given I am logged in as "standard_user" with password "secret_sauce"
    When I sort products by "Name (Z to A)"
    Then the product list should be sorted in reverse alphabetical order