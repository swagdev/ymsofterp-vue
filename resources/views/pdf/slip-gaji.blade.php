<!DOCTYPE html>
<html>
<head>
  <style>
    body { font-family: sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
    td, th { padding: 6px 8px; border: 1px solid #ddd; }
    .bold { font-weight: bold; }
    .text-right { text-align: right; }
    .no-border td { border: none; }
  </style>
</head>
<body>

  <table class="no-border">
    <tr>
      <td><img src="{{ public_path('images/logo.png') }}" width="100"></td>
      <td class="text-right">
        <h3>SLIP GAJI</h3>
        <strong>PRIBADI DAN RAHASIA</strong>
      </td>
    </tr>
  </table>

  <table class="no-border">
    <tr>
      <td>Bulan</td><td>: {{ $bulan_tahun }}</td>
      <td>No. Induk Karyawan</td><td>: {{ $user->nik }}</td>
    </tr>
    <tr>
      <td>Nama</td><td>: {{ $user->nama_lengkap }}</td>
      <td>Level & Jabatan</td><td>: {{ $user->hasjabatan->nama_jabatan ?? '-' }}</td>
    </tr>
    <tr>
      <td>Unit & Divisi</td><td>: {{ $user->hasoutlet->nama_outlet ?? '-' }} - {{ $user->divisi->nama_divisi ?? '-' }}</td>
      <td>Pembayaran</td><td>: BCA #{{ $user->no_rekening }}</td>
    </tr>
  </table>

  <table class="no-border">
    <tr><td>Saldo Cuti</td><td>: {{ $user->saldo_cuti ?? 0 }} Hari</td></tr>
    <tr><td>Saldo Ph</td><td>: {{ $user->saldo_ph ?? 0 }} Hari</td></tr>
  </table>

  <h4>PENGHASILAN</h4>
  <table>
    <tr><td>Gaji Pokok</td><td class="text-right">{{ number_format($user->tgajisatuan->gaji ?? 0) }}</td></tr>
    <tr><td>Tunjangan Jabatan</td><td class="text-right">{{ number_format($user->tgajisatuan->tunjangan_jabatan ?? 0) }}</td></tr>
    <tr><td>Tunjangan Makan 6 hari</td><td class="text-right">0</td></tr>
    <tr><td>Tunjangan Lembur 1 jam</td><td class="text-right">0</td></tr>
    <tr class="bold"><td>TOTAL PENGHASILAN</td><td class="text-right">{{ number_format($user->total_gaji) }}</td></tr>
  </table>

  <h4>POTONGAN</h4>
  <table>
    <tr><td>BPJS Tenaga Kerja #{{ $user->no_bpjs_tk }}</td><td class="text-right">{{ number_format($user->tgajisatuan->bpjs_tk ?? 0) }}</td></tr>
    <tr><td>Alpha {{ $user->alpa_kerja_factor }} hari</td><td class="text-right">{{ number_format($user->jumlah_alpa_kerja); }}</td></tr>
    <tr><td>Telat {{ $user->total_telat }} kali</td><td class="text-right">{{ number_format($user->jumlah_telat) }}</td></tr>
    {{-- <tr><td>Kelebihan Istirahat 0 jam</td><td class="text-right">0</td></tr> --}}
    <tr class="bold"><td>TOTAL POTONGAN</td><td class="text-right">{{ number_format($user->total_potongan) }}</td></tr>
  </table>

  <h4>PENGHASILAN BERSIH</h4>
  <h2 class="text-right">{{ number_format($user->gaji_bersih) }}</h2>

</body>
</html>
