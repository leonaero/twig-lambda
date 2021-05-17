<?php

declare(strict_types=1);

namespace LeonAero\TwigLambda;

use ArrayAccess;
use Closure;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Serializable;
use Traversable;

class Dictionary implements Countable, ArrayAccess, Serializable, IteratorAggregate
{
	private array $data = [];
	private array $keys = [];

	/**
	 * Create hash for each element.
	 */
	protected function generateHash($value): string
	{
		if (is_object($value)) {
			if ($value instanceof Closure) {
				throw new InvalidArgumentException("Closure cannot be Dictionary key.");
			}
			return 'object:' . spl_object_hash($value);
		}
		if (is_string($value)) {
			return 'string:' . $value;
		}
		if (is_int($value)) {
			return 'int:' . $value;
		}
		if (is_float($value)) {
			return 'float:' . $value;
		}
		if (is_bool($value)) {
			return 'bool:' . ((int)$value);
		}
		if (is_null($value)) {
			return 'null:null';
		}
		throw new InvalidArgumentException("Invalid Dictionary key.");
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIterator(): DictionaryIterator
	{
		return new DictionaryIterator($this);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset)
	{
		$hash = $this->generateHash($offset);
		return isset($this->data[$hash]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function &offsetGet($offset)
	{
		$hash = $this->generateHash($offset);
		return $this->data[$hash];
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value)
	{
		$hash = $this->generateHash($offset);
		if (!isset($this->keys[$hash])) {
			$this->keys[$hash] = $offset;
		}
		$this->data[$hash] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset)
	{
		$hash = $this->generateHash($offset);
		unset($this->data[$hash], $this->keys[$hash]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function serialize()
	{
		$pairs = [];
		foreach ($this as $key => $value) {
			$pairs[] = [$key, $value];
		}
		return serialize($pairs);
	}

	/**
	 * {@inheritdoc}
	 */
	public function unserialize($data)
	{
		$pairs = unserialize($data, ['allowed_classes' => true]);
		foreach ($pairs as $pair) {
			$this[$pair[0]] = $pair[1];
		}
	}

	/**
	 * Create new Dictionary from standard PHP array.
	 *
	 * @param  $array|Traversable Array to create Dictionary from.
	 */
	public static function fromArray($array): Dictionary
	{
		if (!is_array($array) && !($array instanceof Traversable)) {
			throw new InvalidArgumentException(
				sprintf(
					'Dictionary::fromArray() argument must be array or Traversable, ' . 'but is "%s".',
					gettype($array)
				)
			);
		}

		$result = new self();
		foreach ($array as $key => $value) {
			$result[$key] = $value;
		}
		return $result;
	}

	/**
	 * Return array with all keys stored in dictionary.
	 *
	 * @return  array
	 */
	public function keys()
	{
		return array_values($this->keys);
	}

	/**
	 * Return array with all values stored in dictionary.
	 *
	 * @return  array
	 */
	public function values()
	{
		return array_values($this->data);
	}
}
