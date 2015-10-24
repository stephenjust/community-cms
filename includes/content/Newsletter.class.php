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

class Newsletter
{
    private $mId;
    private $mExists = false;
    
    private $mPage;
    private $mYear;
    private $mMonth;
    private $mLabel;
    private $mPath;
    private $mHidden;
    
    public function __construct($id) 
    {
        if (!is_numeric($id)) {
            throw new NewsletterException('Invalid newsletter ID'); 
        }
        
        $this->mId = $id;

        // Get newsletter info
        $query = 'SELECT `page`, `year`, `month`, `label`, `path`, `hidden`
			FROM `'.NEWSLETTER_TABLE.'` WHERE `id` = :id LIMIT 1';
        try {
            $result = DBConn::get()->query($query, [':id' => $id], DBConn::FETCH);
        } catch (Exceptions\DBException $e) {
            throw new NewsletterException('Failed to access newsletter database.');
        }
        if ($result) {
            $this->mExists = true;
            $this->mPage = $result['page'];
            $this->mYear = $result['year'];
            $this->mMonth = $result['month'];
            $this->mLabel = $result['label'];
            $this->mPath = $result['path'];
            $this->mHidden = $result['hidden'];
        }
    }

    /**
     * Delete newsletter entry from the database
     * @param integer $id Newsletter ID
     * @throws NewsletterException
     */
    public function delete() 
    {
        if (!$this->mExists) {
            throw new NewsletterException('Newsletter does not exist.'); 
        }
        
        // Check permission
        if (!acl::get()->check_permission('newsletter_delete')) {
            throw new NewsletterException('You are not allowed to delete newsletters.'); 
        }

        // Delete newsletter entry
        $delete_query = 'DELETE FROM `'.NEWSLETTER_TABLE.'`
			WHERE `id` = :id';
        try {
            DBConn::get()->query($delete_query, [':id' => $this->mId], DBConn::NOTHING);
            Log::addMessage('Deleted newsletter \''.$this->mLabel.'\'');
        } catch (Exceptions\DBException $ex) {
            throw new NewsletterException('An error occurred when deleting the newsletter entry.');
        }
        $this->mExists = false;
    }

    /**
     * Create a newsletter record
     * @param string  $entry_name
     * @param string  $file
     * @param integer $page       Numeric Page ID
     * @param integer $year
     * @param integer $month
     * @throws NewsletterException 
     * @return Newsletter Newsletter instance for created item
     */
    public static function create($entry_name,$file,$page,$year,$month) 
    {
        // Check permissions
        if (!acl::get()->check_permission('newsletter_create')) {
            throw new NewsletterException('You are not allowed to create newsletters.'); 
        }

        $entry_file = join('/', array('./files', trim($file, '/')));
        if (strlen($entry_name) == 0) {
            throw new NewsletterException('No label was given for the newsletter.'); 
        }
        if (strlen($entry_file) <= 3) {
            throw new NewsletterException('No file was selected for the newsletter.'); 
        }
        if ($month > 12 || $month < 1) {
            throw new NewsletterException('An invalid month was selected for the newsletter.'); 
        }
        if ($year > 3000 || $year < 1000) {
            throw new NewsletterException('An invalid year was selected for the newsletter.'); 
        }

        // Validate the newsletter page
        if (!PageUtil::exists($page)) {
            throw new NewsletterException('The page given for the newsletter does not exist.');
        }

        // Create the new newsletter record
        $query = 'INSERT INTO `'.NEWSLETTER_TABLE."`
			(`label`,`page`,`year`,`month`,`path`) VALUES
			(:label, :page, :year, :month, :path)";
        try {
            DBConn::get()->query($query,
                [
                    ':label' => $entry_name,
                    ':page' => $page,
                    ':year' => $year,
                    ':month' => $month,
                    ':path' => $entry_file
                ],
                DBConn::NOTHING);
            $insert_id = DBConn::get()->lastInsertId();
            Log::addMessage('Newsletter \''.$entry_name.'\' added to page '.PageUtil::getTitle($page));
        } catch (Exceptions\DBException $ex) {
            throw new NewsletterException('An error occurred when creating the newsletter.');
        }
        return new Newsletter($insert_id);
    }

