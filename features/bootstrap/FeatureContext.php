<?php
require_once 'vendor/autoload.php';

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkContext;

class FeatureContext extends MinkContext implements Context
{
    /**
     * @Given I am on the login page
     */
    public function iAmOnTheLoginPage()
    {
        $this->visitPath('/');
    }

    /**
     * @When I log in with username :username and password :password
     */
    public function iLogInWithUsernameAndPassword($username, $password)
    {
        $this->fillField('user-name', $username);
        $this->fillField('password', $password);
        $this->pressButton('login-button');
    }

    /**
     * @Then I should see an error containing :text
     */
    public function iShouldSeeAnErrorContaining($text)
    {
        $page = $this->getSession()->getPage();
        // SauceDemo shows errors in element with data-test="error" or class .error-message-container
        $el = $page->find('css', '[data-test="error"]');
        if (!$el) {
            $el = $page->find('css', '.error-message-container');
        }

        if (!$el) {
            throw new \Exception('Error element not found on the page.');
        }

        $content = $el->getText();
        if (strpos($content, $text) === false) {
            throw new \Exception(sprintf("Expected error containing '%s' but got '%s'", $text, $content));
        }
    }

    /**
     * @Given /^I am logged in as "([^"]*)" with password "([^"]*)"$/
     */
    public function iAmLoggedInAsWithPassword($username, $password)
    {
        $this->visitPath('/');
        $this->iLogInWithUsernameAndPassword($username, $password);
        // wait for inventory page
        $this->getSession()->wait(2000);
        $page = $this->getSession()->getPage();
        $title = $page->find('css', '.title');
        if (!$title || strpos($title->getText(), 'Products') === false) {
            throw new \Exception('Login failed or Products page not found');
        }
    }

    /**
     * @When I sort products by :option
     */
    public function iSortProductsBy($option)
    {
        $page = $this->getSession()->getPage();
        $select = $page->find('css', '[data-test="product-sort-container"]');
        if (!$select) {
            throw new \Exception('Sort select not found');
        }
        // select by visible text
        $select->selectOption($option);
        // small wait for reorder
        $this->getSession()->wait(1000);
    }

    /**
     * @Then the product list should be sorted in reverse alphabetical order
     */
    public function theProductListShouldBeSortedInReverseAlphabeticalOrder()
    {
        $page = $this->getSession()->getPage();
        $items = $page->findAll('css', '.inventory_item_name');
        if (count($items) < 2) {
            throw new \Exception('Not enough products found to verify sorting');
        }
        $names = array_map(function ($el) {
            return trim($el->getText());
        }, $items);
        $sorted = $names;
        rsort($sorted, SORT_NATURAL | SORT_FLAG_CASE);
        if ($sorted !== $names) {
            throw new \Exception('Product list is not sorted in reverse alphabetical order. Found: ' . implode(', ', $names));
        }
    }

    /**
     * @When I add :productName to the cart
     */
    public function iAddToTheCart($productName)
    {
        $page = $this->getSession()->getPage();
        $items = $page->findAll('css', '.inventory_item');
        foreach ($items as $item) {
            $nameEl = $item->find('css', '.inventory_item_name');
            if ($nameEl && trim($nameEl->getText()) === $productName) {
                $btn = $item->find('css', 'button');
                if ($btn) {
                    $btn->click();
                    // short wait for UI update
                    $this->getSession()->wait(500);
                    return;
                }
            }
        }
        throw new \Exception("Product '$productName' not found to add to cart");
    }

    /**
     * @When I go to the cart
     */
    public function iGoToTheCart()
    {
        $this->visitPath('/cart.html');
        $this->getSession()->wait(500);
    }

    /**
     * @When I proceed to checkout with:
     */
    public function iProceedToCheckoutWith(TableNode $table)
    {
        // assume we are on the cart page
        $this->pressButton('checkout');
        $data = $table->getRowsHash();
        $this->fillField('firstName', $data['first_name']);
        $this->fillField('lastName', $data['last_name']);
        $this->fillField('postalCode', $data['postal_code']);
        $this->pressButton('continue');
        $this->getSession()->wait(1000);
    }

    protected function parsePrice($text)
    {
        // strips $ and converts to float
        $n = preg_replace('/[^0-9.]/', '', $text);
        return (float) $n;
    }

    /**
     * @Then the total price before tax should equal the sum of item prices
     */
    public function theTotalPriceBeforeTaxShouldEqualTheSumOfItemPrices()
    {
        $page = $this->getSession()->getPage();
        $priceEls = $page->findAll('css', '.inventory_item_price');
        if (count($priceEls) < 1) {
            throw new \Exception('No item prices found on overview page');
        }
        $sum = 0.0;
        foreach ($priceEls as $el) {
            $sum += $this->parsePrice($el->getText());
        }
        // fetch item total label
        $subtotalEl = $page->find('css', '.summary_subtotal_label');
        if (!$subtotalEl) {
            throw new \Exception('Subtotal element not found');
        }
        preg_match('/Item total:\s*\$(\d+\.\d{2})/', $subtotalEl->getText(), $m);
        if (!isset($m[1])) {
            throw new \Exception('Could not parse subtotal label: ' . $subtotalEl->getText());
        }
        $subtotal = (float) $m[1];
        // compare with small epsilon
        if (abs($subtotal - $sum) > 0.01) {
            throw new \Exception(sprintf('Subtotal (%.2f) does not equal sum of items (%.2f)', $subtotal, $sum));
        }
    }

    /**
     * @Then the final price should equal item total plus tax
     */
    public function theFinalPriceShouldEqualItemTotalPlusTax()
    {
        $page = $this->getSession()->getPage();
        $subtotalEl = $page->find('css', '.summary_subtotal_label');
        $taxEl = $page->find('css', '.summary_tax_label');
        $totalEl = $page->find('css', '.summary_total_label');
        if (!$subtotalEl || !$taxEl || !$totalEl) {
            throw new \Exception('Summary labels not found on overview page');
        }
        preg_match('/Item total:\s*\$(\d+\.\d{2})/', $subtotalEl->getText(), $m);
        $subtotal = isset($m[1]) ? (float) $m[1] : $this->parsePrice($subtotalEl->getText());
        preg_match('/Tax:\s*\$(\d+\.\d{2})/', $taxEl->getText(), $m2);
        $tax = isset($m2[1]) ? (float) $m2[1] : $this->parsePrice($taxEl->getText());
        preg_match('/Total:\s*\$(\d+\.\d{2})/', $totalEl->getText(), $m3);
        $total = isset($m3[1]) ? (float) $m3[1] : $this->parsePrice($totalEl->getText());

        if (abs(($subtotal + $tax) - $total) > 0.01) {
            throw new \Exception(sprintf('Total (%.2f) does not equal subtotal + tax (%.2f)', $total, $subtotal + $tax));
        }
    }

    /**
     * @Then I should see the message :text
     */
    public function iShouldSeeTheMessage($text)
    {
        $page = $this->getSession()->getPage();
        $el = $page->find('css', 'h2.complete-header');
    }
    public function __construct()
    {
    }
}
