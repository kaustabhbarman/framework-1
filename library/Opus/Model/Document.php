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
 * @category    Framework
 * @package     Opus_Model
 * @author      Felix Ostrowski (ostrowski@hbz-nrw.de)
 * @author      Ralf Claußnitzer (ralf.claussnitzer@slub-dresden.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Domain model for documents in the Opus framework
 *
 * @category    Framework
 * @package     Opus_Model
 * @uses        Opus_Model_Abstract
 */
class Opus_Model_Document extends Opus_Model_Abstract
{

    /**
     * The document is the most complex Opus_Model. An Opus_Document_Builder is
     * used in the _init() function to construct an Opus_Model_Document of a
     * certain type.
     *
     * @var Opus_Document_Builder
     */
    protected $_builder;

    /**
     * The documents external fields, i.e. those not mapped directly to the
     * Opus_Db_Documents table gateway.
     *
     * @var array
     * @see Opus_Model_Abstract::$_externalFields
     */
    protected $_externalFields = array(
            'TitleMain' => array(
                'model' => 'Opus_Model_Dependent_Title',
                'table' => 'Opus_Db_DocumentTitleAbstracts',
                'conditions' => array('title_abstract_type' => 'main')
            ),
            'TitleAbstract' => array(
                'model' => 'Opus_Model_Dependent_Abstract',
                'table' => 'Opus_Db_DocumentTitleAbstracts',
                'conditions' => array('title_abstract_type' => 'abstract')
            ),
            'TitleParent' => array(
                'model' => 'Opus_Model_Dependent_Parent',
                'table' => 'Opus_Db_DocumentTitleAbstracts',
                'conditions' => array('title_abstract_type' => 'parent')
            ),
            'Isbn' => array(
                'model' => 'Opus_Model_Dependent_Isbn',
                'table' => 'Opus_Db_DocumentIdentifiers',
                'conditions' => array('identifier_type' => 'isbn')
            ),
            'Note' => array(
                'model' => 'Opus_Model_Dependent_Note',
                'table' => 'Opus_Db_DocumentNotes',
            ),
            'Patent' => array(
                'model' => 'Opus_Model_Dependent_Patent',
                'table' => 'Opus_Db_DocumentPatents',
            ),
            'Enrichment' => array(
                'model' => 'Opus_Model_Dependent_Enrichment',
                'table' => 'Opus_Db_DocumentEnrichments',
            ),
            'Institute' => array(),
            'PersonAuthor' => array(
                'model' => 'Opus_Model_Dependent_Link_DocumentAuthor',
                'table' => 'Opus_Db_LinkPersonsPublications',
                'conditions' => array('role' => 'author')
            ),
            'Licence' => array(
                'model' => 'Opus_Model_Dependent_Link_DocumentLicence',
                'table' => 'Opus_Db_LinkDocumentsLicences'
            ),
        );

    /**
     * Constructor.
     *
     * @param integer|string $id                (Optional) Id an existing document.
     * @param string         $type              (Optional) Type of a new document.
     * @param Zend_Db_Table  $tableGatewayModel (Optional) Opus_Db class to use.
     * @see    Opus_Model_Abstract::__construct()
     * @see    $_builder
     * @throws InvalidArgumentException         Thrown if id and type are passed.
     * @throws Opus_Model_Exception             Thrown invalid type is passed.
     */
    public function __construct($id = null, $type = null, Zend_Db_Table $tableGatewayModel = null) {
        if ($id === null and $type === null) {
            throw new InvalidArgumentException('Either id or type must be passed.');
        }

        if ($tableGatewayModel === null) {
            parent::__construct($id, new Opus_Db_Documents);
        } else {
            parent::__construct($id, $tableGatewayModel);
        }

        if ($id === null) {
            if (is_string($type) === true) {
                $this->_builder = new Opus_Document_Builder(new Opus_Document_Type($type));
            } else if ($type instanceof Opus_Document_Type) {
                $this->_builder = new Opus_Document_Builder($type);
            } else {
                throw new Opus_Model_Exception('Unkown document type.');
            }
        } else if ($type === null) {
            $this->_builder = new Opus_Document_Builder(new
                    Opus_Document_Type($this->_primaryTableRow->document_type));
        }

        $this->_builder->addFieldsTo($this);

        parent::_fetchValues();
    }

    /**
     * Opus_Model_Document has extensive database initialization to do. Thus,
     * _fetchValues() ist overwritten and parent::_fetchValue() is called at the
     * right time.
     *
     * @see    __construct()
     * @return void
     */
    protected function _fetchValues() {
    }

    /**
     * Set the type for the document.
     *
     * @param string $type
     */
    // FIXME: Currently destroys all field values!
    //public function setDocumentType($type) {
    //    $this->_builder = new Opus_Document_Builder(new Opus_Document_Type($type));
    //    $this->_fields['DocumentType'] = $type;
    //    // TODO: Remove and restore old field values
    //    $this->_builder->addFieldsTo($this);
    //    parent::_fetchValues();
    //}
}
