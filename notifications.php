<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$residentId    = (int) $_SESSION['resident_id'];
$notifications = resident_notifications($residentId);

mark_notifications_read($residentId);

$pageTitle = 'Notifications';
$active    = '';
require __DIR__ . '/includes/header.php';
?>
<h1>Notifications</h1>
<p class="lead">Updates on your requests</p>

<a class="back" href="dashboard.php" aria-label="Back to dashboard"><?= icon('back', 28) ?></a>

<section class="card notif-list">
  <?php if (!$notifications): ?>
    <div class="empty">You have no notifications yet.</div>
  <?php else: ?>
    <?php foreach ($notifications as $item): ?>
      <article class="notif <?= (int) $item['is_read'] === 0 ? 'unread' : '' ?>">
        <span class="notif-dot"></span>
        <div class="notif-body">
          <h3>
            <?php if ($item['request_id'] !== null): ?>
              <a href="request-view.php?id=<?= (int) $item['request_id'] ?>"><?= e($item['title']) ?></a>
            <?php else: ?>
              <?= e($item['title']) ?>
            <?php endif; ?>
          </h3>
          <p><?= e($item['message']) ?></p>
          <small><?= e(format_datetime($item['created_at'])) ?></small>
        </div>
        <form method="post" action="notification-action.php">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
          <button type="submit" class="link-danger" aria-label="Delete notification">Delete</button>
        </form>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<?php if ($notifications): ?>
  <form class="actions" method="post" action="notification-action.php" data-confirm="Delete all notifications?">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="clear">
    <button type="submit" class="btn btn-danger">Clear all</button>
  </form>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
