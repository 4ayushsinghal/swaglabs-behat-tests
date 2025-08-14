<?php
namespace Contexts;
require_once 'vendor/autoload.php';

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Step\Given;
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
    private Session $session;
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
    public function beforeScenario(BeforeScenarioScope $scope): void
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
    public function iLogInWithUsernameAndPassword($username, $password): void
    {
        $this->loginPage->login($username, $password);
    }

    /**
     * @Then I should see an error containing :text
     * @throws Exception
     */
    public function iShouldSeeAnErrorContaining($text): void
    {
        $errorMessage = $this->loginPage->getErrorMessage();
        Assert::assertTrue(str_contains($errorMessage, $text));
    }

    /**
     * @throws ElementNotFoundException
     */
    #[Given('I am logged in as :arg1 with password :arg2')]
    public function iAmLoggedInAsWithPassword($username, $password): void
    {
        $this->loginPage->visit();
        $this->loginPage->login($username, $password);
        // wait for inventory page
        $this->session->wait(2000);
        Assert::assertTrue($this->inventoryPage->isInventoryPageVisible());
    }

    /**
     * @When I sort products by :option
     * @throws Exception
     */
    public function iSortProductsBy($option): void
    {
        $this->inventoryPage->selectSortOption($option);
    }

    /**
     * @Then the product list should be sorted in reverse alphabetical order
     * @throws Exception
     */
    public function theProductListShouldBeSortedInReverseAlphabeticalOrder(): void
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
    public function iAddToTheCart($productName): void
    {
        $this->inventoryPage->addItemToTheCart($productName);
    }

    /**
     * @When I go to the cart
     */
    public function iGoToTheCart(): void
    {
        $this->cartPage->visit();
    }

    /**
     * @When I proceed to checkout with:
     * @throws ElementNotFoundException
     */
    public function iProceedToCheckoutWith(TableNode $table): void
    {
        $this->cartPage->fillCheckoutDetails($table);
    }

    /**
     * @Then The total price before tax should equal the sum of item prices
     */
    public function theTotalPriceBeforeTaxShouldEqualTheSumOfItemPrices(): void
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
    public function theFinalPriceShouldEqualItemTotalPlusTax(): void
    {
        $subtotal = $this->cartPage->getSummarySubTotalPrice();
        $tax = $this->cartPage->getTaxPrice();
        $total = $this->cartPage->getSummaryTotalPrice();
        Assert::assertEquals($total, $subtotal + $tax);
    }

    /**
     * @Then I should see the message :text
     */
    public function iShouldSeeTheMessage($text): void
    {
        $message = $this->cartPage->getCheckoutCompleteMessage();
        Assert::assertTrue(stripos($message, $text) !== false);
    }

    /**
     * @Then I Click Finish
     * @throws ElementNotFoundException
     */
    public function iClickFinish(): void
    {
        $this->cartPage->clickFinish();
    }



    /**
     * @When I refresh the page
     */
    public function iRefreshThePage(): void
    {
        $this->getSession()->reload();
        $this->session->wait(2000);
    }

    /**
     * @Then I should still see the inventory page
     */
    public function iShouldStillSeeTheInventoryPage(): void
    {
        Assert::assertTrue($this->inventoryPage->isInventoryPageVisible());
    }

    /**
     * @When I return to the inventory page
     */
    public function iReturnToTheInventoryPage(): void
    {
        $this->inventoryPage->visit();
    }

    /**
     * @Then the cart should contain :count items
     */
    public function theCartShouldContainItems($count): void
    {
        Assert::assertTrue(
            (string) $this->inventoryPage->getShoppingCartItemCount() === (string) $count,
            sprintf(
                'Expected cart item count to be %s, but got %s',
                (string) $count,
                (string) $this->inventoryPage->getShoppingCartItemCount()
            )
        );
    }

    /**
     * @When I click the checkout button
     * @throws ElementNotFoundException
     */
    public function iClickTheCheckoutButton(): void
    {
        $this->inventoryPage->clickShoppingCartIcon();
    }

    /**
     * @Then I should see empty cart
     */
    public function iShouldSeeEmptyCart(): void
    {
        Assert::assertEquals($this->cartPage->getSumOfAllItemPrices(), 0.0);
    }

    /**
     * @When I log out
     * @throws ElementNotFoundException
     */
    public function iLogOut(): void
    {
        $this->inventoryPage->clickMenuIcon();
        $this->session->wait(2000);
        $this->inventoryPage->clickMenuLogout();
        $this->session->wait(2000);
    }

    /**
     * @Then I should see the login page
     *
     */
    public function iShouldSeeTheLoginPage(): void
    {
        $this->loginPage->isLoginPageVisible();
    }
}
