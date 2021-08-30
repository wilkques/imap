<?php

namespace Wilkques\Imap;

class ImapMailResource
{
    /** @var string */
    protected $subject;
    /** @var string */
    protected $toAddress;
    /** @var string */
    protected $date;
    /** @var string */
    protected $fromAddress;
    /** @var string */
    protected $replyToAddress;
    /** @var string */
    protected $body;

    /**
     * @param string $subject
     * 
     * @return $this
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $toAddress
     * 
     * @return $this
     */
    public function setToAddress(string $toAddress)
    {
        $this->toAddress = $toAddress;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getToAddress()
    {
        return $this->toAddress;
    }

    /**
     * @param string $date
     * 
     * @return $this
     */
    public function setDate(string $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $fromAddress
     * 
     * @return $this
     */
    public function setFromAddress(string $fromAddress)
    {
        $this->fromAddress = $fromAddress;

        return $this;
    }

    public function getFromAddress()
    {
        return $this->fromAddress;
    }

    /**
     * @param string $replyToAddress
     * 
     * @return $this
     */
    public function setReplyToAddress(string $replyToAddress)
    {
        $this->replyToAddress = $replyToAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getReplyToAddress()
    {
        return $this->replyToAddress;
    }

    /**
     * @param string $body
     * 
     * @return $this
     */
    public function setBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $variable
     * 
     * @return string
     */
    public function __get(string $param)
    {
        return $this->$param;
    }
}
