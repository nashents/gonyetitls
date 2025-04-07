<?php

namespace Database\Seeders;

use App\Models\HorseMake;
use App\Models\HorseModel;
use Illuminate\Database\Seeder;

class HorseModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $freightliner = HorseMake::where('name','Freightliner')->get()->first();
        $volvo = HorseMake::where('name','Volvo')->get()->first();
        $mercedes = HorseMake::where('name','Mercedes')->get()->first();
        $shacman = HorseMake::where('name','Shacman')->get()->first();
        $man = HorseMake::where('name','Man')->get()->first();
        $iveco = HorseMake::where('name','Iveco')->get()->first();
        $foton = HorseMake::where('name','Foton')->get()->first();
        $scania = HorseMake::where('name','Scania')->get()->first();

        $horse_models = [
            ['horse_make_id' => $freightliner ? $freightliner->id : "" , 'name' => "Argosy" ],
            ['horse_make_id' => $freightliner ? $freightliner->id : "", 'name' => "Cascadia" ],
            ['horse_make_id' => $freightliner ? $freightliner->id : "", 'name' => "Colombia" ],
            ['horse_make_id' => $freightliner ? $freightliner->id : "", 'name' => "FLD" ],
            ['horse_make_id' => $volvo ? $volvo->id : "", 'name' => "FH 440 - GENERATION 3" ],
            ['horse_make_id' => $volvo ? $volvo->id : "", 'name' => "FH 440 - GENERATION 4" ],
            ['horse_make_id' => $volvo ? $volvo->id : "", 'name' => "FH 460" ],
            ['horse_make_id' => $volvo ? $volvo->id : "", 'name' => "FH 500" ],
            ['horse_make_id' => $volvo ? $volvo->id : "", 'name' => "FH 520" ],
            ['horse_make_id' => $mercedes ? $mercedes->id : "", 'name' => "Actros" ],
            ['horse_make_id' => $shacman ? $shacman->id : "", 'name' => "H3000" ],
            ['horse_make_id' => $shacman ? $shacman->id : "", 'name' => "X3000" ],
            ['horse_make_id' => $scania ? $scania->id : "", 'name' => "G420" ],
            ['horse_make_id' => $scania ? $scania->id : "", 'name' => "G460" ],
            ['horse_make_id' => $scania ? $scania->id : "", 'name' => "R470" ],
            ['horse_make_id' => $scania ? $scania->id : "", 'name' => "R420" ],
        ];
        HorseModel::insert($horse_models);
    }
}
