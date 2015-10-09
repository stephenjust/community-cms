<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2013-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

class Contact
{
    private $mId;
    private $mName;
    private $mPhone;
    private $mEmail;
    private $mAddress;
    private $mTitle;

    /**
     * Check if the supplied page ID belongs to a contact list page
     * @param integer $page_id
     * @return boolean
     * @throws ContactException
     */
    public static function isContactList($page_id)
    {
        $query = "SELECT `page`.`id`, `page`.`title` "
            . "FROM `".PAGE_TABLE."` `page`, `".PAGE_TYPE_TABLE."` `pt` "
            . "WHERE `page`.`type` = `pt`.`id` "
            . "AND `pt`.`name` = :page_type "
            . "AND `page`.`id` = :id";
        try {
            $count = DBConn::get()->query($query, [":id" => $page_id, ":page_type" => "Contacts"], DBConn::ROW_COUNT);
        } catch (Exceptions\DBException $ex) {
            throw new ContactException("Failed to read contact lists.", $ex);
        }
        return ($count > 0);
    }

    /**
     * Load a contact record
     * @param integer $id
     * @throws ContactException
     */
    public function __construct($id) 
    {
        $query = 'SELECT *
            FROM `'.CONTACTS_TABLE.'`
            WHERE `id` = :id LIMIT 1';
        try {
            $contact = DBConn::get()->query($query, [":id" => $id], DBConn::FETCH);
        } catch (Exceptions\DBException $ex) {
            throw new ContactException('Error reading contact information.', $ex);
        }
        if (!$contact) {
                throw new ContactException("Contact not found.");
        }

        $this->mId = $contact['id'];
        $this->mName = $contact['name'];
        $this->mPhone = $contact['phone'];
        $this->mEmail = $contact['email'];
        $this->mAddress = $contact['address'];
        $this->mTitle = $contact['title'];
    }

    /**
     * Check if the contact is on the specified list
     * @param integer $list_id
     * @return boolean
     * @throws ContactException
     */
    public function isOnList($list_id)
    {
        $query = "SELECT `id` FROM `".CONTENT_TABLE."` "
            . "WHERE `ref_id` = :ref_id "
            . "AND `page_id` = :list_id";
        try {
            $count = DBConn::get()->query($query, [":ref_id" => $this->mId, ":list_id" => $list_id], DBConn::ROW_COUNT);
        } catch (Exceptions\DBException $ex) {
            throw new ContactException("Failed to read contact list.", $ex);
        }
        return ($count > 0);
    }

    /**
     * Add contact to a contact list
     * @param integer $list_id
     * @throws ContactException
     */
    public function addToList($list_id) 
    {
        acl::get()->require_permission('contacts_edit_lists');
        if (!self::isContactList($list_id)) {
            throw new ContactException('Contact list does not exist.');
        }
        if ($this->isOnList($list_id)) {
            throw new ContactException("Contact is already on this list.");
        }

        // Add contact to list
        $query = "INSERT INTO `".CONTENT_TABLE."` "
            . "(`page_id`,`ref_type`,`ref_id`) "
            . "VALUES "
            . "(:list_id, "
            . "     (SELECT `id` "
            . "      FROM `".PAGE_TYPE_TABLE."` "
            . "      WHERE `name` = :page_type), "
            . " :id)";
        try {
            DBConn::get()->query($query,
                [":list_id" => $list_id,
                 ":page_type" => "Contacts",
                 ":id" => $this->mId],
                DBConn::NOTHING);

            $page_title = PageUtil::getTitle($list_id);
            Log::addMessage("Added {$this->mName} to contact list {$page_title}");
        } catch (Exceptions\DBException $ex) {
            throw new ContactException("Failed to add contact to list.", $ex);
        }
    }
    
    /**
     * Create a contact record
     * @param string $name
     * @param string $title
     * @param string $phone
     * @param string $address
     * @param string $email
     * @return \Contact
     * @throws ContactException
     */
    public static function create($name, $title, $phone, $address, $email) 
    {
        acl::get()->require_permission('contacts_create');

        if ($phone != "" && !Validate::telephone($phone)) {
            throw new ContactException(sprintf('Invalid telephone number: %s', HTML::schars($phone))); 
        }
        if ($email != "" && !Validate::email($email)) {
            throw new ContactException('Invalid email address.'); 
        }

        $query = 'INSERT INTO `'.CONTACTS_TABLE."` 
            (`name`,`title`,`phone`,`email`,`address`)
            VALUES
            (:name, :title, :phone, :email, :address)";
        try {
            DBConn::get()->query($query,
                [
                    ":name" => $name,
                    ":title" => $title,
                    ":phone" => Validate::telephone($phone),
                    ":email" => $email,
                    ":address" => $address
                ],
                DBConn::NOTHING);
            $insert_id = DBConn::get()->lastInsertId();
            Log::addMessage("New contact '$name'");
        } catch (Exceptions\DBException $ex) {
            throw new ContactException("Failed to create contact record.", $ex);
        }

        return new Contact($insert_id);
    }
    