    /**
     * Get an array of all newsletters
     * from most recent to oldest
     * @return Newsletter
     * @throws NewsletterException
     */
    public static function getAll() 
    {
        $return = array();

        $query = 'SELECT `id`
			FROM `'.NEWSLETTER_TABLE.'`
			ORDER BY year desc, month desc';
        try {
            $results = DBConn::get()->query($query, null, DBConn::FETCH_ALL);
        } catch (Exceptions\DBException $ex) {
            throw new NewsletterException('Failed to lookup newsletters.');
        }

        foreach ($results as $record) {
            $return[] = new Newsletter($record['id']);
        }
        return $return;
    }

    /**
     * Get an array of newsletters on the specified page
     * from most recent to oldest
     * @param int $page
     * @return Newsletter
     * @throws NewsletterException
     */
    public static function getByPage($page) 
    {
        $return = array();
        
        $query = 'SELECT `id`
			FROM `'.NEWSLETTER_TABLE.'`
			WHERE `page` = :page ORDER BY year desc, month desc';
        try {
            $results = DBConn::get()->query($query, [':page' => $page],
                DBConn::FETCH_ALL);
        } catch (Exceptions\DBException $ex) {
            throw new NewsletterException('Failed to lookup newsletters.');
        }

        foreach ($results as $record) {
            $return[] = new Newsletter($record['id']);
        }
        return $return;
    }
    
    /**
     * Get hidden state
     * @return boolean
     * @throws NewsletterException
     */
    public function getHidden() 
    {
        if (!$this->mExists) {
            throw new NewsletterException('Newsletter does not exist!'); 
        }
        
        return (boolean) $this->mHidden;
    }
    
    /**
     * Get ID
     * @return int
     * @throws NewsletterException
     */
    public function getId() 
    {
        if (!$this->mExists) {
            throw new NewsletterException('Newsletter does not exist!'); 
        }
        
        return $this->mId;
    }
    
    /**
     * Get label
     * @return string
     * @throws NewsletterException
     */
    public function getLabel() 
    {
        if (!$this->mExists) {
            throw new NewsletterException('Newsletter does not exist!'); 
        }
        
        return HTML::schars($this->mLabel);
    }
    
    /**
     * Get month
     * @return int
     * @throws NewsletterException
     */
    public function getMonth() 
    {
        if (!$this->mExists) {
            throw new NewsletterException('Newsletter does not exist!'); 
        }
        
        return $this->mMonth;
    }
    
    /**
     * Get month string
     * @return string
     * @throws NewsletterException
     */
    public function getMonthString() 
    {
        if (!$this->mExists) {
            throw new NewsletterException('Newsletter does not exist!'); 
        }
        
        $months = array('January','February','March','April','May','June','July',
        'August','September','October','November','December');
        return $months[$this->mMonth - 1];
    }
    
    /**
     * Get path
     * @return string
     * @throws NewsletterException
     */
    public function getPath() 
    {
        if (!$this->mExists) {
            throw new NewsletterException('Newsletter does not exist!'); 
        }
        
        return HTML::schars($this->mPath);
    }

    /**
     * Get page
     * @return int
     */
    public function getPage()
    {
        if (!$this->mExists) {
            throw new NewsletterException('Newsletter does not exist!');
        }

        return $this->mPage;
    }

    /**
     * Get year
     * @return int
     * @throws NewsletterException
     */
    public function getYear() 
    {
        if (!$this->mExists) {
            throw new NewsletterException('Newsletter does not exist!'); 
        }
        
        return $this->mYear;
    }
}

class NewsletterException extends \Exception
{
}
