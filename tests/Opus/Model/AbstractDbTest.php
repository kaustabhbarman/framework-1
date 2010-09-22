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
 * @package     Opus_Model
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @author      Ralf Claußnitzer (ralf.claussnitzer@slub-dresden.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Test cases for class Opus_Model_AbstractDb.
 *
 * @package Opus_Model
 * @category Tests
 *
 * @group AbstractDbTest
 */
class Opus_Model_AbstractDbTest extends PHPUnit_Extensions_Database_TestCase {

    /**
     * Instance of the concrete table model for Opus_Model_ModelAbstractDb.
     *
     * @var Opus_Model_AbstractTableProvider
     */
    protected $dbProvider = null;

    /**
     * Provides test data as stored in AbstractDataSet.xml.
     *
     * @return array Array containing arrays of id and value pairs.
     */
    public function abstractDataSetDataProvider() {
        return array(
        array(1, 'foobar'),
        array(3, 'foo'),
        array(4, 'bar'),
        array(5, 'bla'),
        array(8, 'blub')
        );
    }

    /**
     * Return the actual database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection() {
        $dba = Zend_Db_Table::getDefaultAdapter();
        $pdo = $dba->getConnection();
        $connection = $this->createDefaultDBConnection($pdo, null);
        return $connection;
    }

    /**
     * Returns test data to set up the Database before a test is started or after a test finished.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet() {
        $dataset = $this->createFlatXMLDataSet(dirname(__FILE__) . '/AbstractDataSet.xml');
        return $dataset;
    }


    /**
     * Prepare the Database.
     *
     * @return void
     */
    public function setUp() {
        // Instantiate the Zend_Db_Table
        $this->dbProvider = Opus_Db_TableGateway::getInstance('Opus_Model_AbstractTableProvider');
        $dba = $this->dbProvider->getAdapter();
        $dba->query('DROP TABLE IF EXISTS testtable');
        $dba->query('CREATE TABLE testtable (
            testtable_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            value        VARCHAR(255))');

        // load table data
        parent::setUp();

    }

    /**
     * Remove temporary table.
     *
     * @return void
     */
    public function tearDown() {
        $dba = $this->dbProvider->getAdapter();
        $dba->query('DROP TABLE IF EXISTS testtable');
    }

    /**
     * Test if an call to add...() throws an exception if the 'through' definition for
     * external fields holding models is invalid.
     *
     * @return void
     */
    public function testAddWithoutPropertLinkModelClassThrowsException() {
        // Build a mockup to observe calls to _loadExternal
        $mockup = new Opus_Model_ModelDefiningExternalField();
        $this->setExpectedException('Opus_Model_Exception');
        $mockup->addLazyExternalModel();
    }

    /**
     * Test if setting a field containing a link model to null removes link
     * model.
     *
     * @return void
     */
    public function testSetLinkModelFieldToNullRemovesLinkModel() {
        $model = new Opus_Model_ModelDefiningExternalField;

        $abstractMock = new Opus_Model_ModelAbstractDbMock;
        $model->setExternalModel($abstractMock);
        $model->setExternalModel(null);
        $field = $model->getField('ExternalModel');

        $this->assertNull($field->getValue(), 'Link model field value is not null.');
    }

    /**
     * Test if a link model is the field value of an external field that uses
     * the 'through' option.
     *
     * @return void
     */
    public function testLinkModelIsFieldValueWhenUsingThroughOption() {
        $model = new Opus_Model_ModelDefiningExternalField();

        $abstractMock = new Opus_Model_ModelAbstractDbMock;
        $external = $model->setExternalModel($abstractMock);
        $field = $model->getField('ExternalModel');
        $fieldvalue = $field->getValue();
        $this->assertTrue($fieldvalue instanceof Opus_Model_Dependent_Link_Abstract, 'Field value is not a link model.');
    }

    /**
     * Test if a linkes model can be retrieved if the standard
     * get<Fieldname>() accessor is called on the containing model.
     *
     * @return void
     */
    public function testGetLinkedModelWhenQueryModel() {
        // construct mockup class
        $clazzez = '

        class testGetLinkedModelWhenQueryModel_Link
            extends Opus_Model_Dependent_Link_Abstract {
            protected $_modelClass = \'Opus_Model_ModelAbstractDbMock\';
            public function __construct() {}
            protected function _init() {}
            public function delete() {}
        }

        class testGetLinkedModelWhenQueryModel
            extends Opus_Model_AbstractDb {
                protected static $_tableGatewayClass = \'Opus_Model_AbstractTableProvider\';
                protected $_externalFields = array(
                    \'LinkField\' => array(
                        \'model\' => \'Opus_Model_ModelAbstractDbMock\',
                        \'through\' => \'testGetLinkedModelWhenQueryModel_Link\')
                );
                protected function _init() {
                    $this->addField(new Opus_Model_Field(\'LinkField\'));
                }
            }

        ';
        eval($clazzez);

        $mock = new testGetLinkedModelWhenQueryModel;
        $linkedModel = new Opus_Model_ModelAbstractDbMock();
        $mock->setLinkField($linkedModel);

        $this->assertType('testGetLinkedModelWhenQueryModel_Link', $mock->getLinkField(), 'Returned linked model has wrong type.');
    }

    /**
     * Test if a linkes model can be retrieved if the standard
     * get<Fieldname>() accessor is called on the containing model.
     *
     * @return void
     */
    public function testGetMultipleLinkedModelWhenQueryModel() {
        // construct mockup class
        $clazzez = '

        class testGetMultipleLinkedModelWhenQueryModel_Link
            extends Opus_Model_Dependent_Link_Abstract {
            protected $_modelClass = \'Opus_Model_ModelAbstractDbMock\';
            public function __construct() {}
            protected function _init() {}
            public function delete() {}
        }

        class testGetMultipleLinkedModelWhenQueryModel
            extends Opus_Model_AbstractDb {
                protected static $_tableGatewayClass = \'Opus_Model_AbstractTableProvider\';
                protected $_externalFields = array(
                    \'LinkField\' => array(
                        \'model\' => \'Opus_Model_ModelAbstractDbMock\',
                        \'through\' => \'testGetMultipleLinkedModelWhenQueryModel_Link\')
                );
                protected function _init() {
                    $field = new Opus_Model_Field(\'LinkField\');
                    $field->setMultiplicity(2);
                    $this->addField($field);
                }
            }

        ';
        eval($clazzez);

        $mock = new testGetMultipleLinkedModelWhenQueryModel;
        $linkedModel = new Opus_Model_ModelAbstractDbMock();
        $mock->addLinkField($linkedModel);

        $this->assertTrue(is_array($mock->getLinkField()), 'Returned value is not an array.');
        $this->assertType('testGetMultipleLinkedModelWhenQueryModel_Link', $mock->getLinkField(0), 'Returned linked model has wrong type.');
    }


    /**
     * Test if loading a model instance from the database devlivers the expected value.
     *
     * @param integer $testtable_id Id of dataset to load.
     * @param mixed   $value        Expected Value.
     * @return void
     *
     * @dataProvider abstractDataSetDataProvider
     */
    public function testValueAfterLoadById($testtable_id, $value) {
        $obj = new Opus_Model_ModelAbstractDb($testtable_id);
        $result = $obj->getValue();
        $this->assertEquals($value,$result, "Expected Value to be $value, got '" . $result . "'");
    }

    /**
     * Test if changing a models value and storing it is reflected in the database.
     *
     * @return void
     */
    public function testChangeOfValueAndStore() {
        $obj = new Opus_Model_ModelAbstractDb(1);
        $obj->setValue('raboof');
        $obj->store();
        $expected = $this->createFlatXMLDataSet(dirname(__FILE__) . '/AbstractDataSetAfterChangedValue.xml')->getTable('testtable');
        $result = $this->getConnection()->createDataSet()->getTable('testtable');
        $this->assertTablesEqual($expected, $result);
    }

    /**
     * Test if a call to store() does not happen when the Model has not been modified.
     *
     * @return void
     */
    public function testIfModelIsNotStoredWhenUnmodified() {
        // A record with id 1 is created by setUp() using AbstractDataSet.xml
        // So create a mocked Model to detect certain calls
        $mock = $this->getMock('Opus_Model_ModelAbstractDb', 
            array('_storeInternalFields', '_storeExternalFields'),
            array(1));

        // Clear modified flag just to be sure
        $mock->clearModified();

        // Expect getValue never to be called
        $mock->expects($this->never())->method('_storeInternalFields');
        $mock->expects($this->never())->method('_storeExternalFields');

        $mock->store();
    }
    
    /**
     * Test if fields get their modified status set back to false after beeing
     * filled with values from the database.
     *
     * @return void
     */
    public function testFieldsAreUnmodifiedWhenFreshFromDatabase() {
        // A record with id 1 is created by setUp() using AbstractDataSet.xml
        $mock = new Opus_Model_ModelAbstractDb(1);
        $field = $mock->getField('Value');
        $this->assertFalse($field->isModified(), 'Field should not be marked as modified when fetched from database.');
    }

    /**
     * Test if the modified status of fields gets cleared after the model
     * stored them.
     *
     * @return void
     *
     */
    public function testFieldsModifiedStatusGetsClearedAfterStore() {
        $clazz = '
            class testFieldsModifiedStatusGetsClearedAfterStore
                extends Opus_Model_AbstractDb {

                protected static $_tableGatewayClass = \'Opus_Model_AbstractTableProvider\';

                protected $_externalFields = array(
                    \'ExternalField1\' => array(),
                    \'ExternalField2\' => array(),
                );

                protected function _init() {
                    $this->addField(new Opus_Model_Field(\'Value\'));
                    $this->addField(new Opus_Model_Field(\'ExternalField1\'));
                    $this->addField(new Opus_Model_Field(\'ExternalField2\'));
                }

                public function getId() {
                    return 1;
                }

                public function _storeExternalField1() {}
                public function _storeExternalField2() {}
                public function _fetchExternalField1() {}
                public function _fetchExternalField2() {}

            }';
        eval($clazz);
        $mock = new testFieldsModifiedStatusGetsClearedAfterStore;
        $mock->setValue('foobar');
        $mock->setExternalField1('foo');
        $mock->setExternalField2('bar');
        $mock->store();

        $field = $mock->getField('Value');
        $this->assertFalse($field->isModified(), 'Field should not be marked as modified after storing to database.');
        $field = $mock->getField('ExternalField1');
        $this->assertFalse($field->isModified(), 'Field should not be marked as modified after storing to database.');
        $field = $mock->getField('ExternalField2');
        $this->assertFalse($field->isModified(), 'Field should not be marked as modified after storing to database.');
    }

    /**
     * Test if model deletion is reflected in database.
     *
     * @return void
     */
    public function testDeletion() {
        $obj = new Opus_Model_ModelAbstractDb(1);
        $preCount = $this->getConnection()->createDataSet()->getTable('testtable')->getRowCount();
        $obj->delete();
        $postCount = $this->getConnection()->createDataSet()->getTable('testtable')->getRowCount();
        $this->assertEquals($postCount, ($preCount - 1), 'Object persists allthough it was deleted.');
    }

    /**
     * Test if the default display name of a model is returned.
     *
     * @return void
     */
    public function testDefaultDisplayNameIsReturned() {
        $obj = new Opus_Model_ModelAbstractDb(1);
        $result = $obj->getDisplayName();
        $this->assertEquals('Opus_Model_ModelAbstractDb#1', $result, 'Default display name not properly formed.');
    }

    /**
     * Test if zero model entities would be retrieved by static getAll()
     * on an empty database.
     *
     * @return void
     */
    public function testGetAllEntitiesReturnsEmptyArrayOnEmtpyDatabase() {
        $dba = Zend_Db_Table::getDefaultAdapter();
        $dba->query('TRUNCATE testtable');

        $result = Opus_Model_ModelAbstractDb::getAllFrom('Opus_Model_ModelAbstractDb', 'Opus_Model_AbstractTableProvider');
        $this->assertTrue(empty($result), 'Empty table should not deliver any objects.');
    }

    /**
     * Test if all model instances can be retrieved.
     *
     * @return void
     */
    public function testGetAllEntities() {
        $dba = Zend_Db_Table::getDefaultAdapter();
        $dba->query('TRUNCATE testtable');

        $entities[0] = new Opus_Model_ModelAbstractDb(); $entities[0]->setValue('SatisfyValidator');
        $entities[1] = new Opus_Model_ModelAbstractDb(); $entities[1]->setValue('SatisfyValidator');
        $entities[2] = new Opus_Model_ModelAbstractDb(); $entities[2]->setValue('SatisfyValidator');

        foreach ($entities as $entity) {
            $entity->store();
        }

        $results = Opus_Model_ModelAbstractDb::getAllFrom('Opus_Model_ModelAbstractDb', 'Opus_Model_AbstractTableProvider');
        $this->assertEquals(count($entities), count($results), 'Incorrect number of instances delivered.');
        $this->assertEquals($entities[0]->toArray(), $results[0]->toArray(), 'Entities fetched differ from entities stored.');
        $this->assertEquals($entities[1]->toArray(), $results[1]->toArray(), 'Entities fetched differ from entities stored.');
        $this->assertEquals($entities[2]->toArray(), $results[2]->toArray(), 'Entities fetched differ from entities stored.');
    }

    /**
     * Test if the model of a field specified as lazy external is not loaded on
     * initialization.
     *
     * @return void
     */
    public function testLazyExternalModelIsNotLoadedOnInitialization() {
        // Build a mockup to observe calls to _loadExternal
        $mockup = new Opus_Model_ModelDefiningExternalField();

        // Query the mock
        $this->assertNotContains('LazyExternalModel', $mockup->loadExternalHasBeenCalledOn,
                'The lazy external field got loaded.');
    }

    /**
     * Test if the loading of an external model is not executed before
     * an explicit call to get...() when the external field's fetching
     * mode has been set to 'lazy'.
     *
     * @return void
     */
    public function testExternalModelLoadingIsSuspendedUntilGetCall() {
        // Build a mockup to observe calls to _loadExternal
        $mockup = new Opus_Model_ModelDefiningExternalField();

        // Check that _loadExternal has not yet been called
        $this->assertNotContains('LazyExternalModel' ,$mockup->loadExternalHasBeenCalledOn, 'The "lazy fetch" external field does get loaded initially.');
    }

    /**
     * Test if suspended loading of external models gets triggered by
     * a call to getField().
     *
     * @return void
     */
    public function testExternalModelLoadingTiggeredByGetFieldCall() {
        // Build a mockup to observe calls to _loadExternal
        $mockup = new Opus_Model_ModelDefiningExternalField();

        $field = $mockup->getField('LazyExternalModel');

        // Check that _loadExternal has not yet been called
        $this->assertContains('LazyExternalModel' ,$mockup->loadExternalHasBeenCalledOn, 'The "lazy fetch" external field is not loaded after getField().');
        $this->assertNotNull($field, 'No field object returned.');
    }

    /**
     * Test that lazy fetching does not happen more than once.
     *
     * @return void
     */
    public function testExternalModelLoadingByGetFieldCallHappensOnlyOnce() {
        // Build a mockup to observe calls to _loadExternal
        $mockup = new Opus_Model_ModelDefiningExternalField();

        // First call to get.
        $field = $mockup->getField('LazyExternalModel');

        // Clear out mock up status
        $mockup->loadExternalHasBeenCalledOn = array();

        // Second call to get should not call _loadExternal again.
        $field = $mockup->getField('LazyExternalModel');


        // Check that _loadExternal has not yet been called
        $this->assertNotContains('LazyExternalModel' ,$mockup->loadExternalHasBeenCalledOn, 'The "lazy fetch" external field is called more than once.');
    }

    /**
     * Test if suspended loading of external models gets triggered by
     * a call to get...().
     *
     * @return void
     */
    public function testExternalModelLoadingTiggeredByGetCall() {
        // Build a mockup to observe calls to _loadExternal
        $mockup = new Opus_Model_ModelDefiningExternalField();

        $mockup->getLazyExternalModel();

        // Check that _loadExternal has been called
        $this->assertContains('LazyExternalModel' ,$mockup->loadExternalHasBeenCalledOn, 'The "lazy fetch" external field is not loaded after get call.');
    }

    /**
     * Test if suspended loading of external models gets triggered by
     * a call to set...().
     *
     * @return void
     */
    public function testExternalModelLoadingTiggeredBySetCall() {
        // Build a mockup to observe calls to _loadExternal
        $mockup = new Opus_Model_ModelDefiningExternalField();

        $mockup->setLazyExternalModel(null);

        // Check that _loadExternal has been called
        $this->assertContains('LazyExternalModel' ,$mockup->loadExternalHasBeenCalledOn, 'The "lazy fetch" external field is not loaded after set call.');
    }

    /**
     * Test if suspended loading of external models gets triggered by
     * a call to add...().
     *
     * @return void
     */
    public function testExternalModelLoadingTiggeredByAddCall() {
        // Build a mockup to observe calls to _loadExternal
        $mockup = new Opus_Model_ModelDefiningExternalField();

        try {
            $mockup->addLazyExternalModel();
        } catch (Exception $ex) {
            // Expect exception because of missing link model class
            $noop = 42;
        }

        // Check that _loadExternal has been called
        $this->assertContains('LazyExternalModel' ,$mockup->loadExternalHasBeenCalledOn, 'The "lazy fetch" external field is not loaded after add call.');
    }

    /**
     * Test if a call to toArray() triggers lazy fetching mechanism.
     *
     * @return void
     */
    public function testToArrayCallTriggersLazyFetching() {
        // Build a mockup to observe calls to _loadExternal
        $mockup = new Opus_Model_ModelDefiningExternalField();

        $mockup->toArray();

        // Check that _loadExternal has been called
        $this->assertContains('LazyExternalModel' ,$mockup->loadExternalHasBeenCalledOn, 'The "lazy fetch" external field is not loaded after toArray() call.');
    }

    /**
     * Test if a call to toXml() triggers lazy fetching mechanism.
     *
     * @return void
     */
    public function testToXmlCallTriggersLazyFetching() {
        // Build a mockup to observe calls to _loadExternal
        $mockup = new Opus_Model_ModelDefiningExternalField();

        $mockup->toXml();

        // Check that _loadExternal has been called
        $this->assertContains('LazyExternalModel' ,$mockup->loadExternalHasBeenCalledOn, 'The "lazy fetch" external field is not loaded after toXml() call.');
    }

    /**
     * Test if multiple calls to store do not change the record.
     *
     * @return void
     */
    public function testStoreIsIdempotend() {
        // Create persistent model
        $model = new Opus_Model_ModelAbstractDb;
        $model->setValue('Foo');
        $id1 = $model->store();

        // Retrieve stored model value from the database table
        $row = $this->dbProvider->find($id1)->current();
        $val1 = $row->value;

        // Trigger a new store
        $id2 = $model->store();

        // Check the value again
        $row = $this->dbProvider->find($id2)->current();
        $val2 = $row->value;

        $this->assertEquals($id1, $id2, 'Store function is not idempotend to identifiers.');
        $this->assertEquals($val1, $val2, 'Store function is not idempotend to values.');
    }

    /**
     * Test if an Exception is thrown is the model to be stored does not
     * validiate its data to be correct.
     *
     * @return void
     */
    public function testStoreThrowsExceptionIfModelHasInvalidData() {
        // Create persistent model
        $model = new Opus_Model_ModelAbstractDb;

        // Inject failing Validator
        $model->getField('Value')->setValidator(new Zend_Validate_Date());
        $model->setValue('InvalidDate');

        // trigger Exception
        $this->setExpectedException('Opus_Model_Exception');
        $id = $model->store();
    }

    /**
     * Test if modified flags of external fields get not cleared while
     * storing internal fields.
     *
     * @return void
     */
    public function testDontClearExternalFieldsModifiedFlagBeforeStoring() {
        // construct mockup class
        $clazz = '
            class testStoreClearsModifiedFlagOfInternalFieldsOnly
            extends Opus_Model_AbstractDb {

                protected static $_tableGatewayClass = \'Opus_Model_AbstractTableProvider\';

                protected $_externalFields = array(
                    \'ExternalField\' => array(
                        \'model\' => \'Opus_Model_ModelAbstractDbMock\')
                );

                protected function _init() {
                    $this->addField(new Opus_Model_Field(\'Value\'));
                }

            }';
        eval($clazz);

        // instanciate mockup
        $model = new testStoreClearsModifiedFlagOfInternalFieldsOnly;

        // mock external field
        $mockFieldExternalModel = $this->getMock('Opus_Model_Field',
            array('clearModified'), array('ExternalField'));
        $model->addField($mockFieldExternalModel);

        // clear and set modified flags respectivly
        $model->getField('ExternalField')->clearModified();
        $model->setValue('XYZ');

        // expect clearModified to be called only once on external field
        $mockFieldExternalModel->expects($this->once())
            ->method('clearModified');

        // trigger behavior
        $model->store();
    }

    /**
     * Test if a new model can be stored even is no modification happend to the instance.
     *
     * @return void
     */
    public function testNewlyCreatedModelCanBeStoredWhenNotModified() {
        $model = new Opus_Model_ModelAbstractDb;
        $id = $model->store();
        $this->assertNotNull($id, 'Expect newly created but unmodified model to be stored.');  
    }

    /**
     * Test is isNewRecord() returns false after successful store.
     *
     * @return void
     */
    public function testIsNewRecordIsFalseAfterStore() {
        $model = new Opus_Model_ModelAbstractDb;
        $id = $model->store();
        $this->assertFalse($model->isNewRecord(), 'Expect stored model not to be marked as new record.');  
    }

 
    /**
     * Test if a second call to store() directly after a successful store()
     * does not execute anything.
     *
     * @return void
     */
    public function testIfStoreTwiceAttemptDoesNotExecuteASecondStore() {
        $model = new Opus_Model_ModelAbstractDb;
        $id = $model->store();
        $model->postStoreHasBeenCalled = false;
        $id = $model->store();
        $this->assertFalse($model->postStoreHasBeenCalled, 'Second store issued on non modified model.');
    }   

    /**
     * Test if a model retreives its external fields in the right order
     *
     * @return void
     */
    public function testFieldsInitializedInWrongOrder() {
        $this->markTestIncomplete('Still waiting/looking for fix...');

        // construct mockup class
        $clazzez = '
            class Opus_CheckFieldOrderDummyClass extends Opus_Model_AbstractDb {
                protected static $_tableGatewayClass = "Opus_Model_AbstractTableProvider";

                protected function _init() {
                    $this->addField(new Opus_Model_Field("Before"));
                    $this->addField(new Opus_Model_Field("Target"));
                    $this->addField(new Opus_Model_Field("After"));
                }

                protected function _fetchBefore() {
                    if (!is_null($this->getTarget())) {
                        return $this->getTarget();
                    }
                    return "bar";
                }

                protected function _fetchTarget() {
                    return "foo";
                }

                protected function _fetchAfter() {
                    if (!is_null($this->getTarget())) {
                        return $this->getTarget();
                    }
                    return "baz";
                }
            }
        ';
        eval($clazzez);

        $model = new Opus_CheckFieldOrderDummyClass();

        $this->assertEquals($model->getBefore(), "foo");
        $this->assertEquals($model->getAfter(), "foo");
    }

}
