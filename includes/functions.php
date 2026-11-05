<?php
require_once __DIR__ . '/db.php';

// Safe output (XSS se bachao): HTML ma data dikhate waqt hamesha use karo
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Price format: PKR 2,190
function money(int $amount): string {
    return CURRENCY . ' ' . number_format($amount);
}

// Saare active products (optional gender filter)
function getProducts(PDO $pdo, ?string $gender = null): array {
    if ($gender && in_array($gender, ['her', 'him', 'unisex'], true)) {
        $stmt = $pdo->prepare(
            'SELECT * FROM products WHERE is_active = 1 AND gender = ? ORDER BY id'
        );
        $stmt->execute([$gender]);
    } else {
        $stmt = $pdo->query('SELECT * FROM products WHERE is_active = 1 ORDER BY id');
    }
    return $stmt->fetchAll();
}

// Slug se ek product
function getProductBySlug(PDO $pdo, string $slug): ?array {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE slug = ? AND is_active = 1');
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

// Redirect helper
function redirect(string $path): void {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
} 
// ---------- Flash messages ----------
function flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function showFlash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="flash flash-' . e($f['type']) . '">' . e($f['msg']) . '</div>';
    }
}

// ---------- Product image (photo ho to photo, warna placeholder) ----------
function productImageHtml(array $p): string {
    $path = __DIR__ . '/../' . ($p['image'] ?? '');
    if (!empty($p['image']) && file_exists($path)) {
        return '<img src="' . BASE_URL . '/' . e($p['image']) . '" alt="' . e($p['name']) . '">';
    }
    return '<div class="img-placeholder">' . e($p['name']) . '</div>';
}

// ---------- Cart ----------
// Session ke cart ko DB ke products ke sath jod ke items banata hai
function cartItems(PDO $pdo): array {
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) return [];

    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 AND id IN ($placeholders)");
    $stmt->execute($ids);

    $items = [];
    foreach ($stmt->fetchAll() as $p) {
        $qty = (int)$cart[$p['id']];
        $items[] = [
            'product'    => $p,
            'qty'        => $qty,
            'line_total' => (int)$p['price'] * $qty,
        ];
    }
    return $items;
}

// Subtotal, bundle discount aur total
// Rule: cart ma 2 ya zyada scents hon to 10% off
function cartTotals(array $items): array {
    $subtotal = 0;
    $count = 0;
    foreach ($items as $it) {
        $subtotal += $it['line_total'];
        $count    += $it['qty'];
    }
    $discount = $count >= 2 ? (int)round($subtotal * BUNDLE_DISCOUNT_PERCENT / 100) : 0;

    return [
        'count'    => $count,
        'subtotal' => $subtotal,
        'discount' => $discount,
        'total'    => $subtotal - $discount,
    ];
} 
// ---------- WhatsApp link ----------
function waLink(string $text): string {
    return 'https://wa.me/' . WHATSAPP_NUMBER . '?text=' . rawurlencode($text);
}

// ---------- Pakistani mobile number ----------
// 03XXXXXXXXX / +923XXXXXXXXX / 923XXXXXXXXX sab ko 03XXXXXXXXX bana deta hai
function normalizePhone(string $raw): ?string {
    $d = preg_replace('/\D+/', '', $raw);
    if (strpos($d, '92') === 0 && strlen($d) === 12) {
        $d = '0' . substr($d, 2);
    }
    return preg_match('/^03\d{9}$/', $d) ? $d : null;
}

// ---------- CSRF (form ko fake requests se bachata hai) ----------
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void {
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(419);
        die('Session expire ho gaya. Page refresh karke dobara try karo.');
    }
}

// ---------- Users / Admin login ----------
function currentUser(PDO $pdo): ?array {
    static $u = false;
    if ($u !== false) return $u;
    $u = null;
    if (!empty($_SESSION['user_id'])) {
        $st = $pdo->prepare('SELECT id, name, email, phone FROM users WHERE id = ?');
        $st->execute([(int)$_SESSION['user_id']]);
        $u = $st->fetch() ?: null;
        if (!$u) unset($_SESSION['user_id']);
    }
    return $u;
}

function requireUser(PDO $pdo): array {
    $u = currentUser($pdo);
    if (!$u) {
        flash('info', 'Pehle login karein.');
        redirect('login.php');
    }
    return $u;
}

function requireAdmin(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

// Galat password ki koshishon ko roko (5 koshish ke baad 60 second ruko)
function loginLocked(string $key): bool {
    return ($_SESSION['lock_' . $key] ?? 0) > time();
}
function loginFailed(string $key): void {
    $_SESSION['fails_' . $key] = ($_SESSION['fails_' . $key] ?? 0) + 1;
    if ($_SESSION['fails_' . $key] >= 5) {
        $_SESSION['lock_' . $key] = time() + 60;
        $_SESSION['fails_' . $key] = 0;
    }
    usleep(400000);
}

function statusBadge(string $s): string {
    return '<span class="badge badge-' . e($s) . '">' . e(ucfirst($s)) . '</span>';
}
