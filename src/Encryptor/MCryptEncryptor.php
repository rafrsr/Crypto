<?php

/**
 * This file is part of the Crypto package.
 *
 * (c) RafaelSR <https://github.com/rafrsr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rafrsr\Crypto\Encryptor;

use Rafrsr\Crypto\EncryptorInterface;

/**
 * MCryptEncryptor
 */
class MCryptEncryptor implements EncryptorInterface
{

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @var string
     */
    protected $algorithm;

    /**
     * @var resource
     */
    protected $module;

    /**
     * @var Base64Encryptor
     */
    protected $middlewareEncryptor;

    /**
     * {@inheritdoc}
     */
    public function __construct($algorithm = MCRYPT_RIJNDAEL_256)
    {
        $this->algorithm = $algorithm;
        $this->middlewareEncryptor = new Base64Encryptor();
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($data)
    {
        $this->init();
        $data = trim($this->middlewareEncryptor->encrypt(mcrypt_generic($this->module, $data)));
        $this->close();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($data)
    {
        $this->init();
        $data = trim(mdecrypt_generic($this->module, $this->middlewareEncryptor->decrypt($data)));
        $this->close();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function isEncrypted($data)
    {
        return $this->middlewareEncryptor->isEncrypted($data);
    }

    /**
     * @param string $secretKey
     *
     * @return $this
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = md5($secretKey);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->algorithm;
    }

    /**
     * init encryption module
     */
    private function init()
    {
        $this->module = mcrypt_module_open($this->algorithm, '', MCRYPT_MODE_ECB, '');

        // Create the IV and determine the keysize length
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->module), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($this->module);

        /* Create key */
        $key = substr(md5($this->secretKey), 0, $ks);

        /* Intialize */
        mcrypt_generic_init($this->module, $key, $iv);
    }

    /**
     * Close encryption module
     */
    private function close()
    {
        mcrypt_generic_deinit($this->module);
        mcrypt_module_close($this->module);
    }
}