<?php

namespace App\Backend\PDDIKTI;

use Exception;

/**
 * Enterprise PDDIKTI Neo Feeder Integration Service
 * Prepares strict Kemendikbudristek REST/JSON payloads for National Higher Education Reporting:
 * - AKM (Aktivitas Kuliah Mahasiswa)
 * - Nilai Perkuliahan Kelas (KHS)
 * - Mahasiswa Baru (PMB)
 * - Dosen Pengajar Kelas Kuliah
 * - Kelulusan & Transkrip Akhir (Yudisium)
 */
class PDDIKTINeoFeederSyncService
{
    /**
     * Build PDDIKTI Neo Feeder JSON payload for Student Activity & KRS (Aktivitas Kuliah Mahasiswa / AKM).
     */
    public static function buildAKMPayload(
        string $idSemester,
        string $nim,
        string $namaMahasiswa,
        string $kodeProdi,
        float $ips,
        float $ipk,
        int $sksSemester,
        int $sksTotal,
        string $statusKuliah = 'A' // A = Aktif, C = Cuti, N = Non-aktif
    ): array {
        return [
            'act' => 'InsertPerkuliahanMahasiswa',
            'token' => 'BEARER_FEF4839840294802_SYNC',
            'record' => [
                'id_semester' => $idSemester,
                'nim' => $nim,
                'nama_mahasiswa' => $namaMahasiswa,
                'id_prodi' => $kodeProdi,
                'id_status_mahasiswa' => $statusKuliah,
                'ips' => number_format($ips, 2, '.', ''),
                'ipk' => number_format($ipk, 2, '.', ''),
                'sks_semester' => $sksSemester,
                'total_sks' => $sksTotal
            ]
        ];
    }

    /**
     * Build PDDIKTI Neo Feeder JSON payload for Final Grades (Nilai Perkuliahan).
     */
    public static function buildNilaiPayload(
        string $idKelasKuliah,
        string $nim,
        float $nilaiAngka,
        string $nilaiHuruf,
        float $nilaiIndeks
    ): array {
        return [
            'act' => 'InsertNilaiPerkuliahanKelas',
            'token' => 'BEARER_FEF4839840294802_SYNC',
            'record' => [
                'id_kelas_kuliah' => $idKelasKuliah,
                'nim' => $nim,
                'nilai_angka' => number_format($nilaiAngka, 2, '.', ''),
                'nilai_huruf' => $nilaiHuruf,
                'nilai_indeks' => number_format($nilaiIndeks, 2, '.', '')
            ]
        ];
    }

    /**
     * Build PDDIKTI Neo Feeder JSON payload for Lecturer Assignment (Dosen Pengajar Kelas Kuliah).
     */
    public static function buildDosenAjarPayload(
        string $idKelasKuliah,
        string $nidn,
        string $namaDosen,
        float $sksSubstansi,
        int $rencanaPertemuan = 16,
        int $realisasiPertemuan = 16
    ): array {
        return [
            'act' => 'InsertDosenPengajarKelasKuliah',
            'token' => 'BEARER_FEF4839840294802_SYNC',
            'record' => [
                'id_registrasi_dosen' => $nidn,
                'id_kelas_kuliah'     => $idKelasKuliah,
                'sks_substansi_total' => $sksSubstansi,
                'rencana_minggu_pertemuan' => $rencanaPertemuan,
                'realisasi_minggu_pertemuan' => $realisasiPertemuan,
                'id_jenis_evaluasi'   => '1' // Evaluasi Standar
            ]
        ];
    }

    /**
     * Build PDDIKTI Neo Feeder JSON payload for Graduation & Degree Conferral (Mahasiswa Lulus / Keluar).
     */
    public static function buildKelulusanPayload(
        string $nim,
        string $noIjazahNasional,
        string $noSkYudisium,
        string $tglSkYudisium,
        float $ipkAkhir,
        string $judulSkripsi
    ): array {
        return [
            'act' => 'InsertMahasiswaLulusDropOut',
            'token' => 'BEARER_FEF4839840294802_SYNC',
            'record' => [
                'nim'                  => $nim,
                'id_jenis_keluar'      => '1', // 1 = Lulus
                'tanggal_keluar'       => $tglSkYudisium,
                'nomor_sk_yudisium'    => $noSkYudisium,
                'nomor_ijazah'         => $noIjazahNasional,
                'ipk'                  => number_format($ipkAkhir, 2, '.', ''),
                'judul_skripsi'        => $judulSkripsi
            ]
        ];
    }
}
