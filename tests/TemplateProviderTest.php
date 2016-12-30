<?php

class TemplateProviderTest extends \PHPUnit_Framework_TestCase {
    private $filePersisterMock;
    private $localizedTemplateName;
    private $localizedTemplatePath;
    private $defaultTemplateName;
    private $defaultTemplatePath;
    private $templateProvider;

    protected function setUp() {
        $this->filePersisterMock = $this->getMockBuilder(Services\FilePersisterInterface::class)
            ->setMethods(['exists'])
            ->getMockForAbstractClass();
        
        $templatePath = 'templates';
        $this->localizedTemplateName = 'name.en.ext';
        $this->localizedTemplatePath = $templatePath . '/' . $this->localizedTemplateName;
        $this->defaultTemplateName = 'name.default.ext';
        $this->defaultTemplatePath = $templatePath . '/' . $this->defaultTemplateName;

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
        $name = $this->templateProvider->getPath('name', 'en', 'ext');
        $this->assertSame($this->localizedTemplateName, $name);
    }

    public function testReturnDefaultPathIfLocalizedPathDoesNotExist() {
        $existsValueMap = [
            [ $this->localizedTemplatePath, false ],
            [ $this->defaultTemplatePath , true ]
        ];
        $this->filePersisterMock
            ->method('exists')
            ->will($this->returnValueMap($existsValueMap));
        $name = $this->templateProvider->getPath('name', 'en', 'ext');
        $this->assertSame($this->defaultTemplateName, $name);
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