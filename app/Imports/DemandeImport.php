<?php

namespace App\Imports;
use DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DemandeImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach( $rows as $row){
            // if($row->getIndex() <= 8){
            //     return null;
            // }
            DB::table('demande_sectionnable')->where('id', $row[0])->update([
                'offre' => $row[3],
                'quantite_offerte' => $row[4]
            ]);
        }
    }
}
