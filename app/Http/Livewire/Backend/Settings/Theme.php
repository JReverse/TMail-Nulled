<?php

namespace App\Http\Livewire\Backend\Settings;

use App\Models\Page;
use Livewire\Component;
use App\Models\Setting;

class Theme extends Component {

    /**
     * Components State
     */
    public $state = [
        'groot' => [
            'extra_text_page' => '',
        ],
        'pages' => []
    ];

    public function mount() {
        if (config('app.settings.theme') == 'groot') {
            $pages = Page::where('parent_id', null)->get();
            foreach ($pages as $page) {
                $this->state['pages'][$page->id] = $page->title;
            }
        }
        $this->state['groot'] = config('app.settings.groot_theme_options');
    }

    public function update() {
        $setting = Setting::where('key', 'groot_theme_options')->first();
        $setting->value = serialize($this->state['groot']);
        $setting->save();
        $this->emit('saved');
    }

    public function render() {
        return view('backend.settings.theme');
    }
}
