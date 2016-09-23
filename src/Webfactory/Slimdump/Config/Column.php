<?php

namespace Webfactory\Slimdump\Config;

use Webfactory\Slimdump\Exception\InvalidDumpTypeException;

/**
 * Class Column
 * @package Webfactory\Slimdump\Config
 *
 * This is a class representation of a configured column.
 * This is _not_ a representation of a database column.
 */
class Column
{

    private $config = null;
    private $faker;

    /**
     * Column constructor.
     * @param \SimpleXMLElement $config
     * @throws InvalidDumpTypeException
     */
    public function __construct(\SimpleXMLElement $config)
    {
        $this->config = $config;
        $this->faker = \Faker\Factory::create();

        $attr = $config->attributes();
        $this->selector = (string) $attr->name;

        $const = 'Webfactory\Slimdump\Config\Config::' . strtoupper((string)$attr->dump);

        if (defined($const)) {
            $this->dump = constant($const);
        } else {
            throw new InvalidDumpTypeException(sprintf("Invalid dump type %s for column %s.", $attr->dump, $this->selector));
        }
    }

    /**
     * @return string
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * @return mixed
     */
    public function getDump() {
        return $this->dump;
    }

    /**
     * @param string $value
     * @return mixed|string
     */
    public function processRowValue($value) {
        if ($this->dump == Config::MASKED) {
            return preg_replace('/[a-z0-9]/i', 'x', $value);
        }

        if ($this->dump == Config::REPLACE) {
            return $this->config->attributes()->replacement;
        }

        if ($this->dump == Config::FIRSTNAME) {
            return $this->faker->firstName;
        }

        if ($this->dump == Config::LASTNAME) {
            return $this->faker->lastName;
        }

        if ($this->dump == Config::EMAIL) {
            return $this->faker->userName . $this->faker->randomDigitNotNull . $this->faker->safeEmailDomain;
        }

        if ($this->dump == Config::PHONE) {
            return $this->faker->numberBetween($min = 1111111111, $max = 9999999999);
        }

        if ($this->dump == Config::IDENTIFIER) {
            return $this->faker->slug . '-' . $this->faker->randomDigitNotNull;
        }

        if ($this->dump == Config::PASSWORD) {
            return 'password';
        }

        if ($this->dump == Config::STRIPEID) {
            return md5(time());
        }

        if ($this->dump == Config::STRIPECUSTID) {
            return md5(time());
        }

        if ($this->dump == Config::BLANK) {
            return '';
        }

        return $value;
    }
}
