<?php declare(strict_types=1);

namespace Core\Email;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class Email
 * @package Core\Email
 */
class Email
{
    protected $wrap = 78;
    protected $to = [];
    protected $subject;
    protected $message;
    protected $headers = [];
    protected $params;
    protected $attachments = [];
    protected $uid;

    /**
     * Named constructor.
     *
     * @return static Email
     */
    public static function make()
    {
        return new Email();
    }

    /**
     * Email constructor.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Resets all properties to initial state.
     *
     * @return Email $this
     */
    public function reset()
    {
        $this->to = [];
        $this->headers = [];
        $this->subject = null;
        $this->message = null;
        $this->wrap = 78;
        $this->params = null;
        $this->attachments = [];
        $this->uid = $this->getUniqueId();
        return $this;
    }

    /**
     * @param string $email The recipient's email address
     * @param string $name The recipient's name
     * @return Email $this
     */
    public function setTo(string $email, string $name)
    {
        $this->to[] = $this->formatHeader($email, $name);
        return $this;
    }

    /**
     * Return an array of formatted recipient addresses.
     *
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }


    /**
     * @param string $email The sender's email address
     * @param string $name The sender's name
     * @return Email $this
     */
    public function setFrom(string $email, string $name)
    {
        $this->addMailHeader('From', $email, $name);
        return $this;
    }

    /**
     * @param array $pairs An array of name => email pairs.
     * @return Email $this
     */
    public function setCc(array $pairs)
    {
        return $this->addMailHeaders('Cc', $pairs);
    }

    /**
     * @param array $pairs An array of name => email pairs
     * @return Email $this
     */
    public function setBcc(array $pairs)
    {
        return $this->addMailHeaders('Bcc', $pairs);
    }

    /**
     * @param string $email
     * @param string|null $name
     * @return Email $this
     */
    public function setReplyTo(string $email, string $name = null)
    {
        return $this->addMailHeader('Reply-To', $email, $name);
    }

    /**
     * @return Email
     */
    public function setHtml()
    {
        return $this->addGenericHeader(
            'Content-Type', 'text/html; charset="utf-8"'
        );
    }

