<?php

namespace Pages;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Exception;

class CartPage extends BasePage
{
    // Locators
    private $checkoutInfoFirstName = 'firstName';
    private $checkoutInfoLastName = 'lastName';
    private $checkoutInfoPostalCode = 'postalCode';
    private $checkoutContinueButton = 'continue';
    private $checkoutItems = '.inventory_item_price';
    private $summarySubtotal = '.summary_subtotal_label';
    private $summaryTax = '.summary_tax_label';
    private $summaryTotal = '.summary_total_label';
    private $checkoutCompleteMessage = '[data-test="complete-header"]';
    private $finishButtonId = 'finish';
    private $summarySubTotalRegex = '/Item total:\s*\$(\d+\.\d{2})/';
    private $summaryTaxRegex = '/Tax:\s*\$(\d+\.\d{2})/';
    private $summaryTotalRegex = '/Total:\s*\$(\d+\.\d{2})/';

    public function visit($url = 'https://www.saucedemo.com/cart.html')
    {
        $this->session->visit($url);
        $this->session->wait(2000);
    }

    /**
     * @throws ElementNotFoundException
     */
    public function fillCheckoutDetails(TableNode $table)
    {
        // assume we are on the cart page
        $this->page->pressButton('checkout');
        $data = $table->getRowsHash();
        $this->page->fillField($this->checkoutInfoFirstName, $data['first_name']);
        $this->page->fillField($this->checkoutInfoLastName, $data['last_name']);
        $this->page->fillField($this->checkoutInfoPostalCode, $data['postal_code']);
        $this->page->pressButton($this->checkoutContinueButton);
        $this->session->wait(1000);
    }

    protected function parsePrice($text): float
    {
        // strips $ and converts to float
        $n = preg_replace('/[^0-9.]/', '', $text);
        return (float) $n;
    }

    public function getSumOfAllItemPrices(): float
    {
        $priceEls = $this->page->findAll('css', $this->checkoutItems);
        $sum = 0.0;
        if (count($priceEls) < 1) {
            return $sum;
        }
        foreach ($priceEls as $el) {
            $sum += $this->parsePrice($el->getText());
        }
        return $sum;
    }

    private function getPregMatchPrice($regex, $element): float
    {
        $subtotalEl = $this->page->find('css', $element);
        if (!$subtotalEl) {
            throw new Exception('Subtotal element not found');
        }
        preg_match($regex, $subtotalEl->getText(), $m);
        if (!isset($m[1])) {
            throw new Exception('Could not parse subtotal label: ' . $subtotalEl->getText());
        }
        return (float) $m[1];
    }

    /**
     * @throws Exception
     */
    public function getSummarySubTotalPrice(): float
    {
        return $this->getPregMatchPrice($this->summarySubTotalRegex, $this->summarySubtotal);
    }

    /**
     * @throws Exception
     */
    public function getTaxPrice(): float
    {
        return $this->getPregMatchPrice($this->summaryTaxRegex, $this->summaryTax);
    }

    /**
     * @throws Exception
     */
    public function getSummaryTotalPrice(): float
    {
        return $this->getPregMatchPrice($this->summaryTotalRegex, $this->summaryTotal);
    }

    public function getCheckoutCompleteMessage(): string
    {
        return $this->page->find('css', $this->checkoutCompleteMessage)->getText();
    }

    /**
     * @throws ElementNotFoundException
     */
    public function clickFinish(){
        $this->page->pressButton($this->finishButtonId);
    }
}