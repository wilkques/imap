<?php

namespace Wilkques\Imap;

class ImapMail
{
    /** @var string */
    protected $imapPath;
    /** @var resource */
    protected $connection;
    /** @var string */
    protected $username;
    /** @var string */
    protected $password;
    /** @var array */
    protected $search;
    /** @var string */
    protected $criteria;

    /**
     * @param string $imapPath
     * @param string $username
     * @param string $password
     * @param string $criteria
     * 
     * @return mixed
     */
    public function __construct(string $imapPath = '', string $username = '', string $password = '', string $criteria = 'SEEN')
    {
        $imapPath != '' && $this->setImapPath($imapPath);
        $username != '' && $this->setUserName($username);
        $password != '' && $this->setPassWord($password);
        $criteria != '' && $this->setCriteria($criteria);
    }

    /**
     * @param string $imapPath
     * 
     * @return string
     */
    public function setImapPath(string $imapPath)
    {
        $this->imapPath = $imapPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getImapPath()
    {
        return $this->imapPath;
    }

    /**
     * @param string $username
     * 
     * @return $this
     */
    public function setUserName(string $username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->username;
    }

    /**
     * @param string $password
     * 
     * @return $this
     */
    public function setPassWord(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassWord()
    {
        return $this->password;
    }

    /**
     * @return $this
     */
    public function connection()
    {
        $this->connection = \imap_open($this->getImapPath(), $this->getUserName(), $this->getPassWord()) or die('Cannot connect to: ' . imap_last_error());

        return $this;
    }

    /**
     * @return resource
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param string $criteria
     * 
     * @return $this
     */
    public function setCriteria(string $criteria = 'SEEN')
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * @return string
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @return $this
     */
    protected function mailSearch()
    {
        $this->search = \imap_search($this->getConnection(), $this->getCriteria());

        return $this;
    }

    /**
     * @return array
     */
    protected function getMailSearch()
    {
        return $this->search;
    }

    /**
     * @return ImapMailCollection
     */
    public function getMail()
    {
        return (new ImapMailCollection(
            $this->connection()->mailSearch()->getMailSearch()
        ))->transform(
            fn ($mail) => $this->mailList($mail)
        );
    }

    /**
     * @param integer $mail
     * 
     * @return App\Library\Mail\ImapMailResource
     */
    protected function mailList(int $mail)
    {
        $mailResource = new ImapMailResource;

        $headerInfo = \imap_headerinfo($this->getConnection(), $mail);

        return $mailResource->setSubject(\imap_utf8($headerInfo->subject))
            ->setToAddress($headerInfo->toaddress)
            ->setDate($headerInfo->date)
            ->setFromAddress(\imap_utf8($headerInfo->fromaddress))
            ->setReplyToAddress(\imap_utf8($headerInfo->reply_toaddress))
            ->setBody($this->mailBody($mail));
    }

    /**
     * @param integer $mail
     * 
     * @return string
     */
    public function mailBody(int $mail)
    {
        $mbox = $this->getConnection();

        $body = '';

        $emailStructure = \imap_fetchstructure($mbox, $mail);

        if (isset($emailStructure->parts)) {
            $data = \imap_fetchbody($mbox, $mail, FT_PEEK);

            $body = $this->mailParserDecode($data, $emailStructure->parts[0]->encoding);
        }

        return $body;
    }

    /**
     * @param string $data
     * @param integer $encoding
     * 
     * @return string
     */
    public function mailParserDecode(string $data, int $encoding = 0)
    {
        switch ($encoding) {
            case 1:
                $data = \imap_utf8($data);
                break;
            case 2:
            case 5:
                break;
            case 3:
                $data = \strip_tags(\imap_base64($data));
                break;
            default:
                $data = \imap_qprint(\strip_tags($data));
                break;
        }

        return $data;
    }

    public function __destruct()
    {
        // colse the connection
        \imap_expunge($this->getConnection());
        \imap_close($this->getConnection());
    }
}
