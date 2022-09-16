<?php

class HelperTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        # curl -LO https://phar.phpunit.de/phpunit-5.7.phar &&
        # mv -f phpunit-5.7.phar /usr/local/bin/phpunit &&
        # chmod +x /usr/local/bin/phpunit
        require_once __DIR__ . '/../../Helper.php';
    }

    public function test_str_tpl()
    {
        $this->assertEquals(
            'Hello World',
            Helper::str_tpl('Hello %var', ['var' => 'World'])
        );
    }

    public function test_str_tpl_prefix()
    {
        $this->assertEquals(
            'Hello World',
            Helper::str_tpl('Hello :var', ['var' => 'World'], ':')
        );
    }

    public function test_str_starts()
    {
        $this->assertTrue(Helper::str_starts_with('<apple/>', '<'));
    }

    public function test_str_ends()
    {
        $this->assertTrue(Helper::str_ends_with('<apple/>', '/>'));
    }

    public function test_db_prepare()
    {
        $this->assertEquals(
            'SELECT * FROM `tbl_rate` WHERE id="33" AND rates IN ("23.00","45.00")',
            Helper::db_prepare('SELECT * FROM `tbl_rate` WHERE id=? AND rates IN (?)', [33, ["23.00", "45.00"]])
        );
    }

    public function test_assoc()
    {
        $this->assertTrue(Helper::is_assoc([
            'ph' => 'Philippines',
            'us' => 'United States',
        ]));
    }

    public function test_assoc_false()
    {
        $this->assertFalse(Helper::is_assoc([
            'Philippines',
            'United States',
        ]));
    }

    public function test_parse_json()
    {
        $this->assertInstanceOf(
            stdClass::class,
            Helper::parse_json('{"id":1,"name":"Foo Bar"}')
        );
    }

    public function test_parse_json_array()
    {
        $this->assertTrue(
            is_array(Helper::parse_json('["x", "y", "z"]'))
        );
    }
}
