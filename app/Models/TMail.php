<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cookie;
use App\Models\Meta;
use Carbon\Carbon;
//IMAP Includes
use Ddeboer\Imap\Server;
use Ddeboer\Imap\SearchExpression;
use Ddeboer\Imap\Search\Email\To;

class TMail extends Model {

    public static function connectMailBox($imap = null) {
        if ($imap === null) {
            $imap = config('app.settings.imap');
        }
        $flags = $imap['protocol'] . '/' . $imap['encryption'];
        if ($imap['validate_cert']) {
            $flags = $flags . '/validate-cert';
        } else {
            $flags = $flags . '/novalidate-cert';
        }
        $server = new Server($imap['host'], $imap['port'], $flags);
        $connection = $server->authenticate($imap['username'], $imap['password']);
        return $connection;
    }

    public static function getMessages($email) {
        $allowed = explode(',', 'doc,docx,xls,xlsx,ppt,pptx,xps,pdf,dxf,ai,psd,eps,ps,svg,ttf,zip,rar,tar,gzip,mp3,mpeg,wav,ogg,jpeg,jpg,png,gif,bmp,tif,webm,mpeg4,3gpp,mov,avi,mpegs,wmv,flx,txt');
        $connection = TMail::connectMailBox();
        $mailbox = $connection->getMailbox('INBOX');
        $search = new SearchExpression();
        $search->addCondition(new To($email));
        $messages = $mailbox->getMessages($search, \SORTDATE, false);
        $response = [
            'data' => [],
            'notifications' => []
        ];
        foreach ($messages as $message) {
            $sender = $message->getFrom();
            $date = $message->getDate();
            $datediff = new Carbon($date);
            $content = '';
            $html = $message->getBodyHtml();
            if ($html) {
                $content = str_replace('<a', '<a target="blank"', $html);
            } else {
                $text = $message->getBodyText();
                $content = str_replace('<a', '<a target="blank"', str_replace(array("\r\n", "\n"), '<br/>', $text));
            }
            $obj = [];
            $obj['subject'] = $message->getSubject();
            $obj['sender_name'] = $sender->getName();
            $obj['sender_email'] = $sender->getAddress();
            $obj['date'] = $date->format(config('app.settings.date_format', 'd M Y h:i A'));
            $obj['datediff'] = $datediff->diffForHumans();
            $obj['id'] = $message->getNumber();
            $obj['content'] = $content;
            $obj['attachments'] = [];
            if ($message->hasAttachments()) {
                $attachments = $message->getAttachments();
                $directory = './tmp/attachments/' . $obj['id'] . '/';
                is_dir($directory) ?: mkdir($directory, 0777, true);
                foreach ($attachments as $attachment) {
                    $filenameArray = explode('.', $attachment->getFilename());
                    $extension = $filenameArray[count($filenameArray) - 1];
                    if (in_array($extension, $allowed)) {
                        if (!file_exists($directory . $attachment->getFilename())) {
                            file_put_contents(
                                $directory . $attachment->getFilename(),
                                $attachment->getDecodedContent()
                            );
                        }
                        if ($attachment->getFilename() !== 'undefined') {
                            $url = env('APP_URL') . str_replace('./', '/', $directory . $attachment->getFilename());
                            $structure = $attachment->getStructure();
                            if (isset($structure->id) && str_contains($obj['content'], trim($structure->id, '<>'))) {
                                $obj['content'] = str_replace('cid:' . trim($structure->id, '<>'), $url, $obj['content']);
                            }
                            array_push($obj['attachments'], [
                                'file' => $attachment->getFilename(),
                                'url' => $url
                            ]);
                        }
                    }
                }
            }
            array_push($response['data'], $obj);
            if (!$message->isSeen()) {
                array_push($response['notifications'], [
                    'subject' => $obj['subject'],
                    'sender_name' => $obj['sender_name'],
                    'sender_email' => $obj['sender_email']
                ]);
            }
            $message->markAsSeen();
        }
        return $response;
    }

