<?php // phpcs:ignore

/**
 * Signature handling model helper
 */
class Snapshot_Model_Full_Remote_Signature {

	/**
	 * Gets signature component (key/value pair) delimiter
	 *
	 * @return string
	 */
	public function get_sig_delimiter () {
		return ':';
	}

	/**
	 * Gets signature component (pairs) delimiter
	 *
	 * @return string
	 */
	public function get_sig_pair_delimiter () {
		return '::';
	}

	/**
	 * Gets signature algorithm delimiter
	 *
	 * @return string
	 */
	public function get_algo_delimiter () {
		return '//';
	}

	/**
	 * Gets signature component (key/value pair) as string
	 *
	 * @param string $key Key component portion
	 * @param string $value Value component portion
	 *
	 * @return string
	 */
	public function get_hash_sig_pair ($key, $value) {
		return $key . $this->get_sig_delimiter() . $value;
	}

	/**
	 * Gets raw (plaintext) signature string from array of components
	 *
	 * @param array $components Components to generate the signature from
	 *
	 * @return string
	 */
	public function get_raw_signature ($components= array()) {
		$result = array();
		foreach ($components as $key => $value) {
			$result[] = $this->get_hash_sig_pair($key, $value);
		}
		return join($this->get_sig_pair_delimiter(), $result);
	}

	/**
	 * Gets the hashed version of the plaintext signature
	 *
	 * @param string $raw Plaintext signature
	 * @param string $secret HMAC key
	 * @param string $algo Preferred hash algorithm to use
	 *
	 * @return string
	 */
	public function get_hashed_signature ($raw, $secret, $algo) {
		$algo = $this->is_preferred_algo($algo) ? $algo : $this->get_default_algo();
		return $algo . $this->get_algo_delimiter() . hash_hmac($algo, $raw, $secret);
	}


	/**
	 * Gets hashed signature from array of signature components
	 *
	 * @param array $components Components to generate the signature from
	 * @param string $secret Secret value to use as HMAC key
	 * @param string $algo Optional preferred algorithm
	 *
	 * @return string
	 */
	public function get_signature ($components= array(), $secret, $algo= false) {
		$raw = $this->get_raw_signature($components);
		return $this->get_hashed_signature($raw, $secret, $algo);
	}

	/**
	 * Check if a signature is in valid format
	 *
	 * @param string $signature Signature to check
	 *
	 * @return bool
	 */
	public function is_valid_signature ($signature= false) {
		if (empty($signature) || !is_string($signature)) return false;

		$valid = false;
		$algos = $this->get_preferred_algos();
		foreach ($algos as $algo) {
			if (!preg_match(
				'/^' .
					preg_quote($algo, '/') .
					preg_quote($this->get_algo_delimiter(), '/') .
				'[a-zA-Z0-9]/', $signature
                )
			) continue;

			$valid = true;
			break;
		}

		return $valid;
	}

	/**
	 * Gets extracted signature algorithm
	 *
	 * Falls back to default implementation algorithm
	 *
	 * @param string $signature Signature to extract the algo from
	 *
	 * @return string
	 */
	public function get_signature_algo ($signature= false) {
		$fallback = $this->get_default_algo();
		$algo = false;

		if ($this->is_valid_signature($signature)) {
			$tmp = explode($this->get_algo_delimiter(), $signature, 2);
			$algo = !empty($tmp[0]) && $this->is_preferred_algo($tmp[0])
				? $tmp[0]
				: false
			;
		}

		return !empty($algo) ? $algo : $fallback;
	}

	/**
	 * Checks if we know the requested hash algorithm
	 *
	 * @param string $algo Hash algorithm
	 *
	 * @return bool
	 */
	public function is_known_algo ($algo= false) {
		if (empty($algo)) return false;
		return in_array( $algo, hash_algos(), true );
	}

	/**
	 * Checks whether the hash algorithm is known locally, and allowed for usage
	 *
	 * @param string $algo Hash algorithm
	 *
	 * @return bool
	 */
	public function is_preferred_algo ($algo= false) {
		if (!$this->is_known_algo($algo)) return false;
		return in_array( $algo, $this->get_preferred_algos(), true );
	}

	/**
	 * Gets a finite list of preferred hash algorithms
	 *
	 * Hash algorithm are listed in order of preference
	 *
	 * @return array
	 */
	public function get_preferred_algos () {
		return array(
			'sha512',
			'sha256',
			'sha1',
			'md5',
		);
	}

	/**
	 * Gets the preferred hash algorithm
	 *
	 * @param string $preferred Optional preferred hash algorithm
	 *
	 * @return string
	 */
	public function get_preferred_algo ($preferred= false) {
		$preferred = !empty($preferred) ? $preferred : $this->get_default_algo();

		if (!$this->is_preferred_algo($preferred)) {
			$preferred = $this->get_default_algo();
		}

		return $preferred;
	}

	/**
	 * Gets the default hash algorithm for usage
	 *
	 * @return string
	 */
	public function get_default_algo () {
		static $preferred;

		if (!$preferred) {
			foreach ($this->get_preferred_algos() as $algo) {
				if (!$this->is_known_algo($algo)) continue;

				$preferred = $algo;
				break;
			}
		}

		return $preferred;
	}

}