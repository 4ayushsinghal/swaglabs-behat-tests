<?php

namespace Pages;

class InventoryPage extends BasePage
{
    // Locators
    private $sortSelect = '[data-test="product-sort-container"]';
    private $title = '[data-test="title"]';
    private $inventoryItems = '.inventory_item';

    public function visit($url = '/inventory.html')
    {
        $this->session->visit($url);
    }

    public function isInventoryPageVisible(): bool
    {
        $title = $this->page->find('css', $this->title);
        if (!$title || strpos($title->getText(), 'Products') === false) {
            return false;
        }
        return true;
    }


    public function selectSortOption($option)
    {
        $select = $this->page->find('css', $this->sortSelect);
        if (!$select) {
            throw new \Exception('Sort select not found');
        }
        // select by visible text
        $select->selectOption($option);
        // small wait for reorder
        $this->session->wait(1000);
    }

    public function getAllInventoryItemNames(): array
    {
        $items = $this->page->findAll('css', $this->inventoryItems);
        if (count($items) < 2) {
            throw new \Exception('Not enough products found to verify sorting');
        }
        return array_map(function ($el) {
            return trim($el->getText());
        }, $items);
    }

    public function addItemToTheCart($productName)
    {
        $items = $this->page->findAll('css', $this->inventoryItems);
        foreach ($items as $item) {
            $nameEl = $item->find('css', '.inventory_item_name');
            if ($nameEl && trim($nameEl->getText()) === $productName) {
                $btn = $item->find('css', 'button');
                if ($btn) {
                    $btn->click();
                    // short wait for UI update
                    $this->session->wait(500);
                    return;
                }
            }
        }
        throw new \Exception("Product '$productName' not found to add to cart");
    }
}