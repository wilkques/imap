# Imap

## How to use
```php
$imapPath = 'smtp host';
$username = 'username';
$password = 'password';

var_dump(
    (new ImapMail)->setImapPath($imapPath)->setUserName($username)->setPassWord($password)->getMail()
);
```

or

```php
$imapPath = 'smtp host';
$username = 'username';
$password = 'password';

var_dump(
    (new ImapMail($imapPath, $username, $password))->getMail()
);
```