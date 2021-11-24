<?php

namespace OCA\ScienceMesh\Tests\Unit\Controller;

use PHPUnit_Framework_TestCase;
use OCP\Security\ISecureRandom;
use OCA\ScienceMesh\TokenHandler\SciencemeshToken;

class SciencemeshTokenTest extends PHPUnit_Framework_TestCase {

	/** @var  TokenHandler */
	private $token;

	/** @var  ISecureRandom | \PHPUnit\Framework\MockObject\MockObject */
	private $secureRandom;

	/** @var int */
	private $expectedTokenLength = 15;

	protected function setUp(): void {
		parent::setUp();

		$this->secureRandom = $this->getMockBuilder(ISecureRandom::class)->getMock();

		$this->token = new SciencemeshToken($this->secureRandom);
	}

	public function testGenerateToken() {
		$this->secureRandom->expects($this->once())->method('generate')
			->with(
				$this->expectedTokenLength,
				ISecureRandom::CHAR_ALPHANUMERIC
			)
			->willReturn('mytoken');

		$this->assertSame('mytoken', $this->token->generateToken());
	}
}
