<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$search     = trim($_GET['q'] ?? '');
$category   = trim($_GET['category'] ?? '');
$categories = document_categories();

if (!in_array($category, $categories, true)) {
    $category = '';
}

$types = document_types($search, $category);

$pageTitle = 'Request Documents';
$active    = 'request';
require __DIR__ . '/includes/header.php';
?>
<h1>Request Documents</h1>
<p class="lead">Choose and request the documents you need</p>

<form class="toolbar" method="get" action="request-documents.php">
  <label class="search">
    <?= icon('search', 20) ?>
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search documents..." maxlength="60">
  </label>
  <select class="select" name="category" data-autosubmit aria-label="Category">
    <option value="">All Categories</option>
    <?php foreach ($categories as $name): ?>
      <option value="<?= e($name) ?>" <?= $category === $name ? 'selected' : '' ?>><?= e($name) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<?php if (!$types): ?>
  <div class="empty">No documents match your search.</div>
<?php else: ?>
  <div class="doc-grid">
    <?php foreach ($types as $type): ?>
      <article class="doc-card">
        <div class="doc-icon"><?= icon($type['icon'], 40) ?></div>
        <div>
          <h3><?= e($type['name']) ?></h3>
          <p><?= e($type['description']) ?></p>
        </div>
        <a class="btn btn-sm" href="request-form.php?type=<?= (int) $type['id'] ?>">Request</a>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
