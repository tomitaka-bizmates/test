<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FoldersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * foldersTable用テストデータ
     *
     * @return void
     */
    public function run()
    {

           // first()：レコードを1行取得する関数
           $user = DB::table('users')->first();

        $titles = ['プライベート', '仕事', '旅行','英語の勉強','読書','研究'];

        foreach ($titles as $title) {
            DB::table('folders')->insert([
                'title' => $title,
                'user_id'=>$user->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}