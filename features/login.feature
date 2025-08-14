Feature: Login Functionality
  In order to secure the app
  As a user
  I want to see correct error messages for failed logins

  @smoke
  Scenario: Failed login with invalid credentials
    Given I am on the login page
    When I log in with username "wrong_user" and password "wrong_pass"
    Then I should see an error containing "do not match any user"

  @smoke
  Scenario: Failed login with locked out user
    Given I am on the login page
    When I log in with username "locked_out_user" and password "secret_sauce"
    Then I should see an error containing "locked out"