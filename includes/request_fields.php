<div class="<?= field_class($errors, 'purpose') ?>">
  <label for="purpose">Purpose of request</label>
  <select id="purpose" name="purpose">
    <option value="">Select a purpose</option>
    <?php foreach (REQUEST_PURPOSES as $purpose): ?>
      <option value="<?= e($purpose) ?>" <?= $values['purpose'] === $purpose ? 'selected' : '' ?>><?= e($purpose) ?></option>
    <?php endforeach; ?>
  </select>
  <?php field_err($errors, 'purpose'); ?>
</div>

<div class="<?= field_class($errors, 'copies') ?>">
  <label for="copies">Number of copies</label>
  <input type="number" id="copies" name="copies" value="<?= e($values['copies']) ?>" min="1" max="<?= MAX_COPIES ?>">
  <?php field_err($errors, 'copies'); ?>
</div>

<div class="<?= field_class($errors, 'notes') ?>">
  <label for="notes">Additional notes (optional)</label>
  <textarea id="notes" name="notes" maxlength="255" placeholder="Anything the barangay staff should know..."><?= e($values['notes']) ?></textarea>
  <?php field_err($errors, 'notes'); ?>
</div>
