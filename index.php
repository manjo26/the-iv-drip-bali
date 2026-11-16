<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

/*
|--------------------------------------------------------------------------
| THE IV DRIP BALI - HALAMAN PUBLIK
|--------------------------------------------------------------------------
| Semua data paket, layanan, testimoni, dan kontak diambil dari database.
| Paket dan layanan hanya tampil jika aktif.
| Testimoni hanya tampil jika sudah disetujui admin.
*/

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

/* Ambil pengaturan website */
$settings = [];
try {
    $settingsStmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    foreach ($settingsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $exception) {
    $settings = [];
}

$siteName = $settings['site_name'] ?? 'The IV Drip Bali';
$whatsappRaw = $settings['whatsapp'] ?? '6285738304853';
$whatsapp = preg_replace('/[^0-9]/', '', (string)$whatsappRaw);
if ($whatsapp === '') {
    $whatsapp = '6285738304853';
}
$email = $settings['email'] ?? 'infusionbali01@gmail.com';
$serviceHours = $settings['service_hours'] ?? '24/7';

$waMessage = 'Halo ' . $siteName . ', saya ingin bertanya tentang layanan.';
$waLink = 'https://wa.me/' . $whatsapp . '?text=' . rawurlencode($waMessage);

/* Ambil layanan aktif dari dashboard */
$services = [];
try {
    $stmt = $pdo->query("
        SELECT id, title, description, icon
        FROM services
        WHERE is_active = 1
        ORDER BY sort_order ASC, id DESC
    ");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    $services = [];
}

/* Ambil paket aktif dari dashboard */
$packages = [];
try {
    $stmt = $pdo->query("
        SELECT id, name, category, description, image
        FROM packages
        WHERE is_active = 1
        ORDER BY sort_order ASC, id DESC
    ");
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    $packages = [];
}

/* Ambil testimoni yang sudah disetujui admin */
$testimonials = [];
try {
    $stmt = $pdo->query("
        SELECT id, customer_name, country, content
        FROM testimonials
        WHERE is_approved = 1
        ORDER BY sort_order ASC, id DESC
    ");
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    $testimonials = [];
}

/* Konten informasi tetap pada halaman */
$steps = [
    ['Penilaian kesehatan awal', 'Tenaga medis melakukan pemeriksaan awal, tanda vital, dan meninjau riwayat kesehatan.'],
    ['Pemeriksaan dan persetujuan', 'Kelayakan tindakan ditentukan oleh tenaga medis yang berwenang setelah pemeriksaan.'],
    ['Persiapan tindakan', 'Peralatan dan produk medis disiapkan sesuai prosedur keselamatan.'],
    ['Pelaksanaan oleh tenaga medis', 'Tindakan dilakukan oleh tenaga medis yang berwenang sesuai indikasi.'],
    ['Pemantauan dan arahan lanjutan', 'Kondisi pasien dipantau dan pasien menerima arahan setelah tindakan.'],
];

$ingredients = [
    ['NaCl 0.9%', 'Cairan infus yang penggunaannya ditentukan berdasarkan penilaian klinis.'],
    ['Vitamin', 'Vitamin digunakan sesuai kebutuhan dan pertimbangan tenaga medis.'],
    ['Elektrolit', 'Mineral yang berperan dalam keseimbangan cairan dan fungsi tubuh.'],
    ['Antioksidan', 'Komponen yang penggunaannya perlu disesuaikan dengan kondisi pasien.'],
];

$areas = ['Seminyak', 'Canggu', 'Pererenan', 'Umalas', 'Legian', 'Kuta', 'Nusa Dua', 'Sanur', 'Uluwatu', 'Denpasar', 'Kerobokan', 'Bingin'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="The IV Drip Bali provides consultation and home service information across Bali. Contact our team for an initial assessment and scheduling.">
  <title><?= e($siteName) ?> | Home Service</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
<style>
.language-switch{display:inline-flex;align-items:center;gap:3px;padding:4px;border:1px solid #dce8ed;border-radius:999px;background:#f5f9fb}
.language-btn{border:0;background:transparent;color:#58717e;border-radius:999px;padding:6px 10px;font-size:10px;font-weight:800;line-height:1;cursor:pointer}
.language-btn.active{background:#168f9a;color:#fff;box-shadow:0 3px 9px rgba(22,143,154,.18)}
@media(max-width:991px){.language-switch{margin:8px 0}.nav-consult{display:inline-block;margin-top:8px}}
/* Foto hero menyatu dengan latar biru dan tidak terasa seperti kartu terpisah */
.hero{position:relative;overflow:hidden}
.hero .row{position:relative;z-index:1}
.hero .col-lg-5{position:relative;z-index:0}
.hero-visual.hero-photo{
  position:relative;
  width:calc(100% + 112px);
  height:clamp(430px, 48vw, 590px);
  margin-left:-112px;
  border-radius:30px 0 0 30px;
  overflow:hidden;
  isolation:isolate;
  background:#174d66;
  box-shadow:none;
}
.hero-visual.hero-photo::before{
  content:"";
  position:absolute;
  inset:0;
  z-index:1;
  pointer-events:none;
  background:linear-gradient(90deg,
    #174d66 0%,
    rgba(23,77,102,.88) 7%,
    rgba(23,77,102,.36) 23%,
    rgba(23,77,102,0) 48%);
}
.hero-photo-image{
  display:block;
  width:100%;
  height:100%;
  object-fit:cover;
  object-position:72% center;
}
.hero-photo-caption{
  position:absolute;
  left:22px;
  right:22px;
  bottom:22px;
  z-index:2;
  display:flex;
  align-items:center;
  gap:12px;
  padding:14px 17px;
  border:1px solid rgba(255,255,255,.42);
  border-radius:17px;
  color:#fff;
  background:rgba(5,35,48,.68);
  backdrop-filter:blur(12px);
}
.hero-photo-caption-mark{
  display:grid;
  place-items:center;
  width:39px;
  height:39px;
  flex:0 0 39px;
  border-radius:50%;
  color:#063b48;
  background:#b8eee3;
  font-size:20px;
}
.hero-photo-caption strong,.hero-photo-caption small{display:block}
.hero-photo-caption strong{font-size:12px;letter-spacing:1.5px}
.hero-photo-caption small{margin-top:3px;color:rgba(255,255,255,.82);font-size:12px}
.hero-visual.hero-photo .note-bottom{z-index:3}\n.hero-photo-caption{z-index:2}
@media(max-width:991px){
  .hero-visual.hero-photo{
    width:100%;
    height:420px;
    margin:28px 0 12px;
    border-radius:24px;
  }
  .hero-visual.hero-photo::before{
    background:linear-gradient(180deg,rgba(23,77,102,0) 58%,rgba(23,77,102,.22) 100%);
  }
}
@media(max-width:575px){
  .hero-visual.hero-photo{height:340px;border-radius:22px}
  .hero-photo-image{object-position:68% center}
  .hero-photo-caption{left:13px;right:13px;bottom:13px;padding:11px 13px}
}

</style>

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light fixed-top nav-glass">
  <div class="container">
    <a class="navbar-brand brand" href="#">
      <span class="brand-mark">IV</span>
      <span>THE IV DRIP<small>BALI · HOME SERVICE</small></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Buka navigasi">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
        <li><a class="nav-link" href="#services">Layanan</a></li>
        <li><a class="nav-link" href="#packages">Paket IV</a></li>
        <li><a class="nav-link" href="#process">Cara Kerja</a></li>
        <li><a class="nav-link" href="#areas">Area Layanan</a></li>
        <li><a class="nav-link" href="#contact">Kontak</a></li>
        <li class="nav-item">
          <div class="language-switch" role="group" aria-label="Choose language">
            <button type="button" class="language-btn active" data-lang="id" aria-pressed="true">ID</button>
            <button type="button" class="language-btn" data-lang="en" aria-pressed="false">EN</button>
          </div>
        </li>
        <li><a class="btn btn-primary rounded-pill px-4 nav-consult" href="<?= e($waLink) ?>" target="_blank" rel="noopener">Konsultasi WhatsApp</a></li>
      </ul>
    </div>
  </div>
</nav>

<header class="hero">
  <div class="hero-orb orb-one"></div><div class="hero-orb orb-two"></div>
  <div class="container position-relative">
    <div class="row align-items-center min-vh-100 py-5">
      <div class="col-lg-7 pt-5">
        <div class="eyebrow"><span class="pulse"></span> HOME SERVICE · BALI</div>
        <h1>Perawatan IV di lokasi Anda, <em>dengan perhatian profesional.</em></h1>
        <p class="hero-copy">Layanan konsultasi dan home service di berbagai area Bali. Hubungi tim kami untuk informasi, pemeriksaan awal, dan penjadwalan.</p>
        <div class="d-flex flex-wrap gap-3 mt-4">
          <a class="btn btn-primary btn-lg rounded-pill px-4" href="<?= e($waLink) ?>" target="_blank" rel="noopener">Hubungi via WhatsApp <span aria-hidden="true">↗</span></a>
          <a class="btn btn-outline-light btn-lg rounded-pill px-4" href="#services">Jelajahi layanan</a>
        </div>
        <div class="hero-trust mt-5">
          <div><strong><?= e($serviceHours) ?></strong><span>Informasi & permintaan layanan</span></div>
          <div><strong>BALI</strong><span>Layanan di berbagai area</span></div>
          <div><strong>PERSONAL</strong><span>Penilaian sebelum tindakan</span></div>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-block">
        <div class="hero-visual hero-photo">
          <img
            src="assets/images/hero-iv-drip.png"
            alt="Layanan IV Drip Bali di klinik dengan pendampingan tenaga medis"
            class="hero-photo-image"
            fetchpriority="high"
          >
          <div class="hero-photo-caption">
            <span class="hero-photo-caption-mark">✦</span>
            <span><strong>THE IV DRIP BALI</strong><small>Care that comes to you.</small></span>
          </div>
          <div class="floating-note note-bottom"><span class="note-icon">⌖</span><span><b>Across Bali</b><small>Ask about your area</small></span></div>
        </div>
      </div>
    </div>
  </div>
</header>

<section class="trust-strip">
  <div class="container">
    <div class="row g-3 text-center">
      <div class="col-6 col-lg-3"><span>✦</span> Penilaian awal</div>
      <div class="col-6 col-lg-3"><span>＋</span> Tenaga medis</div>
      <div class="col-6 col-lg-3"><span>⌖</span> Home service Bali</div>
      <div class="col-6 col-lg-3"><span>◷</span> Hubungi <?= e($serviceHours) ?></div>
    </div>
  </div>
</section>

<section class="section-pad" id="services">
  <div class="container">
    <div class="section-heading">
      <div class="eyebrow light">WHAT WE OFFER</div>
      <h2>Solusi layanan yang <em>memperhatikan Anda.</em></h2>
      <p>Kenali layanan kami dan konsultasikan kebutuhan Anda. Jenis tindakan ditentukan setelah penilaian oleh tenaga medis yang berwenang.</p>
    </div>
    <div class="row g-4">
      <?php if (!$services): ?>
        <div class="col-12"><div class="alert alert-light text-center">Informasi layanan sedang diperbarui. Silakan hubungi tim kami.</div></div>
      <?php else: ?>
        <?php foreach ($services as $i => $service): ?>
          <div class="col-md-6 col-lg-4">
            <article class="service-card h-100">
              <div class="card-icon"><?= e($service['icon'] ?: '✧') ?></div>
              <div class="card-number"><?= e(str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT)) ?></div>
              <h3><?= e($service['title']) ?></h3>
              <p><?= nl2br(e($service['description'])) ?></p>
              <a href="#contact" class="text-link">Tanyakan layanan <span>↗</span></a>
            </article>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section-pad section-soft" id="process">
  <div class="container">
    <div class="section-heading">
      <div class="eyebrow light">SIMPLE & CONSIDERED</div>
      <h2>Bagaimana proses <em>layanan kami?</em></h2>
      <p>Keselamatan dan penilaian kondisi menjadi bagian penting sebelum tindakan medis apa pun.</p>
    </div>
    <div class="row g-4">
      <?php foreach ($steps as $i => $step): ?>
        <div class="col-md-6 col-lg">
          <div class="step-card h-100">
            <div class="step-no"><?= e(str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT)) ?></div>
            <h3><?= e($step[0]) ?></h3>
            <p><?= e($step[1]) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-pad" id="ingredients">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5">
        <div class="eyebrow light">OUR INGREDIENTS</div>
        <h2 class="display-title">Informasi bahan <em>yang digunakan.</em></h2>
        <p class="muted-copy">Produk dan bahan medis harus digunakan sesuai indikasi, pemeriksaan, serta keputusan tenaga medis yang berwenang.</p>
        <a class="btn btn-outline-primary rounded-pill px-4" href="<?= e($waLink) ?>" target="_blank" rel="noopener">Tanya tim kami</a>
      </div>
      <div class="col-lg-7">
        <div class="ingredient-grid">
          <?php foreach ($ingredients as $i => $ingredient): ?>
            <div class="ingredient-item">
              <span class="ingredient-symbol"><?= ['H₂O', '✧', '＋', '◈'][$i] ?></span>
              <div><h3><?= e($ingredient[0]) ?></h3><p><?= e($ingredient[1]) ?></p></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section-pad section-dark" id="packages">
  <div class="container">
    <div class="section-heading section-heading-light">
      <div class="eyebrow">EXPLORE OUR PACKAGES</div>
      <h2>Pilihan paket <em>IV therapy.</em></h2>
      <p>Daftar informasi paket. Ketersediaan dan kelayakan layanan perlu dikonfirmasi melalui konsultasi dan pemeriksaan medis.</p>
    </div>

    <div class="package-filters d-flex flex-wrap justify-content-center gap-2 mb-4">
      <button class="filter-btn active" data-filter="all" type="button">Semua</button>
      <button class="filter-btn" data-filter="Pemulihan" type="button">Pemulihan</button>
      <button class="filter-btn" data-filter="Hidrasi" type="button">Hidrasi</button>
      <button class="filter-btn" data-filter="Kekebalan" type="button">Kekebalan</button>
      <button class="filter-btn" data-filter="Energi" type="button">Energi</button>
    </div>

    <div class="row g-3" id="packageGrid">
      <?php if (!$packages): ?>
        <div class="col-12"><div class="alert alert-light text-center">Belum ada paket yang dipublikasikan. Silakan hubungi tim kami untuk informasi.</div></div>
      <?php else: ?>
        <?php foreach ($packages as $i => $package): ?>
          <div class="col-md-6 col-lg-4 package-col" data-category="<?= e($package['category']) ?>">
            <article class="package-card h-100">
              <?php if (!empty($package['image'])): ?>
                <div class="package-image mb-3">
                  <img src="<?= e($package['image']) ?>" alt="<?= e($package['name']) ?>" loading="lazy" style="width:100%;height:190px;object-fit:cover;border-radius:14px;">
                </div>
              <?php endif; ?>
              <div class="package-top">
                <span class="package-index">PACKAGE <?= e(str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                <span class="package-dot"></span>
              </div>
              <span class="package-category"><?= e($package['category']) ?></span>
              <h3><?= e($package['name']) ?></h3>
              <p><?= nl2br(e($package['description'])) ?></p>
              <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" class="package-link">Tanyakan paket <span>↗</span></a>
            </article>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section-pad" id="areas">
  <div class="container">
    <div class="row align-items-end mb-4">
      <div class="col-lg-7"><div class="eyebrow light">SERVICE AREAS</div><h2 class="display-title">Menjangkau berbagai <em>area di Bali.</em></h2></div>
      <div class="col-lg-5"><p class="muted-copy mb-0">Hubungi tim kami untuk memastikan ketersediaan layanan di lokasi Anda.</p></div>
    </div>
    <div class="area-list">
      <?php foreach ($areas as $area): ?><span class="area-chip"><i>⌖</i><?= e($area) ?></span><?php endforeach; ?>
    </div>
    <div class="area-cta mt-4">
      <div><h3>Lokasi Anda belum tercantum?</h3><p>Tanyakan cakupan layanan untuk area lainnya di Bali.</p></div>
      <a class="btn btn-light rounded-pill px-4" href="<?= e($waLink) ?>" target="_blank" rel="noopener">Periksa area ↗</a>
    </div>
  </div>
</section>

<section class="section-pad section-soft" id="testimonials">
  <div class="container">
    <div class="section-heading">
      <div class="eyebrow light">CLIENT STORIES</div>
      <h2>Cerita dan pengalaman <em>pelanggan.</em></h2>
      <p>Testimoni ditampilkan setelah mendapatkan persetujuan admin.</p>
    </div>
    <div class="row g-4">
      <?php if (!$testimonials): ?>
        <div class="col-12"><div class="alert alert-light text-center">Belum ada testimoni yang dipublikasikan.</div></div>
      <?php else: ?>
        <?php foreach ($testimonials as $testimonial): ?>
          <div class="col-md-6 col-lg-4">
            <article class="testimonial-card h-100">
              <div class="stars" aria-label="Testimoni pelanggan">★★★★★</div>
              <p>“<?= e($testimonial['content']) ?>”</p>
              <div class="person">
                <span class="avatar"><?= e(strtoupper(substr($testimonial['customer_name'], 0, 1))) ?></span>
                <div>
                  <b><?= e($testimonial['customer_name']) ?></b>
                  <small><?= e($testimonial['country'] ?: 'Pelanggan') ?></small>
                </div>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section-pad contact-section" id="contact">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6">
        <div class="eyebrow">GET IN TOUCH</div>
        <h2 class="display-title">Kami siap <em>mendengarkan.</em></h2>
        <p class="contact-copy">Punya pertanyaan tentang layanan atau ingin memeriksa ketersediaan? Hubungi tim The IV Drip Bali.</p>
        <div class="contact-detail"><span>☏</span><div><small>WHATSAPP</small><b><?= e($whatsapp) ?></b></div></div>
        <div class="contact-detail"><span>✉</span><div><small>EMAIL</small><b><?= e($email) ?></b></div></div>
        <div class="contact-detail"><span>◷</span><div><small>INFORMASI LAYANAN</small><b>Hubungi kami <?= e($serviceHours) ?></b></div></div>
      </div>
      <div class="col-lg-6">
        <div class="contact-box">
          <div class="eyebrow light">LET'S TALK</div>
          <h3>Mulai dengan konsultasi.</h3>
          <p>Isi informasi singkat berikut. Pesan akan diteruskan ke WhatsApp tim kami.</p>
          <form id="contactForm">
            <div class="mb-3"><label class="form-label" for="name">Nama</label><input class="form-control" id="name" required placeholder="Nama Anda" data-placeholder-en="Your name"></div>
            <div class="mb-3"><label class="form-label" for="location">Lokasi di Bali</label><input class="form-control" id="location" required placeholder="Contoh: Seminyak" data-placeholder-en="e.g. Seminyak"></div>
            <div class="mb-3"><label class="form-label" for="message">Pesan</label><textarea class="form-control" id="message" rows="3" required placeholder="Apa yang ingin Anda tanyakan?" data-placeholder-en="What would you like to ask?"></textarea></div>
            <button class="btn btn-primary w-100 rounded-pill py-3" type="submit">Kirim melalui WhatsApp ↗</button>
            <small class="form-note">Jangan kirim informasi medis sensitif melalui formulir publik ini.</small>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-5">
        <a class="navbar-brand brand brand-footer" href="#"><span class="brand-mark">IV</span><span>THE IV DRIP<small>BALI · HOME SERVICE</small></span></a>
        <p class="footer-copy mt-3">Layanan konsultasi dan home service di Bali. Hubungi kami untuk informasi dan pemeriksaan awal.</p>
      </div>
      <div class="col-6 col-lg-2"><h4>Navigasi</h4><a href="#services">Layanan</a><a href="#packages">Paket IV</a><a href="#process">Cara Kerja</a><a href="#areas">Area Layanan</a></div>
      <div class="col-6 col-lg-2"><h4>Informasi</h4><a href="#testimonials">Testimoni</a><a href="#contact">Kontak</a><a href="privacy.php">Kebijakan Privasi</a><a href="terms.php">Syarat & Ketentuan</a></div>
      <div class="col-lg-3"><h4>Hubungi kami</h4><a href="<?= e($waLink) ?>" target="_blank" rel="noopener">WhatsApp <?= e($serviceHours) ?></a><a href="mailto:<?= e($email) ?>">Email</a><span class="footer-hours">Informasi layanan: <?= e($serviceHours) ?></span></div>
    </div>
    <div class="footer-bottom"><span>© <?= date('Y') ?> <?= e($siteName) ?>. Hak cipta dilindungi.</span><span>Made with care in Bali.</span></div>
  </div>
</footer>

<a class="floating-wa" href="<?= e($waLink) ?>" target="_blank" rel="noopener" aria-label="Hubungi WhatsApp">☏</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>

<script>
/* Bilingual interface: static page content is translated; dashboard content remains admin-managed. */
document.addEventListener('DOMContentLoaded', function () {
    const translations = {
        '.navbar-nav li:nth-child(1) .nav-link': 'Services',
        '.navbar-nav li:nth-child(2) .nav-link': 'IV Packages',
        '.navbar-nav li:nth-child(3) .nav-link': 'How It Works',
        '.navbar-nav li:nth-child(4) .nav-link': 'Service Areas',
        '.navbar-nav li:nth-child(5) .nav-link': 'Contact',
        '.nav-consult': 'WhatsApp Consultation',
        '.hero h1': 'IV care at your location, <em>with professional attention.</em>',
        '.hero-copy': 'Consultation and home service across Bali. Contact our team for information, an initial assessment, and scheduling.',
        '.hero .btn-primary': 'Contact us on WhatsApp <span aria-hidden="true">↗</span>',
        '.hero .btn-outline-light': 'Explore our services',
        '.hero-trust div:nth-child(1) span': 'Information & service requests',
        '.hero-trust div:nth-child(2) span': 'Service across selected areas',
        '.hero-trust div:nth-child(3) span': 'Assessment before treatment',
        '.trust-strip .col-6:nth-child(1)': '<span>✦</span> Initial assessment',
        '.trust-strip .col-6:nth-child(2)': '<span>＋</span> Medical professionals',
        '.trust-strip .col-6:nth-child(3)': '<span>⌖</span> Home service in Bali',
        '.trust-strip .col-6:nth-child(4)': '<span>◷</span> Contact us <?= e($serviceHours) ?>',
        '#services .section-heading h2': 'Services designed <em>around your care.</em>',
        '#services .section-heading p': 'Explore our services and discuss your needs with our team. Any treatment is subject to assessment by an authorised medical professional.',
        '#services .alert': 'Service information is being updated. Please contact our team.',
        '#process .section-heading h2': 'How does <em>our process work?</em>',
        '#process .section-heading p': 'Your safety and clinical assessment are essential before any medical procedure.',
        '#ingredients .display-title': 'Information about <em>the ingredients used.</em>',
        '#ingredients .muted-copy': 'Medical products and ingredients must be used according to clinical indications, assessment, and the decision of an authorised medical professional.',
        '#ingredients .btn': 'Ask our team',
        '#packages .section-heading h2': 'Explore our <em>IV therapy packages.</em>',
        '#packages .section-heading p': 'Package information is provided for reference. Availability and suitability must be confirmed through consultation and medical assessment.',
        '.filter-btn[data-filter="all"]': 'All',
        '.filter-btn[data-filter="Pemulihan"]': 'Recovery',
        '.filter-btn[data-filter="Hidrasi"]': 'Hydration',
        '.filter-btn[data-filter="Kekebalan"]': 'Immunity',
        '.filter-btn[data-filter="Energi"]': 'Energy',
        '#packageGrid .alert': 'No packages are currently published. Please contact our team for information.',
        '#areas .display-title': 'Serving locations <em>across Bali.</em>',
        '#areas .muted-copy': 'Contact our team to confirm service availability in your location.',
        '.area-cta h3': 'Can’t find your location?',
        '.area-cta p': 'Ask our team about service coverage in other areas of Bali.',
        '.area-cta .btn': 'Check service area ↗',
        '#testimonials .section-heading h2': 'Client stories and <em>experiences.</em>',
        '#testimonials .section-heading p': 'Testimonials are displayed after approval by our admin team.',
        '#testimonials .alert': 'There are no published testimonials yet.',
        '#contact .display-title': 'We are here <em>to listen.</em>',
        '.contact-copy': 'Have questions about our services or want to check availability? Contact The IV Drip Bali team.',
        '#contact .contact-detail:nth-of-type(3) small': 'SERVICE INFORMATION',
        '#contact .contact-detail:nth-of-type(3) b': 'Contact us <?= e($serviceHours) ?>',
        '#contact .contact-box h3': 'Start with a consultation.',
        '#contact .contact-box > p': 'Share a few details below. Your message will be sent to our team via WhatsApp.',
        'label[for="name"]': 'Name',
        'label[for="location"]': 'Location in Bali',
        'label[for="message"]': 'Message',
        '#contactForm button[type="submit"]': 'Send via WhatsApp ↗',
        '.form-note': 'Please do not submit sensitive medical information through this public form.',
        '.footer .footer-copy': 'Consultation and home service across Bali. Contact us for information and an initial assessment.',
        '.footer .col-6.col-lg-2:nth-of-type(2) h4': 'Navigation',
        '.footer .col-6.col-lg-2:nth-of-type(2) a:nth-of-type(1)': 'Services',
        '.footer .col-6.col-lg-2:nth-of-type(2) a:nth-of-type(2)': 'IV Packages',
        '.footer .col-6.col-lg-2:nth-of-type(2) a:nth-of-type(3)': 'How It Works',
        '.footer .col-6.col-lg-2:nth-of-type(2) a:nth-of-type(4)': 'Service Areas',
        '.footer .col-6.col-lg-2:nth-of-type(3) h4': 'Information',
        '.footer .col-6.col-lg-2:nth-of-type(3) a:nth-of-type(1)': 'Testimonials',
        '.footer .col-6.col-lg-2:nth-of-type(3) a:nth-of-type(2)': 'Contact',
        '.footer .col-6.col-lg-2:nth-of-type(3) a:nth-of-type(3)': 'Privacy Policy',
        '.footer .col-6.col-lg-2:nth-of-type(3) a:nth-of-type(4)': 'Terms & Conditions',
        '.footer .col-lg-3 h4': 'Contact us',
        '.footer .footer-hours': 'Service information: <?= e($serviceHours) ?>',
        '.footer-bottom span:last-child': 'Made with care in Bali.'
    };

    const listTranslations = {
        '#process .step-card h3': [
            'Initial health assessment', 'Examination and consent', 'Treatment preparation',
            'Procedure by medical professionals', 'Monitoring and follow-up guidance'
        ],
        '#process .step-card p': [
            'Medical professionals conduct an initial examination, check vital signs, and review your medical history.',
            'Eligibility is determined by an authorised medical professional after an assessment.',
            'Medical equipment and products are prepared according to safety procedures.',
            'The procedure is performed by authorised medical professionals when clinically indicated.',
            'Your condition is monitored and you receive guidance after the procedure.'
        ],
        '#ingredients .ingredient-item h3': ['NaCl 0.9%', 'Vitamins', 'Electrolytes', 'Antioxidants'],
        '#ingredients .ingredient-item p': [
            'An IV fluid used according to clinical assessment.',
            'Vitamins are used according to individual needs and medical advice.',
            'Minerals that support fluid balance and normal body functions.',
            'Components whose use should be based on the patient’s condition.'
        ]
    };

    const originals = new Map();
    function setHtml(element, html) {
        if (!originals.has(element)) originals.set(element, element.innerHTML);
        element.innerHTML = html;
    }
    function applyLanguage(language) {
        if (language === 'id') {
            originals.forEach((html, element) => { if (element.isConnected) element.innerHTML = html; });
            originals.clear();
        } else {
            Object.entries(translations).forEach(([selector, value]) => {
                document.querySelectorAll(selector).forEach(element => setHtml(element, value));
            });
            Object.entries(listTranslations).forEach(([selector, values]) => {
                document.querySelectorAll(selector).forEach((element, index) => {
                    if (values[index] !== undefined) setHtml(element, values[index]);
                });
            });
            document.querySelectorAll('.service-card .text-link').forEach(element => setHtml(element, 'Ask about this service <span>↗</span>'));
            document.querySelectorAll('.package-card .package-link').forEach(element => setHtml(element, 'Ask about this package <span>↗</span>'));
            document.querySelectorAll('.package-card .package-category').forEach(element => {
                const category = element.textContent.trim();
                const translated = { 'Pemulihan':'Recovery', 'Hidrasi':'Hydration', 'Kekebalan':'Immunity', 'Energi':'Energy' }[category];
                if (translated) setHtml(element, translated);
            });
            document.querySelectorAll('.package-card .package-index').forEach(element => {
                setHtml(element, element.textContent.replace('PACKAGE', 'PACKAGE'));
            });
        }

        document.querySelectorAll('[data-placeholder-en]').forEach(field => {
            field.placeholder = language === 'en' ? field.dataset.placeholderEn : (
                field.id === 'name' ? 'Nama Anda' : field.id === 'location' ? 'Contoh: Seminyak' : 'Apa yang ingin Anda tanyakan?'
            );
        });
        document.documentElement.lang = language;
        document.querySelectorAll('.language-btn').forEach(button => {
            const active = button.dataset.lang === language;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        try { localStorage.setItem('ivdrip-language', language); } catch (error) {}
    }

    document.querySelectorAll('.language-btn').forEach(button => {
        button.addEventListener('click', () => applyLanguage(button.dataset.lang));
    });
    let preferredLanguage = 'id';
    try { if (localStorage.getItem('ivdrip-language') === 'en') preferredLanguage = 'en'; } catch (error) {}
    if (preferredLanguage === 'en') applyLanguage('en');
});
</script>

<script>
/* Form kontak mengirim pesan melalui nomor WhatsApp dari database */
document.addEventListener('DOMContentLoaded', function () {
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const name = document.getElementById('name').value.trim();
            const location = document.getElementById('location').value.trim();
            const message = document.getElementById('message').value.trim();

            const text = document.documentElement.lang === 'en'
                ? `Hello The IV Drip Bali, my name is ${name}.\nLocation: ${location}.\nMessage: ${message}`
                : `Halo The IV Drip Bali, saya ${name}.\nLokasi: ${location}.\nPesan: ${message}`;
            const phone = <?= json_encode($whatsapp) ?>;
            window.open('https://wa.me/' + phone + '?text=' + encodeURIComponent(text), '_blank', 'noopener');
        });
    }

    /* Filter paket berdasarkan kategori */
    const filterButtons = document.querySelectorAll('.filter-btn');
    const packageCards = document.querySelectorAll('.package-col');

    filterButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const filter = this.dataset.filter;

            filterButtons.forEach(function (item) {
                item.classList.remove('active');
            });
            this.classList.add('active');

            packageCards.forEach(function (card) {
                const category = card.dataset.category;
                card.style.display = (filter === 'all' || category === filter) ? '' : 'none';
            });
        });
    });
});
</script>
</body>
</html>
