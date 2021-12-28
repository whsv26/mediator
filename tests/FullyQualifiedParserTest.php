<?php

namespace Whsv26\Tests;

use Fp\Collections\ArrayList;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Whsv26\Mediator\Parsing\FullyQualifiedParser;
use Whsv26\Mediator\Parsing\HandlerMapParser;
use Whsv26\Tests\Dummy\DummyCommandOne;
use Whsv26\Tests\Dummy\DummyCommandOneHandler;
use Whsv26\Tests\Dummy\DummyQueryOne;
use Whsv26\Tests\Dummy\DummyQueryOneHandler;
use Whsv26\Tests\Dummy\DummyQueryTwo;
use Whsv26\Tests\Dummy\DummyQueryTwoHandler;

use function Fp\Collection\firstOf;
use function Fp\Evidence\proveString;

class FullyQualifiedParserTest extends TestCase
{
    public function testParsing(): void
    {
        $ast = (new ParserFactory())
            ->create(ParserFactory::PREFER_PHP7)
            ->parse(/** @lang InjectablePHP */ '<?php
                namespace Foo\Bar;

                use X\Y\DummyOne;
                use M\A;
                
                echo "1";
            ');

        $namespace = firstOf($ast ?? [], Namespace_::class)->getUnsafe();

        $parser = new FullyQualifiedParser($namespace);

        $this->assertEquals(
            'Foo\Bar\SomeId',
            $parser->parse(new Identifier('SomeId'))
        );

        $this->assertEquals(
            'M\A\C',
            $parser->parse(new Name('A\\C'))
        );

        $this->assertEquals(
            'Foo\Bar\C\D',
            $parser->parse(new Name('C\\D'))
        );
    }
}
