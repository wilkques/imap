<?php

namespace Wilkques\Imap;

use Wilkques\Imap\Exceptions\ConnectionException;
use Wilkques\Imap\Exceptions\FetchbodyException;
use Wilkques\Imap\Exceptions\HearderInfoException;
use Wilkques\Imap\Exceptions\SearchException;
use Wilkques\Imap\Helpers\Collection;

class MailBox
{
    /** @var string|null */
    protected $mailBox;

    /** @var resource */
    protected $connection;

    /** @var array|[] */
    protected $auth = [];

    /** @var array */
    protected $search;

    /**
     * @param string $mailBox
     * @param string $username
     * @param string $password
     */
    public function __construct(string $mailBox = null, string $username = null, string $password = null)
    {
        $this->setMailBox($mailBox)->setUserName($username)->setPassWord($password);
    }

    /**
     * @param string $mailBox
     * 
     * @return static
     */
    public function setMailBox(string $mailBox = null)
    {
        $this->mailBox = $mailBox;

        return $this;
    }

    /**
     * @return string
     */
    public function getMailBox()
    {
        return $this->mailBox;
    }

    /**
     * @param string $username
     * 
     * @return static
     */
    public function setUserName(string $username = null)
    {
        $this->auth['username'] = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->auth['username'];
    }

    /**
     * @param string $password
     * 
     * @return static
     */
    public function setPassWord(string $password = null)
    {
        $this->auth['password'] = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassWord()
    {
        return $this->auth['password'];
    }

    /**
     * @param int $flags
     * @param int $retries
     * @param array|[] $options
     * 
     * @throws ConnectionException
     * 
     * @return static
     * 
     * @see [imap_open](https://www.php.net/manual/en/function.imap-open.php)
     */
    public function connection(int $flags = 0, int $retries = 0, array $options = [])
    {
        !$this->getConnection() && $this->connection = \imap_open($this->getMailBox(), $this->getUserName(), $this->getPassWord(), $flags, $retries, $options) or throw new ConnectionException('Cannot connect to: ' . imap_last_error());

        return $this;
    }

    /**
     * @return resource|null|\IMAP\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param string $criteria
     * @param int $flags
     * @param string $charset
     * 
     * @throws SearchException
     * 
     * @return static
     * 
     * @see [imap_search](https://www.php.net/manual/en/function.imap-search.php)
     */
    public function setMailSearch(string $criteria = 'SEEN', int $flags = SE_FREE, string $charset = "")
    {
        !$this->search && $this->search = \imap_search($this->getConnection(), $criteria, $flags, $charset) or throw new SearchException('Search error: ' . imap_last_error());

        return $this;
    }

    /**
     * @return array
     */
    public function getMailSearch()
    {
        return $this->search;
    }

    /**
     * @param array|[] $items
     * 
     * @return Collection
     */
    protected function newCollection($items = [])
    {
        return new Collection($items);
    }

    /**
     * @param callback $callable
     * 
     * @return Collection
     */
    public function getMail(callable $callable = null)
    {
        return $this->newCollection(
            $this->connection()->setMailSearch()->getMailSearch()
        )->transform(
            fn ($mail) => $callable ? $callable($mail) : $this->mailList($mail)
        );
    }

    /**
     * @param int $mail
     * 
     * @return Resource
     */
    protected function mailList(int $mail)
    {
        $headerInfo = $this->headers($mail);

        return $this->newCollection([
            'subject'           => \imap_utf8($headerInfo->subject),
            'toaddress'         => \imap_utf8($headerInfo->toaddress),
            'date'              => \imap_utf8($headerInfo->date),
            'fromaddress'       => \imap_utf8($headerInfo->fromaddress),
            'reply_toaddress'   => \imap_utf8($headerInfo->reply_toaddress),
            'body'              => \imap_utf8($this->parts($mail)),
        ]);
    }

    /**
     * @param int $mail
     * 
     * @return object|\stdClass|false
     */
    public function headers(int $mail)
    {
        return \imap_headerinfo($this->getConnection(), $mail) or throw new HearderInfoException('Header info error: ' . imap_last_error());
    }

    /**
     * @param int $mail
     * 
     * @return string
     */
    public function parts(int $mail)
    {
        $body = '';

        $emailStructure = \imap_fetchstructure($this->getConnection(), $mail);

        isset($emailStructure->parts) && $body = $this->body($mail, $emailStructure);

        return $body;
    }

    /**
     * @param int $mail
     * @param object|\stdClass|false $emailStructure
     * 
     * @return string
     */
    public function body(int $mail, $emailStructure)
    {
        $data = \imap_fetchbody($this->getConnection(), $mail, FT_PEEK) or throw new FetchbodyException('Header info error: ' . imap_last_error());

        return $this->mailParserDecode($data, $emailStructure->parts[0]->encoding);
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
        // \imap_expunge($this->getConnection());
        // \imap_close($this->getConnection());
    }

    /**
     * @param string $method
     * @param array $arguments
     * 
     * @return static|Client
     */
    public function __call(string $method, array $arguments)
    {
        $method = ltrim(trim($method));

        in_array($method, ['mailBox', 'userName', 'passWord', 'mailSearch', 'connection']) && $method = 'set' . ucfirst($method);

        return $this->{$method}(...$arguments);
    }

    /**
     * @param string $method
     * @param array $arguments
     * 
     * @return static
     */
    public static function __callStatic(string $method, array $arguments)
    {
        return (new static)->{$method}(...$arguments);
    }
}
