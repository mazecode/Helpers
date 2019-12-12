<?php

namespace Siga98\Helpers\Test;

use PHPUnit\Framework\TestCase;

abstract class BaseCase extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();
	}
	
	/**
	 * @param $data
	 *
	 * @return false|string
	 */
	public function generateMessage($data)
	{
		return (is_string($data)) ? $data : json_encode(['data' => $data]);
	}
}