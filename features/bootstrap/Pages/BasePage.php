<?php
namespace Pages;

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Session;

abstract class BasePage
{
    protected $session;
    protected $page;
    private $shoppingCartIcon = '[data-test="shopping-cart-link"]';
    private $menuIcon = 'react-burger-menu-btn';
    private $menuLogoutLink = 'logout_sidebar_link';

    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->page = $session->getPage();
    }

    protected function find($selector, $locator)
    {
        return $this->page->find($selector, $locator);
    }

    /**
     * @throws ElementNotFoundException
     */
    public function clickShoppingCartIcon(){
        $this->page->find('css', $this->shoppingCartIcon)->click();
    }

    public function getShoppingCartItemCount(): string
    {
        return $this->page->find('css', $this->shoppingCartIcon)->getText();
    }

    /**
     * @throws ElementNotFoundException
     */
    public function clickMenuIcon(){
        $this->page->pressButton($this->menuIcon);
    }

    /**
     * @throws ElementNotFoundException
     */
    public function clickMenuLogout(){
        $this->page->clickLink($this->menuLogoutLink);
    }
}
