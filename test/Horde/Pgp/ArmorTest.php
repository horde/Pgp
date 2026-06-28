<?php

/**
 * Copyright 2015-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category   Horde
 * @copyright  2015-2016 Horde LLC
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package    Pgp
 * @subpackage UnitTests
 */

/**
 * Tests for PGP armor parsing.
 *
 * @author     Michael Slusarz <slusarz@horde.org>
 * @category   Horde
 * @copyright  2015-2016 Horde LLC
 * @ignore
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package    Pgp
 * @subpackage UnitTests
 * @coversNothing
 */
class Horde_Pgp_ParseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider parsePgpDataProvider
     */
    public function testParsePgpData($fixture, $expected, $headers)
    {
        $data = file_get_contents(__DIR__ . '/fixtures/' . $fixture);

        $stream = new Horde_Stream_Temp();
        $stream->add($data, true);

        $obs = [
            new Horde_Pgp_Armor($data),
            new Horde_Pgp_Armor($stream),
        ];

        foreach ($obs as $ob) {
            $this->assertEquals(
                count($expected),
                count($ob)
            );

            $i = 0;
            foreach ($ob as $val) {
                $this->assertEquals(
                    $expected[$i++],
                    get_class($val)
                );

                $this->assertEquals(
                    $headers,
                    $val->headers
                );
            }
        }
    }

    public function parsePgpDataProvider()
    {
        return [
            [
                'clear.txt',
                [],
                [],
            ],
            [
                'pgp_encrypted_symmetric.txt',
                ['Horde_Pgp_Element_Message'],
                ['Version' => 'GnuPG v1.4.5 (GNU/Linux)'],
            ],
            [
                'pgp_encrypted.txt',
                ['Horde_Pgp_Element_Message'],
                ['Version' => 'GnuPG v1.4.5 (GNU/Linux)'],
            ],
            [
                'pgp_private.asc',
                ['Horde_Pgp_Element_PrivateKey'],
                ['Version' => 'GnuPG v1.4.5 (GNU/Linux)'],
            ],
            [
                'pgp_public.asc',
                ['Horde_Pgp_Element_PublicKey'],
                ['Version' => 'GnuPG v1.4.5 (GNU/Linux)'],
            ],
            [
                'pgp_signature.txt',
                ['Horde_Pgp_Element_Signature'],
                ['Version' => 'GnuPG v1.4.5 (GNU/Linux)'],
            ],
            [
                'pgp_signed.txt',
                ['Horde_Pgp_Element_SignedMessage'],
                ['Hash' => 'SHA1'],
            ],
        ];
    }

}
