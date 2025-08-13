<?php
namespace Pages;

use Behat\Mink\Exception\ElementNotFoundException;
use Pages\BasePage;

class LoginPage extends BasePage
{
    // Locators
    private $usernameField = 'user-name';
    private $passwordField = 'password';
    private $loginButton = 'login-button';
    private $error = '[data-test="error"]';

    public function visit($url = 'https://www.saucedemo.com')
    {
        $this->session->visit($url);
    }

    /**
     * @throws ElementNotFoundException
     */
    public function login($username, $password)
    {
        $this->page->fillField($this->usernameField, $username);
        $this->page->fillField($this->passwordField, $password);
        $this->page->pressButton($this->loginButton);
    }

    /**
     * @throws \Exception
     */
    public function getErrorMessage(): string
    {
        return $this->find('css', $this->error)->getText();
    }

    public function isLoginPageVisible()
    {
        return $this->page->find('css', $this->loginButton);
    }
}