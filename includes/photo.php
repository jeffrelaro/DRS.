<?php
const PHOTO_DIR       = __DIR__ . '/../uploads/avatars';
const PHOTO_MAX_BYTES = 2 * 1024 * 1024;
const PHOTO_TYPES     = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

function photo_path(int $residentId): ?string
{
    foreach (PHOTO_TYPES as $ext) {
        $path = PHOTO_DIR . '/' . $residentId . '.' . $ext;
        if (is_file($path)) {
            return $path;
        }
    }
    return null;
}

function photo_url(int $residentId): ?string
{
    $path = photo_path($residentId);
    return $path ? 'uploads/avatars/' . basename($path) . '?v=' . filemtime($path) : null;
}

function delete_photo(int $residentId): void
{
    while (($path = photo_path($residentId)) !== null) {
        unlink($path);
    }
}

function photo_error(array $file): string
{
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return 'The photo is too large. Maximum size is 2 MB.';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'The photo could not be uploaded. Please try again.';
    }
    if ($file['size'] > PHOTO_MAX_BYTES) {
        return 'The photo is too large. Maximum size is 2 MB.';
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(PHOTO_TYPES[$mime]) || @getimagesize($file['tmp_name']) === false) {
        return 'Please choose a JPG, PNG or WEBP image.';
    }
    return '';
}

function save_photo(int $residentId, array $file): void
{
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

    if (!is_dir(PHOTO_DIR)) {
        mkdir(PHOTO_DIR, 0755, true);
    }

    delete_photo($residentId);
    move_uploaded_file($file['tmp_name'], PHOTO_DIR . '/' . $residentId . '.' . PHOTO_TYPES[$mime]);
}
