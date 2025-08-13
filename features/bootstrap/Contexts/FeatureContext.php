<?php
namespace Contexts;
require_once 'vendor/autoload.php';

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkContext;
use Exception;
use Pages\CartPage;
use Pages\InventoryPage;
use Pages\LoginPage;
use PHPUnit\Framework\Assert;


class FeatureContext extends MinkContext implements Context
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var LoginPage
     */
    private $loginPage;
    /**
     * @var InventoryPage
     */
    private $inventoryPage;
    /**
     * @var CartPage
     */
    private $cartPage;

    /**
     * Behat hook: runs before each scenario
     * Initializes page objects after Mink session is available
     * @BeforeScenario
     */
    public function beforeScenario(BeforeScenarioScope $scope)
    {
        $this->session = $this->getSession();
        $this->loginPage = new LoginPage($this->session);
        $this->inventoryPage = new InventoryPage($this->session);
        $this->cartPage = new CartPage($this->session);
    }
    /**
     * @Given I am on the login page
     */
    public function iAmOnTheLoginPage()
    {
        $this->loginPage->visit();
    }

    /**
     * @When I log in with username :username and password :password
     * @throws ElementNotFoundException
     */
    public function iLogInWithUsernameAndPassword($username, $password)
    {
        $this->loginPage->login($username, $password);
    }

    /**
     * @Then I should see an error containing :text
     * @throws Exception
     */
    public function iShouldSeeAnErrorContaining($text)
    {
        $errorMessage = $this->loginPage->getErrorMessage();
        Assert::assertTrue(strpos($errorMessage, $text) !== false);
    }

    /**
     * @Given /^I am logged in as "([^"]*)" with password "([^"]*)"$/
     * @throws ElementNotFoundException
     */
    public function iAmLoggedInAsWithPassword($username, $password)
    {
        $this->loginPage->visit();
        $this->loginPage->login($username, $password);
        // wait for inventory page
        $this->session->wait(2000);
        Assert::assertTrue($this->inventoryPage->isInventoryPageVisible());
    }

    /**
     * @When I sort products by :option
     */
    public function iSortProductsBy($option)
    {
        $this->inventoryPage->selectSortOption($option);
    }

    /**
     * @Then the product list should be sorted in reverse alphabetical order
     * @throws Exception
     */
    public function theProductListShouldBeSortedInReverseAlphabeticalOrder()
    {
        $names = $this->inventoryPage->getAllInventoryItemNames();
        $sorted = $names;
        rsort($sorted, SORT_NATURAL | SORT_FLAG_CASE);
        Assert::assertTrue($sorted === $names);
    }

    /**
     * @When I add :productName to the cart
     * @throws Exception
     */
    public function iAddToTheCart($productName)
    {
        $this->inventoryPage->addItemToTheCart($productName);
    }

    /**
     * @When I go to the cart
     */
    public function iGoToTheCart()
    {
        $this->cartPage->visit();
    }

    /**
     * @When I proceed to checkout with:
     * @throws ElementNotFoundException
     */
    public function iProceedToCheckoutWith(TableNode $table)
    {
        $this->cartPage->fillCheckoutDetails($table);
    }

    /**
     * @Then The total price before tax should equal the sum of item prices
     */
    public function theTotalPriceBeforeTaxShouldEqualTheSumOfItemPrices()
    {
        $itemsTotal = $this->cartPage->getSumOfAllItemPrices();
        $summaryTotal = $this->cartPage->getSummarySubTotalPrice();
        $absoluteAmount = abs($itemsTotal - $summaryTotal);
        // compare with small epsilon
        Assert::assertTrue($absoluteAmount <= 0.01);
    }

    /**
     * @Then The final price should equal item total plus tax
     * @throws Exception
     */
    public function theFinalPriceShouldEqualItemTotalPlusTax()
    {
        $subtotal = $this->cartPage->getSummarySubTotalPrice();
        $tax = $this->cartPage->getTaxPrice();
        $total = $this->cartPage->getSummaryTotalPrice();
        Assert::assertEquals($total, $subtotal + $tax);
    }

    /**
     * @Then I should see the message :text
     */
    public function iShouldSeeTheMessage($text)
    {
        $message = $this->cartPage->getCheckoutCompleteMessage();
        Assert::assertTrue(stripos($message, $text) !== false);
    }

    /**
     * @Then I Click Finish
     * @throws ElementNotFoundException
     */
    public function iClickFinish()
    {
        $this->cartPage->clickFinish();
    }
}
