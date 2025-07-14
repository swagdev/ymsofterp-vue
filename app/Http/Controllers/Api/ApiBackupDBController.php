<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ApiBackupDBController extends Controller
{
  public function index(Request $request)
  {
    ini_set('disable_functions', '');
    // Tentukan nama file untuk backup
    $fileName = 'backup-' . date('Y-m-d_H-i-s') . '.sql';

    // Path ke folder 'backups' di dalam folder 'public'
    $backupDir = public_path('backups');

    // Cek apakah folder backups sudah ada, jika belum buat foldernya
    if (!is_dir($backupDir)) {
      mkdir($backupDir, 0755, true); // Buat folder dengan permission 755
    }

    // Full path untuk file backup
    $filePath = $backupDir . '/' . $fileName;

    // Komando mysqldump
    $command = "mysqldump --user=" . env('DB_USERNAME') .
      " --password=" . env('DB_PASSWORD') .
      " --host=" . env('DB_HOST') .
      " " . env('DB_DATABASE') . " > " . $filePath;

    // Eksekusi command
    $result = null;
    $output = null;
    exec($command, $output, $result);

    if ($result == 0) {
      return response()->json(['message' => 'Backup berhasil disimpan di ' . $filePath], 200);
    } else {
      return response()->json(['message' => 'Backup gagal'], 500);
    }
  }
}
