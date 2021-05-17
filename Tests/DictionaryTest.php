<?php

namespace LeonAero\TwigLambda\Tests;


use ArrayIterator;
use InvalidArgumentException;
use LeonAero\TwigLambda\Dictionary;
use PHPUnit\Framework\TestCase;
use stdClass;

class DictionaryTest extends TestCase
{

	/**
	 * @dataProvider getOffsets
	 */
	public function testArrayAccess($offset): void
	{
		$d = new Dictionary();
		self::assertFalse($d->offsetExists($offset));

		$d->offsetSet($offset, 34);
		self::assertTrue($d->offsetExists($offset));
		self::assertEquals(34, $d->offsetGet($offset));

		$d->offsetUnset($offset);
		self::assertFalse($d->offsetExists($offset));
	}

	public function getOffsets(): array
	{
		return [
			'int' => [12],
			'object' => [new stdClass()],
			'string' => ['foobar'],
			'bool' => [true],
			'null' => [null],
			'float' => [1.4],
		];
	}

	/**
	 * Check if Dictionary recognize difference between
	 * key types.
	 */
	public function testKeyTypeAwareness(): void
	{
		$keys = [
			1,
			0,
			true,
			false,
			'1',
			'0',
			1.0,
			0.0,
			null,
		];

		$d = new Dictionary();
		foreach ($keys as $value => $key) {
			$d[$key] = $value;
		}

		foreach ($keys as $value => $key) {
			self::assertEquals($value, $d[$key]);
		}
	}

	public function testCount(): void
	{
		$d = new Dictionary();

		self::assertEquals(0, $d->count());

		$d[1] = 12;
		$d[true] = new stdClass();
		$d[false] = new stdClass();
		$d['false'] = 'abc';
		$d['1'] = 'def';
		$d[null] = 13;
		$d[new stdClass()] = 1;

		self::assertEquals(7, $d->count());
	}

	/**
	 * @dataProvider getInvalidOffsets
	 */
	public function testOffsetSet_InvalidOffset_ThrowException($offset): void
	{
		$this->expectException(InvalidArgumentException::class);
		$d = new Dictionary();
		$d[$offset] = 1;
	}

	/**
	 * @dataProvider getInvalidOffsets
	 */
	public function testOffsetGet_InvalidOffset_ThrowException($offset): void
	{
		$this->expectException(InvalidArgumentException::class);
		$d = new Dictionary();
		$d[$offset];
	}

	/**
	 * @dataProvider getInvalidOffsets
	 */
	public function testOffsetExists_InvalidOffset_ThrowException($offset): void
	{
		$this->expectException(InvalidArgumentException::class);
		$d = new Dictionary();
		isset($d[$offset]);
	}

	/**
	 * @dataProvider getInvalidOffsets
	 */
	public function testOffsetUnset_InvalidOffset_ThrowException($offset): void
	{
		$this->expectException(InvalidArgumentException::class);
		$d = new Dictionary();
		unset($d[$offset]);
	}

	public function getInvalidOffsets(): array
	{
		return [
			'array' => [[1, 2, 3]],
			'Closure' => [
				static function () {
				}
			],
		];
	}

	public function testFromArray_CreateFromArray(): void
	{
		$array = [
			'ab' => new stdClass(),
			12 => 'cd',
			0 => 'ef',
			'gh' => 10.2,
		];
		$d = Dictionary::fromArray($array);

		self::assertSame($array['ab'], $d['ab']);
		self::assertSame($array[12], $d[12]);
		self::assertSame($array[0], $d[0]);
		self::assertSame($array['gh'], $d['gh']);
		self::assertEquals(4, $d->count());
	}

	public function testFromArray_CreateFromTraversable(): void
	{
		$array = [
			'ab' => new stdClass(),
			12 => 'cd',
			0 => 'ef',
			'gh' => 10.2,
		];
		$d = Dictionary::fromArray(new ArrayIterator($array));

		self::assertSame($array['ab'], $d['ab']);
		self::assertSame($array[12], $d[12]);
		self::assertSame($array[0], $d[0]);
		self::assertSame($array['gh'], $d['gh']);
		self::assertEquals(4, $d->count());
	}

	/**
	 * @dataProvider getInvalidArrays
	 */
	public function testFromArray_InvalidArray_ThrowException($array): void
	{
		$this->expectException(InvalidArgumentException::class);
		Dictionary::fromArray($array);
	}

	public function getInvalidArrays(): array
	{
		return [
			'int' => [12],
			'float' => [1.2],
			'string' => ['abcdef'],
			'object' => [new stdClass()],
			'null' => [null],
			'false' => [false],
			'true' => [true],
		];
	}

	public function testUnserialize(): void
	{
		$serialized = 'C:30:"LeonAero\TwigLambda\Dictionary":203:{a:5:{i:0;a:2:{i:0;O:8:"stdClass":0:{}i:1;i:12;}i:1;a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}i:2;a:2:{i:0;O:8:"stdClass":0:{}i:1;s:3:"tet";}i:3;a:2:{i:0;i:12;i:1;O:8:"stdClass":0:{}}i:4;a:2:{i:0;i:15;i:1;r:14;}}}';
		/* @var $d Dictionary */
		$d = unserialize($serialized);

		self::assertEquals('bar', $d['foo']);
		self::assertSame($d[12], $d[15]);
		self::assertCount(5, $d);

		$it = $d->getIterator();

		$it->rewind();
		$obj1 = $it->key();
		self::assertEquals(12, $it->current());
		$it->next();
		$it->next();
		$obj2 = $it->key();
		self::assertEquals('tet', $it->current());
		self::assertNotSame($obj1, $obj2);
	}

}
