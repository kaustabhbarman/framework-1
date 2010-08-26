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
 * @package     Opus_Document
 * @author      Ralf Claussnitzer <ralf.claussnitzer@slub-dresden.de>
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


/**
 * Test cases for class Opus_Document_Type.
 *
 * @category    Tests
 * @package     Opus_Document
 *
 * @group       TypeTest
 *
 */
class Opus_Document_TypeTest extends TestCase {

    /**
     * Overwrite parent methods.
     */
    public function setUp() {}
    public function tearDown() {}

    /**
     * Data provider for invalid creation arguments.
     *
     * @return array Array of invalid creation arguments and an error message.
     */
    public function invalidCreationDataProvider() {
        return array(
        array('','Empty string not rejected.'),
        array(null,'Null not rejected.'),
        array('/filethatnotexists.foo','Invalid filename not rejected.'),
        array(new Exception(),'Wrong object type not rejected.'),
        );
    }


    /**
     * Return invalid XML descriptions.
     *
     * @return array Array of invalid XML type descriptions.
     */
    public function invalidXmlDataProvider() {
        return array(
        array('<?xml version="1.0" encoding="UTF-8" ?>
                <documenttype name="doctoral_thesis"
                    xmlns="http://schemas.opus.org/documenttype"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <not_a_valid_tag/>
                </documenttype>'),
        array('<?xml version="1.0" encoding="UTF-8" ?>
                <documenttype name="doctoral_thesis"
                    xmlns="http://schemas.opus.org/documenttype"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <field wrong_attr="not_a_valid_fieldname"/>
                </documenttype>')
        );
    }


    /**
     * Test if an InvalidArgumentException occurs when instanciating with invalid arguments.
     *
     * @param mixed  $arg Constructor parameter.
     * @param string $msg Error message.
     * @return void
     *
     * @dataProvider invalidCreationDataProvider
     */
    public function testCreateWithInvalidArgumentThrowsException($arg, $msg) {


        try {
            $obj = new Opus_Document_Type($arg);
        } catch (InvalidArgumentException $ex) {
            return;
        }
        $this->fail($msg);
    }


    /**
     * Create a document type by parsing an XML string.
     *
     * @return void
     */
    public function testCreateByXmlString() {
        $this->markTestSkipped("Opus_Document_Type is deprecated and won't be fixed.");

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
                <documenttype name="doctoral_thesis"
                    xmlns="http://schemas.opus.org/documenttype"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <field name="Language" multiplicity="*" mandatory="yes" />
                    <mandatory type="one-at-least">
                        <field name="CompletedYear" />
                        <field name="CompletedDate" />
                    </mandatory>
                </documenttype>';
        try {
            $type = new Opus_Document_Type($xml);
        } catch (Exception $ex) {
            $this->fail('Creation failed: ' . $ex->getMessage());
        }
    }




    /**
     * Expect an exception when passing an invalid XML source.
     *
     * @param string $xml XML type description.
     * @return void
     *
     * @dataProvider invalidXmlDataProvider
     */
    public function testCreateWithValidationErrors($xml) {
        $this->setExpectedException('Opus_Document_Exception');
        $type = new Opus_Document_Type($xml);
    }


    /**
     * Create a document type by parsing an XML file.
     *
     * @return void
     */
    public function testCreateByXmlFile() {
        $this->markTestSkipped("Opus_Document_Type is deprecated and won't be fixed.");

        $xml = dirname(__FILE__) . '/repository/type_test.xml';
        try {
            $type = new Opus_Document_Type($xml);
        } catch (Exception $ex) {
            $this->fail('Creation failed: ' . $ex->getMessage());
        }
    }


    /**
     * Creating a type with an invalid filename throws exception that points
     * to a file problem.
     *
     * @return void
     */
    public function testCreateWithWrongFilenameThrowsFileException() {
        $this->setExpectedException('InvalidArgumentException');
        $xml = '../xml/nofile.xml';
        $type = new Opus_Document_Type($xml);
    }


    /**
     * Test if loading an file that cannot be loaded as xml file for
     * any reason leads to an exception.
     *
     * @return void
     */
    public function testLoadInvalidFileThrowsException() {
        $this->setExpectedException('InvalidArgumentException');
        $xml = 'TypeTest.php';
        $type = new Opus_Document_Type($xml);
    }

    /**
     * Create a document type by providing a DOMDocument.
     *
     * @return void
     */
    public function testCreateByXmlDomDocument() {
        $this->markTestSkipped("Opus_Document_Type is deprecated and won't be fixed.");

        $file = dirname(__FILE__) . '/repository/type_test.xml';
        $dom = new DOMDocument();
        $dom->load($file);
        try {
            $type = new Opus_Document_Type($dom);
        } catch (Exception $ex) {
            $this->fail('Creation failed: ' . $ex->getMessage());
        }
    }


    /**
     * Test if all field definitions come with their default options set.
     *
     * @return void
     */
    public function testDefaultOptions() {
        $this->markTestSkipped("Opus_Document_Type is deprecated and won't be fixed.");

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
                <documenttype name="doctoral_thesis"
                    xmlns="http://schemas.opus.org/documenttype"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <field name="Language" multiplicity="*" mandatory="yes" />
                    <mandatory type="one-at-least">
                        <field name="CompletedYear" />
                        <field name="CompletedDate" />
                    </mandatory>
                </documenttype>';
        $type = new Opus_Document_Type($xml);
        $fields = $type->getFields();
        foreach ($fields as $fieldname => $fielddef) {
            $this->assertArrayHasKey('multiplicity', $fielddef);
            $this->assertArrayHasKey('mandatory', $fielddef);
        }
    }


    /**
     * Test if successfully creating a type registers it in the Zend Registry.
     *
     * @return void
     */
    public function testTypeFromFileGetsRegisteredInZendRegistry() {
        $this->markTestSkipped("Opus_Document_Type is deprecated and won't be fixed.");

        $xml1 = dirname(__FILE__) . '/repository/type_test.xml';
        $type1 = new Opus_Document_Type($xml1);
        $typename = $type1->getName();
        $xml2 = dirname(__FILE__) . '/repository/type_test.xml';
        $type2 = new Opus_Document_Type($xml2);

        // Check if the type2 is registered.
        $registry = Zend_Registry::getInstance();
        $registered = $registry->get(Opus_Document_Type::ZEND_REGISTRY_KEY);
        $result = $registered[$typename];
        $this->assertNotSame($type1, $result, 'Second attempt to register type did not override the old type.');
        $this->assertSame($type2, $result, 'Second attempt to register type did not override the old type.');
    }

    /**
     * Test if a loaded type instance gets registered with its name.
     *
     * @return void
     */
    public function testTypeGetsRegisteredInZendRegistry() {
        $this->markTestSkipped("Opus_Document_Type is deprecated and won't be fixed.");

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
                <documenttype name="special_type"
                    xmlns="http://schemas.opus.org/documenttype"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <field name="Language" multiplicity="*" mandatory="yes" />
                </documenttype>';
        // Register the type.
        $type = new Opus_Document_Type($xml);

        // Reload the type from the registry.
        $type2 = new Opus_Document_Type('special_type');

        $this->assertNotNull($type2, 'Type has not been registered.');
        $this->assertEquals($type, $type2, 'Type returned is not the one expected.');
    }


    /**
     * Test if a type specification gets overwritten when another one gets registered
     * under the same name.
     *
     * @return void
     */
    public function testTypeOverrideInRegistry() {
        $this->markTestSkipped("Opus_Document_Type is deprecated and won't be fixed.");

        $xml1 = '<?xml version="1.0" encoding="UTF-8" ?>
                <documenttype name="doctoral_thesis"
                    xmlns="http://schemas.opus.org/documenttype"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <field name="Language" multiplicity="*" mandatory="yes" />
                </documenttype>';
        $type1 = new Opus_Document_Type($xml1);
        $xml2 = '<?xml version="1.0" encoding="UTF-8" ?>
                <documenttype name="doctoral_thesis"
                    xmlns="http://schemas.opus.org/documenttype"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <field name="Language" multiplicity="*" mandatory="yes" />
                </documenttype>';
        $type2 = new Opus_Document_Type($xml2);

        // Check if the type2 is registered.
        $registry = Zend_Registry::getInstance();
        $registered = $registry->get(Opus_Document_Type::ZEND_REGISTRY_KEY);
        $result = $registered['doctoral_thesis'];
        $this->assertNotSame($type1, $result, 'Second attempt to register type did not override the old type.');
        $this->assertSame($type2, $result, 'Second attempt to register type did not override the old type.');
    }


    /**
     * Test if the multiplicity attribute can be queried when initially specified in
     * the types describing xml.
     *
     * @return void
     */
    public function testGetMultiplicityWhenGivenByXml() {
        $this->markTestSkipped("Opus_Document_Type is deprecated and won't be fixed.");

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
                <documenttype name="doctoral_thesis"
                    xmlns="http://schemas.opus.org/documenttype"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <field name="Institute" multiplicity="12" mandatory="yes" />
                    <mandatory type="one-at-least">
                        <field name="CompletedYear" />
                        <field name="CompletedDate" />
                    </mandatory>
                </documenttype>';
        $type = new Opus_Document_Type($xml);
        $fields = $type->getFields();

        $this->assertArrayHasKey('multiplicity', $fields['Institute'], 'Multiplicity attribute is missing.');
        $this->assertEquals('12', $fields['Institute']['multiplicity'], 'Multiplicity attribute has wrong value.');
    }


    /**
     * Test if the mandatory attribute can be queried when initially specified in
     * the types describing xml.
     *
     * @return void
     */
    public function testGetMandatoryWhenGivenByXml() {
        $this->markTestSkipped("Opus_Document_Type is deprecated and won't be fixed.");

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
                <documenttype name="doctoral_thesis"
                    xmlns="http://schemas.opus.org/documenttype"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <field name="Language" mandatory="yes" />
                </documenttype>';
        $type = new Opus_Document_Type($xml);
        $fields = $type->getFields();

        $this->assertArrayHasKey('mandatory', $fields['Language'], 'Mandatory attribute is missing.');
        $this->assertEquals('yes', $fields['Language']['mandatory'], 'Mandatory attribute has wrong value.');
    }

    /**
     * Test if the type parser error message is correct.
     *
     * @return
     */
    public function testAppropriateErrorMessageOnXmlSchemaViolations() {
        $this->markTestSkipped("Opus_Document_Type is deprecated and won't be fixed.");

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
                <documenttype name="doctoral_thesis"
                    xmlns="http://schemas.opus.org/documenttype"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <field name="error1" mandatory="error2" />
                </documenttype>';
        try {
            $type = new Opus_Document_Type($xml);
            $this->fail('Invalid document type description gets parsed without error.');
        } catch(Opus_Document_Exception $ex) {
            $message = $ex->getMessage();
            $this->assertRegExp('/\'error1\' is not a valid value of the atomic type/', $message);
            $this->assertRegExp('/\'error2\' is not a valid value of the atomic type/', $message);
            $this->assertRegExp('/The value \'error1\' is not an element of the set/', $message);
        }
    }

    /**
     * Test if a document type file can be loaded by inferencing the filename
     * from the types name.
     *
     * @return void
     */
    public function testGetDocumentTypeFileByTypeName() {
        $this->markTestSkipped("Opus_Document_Type is deprecated and won't be fixed.");

        Opus_Document_Type::setXmlDoctypePath(dirname(dirname(__FILE__)));
        $type = new Opus_Document_Type('article');
        $this->assertNotNull($type);
    }

    /**
     * Test if a multiplicity value is integer.
     *
     * @return void
     */
    public function testMultiplicityIsIntegerValue() {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
                <documenttype name="doctoral_thesis"
                    xmlns="http://schemas.opus.org/documenttype"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <field name="TitleMain" multiplicity="15" />
                </documenttype>';

        $type = new Opus_Document_Type($xml);
        $fields = $type->getFields();
        $multi = $fields['TitleMain']['multiplicity'];
        $this->assertTrue(is_int($multi), 'Multiplicity should be an integer');
    }

    /**
     * Test if the mandatory field description is of type boolean
     * especially when mandatory="no" is given in the schema description.
     *
     * @return void
     */
    public function testMandatoryIsBooleanValue() {
        $this->markTestSkipped("Opus_Document_Type is deprecated and won't be fixed.");

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
                <documenttype name="doctoral_thesis"
                    xmlns="http://schemas.opus.org/documenttype"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <field name="TitleMain" mandatory="no" />
                </documenttype>';

        $type = new Opus_Document_Type($xml);
        $fields = $type->getFields();
        $result = $fields['TitleMain']['mandatory'];
        $this->assertTrue(is_bool($result), 'Mandatory option should be of type Boolean.');
    }

    /**
     * Test if setting an invalid document type path throws an exception.
     *
     * @return void
     */
    public function testSetInvalidDocumentTypePathThrowsException() {
        $this->setExpectedException('InvalidArgumentException');
        Opus_Document_Type::setXmlDoctypePath('xxyyzz');
    }

}
