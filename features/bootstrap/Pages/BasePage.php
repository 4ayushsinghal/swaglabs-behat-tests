<?php
namespace Pages;

use Behat\Mink\Session;

abstract class BasePage
{
    protected $session;
    protected $page;

    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->page = $session->getPage();
    }

    protected function find($selector, $locator)
    {
        return $this->page->find($selector, $locator);
    }
}
