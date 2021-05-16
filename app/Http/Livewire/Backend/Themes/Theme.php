<?php

namespace App\Http\Livewire\Backend\Themes;

use Livewire\Component;
use Livewire\WithFileUploads;
use DirectoryIterator;
use ZipArchive;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class Theme extends Component {

    use WithFileUploads;

    public $themes, $current, $new, $error;

    public function mount() {
        $directory = new DirectoryIterator(base_path('resources/views/themes'));
        $this->themes = [];
        foreach ($directory as $file) {
            if ($file->isDot()) continue;
            if ($file->isFile()) continue;
            array_push($this->themes, $file->getFilename());
        }
        $this->current = Setting::pick('theme');
        $this->error = null;
    }

    public function set($theme) {
        if (in_array($theme, $this->themes)) {
            Setting::put('theme', $theme);
            $this->current = Setting::pick('theme');
        }
    }

    public function delete($theme) {
        Storage::disk('themes')->deleteDirectory($theme);
        $this->mount();
    }

    public function upload() {
        if ($this->new) {
            $filename = Storage::disk('tmp')->put('/', $this->new);
            $this->handle('tmp/' . $filename);
            Storage::disk('tmp')->delete($filename);
        } else {
            $this->error = 'Please upload a Theme ZIP file';
        }
    }

    private function handle($filename) {
        $file = new ZipArchive;
        $verify = [
            'app.blade.php',
            'components/actions.blade.php',
            'components/app.blade.php',
            'components/nav.blade.php',
            'components/page.blade.php'
        ];
        if ($file->open($filename) !== TRUE) {
            $this->error = 'Looks like the Theme ZIP file is corrupted';
            return false;
        } else {
            $folder = '';
            for ($i = 0; $i < $file->numFiles; $i++) {
                $item = $file->getNameIndex($i);
                if ($folder === '') {
                    if ($item[strlen($item) - 1] === '/') {
                        $folder = substr($item, 0, -1);
                    }
                } else {
                    $key = array_search(str_replace($folder . '/', '', $item), $verify);
                    if ($key !== FALSE) {
                        unset($verify[$key]);
                    }
                }
            }
            if (count($verify) > 0) {
                $this->error = 'Incompleted Theme Files. Your ZIP is missing below file(s)<br><br>';
                foreach ($verify as $item) {
                    $this->error .= '/' . $item . '<br>';
                }
                return false;
            } else {
                $file->extractTo('themes');
                $this->new = null;
                $this->mount();
            }
        }
    }

    public function render() {
        return view('backend.themes.theme');
    }
}
