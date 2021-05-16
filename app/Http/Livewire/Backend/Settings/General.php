<?php

namespace App\Http\Livewire\Backend\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Setting;
use App\Models\Page;
use Illuminate\Support\Facades\Storage;

class General extends Component {

    use WithFileUploads;

    /**
     * Components State
     */
    public $state = [
        'name' => '',
        'pages' => [],
        'homepage' => 0,
        'colors' => [
            'primary' => '#000000',
            'secondary' => '#000000',
            'tertiary' => '#000000'
        ],
        'cookie' => [],
        'custom_logo' => '',
        'custom_favicon' => '',
        'language' => 'en',
        'enable_create_from_url' => false
    ];

    public $logo, $favicon;

    public function mount() {
        $pages = Page::where('parent_id', null)->get();
        foreach ($pages as $page) {
            $this->state['pages'][$page->id] = $page->title;
        }
        $this->state['name'] = config('app.settings.name');
        $this->state['homepage'] = config('app.settings.homepage');
        $this->state['colors'] = config('app.settings.colors');
        $this->state['cookie'] = config('app.settings.cookie');
        $this->state['language'] = config('app.settings.language');
        $this->state['enable_create_from_url'] = config('app.settings.enable_create_from_url');
        if (Storage::exists('public/images/custom-logo.png')) {
            $this->state['custom_logo'] = Storage::url('public/images/custom-logo.png');
        }
        if (Storage::exists('public/images/custom-favicon.png')) {
            $this->state['custom_favicon'] = Storage::url('public/images/custom-favicon.png');
        }
    }

    public function update() {
        $this->validate(
            [
                'state.name' => 'required',
                'state.logo' => 'image|max:1024',
                'state.favicon' => 'image|max:1024'
            ],
            [
                'state.name.required' => 'App Name is Required',
                'state.logo.image' => 'Invalid Logo file',
                'state.logo.max' => 'Max Size is 1MB',
                'state.favicon.image' => 'Invalid Logo file',
                'state.favicon.max' => 'Max Size is 1MB'
            ]
        );
        if ($this->logo) {
            $this->logo->storeAs('public/images', 'custom-logo.png');
        }
        if ($this->favicon) {
            $this->favicon->storeAs('public/images', 'custom-favicon.png');
        }
        $settings = Setting::whereIn('key', ['name', 'homepage', 'colors', 'cookie', 'language', 'enable_create_from_url'])->get();
        foreach ($settings as $setting) {
            $setting->value = serialize($this->state[$setting->key]);
            $setting->save();
        }
        $this->emit('saved');
    }

    public function render() {
        return view('backend.settings.general');
    }
}
