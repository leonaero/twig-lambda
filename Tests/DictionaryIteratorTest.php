<?php

declare(strict_types=1);

namespace LeonAero\TwigLambda\Tests;

use LeonAero\TwigLambda\Dictionary;
use LeonAero\TwigLambda\DictionaryIterator;
use PHPUnit\Framework\TestCase;
use stdClass;

class DictionaryIteratorTest extends TestCase
{
	public function testIterator(): void
	{
		$object1 = new stdClass();
		$object2 = new stdClass();
		$object3 = new stdClass();

		$d = new Dictionary();
		$d[4] = $object1;
		$d['abc'] = $object2;
		$d[$object3] = $this;

		$it = new DictionaryIterator($d);

		$it->rewind();
		self::assertSame(4, $it->key());
		self::assertSame($object1, $it->current());
		self::assertTrue($it->valid());

		$it->next();
		self::assertSame('abc', $it->key());
		self::assertSame($object2, $it->current());
		self::assertTrue($it->valid());

		$it->next();
		self::assertSame($object3, $it->key());
		self::assertSame($this, $it->current());
		self::assertTrue($it->valid());

		$it->next();
		self::assertFalse($it->valid());

		$it->rewind();
		self::assertTrue($it->valid());
	}
}
