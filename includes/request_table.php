<?php if (!$rows): ?>
  <div class="empty"><?= e($emptyMessage ?? 'No requests yet.') ?></div>
<?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>DOCUMENT NAME</th>
          <th>DATE SUBMITTED</th>
          <th>STATUS</th>
          <th>ACTION</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><div class="doc"><?= icon($row['document_icon']) ?><?= e($row['document_name']) ?></div></td>
            <td><?= e(format_date($row['created_at'])) ?></td>
            <td><span class="badge <?= e(status_class($row['status'])) ?>"><?= e($row['status']) ?></span></td>
            <td>
              <a class="go" href="request-view.php?id=<?= (int) $row['id'] ?>" aria-label="View request">
                <?= icon('chevron', 20) ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
