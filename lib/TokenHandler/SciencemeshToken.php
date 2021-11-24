<?php

namespace OCA\ScienceMesh\TokenHandler;

use OCP\Security\ISecureRandom;

class SciencemeshToken {
	public const TOKEN_LENGTH = 15;

	/** @var ISecureRandom */
	private $secureRandom;

	/**
	 * TokenHandler constructor.
	 *
	 * @param ISecureRandom $secureRandom
	 */
	public function __construct(ISecureRandom $secureRandom) {
		$this->secureRandom = $secureRandom;
	}

	/**
	 * generate to token used to authenticate federated shares
	 *
	 * @return string
	 */
	public function generateToken() {
		$token = $this->secureRandom->generate(
			self::TOKEN_LENGTH,
			ISecureRandom::CHAR_ALPHANUMERIC);
		return $token;
	}
}
