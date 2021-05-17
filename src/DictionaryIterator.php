<?php

declare(strict_types=1);

namespace LeonAero\TwigLambda;

use Iterator;

class DictionaryIterator implements Iterator
{
	private array $data;
	private array $keys;

	public function __construct(Dictionary $dictionary)
	{
		$this->data = $dictionary->values();
		$this->keys = $dictionary->keys();
	}

	/**
	 * {@inheritdoc}
	 */
	public function current()
	{
		return current($this->data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function next()
	{
		next($this->data);
		next($this->keys);
	}

	/**
	 * {@inheritdoc}
	 */
	public function key()
	{
		return current($this->keys);
	}

	/**
	 * {@inheritdoc}
	 */
	public function valid()
	{
		return key($this->data) !== null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function rewind()
	{
		reset($this->data);
		reset($this->keys);
	}
}