    public static function deleteMessage($id) {
        $connection = TMail::connectMailBox();
        $mailbox = $connection->getMailbox('INBOX');
        $mailbox->getMessage($id)->delete();
        $connection->expunge();
    }

    public static function getEmail($generate = false) {
        if (Cookie::has('email')) {
            return Cookie::get('email');
        } else {
            return $generate ? TMail::generateRandomEmail() : null;
        }
    }

    public static function getEmails() {
        if (Cookie::has('emails')) {
            return unserialize(Cookie::get('emails'));
        } else {
            return [];
        }
    }

    public static function setEmail($email) {
        $emails = unserialize(Cookie::get('emails'));
        if (in_array($email, $emails)) {
            Cookie::queue('email', $email, 43800);
        }
    }

    public static function removeEmail($email) {
        $emails = TMail::getEmails();
        $key = array_search($email, $emails);
        if ($key !== false) {
            array_splice($emails, $key, 1);
        }
        if (count($emails) > 0) {
            TMail::setEmail($emails[0]);
            Cookie::queue('emails', serialize($emails), 43800);
        } else {
            Cookie::queue('email', '', -1);
            Cookie::queue('emails', serialize([]), -1);
        }
    }

    /**
     * this method is used to save emails
     */

    private static function storeEmail($email) {
        Cookie::queue('email', $email, 43800);
        $emails = Cookie::has('emails') ? unserialize(Cookie::get('emails')) : [];
        if (array_search($email, $emails) === false) {
            TMail::incrementEmailStats();
            array_push($emails, $email);
            Cookie::queue('emails', serialize($emails), 43800);
        }
    }

    public static function createCustomEmailFull($email) {
        $data = explode('@', $email);
        $username = $data[0];
        $domain = $data[1];
        return TMail::createCustomEmail($username, $domain);
    }

    public static function createCustomEmail($username, $domain) {
        $username = \str_replace('[^a-zA-Z0-9]', '', strtolower($username));
        $forbidden_ids = config('app.settings.forbidden_ids');
        if (in_array($username, $forbidden_ids)) {
            return TMail::generateRandomEmail(true);
        }
        $domains = config('app.settings.domains');
        if (in_array($domain, $domains)) {
            $email = $username . '@' . $domain;
            TMail::storeEmail($email);
            return $email;
        } else {
            $email = $username . '@' . $domains[0];
            TMail::storeEmail($email);
            return $email;
        }
    }

    /**
     * Stats Handling Functions
     */
    public static function incrementEmailStats($count = 1) {
        Meta::incrementEmailIdsCreated($count);
    }

    public static function incrementMessagesStats($count = 1) {
        Meta::incrementMessagesReceived($count);
    }

    public static function generateRandomEmail($store = true) {
        $tmail = new TMail;
        $email = $tmail->generateRandomUsername() . '@' . $tmail->getRandomDomain();
        if ($store) {
            TMail::storeEmail($email);
        }
        return $email;
    }

    private function generateRandomUsername() {
        $start = config('app.settings.random.start', 0);
        $end = config('app.settings.random.end', 0);
        if ($start == 0 && $end == 0) {
            return $this->generatePronounceableWord();
        }
        return $this->generatedRandomBetweenLength($start, $end);
    }

    protected function generatedRandomBetweenLength($start, $end) {
        $length = rand($start, $end);
        return $this->generateRandomString($length);
    }

    private function getRandomDomain() {
        $domains = config('app.settings.domains');
        $count = count($domains);
        return $count > 0 ? $domains[rand(1, $count) - 1] : '';
    }

    private function generatePronounceableWord() {
        $c  = 'bcdfghjklmnprstvwz'; //consonants except hard to speak ones
        $v  = 'aeiou';              //vowels
        $a  = $c . $v;                //both
        $random = '';
        for ($j = 0; $j < 2; $j++) {
            $random .= $c[rand(0, strlen($c) - 1)];
            $random .= $v[rand(0, strlen($v) - 1)];
            $random .= $a[rand(0, strlen($a) - 1)];
        }
        return $random;
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
