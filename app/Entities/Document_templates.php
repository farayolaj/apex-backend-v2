<?php

namespace App\Entities;

use App\Models\Crud;
use CodeIgniter\Database\BaseBuilder;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the document_templates table.
 */
class Document_templates extends Crud
{
    protected static $tablename = 'Document_templates';
    /* this array contains the field that can be null*/
    static $nullArray = array();
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('name' => 'varchar', 'slug' => 'varchar', 'category' => 'varchar', 'printable' => 'varchar', 'session' => 'smallint', 'prerequisite_fee' => 'int', 'barcode_content' => 'text', 'content' => 'longtext', 'active' => 'tinyint', 'date_added' => 'datetime');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'name' => '', 'slug' => '', 'category' => '', 'printable' => '', 'session' => '', 'prerequisite_fee' => '', 'barcode_content' => '', 'content' => '', 'active' => '', 'date_added' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array(); //array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array();
    static $tableAction = array('delete' => 'delete/document_templates', 'edit' => 'edit/document_templates');

    protected ?string $createdField = 'date_added';
    protected ?string $updatedField = null;
    protected array $searchable = ['a.name', 'a.slug'];

    protected function baseBuilder(): BaseBuilder
    {
        return $this->db->table('document_templates a')
            ->join('sessions b', 'b.id = a.session', 'left')
            ->select("a.id, a.name, a.slug, a.category, a.active, a.date_added, b.date as session");
    }

    public function defaultSelect(): string|array
    {
        return '';
    }

    protected function applyDefaultOrder(BaseBuilder $builder): void
    {
        $builder->orderBy('b.date', 'desc');
    }

    protected function postProcessOne(array $row): array
    {
        if($row['content']) $row['content'] = base64_decode($row['content']);
        return $row;
    }

    public function getDocuments($entryYear, $session)
    {
        $query = "SELECT * from document_templates where (printable = 'year_of_entry' and session = ?) or 
         (printable = 'session' and session = ?) or (category = 'misc') and active = '1'";
        $result = $this->query($query, [$entryYear, $session]);
        if (!$result) {
            return false;
        }
        return $result;
    }

    public function getSingleDocument($session, $dbColumn, $dbValue)
    {
        $query = "SELECT * from document_templates where (printable = 'session' and session = ?) and active = '1' 
        and $dbColumn = ?";
        $result = $this->query($query, [$session, $dbValue]);
        if (!$result) {
            return false;
        }
        return $result;
    }

    public function getDocumentTemplates($slug, $variables, $yearOfEntry = '', $currentSession = '')
    {
        $parser = \CodeIgniter\Config\Factories::libraries('Parser');
        $result = $this->getWhere(['slug' => $slug], $count, 0, null, false);
        if ($result) {
            $documentContent = '';
            foreach ($result as $row) {
                if ($row->category == 'general' && $row->printable == 'year_of_entry') {
                    $temp = $this->getWhere(array('slug' => $slug, 'session' => $yearOfEntry), $count, 0, null, false);
                    if (!$temp) {
                        return null;
                    }
                    $temp = $temp[0];
                    $documentContent = $temp->content;
                } elseif ($row->category == 'general' && $row->printable == 'session') {
                    $temp = $this->getWhere(array('slug' => $slug, 'session' => $currentSession), $count, 0, null, false);
                    if (!$temp) {
                        return null;
                    }
                    $temp = $temp[0];
                    $documentContent = $temp->content;
                } else {
                    $documentContent = $row->content;
                }

                $message = base64_decode($documentContent);
                $message = str_replace("{current_level}00", "{current_level}", $message);
                return $parser->parse_string($message, $variables, true);

            }
        }
    }

    public function getDocumentBarcodeContentBySlug($slug)
    {
        $result = $this->getWhere(['slug' => $slug], $count, 0, null, false);
        if (!$result) {
            return null;
        }
        return $result[0]->barcode_content;
    }

}

