<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProfilesImport implements ToCollection, WithHeadingRow
{
    /**
     * @param  Collection  $sheets
     * @return Collection
     */
    public function collection(Collection $sheets)
    {
        return $sheets[0];
    }
}
