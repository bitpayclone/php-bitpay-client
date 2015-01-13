<?php
/**
 * @license Copyright 2011-2014 BitPay Inc., MIT License
 * see https://github.com/bitpay/php-bitpay-client/blob/master/LICENSE
 */

namespace Bitpay\Math;

/**
 * Math interface class which handles the routing to the correct engine,
 * either GMP or BC, for all math operations with the former preferred.
 * 
 * @package Bitpay
 */
class Math
{
    private static $engine = null;

    /**
     * Sets the Math::$engine property if not null.
     * 
     * @param object $engine  Either a GMP or BC Math engine object.
     */
    public static function setEngine($engine)
    {
        if (true === isset($engine)) {
            static::$engine = $engine;
        }
    }

    /**
     * Returns the Math::$engine object or null if not set.
     * 
     * @return object|null  Can be null, otherwise returns a GMP or BC Math engine object.
     */
    public static function getEngine()
    {
        return static::$engine;
    }

    /**
     * Intercepts the call for math operation and hands it off to
     * the correct mathematics engine, either a GMP or BC Math object.
     * 
     * @param  string $name      The math operation name, i.e. add, mod, sub...
     * @param  mixed  $arguments The operations for that particular operation.
     * @return mixed             Either result of the operation or error.
     */
    public static function __callStatic($name, $arguments)
    {
        if (false === isset(static::$engine) || true === empty(static::$engine)) {
            if (true === extension_loaded('gmp')) {
                static::$engine = new GmpEngine();
            } else if (true === extension_loaded('bcmath')) {
                static::$engine = new BcEngine();
            } else {
                throw new \Exception('[ERROR] In Math::__callStatic(): The GMP or BCMATH extension for PHP is required but neither are found on this system.');
            }
        }

        /*
         * Check to ensure our math function name and the
         * requisite parameters were passed to this function.
         */
        if (true  === isset($name) && true  === isset($arguments) &&
            false === empty($name) && false === empty($arguments)) {

            return call_user_func_array(array(static::$engine, $name), $arguments);

        } else {
            throw new \Exception('[ERROR] In Math::__callStatic(): Missing or invalid $name or $arguments parameters.');
        }

    }
}
