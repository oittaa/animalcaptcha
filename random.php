<?php
if (!function_exists('random_bytes')) {
    /**
     * PHP 5.3.0 - 5.6.x way to implement random_bytes()
     * 
     * @param int $bytes
     * @return string
     */
    function random_bytes($bytes)
    {
        if (!is_int($bytes) || $bytes < 1) {
             throw new InvalidArgumentException('random_bytes() expects a positive integer');
        }
        $secure = true;
        $buf = openssl_random_pseudo_bytes($bytes, $secure);
        if (!$secure) {
            throw new \Exception('Better for PRNG failures to terminate than propagate');
        }
        return $buf;
    }
}

if (!function_exists('random_int')) {
    function random_int($min, $max)
    {
        if (!is_int($min) || !is_int($max)) {
             throw new InvalidArgumentException('random_int() expects two positive integers');
        }
        if ($min >= $max) {
             throw new InvalidArgumentException('$min must be less than $max');
        }
        $range = $max - $min;
        // Test for integer overflow:
        if (!is_int($range)) {
             throw new InvalidArgumentException('Integer overflow');
        }
        // Do we have a meaningful range?
        if ($range < 1) {
            return $min;
        }
        
        // Initialize variables to 0
        $bits = $bytes = $mask = 0;
        
        $tmp = $range;
        /**
         * We want to store:
         * $bytes => the number of random bytes we need
         * $mask => an integer bitmask (for use with the &) operator
         *          so we can minimize the number of discards
         */
        while ($tmp > 0) {
            if ($bits % 8 === 0) {
               ++$bytes;
            }
            ++$bits;
            $tmp >>= 1;
            $mask = $mask << 1 | 1;
        }
        
        /**
         * Now that we have our parameters set up, let's begin generating
         * random integers until one falls within $range
         */
        do {
            $rval = random_bytes($bytes);
            if ($rval === false) {
                throw new Exception('Random number generator failure');
            }
            
            /**
             * Let's turn $rval (random bytes) into an integer
             * 
             * This uses bitwise operators (<< and |) to build an integer
             * out of the values extracted from ord()
             * 
             * Example: [9F] | [6D] | [32] | [0C] =>
             *   159 + 27904 + 3276800 + 201326592 =>
             *   204631455
             */
            $val = 0;
            for ($i = 0; $i < $bytes; ++$i) {
                $val |= (ord($rval[$i]) << ($i * 8));
            }
            
            // Apply mask
            $val = $val & $mask;
            
            // If $val is larger than the maximum acceptable number for
            // $min and $max, we discard and try again.
        } while ($val > $range);
        return (int) ($min + $val) & PHP_INT_MAX;
    }
}

function random_str($length = 26, $alphabet = 'abcdefghijklmnopqrstuvwxyz234567')
{
    if ($length < 1) {
        throw new InvalidArgumentException('Length must be a positive integer');
    }
    $str = '';
    $alphmax = strlen($alphabet) - 1;
    if ($alphmax < 1) {
        throw new InvalidArgumentException('Invalid alphabet');
    }
    for ($i = 0; $i < $length; ++$i) {
        $str .= $alphabet[random_int(0, $alphmax)];
    }
    return $str;
}
