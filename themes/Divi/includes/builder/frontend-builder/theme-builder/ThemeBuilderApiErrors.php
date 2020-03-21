<?php

class ET_Theme_Builder_Api_Errors {
	const UNKNOWN                                                = 'unknown';
	const PORTABILITY_INCORRECT_CONTEXT                          = 'incorrect_context';
	const PORTABILITY_REQUIRE_INCOMING_LAYOUT_DUPLICATE_DECISION = 'require_incoming_layout_duplicate_decision';
	const PORTABILITY_IMPORT_DEFAULTS_FAILURE                    = 'import_defaults_failure';

	/**
	 * Get map of all error codes.
	 *
	 * @since 4.0
	 *
	 * @return string[]
	 */
	public static function getMap() {
		return array(
			'unknown'                                           => self::UNKNOWN,
			'portabilityIncorrectContext'                       => self::PORTABILITY_INCORRECT_CONTEXT,
			'portabilityRequireIncomingLayoutDuplicateDecision' => self::PORTABILITY_REQUIRE_INCOMING_LAYOUT_DUPLICATE_DECISION,
			'portabilityImportDefaultsFailure'                  => self::PORTABILITY_IMPORT_DEFAULTS_FAILURE,
		);
	}
}