    /**
     * @param string $subject The email's subject
     * @return Email $this
     */
    public function setSubject(string $subject)
    {
        $this->subject = $this->encodeUtf8(
            $this->filterOther($subject)
        );
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $message The message to send.
     * @return Email $this
     */
    public function setMessage(string $message)
    {
        $this->message = str_replace("\n.", "\n..", $message);
        return $this;
    }

    /**
     * @return null|mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $path The file path to the attachment
     * @param string|null $filename The filename of the attachment that will be shown in the email
     * @param null $data
     * @return Email $this
     */
    public function addAttachment(string $path, string $filename = null, $data = null)
    {
        $filename = empty($filename) ? basename($path) : $filename;
        $filename = $this->encodeUtf8($this->filterOther($filename));
        $data = empty($data) ? $this->getAttachmentData($path) : $data;
        $this->attachments[] = [
            'path' => $path,
            'file' => $filename,
            'data' => chunk_split(base64_encode($data))
        ];
        return $this;
    }

    /**
     * @param string $path Path to the attachment file
     * @return bool|string
     */
    public function getAttachmentData(string $path)
    {
        $filesize = filesize($path);
        $handle = fopen($path, "r");
        $attachment = fread($handle, $filesize);
        fclose($handle);
        return $attachment;
    }

    /**
     * @param string $header The header to add
     * @param string $email The email to add
     * @param string|null $name The name to add
     * @return Email $this
     */
    public function addMailHeader(string $header, string $email, string $name = null)
    {
        $address = $this->formatHeader($email, $name);
        $this->headers[] = sprintf('%s: %s', $header, $address);
        return $this;
    }

    /**
     * @param string $header The header to add
     * @param array $pairs An array of name => email pairs
     * @return Email $this
     */
    public function addMailHeaders(string $header, array $pairs)
    {
        if (count($pairs) == 0) {
            throw new InvalidArgumentException(
                'You must pass at least one name => email pair.'
            );
        }

        $addresses = [];
        foreach ($pairs as $name => $email) {
            $name = is_numeric($name) ? null : $name;
            $addresses[] = $this->formatHeader($email, $name);
        }

        $this->addGenericHeader($header, implode(',', $addresses));
        return $this;
    }

    /**
     * @param string $header The generic header to add
     * @param string $value The header's value
     * @return Email $this
     */
    public function addGenericHeader(string $header, string $value)
    {
        $this->headers[] = sprintf(
            '%s: %s',
            $header,
            $value
        );
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $additionalParameters
     * @return Email $this
     */
    public function setParameters(string $additionalParameters)
    {
        $this->params = $additionalParameters;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getParameters()
    {
        return $this->params;
    }

    /**
     * @param int $wrap The number of characters at which the message will wrap
     * @return Email $this
     */
    public function setWrap(int $wrap = 78)
    {
        if ($wrap < 1) {
            $wrap = 78;
        }
        $this->wrap = $wrap;
        return $this;
    }

    /**
     * @return int
     */
    public function getWrap()
    {
        return $this->wrap;
    }

    /**
     * @return bool
     */
    public function hasAttachments()
    {
        return !empty($this->attachments);
    }

    /**
     * @return string
     */
    public function assembleAttachmentHeaders()
    {
        $head = [];
        $head[] = "MIME-Version: 1.0";
        $head[] = "Content-Type: multipart/mixed; boundary=\"{$this->uid}\"";

        return join(PHP_EOL, $head);
    }

    /**
     * @return string
     */
    public function assembleAttachmentBody()
    {
        $body = [];
        $body[] = "This is a multi-part message in MIME format.";
        $body[] = "--{$this->uid}";
        $body[] = "Content-Type: text/html; charset=\"utf-8\"";
        $body[] = "Content-Transfer-Encoding: quoted-printable";
        $body[] = "";
        $body[] = quoted_printable_encode($this->message);
        $body[] = "";
        $body[] = "--{$this->uid}";
        foreach ($this->attachments as $attachment) {
            $body[] = $this->getAttachmentMimeTemplate($attachment);
        }
        return implode(PHP_EOL, $body) . '--';
    }

    /**
     * @param array $attachment An array containing 'file' and 'data' keys
     * @return string
     */
    public function getAttachmentMimeTemplate(array $attachment)
    {
        $file = $attachment['file'];
        $data = $attachment['data'];
        $head = [];
        $head[] = "Content-Type: application/octet-stream; name=\"{$file}\"";
        $head[] = "Content-Transfer-Encoding: base64";
        $head[] = "Content-Disposition: attachment; filename=\"{$file}\"";
        $head[] = "";
        $head[] = $data;
        $head[] = "";
        $head[] = "--{$this->uid}";
        return implode(PHP_EOL, $head);
    }

    /**
     * @return bool
     * @throws RuntimeException on no 'To:' address to send to
     */
    public function send()
    {
        $to = $this->getToForSend();
        $headers = $this->getHeadersForSend();
        if (empty($to)) {
            throw new RuntimeException(
                'Unable to send, no To address has been set.'
            );
        }
        if ($this->hasAttachments()) {
            $message  = $this->assembleAttachmentBody();
            $headers .= PHP_EOL . $this->assembleAttachmentHeaders();
        } else {
            $message = $this->getWrapMessage();
        }
        return mail($to, $this->subject, $message, $headers);
    }

    /**
     * @return string
     */
    public function debug()
    {
        return '<pre>' . print_r($this, true) . '</pre>';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)print_r($this, true);
    }

    /**
     * Formats a display address for emails according to RFC2822
     *
     * @param string $email The email address
     * @param string $name The display name
     * @return string
     */
    public function formatHeader(string $email, string $name = null)
    {
        $email = $this->filterEmail((string) $email);
        if (empty($name)) {
            return $email;
        }
        $name = $this->encodeUtf8($this->filterName((string) $name));
        return sprintf('"%s" <%s>', $name, $email);
    }

    /**
     * @param string $value The value to encode
     * @return string
     */
    public function encodeUtf8(string $value)
    {
        $value = trim($value);
        if (preg_match('/(\s)/', $value)) {
            return $this->encodeUtf8Words($value);
        }
        return $this->encodeUtf8Word($value);
    }

    /**
     * @param string $value The word to encode
     * @return string
     */
    public function encodeUtf8Word(string $value)
    {
        return sprintf('=?UTF-8?B?%s?=', base64_encode($value));
    }

    /**
     * @param string $value The words to encode
     * @return string
     */
    public function encodeUtf8Words(string $value)
    {
        $words = explode(' ', $value);
        $encoded = [];
        foreach ($words as $word) {
            $encoded[] = $this->encodeUtf8Word($word);
        }
        return join($this->encodeUtf8Word(' '), $encoded);
    }

    /**
     * Remove any carriage return, line feed, tab, double quote, comma
     * and angle bracket characters before sanitizing the email address.
     *
     * @param string $email The email to filter
     * @return string
     */
    public function filterEmail(string $email)
    {
        $rule = [
            "\r" => '',
            "\n" => '',
            "\t" => '',
            '"'  => '',
            ','  => '',
            '<'  => '',
            '>'  => ''
        ];
        $email = strtr($email, $rule);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return $email;
    }

    /**
     * Remove any carriage return, line feed or tab characters.
     * Replace double quotes with single quotes and angle brackets with
     * square brackets, before sanitizing the string and stripping out
     * html tags.
     *
     * @param string $name The name to filter
     * @return string
     */
    public function filterName(string $name)
    {
        $rule = [
            "\r" => '',
            "\n" => '',
            "\t" => '',
            '"'  => "'",
            '<'  => '[',
            '>'  => ']',
        ];
        $filtered = filter_var(
            $name,
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_NO_ENCODE_QUOTES
        );
        return trim(strtr($filtered, $rule));
    }

    /**
     * Remove ASCII control characters including any carriage return,
     * line feed or tab characters.
     * @param string $data The data to filter
     * @return string
     */
    public function filterOther(string $data)
    {
        return filter_var($data, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
    }

    /**
     * @return string
     */
    public function getHeadersForSend()
    {
        if (empty($this->headers)) {
            return '';
        }
        return join(PHP_EOL, $this->headers);
    }

    /**
     * @return string
     */
    public function getToForSend()
    {
        if (empty($this->to)) {
            return '';
        }
        return join(', ', $this->to);
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return md5(uniqid((string)time()));
    }

    /**
     * @return string
     */
    public function getWrapMessage()
    {
        return wordwrap($this->message, $this->wrap);
    }
}
