<?php
/**
 * File: proxy-penjual.php
 * Deskripsi: File ini disimpan di server PENJUAL untuk bertindak sebagai perantara yang aman.
 * File ini menerima request dari plugin pembeli, menambahkan API Key rahasia, lalu meneruskannya ke Pasdigi.
 */

header('Content-Type: application/json');

// Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => ['code' => 'method_not_allowed', 'message' => 'Method Not Allowed']]);
    exit();
}

// Ambil data JSON dari request plugin pembeli
$input_data = json_decode(file_get_contents('php://input'), true);

$license_key = $input_data['license_key'] ?? null;
$product_id = $input_data['product_id'] ?? null;
$domain = $input_data['domain'] ?? null;

// Validasi input dasar
if (empty($license_key) || empty($product_id) || empty($domain)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => ['code' => 'bad_request', 'message' => 'Parameter tidak lengkap: license_key, product_id, dan domain wajib diisi.']]);
    exit();
}

// ===================================================================
// BAGIAN AMAN: Komunikasi Server-ke-Server dengan Pasdigi
// ===================================================================

// GANTI DENGAN API KEY RAHASIA TOKO ANDA DARI PASDIGI
$pasdigi_api_key = 'pasd_sk_xxxxxxxxxxxxxxxxxxxxxxxxxx'; 
$pasdigi_api_url = 'https://pasdigi.com/api/v1/license/validate';

$data_to_pasdigi = [
    'license_key' => $license_key,
    'product_id'  => $product_id,
    'domain'      => $domain
];

$ch = curl_init($pasdigi_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_to_pasdigi));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $pasdigi_api_key // Kunci rahasia digunakan di sini
]);
// Opsi tambahan untuk production:
// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
// curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$response_from_pasdigi = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    // Jika ada error cURL (misal: tidak bisa konek ke server Pasdigi)
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => ['code' => 'service_unavailable', 'message' => 'Tidak dapat menghubungi server lisensi.']]);
    curl_close($ch);
    exit();
}

curl_close($ch);

// ===================================================================
// Teruskan respon dari Pasdigi kembali ke plugin pembeli
// ===================================================================
http_response_code($http_code);
echo $response_from_pasdigi;
