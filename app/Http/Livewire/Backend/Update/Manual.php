<?php

namespace App\Http\Livewire\Backend\Update;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Exception;

class Manual extends Component {

    use WithFileUploads;

    public $error = '';
    public $filename;
    public $progress = '';

    protected $listeners = ['manual' => 'apply'];

    public function apply($step = 0) {
        $this->error = '';
        if ($step === 0) {
            $file = new ZipArchive;
            if ($file->open('tmp/' . $this->filename) !== TRUE) {
                $this->error = 'Looks like the ZIP file is corrupted or does not exist.';
                return false;
            } else {
                $this->progress .= '<div class="text-green-500">Extracting Files</div>';
                $file->extractTo(base_path());
                for ($i = 0; $i < $file->numFiles; $i++) {
                    $item = $file->getNameIndex($i);
                    $this->progress .= '<div class="text-white">/' . $item . '</div>';
                }
                Storage::disk('tmp')->delete($this->filename);
                $this->emit('manual', 1);
            }
        } else if ($step === 1) {
            $this->progress .= '<div class="text-green-500">Files Received and Updated Successfully</div>';
            $this->progress .= '<div class="text-white">Preparing Database Changes</div>';
            $this->emit('manual', 2);
        } else if ($step === 2) {
            try {
                Artisan::call('migrate', ["--force" => true]);
                Artisan::call('db:seed', ["--force" => true]);
                $this->progress .= '<div class="text-green-500">Database Changes Completed Successfully</div>';
                $this->progress .= '<div class="text-white">Updating Available Vendor Files</div>';
                $this->emit('manual', 3);
            } catch (Exception $e) {
                Artisan::call('migrate:rollback', ["--step" => 1]);
                $this->progress .= '<div class="text-red-600">Encountered Error' . $e->getMessage() . '</div>';
            }
        } else if ($step === 3) {
            try {
                if (file_exists(base_path() . '/vendor_new')) {
                    File::deleteDirectory(base_path('vendor'));
                    rename(base_path('vendor_new'), base_path('vendor'));
                }
                $this->progress .= '<br><div class="text-green-500 font-bold">Patch Applied Successfully</div>';
            } catch (Exception $e) {
                $this->progress .= '<div class="text-red-600">Encountered Error' . $e->getMessage() . '</div>';
            }
        }
    }

    public function render() {
        return view('backend.update.manual');
    }
}
