<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\TMail;
use Carbon\Carbon;
use Ddeboer\Imap\Search\Date\Before;

class AppController extends Controller {

    public function load() {
        if (file_exists(public_path('themes')) !== TRUE) {
            symlink(base_path('resources/views/themes'), public_path('themes'));
        }
        $homepage = config('app.settings.homepage');
        if ($homepage == 0) {
            TMail::getEmail(true);
            return redirect()->route('app');
        } else {
            $page = Page::findOrFail($homepage);
            return view('themes.' . config('app.settings.theme') . '.app')->with(compact('page'));
        }
    }

    public function app($email = null) {
        if ($email) {
            if (config('app.settings.enable_create_from_url')) {
                TMail::createCustomEmailFull($email);
            }
            return redirect()->route('app');
        }
        if (config('app.settings.theme') == 'groot' && config('app.settings.groot_theme_options.extra_text_page')) {
            $in_page = Page::find(config('app.settings.groot_theme_options.extra_text_page'));
            return view('themes.' . config('app.settings.theme') . '.app')->with(compact('in_page'));
        }
        return view('themes.' . config('app.settings.theme') . '.app');
    }

    public function page($slug = '', $inner = '') {
        if ($inner) {
            $parent = Page::select('id')->where('slug', $slug)->first();
            if ($parent) {
                $parent_id = $parent->id;
            } else {
                return abort(404);
            }
        }
        $page = Page::where('slug', ($inner) ? $inner : $slug)->where('parent_id', isset($parent_id) ? $parent_id : null)->first();
        if ($page) {
            $header = $page->header;
            foreach ($page->meta ? unserialize($page->meta) : [] as $meta) {
                if ($meta['name'] == 'canonical') {
                    $header .= '<link rel="canonical" href="' . $meta['content'] . '" />';
                } else if (str_contains($meta['name'], 'og:')) {
                    $header .= '<meta property="' . $meta['name'] . '" content="' . $meta['content'] . '" />';
                } else {
                    $header .= '<meta name="' . $meta['name'] . '" content="' . $meta['content'] . '" />';
                }
            }
            $page->header = $header;
            return view('themes.' . config('app.settings.theme') . '.app')->with(compact('page'));
        }
        return abort(404);
    }

    public function switch($email) {
        TMail::setEmail($email);
        return redirect()->route('app');
    }

    public function locale($locale) {
        if (in_array($locale, config('app.locales'))) {
            session(['locale' => $locale]);
            return redirect()->back();
        }
        abort(400);
    }

    public function cron($password) {
        if ($password == config('app.settings.cron_password')) {
            $before = null;
            if (config('app.settings.delete.key') == 'd') {
                $before = Carbon::now()->subDays(config('app.settings.delete.value'));
            } else if (config('app.settings.delete.key') == 'w') {
                $before = Carbon::now()->subWeeks(config('app.settings.delete.value'));
            } else {
                $before = Carbon::now()->subMonths(config('app.settings.delete.value'));
            }
            $limit = 50;
            $today = new \DateTimeImmutable($before);
            $connection = TMail::connectMailBox();
            $mailbox = $connection->getMailbox('INBOX');
            $messages = $mailbox->getMessages(new Before($today));
            $count = 0;
            foreach ($messages as $message) {
                $message->delete();
                $count++;
                if ($count >= $limit) {
                    break;
                }
            }
            $connection->expunge();
            $directory = './tmp/attachments/';
            $this->rrmdir($directory);
            return "Deleted " . $count . " Messages";
        } else {
            return abort(401);
        }
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
