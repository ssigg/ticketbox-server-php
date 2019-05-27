<?php

class MailTemplateParserTest extends \PHPUnit_Framework_TestCase {
    private $filePersisterMock;

    protected function setUp() {
        $this->filePersisterMock = $this->getMockBuilder(Services\FilePersisterInterface::class)
            ->setMethods(['read'])
            ->getMockForAbstractClass();
        
        $frontMatter = new \Webuni\FrontMatter\FrontMatter();
        
        $loader = new \Twig_Loader_String();
        $twig = new \Twig_Environment($loader, [ 'cache' => false ]);
        
        $this->parser = new Services\MailTemplateParser($this->filePersisterMock, $frontMatter, $twig);
    }

    public function testParseWithoutPlaceholders() {
        $template = "---\r\nsubject: Example subject\r\n---\r\nExample body";
        $this->filePersisterMock->method('read')->willReturn($template);
        $mailContents = $this->parser->parse($template, []);
        $this->assertSame('Example subject', $mailContents->subject);
        $this->assertSame('Example body', $mailContents->body);
    }

    public function testParseWithPlaceholdersWithoutParams() {
        $template = "---\r\nsubject: Example subject {{placeholder1}}\r\n---\r\nExample body {{placeholder2}}";
        $this->filePersisterMock->method('read')->willReturn($template);
        $mailContents = $this->parser->parse($template, []);
        $this->assertSame('Example subject ', $mailContents->subject);
        $this->assertSame('Example body ', $mailContents->body);
    }

    public function testParseWithPlaceholdersWithParams() {
        $template = "---\r\nsubject: Example subject {{placeholder1}}\r\n---\r\nExample body {{placeholder2}}";
        $this->filePersisterMock->method('read')->willReturn($template);
        $mailContents = $this->parser->parse($template, [ 'placeholder1' => 'foo', 'placeholder2' => 'bar' ]);
        $this->assertSame('Example subject foo', $mailContents->subject);
        $this->assertSame('Example body bar', $mailContents->body);
    }

    public function testParseWithPlaceholdersWithSuperfluousParams() {
        $template = "---\r\nsubject: Example subject {{placeholder1}}\r\n---\r\nExample body {{placeholder2}}";
        $this->filePersisterMock->method('read')->willReturn($template);
        $mailContents = $this->parser->parse($template, [ 'placeholder1' => 'foo', 'placeholder2' => 'bar', 'placeholder3' => 'baz' ]);
        $this->assertSame('Example subject foo', $mailContents->subject);
        $this->assertSame('Example body bar', $mailContents->body);
    }
}