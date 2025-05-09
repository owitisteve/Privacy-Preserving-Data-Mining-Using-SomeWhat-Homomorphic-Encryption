<?php
class Paillier
{
    private $n;
    private $g;
    private $lambda;
    private $mu;
    private $r; // Fixed 'r' for deterministic encryption

    public function __construct($bit_length = 512, $fixed_r = "1234567890")
    {
        // Use fixed primes for repeatable key generation
        $p = gmp_nextprime(gmp_init("10123457689")); // Fixed p
        $q = gmp_nextprime(gmp_init("11234576891")); // Fixed q

        $this->n = gmp_mul($p, $q);
        $this->g = gmp_add($this->n, 1);
        $this->lambda = gmp_lcm(gmp_sub($p, 1), gmp_sub($q, 1));

        // Validate inverse (prevents fatal error)
        $l = gmp_div_q(gmp_sub(gmp_powm($this->g, $this->lambda, gmp_pow($this->n, 2)), 1), $this->n);
        $this->mu = @gmp_invert($l, $this->n);

        if ($this->mu === false) {
            die("Error: Unable to compute Paillier decryption parameters. Try different primes.");
        }

        $this->r = gmp_init($fixed_r); // Use fixed 'r' for deterministic encryption
    }

    public function encrypt($m)
    {
        $n2 = gmp_pow($this->n, 2);
        return gmp_mod(gmp_mul(gmp_powm($this->g, $m, $n2), gmp_powm($this->r, $this->n, $n2)), $n2);
    }

    public function decrypt($c)
    {
        $n2 = gmp_pow($this->n, 2);
        $l = gmp_div_q(gmp_sub(gmp_powm($c, $this->lambda, $n2), 1), $this->n);
        $decrypted = gmp_mod(gmp_mul($l, $this->mu), $this->n);

        // Ensure decrypted value is in correct range
        if (gmp_cmp($decrypted, gmp_div_q($this->n, 2)) > 0) {
            $decrypted = gmp_sub($decrypted, $this->n);
        }

        return $decrypted;
    }

    public function addEncrypted($c1, $c2)
    {
        $n2 = gmp_pow($this->n, 2);
        return gmp_mod(gmp_mul($c1, $c2), $n2);
    }
    public function convertStringToNumber($string)
{
    return gmp_init(bin2hex($string), 16); // Convert string to hexadecimal, then to GMP number
}

public function convertNumberToString($number)
{
    return hex2bin(gmp_strval($number, 16)); // Convert GMP number to hex, then to original string
}

}
?>
