<?php
/**
 * File: validasi-plugin.php
 * Deskripsi: Kode ini ditempatkan di dalam produk (plugin/software) yang didistribusikan ke pembeli.
 * Kode ini memanggil server proxy milik PENJUAL, bukan server Pasdigi.
 */

/**
 * Fungsi untuk memvalidasi lisensi dengan menghubungi server proxy penjual.
 *
 * @param string $license_key Kunci lisensi dari pengguna.
 * @param int $product_id ID produk ini di Pasdigi.
 * @return array|null Respon dari server atau null jika gagal.
 */
function validate_my_plugin_license($license_key, $product_id) {
    // GANTI DENGAN URL ENDPOINT PROXY DI SERVER ANDA
    $seller_api_url = 'https://website-anda.com/api/proxy-penjual.php';

    // Ambil domain tempat plugin ini diinstal secara otomatis
    $domain = $_SERVER['SERVER_NAME'];

    $data = [
        'license_key' => $license_key,
        'product_id'  => $product_id,
        'domain'      => $domain
    ];

    $ch = curl_init($seller_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
        // Tidak ada API Key rahasia di sini
    ]);

    $response_json = curl_exec($ch);
    curl_close($ch);

    return json_decode($response_json, true);
}

// --- CONTOH PENGGUNAAN DI DALAM PLUGIN ---

// Misalkan nilai ini diambil dari form input di halaman pengaturan plugin
$license_from_user = 'PASDIGI-78-A1B2C3D4E5F6'; 
// ID produk ini (hardcoded di dalam plugin)
$my_product_id = 78;

echo "<h3>Memvalidasi Lisensi...</h3>";
$result = validate_my_plugin_license($license_from_user, $my_product_id);

echo "<pre>";
if (isset($result['success']) && $result['success'] === true) {
    echo "<strong>Status: BERHASIL</strong><br>";
    echo "Pesan: " . htmlspecialchars($result['data']['message']) . "<br>";
    echo "Domain Aktif: " . htmlspecialchars($result['data']['domain']) . "<br>";
    echo "Berlaku Hingga: " . htmlspecialchars($result['data']['expires_at']) . "<br>";
    // Di sini Anda akan menyimpan status 'aktif' ke database
    // update_option('my_plugin_license_status', 'active');
} else {
    $error_message = $result['error']['message'] ?? 'Lisensi tidak valid atau gagal menghubungi server.';
    echo "<strong>Status: GAGAL</strong><br>";
    echo "Pesan Error: " . htmlspecialchars($error_message) . "<br>";
    // Di sini Anda akan menyimpan status 'tidak aktif' ke database
    // update_option('my_plugin_license_status', 'inactive');
}
echo "</pre>";