    /**
     * Delete this contact
     * @throws ContactException
     */
    public function delete() 
    {
        acl::get()->require_permission('contacts_delete');

        $this->deleteFromAllLists();

        $query = 'DELETE FROM `'.CONTACTS_TABLE.'`
            WHERE `id` = :id';
        try {
            DBConn::get()->query($query, [":id" => $this->mId], DBConn::NOTHING);
            Log::addMessage("Deleted contact '{$this->mName}'");
        } catch (Exceptions\DBException $ex) {
            throw new ContactException("Failed to delete contact record.", $ex);
        }
    }

    /**
     * Remove contact from list
     * @param integer $list_id
     * @throws ContactException
     */
    function deleteFromList($list_id)
    {
        acl::get()->require_permission('contacts_edit_lists');
        if (!self::isContactList($list_id)) {
            throw new ContactException('Invalid list.'); 
        }

        $query = 'DELETE FROM `'.CONTENT_TABLE.'`
            WHERE `ref_id` = :id
            AND `page_id` = :list_id';
        try {
            DBConn::get()->query($query,
                [":id" => $this->mId, ":list_id" => $list_id],
                DBConn::NOTHING);
            Log::addMessage("Removed '{$this->mName}' from contact list.");
        } catch (Exceptions\DBException $ex) {
            throw new ContactException("Failed to remove contact from list.", $ex);
        }
    }

    /**
     * Remove this contact from all lists
     * @throws ContactException
     */
    public function deleteFromAllLists()
    {
       acl::get()->require_permission('contacts_edit_lists');

        $query = 'DELETE FROM `'.CONTENT_TABLE.'`
            WHERE `ref_id` = :id';
        try {
            DBConn::get()->query($query,
                [":id" => $this->mId],
                DBConn::NOTHING);
        } catch (Exceptions\DBException $ex) {
            throw new ContactException("Failed to remove contact from list.", $ex);
        }
    }

    /**
     * Edit contact record
     * @param string $name
     * @param string $title
     * @param string $phone
     * @param string $address
     * @param string $email
     * @throws ContactException
     */
    public function edit($name, $title, $phone, $address, $email) 
    {
        acl::get()->require_permission('contacts_edit');

        if ($phone != "" && !Validate::telephone($phone)) {
            throw new ContactException(sprintf('Invalid telephone number: %s', HTML::schars($phone))); 
        }
        if ($email != "" && !Validate::email($email)) {
            throw new ContactException('Invalid email address.'); 
        }

        $query = "UPDATE `".CONTACTS_TABLE."`
		SET `name`=:name, `title`=:title,
		`phone`=:phone, `email`=:email, `address`=:address
		WHERE `id` = :id";
        try {
            DBConn::get()->query($query,
                [
                    ":name" => $name,
                    ":title" => $title,
                    ":phone" => Validate::telephone($phone),
                    ":email" => $email,
                    ":address" => $address,
                    ":id" => $this->mId,
                ],
                DBConn::NOTHING);
            Log::addMessage("Edited contact '{$this->mName}'");
        } catch (Exceptions\DBException $ex) {
            throw new ContactException("Failed to update contact record.", $ex);
        }
        
        $this->mName = $name;
        $this->mTitle = $title;
        $this->mPhone = $phone;
        $this->mEmail = $email;
        $this->mAddress = $address;
    }
    
    public function getAddress() 
    {
        return HTML::schars($this->mAddress);
    }
    
    public function getEmail() 
    {
        return HTML::schars($this->mEmail);
    }
    
    public function getId() 
    {
        return $this->mId;
    }
    
    /**
     * Get IDs of contacts in a list
     * @param integer $list_id
     * @return \Contact
     * @throws ContactException
     */
    public static function getList($list_id) 
    {
        if (!self::isContactList($list_id)) {
            throw new ContactException('Invalid contact list.');
        }
        
        $query = 'SELECT `ref_id`
            FROM `'.CONTENT_TABLE.'`
            WHERE `page_id` = :list_id
            ORDER BY `order` ASC';
        try {
            $refs = DBConn::get()->query($query, [":list_id" => $list_id], DBConn::FETCH_ALL);
        } catch (Exceptions\DBException $ex) {
            throw new ContactException('Failed to load contact list.', $ex);
        }

        $contacts = array();
        foreach ($refs as $ref) {
            $contacts[] = new Contact($ref['ref_id']);
        }
        return $contacts;
    }
    
    public function getName() 
    {
        return HTML::schars($this->mName);
    }
    
    public function getPhone() 
    {
        return HTML::schars(StringUtils::formatTelephoneNumber($this->mPhone));
    }
    
    public function getTitle() 
    {
        return HTML::schars($this->mTitle);
    }

    /**
     * Set contact list order
     * @param integer $order
     * @param integer $list_id
     * @throws ContactException
     */
    public function setListOrder($order, $list_id)
    {
        acl::get()->require_permission('contacts_edit_lists');
        if (!self::isContactList($list_id)) {
            throw new ContactException('Invalid contact list.');
        }
        if (!is_numeric($order)) {
            throw new ContactException('Invalid order.');
        }

        $query = 'UPDATE `'.CONTENT_TABLE.'`
            SET `order` = :order
            WHERE `ref_id` = :id
            AND `page_id` = :list_id';
        try {
            DBConn::get()->query($query, [":order" => $order, ":id" => $this->mId, ":list_id" => $list_id], DBConn::NOTHING);
        } catch (Exceptions\DBException $ex) {
            throw new ContactException("Failed to set contact order.", $ex);
        }
    }
    
}

class ContactException extends \Exception
{
}
