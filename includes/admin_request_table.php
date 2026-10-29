<?php if (!$rows): ?>
  <div class="empty"><?= e($emptyMessage ?? 'No requests found.') ?></div>
<?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>REFERENCE NO.</th>
          <th>RESIDENT</th>
          <th>DOCUMENT</th>
          <th>DATE SUBMITTED</th>
          <th>STATUS</th>
          <th>ACTION</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><strong><?= e((string) $row['reference_no']) ?></strong></td>
            <td><?= e($row['resident_name']) ?></td>
            <td><div class="doc"><?= icon($row['document_icon'], 20) ?><?= e($row['document_name']) ?></div></td>
            <td><?= e(format_date($row['created_at'])) ?></td>
            <td>
              <span class="badge <?= e(status_class($row['status'])) ?>"><?= e($row['status']) ?></span>
              <?php if ($row['status'] === 'In Progress' && $row['verified_at'] === null): ?>
                <div class="note">Needs verification</div>
              <?php endif; ?>
            </td>
            <td>
              <a class="go" href="request-view.php?id=<?= (int) $row['id'] ?>" aria-label="Open request">
                <?= icon('chevron', 20) ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
