<?php
declare(strict_types=1);

const OOXML = ['application/zip', 'application/octet-stream'];
const OLE = ['application/vnd.ms-office', 'application/CDFV2', 'application/x-ole-storage'];

/** Extensions acceptées => types MIME réels acceptés (détectés par finfo). */
function allowed_uploads(): array
{
    return [
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword', ...OLE],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', ...OOXML],
        'xls'  => ['application/vnd.ms-excel', ...OLE],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', ...OOXML],
        'ppt'  => ['application/vnd.ms-powerpoint', ...OLE],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', ...OOXML],
        'odt'  => ['application/vnd.oasis.opendocument.text', ...OOXML],
        'ods'  => ['application/vnd.oasis.opendocument.spreadsheet', ...OOXML],
        'odp'  => ['application/vnd.oasis.opendocument.presentation', ...OOXML],
        'txt'  => ['text/plain'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'webp' => ['image/webp'],
        'mp3'  => ['audio/mpeg'],
        'm4a'  => ['audio/mp4', 'audio/x-m4a'],
        'mp4'  => ['video/mp4'],
    ];
}

function upload_dir(): string
{
    return BASE_PATH . '/storage/uploads';
}

/**
 * Valide et range un fichier envoyé hors du dossier public.
 * Retourne ['ok' => [...infos]] ou ['error' => message].
 */
function store_upload(mixed $file): array
{
    if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) {
        return ['error' => 'Aucun fichier reçu.'];
    }
    $maxBytes = (int) config('uploads.max_mb', 20) * 1048576;
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['error' => 'Choisissez un fichier.'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['error' => 'Fichier trop volumineux.'];
        default:
            return ['error' => 'L\'envoi du fichier a échoué.'];
    }
    if ($file['size'] <= 0 || $file['size'] > $maxBytes) {
        return ['error' => 'Fichier vide ou trop volumineux (maximum ' . config('uploads.max_mb', 20) . ' Mo).'];
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['error' => 'Fichier invalide.'];
    }

    $original = clean_filename((string) $file['name']);
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $allowed = allowed_uploads();
    if (!isset($allowed[$ext])) {
        return ['error' => 'Type de fichier non autorisé. Formats acceptés : ' . implode(', ', array_keys($allowed)) . '.'];
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: 'application/octet-stream';
    if (!in_array($mime, $allowed[$ext], true)) {
        return ['error' => 'Le contenu du fichier ne correspond pas à son extension.'];
    }

    $stored = bin2hex(random_bytes(32));
    $dest = upload_dir() . '/' . $stored;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['error' => 'Impossible d\'enregistrer le fichier.'];
    }
    @chmod($dest, 0640);

    // Type servi au téléchargement : celui de l'extension, jamais du HTML.
    $serveMime = in_array($mime, OOXML, true) || in_array($mime, OLE, true) ? $allowed[$ext][0] : $mime;

    return ['ok' => [
        'nom_original' => $original,
        'nom_stockage' => $stored,
        'mime'         => $serveMime,
        'taille'       => (int) $file['size'],
    ]];
}

function clean_filename(string $name): string
{
    $name = basename(str_replace('\\', '/', $name));
    $name = preg_replace('/[\x00-\x1F\x7F"]/u', '', $name);
    $name = trim($name, " .");
    return mb_substr($name !== '' ? $name : 'fichier', -200);
}

function delete_upload(string $stored): void
{
    if (preg_match('/^[a-f0-9]{64}$/', $stored)) {
        @unlink(upload_dir() . '/' . $stored);
    }
}
