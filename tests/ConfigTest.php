<?php

namespace ipl\Tests\Sql;

use ipl\Sql\Config;

class ConfigTest extends TestCase
{
    public function testKeyNamesAreConvertedToCamelCase(): void
    {
        $config = new Config([
            'db'      => 'mysql',
            'Host'    => 'localhost',
            'DbName'  => 'test_dbname',
            'uSe_SsL' => 'true'
        ]);

        $this->assertSame($config->db, 'mysql');
        $this->assertSame($config->host, 'localhost');
        $this->assertSame($config->useSsl, 'true');
        $this->assertSame($config->dbname, 'test_dbname');
    }


    public function testSettingDynamicValue(): void
    {
        $config = new Config([
            'db'      => 'mysql',
            'Host'    => 'localhost',
            'DbName'  => 'test_dbname',
            'uSe_SsL' => 'true'
        ]);

        // Won't get converted to camel case
        $config->ssl_cert_key = 'test_ssl_cert_key';

        $this->assertSame($config->db, 'mysql');
        $this->assertSame($config->host, 'localhost');
        $this->assertSame($config->useSsl, 'true');
        $this->assertSame($config->dbname, 'test_dbname');
        $this->assertSame($config->ssl_cert_key, 'test_ssl_cert_key');
    }

    public function testAllPropertiesCanBeSet(): void
    {
        $config = new Config([
            'db'                            => 'mysql',
            'host'                          => 'localhost',
            'port'                          => '3306',
            'dbname'                        => 'test_dbname',
            'username'                      => 'test_username',
            'password'                      => 'test_password',
            'use_ssl'                       => 'true',
            'charset'                       => 'latin1',
            'options'                       => 'test_options',
            'ssl_key'                       => 'test_ssl_key',
            'ssl_cert'                      => 'test_ssl_cert',
            'ssl_ca'                        => 'test_ssl_ca',
            'ssl_capath'                    => 'test_ssl_capath',
            'ssl_cipher'                    => 'test_ssl_cipher',
            'ssl_do_not_verify_server_cert' => 'test_ssl_do_not_verify_server_cert'
        ]);

        $this->assertSame($config->db, 'mysql');
        $this->assertSame($config->host, 'localhost');
        $this->assertSame($config->port, '3306');
        $this->assertSame($config->dbname, 'test_dbname');
        $this->assertSame($config->username, 'test_username');
        $this->assertSame($config->password, 'test_password');
        $this->assertSame($config->useSsl, 'true');
        $this->assertSame($config->charset, 'latin1');
        $this->assertSame($config->options, 'test_options');
        $this->assertSame($config->sslKey, 'test_ssl_key');
        $this->assertSame($config->sslCert, 'test_ssl_cert');
        $this->assertSame($config->sslCa, 'test_ssl_ca');
        $this->assertSame($config->sslCapath, 'test_ssl_capath');
        $this->assertSame($config->sslCipher, 'test_ssl_cipher');
        $this->assertSame($config->sslDoNotVerifyServerCert, 'test_ssl_do_not_verify_server_cert');
    }
}
