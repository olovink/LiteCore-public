<?php
/** @noinspection PhpDocMissingThrowsInspection PhpUnhandledExceptionInspection */

namespace pocketmine\enum;

use ReflectionClass;

abstract class Enum
{
	/**
	 * Returns the value of the constant with the given name or null if it doesn't exist.
	 *
	 * @param string $name The name of the constant. Case sensitive!
	 * @return mixed
	 * @since 1.1
	 */
	static function valueOf(string $name): mixed {
		return @static::all()[$name];
	}

	/**
	 * Returns an array with all keys and their values of this enum.
	 *
	 * @return array
	 */
	static function all(): array {
		return (new ReflectionClass(get_called_class()))->getConstants();
	}

	/**
	 * Returns the name of the first constant with the given value or null if none is found.
	 *
	 * @param mixed $value
	 * @return string|null
	 * @since 1.2
	 */
	static function nameOf(mixed $value): ?string {
		foreach (static::all() as $enum_name => $enum_value) {
			if ($enum_value === $value) {
				return $enum_name;
			}
		}
		return null;
	}

	/**
	 * Returns true if this enum has a constant with the given name.
	 *
	 * @param string $name The name of the constant. Case sensitive!
	 * @return boolean
	 */
	static function validateName(string $name): bool {
		return array_key_exists($name, static::all());
	}

	/**
	 * Returns true if this enum has a constant with the given value.
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	static function validateValue(mixed $value): bool {
		return in_array($value, static::all());
	}
}
