<?php
$reviewFile = __DIR__ . '/data/reviews.json';
$defaultReviews = [
    [
        'nama' => 'Alya',
        'produk' => 'Kopi Arabika Gayo',
        'rating' => 5,
        'catatan' => 'Rasa bersih dan aromanya enak.',
    ],
    [
        'nama' => 'Dimas',
        'produk' => 'Gift Box Senja',
        'rating' => 4,
        'catatan' => 'Packaging premium, cocok untuk hadiah.',
    ],
];

if (!is_dir(dirname($reviewFile))) {
    mkdir(dirname($reviewFile), 0777, true);
}

if (!file_exists($reviewFile)) {
    file_put_contents($reviewFile, json_encode($defaultReviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$storedJson = @file_get_contents($reviewFile);
$reviews = json_decode((string) $storedJson, true);
if (!is_array($reviews)) {
    $reviews = $defaultReviews;
}

$statusMessage = '';
$statusClass = '';

if (($_GET['status'] ?? '') === 'success') {
  $statusMessage = 'Review berhasil ditambahkan ke tabel.';
  $statusClass = 'alert-success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim((string) ($_POST['nama'] ?? ''));
    $produk = trim((string) ($_POST['produk'] ?? ''));
    $rating = (int) ($_POST['jumlah'] ?? 0);
    $catatan = trim((string) ($_POST['catatan'] ?? ''));

    if ($nama === '' || $produk === '' || $rating < 1 || $rating > 5) {
        $statusMessage = 'Data review belum lengkap. Pastikan nama, produk, dan rating terisi.';
        $statusClass = 'alert-danger';
    } else {
        $newReview = [
            'nama' => substr($nama, 0, 80),
            'produk' => substr($produk, 0, 80),
            'rating' => $rating,
            'catatan' => $catatan !== '' ? substr($catatan, 0, 300) : '-',
        ];

        array_unshift($reviews, $newReview);
        $saved = @file_put_contents($reviewFile, json_encode($reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($saved === false) {
            $statusMessage = 'Review gagal disimpan. Cek izin tulis folder data.';
            $statusClass = 'alert-danger';
        } else {
          header('Location: order.php?status=success');
          exit;
        }
    }
}

$totalReviews = count($reviews);
$ratingTotal = array_sum(array_map(static fn(array $item): int => (int) ($item['rating'] ?? 0), $reviews));
$averageRating = $totalReviews > 0 ? number_format($ratingTotal / $totalReviews, 1) : '0.0';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Rating & Order - Kopi Senja</title>
    <link rel="icon" type="image/png" href="logo/logo.png" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
      <div class="container">
        <a class="navbar-brand" href="index.html">Cerita Senja</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
          <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
            <li class="nav-item"><a class="nav-link" href="index.html">Beranda</a></li>
            <li class="nav-item"><a class="nav-link" href="about.html">Profil</a></li>
            <li class="nav-item"><a class="nav-link" href="products.html">Produk</a></li>
            <li class="nav-item"><a class="nav-link" href="services.html">Layanan</a></li>
            <li class="nav-item"><a class="nav-link" href="gallery.html">Galeri</a></li>
            <li class="nav-item"><a class="nav-link active" href="order.php">Pesanan</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <header class="page-banner">
      <div class="container">
        <div class="banner-shell">
          <div class="tag mb-3 d-inline-block">Rating pelanggan</div>
          <h1 class="display-4 mb-3">Isi rating, testimoni tampil di tabel, dan order lewat social media.</h1>
          <p class="section-text mb-0">Bagian review di halaman ini sudah diproses server-side pakai PHP agar data tabel tersimpan.</p>
        </div>
      </div>
    </header>

    <main>
      <section class="section pt-0">
        <div class="container">
          <div class="row g-4">
            <div class="col-lg-5">
              <div class="contact-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h2 class="section-title mb-0">Form Rating</h2>
                  <span class="tag">Aktif</span>
                </div>

                <?php if ($statusMessage !== ''): ?>
                <div class="alert <?= h($statusClass) ?>" role="alert">
                  <?= h($statusMessage) ?>
                </div>
                <?php endif; ?>

                <div class="order-photo-wrap mb-4">
                  <img
                    class="order-photo"
                    src="https://images.unsplash.com/photo-1559925393-8be0ec4767c8?auto=format&fit=crop&w=1400&q=80"
                    alt="Barista menyambut pelanggan dengan ramah"
                    loading="lazy"
                  />
                  <div class="order-photo-caption">Pelayanan hangat dan ramah untuk setiap pelanggan.</div>
                </div>

                <form id="orderForm" class="row g-3" method="post" action="order.php">
                  <div class="col-12">
                    <label for="nama" class="form-label">Nama pelanggan</label>
                    <input type="text" class="form-control" id="nama" name="nama" placeholder="Contoh: Rina" required />
                  </div>
                  <div class="col-12">
                    <label for="produk" class="form-label">Produk yang dicoba</label>
                    <select class="form-select" id="produk" name="produk" required>
                      <option value="">Pilih produk</option>
                      <option>Kopi Arabika Gayo</option>
                      <option>Kopi Robusta Temanggung</option>
                      <option>Gift Box Senja</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <label for="jumlah" class="form-label">Rating (1 - 5)</label>
                    <select class="form-select" id="jumlah" name="jumlah" required>
                      <option value="">Pilih rating</option>
                      <option value="5">5 - Sangat puas</option>
                      <option value="4">4 - Puas</option>
                      <option value="3">3 - Cukup</option>
                      <option value="2">2 - Kurang</option>
                      <option value="1">1 - Tidak puas</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <label for="catatan" class="form-label">Testimoni</label>
                    <textarea class="form-control" id="catatan" name="catatan" rows="4" placeholder="Contoh: rasanya enak dan pengiriman cepat"></textarea>
                  </div>
                  <div class="col-12 d-grid">
                    <button type="submit" class="btn btn-brand btn-lg">Tambah rating ke tabel</button>
                  </div>
                </form>

                <hr class="my-4" />

                <div class="social-order-card">
                  <h3 class="h2 mb-3">Order via Social Media</h3>
                  <p class="section-text mb-3">Klik salah satu tombol untuk langsung chat/admin.</p>
                  <div class="d-grid gap-2">
                    <a
                      class="btn btn-success btn-lg"
                      target="_blank"
                      rel="noopener noreferrer"
                      href="https://api.whatsapp.com/qr/XY5PSWPK2EZLD1?autoload=1&app_absent=0"
                    >
                      Order via WhatsApp
                    </a>
                    <a
                      class="btn btn-outline-brand btn-lg"
                      target="_blank"
                      rel="noopener noreferrer"
                      href="https://instagram.com/kopisenja.id"
                    >
                      Kunjungi Instagram
                    </a>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="table-card h-100">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                  <div>
                    <h2 class="section-title mb-1">Tabel Rating Pelanggan</h2>
                    <p class="section-text mb-0">Total ulasan: <span id="totalOrders" class="tag"><?= h((string) $totalReviews) ?></span></p>
                  </div>
                  <div class="tag" id="averageRating">Rata-rata: <?= h($averageRating) ?></div>
                </div>
                <div class="table-responsive">
                  <table class="table align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Nama</th>
                        <th>Produk</th>
                        <th>Rating</th>
                        <th>Testimoni</th>
                      </tr>
                    </thead>
                    <tbody id="orderTableBody">
                      <?php foreach ($reviews as $review): ?>
                      <tr>
                        <td><?= h((string) ($review['nama'] ?? '-')) ?></td>
                        <td><?= h((string) ($review['produk'] ?? '-')) ?></td>
                        <td><?= h((string) ($review['rating'] ?? '0')) ?></td>
                        <td><?= h((string) ($review['catatan'] ?? '-')) ?></td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-12">
              <div class="table-card">
                <h3 class="section-title mb-2">Lokasi UMKM</h3>
                <p class="section-text mb-3">Lokasi yang anda bisa kunjungi</p>
                <div class="map-embed">
                  <iframe
                    title="Lokasi Kopi Senja"
                    src="https://www.google.com/maps?q=Semarang&output=embed"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen
                  ></iframe>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="footer">
      <div class="container d-flex flex-column flex-md-row justify-content-between gap-2 border-top pt-4">
        <div>Copyright 2026 Kopi Senja.</div>
        <div class="d-flex gap-3">
          <a href="products.html">Produk</a>
          <a href="services.html">Layanan</a>
        </div>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
