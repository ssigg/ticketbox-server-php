<?php

class TemplateProviderTest extends \PHPUnit_Framework_TestCase {
    private $filePersisterMock;
    private $localizedTemplatePath;
    private $defaultTemplatePath;
    private $templateProvider;

    protected function setUp() {
        $this->filePersisterMock = $this->getMockBuilder(Services\FilePersisterInterface::class)
            ->setMethods(['exists'])
            ->getMockForAbstractClass();
        
        $templatePath = 'templates';
        $this->localizedTemplatePath = $templatePath . '/name.en.ext';
        $this->defaultTemplatePath = $templatePath . '/name.default.ext';

        $this->templateProvider = new Services\TemplateProvider($this->filePersisterMock, $templatePath);
    }

    public function testReturnLocalizedPathIfItExists() {
        $existsValueMap = [
            [ $this->localizedTemplatePath, true ],
            [ $this->defaultTemplatePath , true ]
        ];
        $this->filePersisterMock
            ->method('exists')
            ->will($this->returnValueMap($existsValueMap));
        $path = $this->templateProvider->getPath('name', 'en', 'ext');
        $this->assertSame($this->localizedTemplatePath, $path);
    }

    public function testReturnDefaultPathIfLocalizedPathDoesNotExist() {
        $existsValueMap = [
            [ $this->localizedTemplatePath, false ],
            [ $this->defaultTemplatePath , true ]
        ];
        $this->filePersisterMock
            ->method('exists')
            ->will($this->returnValueMap($existsValueMap));
        $path = $this->templateProvider->getPath('name', 'en', 'ext');
        $this->assertSame($this->defaultTemplatePath, $path);
    }

    public function testThrowsExceptionWhenNoTemplateFileExists() {
        $existsValueMap = [
            [ $this->localizedTemplatePath, false ],
            [ $this->defaultTemplatePath , false ]
        ];
        $this->filePersisterMock
            ->method('exists')
            ->will($this->returnValueMap($existsValueMap));

        $this->setExpectedException(\Exception::class);
        $this->templateProvider->getPath('name', 'en', 'ext');
    }
}