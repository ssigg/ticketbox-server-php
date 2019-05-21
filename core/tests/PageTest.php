<?php

class PageTest extends \PHPUnit_Framework_TestCase {
    private $templateMock;
    private $twigMock;
    private $templateProviderMock;

    protected function setUp() {
        $this->templateMock = $this->getMockBuilder(\Twig_TemplateInterface::class)
            ->setMethods(['render'])
            ->getMockForAbstractClass();

        $this->twigMock = $this->getMockBuilder(\Twig_Environment::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadTemplate'])
            ->getMockForAbstractClass();
        $this->twigMock->method('loadTemplate')->willReturn($this->templateMock);

        $this->templateProviderMock = $this->getMockBuilder(Services\TemplateProviderInterface::class)
            ->setMethods(['getPath'])
            ->getMockForAbstractClass();

        $this->page = new Services\Page($this->twigMock, $this->templateProviderMock);
    }

    public function ticketValidatorStatusProvider() {
        return [ [ Services\TicketValidatorStatus::Ok ], [ Services\TicketValidatorStatus::Warning ], [ Services\TicketValidatorStatus::Error ] ];
    }
    /**
    * @dataProvider ticketValidatorStatusProvider
    */
    public function testTemplateProviderIsUsedToCreateTheTemplatePath($ticketValidatorStatus) {
        $this->templateProviderMock
            ->expects($this->once())
            ->method('getPath');
        $ticketValidatorResult = new Services\TicketValidatorResult([], $ticketValidatorStatus);
        $this->page->getTicketValidatorResultPage($ticketValidatorResult);
    }

    public function testExceptionIsThrownWhenTheTicketValidatorStatusIsUnknown() {
        $ticketValidatorResult = new Services\TicketValidatorResult([], 42);

        $this->setExpectedException('\Exception');
        $this->page->getTicketValidatorResultPage($ticketValidatorResult);
    }

    public function testTwigIsUsedToLoadTheTemplate() {
        $this->twigMock
            ->expects($this->once())
            ->method('loadTemplate');
        $ticketValidatorResult = new Services\TicketValidatorResult([], Services\TicketValidatorStatus::Ok);
        $this->page->getTicketValidatorResultPage($ticketValidatorResult);
    }

    public function testTemplateIsUsedToRenderTheTemplate() {
        $this->templateMock
            ->expects($this->once())
            ->method('render');
        $ticketValidatorResult = new Services\TicketValidatorResult([], Services\TicketValidatorStatus::Ok);
        $this->page->getTicketValidatorResultPage($ticketValidatorResult);
    }
}