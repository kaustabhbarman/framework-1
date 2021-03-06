<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Tests
 * @package     Opus_Mail
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2009-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Test cases for class Opus_Mail.
 *
 * @category Tests
 * @package  Opus_Mail
 *
 * @group    MailSendMailTest
 */
class Opus_Mail_SendMailTest extends TestCase
{

    protected $_config_dummy = null;

    /**
     * Set up test fixtures.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_config_dummy = new Zend_Config([
            'mail' => [ 'opus' => [
                'smtp' => 'host.does.not.exists.hopefully',
                'port' => 22,
            ]],
        ]);
    }

    /**
     * Test construtor.
     */
    public function testConstructor()
    {
        Zend_Registry::set('Zend_Config', $this->_config_dummy);
        $mail = new Opus_Mail_SendMail();
    }

    /**
     * Test construtor without config.
     */
    public function testConstructorWoConfig()
    {
        Zend_Registry::set('Zend_Config', null);
        $mail = new Opus_Mail_SendMail();
    }

    /**
     * Test sending mail.
     */
    public function testSendmailWoParameters()
    {
        Zend_Registry::set('Zend_Config', null);
        $mail = new Opus_Mail_SendMail();
        $this->setExpectedException('Opus_Mail_Exception');
        $mail->sendMail(null, null, null, null, null);
    }

    /**
     * Test sending mail.
     */
    public function testSendmailRemoteHostDoesNotExist()
    {
        $mail = new Opus_Mail_SendMail();
        $this->setExpectedException('Opus_Mail_Exception');
        $mail->sendMail(
            'Sender',
            'sender@does.not.exists.hopefully.mil',
            'no subject',
            'no body',
            [[
                        'name' => 'Recipient',
                        'address' => 'sender@does.not.exists.hopefully.mil',
                ]]
        );
    }

    /**
     * Tests the sending of an e-mail, but without mail body.
     */
    public function testSendMailNoMailFrom()
    {
        $mail = new Opus_Mail_SendMail();
        $recipient = ['recipients' => ['address' => 'recipient@testmail.de', 'name' => 'John R. Public']];

        $this->setExpectedException('Opus_Mail_Exception');
        $mail->sendMail('', 'John S. Public', 'My subject', 'My Text', $recipient);
    }

    /**
     * Tests the sending of an e-mail, but without mail from.
     */
    public function testSendMailNoMailBody()
    {
        $mail = new Opus_Mail_SendMail();
        $recipient = ['recipients' => ['address' => 'recipient@testmail.de', 'name' => 'John R. Public']];

        $this->setExpectedException('Opus_Mail_Exception');
        $mail->sendMail('recipient@testmail.de', 'John S. Public', '', 'My Text', $recipient);
    }

    /**
     * Tests the sending of an e-mail.
     */
    public function testSendMailSuccess()
    {
        $recipient = ['recipients' => ['address' => 'recipient@testmail.de', 'name' => 'John R. Public']];

        $config = Zend_Registry::get('Zend_Config');
        if (! isset($config, $config->mail->opus)) {
            $this->markTestSkipped('Test mail server is not configured yet.');
        }

        $mail = new Opus_Mail_SendMail();
        $mail->sendMail('recipient@testmail.de', 'John S. Public', 'Mail Body', 'My Text', $recipient);
    }
}
