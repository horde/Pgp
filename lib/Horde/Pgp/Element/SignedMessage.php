<?php

/**
 * Copyright 2015-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category  Horde
 * @copyright 2015-2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Pgp
 */

/**
 * PGP armor part: signed message.
 *
 * @author    Michael Slusarz <slusarz@horde.org>
 * @category  Horde
 * @copyright 2015-2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Pgp
 *
 * @property-read Horde_Pgp_Element_Signature $signature  Signature element.
 * @property-read Horde_Pgp_Element_Text $text  Literal text element.
 */
class Horde_Pgp_Element_SignedMessage extends Horde_Pgp_Element
{
    /**
     */
    protected $_armor = 'SIGNED MESSAGE';

    /**
     * Returns text after reversing any dash-escaping (RFC 4880 [7.1])
     * previously done on it.
     *
     * @param string $text  Escaped text.
     *
     * @return string  Unescaped text.
     */
    public static function dashUnescapeText($text)
    {
        return str_replace(
            ["\r\n", "\n- -", "\n- From "],
            ["\n", "\n-", "\nFrom "],
            $text
        );
    }

    /**
     * Returns the normalized & dash-escaped text (RFC 4880 [7.1]) of the
     * cleartext signed message.
     *
     * @param string $text  Unescaped text.
     *
     * @return string  Escaped text.
     */
    public static function dashEscapeText($text)
    {
        /* Normalize EOLs and dash escape text output (RFC 4880 [7.1]) */
        return str_replace(
            ["\r\n", "\n-", "\nFrom "],
            ["\n", "\n- -", "\n- From "],
            $text
        );
    }

    /**
     */
    public function __construct($data, array $headers = [])
    {
        if (!($data instanceof OpenPGP_Message)) {
            Horde_Pgp_Backend_Openpgp::autoload();
            $msg = new OpenPGP_Message();

            /* Trailing (CR)LF is not part of signed data. */
            $pos = strpos($data, '-----BEGIN PGP SIGNATURE-----');
            if ($data[--$pos] === "\r") {
                --$pos;
            }

            $msg[] = new OpenPGP_LiteralDataPacket(
                self::dashUnescapeText(substr($data, 0, $pos)),
                ['format' => 'u']
            );
            $msg[] = Horde_Pgp_Element_Signature::create(
                substr($data, $pos) . "-----END PGP SIGNATURE-----\n"
            )->message[0];
        } else {
            $msg = $data;
        }

        parent::__construct($msg, $headers);
    }

    /**
     */
    public function __toString()
    {
        $out = "-----BEGIN PGP SIGNED MESSAGE-----\n";
        foreach (array_intersect_key($this->headers, ['Hash' => true]) as $key => $val) {
            $out .= $key . ': ' . $val . "\n";
        }

        return $out . "\n"
            . self::dashEscapeText($this->text) . "\n"
            . strval($this->signature);
    }

    /**
     */
    public function __get($name)
    {
        switch ($name) {
            case 'signature':
                return new Horde_Pgp_Element_Signature(
                    new OpenPGP_Message([$this->message[1]])
                );

            case 'text':
                return new Horde_Pgp_Element_Text(
                    new OpenPGP_Message([$this->message[0]])
                );
        }
    }

}
