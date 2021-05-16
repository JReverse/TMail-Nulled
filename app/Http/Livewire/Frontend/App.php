<?php

namespace App\Http\Livewire\Frontend;

use Livewire\Component;
use App\Models\TMail;

class App extends Component {

    public $messages = [];
    public $error = '';
    public $email;
    public $initial;

    protected $listeners = ['fetchMessages' => 'fetch', 'syncEmail'];

    public function mount() {
        $this->email = TMail::getEmail();
        $this->initial = false;
    }

    public function syncEmail($email) {
        $this->email = $email;
    }

    public function fetch() {
        try {
            $response = TMail::getMessages($this->email);
            $this->messages = $response['data'];
            $notifications = $response['notifications'];
            foreach ($notifications as $notification) {
                $this->dispatchBrowserEvent('showNewMailNotification', $notification);
            }
            TMail::incrementMessagesStats(count($notifications));
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
        $this->dispatchBrowserEvent('stopLoader');
        $this->dispatchBrowserEvent('loadDownload');
        $this->initial = true;
    }

    public function delete($messageId) {
        TMail::deleteMessage($messageId);
        foreach ($this->messages as $key => $message) {
            if ($message['id'] == $messageId) {
                $directory = './tmp/attachments/' . $messageId;
                $this->rrmdir($directory);
                unset($this->messages[$key]);
            }
        }
    }

    public function render() {
        return view('themes.' . config('app.settings.theme') . '.components.app');
    }

    private function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                        $this->rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    else
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
            rmdir($dir);
        }
    }
}
