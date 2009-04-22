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
 * @package     Opus_Validate
 * @author      Ralf Claussnitzer <ralf.claussnitzer@slub-dresden.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


/**
 * Test cases for class Opus_Validate_Language.
 *
 * @category    Tests
 * @package     Opus_Validate
 *
 * @group       LanguageTest
 *
 */
class Opus_Validate_LanguageTest extends PHPUnit_Framework_TestCase {

    /**
     * Data provider for valid arguments.
     *
     * @return array Array of invalid arguments.
     */
    public function validDataProvider() {
        return array(
            array('de'),
            array('fr'),
            array('az'),
            array('es'),
            array('ar'),
            array('zu')
        );
    }

    /**
     * Data provider for invalid arguments.
     *
     * @return array Array of invalid arguments.
     */
    public function invalidDataProvider() {
        return array(
            array(null),
            array(''),
            array(4711),
            array(true),
            array('not_a_valid_type')
        );
    }


    /**
     * Set up test fixture.
     *
     * @return void
     */
    public function setUp() {
        // Set up a mock language list.
        $list = array(
            'de' => 'Test_Deutsch',
            'en' => 'Test_Englisch',
            'fr' => 'Test_Französisch',
            'az' => 'Test_Aserbaidschanisch',
            'es' => 'Test_Spanisch',
            'ar' => 'Test_Arabisch',
            'zu' => 'Test_Zulu',
        );
        Zend_Registry::set('Available_Languages', $list);
    }


    /**
     * Test validation of correct arguments.
     *
     * @param string $arg Name of a locale type to validate.
     * @return void
     *
     * @dataProvider validDataProvider
     */
    public function testValidArguments($arg) {
        $validator = new Opus_Validate_Language();
        $this->assertTrue($validator->isValid($arg), $arg . ' should pass validation.');
    }

    /**
     * Test validation of incorrect arguments.
     *
     * @param string $arg Name of a locale type to validate.
     * @return void
     *
     * @dataProvider invalidDataProvider
     */
    public function testInvalidArguments($arg) {
        $validator = new Opus_Validate_Language();
        $this->assertFalse($validator->isValid($arg), 'Value should not pass validation.');
    }

    /**
     * Test if a error message is set if the validated field has "null" as its value.
     *
     * @return void
     */
    public function testErrorMessageIsSetIfNullIsGivenAsValue() {
        $validator = new Opus_Validate_Language();
        $validator->isValid(NULL);
        $errorMessage = $validator->getMessages();
        $this->assertFalse(empty($errorMessage), 'There should be at least one error message.');
        $this->assertEquals('\'\' is not a valid language shortcut.', $errorMessage['language'], 'Wrong error message set.');
    }

    /**
     * Test  if an array of language values could be validated.
     *
     * @return void
     */
    public function testValidateMultiValueLanguage() {
        $validator = new Opus_Validate_Language();
        $languages = array('de', 'en', 'fr');
        $this->assertTrue($validator->isValid($languages), 'An array of values should pass validation.');
    }

    public function testValidateMultiValueLanguageWithInvalidData() {
        $validator = new Opus_Validate_Language();
        $languages = array('de', 'en', 'fr', 'blablub');
        $this->assertFalse($validator->isValid($languages), 'Value should not pass validation.');
        $errorMessage = $validator->getMessages();
        $this->assertFalse(empty($errorMessage), 'There should be at least one error message.');
        $this->assertEquals('\'blablub\' is not a valid language shortcut.', $errorMessage['language'], 'Wrong error message set.');
    }
}
