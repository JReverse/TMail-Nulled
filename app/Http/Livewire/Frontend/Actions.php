<?php

namespace App\Http\Livewire\Frontend;

use Livewire\Component;
use App\Models\TMail;

class Actions extends Component {

    public $in_app = false;
    public $user, $domain, $domains, $email, $emails;

    protected $listeners = ['syncEmail'];

    public function mount() {
        $this->domains = config('app.settings.domains');
        $this->email = TMail::getEmail();
        $this->emails = TMail::getEmails();
        $this->validateDomainInEmail();
    }

    public function syncEmail($email) {
        $this->email = $email;
        if (count($this->emails) == 0) {
            $this->emails = [$email];
        }
    }

    public function setDomain($domain) {
        $this->domain = $domain;
    }

    public function create() {
        if (!$this->user) {
            return $this->showAlert('error', __('Please enter Username'));
        }
        $this->checkDomainInUsername();
        if (!$this->domain) {
            return $this->showAlert('error', __('Please Select a Domain'));
        }
        if (in_array($this->user, config('app.settings.forbidden_ids'))) {
            return $this->showAlert('error', __('Username not allowed'));
        }
        $this->email = TMail::createCustomEmail($this->user, $this->domain);
        $this->redirect(route('app'));
    }

    public function random() {
        $this->email = TMail::generateRandomEmail();
        $this->redirect(route('app'));
    }

    public function deleteEmail() {
        TMail::removeEmail($this->email);
        if (count($this->emails) == 1 && config('app.settings.after_last_email_delete') == 'redirect_to_homepage') {
            return redirect()->route('home');
        }
        $this->email = TMail::getEmail(true);
        $this->emails = TMail::getEmails();
        return redirect()->route('app');
    }

    public function render() {
        return view('themes.' . config('app.settings.theme') . '.components.actions');
    }

    /**
     * Private Functions
     */

    private function showAlert($type, $message) {
        $this->dispatchBrowserEvent('showAlert', ['type' => $type, 'message' => $message]);
    }

    /**
     * Check if Username already consist of Domain
     */
    private function checkDomainInUsername() {
        $parts = explode('@', $this->user);
        if (isset($parts[1])) {
            if (in_array($parts[1], $this->domains)) {
                $this->domain = $parts[1];
            }
            $this->user = $parts[0];
        }
    }

    /**
     * Validate if Domain in Email Exist
     */
    private function validateDomainInEmail() {
        $data = explode('@', $this->email);
        if (isset($data[1])) {
            $domain = $data[1];
            $domains = config('app.settings.domains');
            if (!in_array($domain, $domains)) {
                $key = array_search($this->email, $this->emails);
                TMail::removeEmail($this->email);
                if ($key == 0 && count($this->emails) == 1 && config('app.settings.after_last_email_delete') == 'redirect_to_homepage') {
                    return redirect()->route('home');
                } else {
                    return redirect()->route('app');
                }
            }
        }
    }
}
