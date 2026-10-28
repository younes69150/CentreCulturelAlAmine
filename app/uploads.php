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

/** Messages vocaux : le premier type de chaque liste est celui servi à la lecture. */
function allowed_audio(): array
{
    return [
        'webm' => ['audio/webm', 'video/webm'],
        'ogg'  => ['audio/ogg', 'video/ogg', 'application/ogg'],
        'mp3'  => ['audio/mpeg'],
        'm4a'  => ['audio/mp4', 'audio/x-m4a', 'video/mp4'],
        'wav'  => ['audio/wav', 'audio/x-wav'],
    ];
}

function store_audio(mixed $file): array
{
    $result = store_upload($file, allowed_audio(), 15);
    if (isset($result['ok'])) {
        $ext = strtolower(pathinfo($result['ok']['nom_original'], PATHINFO_EXTENSION));
        $result['ok']['mime'] = allowed_audio()[$ext][0];
    }
    return $result;
}

/** Envoie un fichier audio en gérant les requêtes partielles (nécessaires à Safari). */
function stream_audio(string $path, string $mime): never
{
    $size = filesize($path);
    $start = 0;
    $end = $size - 1;
    if (preg_match('/^bytes=(\d*)-(\d*)$/', $_SERVER['HTTP_RANGE'] ?? '', $m) && ($m[1] !== '' || $m[2] !== '')) {
        if ($m[1] === '') {
            $start = max(0, $size - (int) $m[2]);
        } else {
            $start = (int) $m[1];
            $end = $m[2] !== '' ? min((int) $m[2], $end) : $end;
        }
        if ($start > $end || $start >= $size) {
            http_response_code(416);
            header('Content-Range: bytes */' . $size);
            exit;
        }
        http_response_code(206);
        header("Content-Range: bytes $start-$end/$size");
    }
    header('Content-Type: ' . $mime);
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . ($end - $start + 1));
    header('Cache-Control: private, max-age=3600');
    header("Content-Security-Policy: default-src 'none'; sandbox");
    $fh = fopen($path, 'rb');
    fseek($fh, $start);
    $left = $end - $start + 1;
    while ($left > 0 && !feof($fh)) {
        $chunk = fread($fh, min(65536, $left));
        echo $chunk;
        $left -= strlen($chunk);
    }
    fclose($fh);
    exit;
}

function upload_dir(): string
{
    return BASE_PATH . '/storage/uploads';
}

/**
 * Valide et range un fichier envoyé hors du dossier public.
 * Retourne ['ok' => [...infos]] ou ['error' => message].
 */
function store_upload(mixed $file, ?array $allowed = null, ?int $maxMb = null): array
{
    if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) {
        return ['error' => 'Aucun fichier reçu.'];
    }
    $maxMb ??= (int) config('uploads.max_mb', 20);
    $maxBytes = $maxMb * 1048576;
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
        return ['error' => 'Fichier vide ou trop volumineux (maximum ' . $maxMb . ' Mo).'];
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['error' => 'Fichier invalide.'];
    }

    $original = clean_filename((string) $file['name']);
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $allowed ??= allowed_uploads();
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
