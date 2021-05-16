<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\TMail;
use Exception;

class APIController extends Controller {

    public function domains($key = '') {
        $keys = Setting::pick('api_keys');
        if (in_array($key, $keys)) {
            return Setting::pick('domains');
        } else {
            return abort(401);
        }
    }

    public function email($email = '', $key = '') {
        $keys = Setting::pick('api_keys');
        if (in_array($key, $keys)) {
            if ($email) {
                try {
                    $split = explode('@', $email);
                    return TMail::createCustomEmail($split[0], $split[1]);
                } catch (Exception $e) {
                    return TMail::generateRandomEmail(false);
                }
            } else {
                return TMail::generateRandomEmail(false);
            }
        } else {
            return abort(401);
        }
    }

    public function messages($email = '', $key = '') {
        $keys = Setting::pick('api_keys');
        if (in_array($key, $keys)) {
            if ($email) {
                try {
                    $data = [];
                    $response = TMail::getMessages($email);
                    $data = $response['data'];
                    TMail::incrementMessagesStats(count($response['notifications']));
                    return $data;
                } catch (\Exception $e) {
                    return abort(500);
                }
            } else {
                return abort(204);
            }
        } else {
            return abort(401);
        }
    }
}
