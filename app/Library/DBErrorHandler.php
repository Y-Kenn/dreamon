<?php

namespace App\Library;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

//DB処理時のエラーハンドリング
class DBErrorHandler
{
    //findで取得した戻り値をチェック
    static function checkFound($result){
        if(!isset($result['created_at'])){
            throw new \Exception('[thrown by DBErrorHandler] No record was found.');
        }
    }

    //create()の戻り値をチェック
    static function checkCreated($result){
        if(!isset($result['created_at'])){
            throw new \Exception('[thrown by DBErrorHandler] No record was created.');
        }
    }

    //update()の戻り値をチェック
    static function checkUpdated($result){
        if(!$result){
            throw new \Exception('[thrown by DBErrorHandler] No record was updated.');
        }
    }

    //delete()の戻り値をチェック
    static function checkDeleted($result){
        if(!$result){
            throw new \Exception('[thrown by DBErrorHandler] No record was deleted.');
        }
    }
}
