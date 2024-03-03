<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MTRLineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('mtr_info')->insert([
            'line_id' => 'TML',
            'line_name_tc' => '屯馬綫',
            'line_name_en' => 'Tuen Ma Line',
            'line_color' => '#9a3b26',
        ]);

        DB::table('mtr_info')->insert([
            'line_id' => 'EAL',
            'line_name_tc' => '東鐵綫',
            'line_name_en' => 'East Rail Line',
            'line_color' => '#53b7e8',
        ]);

        DB::table('mtr_info')->insert([
            'line_id' => 'TWL',
            'line_name_tc' => '荃灣綫',
            'line_name_en' => 'Tsuen Wan Line',
            'line_color' => '#ff0000',
        ]);

        DB::table('mtr_info')->insert([
            'line_id' => 'KTL',
            'line_name_tc' => '觀塘綫',
            'line_name_en' => 'Kwun Tong Line',
            'line_color' => '#1a9431',
        ]);

        DB::table('mtr_info')->insert([
            'line_id' => 'TKL',
            'line_name_tc' => '將軍澳綫',
            'line_name_en' => 'Tseung Kwan O Line',
            'line_color' => '#6b208b',
        ]);

        DB::table('mtr_info')->insert([
            'line_id' => 'TCL',
            'line_name_tc' => '東涌綫',
            'line_name_en' => 'Tung Chung Line',
            'line_color' => '#fe7f1d',
        ]);

        DB::table('mtr_info')->insert([
            'line_id' => 'SIL',
            'line_name_tc' => '南港島綫',
            'line_name_en' => 'South Island Line',
            'line_color' => '#b5bd00',
        ]);

        DB::table('mtr_info')->insert([
            'line_id' => 'ISL',
            'line_name_tc' => '港島綫',
            'line_name_en' => 'Island Line',
            'line_color' => '#0860a8',
        ]);

        DB::table('mtr_info')->insert([
            'line_id' => 'AEL',
            'line_name_tc' => '機場快綫',
            'line_name_en' => 'Airport Express',
            'line_color' => '#1c7670',
        ]);

        DB::table('mtr_info')->insert([
            'line_id' => 'DRL',
            'line_name_tc' => '迪士尼綫',
            'line_name_en' => 'Disneyland Resort Line',
            'line_color' => '#f550a6',
        ]);
    }
}
