# Imap for PHP

## How to use
```php
$mailBox = 'smtp host';
$username = 'username';
$password = 'password';

$mail = (new MailBox)->mailBox($mailBox)->userName($username)->passWord($password);

// or

$mail = new MailBox($mailBox, $username, $password);

// or

$mail = MailBox::mailBox($mailBox)->userName($username)->passWord($password);

// imap open args
$mail->connect(OP_SECURE); // Don't do non-secure authentication

// imap search args
$mail->mailSearch('ALL'); // return all messages matching the rest of the criteria

$mailCollection = $mail->getMail();

// or

$mailCollection = $mail->getMail(function ($mail) {
    // do something
});
```

