<?php

namespace App\Http\Livewire\Backend\Settings;

use Livewire\Component;
use App\Models\Setting;

class Configuration extends Component {

    /**
     * Components State
     */
    public $state = [
        'domains' => [],
        'fetch_seconds' => 0,
        'forbidden_ids' => [],
        'cron_password' => '',
        'delete' => [],
        'random' => [],
        'after_last_email_delete' => 'redirect_to_homepage',
        'date_format' => 'd M Y h:i A'
    ];

    public function mount() {
        foreach ($this->state as $key => $props) {
            $this->state[$key] = config('app.settings')[$key];
        }
        if ($this->state['random']['start'] || $this->state['random']['end']) {
            $this->state['advance_random'] = true;
        } else {
            $this->state['advance_random'] = false;
        }
    }

    public function add($type = 'domains') {
        $this->resetErrorBag();
        array_push($this->state[$type], '');
    }

    public function remove($type = 'domains', $key) {
        unset($this->state[$type][$key]);
        $this->state[$type] = array_values($this->state[$type]);
    }

    public function update() {
        $this->validate(
            [
                'state.domains.0' => 'required',
                'state.domains.*' => 'required',
                'state.forbidden_ids.*' => 'required',
                'state.fetch_seconds' => 'required|numeric',
                'state.cron_password' => 'required',
                'state.delete.value' => 'required|numeric',
                'state.random.end' => 'gte:' . $this->state['random']['start'],
                'state.date_format' => 'required'
            ],
            [
                'state.domains.0.required' => 'Atleast one Domain is Required',
                'state.domains.*.required' => 'Domain field is Required',
                'state.forbidden_ids.*.required' => 'Forbidden ID field is Required',
                'state.fetch_seconds.required' => 'Fetch Seconds field is Required',
                'state.fetch_seconds.numeric' => 'Fetch Seconds field can only be Numeric',
                'state.cron_password.required' => 'CRON Password field is Required',
                'state.delete.value.required' => 'Delete Value field is Required',
                'state.delete.value.numeric' => 'Delete Value field can only be Numeric',
                'state.random.end.gte' => 'Random End must be greater than or equal to ' . $this->state['random']['start'],
                'state.date_format.required' => 'Date Format field is Required'
            ]
        );
        if (!$this->state['advance_random']) {
            $this->state['random']['start'] = 0;
            $this->state['random']['end'] = 0;
        }
        $settings = Setting::whereIn('key', ['domains', 'fetch_seconds', 'forbidden_ids', 'cron_password', 'delete', 'random', 'after_last_email_delete', 'date_format'])->get();
        foreach ($settings as $setting) {
            $setting->value = serialize($this->state[$setting->key]);
            $setting->save();
        }
        $this->emit('saved');
    }

    public function render() {
        return view('backend.settings.configuration');
    }
}
