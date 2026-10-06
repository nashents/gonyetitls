<?php

namespace App\Http\Controllers;

use App\Models\DataBackup;
use Illuminate\Support\Facades\Auth;

class DataBackupsController extends Controller
{
    public function download(DataBackup $dataBackup)
    {
        abort_unless(DataBackup::userCanManage(Auth::user()), 403);
        abort_unless($dataBackup->isDownloadable(), 404);

        $name = str_replace(' ', '_', config('app.name')).'_backup_'.$dataBackup->created_at->format('Y-m-d_His').'.zip';

        return response()->download($dataBackup->path, $name);
    }
}
